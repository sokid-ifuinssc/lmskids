<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\UjianPeserta;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('kelas');

        $ujian = Ujian::with('mataKuliah')->withCount('soal')
            ->where('is_active', true)
            ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $user->kelas_id))
            ->where('waktu_selesai', '>=', now()->subDays(1))
            ->orderBy('waktu_mulai')
            ->get();

        $attempts = UjianPeserta::where('user_id', $user->id)->get()->keyBy('ujian_id');

        $riwayat = UjianPeserta::with('ujian.mataKuliah')
            ->where('user_id', $user->id)->where('status', 'selesai')
            ->latest('selesai_at')->limit(5)->get();

        return view('mahasiswa.dashboard', compact('user', 'ujian', 'attempts', 'riwayat'));
    }
}
