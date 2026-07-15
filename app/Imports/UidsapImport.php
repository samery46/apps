<?php

namespace App\Imports;

use App\Models\Uidsap;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class UidsapImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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

        // Periksa apakah nilai tanggal tidak kosong dan valid
        $validFrom = $row['valid_from'] ? Carbon::parse($row['valid_from'])->format('Y-m-d') : null;
        $validEnd = $row['valid_end'] ? Carbon::parse($row['valid_end'])->format('Y-m-d') : null;


        return new Uidsap([
            'username' => $row['username'],
            'karyawan_id' => $row['karyawan_id'],
            'valid_from' => $validFrom,
            'valid_end' => $validEnd,
            'cost_center' => $row['cost_center'],
            'keterangan' => $row['keterangan'],
            'user_id' => $row['user_id'],
            'is_aktif' => $row['is_aktif'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.karyawan_id' => 'required|exists:karyawans,id',
            '*.user_id' => 'required|exists:users,id',
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
