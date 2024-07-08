<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\EmailHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Log;

class Pinjam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'karyawan_id',
        'tgl_pinjam',
        'tgl_kembali',
        'user_id',
        'keterangan',
        'is_complete'
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PinjamPerangkat::class, 'pinjam_id');
    }

    public function getStatusTextAttribute()
    {
        return $this->is_complete ? 'Sudah Dikembalikan' : 'Belum Dikembalikan';
    }

    protected static function booted()
    {

        // static::created(function ($record) {
        //     $karyawanName = $record->karyawan->nama ?? '-';
        //     $karyawanEmail = $record->karyawan->email ?? '-';
        //     $karyawanJob = $record->karyawan->job_title ?? '-';
        //     $userName = $record->user->name ?? '-';
        //     $emailcc = $record->user->email ?? '-';
        //     $tglPinjam = DateHelper::formatIndonesianDate($record->tgl_pinjam);
        //     $tglCreate = DateHelper::formatIndonesianDate($record->created_at);

        //     $items = $record->items;
        //     $itemDetailsRows = '';
        //     $perangkatNama = '';
        //     $serialNumber = '';

        //     $nomor = 1;

        //     foreach ($items as $item) {
        //         $perangkatNama = $item->perangkat->nama ?? '';
        //         $serialNumber = $item->perangkat->serial_number ?? '';
        //         $itemDetailsRows .= "
        //         <tr>
        //         <td width=8%></td>
        //         <td width=2%></td>
        //         <td><b>{$nomor}. {$perangkatNama} {$serialNumber}</b></td>
        //         </tr>";
        //         $nomor++;
        //     }

        //     $to = $karyawanEmail;
        //     $toName = $karyawanName;
        //     $fromName = $userName;
        //     $cc = $emailcc;
        //     $ccName = $userName;
        //     $subject = 'Peminjaman Perangkat';

        //     $body = "
        //     <p>Dear Bpk/Ibu/Sdr/i :</p>
        //     <p><b>{$karyawanName}</b></p>
        //     <p>Job Title: <b>{$karyawanJob}</b></p>
        //     <p>Berikut adalah data peminjaman perangkat Anda:</p>
        //     <table>
        //     <tbody>

        //         <tr>
        //             <td width=8%>Nama Perangkat - Serial Number</td>
        //             <td width=2%>:</td>
        //         </tr>
        //         {$itemDetailsRows}
        //         <tr>
        //             <td width=8%>Tanggal Pinjam</td>
        //             <td width=2%>:</td>
        //             <td width=25%>{$tglPinjam}</td>
        //         </tr>
        //     </tbody>
        //     </table>
        //     <br>
        //     <p>Mohon untuk segera di kembalikan perangkat tsb, jika sudah selesai digunakan.</p>
        //     Terimakasih.<br>
        //     <br>
        //     <b>Penerima</b><br>
        //     <br>
        //     <br>
        //     <u><b>{$userName}</b></u><br>
        //     Created : <i>{$tglCreate}</i><br>
        //     ";
        //     EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        // });

        static::updated(function ($record) {
            $karyawanName = $record->karyawan->nama ?? '-';
            $karyawanEmail = $record->karyawan->email ?? '-';
            $karyawanJob = $record->karyawan->job_title ?? '-';
            $userName = $record->user->name ?? '-';
            $emailcc = $record->user->email ?? '-';
            $tglPinjam = DateHelper::formatIndonesianDate($record->tgl_pinjam);
            $tglKembali = $record->tgl_kembali ? DateHelper::formatIndonesianDate($record->tgl_kembali) : '';
            $tglUpdate = DateHelper::formatIndonesianDate($record->updated_at);

            $items = $record->items;
            $itemDetailsRows = '';

            $nomor = 1;

            foreach ($items as $item) {
                $perangkatNama = $item->perangkat->nama ?? '';
                $serialNumber = $item->perangkat->serial_number ?? '';
                $itemDetailsRows .= "
                <tr>
                <td width=8%></td>
                <td width=2%></td>
                <td><b>{$nomor}. {$perangkatNama} {$serialNumber}</b></td>
                </tr>";
                $nomor++;
            }

            $to = $karyawanEmail;
            $toName = $karyawanName;
            $fromName = $userName;
            $cc = $emailcc;
            $ccName = $userName;
            $subject = 'Peminjaman Perangkat';

            $body = "
            <p>Dear Bpk/Ibu/Sdr/i :</p>
            <p><b>{$karyawanName}</b></p>
            <p>Job Title: <b>{$karyawanJob}</b></p>
            <p>Berikut adalah data peminjaman perangkat Anda:</p>
            <table>
            <tbody>

                <tr>
                    <td width=8%>Nama Perangkat - Serial Number</td>
                    <td width=2%>:</td>
                </tr>
                {$itemDetailsRows}
                <tr>
                    <td width=8%>Tanggal Pinjam | Tanggal Kembali</td>
                    <td width=2%>:</td>
                    <td width=25%>{$tglPinjam} | {$tglKembali}</td>
                </tr>
                <tr>
                    <td width=8%>Status</td>
                    <td width=2%>:</td>
                    <td width=25%><b>{$record->status_text}</b></td>
                </tr>
            </tbody>
            </table>
            <br>
            Terimakasih.<br>
            <br>
            <b>Penyerah/Penerima</b><br>
            <br>
            <br>
            <u><b>{$userName}</b></u><br>
            <i>Tgl : {$tglUpdate}</i><br>
            ";

            // EmailHelper::sendEmail($to, $subject, $body);
            EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        });
    }
}
