<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Helpers\EmailHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\DateHelper;

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
        static::created(function ($record) {
            $karyawanEmail = $record->karyawan->email ?? '-';
            $emailcc = $record->user->email ?? '-';
            $userName = $record->user->name ?? '-';
            $karyawanName = $record->karyawan->nama ?? '-';
            $karyawanJob = $record->karyawan->job_title ?? '-';

            $tglPinjam = DateHelper::formatIndonesianDate($record->tgl_pinjam);
            $tglBuat = DateHelper::formatIndonesianDate($record->created_at);


            $items = $record->items;
            $itemDetails = [];

            foreach ($items as $item) {
                $perangkatNama = $item->perangkat->nama ?? '-';
                $serialNumber = $item->perangkat->serial_number ?? '-';
                $itemDetails[] = "{$perangkatNama} - {$serialNumber}";
            }
            $itemDetailsStr = implode(', ', $itemDetails);


            $to = $karyawanEmail; //$karyawanEmail
            $cc = $emailcc; //$emailcc
            $ccName = $userName;
            $fromName = $userName;
            $toName = $karyawanName;
            $subject = 'Info Peminjaman Perangkat';
            $body = "
            Dear Bpk/Ibu/Sdr/i
            <br>
            <b>{$karyawanName}</b>,<br>
            Job Title : <b>{$karyawanJob}</b><br>
            <br>
            Berikut data Perangkat yang Anda pinjam :
            <br>
            <br>
            <table>
            <tbody>
                <tr>
                    <td width=8%>Nama Perangkat - Serial Number</td>
                    <td width=2%>:</td>
                    <td width=25%>{$itemDetailsStr}</td>
                </tr>
                <tr>
                    <td width=8%>Tgl Pinjam</td>
                    <td width=2%>:</td>
                    <td width=25%>{$tglPinjam}</td>
                </tr>
            </tbody>
            </table>
            <br>
            Mohon untuk segera di kembalikan perangkat tsb, jika sudah selesai digunakan.
            <br><br>
            Terimakasih,
            <br><br>
            <b>Penyerah</b><br>
            <br><br>
            <u><b>{$userName}</b></u><br>
            Dibuat : <i>{$tglBuat}</i><br>
            ";

            EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        });


        static::updated(function ($record) {
            $karyawanName = $record->karyawan->nama ?? '-';
            $karyawanEmail = $record->karyawan->email ?? '-';
            $karyawanJob = $record->karyawan->job_title ?? '-';
            $userName = $record->user->name ?? '-';
            $emailcc = $record->user->email ?? '-';
            $tglPinjam = DateHelper::formatIndonesianDate($record->tgl_pinjam);
            $tglKembali = DateHelper::formatIndonesianDate($record->tgl_kembali);
            $tglUpdate = DateHelper::formatIndonesianDate($record->updated_at);

            $items = $record->items;
            $itemDetailsRows = '';

            foreach ($items as $item) {
                $perangkatNama = $item->perangkat->nama ?? '-';
                $serialNumber = $item->perangkat->serial_number ?? '-';
                $itemDetailsRows .= "
                    <tr>
                        <td width=25%>{$perangkatNama} {$serialNumber}</td>
                    </tr>
                ";
            }

            $to = $karyawanEmail; // Ganti dengan email penerima
            $toName = $karyawanName;
            $fromName = $userName;
            $cc = $emailcc;
            $ccName = $userName;
            $subject = 'Pengembalian Perangkat';

            $body = "
            <p>Dear Bpk/Ibu/Sdr/i <b>{$karyawanName}</b>,</p>
            <p>Job Title: <b>{$karyawanJob}</b></p>
            <p>Berikut adalah data peminjaman perangkat Anda:</p>
            <table>
            <tbody>
                <tr>
                    <td width=8%><b>Nama Perangkat - Serial Number</b></td>
                    <td width=2%>:</td>
                </tr>
                    {$itemDetailsRows}
                <tr>
                    <td width=8%><b>Tanggal Pinjam | Tanggal Kembali</b></td>
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
            <b>Penerima</b><br>
            <br>
            <br>
            <u><b>{$userName}</b></u><br>
            Update : <i>{$tglUpdate}</i><br>
            ";

            // EmailHelper::sendEmail($to, $subject, $body);
            EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        });
    }
}
