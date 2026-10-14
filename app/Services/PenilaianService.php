<?php

namespace App\Services;

use App\Models\Jawaban;
use App\Models\Soal;
use App\Models\UjianPeserta;
use Illuminate\Support\Facades\DB;

class PenilaianService
{
    /**
     * Koreksi satu jawaban (otomatis) dan kembalikan atribut hasil.
     * PG: benar = bobot penuh, salah = 0.
     * Essay: skor = bobot x persentase kata kunci yang ditemukan.
     */
    public function koreksi(Soal $soal, ?string $jawaban): array
    {
        $jawaban = trim((string) $jawaban);

        if ($soal->isPg()) {
            $benar = $jawaban !== '' && strtoupper($jawaban) === strtoupper((string) $soal->kunci);

            return [
                'is_benar' => $benar,
                'persen_kunci' => null,
                'skor' => $benar ? $soal->bobot : 0,
            ];
        }

        $persen = $this->persenKataKunci($soal, $jawaban);

        return [
            'is_benar' => null,
            'persen_kunci' => $persen,
            'skor' => round($soal->bobot * $persen / 100, 2),
        ];
    }

    /** Persentase kata kunci yang muncul dalam jawaban essay. */
    public function persenKataKunci(Soal $soal, string $jawaban): float
    {
        $kunci = $soal->daftarKataKunci();
        if (empty($kunci) || $jawaban === '') {
            return 0;
        }
        $teks = $this->normalisasi($jawaban);
        $cocok = collect($kunci)->filter(fn ($k) => str_contains($teks, $this->normalisasi($k)))->count();

        return round($cocok / count($kunci) * 100, 2);
    }

    /** Kata kunci yang ditemukan (untuk ditampilkan ke dosen). */
    public function kataKunciCocok(Soal $soal, ?string $jawaban): array
    {
        $teks = $this->normalisasi((string) $jawaban);

        return collect($soal->daftarKataKunci())
            ->mapWithKeys(fn ($k) => [$k => $teks !== '' && str_contains($teks, $this->normalisasi($k))])
            ->all();
    }

    private function normalisasi(string $s): string
    {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);

        return ' ' . trim(preg_replace('/\s+/', ' ', $s)) . ' ';
    }

    /**
     * Selesaikan ujian: koreksi seluruh soal (termasuk yang tidak dijawab)
     * lalu hitung nilai akhir skala 0-100.
     */
    public function selesaikan(UjianPeserta $peserta, ?string $catatan = null): UjianPeserta
    {
        return DB::transaction(function () use ($peserta, $catatan) {
            $peserta->refresh();
            if ($peserta->isSelesai()) {
                return $peserta;
            }

            $soalList = $peserta->ujian->soal;
            $jawabanMap = $peserta->jawaban()->get()->keyBy('soal_id');

            foreach ($soalList as $soal) {
                $jwb = $jawabanMap->get($soal->id);
                $hasil = $this->koreksi($soal, $jwb?->jawaban);
                Jawaban::updateOrCreate(
                    ['ujian_peserta_id' => $peserta->id, 'soal_id' => $soal->id],
                    $hasil + ['jawaban' => $jwb?->jawaban]
                );
            }

            $peserta->status = 'selesai';
            $peserta->selesai_at = now()->min($peserta->batas_at->copy()->addMinute());
            if ($catatan) {
                $peserta->catatan = $catatan;
            }
            $peserta->save();

            return $this->hitungNilai($peserta);
        });
    }

    /** Hitung ulang nilai akhir dari skor jawaban. */
    public function hitungNilai(UjianPeserta $peserta): UjianPeserta
    {
        $soalList = $peserta->ujian->soal;
        $totalBobot = $soalList->sum('bobot');
        $totalSkor = $peserta->jawaban()->whereIn('soal_id', $soalList->pluck('id'))->sum('skor');

        $peserta->nilai = $totalBobot > 0 ? round($totalSkor / $totalBobot * 100, 2) : 0;
        $peserta->save();

        return $peserta;
    }
}
