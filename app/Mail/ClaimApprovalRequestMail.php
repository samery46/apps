<?php

namespace App\Mail;

use App\Models\Claim;
use App\Models\ClaimApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ClaimApprovalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public Claim $claim;
    public ClaimApproval $approval;
    public string $approveUrl;
    public string $rejectUrl;
    public string $viewUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Claim $claim, ClaimApproval $approval)
    {
        $this->claim = $claim->load(['plant', 'asset', 'creator']);
        $this->approval = $approval->load('user');

        // Signed URL berlaku selama 7 hari
        $this->approveUrl = URL::temporarySignedRoute(
            'claim.approval.action',
            now()->addDays(30),
            [
                'claim'    => $claim->id,
                'approval' => $approval->id,
                'action'   => 'approve',
            ]
        );

        $this->rejectUrl = URL::temporarySignedRoute(
            'claim.approval.action',
            now()->addDays(30),
            [
                'claim'    => $claim->id,
                'approval' => $approval->id,
                'action'   => 'reject',
            ]
        );

        try {
            $this->viewUrl = route('filament.admin.resources.claims.edit', ['record' => $claim->id]);
        } catch (\Exception $e) {
            $this->viewUrl = url('/admin/claims/' . $claim->id . '/edit');
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $replyTo = [];
        if ($this->claim->creator && !empty($this->claim->creator->email)) {
            $replyTo[] = new Address($this->claim->creator->email, $this->claim->creator->name);
        }

        return new Envelope(
            subject: 'Perlu Persetujuan: Klaim Aset ' . $this->claim->ticket_number . ' (Tahap ' . $this->approval->step_order . ')',
            replyTo: $replyTo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.claim_approval_request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
