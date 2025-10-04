<?php

// 1. استيراد الـ Controllers
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\MediaItemController;
use App\Http\Controllers\Admin\PlaylistController;
use App\Http\Controllers\Admin\ScreenController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\BroadcastController;
use App\Events\PusherTestEvent;

Route::get('/test-broadcast', function () {
    PusherTestEvent::dispatch();
    return 'Test event has been dispatched!';
});
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// مسار عرض الشاشة العام (يجب أن يكون خارج المصادقة)
Route::get('/display/{code}', [DisplayController::class, 'show'])->name('display.show');


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// مسارات لوحة التحكم (محمية وتتطلب تسجيل الدخول)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD Routes لكل قسم
    Route::resource('screens', ScreenController::class);
    Route::resource('media-items', MediaItemController::class);
    Route::resource('playlists', PlaylistController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('doctors', DoctorController::class);

    Route::post('doctors/{doctor}/schedules', [DoctorController::class, 'storeSchedule'])->name('doctors.schedules.store');
    Route::delete('schedules/{schedule}', [DoctorController::class, 'destroySchedule'])->name('schedules.destroy');

        // **جديد**: مسارات البث العام
        Route::get('broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
        Route::delete('broadcast', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
