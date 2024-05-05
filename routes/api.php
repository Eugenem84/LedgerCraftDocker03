<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecializationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware('auth:api')->group(function (){
    Route::get('/get_all_specializations', [SpecializationController::class, 'getAll']);
});

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


Route::get('/get_categories/{specializationId}', [CategoryController::class, 'getBySpecialization']);
Route::post('/add_category', [CategoryController::class, 'addNew']);
Route::post('/delete_category', [CategoryController::class, 'delete']);
Route::post('/edit_category', [CategoryController::class, 'edit']);


//Route::get('/get_all_orders', [OrderController::class, 'getAll']);
Route::get('/order/{orderId}', [OrderController::class, 'getDetails']);
//Route::post('/save_order', [OrderController::class, 'saveOrder']);
Route::delete('/delete_order/{orderId}', [OrderController::class, 'deleteOrder']);
Route::post('/update_order', [OrderController::class, 'updateOrder']);
Route::get('/get_services/{orderId}', [OrderController::class, 'getServices']);
