<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'uom',
        'kategori',
        'group',
        'keterangan',
        'user_id',
        'is_aktif'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk kategori
    public function getKategoriDeskripsiAttribute()
    {
        switch ($this->kategori) {
            case 1:
                return 'Finish Goods';
            case 2:
                return 'Raw Material';
            default:
                return 'Unknown';
        }
    }
}
