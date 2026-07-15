<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CopackerMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'copacker_id',
        'material_id',
        'qty',
        'keterangan',
    ];

    public function copacker(): BelongsTo
    {
        return $this->belongsTo(Copacker::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
