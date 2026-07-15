<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'uom',
        'kategori',
        'group',
        'keterangan',
        'user_id',
        'is_aktif'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk kategori
    public function getKategoriDeskripsiAttribute()
    {
        switch ($this->kategori) {
            case 1:
                return 'Finish Goods';
            case 2:
                return 'Raw Material';
            default:
                return 'Unknown';
        }
    }

    // public function getKategoriLabelAttribute()
    // {
    //     return $this->kategori === 1 ? 'FG' : ($this->kategori === 2 ? 'RM' : 'Lainnya');
    // }

    public function copackers()
    {
        return $this->hasMany(Copacker::class);
    }

    public function copackerMaterials()
    {
        return $this->hasMany(CopackerMaterial::class);
    }

    // public function getStokAttribute()
    // {
    //     $masuk = $this->copackerMaterials()
    //         ->whereHas('copacker', function ($query) {
    //             $query->whereHas('typeTransaksi', function ($query) {
    //                 $query->where('kategori', 'Masuk');
    //             });
    //         })
    //         ->sum('qty');

    //     $keluar = $this->copackerMaterials()
    //         ->whereHas('copacker', function ($query) {
    //             $query->whereHas('typeTransaksi', function ($query) {
    //                 $query->where('kategori', 'Keluar');
    //             });
    //         })
    //         ->sum('qty');

    //     return $masuk - $keluar;
    // }

    // public function getStokPerPlant($plantId)
    // {
    //     $masuk = $this->copackerMaterials()
    //         ->whereHas('copacker', function ($query) use ($plantId) {
    //             $query->where('plant_id', $plantId)
    //                 ->whereHas('typeTransaksi', function ($query) {
    //                     $query->where('kategori', 'Masuk');
    //                 });
    //         })
    //         ->sum('qty');

    //     $keluar = $this->copackerMaterials()
    //         ->whereHas('copacker', function ($query) use ($plantId) {
    //             $query->where('plant_id', $plantId)
    //                 ->whereHas('typeTransaksi', function ($query) {
    //                     $query->where('kategori', 'Keluar');
    //                 });
    //         })
    //         ->sum('qty');

    //     return $masuk - $keluar;
    // }

    public function getStokPerplantDanTanggal($plantId, $tgl)
    {
        $masuk = $this->copackerMaterials()
            ->whereHas('copacker', function ($query) use ($plantId, $tgl) {
                $query->where('plant_id', $plantId)
                    ->whereDate('tgl', '<=', $tgl)
                    ->whereHas('typeTransaksi', function ($query) {
                        $query->where('kategori', 'Masuk');
                    });
            })
            ->sum('qty');

        $keluar = $this->copackerMaterials()
            ->whereHas('copacker', function ($query) use ($plantId, $tgl) {
                $query->where('plant_id', $plantId)
                    ->whereDate('tgl', '<=', $tgl)
                    ->whereHas('typeTransaksi', function ($query) {
                        $query->where('kategori', 'Keluar');
                    });
            })
            ->sum('qty');

        return $masuk - $keluar;
    }

    public function plants()
    {
        return $this->belongsToMany(Plant::class, 'material_plant');
    }
}
