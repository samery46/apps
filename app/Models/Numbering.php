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
}
