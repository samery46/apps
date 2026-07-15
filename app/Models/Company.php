<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'kota',
        'alamat',
        'pos',
        'is_aktif',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
