<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjianPeserta extends Model
{
    protected $table = 'ujian_peserta';

    protected $fillable = [
        'ujian_id', 'user_id', 'mulai_at', 'batas_at', 'selesai_at',
        'status', 'pelanggaran', 'urutan_soal', 'nilai', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at' => 'datetime',
            'batas_at' => 'datetime',
            'selesai_at' => 'datetime',
            'urutan_soal' => 'array',
            'nilai' => 'decimal:2',
        ];
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(Jawaban::class);
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function sisaDetik(): int
    {
        return max(0, now()->diffInSeconds($this->batas_at, false));
    }
}
