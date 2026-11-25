<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimStatusNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Claim $claim;
    public string $finalStatus; // 'approved' atau 'rejected'
    public string $notes;
    public string $viewUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Claim $claim, string $finalStatus, string $notes = '')
    {
        $this->claim = $claim->load(['plant', 'asset', 'creator']);
        $this->finalStatus = $finalStatus;
        $this->notes = $notes;

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
        $statusLabel = $this->finalStatus === 'approved' ? 'DISETUJUI' : 'DITOLAK';

        return new Envelope(
            subject: "Update Status: Klaim Aset {$this->claim->ticket_number} - {$statusLabel}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.claim_status_notification',
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
