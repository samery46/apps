<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profit extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'periode',
        'tahun',
        'keterangan',
        'is_aktif'
    ];


    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProfitGl::class, 'profit_id');
    }

    public function gl()
    {
        return $this->belongsTo(Gl::class);
    }
}
