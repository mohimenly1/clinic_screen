<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScreenController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\BroadcastController;

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

// Public API Routes (No authentication required for display screens)
Route::prefix('v1')->group(function () {
    // Get screen data by code
    Route::get('/screens/{code}', [ScreenController::class, 'show'])->name('api.screens.show');
    
    // Get departments with doctors and schedules
    Route::get('/departments', [DepartmentController::class, 'index'])->name('api.departments.index');
    Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('api.departments.show');
    
    // Get media item download URL
    Route::get('/media/{id}', [MediaController::class, 'show'])->name('api.media.show');
    
    // Get broadcast status
    Route::get('/broadcast/status', [BroadcastController::class, 'status'])->name('api.broadcast.status');
});

