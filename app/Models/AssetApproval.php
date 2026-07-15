<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_request_id',
        'user_id',
        'level',
        'status',
        'approved_at',
        'note',
    ];

    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class,'asset_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
