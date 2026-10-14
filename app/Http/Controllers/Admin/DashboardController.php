<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'mahasiswa' => User::mahasiswa()->count(),
            'kelas' => Kelas::count(),
            'matakuliah' => MataKuliah::count(),
            'soal' => Soal::count(),
            'ujian' => Ujian::count(),
            'ujian_aktif' => Ujian::where('is_active', true)->count(),
        ];

        $ujianTerdekat = Ujian::with(['mataKuliah', 'kelas'])
            ->withCount(['soal', 'peserta'])
            ->where('waktu_selesai', '>=', now())
            ->orderBy('waktu_mulai')
            ->limit(6)
            ->get();

        $aktivitas = UjianPeserta::with(['user.kelas', 'ujian'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'ujianTerdekat', 'aktivitas'));
    }
}
