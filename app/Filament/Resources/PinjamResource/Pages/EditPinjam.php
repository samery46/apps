<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use App\Mail\NotifikasiKembaliMail; // Import Mailable baru
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Address;

class EditPinjam extends EditRecord
{
    protected static string $resource = PinjamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Hook yang berjalan setelah tombol "Save" sukses diklik pada mode Edit
    protected function afterSave(): void
    {
        // Muat relasi terbaru dari record yang diubah
        $pinjam = $this->record->load(['karyawan', 'user']);

        $emailKaryawan = $pinjam->karyawan->email ?? null;
        $namaKaryawan = $pinjam->karyawan->nama ?? 'Karyawan';

        $emailUserPenyerah = $pinjam->user->email ?? null;
        $namaUserPenyerah = $pinjam->user->name ?? 'User Penyerah';

        // Email dikirim HANYA JIKA tanggal kembali sudah diisi/diupdate oleh user
        if ($pinjam->tgl_kembali && $emailKaryawan) {
            
            $mail = Mail::to(new Address($emailKaryawan, $namaKaryawan));

            if ($emailUserPenyerah) {
                $mail->cc(new Address($emailUserPenyerah, $namaUserPenyerah));
            }

            // Kirim notifikasi pengembalian
            $mail->send(new NotifikasiKembaliMail($pinjam));
        }
    }
}
