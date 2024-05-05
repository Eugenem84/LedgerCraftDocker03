<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/statistic', [HomeController::class, 'showStatistic']);
Route::get('/order', [HomeController::class, 'showOrderMake']);
Route::get('/catalog', [HomeController::class, 'showCatalog']);
Route::get('/history', [HomeController::class, 'showHistory']);


Route::get('/get_all_specializations', [SpecializationController::class, 'getAll']);
Route::post('/add_specialization', [SpecializationController::class, 'addNew']);

Route::get('/get_all_orders', [OrderController::class, 'getAll']);
Route::get('/get_orders_by_user', [OrderController::class, 'getByUser']);
Route::post('/save_order', [OrderController::class, 'saveOrder']);
