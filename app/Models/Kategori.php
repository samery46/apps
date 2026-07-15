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
        'is_barang',
        'satuan',
        'stok_minimum',
        'is_aktif'
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function parent()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function viewStock() { return $this->hasOne(ViewStock::class, 'barang_id'); }

}
