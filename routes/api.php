<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\Auth\RegisterController;
use App\Http\Controllers\API\v1\Auth\LoginController;
use App\Http\Controllers\API\v1\Home\HomeController;
use App\Http\Controllers\API\v1\TrashManagement\TrashBankController;
use App\Http\Controllers\API\v1\TrashManagement\TrashController;
use App\Http\Controllers\API\v1\Auth\LogoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::resource('login', LoginController::class);
    Route::resource('registration', RegisterController::class);
});

Route::group(['prefix' => 'home', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/', [HomeController::class, 'index']);
});

Route::group(['prefix' => 'bank-sampah', 'middleware' => ['auth:sanctum']], function () {
    Route::get('list', [TrashBankController::class, 'getBankSampah']);
    Route::post('choose', [TrashBankController::class, 'chooseBankSampah']);
});

Route::group(['prefix' => 'trash', 'middleware' => ['auth:sanctum']], function () {
    Route::post('store', [TrashController::class, 'storeTrash']);
    Route::get('list', [TrashController::class, 'list']);
    Route::get('category', [TrashController::class, 'getCategories']);
    Route::post('/weight/update', [TrashController::class, 'updateWeight']);
});

Route::group(['prefix' => 'iot'], function () {
    Route::post('store', [TrashController::class, 'storeIOT']);
});

Route::group(['prefix' => 'iot', 'middleware' => ['auth:sanctum']], function () {
    Route::post('connect', [TrashController::class, 'connectIOT']);
});

Route::group(['prefix' => 'auth', 'middleware' => ['auth:sanctum']], function() {
    Route::get('logout', [LogoutController::class, 'logout']);
});
