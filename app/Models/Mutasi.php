<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mutasi extends Model 
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tgl',
        'plant_id',
        'periode',
        'iap',
        'adm',
        'potongan',
        'ar_mars',
        'direct_selling',
        'rumah_club',
        'sewa_dispenser',
        'avalan',
        'fada',
        'jaminan',
        'packaging',
        'galon_afkir',
        'sewa_depo',
        'raw_material',
        'pem_listrik',
        'klaim_sopir',
        'admin_bank',
        'others',
        'keterangan',
        'foto',
        'user_id',
        'is_aktif',
        'subtotal1',
        'subtotal2',
        'subtotal3',
        'grandtotal'
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Kalkulasi subtotal1, subtotal2, dan subtotal3
            $model->subtotal1 = $model->iap + $model->adm + $model->potongan;
            $model->subtotal2 = $model->ar_mars + $model->direct_selling + $model->rumah_club;
            $model->subtotal3 = $model->sewa_dispenser + $model->avalan + $model->fada
                + $model->jaminan + $model->packaging + $model->galon_afkir
                + $model->sewa_depo + $model->raw_material + $model->pem_listrik
                + $model->klaim_sopir + $model->admin_bank + $model->others;

            // Kalkulasi grandtotal
            $model->grandtotal = $model->subtotal1 + $model->subtotal2 + $model->subtotal3;
        });
    }
}
