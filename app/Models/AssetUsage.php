<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AssetUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'karyawan_id',
        'start_date',
        'end_date',
        'notes'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    // protected static function booted()
    // {
    //     static::creating(function ($assetUsage) {
    //         // Cek apakah aset sedang digunakan
    //         $isInUse = AssetUsage::where('asset_id', $assetUsage->asset_id)
    //             ->whereNull('end_date')
    //             ->exists();

    //         // Validasi menggunakan Laravel Validator
    //         $validator = Validator::make(
    //             ['is_in_use' => $isInUse],
    //             ['is_in_use' => 'boolean|in:0'],
    //             ['is_in_use.in' => 'Aset ini sedang digunakan dan belum dikembalikan.']
    //         );

    //         // Jika validasi gagal, kirim pesan error
    //         if ($validator->fails()) {
    //             throw ValidationException::withMessages([
    //                 'asset_id' => 'Aset ini sedang digunakan dan belum dikembalikan.'
    //             ]);
    //         }
    //     });
    // }


    // protected static function booted()
    // {
    //     static::created(function ($assetUsage) {
    //         // Setelah penggunaan baru dibuat, update pengguna di Asset
    //         $assetUsage->updateAssetUser();
    //     });

    //     static::updated(function ($assetUsage) {
    //         // Setelah penggunaan diperbarui, update pengguna di Asset
    //         $assetUsage->updateAssetUser();
    //     });
    // }

    // // Method untuk mengupdate pengguna di Asset berdasarkan pengguna terakhir
    // public function updateAssetUser()
    // {
    //     // Ambil data penggunaan terakhir untuk asset ini
    //     $latestUsage = AssetUsage::where('asset_id', $this->asset_id)
    //         ->latest('start_date') // Bisa berdasarkan tanggal mulai
    //         ->first();

    //     // Jika ada penggunaan terakhir, update karyawan di tabel Asset
    //     if ($latestUsage) {
    //         Asset::where('id', $this->asset_id)
    //             ->update(['karyawan_id' => $latestUsage->karyawan_id]);
    //     }
    // }


    protected static function booted()
    {
        static::saved(function ($assetUsage) {
            // Ambil penggunaan terakhir dari asset_id yang sama
            $latestUsage = AssetUsage::where('asset_id', $assetUsage->asset_id)
                ->latest('start_date') // Berdasarkan tanggal pemakaian terbaru
                ->first();

            // Jika penggunaan terakhir ditemukan, update karyawan_id di Asset
            if ($latestUsage) {
                Asset::where('id', $latestUsage->asset_id)
                    ->update(['karyawan_id' => $latestUsage->karyawan_id]);
            }
        });
    }
}
