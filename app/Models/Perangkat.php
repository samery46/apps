<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perangkat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'serial_number',
        'keterangan',
        'qty',
        'plant_id',
        'is_aktif'
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function pinjamPerangkats(): HasMany
    {
        return $this->hasMany(PinjamPerangkat::class);
    }
}
