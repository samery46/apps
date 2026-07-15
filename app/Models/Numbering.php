<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Numbering extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tgl',
        'plant_id',
        'departemen_id',
        'transaction_number',
        'hal',
        'kepada',
        'up',
        'alamat',
        'isi',
        'lampiran',
        'keterangan',
        'user_id',
        'is_aktif'
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {

        static::creating(function ($numbering) {
            $month = now()->format('m');
            $year = now()->format('Y');

            $plant = \App\Models\Plant::find($numbering->plant_id);
            $plantKode = $plant?->kode ?? 'XXX';

            $departemen = \App\Models\Departemen::find($numbering->departemen_id);
            $deptKode = $departemen?->kode ?? 'XXX';

            $count = self::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->where('plant_id', $numbering->plant_id)
                        ->count();

            $urut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $numbering->transaction_number = "{$urut}/{$plantKode}/{$deptKode}/{$month}/{$year}";
        });
    }

}
