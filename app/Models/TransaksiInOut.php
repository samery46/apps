<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiInOut extends Model
{
    use HasFactory, SoftDeletes;
        protected $fillable = [
        'barang_id',
        'karyawan_id',
        'user_id',
        'jenis_transaksi',
        'qty',
        'tgl_pengajuan',
        'tgl_diterima',
        'status_pengajuan',
        'kondisi_barang',
        'pengaruh_stok',
        'catatan',
        'plant_id',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id'); // untuk nama_pengguna
    }

    public function pemberi()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function barang()
    {
        return $this->belongsTo(Kategori::class, 'barang_id');
    }

    /**
     * Status apakah transaksi valid terhadap stok
     */
    public function isSelesai()
    {
        return $this->status_pengajuan === 'Selesai';
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Jenis transaksi deskriptif
     */
    public function getJenisFormattedAttribute()
    {
        return $this->jenis_transaksi === 'masuk' ? 'Masuk (Inbound)' : 'Keluar (Outbound)';
    }

    public function getStatusBadgeColorAttribute()
    {
        return match ($this->status_pengajuan) {
            'Diajukan' => 'warning',
            'Disetujui' => 'primary',
            'Selesai' => 'success',
            'Ditolak' => 'danger',
            default => 'gray',
        };
    }

    public static function booted()
    {
        static::saving(function ($model) {
            if (!$model->barang->is_barang) {
                throw new \Exception('Yang dipilih bukan barang.');
            }
        });
    }

}
