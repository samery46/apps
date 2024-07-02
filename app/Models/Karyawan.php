<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ActiveKaryawanScope;

class Karyawan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'nik',
        'job_title',
        'email',
        'uid_sap',
        'user_ad',
        'computer_name',
        'tgl_lahir',
        'status',
        'foto',
        'plant_id',
        'departemen_id',
        'is_aktif'
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    // Menambahkan global scope, hanya menampilkan karyawan yang aktif
    protected static function booted()
    {
        static::addGlobalScope(new ActiveKaryawanScope);
    }
}
