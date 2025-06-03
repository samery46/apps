<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalSetting extends Model
{
    use HasFactory;

    protected $fillable = ['plant_id', 'level', 'user_id', 'position', 'is_aktif'];

    protected $casts = [
        'level' => 'integer',
        'is_aktif' => 'boolean', // opsional jika kamu ingin is_aktif otomatis dibaca sebagai boolean
    ];


    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
