<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gl extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'gl_id',
        'is_aktif'
    ];

    public function gl(): BelongsTo
    {
        return $this->belongsTo(Gl::class);
    }
}
