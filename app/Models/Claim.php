<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ClaimAttachment::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ClaimApproval::class)
            ->with('user')
            ->orderBy('step_order', 'asc');
    }

    public function currentStepOrder(): ?int
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->orderBy('step_order', 'asc')
            ->value('step_order');
    }

    public function isUserCurrentApprover(User $user): bool
    {
        $currentStep = $this->currentStepOrder();
        if ($currentStep === null) {
            return false;
        }

        return $this->approvals()
            ->where('status', 'pending')
            ->where('user_id', $user->id)
            ->where('step_order', $currentStep)
            ->exists();
    }

    public function approve(int $currentStep, int $userId, ?string $catatan = null): bool
    {
        if ($currentStep > 1) {
            $previousRejected = $this->approvals()
                ->where('step_order', '<', $currentStep)
                ->where('status', 'rejected')
                ->exists();

            if ($previousRejected) {
                throw new \Exception("Approval di step sebelumnya ditolak, tidak bisa melanjutkan.");
            }
        }

        $approval = $this->approvals()
            ->where('step_order', $currentStep)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            throw new \Exception("Step approval tidak ditemukan atau sudah diproses.");
        }

        $approval->update([
            'status'    => 'approved',
            'action_at' => now(),
            'catatan'   => $catatan,
        ]);

        $hasPending = $this->approvals()->where('status', 'pending')->exists();
        if (!$hasPending) {
            $this->update(['status' => 'approved']);
        }

        return true;
    }

    public function reject(int $currentStep, int $userId, string $catatan): bool
    {
        $approval = $this->approvals()
            ->where('step_order', $currentStep)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            throw new \Exception("Step approval tidak ditemukan atau sudah diproses.");
        }

        $approval->update([
            'status'    => 'rejected',
            'action_at' => now(),
            'catatan'   => $catatan,
        ]);

        $this->update(['status' => 'rejected']);

        return true;
    }

    public function isFullyApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function hasAnyApproval(): bool
    {
        return $this->approvals()->where('status', 'approved')->exists();
    }

    public function canBeEdited(): bool
    {
        // Klaim hanya bisa diedit jika statusnya masih pending dan BELUM ADA satupun approver yang menyetujui
        return $this->status === 'pending' && ! $this->hasAnyApproval();
    }

    public static function generateTicketNumber(?int $plantId): string
    {
        // 1. Ambil Kode Plant (Default 'J000' jika plant belum dipilih)
        $plantKode = 'J000';
        if ($plantId) {
            $plant = \App\Models\Plant::find($plantId);
            if ($plant) {
                $plantKode = $plant->kode;
            }
        }

        $bulan = date('m');
        $tahun = date('Y');

        // 2. Hitung jumlah klaim yang dibuat pada bulan & tahun berjalan
        $count = self::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();

        // 3. Format Running Number 3 digit (001, 002, DST)
        $runningNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Format: 001/CLM/J302/09/2026
        return "{$runningNumber}/CLM/{$plantKode}/{$bulan}/{$tahun}";
    }
}
