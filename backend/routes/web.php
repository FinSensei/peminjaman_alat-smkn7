<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PeminjamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotifikasiController;

Route::get('/', function () {
    return redirect()->route('login');
});

// ======================= ADMIN =======================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // CRUD User
    Route::get('/users', [AdminController::class, 'indexUser'])->name('user.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('user.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('user.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('user.destroy');

    // CRUD Kategori
    Route::get('/kategori', [AdminController::class, 'indexKategori'])->name('kategori.index');
    Route::get('/kategori/create', [AdminController::class, 'createKategori'])->name('kategori.create');
    Route::post('/kategori', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::put('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'destroyKategori'])->name('kategori.destroy');

    // CRUD Alat
    Route::get('/alat', [AdminController::class, 'indexAlat'])->name('alat.index');
    Route::get('/alat/create', [AdminController::class, 'createAlat'])->name('alat.create');
    Route::post('/alat', [AdminController::class, 'storeAlat'])->name('alat.store');
    Route::get('/alat/{id}/edit', [AdminController::class, 'editAlat'])->name('alat.edit');
    Route::put('/alat/{id}', [AdminController::class, 'updateAlat'])->name('alat.update');
    Route::delete('/alat/{id}', [AdminController::class, 'destroyAlat'])->name('alat.destroy');

    //CRUD Peminjaman
    Route::get('/peminjaman', [AdminController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [AdminController::class, 'createPeminjaman'])->name('peminjaman.create');
    Route::post('/peminjaman', [AdminController::class, 'storePeminjaman'])->name('peminjaman.store');
    Route::put('/peminjaman/{id}/status', [AdminController::class, 'updateStatusPeminjaman'])->name('peminjaman.updateStatus');
    Route::delete('/peminjaman/{id}', [AdminController::class, 'destroyPeminjaman'])->name('peminjaman.destroy');

    // CRUD Pengembalian
    Route::get('/pengembalian', [AdminController::class, 'indexPengembalian'])
        ->name('pengembalian.index');

    Route::get('/pengembalian/{id}/edit', [AdminController::class, 'editPengembalian'])
        ->name('pengembalian.edit');

    Route::put('/pengembalian/{id}', [AdminController::class, 'updatePengembalian'])
        ->name('pengembalian.update');

    Route::get('/pengembalian/{id}', [AdminController::class, 'createPengembalian'])
        ->name('pengembalian.create');

    Route::post('/pengembalian/{id}', [AdminController::class, 'storePengembalian'])
        ->name('pengembalian.store');

    // ReqEdit persetujuan (TAMBAHAN)
    Route::post('/pengembalian/{id}/reqedit/approve', [AdminController::class, 'approvePerbaikan'])
        ->name('pengembalian.approve');
    Route::post('/pengembalian/{id}/reqedit/reject', [AdminController::class, 'rejectPerbaikan'])
        ->name('pengembalian.reject');
});

// ======================= PETUGAS =======================
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('dashboard');
    // Peminjaman & Persetujuan
    Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])
    ->name('peminjaman.tolak');

    // Pengembalian & Denda
    Route::post('/pengembalian/{id}', [PetugasController::class, 'prosesPengembalian'])
            ->name('pengembalian.proses');

    // ReqEdit - Ajukan perbaikan ke Admin (TAMBAHAN)
    Route::post('/pengembalian/{id}/reqedit', [PetugasController::class, 'ajukanPerbaikan'])
            ->name('pengembalian.reqedit');

    Route::get('/pengembalian', [PetugasController::class, 'indexPengembalian'])
            ->name('pengembalian.index');

    //laporan
    Route::get('/laporan', [PetugasController::class, 'indexLaporan'])->name('laporan.index');
    Route::get('/laporan/pdf', [PetugasController::class, 'cetakLaporanPdf'])
    ->name('laporan.pdf');
    Route::get('/laporan/excel', [PetugasController::class, 'cetakLaporanExcel'])
    ->name('laporan.excel');
});

// ======================= PEMINJAM =======================
Route::middleware(['auth', 'role:peminjam'])->prefix('peminjam')->name('peminjam.')->group(function () {
    // Katalog & Pengajuan
    Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
    Route::post('/peminjaman/ajukan', [PeminjamController::class, 'ajukanPeminjaman'])->name('peminjaman.ajukan');
    Route::get('/riwayat', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');
    Route::delete('/peminjaman/{id}', [PeminjamController::class, 'batalkanPeminjaman'])->name('peminjaman.batal');
});

// ======================= AUTH =======================
// Route Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Route Logout (Harus sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ======================= NOTIFIKASI (semua role login) =======================
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi/{id}/buka', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
});
