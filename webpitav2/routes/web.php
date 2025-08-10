<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BarangController as AdminBarangController;
use App\Http\Controllers\Admin\LogBarangController;
use App\Http\Controllers\Admin\NotaHistoryController;
use App\Http\Controllers\Admin\UserActivityController;
use App\Http\Controllers\Admin\ReturController as AdminReturController;
use App\Http\Controllers\Admin\PelunasanController as AdminPelunasanController;
use App\Http\Controllers\Admin\NotaController as AdminNotaController;
use App\Http\Controllers\Admin\SalesController as AdminSalesController;

use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\NotaController;
use App\Http\Controllers\User\ReturController;
use App\Http\Controllers\User\PelunasanController;
use App\Http\Controllers\User\StokController;
use App\Http\Controllers\User\NotaHistoryController as UserNotaHistoryController;
use App\Http\Controllers\User\SalesController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'authenticate']);
Route::get('/logout', [LogoutController::class, 'logout']);

// ADMIN
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
// Route::resource('/admin/user', AdminUserController::class);
Route::resource('/admin/barang', AdminBarangController::class);



Route::get('/admin/api/barang-search', [AdminBarangController::class, 'barangSearch']);
Route::get('/admin/barang/paket/create', [AdminBarangController::class, 'formPaket']);
Route::post('/admin/barang/paket/store', [AdminBarangController::class, 'storePaket']);
Route::get('/admin/barang/paket/edit/{id}', [AdminBarangController::class, 'editPaket']);
Route::post('/admin/barang/paket/update/{id}', [AdminBarangController::class, 'updatePaket']);



Route::get('/admin/user', [AdminUserController::class, 'index'])->name('admin.user.index');
Route::get('/admin/user/create', [AdminUserController::class, 'create'])->name('admin.user.create');
Route::post('/admin/user/store', [AdminUserController::class, 'store'])->name('admin.user.store');
Route::get('/admin/user/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.user.edit');
Route::post('/admin/user/{id}/update', [AdminUserController::class, 'update'])->name('admin.user.update');
Route::post('/admin/user/{id}/delete', [AdminUserController::class, 'destroy'])->name('admin.user.destroy');





Route::get('/admin/log-barang', [LogBarangController::class, 'index']);
Route::get('/admin/user-activity', [UserActivityController::class, 'index']);


// admin nota 
// Riwayat Nota
Route::get('/admin/history-nota', [NotaHistoryController::class, 'index'])->name('admin.historynota');
Route::post('/admin/history-nota/{id}/cancel', [NotaHistoryController::class, 'cancel'])->name('admin.historynota.cancel');
Route::get('/admin/history-nota/{id}/edit', [NotaHistoryController::class, 'edit'])->name('admin.historynota.edit');
Route::post('/admin/history-nota/{id}/update', [NotaHistoryController::class, 'update'])->name('admin.historynota.update');


// retur n pelunasan
Route::get('/admin/retur', [AdminReturController::class, 'index'])->name('admin.retur');
Route::post('/admin/retur/simpan', [AdminReturController::class, 'submit'])->name('admin.retur.submit');

Route::get('/admin/pelunasan', [AdminPelunasanController::class, 'index'])->name('admin.pelunasan');
Route::post('/admin/pelunasan/simpan', [AdminPelunasanController::class, 'simpan'])->name('admin.pelunasan.simpan');

Route::get('/admin/order', [AdminNotaController::class, 'form']);
Route::post('/admin/order', [AdminNotaController::class, 'submit']);

Route::get('/admin/sales', [AdminSalesController::class, 'index'])->name('admin.sales.index');
Route::get('/admin/sales/create', [AdminSalesController::class, 'create'])->name('admin.sales.create');
Route::post('/admin/sales/store', [AdminSalesController::class, 'store'])->name('admin.sales.store');
Route::post('/admin/sales/{id}/delete', [AdminSalesController::class, 'destroy'])->name('admin.sales.destroy');







// USER
Route::get('/user/dashboard', [UserDashboardController::class, 'index']);
Route::get('/user/historynota', [UserNotaHistoryController::class, 'index']);

Route::get('/user/order', [NotaController::class, 'form']);
Route::post('/user/order', [NotaController::class, 'submit']);
Route::get('/api/barang/{id}', function ($id) {
    $barang = DB::table('barang')->where('id', $id)->first();
    if (!$barang) {
        return response()->json(['error' => 'Barang tidak ditemukan'], 404);
    }
    return response()->json([
        'id' => $barang->id,
        'nama' => $barang->nama,
        'harga' => $barang->harga
    ]);
});
Route::get('/api/order-search', [NotaController::class, 'barangAutocomplete']);



Route::get('/user/retur', [ReturController::class, 'index'])->name('user.retur');
Route::post('/user/retur', [ReturController::class, 'submit'])->name('user.retur.submit');



Route::get('/user/pelunasan', [PelunasanController::class, 'index'])->name('user.pelunasan');
Route::post('/user/pelunasan/simpan', [PelunasanController::class, 'simpan'])->name('user.pelunasan.simpan');



Route::resource('/user/stok', StokController::class);
Route::get('/user/stok/{id}/edit', [StokController::class, 'edit']);
Route::post('/user/stok/{id}/update', [StokController::class, 'update']);
Route::delete('/user/stok/{id}/delete', [StokController::class, 'destroy']);

// Barang
Route::get('/user/barang/create', [StokController::class, 'createBarang'])->name('user.barang.create');
Route::post('/user/barang/store', [StokController::class, 'storeBarang'])->name('user.barang.store');

// Paket
Route::get('/api/barang-search', [StokController::class, 'barangSearch']);
Route::get('/user/paket/create', [StokController::class, 'createPaket'])->name('user.paket.create');
Route::post('/user/paket/store', [StokController::class, 'storePaket'])->name('user.paket.store');

Route::get('/user/sales', [SalesController::class, 'index'])->name('sales.index');
Route::get('/user/sales/create', [SalesController::class, 'create'])->name('sales.create');
Route::post('/user/sales', [SalesController::class, 'store'])->name('sales.store');
