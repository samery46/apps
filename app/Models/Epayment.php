<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Epayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_to',
        'bill_to_name',
        'vendor',
        'vendor_name',
        'ticket_no',
        'ticket_date',
        'miro_no',
        'miro_date',
        'paid_date',
        'po_no',
        'sj_no',
        'gr_no',
        'amount',
        'status',
        'created_date',
        'created_time',
        'confirm_by',
        'composite_key',
        'invoice_no',
        'invoice_date',
        'invoice_amount',
        'tax_invoice',
        'tax_invoice_date',
        'tax_amount',
    ];


    public function getSelisihAttribute()
    {
        $tanggal = $this->status === 'Confirm' ? $this->miro_date : $this->created_date;
        return $tanggal ? Carbon::parse($tanggal)->diffInDays(Carbon::now()) : null;
    }

    public function getKategoriAttribute()
    {
        $selisih = $this->selisih;

        if (is_null($selisih) || $selisih < 7) {
            return '1. 1-7';
        } elseif ($selisih < 14) {
            return '2. 7-14';
        } elseif ($selisih < 30) {
            return '3. 14-30';
        } else {
            return '4. >30';
        }
    }

}
