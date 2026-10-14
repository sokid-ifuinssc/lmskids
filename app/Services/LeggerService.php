<?php

namespace App\Services;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use Illuminate\Support\Collection;

class LeggerService
{
    /**
     * Susun legger nilai untuk satu mata kuliah & kelas.
     * Hasil: ['ujian' => Collection<Ujian>, 'rows' => [ ['user'=>User, 'nilai'=>[ujian_id => nilai|null]] ]]
     */
    public function build(int $mataKuliahId, ?int $kelasId, ?int $userId = null): array
    {
        $ujianQuery = Ujian::where('mata_kuliah_id', $mataKuliahId);
        if ($kelasId) {
            $ujianQuery->whereHas('kelas', fn ($q) => $q->where('kelas.id', $kelasId));
        }
        /** @var Collection $ujian */
        $ujian = $ujianQuery->get()->sortBy([['sort_key', 'asc'], ['waktu_mulai', 'asc']])->values();

        $mhsQuery = User::mahasiswa()->with('kelas')->orderBy('username');
        if ($userId) {
            $mhsQuery->where('id', $userId);
        } elseif ($kelasId) {
            $mhsQuery->where('kelas_id', $kelasId);
        }
        $mahasiswa = $mhsQuery->get();

        $nilai = UjianPeserta::whereIn('ujian_id', $ujian->pluck('id'))
            ->whereIn('user_id', $mahasiswa->pluck('id'))
            ->where('status', 'selesai')
            ->get()
            ->groupBy('user_id');

        $rows = $mahasiswa->map(function ($m) use ($ujian, $nilai) {
            $mine = ($nilai->get($m->id) ?? collect())->keyBy('ujian_id');
            $nilaiMap = $ujian->mapWithKeys(fn ($u) => [$u->id => $mine->get($u->id)?->nilai])->all();
            $terisi = array_filter($nilaiMap, fn ($v) => $v !== null);
            $rata = count($terisi) > 0 ? round(array_sum($terisi) / count($terisi), 1) : null;

            return [
                'user' => $m,
                'nilai' => $nilaiMap,
                'rata' => $rata,
            ];
        });

        return ['ujian' => $ujian, 'rows' => $rows];
    }
}
