<?php

use App\Http\Controllers\StatisticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\EquipmentModelController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AppVersionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\SpecializationTemplateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::post('/login', function (Request $request) {
//   $credentials = $request->only('email', 'password');
//   if (!Auth::attempt($credentials)){
//       return response()->json(['message' => 'авторизация не пройдена'], 401);
//   }
//   $user = Auth::user();
//   $token = $user->createToken('auth_token')->plainTextToken;
//   return response()->json([
//      'access_token' => $token,
//      'token_type' => 'Bearer',
//       'user' => $user,
//   ]);
//});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);


// Задача 7.6: группа `auth:api` удалена. Guard `api` — token-драйвер, а у `users` нет
// колонки `api_token`, поэтому маршрут был недостижим (всегда 401). Web-часть
// (`routes/web.php`) пользуется своим `/get_all_specializations`, а клиенту нужен
// sanctum-вариант ниже (`/get_specializations_by_user`).

//Old version
//Route::get('/getSpecialization', [ServiceController::class, 'getSpecializations']);


Route::get('/get_service/{categoryId}', [ServiceController::class, 'getByCategory']);
Route::post('/add_service', [ServiceController::class, 'addNew']);
Route::post('/delete_service', [ServiceController::class, 'delete']);
Route::post('/edit_service', [ServiceController::class, 'edit']);


//Route::post('/add_specialization', [SpecializationController::class, 'addNew']);
Route::post('/delete_specialization', [SpecializationController::class, 'delete']);
Route::post('/edit_specialization', [SpecializationController::class, 'edit']);
//Route::get('/get_all_specializations', [SpecializationController::class, 'getAll']);


Route::get('/get_all_clients', [ClientController::class, 'getAll']);
Route::get('/get_clients/{specializationId}', [ClientController::class, 'getBySpecialization']);
Route::post('/add_client', [ClientController::class, 'addNew']);
Route::post('/edit_client', [ClientController::class, 'edit']);
Route::post('/delete_client', [ClientController::class, 'delete']);

Route::get('get_equipment_models/{specializationId}', [EquipmentModelController::class, 'getBySpecialization']);
Route::post('/add_equipment_model', [EquipmentModelController::class, 'addNew']);
Route::post('/delete_equipment_model', [EquipmentModelController::class, 'delete']);
Route::post('/edit_equipment_model', [EquipmentModelController::class, 'edit']);

Route::get('/get_categories/{specializationId}', [CategoryController::class, 'getBySpecialization']);
Route::post('/add_category', [CategoryController::class, 'addNew']);
Route::post('/delete_category', [CategoryController::class, 'delete']);
Route::post('/edit_category', [CategoryController::class, 'edit']);

Route::get('/get_product_categories/{specializationId}', [ProductCategoryController::class, 'getBySpecialization']);
Route::post('/add_product_category', [ProductCategoryController::class, 'addNew']);
Route::post('/delete_product_category', [ProductCategoryController::class, 'delete']);
Route::post('/edit_product_category', [ProductCategoryController::class, 'edit']);


Route::get('/get_product_stocks/{productCategoryId}', [ProductStockController::class, 'getByProductCategory']);
Route::get('/get_products/{productCategoryId}', [ProductController::class, 'getByProductCategory']);
Route::post('/add_product', [ProductController::class, 'addNew']);
Route::post('/delete_store_product', [ProductController::class, 'delete']);
Route::post('/edit_product', [ProductController::class, 'edit']);
// Приход товара (задача 11.7, бывший O-6). Было: маршрут без `auth` — приходовать
// чужой товар мог кто угодно. Стало: `auth:sanctum` — без токена/сессии `401`, а
// владелец товара проверяется в контроллере (`FORBIDDEN_NOT_OWNER`).
// `EnsureFrontendRequestsAreStateful` сохраняет рабочей web-версию (`resources/js`,
// вход по сессии + CSRF из meta-тега), а bearer-токену (мобильное приложение)
// сессия не нужна — для него middleware no-op.
Route::middleware([
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',
])->post('/arrival_product', [ProductController::class, 'arrival']);

Route::get('/orders_by_specialization/{id}', [OrderController::class, 'getBySpecialization']);

//Route::get('/get_all_orders', [OrderController::class, 'getAll']);
Route::get('/order/{orderId}', [OrderController::class, 'getDetails']);
//Route::post('/save_order', [OrderController::class, 'saveOrder']);
Route::delete('/delete_order/{orderId}', [OrderController::class, 'deleteOrder']);
Route::post('/update_order', [OrderController::class, 'updateOrder']);
Route::get('/get_services/{orderId}', [OrderController::class, 'getServices']);
Route::put('update_order_status/{id}', [OrderController::class, 'updateStatus']);
// Задача 7.6: одна ручка смены оплаты. `switch_paid_status` дублировал её
// (переключал статус на сервере) — удалён вместе с методом контроллера и
// репозитория, а web-компонент `HistoryOrders.vue` переведён на `update_paid_status`
// с `{paid}` (он и так умел переключать статус на клиенте).
Route::put('update_paid_status/{id}', [OrderController::class, 'updatePadeStatus']);

// Публичная share-ссылка на отчёт (задача 9.4): ссылку выдаёт сервер, но только
// владельцу заказа — маршрут под `auth:sanctum`. Без авторизации перебором id
// можно было сгенерировать ссылку на чужой отчёт (IDOR); проверка владельца — в контроллере.
Route::post('/order-report/{order}/share-link', [OrderController::class, 'generateShareLink'])
    ->middleware('auth:sanctum');


Route::get('/get_materials_by_order/{orderId}', [MaterialController::class, 'getMaterialsByOrder']);

Route::get('/get_total_DWYM/{specializationId}', [StatisticController::class, 'getTotalDWMY']);
Route::get('/get_top_services/{specializationId}', [StatisticController::class, 'getTopServicesBySpecialization']);
Route::get('/get_top_profit_clients/{specializationId}', [StatisticController::class, 'getTopProfitClients']);
// Зеркала клиентской страницы аналитики (задача 9.1): топы товаров/материалов и
// распределение заказов по статусам — чтобы цифры на клиенте и сервере сходились.
Route::get('/get_top_products/{specializationId}', [StatisticController::class, 'getTopProductsBySpecialization']);
Route::get('/get_top_materials/{specializationId}', [StatisticController::class, 'getTopMaterialsBySpecialization']);
Route::get('/get_orders_status/{specializationId}', [StatisticController::class, 'getStatusDistribution']);
Route::get('/income_by_year/{specializationId}', [StatisticController::class, 'getIncomeByYear']);
Route::post('/incomes_by_period/{specializationId}', [StatisticController::class, 'getStatsByPeriod']);
//Route::post('/incomes_by_day/{specializationId}', [StatisticController::class, 'getIncomesByDay']);


// IDOR (задача 3.10): `/get_orders_by_user/{id}` отдавал заказы ЛЮБОГО пользователя,
// а публичный `/get_orders_by_user` падал в 500 на `Auth::user()`. Оставляем один
// маршрут — под `auth:sanctum` и без параметра id (пользователь берётся из токена).
Route::middleware('auth:sanctum')->get('/get_orders_by_user', [OrderController::class, 'getByUser']);
Route::middleware('auth:sanctum')->get('/get_specializations_by_user', [SpecializationController::class, 'getAll']);
Route::middleware('auth:sanctum')->post('/save_order', [OrderController::class, 'saveOrder']);

Route::get('/app-quasar-android-version', [AppVersionController::class, 'checkQuasarAndroidVersion']);

Route::get('/hcp/chcp.json', [AppVersionController::class, 'getChcpManifest']);
Route::get('/hcp/chcp.manifest', [AppVersionController::class, 'getChcpManifest']);

Route::get('/download-apk', [AppVersionController::class, 'downloadApk']);

// Синк — под `auth:sanctum` (задача 3.10): сервер знает владельца данных,
// проставляет `user_id` при вставке и отдаёт/меняет только записи этого пользователя.
// Без токена (401) синхронизация не работает — клиентский вход/токен — задача 7.4.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sync', [SyncController::class, 'sync']);
    Route::get('/sync-updates', [SyncController::class, 'fetchUpdates']);

    // Пресеты специализаций (Фаза 10, задача 10.7): клиент забирает контент и
    // держит read-only кэш, поэтому новый пресет приезжает без релиза приложения.
    Route::get('/specialization-templates', [SpecializationTemplateController::class, 'index']);
});
