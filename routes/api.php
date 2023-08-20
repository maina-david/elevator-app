<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ElevatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'registerUser');
    Route::post('/login', 'authenticateUser');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(ElevatorController::class)->group(function () {
        Route::post('/create-building', 'createBuildingWithElevators');
        Route::get('/list-buildings', 'listBuildingsWithElevators')->withoutMiddleware('auth:sanctum');
        Route::post('/{building}/create-elevator', 'createElevator');
        Route::post('/call-elevator/{elevator}', 'callElevator');
    });
});