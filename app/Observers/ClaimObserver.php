<?php

namespace App\Observers;

use App\Models\Claim;
use App\Models\WorkflowConfig;
use App\Models\ClaimApproval;
use App\Services\ClaimNotificationService;

class ClaimObserver
{
    /**
     * Handle the Claim "created" event.
     */
    public function created(Claim $claim): void
    {
        // Ambil setting workflow berdasarkan plant klaim
        $workflows = WorkflowConfig::where('plant_id', $claim->plant_id)
            ->orderBy('step_order', 'asc')
            ->get();

        // Duplikasikan alur ke tabel claim_approvals
        foreach ($workflows as $wf) {
            ClaimApproval::create([
                'claim_id'   => $claim->id,
                'user_id'    => $wf->user_id,
                'step_order' => $wf->step_order,
                'status'     => 'pending',
            ]);
        }

        // Otomatis kirim email permintaan persetujuan ke approver tahap pertama (Step 1)
        ClaimNotificationService::sendNextApprovalOrCompletionNotification($claim);
    }

    /**
     * Handle the Claim "updated" event.
     */
    public function updated(Claim $claim): void
    {
        //
    }

    /**
     * Handle the Claim "deleted" event.
     */
    public function deleted(Claim $claim): void
    {
        //
    }

    /**
     * Handle the Claim "restored" event.
     */
    public function restored(Claim $claim): void
    {
        //
    }

    /**
     * Handle the Claim "force deleted" event.
     */
    public function forceDeleted(Claim $claim): void
    {
        //
    }
}
