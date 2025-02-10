<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitGl extends Model
{
    use HasFactory;

    protected $fillable = [
        // 'profit_id',
        'gl_id',
        'value',
    ];

    public function gl()
    {
        return $this->belongsTo(Gl::class);
    }

    public function profit()
    {
        return $this->belongsTo(Profit::class);
    }
}
