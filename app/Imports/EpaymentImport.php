<?php

namespace App\Imports;

use App\Models\Epayment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EpaymentImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $key = implode('|', [
            $row['bill_to'],
            $row['vendor'],
            $row['ticket_no'],
            $row['po_no'],
            $row['sj_no'],
            $row['gr_no'],
        ]);

        // return new Epayment([
        //     //
        // ]);


        return Epayment::updateOrCreate(
            ['composite_key' => $key],
            [
                'bill_to' => $row['bill_to'],
                'bill_to_name' => $row['bill_to_name'],
                'vendor' => $row['vendor'],
                'vendor_name' => $row['vendor_name'],
                'ticket_no' => $row['ticket_no'],
                'ticket_date' => Carbon::parse($row['ticket_date']),
                'invoice_no' => $row['invoice_no'],
                'invoice_date' => $this->parseDate($row['invoice_date']),
                'invoice_amount' => $this->parseAmount($row['invoice_amount']),
                'tax_invoice' => $row['tax_invoice'],
                'tax_invoice_date' => $this->parseDate($row['tax_invoice_date']),
                'tax_amount' => $this->parseAmount($row['tax_amount']),
                'miro_no' => $row['miro_no'] ?? null,
                'miro_date' => $row['miro_date'] ? Carbon::parse($row['miro_date']) : null,
                'paid_date' => $row['paid_date'] ? Carbon::parse($row['paid_date']) : null,
                'po_no' => $row['po_no'],
                'sj_no' => $row['sj_no'],
                'gr_no' => $row['gr_no'],
                'amount' => str_replace(['.', ','], '', $row['amount']),
                'status' => $row['status'],
                'created_date' => Carbon::parse($row['created_date']),
                'created_time' => $row['created_time'],
                'confirm_by' => $row['confirm_by'],
                'composite_key' => $key,
            ]
        );
    }

    protected function parseDate($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    protected function parseAmount($value)
    {
        return $value ? floatval(str_replace(['.', ','], '', $value)) : null;
    }
}
