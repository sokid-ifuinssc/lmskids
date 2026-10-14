<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Soal extends Model
{
    protected $table = 'soal';

    protected $fillable = [
        'mata_kuliah_id', 'tipe', 'pertanyaan',
        'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d',
        'kunci', 'kata_kunci', 'bobot',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function ujian(): BelongsToMany
    {
        return $this->belongsToMany(Ujian::class, 'ujian_soal');
    }

    public function isPg(): bool
    {
        return $this->tipe === 'pg';
    }

    /** Opsi jawaban PG dalam bentuk ['A' => '...', ...] */
    public function opsi(): array
    {
        return [
            'A' => $this->opsi_a,
            'B' => $this->opsi_b,
            'C' => $this->opsi_c,
            'D' => $this->opsi_d,
        ];
    }

    /** Daftar kata kunci essay yang sudah dinormalisasi. */
    public function daftarKataKunci(): array
    {
        return collect(preg_split('/[,;\n]+/', (string) $this->kata_kunci))
            ->map(fn ($k) => trim(mb_strtolower($k)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getTipeLabelAttribute(): string
    {
        return $this->tipe === 'pg' ? 'Pilihan Ganda' : 'Essay';
    }
}
