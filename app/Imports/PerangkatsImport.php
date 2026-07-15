<?php

namespace App\Imports;

use App\Models\Perangkat;
use App\Models\Plant;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class PerangkatsImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
{

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($row);
        return new Perangkat([
            'nama' => $row['nama'],
            'serial_number' => $row['serial_number'],
            'keterangan' => $row['keterangan'],
            'qty' => $row['qty'],
            'plant_id' => $row['plant_id'],
            'is_aktif' => true
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nama' => 'required|string',
            '*.plant_id' => 'required|exists:plants,id',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.required' => 'Kolom :attribute harus diisi.',
            '*.exists' => 'Nilai :attribute tidak valid.',
            '*.integer' => 'Kolom :attribute harus berupa angka.',
            '*.numeric' => 'Kolom :attribute harus berupa angka.',
            '*.min' => 'Kolom :attribute minimal harus :min.',
            '*.unique' => 'Nilai :attribute sudah ada sebelumnya.',
        ];
    }
}
