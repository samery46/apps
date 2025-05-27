<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ApprovalSetting;
use App\Models\AssetApproval;
use Illuminate\Support\Facades\DB;

class AssetRequest extends Model
{

    protected $fillable = [
        'plant_id',
        'timestamp',
        'document_number',
        'asset_group_id',
        'fixed_asset_number',
        'cea_number',
        'cost_center_id', // ← tambahkan ini
        'type',
        'sub_asset_number',
        'usage_period',
        'quantity',
        'condition',
        'item_name',
        'country_of_origin',
        'year_of_manufacture',
        'supplier',
        'expected_arrival',
        'expected_usage',
        'location',
        'description',
        'status',
        'is_aktif',
        'user_id',
    ];

    protected static function booted()
    {

        static::creating(function ($assetRequest) {
            $month = now()->format('m');
            $year = now()->format('Y');

            $plant = \App\Models\Plant::find($assetRequest->plant_id);
            $plantKode = $plant?->kode ?? 'XXX';

            $count = self::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->where('plant_id', $assetRequest->plant_id)
                        ->count();

            $urut = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $assetRequest->document_number = "{$urut}/{$plantKode}/{$month}/{$year}";
        });

        static::created(function ($assetRequest) {
            // Buat asset approvals berdasarkan approval setting plant terkait
            $settings = ApprovalSetting::where('plant_id', $assetRequest->plant_id)
                ->orderBy('level')
                ->get();

            foreach ($settings as $setting) {
                AssetApproval::create([
                    'asset_request_id' => $assetRequest->id,
                    'user_id' => $setting->user_id,
                    'level' => $setting->level,
                    'status' => 'pending',
                ]);
            }
            // Set status awal jadi pending
            $assetRequest->update(['status' => 'pending']);
        });

    }

    // Relasi ke Plant

    public function plant(): BelongsTo
    {
            return $this->belongsTo(Plant::class);
    }

    // Relasi ke Cost Center
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

        public function assetGroup(): BelongsTo
    {
        return $this->belongsTo(AssetGroup::class);
    }


public function approvals(): HasMany
    {
        return $this->hasMany(AssetApproval::class)
            ->with('user') // <- penting untuk bisa akses user->name tanpa query tambahan
            ->orderBy('level');
    }

    // Mendapatkan level approval yang sedang pending pertama kali
    public function currentApprovalLevel(): ?int
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('level')
            ->value('level');
    }

    // Cek apakah user ini adalah approver pada level saat ini yang pending
    public function isUserCurrentApprover(User $user): bool
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->where('user_id', $user->id)
            ->where('level', $this->currentApprovalLevel())
            ->exists();
    }

    // Fungsi approve
    public function approve(int $currentLevel, int $userId, ?string $note = null): bool
    {
        // Cek apakah ada approval level sebelumnya yang rejected
        if ($currentLevel > 1) {
            // $previousApproval = $assetRequest->approvals()
            $previousApproval = $this->approvals()
                ->where('level', $currentLevel - 1)
                ->first();

            if ($previousApproval && $previousApproval->status === 'rejected') {
                throw new \Exception("Approval di level sebelumnya ditolak, tidak bisa melanjutkan approval.");
            }
        }

        // Cari approval pending level dan user yang sesuai
        $approval = $this->approvals()
            ->where('level', $currentLevel)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            throw new \Exception("Approval tidak ditemukan atau sudah diproses.");
        }

        // Update approval jadi approved
        $approval->update([
            'status' => 'approved',
            'approved_at' => now(),
            'note' => $note,
        ]);

        // Update status AssetRequest berdasarkan approval
        $pendingCount = $this->approvals()->where('status', 'pending')->count();
        $rejectedCount = $this->approvals()->where('status', 'rejected')->count();

        if ($pendingCount === 0 && $rejectedCount === 0) {
            $this->update(['status' => 'approved']);
        } elseif ($rejectedCount > 0) {
            $this->update(['status' => 'rejected']);
        } else {
            $this->update(['status' => 'in_review']);
        }

        return true;
    }

    // Fungsi reject
    public function reject(int $currentLevel, int $userId, ?string $note = null): bool
    {
        $approval = $this->approvals()
            ->where('level', $currentLevel)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            throw new \Exception("Approval tidak ditemukan atau sudah diproses.");
        }

        $approval->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'note' => $note,
        ]);

        $this->update(['status' => 'rejected']);

        return true;
    }

    // Cek apakah sudah fully approved
    public function isFullyApproved(): bool
    {
        return $this->status === 'approved';
    }
}
