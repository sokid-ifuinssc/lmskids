<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jawaban extends Model
{
    protected $table = 'jawaban';

    protected $fillable = [
        'ujian_peserta_id', 'soal_id', 'jawaban', 'is_benar',
        'persen_kunci', 'skor', 'skor_manual',
    ];

    protected function casts(): array
    {
        return [
            'is_benar' => 'boolean',
            'skor_manual' => 'boolean',
            'skor' => 'decimal:2',
            'persen_kunci' => 'decimal:2',
        ];
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(UjianPeserta::class, 'ujian_peserta_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
