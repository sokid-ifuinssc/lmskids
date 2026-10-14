<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';

    protected $fillable = ['kode', 'nama', 'sks', 'semester'];

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class);
    }

    public function ujian(): HasMany
    {
        return $this->hasMany(Ujian::class);
    }
}
