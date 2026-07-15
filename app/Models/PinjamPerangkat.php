<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PinjamPerangkat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pinjam_id',
        'perangkat_id',
    ];

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class);
    }

    public function pinjam()
    {
        return $this->belongsTo(Pinjam::class);
    }

    public function getPerangkatNamaAttribute()
    {
        return $this->perangkat ? $this->perangkat->nama : null;
    }
}
