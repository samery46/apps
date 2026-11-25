<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimApproval;
use App\Mail\ClaimApprovalRequestMail;
use App\Mail\ClaimStatusNotificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClaimNotificationService
{
    /**
     * Kirim email permintaan persetujuan ke approver tertentu.
     */
    public static function sendApprovalRequest(Claim $claim, ClaimApproval $approval): bool
    {
        try {
            $approver = $approval->user;
            if (!$approver || empty($approver->email)) {
                Log::warning("Gagal kirim email approval klaim {$claim->ticket_number}: Email approver tidak ditemukan.");
                return false;
            }

            Mail::to($approver->email)->send(new ClaimApprovalRequestMail($claim, $approval));
            Log::info("Email approval klaim {$claim->ticket_number} (Step {$approval->step_order}) terkirim ke {$approver->email}.");
            return true;
        } catch (\Exception $e) {
            Log::error("Error saat mengirim email approval klaim {$claim->ticket_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cari approver pada step pending berikutnya dan kirimkan email.
     * Jika seluruh step sudah selesai diapprove, kirimkan notifikasi sukses ke pembuat tiket.
     */
    public static function sendNextApprovalOrCompletionNotification(Claim $claim): void
    {
        $claim->refresh();
        $currentStep = $claim->currentStepOrder();

        if ($currentStep !== null) {
            // Masih ada step yang pending, cari approver untuk step tersebut
            $nextApproval = $claim->approvals()
                ->where('step_order', $currentStep)
                ->where('status', 'pending')
                ->first();

            if ($nextApproval) {
                self::sendApprovalRequest($claim, $nextApproval);
            }
        } else {
            // Semua step sudah diapprove
            if ($claim->status === 'approved' && $claim->creator && !empty($claim->creator->email)) {
                try {
                    Mail::to($claim->creator->email)->send(
                        new ClaimStatusNotificationMail($claim, 'approved', 'Seluruh tahapan persetujuan telah selesai.')
                    );
                } catch (\Exception $e) {
                    Log::error("Error saat kirim notifikasi klaim approved ke pembuat: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Kirim notifikasi ke pembuat tiket jika klaim ditolak.
     */
    public static function sendRejectionNotification(Claim $claim, ClaimApproval $approval): void
    {
        if ($claim->creator && !empty($claim->creator->email)) {
            try {
                $reason = "Ditolak pada tahap {$approval->step_order} oleh {$approval->user->name}. Catatan: " . ($approval->catatan ?? '-');
                Mail::to($claim->creator->email)->send(
                    new ClaimStatusNotificationMail($claim, 'rejected', $reason)
                );
            } catch (\Exception $e) {
                Log::error("Error saat kirim notifikasi klaim rejected ke pembuat: " . $e->getMessage());
            }
        }
    }
}
