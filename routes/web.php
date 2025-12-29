<?php

// 1. استيراد الـ Controllers
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\MediaItemController;
use App\Http\Controllers\Admin\NavigationPathController;
use App\Http\Controllers\Admin\PlaylistController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomImageController;
use App\Http\Controllers\Admin\ScreenController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
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
    return Inertia::render('Dashboard', [
        'stats' => [
            'screens' => \App\Models\Screen::count(),
            'media_items' => \App\Models\MediaItem::count(),
            'playlists' => \App\Models\Playlist::count(),
            'doctors' => \App\Models\Doctor::count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// مسارات لوحة التحكم (محمية وتتطلب تسجيل الدخول)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // CRUD Routes لكل قسم
    Route::resource('screens', ScreenController::class);
    Route::resource('media-items', MediaItemController::class);
    Route::post('media-items/scan', [MediaItemController::class, 'scan'])->name('media-items.scan');
    Route::resource('playlists', PlaylistController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('doctors', DoctorController::class);
    Route::resource('floors', FloorController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('navigation-paths', NavigationPathController::class);
    
    // Room Images Management
    Route::get('rooms/{room}/images', [RoomImageController::class, 'index'])->name('rooms.images.index');
    Route::get('rooms/{room}/images/create', [RoomImageController::class, 'create'])->name('rooms.images.create');
    Route::post('rooms/{room}/images', [RoomImageController::class, 'store'])->name('rooms.images.store');
    Route::get('rooms/{room}/images/{image}/edit', [RoomImageController::class, 'edit'])->name('rooms.images.edit');
    Route::put('rooms/{room}/images/{image}', [RoomImageController::class, 'update'])->name('rooms.images.update');
    Route::delete('rooms/{room}/images/{image}', [RoomImageController::class, 'destroy'])->name('rooms.images.destroy');
    Route::post('rooms/{room}/images/reorder', [RoomImageController::class, 'reorder'])->name('rooms.images.reorder');

    Route::post('doctors/{doctor}/schedules', [DoctorController::class, 'storeSchedule'])->name('doctors.schedules.store');
    Route::delete('schedules/{schedule}', [DoctorController::class, 'destroySchedule'])->name('schedules.destroy');

        // **جديد**: مسارات البث العام
        Route::get('broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
        Route::post('broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
        Route::delete('broadcast', [BroadcastController::class, 'destroy'])->name('broadcast.destroy');

        // **جديد**: مسارات إدارة المستخدمين والأدوار
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
