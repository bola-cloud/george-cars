<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;

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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected Routes (Require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    // Update authenticated user
    Route::match(['put', 'patch'], '/user', [AuthController::class, 'update']);

    // Create a device for authenticated user
    Route::post('/devices', [DeviceController::class, 'store']);
    // Update a device (owned by authenticated user)
    Route::match(['put','patch'], '/devices/{id}', [DeviceController::class, 'update']);

    // Logout route
    Route::post('logout', [AuthController::class, 'logout']);
});
