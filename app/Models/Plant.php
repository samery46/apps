<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\EmailHelper;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'kota',
        'alamat',
        'pos',
        'telp',
        'company_id',
        'is_aktif'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Relasi many-to-many dengan User
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected static function booted()
    {
        static::created(function ($plant) {
            $to = 'sam@ketik-kan.com'; // Ganti dengan email penerima
            $toName = '';
            $cc = '';
            $ccName = '';
            $fromName = '';

            $subject = 'New Plant Created';
            $body = "New Plant with kode: {$plant->kode} and nama: {$plant->nama} has been created.";

            EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        });

        static::updated(function ($plant) {
            $to = 'sam@ketik-kan.com'; // Ganti dengan email penerima
            $toName = '';
            $cc = '';
            $ccName = '';
            $fromName = '';
            $subject = 'Plant Data Updated';
            $body = "Plant with kode: {$plant->kode} and nama: {$plant->nama} has been updated.";

            EmailHelper::sendEmail($to, $cc, $subject, $body, $toName, $ccName, $fromName);
        });
    }
}
