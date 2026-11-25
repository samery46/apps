<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewStock extends Model
{
    use HasFactory;

    protected $table = 'view_stocks';
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function getRouteKey()
    {
        return $this->id;
    }

    public function barang()
    {
        return $this->belongsTo(Kategori::class, 'barang_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function getSisaStokAttribute()
    {
        return ($this->total_masuk ?? 0) - ($this->total_keluar ?? 0);
    }
}
