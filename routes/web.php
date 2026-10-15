<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('mhs.dashboard');
    })->name('home');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // ================= ADMIN / DOSEN =================
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Mahasiswa
        Route::get('mahasiswa/template', [Admin\MahasiswaController::class, 'template'])->name('mahasiswa.template');
        Route::get('mahasiswa/export', [Admin\MahasiswaController::class, 'export'])->name('mahasiswa.export');
        Route::get('mahasiswa/import', fn () => redirect()->route('admin.mahasiswa.index'));
        Route::post('mahasiswa/import', [Admin\MahasiswaController::class, 'import'])->name('mahasiswa.import');
        Route::delete('mahasiswa/bulk', [Admin\MahasiswaController::class, 'bulkDestroy'])->name('mahasiswa.bulk-destroy');
        Route::resource('mahasiswa', Admin\MahasiswaController::class)->except(['show', 'create'])
            ->parameters(['mahasiswa' => 'mahasiswa']);

        // Kelas
        Route::delete('kelas/bulk', [Admin\KelasController::class, 'bulkDestroy'])->name('kelas.bulk-destroy');
        Route::resource('kelas', Admin\KelasController::class)->except(['show', 'create', 'edit'])
            ->parameters(['kelas' => 'kelas']);

        // Mata kuliah
        Route::resource('matakuliah', Admin\MataKuliahController::class)->except(['show', 'create', 'edit'])
            ->parameters(['matakuliah' => 'matakuliah']);

        // Bank soal
        Route::get('soal/template/{tipe}', [Admin\SoalController::class, 'template'])->name('soal.template');
        Route::get('soal/export', [Admin\SoalController::class, 'export'])->name('soal.export');
        Route::get('soal/import', fn () => redirect()->route('admin.soal.index'));
        Route::post('soal/import', [Admin\SoalController::class, 'import'])->name('soal.import');
        Route::delete('soal/bulk', [Admin\SoalController::class, 'bulkDestroy'])->name('soal.bulk-destroy');
        Route::resource('soal', Admin\SoalController::class)->except(['show']);

        // Ujian
        Route::patch('ujian/{ujian}/toggle', [Admin\UjianController::class, 'toggle'])->name('ujian.toggle');
        Route::get('ujian/{ujian}/soal', [Admin\UjianController::class, 'soal'])->name('ujian.soal');
        Route::post('ujian/{ujian}/soal', [Admin\UjianController::class, 'attachSoal'])->name('ujian.soal.attach');
        Route::post('ujian/{ujian}/soal/generate', [Admin\UjianController::class, 'generateSoal'])->name('ujian.soal.generate');
        Route::post('ujian/{ujian}/soal/baru', [Admin\UjianController::class, 'storeSoalBaru'])->name('ujian.soal.baru');
        Route::delete('ujian/{ujian}/soal/{soal}', [Admin\UjianController::class, 'detachSoal'])->name('ujian.soal.detach');
        Route::delete('ujian/{ujian}/soal', [Admin\UjianController::class, 'detachAll'])->name('ujian.soal.detach-all');
        Route::get('ujian/{ujian}/hasil', [Admin\UjianController::class, 'hasil'])->name('ujian.hasil');
        Route::get('ujian/{ujian}/hasil/export', [Admin\UjianController::class, 'exportHasil'])->name('ujian.hasil.export');
        Route::get('ujian/{ujian}/peserta/{peserta}', [Admin\UjianController::class, 'detailPeserta'])->name('ujian.peserta');
        Route::put('ujian/{ujian}/peserta/{peserta}/skor', [Admin\UjianController::class, 'updateSkor'])->name('ujian.peserta.skor');
        Route::delete('ujian/{ujian}/peserta/{peserta}', [Admin\UjianController::class, 'resetPeserta'])->name('ujian.peserta.reset');
        Route::resource('ujian', Admin\UjianController::class)->except(['show', 'create']);

        // Monitoring Ujian Realtime
        Route::get('monitoring', [Admin\MonitoringController::class, 'index'])->name('monitoring.index');

        // Rekap / Legger
        Route::get('rekap', [Admin\RekapController::class, 'index'])->name('rekap');
        Route::get('rekap/export', [Admin\RekapController::class, 'export'])->name('rekap.export');
    });

    // ================= MAHASISWA =================
    Route::prefix('mahasiswa')->name('mhs.')->middleware('role:mahasiswa')->group(function () {
        Route::get('/', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
        Route::get('ujian/{ujian}', [Mahasiswa\UjianController::class, 'konfirmasi'])->name('ujian.konfirmasi');
        Route::post('ujian/{ujian}/mulai', [Mahasiswa\UjianController::class, 'mulai'])->name('ujian.mulai');
        Route::get('ujian/{ujian}/kerjakan', [Mahasiswa\UjianController::class, 'kerjakan'])->name('ujian.kerjakan');
        Route::post('ujian/{ujian}/jawab', [Mahasiswa\UjianController::class, 'jawab'])->name('ujian.jawab');
        Route::post('ujian/{ujian}/pelanggaran', [Mahasiswa\UjianController::class, 'pelanggaran'])->name('ujian.pelanggaran');
        Route::post('ujian/{ujian}/selesai', [Mahasiswa\UjianController::class, 'selesai'])->name('ujian.selesai');
        Route::get('ujian/{ujian}/hasil', [Mahasiswa\UjianController::class, 'hasil'])->name('ujian.hasil');
        Route::get('hasil', [Mahasiswa\HasilController::class, 'index'])->name('hasil');
    });
});
