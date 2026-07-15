<?php

namespace App\Filament\Resources\PinjamResource\Pages;

use App\Filament\Resources\PinjamResource;
use App\Models\Pinjam;
use App\Mail\NotifikasiPinjamMail; // 1. Import Mailable yang sudah dibuat
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail; // 2. Import Facade Mail Laravel
use Illuminate\Mail\Mailables\Address;
use Illuminate\Validation\ValidationException;

class CreatePinjam extends CreateRecord
{
    protected static string $resource = PinjamResource::class;

    // Digunakan untuk PHPMailer : AutoEmail setelah Create
    protected function afterCreate(): void
    {
        // Muat relasi agar data nama dan email tersedia
        $pinjam = $this->record->load(['karyawan', 'user']);

        $emailKaryawan = $pinjam->karyawan->email ?? null;
        $namaKaryawan = $pinjam->karyawan->nama ?? 'Karyawan';

        $emailUserPenyerah = $pinjam->user->email ?? null;
        $namaUserPenyerah = $pinjam->user->name ?? 'User Penyerah';

        if ($emailKaryawan) {
            // 2. Gunakan objek Address untuk Mail::to()
            $mail = Mail::to(new Address($emailKaryawan, $namaKaryawan));

            // 3. Gunakan objek Address untuk CC jika email penyerah ada
            if ($emailUserPenyerah) {
                $mail->cc(new Address($emailUserPenyerah, $namaUserPenyerah));
            }

            // Kirim email
            $mail->send(new NotifikasiPinjamMail($pinjam));
        } else {
            // Backup plan jika email karyawan kosong
            Mail::to('appsclub@ketik-kan.site')->send(new NotifikasiPinjamMail($pinjam));
        }
    }
}
