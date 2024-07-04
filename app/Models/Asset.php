<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor',
        'sub',
        'tipe',
        'plant_id',
        'nama',
        'tgl_perolehan',
        'harga',
        'nbv',
        'serial_number',
        'status',
        'qty_sap',
        'qty_aktual',
        'kondisi',
        'karyawan_id',
        'lokasi',
        'keterangan',
        'foto',
        'user_id',
        'is_aktif'
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
