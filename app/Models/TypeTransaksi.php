<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeTransaksi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_type',
        'kategori',
    ];

    public function copackers()
    {
        return $this->hasMany(Copacker::class);
    }
}
