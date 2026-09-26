<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuthController extends Controller
{
    /**
     * Само-регистрация (Фаза 10, задача 10.5, решения D4/D5).
     *
     * До Фазы 10 метод создавал только `users` — без специализации. Поскольку
     * специализация это сквозная ось (к ней привязаны заказы, клиенты, каталог),
     * новый пользователь получал аккаунт без рабочего профиля. Теперь вместе с
     * пользователем создаются выбранные специализации (1..N), а ответ содержит
     * их — клиент кладёт записи локально без операции в очередь (иначе дубли).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed', // пароль + подтверждение
            // Лимит = число доступных ниш в реестре пресетов (12), см.
            // `src/domain/presets/index.js` на клиенте и `SpecializationTemplateSeeder`.
            'specializations' => 'sometimes|array|max:12',
            'specializations.*.name' => 'required_with:specializations|string|max:255',
            'specializations.*.preset_key' => 'nullable|string|max:64',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->createSpecializations($user, $data['specializations'] ?? []);

            return $user;
        });

        // Письмо со ссылкой на подтверждение адреса (мягкая верификация). Событие
        // Registered слушает Laravel (`EventServiceProvider`) и отправляет
        // `VerifyEmailNotification`, потому что `User` реализует `MustVerifyEmail`.
        // Сбой почты не должен ломать регистрацию — пишем в лог и продолжаем:
        // работа и синк без подтверждения доступны.
        try {
            event(new Registered($user));
        } catch (Throwable $e) {
            Log::warning('Verification email failed: '.$e->getMessage(), ['user_id' => $user->id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'specializations' => $this->serializeSpecializations(
                Specialization::where('user_id', $user->id)->orderBy('id')->get()
            ),
        ]);
    }

    /**
     * Создаёт рабочие профили пользователя.
     *
     * Если специализации не передали (старый клиент), создаём одну по умолчанию:
     * без профиля приложение не сможет привязать заказы/каталог.
     *
     * @param  array<int, array{name: string, preset_key?: string|null}>  $definitions
     */
    private function createSpecializations(User $user, array $definitions): void
    {
        if (empty($definitions)) {
            $definitions = [[
                'name' => $user->name ?: 'Моя мастерская',
                'preset_key' => null,
            ]];
        }

        foreach ($definitions as $definition) {
            // Свойства присваиваем напрямую: у модели `Specialization` нет
            // `$fillable`, а массовое присваивание было бы отброшено.
            $specialization = new Specialization();
            $specialization->specializationName = $definition['name'];
            $specialization->user_id = $user->id;
            $specialization->preset_key = $definition['preset_key'] ?? null;
            $specialization->archived = false;
            $specialization->popularCounter = 1;
            $specialization->save();
        }
    }

    /**
     * Отдаёт специализации клиенту сразу после регистрации: `name` — алиас для
     * `specializationName`, как в выдаче `/sync-updates`.
     *
     * @param  \Illuminate\Support\Collection<int, Specialization>  $specializations
     * @return array<int, array<string, mixed>>
     */
    private function serializeSpecializations($specializations): array
    {
        return $specializations->map(fn (Specialization $specialization) => [
            'id' => $specialization->id,
            'name' => $specialization->specializationName,
            'specializationName' => $specialization->specializationName,
            'preset_key' => $specialization->preset_key,
            'accent' => $specialization->accent,
            'features' => $specialization->features,
            'archived' => (bool) $specialization->archived,
            'template_version' => $specialization->template_version,
            'user_id' => $specialization->user_id,
            'created_at' => optional($specialization->created_at)->toJSON(),
            'updated_at' => optional($specialization->updated_at)->toJSON(),
        ])->all();
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'авторизация не пройдена'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Вы вышли из системы']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Удаление аккаунта вместе со ВСЕМИ его данными — безвозвратно.
     *
     * Раньше метод только отзывал токены и звал `$user->delete()`, полагаясь на каскады.
     * Для аккаунта с данными это не работает:
     *   • часть внешних ключей объявлена без `onDelete('cascade')` — `orders.user_id` →
     *     `users`, `clients`/`equipment_models` → `specializations`,
     *     `products` → `product_categories`, `incoming_products` → `products`; PostgreSQL
     *     отвечал `23503`, и аккаунт не удалялся;
     *   • `orders`, `products`, `clients`, `services`, `categories`,
     *     `equipment_models`, `order_service` — soft-delete, поэтому `delete()` через
     *     Eloquent оставлял бы строки в таблицах;
     *   • `sync_tombstones.user_id` и `feedback_reports.user_id` — обычные колонки без FK,
     *     их никто бы не тронул, и от удалённого пользователя оставался «хвост».
     *
     * Поэтому чистим явно: `DB::table()` — это hard delete (мимо soft-delete), порядок —
     * «дети → родители», всё в одной транзакции. Скоуп — только данные удаляемого
     * пользователя (профили по `user_id`, дальше по цепочке FK), чужие не трогаем.
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $email = $user->email;

        DB::transaction(function () use ($userId, $email) {
            // Профили пользователя — корень владения каталогом, складом и клиентами.
            $specializationIds = DB::table('specializations')
                ->where('user_id', $userId)
                ->pluck('id')->all();

            $categoryIds = DB::table('categories')
                ->whereIn('specialization_id', $specializationIds)
                ->pluck('id')->all();

            $productCategoryIds = DB::table('product_categories')
                ->whereIn('specialization_id', $specializationIds)
                ->pluck('id')->all();

            $productIds = DB::table('products')
                ->whereIn('product_category_id', $productCategoryIds)
                ->pluck('id')->all();

            $serviceIds = DB::table('services')
                ->whereIn('category_id', $categoryIds)
                ->pluck('id')->all();

            $orderIds = DB::table('orders')
                ->where('user_id', $userId)
                ->orWhereIn('specialization_id', $specializationIds)
                ->pluck('id')->all();

            // 1. Строки заказов (работы, товары, ручные позиции, цены продажи).
            if (!empty($orderIds)) {
                DB::table('materials')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_service')->whereIn('order_id', $orderIds)->delete();
                DB::table('order_product')->whereIn('order_id', $orderIds)->delete();
                DB::table('sales_products_prices')->whereIn('order_id', $orderIds)->delete();
            }

            // 2. Склад: дети товара (`incoming_products.product_id` — без каскада).
            if (!empty($productIds)) {
                DB::table('incoming_products')->whereIn('product_id', $productIds)->delete();
                DB::table('product_stocks')->whereIn('product_id', $productIds)->delete();
                DB::table('buy_product_prices')->whereIn('product_id', $productIds)->delete();
            }

            // 3. Заказы → товары → категории товаров → работы → категории работ.
            if (!empty($orderIds)) {
                DB::table('orders')->whereIn('id', $orderIds)->delete();
            }
            if (!empty($productIds)) {
                DB::table('products')->whereIn('id', $productIds)->delete();
            }
            if (!empty($productCategoryIds)) {
                DB::table('product_categories')->whereIn('id', $productCategoryIds)->delete();
            }
            if (!empty($serviceIds)) {
                DB::table('services')->whereIn('id', $serviceIds)->delete();
            }
            if (!empty($categoryIds)) {
                DB::table('categories')->whereIn('id', $categoryIds)->delete();
            }

            // 4. Клиенты и модели техники (`specialization_id` без каскада).
            if (!empty($specializationIds)) {
                DB::table('clients')->whereIn('specialization_id', $specializationIds)->delete();
                DB::table('equipment_models')->whereIn('specialization_id', $specializationIds)->delete();
            }

            // 5. Профили и «хвосты» без FK, но привязанные к пользователю.
            DB::table('specializations')->where('user_id', $userId)->delete();
            DB::table('sync_tombstones')->where('user_id', $userId)->delete();
            DB::table('feedback_reports')->where('user_id', $userId)->delete();

            // 6. Доступ: токены всех устройств и токены сброса пароля.
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $userId)
                ->delete();

            foreach (['password_reset_tokens', 'password_resets'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('email', $email)->delete();
                }
            }

            DB::table('users')->where('id', $userId)->delete();
        });

        Log::info('Аккаунт удалён вместе с данными', ['user_id' => $userId]);

        return response()->json(['message' => 'Аккаунт удалён']);
    }
}
