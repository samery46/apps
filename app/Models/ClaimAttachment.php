<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_id',
        'nama_file_asli',
        'file_path',
        'keterangan',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($attachment) {
            if (empty($attachment->nama_file_asli) && !empty($attachment->file_path)) {
                $attachment->nama_file_asli = basename($attachment->file_path);
            }
        });

        static::deleting(function ($attachment) {
            if ($attachment->exists && $attachment->claim && $attachment->claim->hasAnyApproval()) {
                throw new \Exception('Lampiran berkas yang sudah diunggah tidak boleh dihapus setelah dokumen memiliki persetujuan approval.');
            }
        });
    }
}
