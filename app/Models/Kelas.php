<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = ['nama', 'keterangan'];

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'mahasiswa');
    }

    public function ujian(): BelongsToMany
    {
        return $this->belongsToMany(Ujian::class, 'ujian_kelas');
    }

    /** Cari kelas berdasarkan nama, buat otomatis bila belum ada. */
    public static function findOrCreateByName(?string $nama): ?self
    {
        $nama = strtoupper(trim((string) $nama));
        if ($nama === '') {
            return null;
        }

        return static::firstOrCreate(['nama' => $nama]);
    }
}
