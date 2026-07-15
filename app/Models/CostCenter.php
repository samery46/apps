<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    protected $fillable = [
        'plant_id',
        'cost_center',
        'name',
        'short_text',
        'is_aktif',
        'user_id',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function assetRequests()
    {
        return $this->hasMany(AssetRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
