<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barang_id',
        'tanggal',
        'jumlah_penyesuaian',
        'tipe',
        'alasan',
        'user_id',
        'plant_id',
    ];

    // public function barang()
    // {
    //     return $this->belongsTo(Kategori::class, 'id')->where('is_barang', true); //
    // }

    public function barang()
    {
        return $this->belongsTo(Kategori::class, 'barang_id')->where('is_barang', true);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    //  public function barang()
    // {
    //     return $this->belongsTo(Kategori::class, 'id');
    // }


}
