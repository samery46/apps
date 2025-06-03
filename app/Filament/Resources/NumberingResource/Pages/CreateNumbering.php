<?php

namespace App\Filament\Resources\NumberingResource\Pages;

use App\Filament\Resources\NumberingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNumbering extends CreateRecord
{
    protected static string $resource = NumberingResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     $numbering = parent::handleRecordCreation($data);

    //     // Atur tanggal dan nomor transaksi
    //     $numbering->tgl = now();
    //     $numbering->transaction_number = NumberingResource::generateTransactionNumber($numbering->tgl, $numbering->departemen_id, $numbering->plant_id);
    //     $numbering->save();

    //     return $numbering;
    // }
}
