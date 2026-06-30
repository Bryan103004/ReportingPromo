<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('permission', PermissionController::class);

    Route::resource('role', RoleController::class);

    Route::resource('user', UserController::class);

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::delete('/activity-log/destroy', [ActivityLogController::class, 'destroy'])->name('activity-log.destroy');
    
    // Route::get('/user', [UserController::class, 'index'])->name('user.index')->middleware('permission:view_users');
    // Route::get('/user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:create_users');
    // Route::post('/user', [UserController::class, 'store'])->name('user.store')->middleware('permission:create_users');
    // Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:edit_users');
    // Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:edit_users');
    // Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy')->middleware('permission:delete_users');


});

use App\Http\Controllers\ReportController;

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('/reports/upload', [ReportController::class, 'store'])->name('reports.upload');
Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
Route::get('/api/reports/weekly-matrix', [App\Http\Controllers\ReportController::class, 'getWeeklyMatrix'])->name('api.reports.matrix');

require __DIR__.'/auth.php';
