<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\JsmController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RafaksiController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierRafaksiController;
use App\Http\Controllers\TokoController;
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



Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('/reports/upload', [ReportController::class, 'store'])->name('reports.upload');
Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
Route::get('/api/reports/weekly-matrix', [ReportController::class, 'getWeeklyMatrix'])->name('api.reports.matrix');

Route::get('/jsm/print', [JsmController::class, 'printPdf'])->name('jsm.print');
Route::get('/jsm/{year}/{month}', [JsmController::class, 'showMonth'])->name('jsm.show_month');
Route::get('/jsm/export_excel', [JsmController::class, 'exportExcel'])->name('jsm.export.excel');
Route::get('/jsm/export_csv', [JsmController::class, 'exportCSV'])->name('jsm.export');

Route::resource('jsm', JsmController::class);

Route::get('/rafaksi/print', [JsmController::class, 'printPdf'])->name('rafaksi.print');
Route::get('/rafaksi/{year}/{month}', [RafaksiController::class, 'showMonth'])->name('rafaksi.show_month');
Route::get('/rafaksi/export_excel', [RafaksiController::class, 'exportExcel'])->name('rafaksi.export.excel');
Route::get('/rafaksi/export_csv', [RafaksiController::class, 'exportCSV'])->name('rafaksi.export');
Route::resource('rafaksi', RafaksiController::class);

Route::resource('region', RegionController::class);

Route::get('/get-tokos/{region_id}',[TokoController::class, 'getByRegion']);
Route::resource('toko', TokoController::class);

Route::resource('supplier_rafaksi', SupplierRafaksiController::class);

require __DIR__.'/auth.php';
