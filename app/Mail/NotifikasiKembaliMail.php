<?php

namespace App\Mail;

use App\Models\Pinjam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiKembaliMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pinjam $pinjam;

    public function __construct(Pinjam $pinjam)
    {
        // Memuat relasi agar data lengkap saat ditampilkan di email view
        $this->pinjam = $pinjam->load(['karyawan', 'items.perangkat', 'user']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi: Pengembalian Perangkat Sukses',
            replyTo: $this->pinjam->user->email ? [
                new \Illuminate\Mail\Mailables\Address($this->pinjam->user->email, $this->pinjam->user->name)
            ] : [],
        );
    }

    /**
     * Get the message content definition.
     */
    /**
 * Get the message content definition.
 */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pinjam_returned', // PASTIKAN bagian ini diubah ke template yang benar
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
