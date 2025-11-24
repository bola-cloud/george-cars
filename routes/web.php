<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Admin\WebUserController;
use App\Http\Controllers\Admin\WebDeviceController;

/*
|--------------------------------------------------------------------------
| Web Admin Routes
|--------------------------------------------------------------------------
|
| Admin panel routes protected by auth and admin middleware.
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Users
    Route::get('users', [WebUserController::class, 'index'])->name('users.index');
    Route::get('users/create', [WebUserController::class, 'create'])->name('users.create');
    Route::post('users', [WebUserController::class, 'store'])->name('users.store');
    Route::get('users/{id}/edit', [WebUserController::class, 'edit'])->name('users.edit');
    Route::match(['put','patch'], 'users/{id}', [WebUserController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [WebUserController::class, 'destroy'])->name('users.destroy');

    // Devices
    Route::get('devices', [WebDeviceController::class, 'index'])->name('devices.index');
    Route::get('devices/create', [WebDeviceController::class, 'create'])->name('devices.create');
    Route::post('devices', [WebDeviceController::class, 'store'])->name('devices.store');
    Route::get('devices/{id}/edit', [WebDeviceController::class, 'edit'])->name('devices.edit');
    Route::match(['put','patch'], 'devices/{id}', [WebDeviceController::class, 'update'])->name('devices.update');
    Route::delete('devices/{id}', [WebDeviceController::class, 'destroy'])->name('devices.destroy');
});


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
// Route::group([
//     'prefix' => LaravelLocalization::setLocale(), // Set the language prefix correctly
//     'middleware' => [
//         'auth:sanctum',
//         config('jetstream.auth_session'),
//         'verified',
//     ]
// ], function () {
// });
    Route::get('/', [\App\Http\Controllers\Admin\Dashboard::class, 'index'])->name('dashboard');
