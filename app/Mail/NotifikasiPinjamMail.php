<?php

namespace App\Mail;

use App\Models\Pinjam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiPinjamMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pinjam $pinjam;

    /**
     * Create a new message instance.
     */
    public function __construct(Pinjam $pinjam)
    {
        // Memuat relasi agar datanya lengkap saat ditampilkan di email view
        $this->pinjam = $pinjam->load(['karyawan', 'items.perangkat', 'user']);
    }

    /**
    * public function build()
    * {
    *    return $this->subject('Notifikasi: Peminjaman Perangkat Baru')
    *                ->view('emails.pinjam_created');
    *}

    */
                    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
    // Mengambil email dan nama dari user penyerah
    $emailPenyerah = $this->pinjam->user->email ?? null;
    $namaPenyerah = $this->pinjam->user->name ?? 'Admin';

    // Buat array penampung replyTo
    $replyToComponents = [];

    // Jika email user penyerah tersedia, masukkan ke dalam replyTo
    if ($emailPenyerah) {
        $replyToComponents[] = new Address($emailPenyerah, $namaPenyerah);
    }

    return new Envelope(
        subject: 'Notifikasi : Peminjaman Perangkat',
        replyTo: $replyToComponents, // Mengatur ke siapa email ini dibalas
    );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pinjam_created', // Di sini kita ubah menjadi file blade yang benar!
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
