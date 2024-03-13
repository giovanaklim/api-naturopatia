<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::prefix('v1')->group(function () {
    Route::post('login', [\App\Http\Controllers\LoginController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\LoginController::class, 'logout']);
    Route::resource('user', \App\Http\Controllers\UsersController::class);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/update-user', [\App\Http\Controllers\UsersController::class, 'updateUser']);
        Route::get('/is-auth', [\App\Http\Controllers\LoginController::class, 'isAuth']);
        Route::resource('/new-plant', \App\Http\Controllers\PlantController::class);
        Route::resource('/new-company', \App\Http\Controllers\CompanyController::class);
        Route::get('/get-plants', [\App\Http\Controllers\PlantController::class, 'getPlants']);
        Route::put('/plant/{id}/like', [\App\Http\Controllers\PlantController::class, 'updatePlantLike']);
        Route::get('/get-company', [\App\Http\Controllers\CompanyController::class,'getCompany']);
    });
});
