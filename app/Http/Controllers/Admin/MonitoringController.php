<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua ujian yang ada, prioritaskan yang aktif dan terbaru
        $ujianList = Ujian::with(['mataKuliah', 'kelas'])
            ->orderByDesc('is_active')
            ->orderByDesc('waktu_mulai')
            ->get();

        // 2. Tentukan ujian yang dipilih
        $ujianId = $request->ujian_id ? (int) $request->ujian_id : $ujianList->first()?->id;
        $selectedUjian = $ujianId ? $ujianList->firstWhere('id', $ujianId) : null;

        $kelasList = collect();
        $selectedKelas = null;
        $mahasiswa = collect();
        $pesertaMap = collect();
        $stats = [
            'total' => 0,
            'selesai' => 0,
            'sedang' => 0,
            'belum' => 0,
            'persen_selesai' => 0,
            'persen_sedang' => 0,
            'persen_belum' => 0,
            'persen_partisipasi' => 0,
            'rata_nilai' => 0,
        ];
        $listBelum = collect();
        $listSedang = collect();
        $listSelesai = collect();

        if ($selectedUjian) {
            $selectedUjian->loadCount('soal');
            $kelasList = $selectedUjian->kelas()->orderBy('nama')->get();

            // Tentukan kelas yang dipilih (default ke kelas pertama dari ujian jika belum memilih)
            $kelasId = $request->kelas_id ? (int) $request->kelas_id : $kelasList->first()?->id;
            $selectedKelas = $kelasId ? $kelasList->firstWhere('id', $kelasId) : null;

            $targetKelasIds = $selectedKelas ? [$selectedKelas->id] : $kelasList->pluck('id')->all();

            if (! empty($targetKelasIds)) {
                $allStudentsInTarget = User::mahasiswa()
                    ->with('kelas')
                    ->whereIn('kelas_id', $targetKelasIds)
                    ->orderBy('kelas_id')
                    ->orderBy('username')
                    ->get();

                // Ambil data attempt peserta
                $pesertaMap = UjianPeserta::where('ujian_id', $selectedUjian->id)
                    ->whereIn('user_id', $allStudentsInTarget->pluck('id'))
                    ->get()
                    ->keyBy('user_id');

                $total = $allStudentsInTarget->count();
                $selesaiCount = $pesertaMap->where('status', 'selesai')->count();
                $sedangCount = $pesertaMap->where('status', 'berlangsung')->count();
                $belumCount = max(0, $total - ($selesaiCount + $sedangCount));

                $selesaiPeserta = $pesertaMap->where('status', 'selesai');
                $avgNilai = $selesaiPeserta->count() > 0 ? round((float) $selesaiPeserta->avg('nilai'), 2) : 0;

                $stats = [
                    'total' => $total,
                    'selesai' => $selesaiCount,
                    'sedang' => $sedangCount,
                    'belum' => $belumCount,
                    'persen_selesai' => $total > 0 ? round(($selesaiCount / $total) * 100, 1) : 0,
                    'persen_sedang' => $total > 0 ? round(($sedangCount / $total) * 100, 1) : 0,
                    'persen_belum' => $total > 0 ? round(($belumCount / $total) * 100, 1) : 0,
                    'persen_partisipasi' => $total > 0 ? round((($selesaiCount + $sedangCount) / $total) * 100, 1) : 0,
                    'rata_nilai' => $avgNilai,
                ];

                // Kelompokkan mahasiswa berdasarkan status pengerjaan
                foreach ($allStudentsInTarget as $m) {
                    $attempt = $pesertaMap->get($m->id);
                    if ($attempt && $attempt->status === 'selesai') {
                        $listSelesai->push(['user' => $m, 'attempt' => $attempt]);
                    } elseif ($attempt && $attempt->status === 'berlangsung') {
                        $listSedang->push(['user' => $m, 'attempt' => $attempt]);
                    } else {
                        $listBelum->push(['user' => $m, 'attempt' => null]);
                    }
                }

                // Filter pencarian nama/NIM jika ada
                if ($request->filled('q')) {
                    $search = strtolower($request->q);
                    $mahasiswa = $allStudentsInTarget->filter(function ($m) use ($search) {
                        return str_contains(strtolower($m->username), $search) || str_contains(strtolower($m->name), $search);
                    });
                } else {
                    $mahasiswa = $allStudentsInTarget;
                }
            }
        }

        return view('admin.monitoring.index', compact(
            'ujianList',
            'selectedUjian',
            'kelasList',
            'selectedKelas',
            'mahasiswa',
            'pesertaMap',
            'stats',
            'listBelum',
            'listSedang',
            'listSelesai'
        ));
    }
}
