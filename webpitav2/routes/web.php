<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BarangController as AdminBarangController;
use App\Http\Controllers\Admin\LogBarangController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserActivityController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\NotaController;
use App\Http\Controllers\User\ReturController;
use App\Http\Controllers\User\PelunasanController;
use App\Http\Controllers\User\StokController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/login', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'authenticate']);
Route::get('/logout', [LogoutController::class, 'logout']);

// ADMIN
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
Route::resource('/admin/user', AdminUserController::class);
Route::resource('/admin/barang', AdminBarangController::class);

Route::get('/api/barang-search', [AdminBarangController::class, 'barangSearch']);
Route::get('/admin/barang/paket/create', [AdminBarangController::class, 'formPaket']);
Route::post('/admin/barang/paket/store', [AdminBarangController::class, 'storePaket']);
Route::get('/admin/barang/paket/edit/{id}', [AdminBarangController::class, 'editPaket']);
Route::post('/admin/barang/paket/update/{id}', [AdminBarangController::class, 'updatePaket']);



Route::get('/admin/log-barang', [LogBarangController::class, 'index']);
Route::get('/admin/user-activity', [UserActivityController::class, 'index']);

















// USER
Route::get('/user/dashboard', [UserDashboardController::class, 'index']);
Route::resource('/user/nota', NotaController::class);
Route::resource('/user/retur', ReturController::class);
Route::resource('/user/pelunasan', PelunasanController::class);
Route::resource('/user/stok', StokController::class);
