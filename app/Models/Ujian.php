<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ujian extends Model
{
    protected $table = 'ujian';

    protected $fillable = [
        'kode', 'judul', 'mata_kuliah_id', 'jenis', 'cpmk_ke', 'durasi',
        'waktu_mulai', 'waktu_selesai', 'is_active', 'acak_soal',
        'tampilkan_nilai', 'max_pelanggaran', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
            'is_active' => 'boolean',
            'acak_soal' => 'boolean',
            'tampilkan_nilai' => 'boolean',
        ];
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'ujian_kelas');
    }

    public function soal(): BelongsToMany
    {
        return $this->belongsToMany(Soal::class, 'ujian_soal')
            ->withPivot('urutan')
            ->orderBy('ujian_soal.urutan');
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(UjianPeserta::class);
    }

    /** Label untuk kolom legger, mis. "CPMK 1", "UTS", "UAS". */
    public function getLabelAttribute(): string
    {
        return $this->jenis === 'CPMK' ? 'CPMK ' . ($this->cpmk_ke ?: 1) : $this->jenis;
    }

    /** Urutan kolom legger: CPMK (berdasar nomor) -> UTS -> UAS */
    public function getSortKeyAttribute(): int
    {
        return match ($this->jenis) {
            'CPMK' => (int) $this->cpmk_ke,
            'UTS' => 500,
            'UAS' => 900,
            default => 999,
        };
    }

    /** Status jadwal: belum, berlangsung, selesai, nonaktif. */
    public function getStatusJadwalAttribute(): string
    {
        if (! $this->is_active) {
            return 'nonaktif';
        }
        $now = now();
        if ($now->lt($this->waktu_mulai)) {
            return 'belum';
        }
        if ($now->gt($this->waktu_selesai)) {
            return 'selesai';
        }

        return 'berlangsung';
    }

    public function bisaDikerjakan(): bool
    {
        return $this->status_jadwal === 'berlangsung';
    }
}
