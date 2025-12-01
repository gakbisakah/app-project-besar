<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\App\Home\HomeController;
use App\Http\Controllers\App\HakAkses\HakAksesController;
use App\Http\Controllers\App\Todo\TodoController;
use App\Http\Controllers\App\RegisSemi\RegisSemiController;
use App\Http\Controllers\App\Penghargaan\PenghargaanBukuController;
use Inertia\Inertia;

Route::middleware(['throttle:req-limit', 'handle.inertia'])->group(function () {

    // ------------------- SSO -------------------
    Route::prefix('sso')->group(function () {
        Route::get('/callback', [AuthController::class, 'ssoCallback'])->name('sso.callback');
    });

    // ------------------- AUTH -------------------
    Route::prefix('auth')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('/login-check', [AuthController::class, 'postLoginCheck'])->name('auth.login-check');
        Route::post('/login-post', [AuthController::class, 'postLogin'])->name('auth.login-post');
        Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/totp', [AuthController::class, 'totp'])->name('auth.totp');
        Route::post('/totp-post', [AuthController::class, 'postTotp'])->name('auth.totp-post');
    });

    // ------------------- PROTECTED -------------------
    Route::middleware('check.auth')->group(function () {

        Route::get('/', [HomeController::class, 'index'])->name('home');

        // DOSEN Dashboard
        Route::get('/dosen/home', function () {
            return inertia('app/dosen/dosen-home-page', ['pageName' => 'Dashboard Dosen']);
        })->name('dosen.home');

        // HAK AKSES
        Route::prefix('hak-akses')->group(function () {
            Route::get('/', [HakAksesController::class, 'index'])->name('hak-akses');
            Route::post('/change', [HakAksesController::class, 'postChange'])->name('hak-akses.change-post');
            Route::post('/delete', [HakAksesController::class, 'postDelete'])->name('hak-akses.delete-post');
            Route::post('/delete-selected', [HakAksesController::class, 'postDeleteSelected'])->name('hak-akses.delete-selected-post');
        });

        // TODO
        Route::prefix('todo')->group(function () {
            Route::get('/', [TodoController::class, 'index'])->name('todo');
            Route::post('/change', [TodoController::class, 'postChange'])->name('todo.change-post');
            Route::post('/delete', [TodoController::class, 'postDelete'])->name('todo.delete-post');
        });

        // REGIS SEMI
        Route::middleware('role:LppmKetua|Lppm Staff')->prefix('regis-semi')->name('regis-semi.')->group(function () {
            Route::get('/', [RegisSemiController::class, 'index'])->name('index');
            Route::get('/indexx', [RegisSemiController::class, 'indexx'])->name('indexx'); // Lppm Staff
            Route::post('/change', [RegisSemiController::class, 'postChange'])->name('change');
            Route::post('/delete', [RegisSemiController::class, 'postDelete'])->name('delete');
            Route::post('/delete-selected', [RegisSemiController::class, 'postDeleteSelected'])->name('delete-selected');
            Route::get('/{id}/detail', [RegisSemiController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [RegisSemiController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [RegisSemiController::class, 'reject'])->name('reject');
            Route::get('/{id}/result', [RegisSemiController::class, 'result'])->name('result');
            Route::get('/{id}/invite', [RegisSemiController::class, 'invite'])->name('invite');
            Route::post('/{id}/invite', [RegisSemiController::class, 'storeInvite'])->name('store-invite');
            Route::get('/{id}/link-google-drive', [RegisSemiController::class, 'showInvite'])->name('link-google-drive');
        });

        // PENGHARGAAN DOSEN
        Route::prefix('penghargaan')->middleware('role:Dosen')->group(function () {
            Route::get('/buku', [PenghargaanBukuController::class, 'index'])->name('app.penghargaan.buku.index');
            Route::get('/buku/ajukan', [PenghargaanBukuController::class, 'create'])->name('app.penghargaan.buku.create');
            Route::post('/buku', [PenghargaanBukuController::class, 'store'])->name('app.penghargaan.buku.store');
            Route::get('/buku/upload/{id}', [PenghargaanBukuController::class, 'uploadDocs'])->name('app.penghargaan.buku.upload');
            Route::post('/buku/upload/{id}', [PenghargaanBukuController::class, 'storeUpload'])->name('app.penghargaan.buku.store-upload');
            Route::get('/buku/{id}', [PenghargaanBukuController::class, 'show'])->name('app.penghargaan.buku.detail');
        });

        Route::get('/regis-semi/{id}/detail-l', [RegisSemiController::class, 'showL'])->name('regis-semi.showl');


        // PENGHARGAAN MAHASISWA
        Route::prefix('penghargaan')->middleware('role:Mahasiswa')->group(function () {
            Route::get('/mahasiswa', function () {
                return 'Penghargaan Mahasiswa UI (Dummy)';
            })->name('penghargaan.mahasiswa');
        });

        // Notifikasi dummy
        Route::get('/notifikasi-dummy', function () { 
            return Inertia::render('app/notifikasi/page'); 
        })->name('notifications.index');
    });
});
