<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\UjianPeserta;
use App\Services\LeggerService;

class HasilController extends Controller
{
    public function index(LeggerService $legger)
    {
        $user = auth()->user()->load('kelas');

        // Mata kuliah yang memiliki ujian untuk kelas mahasiswa
        $mkList = MataKuliah::whereHas('ujian.kelas', fn ($q) => $q->where('kelas.id', $user->kelas_id))
            ->orderBy('nama')->get();

        $rekap = $mkList->map(function ($mk) use ($legger, $user) {
            $data = $legger->build($mk->id, $user->kelas_id, $user->id);

            return [
                'mk' => $mk,
                'ujian' => $data['ujian'],
                'nilai' => $data['rows']->first()['nilai'] ?? [],
            ];
        });

        $riwayat = UjianPeserta::with('ujian.mataKuliah')
            ->where('user_id', $user->id)->where('status', 'selesai')
            ->latest('selesai_at')->get();

        return view('mahasiswa.hasil', compact('user', 'rekap', 'riwayat'));
    }
}
