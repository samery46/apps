<?php

namespace App\Imports;

use App\Models\Software;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class SoftwareImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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
        // Periksa apakah nilai tanggal tidak kosong dan valid
        $tanggal = $row['tgl'] ? Carbon::parse($row['tgl'])->format('Y-m-d') : null;

        return new Software([
            'plant_id' => $row['plant_id'],
            'nama' => $row['nama'],
            'tgl' => $tanggal,
            'srf' => $row['srf'],
            'karyawan_id' => $row['karyawan_id'],
            'keterangan' => $row['keterangan'],
            'user_id' => $row['user_id'],
            'is_aktif' => $row['is_aktif'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.plant_id' => 'required|exists:plants,id',
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
