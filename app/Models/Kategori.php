<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'kategori_id',
        'is_aktif'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }
}
