<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'specializations' => 'sometimes|array|max:10',
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

    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Удалим все токены
        $user->delete();

        return response()->json(['message' => 'Аккаунт удалён']);
    }
}
