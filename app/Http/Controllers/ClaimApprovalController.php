<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimApproval;
use App\Services\ClaimNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ClaimApprovalController extends Controller
{
    /**
     * Menangani link aksi approval/rejection dari email (Signed URL).
     */
    public function handle(Request $request, Claim $claim, ClaimApproval $approval, string $action)
    {
        // 1. Verifikasi kecocokan relasi
        if ($approval->claim_id !== $claim->id) {
            abort(404, 'Data approval tidak cocok dengan klaim.');
        }

        // 2. Cek apakah approval sudah pernah diproses
        if ($approval->status !== 'pending') {
            return view('claims.approval_result', [
                'success' => false,
                'message' => "Tahap persetujuan ini sudah pernah diproses sebelumnya dengan status: " . strtoupper($approval->status) . ".",
                'claim'   => $claim,
            ]);
        }

        // 3. Cek urutan step (Sequential Guard)
        $currentStep = $claim->currentStepOrder();
        if ($currentStep === null || $approval->step_order !== $currentStep) {
            return view('claims.approval_result', [
                'success' => false,
                'message' => "Tahap persetujuan sebelumnya belum diselesaikan atau giliran approval telah terlewati.",
                'claim'   => $claim,
            ]);
        }

        // 4. Jika Aksi APPROVE
        if ($action === 'approve') {
            try {
                $claim->approve(
                    $approval->step_order,
                    $approval->user_id,
                    'Disetujui via email link pada ' . now()->format('d/m/Y H:i')
                );

                // Kirim email ke approver step berikutnya atau ke pembuat tiket jika selesai
                ClaimNotificationService::sendNextApprovalOrCompletionNotification($claim);

                return view('claims.approval_result', [
                    'success' => true,
                    'message' => "Terima kasih. Pengajuan klaim aset {$claim->ticket_number} berhasil Anda SETUJUI pada Tahap {$approval->step_order}.",
                    'claim'   => $claim,
                ]);
            } catch (\Exception $e) {
                return view('claims.approval_result', [
                    'success' => false,
                    'message' => "Gagal memproses persetujuan: " . $e->getMessage(),
                    'claim'   => $claim,
                ]);
            }
        }

        // 5. Jika Aksi REJECT: Tampilkan form alasan penolakan
        if ($action === 'reject') {
            $submitUrl = URL::temporarySignedRoute(
                'claim.approval.reject.submit',
                now()->addHours(2),
                [
                    'claim'    => $claim->id,
                    'approval' => $approval->id,
                ]
            );

            return view('claims.approval_reject_form', [
                'claim'     => $claim,
                'approval'  => $approval,
                'submitUrl' => $submitUrl,
            ]);
        }

        abort(400, 'Aksi tidak valid.');
    }

    /**
     * Memproses penolakan klaim beserta catatan dari formulir.
     */
    public function submitReject(Request $request, Claim $claim, ClaimApproval $approval)
    {
        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ]);

        if ($approval->claim_id !== $claim->id || $approval->status !== 'pending') {
            return view('claims.approval_result', [
                'success' => false,
                'message' => "Tahap persetujuan ini sudah tidak dalam status pending.",
                'claim'   => $claim,
            ]);
        }

        try {
            $catatan = $request->input('catatan');
            $claim->reject($approval->step_order, $approval->user_id, $catatan);

            // Kirim notifikasi penolakan ke pembuat tiket
            ClaimNotificationService::sendRejectionNotification($claim, $approval);

            return view('claims.approval_result', [
                'success' => false,
                'message' => "Pengajuan klaim aset {$claim->ticket_number} telah Anda TOLAK pada Tahap {$approval->step_order}.",
                'claim'   => $claim,
            ]);
        } catch (\Exception $e) {
            return view('claims.approval_result', [
                'success' => false,
                'message' => "Gagal memproses penolakan: " . $e->getMessage(),
                'claim'   => $claim,
            ]);
        }
    }
}
