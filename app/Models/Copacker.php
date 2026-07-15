<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Copacker extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tgl',
        'plant_id',
        'type_transaksi_id',
        'no_doc',
        'supplier',
        'nopol',
        'keterangan',
        'user_id',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function typeTransaksi(): BelongsTo
    {
        return $this->belongsTo(TypeTransaksi::class);
    }

    public function material()
    {
        return $this->hasMany(Material::class);
    }

    public function copackerMaterials()
    {
        return $this->hasMany(CopackerMaterial::class);
    }
}
