<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalSetting extends Model
{
    use HasFactory;

    protected $fillable = ['plant_id', 'level', 'user_id', 'position', 'is_aktif'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
