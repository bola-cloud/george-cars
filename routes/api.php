<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;

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

// Public device store route (no authentication required)
Route::post('/devices', [DeviceController::class, 'store']);
// Public endpoint to generate a unique 14-char alphanumeric serial
Route::get('/devices/generate-serial', [DeviceController::class, 'generateSerial']);

// Protected Routes (Require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    // Shares (parent -> child user mapping)
    Route::get('shares', [ShareController::class, 'index']);
    Route::post('shares', [ShareController::class, 'store']);
    Route::delete('shares/{id}', [ShareController::class, 'destroy']);
    Route::patch('shares/{id}', [ShareController::class, 'update']);

    // Update authenticated user
    Route::match(['put', 'patch'], '/user', [AuthController::class, 'update']);
    // Update a device (owned by authenticated user)
    Route::match(['put','patch'], '/devices/{id}', [DeviceController::class, 'update']);
    // Delete a device (only if it belongs to the authenticated user)
    Route::delete('/devices/{id}', [DeviceController::class, 'destroy']);
    // Notify users about device status change (owner + shared users)
    Route::post('/devices/{id}/notify', [DeviceController::class, 'notifyChange']);

    // Logout route
    Route::post('logout', [AuthController::class, 'logout']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Users
        Route::get('users', [AdminUserController::class, 'index']);
        Route::post('users', [AdminUserController::class, 'store']);
        Route::get('users/{id}', [AdminUserController::class, 'show']);
        Route::match(['put','patch'], 'users/{id}', [AdminUserController::class, 'update']);
        Route::delete('users/{id}', [AdminUserController::class, 'destroy']);

        // Devices
        Route::get('devices', [AdminDeviceController::class, 'index']);
        Route::post('devices', [AdminDeviceController::class, 'store']);
        Route::get('devices/{id}', [AdminDeviceController::class, 'show']);
        Route::match(['put','patch'], 'devices/{id}', [AdminDeviceController::class, 'update']);
        Route::delete('devices/{id}', [AdminDeviceController::class, 'destroy']);
    });

    // Public API to get broker_ip
});
    Route::get('settings/broker-ip', [\App\Http\Controllers\Api\SettingController::class, 'brokerIp']);
