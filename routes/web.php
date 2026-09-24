<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JsmController;
use App\Http\Controllers\LocController;
use App\Http\Controllers\NotificationRecipientController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwpController;
use App\Http\Controllers\RafaksiController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierRafaksiController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\UnifiedDocumentController;
use App\Http\Controllers\UtilityController;
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

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

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

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/upload', [ReportController::class, 'store'])->name('reports.upload');
    Route::get('/reports/export', [ReportController::class, 'exportExcel'])->name('reports.export');
    Route::get('/api/reports/weekly-matrix', [ReportController::class, 'getWeeklyMatrix'])->name('api.reports.matrix');

    Route::get('/jsm/renew', [JsmController::class, 'renewIndex'])->name('jsm.renew.index');
    Route::put('/jsm/renew/{jsm}', [JsmController::class, 'renew'])->name('jsm.renew');
    Route::get('/jsm/print', [JsmController::class, 'printPdf'])->name('jsm.print');
    Route::get('/jsm/rekap/{year}/{month}', [JsmController::class, 'showMonth'])->name('jsm.show_month');
    Route::get('/jsm/export_excel', [JsmController::class, 'exportExcel'])->name('jsm.export.excel');
    Route::get('/jsm/export_csv', [JsmController::class, 'exportCSV'])->name('jsm.export');
    Route::get('/jsm/documents/{document}/download', [JsmController::class, 'downloadDocument'])->name('jsm.documents.download');
    Route::delete('/jsm/documents/{document}', [JsmController::class, 'deleteDocument'])->name('jsm.documents.destroy');
    Route::get('/jsm/{jsm}/status-tidak-aktif', [JsmController::class, 'statusTidakAktif'])->name('jsm.status-tidak-aktif');
    Route::get('/jsm/{jsm}/status-aktif', [JsmController::class, 'statusAktif'])->name('jsm.status-aktif');

    Route::resource('jsm', JsmController::class);

    Route::get('/rafaksi/renew', [RafaksiController::class, 'renewIndex'])->name('rafaksi.renew.index');
    Route::put('/rafaksi/renew/{rafaksi}', [RafaksiController::class, 'renew'])->name('rafaksi.renew');
    Route::get('/rafaksi/print', [RafaksiController::class, 'printPdf'])->name('rafaksi.print');
    Route::get('/rafaksi/rekap/{year}/{month}', [RafaksiController::class, 'showMonth'])->name('rafaksi.show_month');
    Route::get('/rafaksi/export_excel', [RafaksiController::class, 'exportExcel'])->name('rafaksi.export.excel');
    Route::get('/rafaksi/export_csv', [RafaksiController::class, 'exportCSV'])->name('rafaksi.export');
    Route::get('/rafaksi/documents/{document}/download', [RafaksiController::class, 'downloadDocument'])->name('rafaksi.documents.download');
    Route::delete('/rafaksi/documents/{document}', [RafaksiController::class, 'deleteDocument'])->name('rafaksi.documents.destroy');
    Route::get('/rafaksi/{rafaksi}/status-tidak-aktif', [RafaksiController::class, 'statusTidakAktif'])->name('rafaksi.status-tidak-aktif');
    Route::get('/rafaksi/{rafaksi}/status-aktif', [RafaksiController::class, 'statusAktif'])->name('rafaksi.status-aktif');
    Route::resource('rafaksi', RafaksiController::class);

    Route::get('/pwp/renew', [PwpController::class, 'renewIndex'])->name('pwp.renew.index');
    Route::put('/pwp/renew/{pwp}', [PwpController::class, 'renew'])->name('pwp.renew');
    Route::get('/pwp/print', [PwpController::class, 'printPdf'])->name('pwp.print');
    Route::get('/pwp/rekap/{year}/{month}', [PwpController::class, 'showMonth'])->name('pwp.show_month');
    Route::get('/pwp/export_excel', [PwpController::class, 'exportExcel'])->name('pwp.export.excel');
    Route::get('/pwp/export_csv', [PwpController::class, 'exportCSV'])->name('pwp.export');
    Route::get('/pwp/documents/{document}/download', [PwpController::class, 'downloadDocument'])->name('pwp.documents.download');
    Route::delete('/pwp/documents/{document}', [PwpController::class, 'deleteDocument'])->name('pwp.documents.destroy');
    Route::get('/pwp/{pwp}/status-tidak-aktif', [PwpController::class, 'statusTidakAktif'])->name('pwp.status-tidak-aktif');
    Route::get('/pwp/{pwp}/status-aktif', [PwpController::class, 'statusAktif'])->name('pwp.status-aktif');

    Route::resource('pwp', PwpController::class);

    Route::get('/loc/renew', [LocController::class, 'renewIndex'])->name('loc.renew.index');
    Route::put('/loc/renew/{loc}', [LocController::class, 'renew'])->name('loc.renew');
    Route::get('/loc/print', [LocController::class, 'printPdf'])->name('loc.print');
    Route::get('/loc/rekap/{year}/{month}', [LocController::class, 'showMonth'])->name('loc.show_month');
    Route::get('/loc/export_excel', [LocController::class, 'exportExcel'])->name('loc.export.excel');
    Route::get('/loc/export_csv', [LocController::class, 'exportCSV'])->name('loc.export');
    Route::post('/loc/{loc}/approve', [LocController::class, 'approve'])->name('loc.approve');
    Route::get('/loc/{loc}/download', [LocController::class, 'downloadDocument'])->name('loc.download');
    Route::get('/loc/documents/{document}/download', [LocController::class, 'downloadDocumentFile'])->name('loc.documents.download');
    Route::delete('/loc/documents/{document}', [LocController::class, 'deleteDocumentFile'])->name('loc.documents.destroy');
    Route::resource('loc', LocController::class);

    Route::resource('region', RegionController::class);

    Route::get('/get-tokos/{region_id}',[TokoController::class, 'getByRegion']);
    Route::get('/get-tokos-all',[TokoController::class, 'getAll']);
    Route::get('/next-no-raf',[UtilityController::class, 'nextNoRaf']);
    Route::get('/create-document', [UnifiedDocumentController::class, 'create'])->name('create.document');
    Route::post('/create-document/import-preview', [UnifiedDocumentController::class, 'importPreview'])->name('create.document.import');
    
    Route::resource('toko', TokoController::class);

    Route::resource('supplier_rafaksi', SupplierRafaksiController::class);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('notification-recipients', NotificationRecipientController::class);

    // Rute untuk menampilkan form centang toko sebelum print
    Route::get('/document/{type}/{id}/select-stores', [UtilityController::class, 'selectStoresForPrint'])->name('document.select-stores');

    // Rute proses cetak sesungguhnya (menerima parameter toko_ids[] dari form di atas)
    Route::get('/document/{type}/{id}/print', [UtilityController::class, 'printDocument'])->name('document.print');
    Route::get('/document/{type}/{id}/print-attachments', [UtilityController::class, 'printAttachments'])->name('document.print-attachments');
});

require __DIR__.'/auth.php';
