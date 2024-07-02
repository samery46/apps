<?php

namespace App\Imports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class KaryawansImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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
        $tglLahir = $row['tgl_lahir'] ? Carbon::parse($row['tgl_lahir'])->format('Y-m-d') : null;

        return new Karyawan([

            'nama' => $row['nama'],
            'nik' => $row['nik'],
            'job_title' => $row['job_title'],
            'email' => $row['email'],
            'uid_sap' => $row['uid_sap'],
            'user_ad' => $row['user_ad'],
            'computer_name' => $row['computer_name'],
            'tgl_lahir' => $tglLahir,
            'status' => $row['status'],
            'foto' => $row['foto'],
            'plant_id' => $row['plant_id'],
            'departemen_id' => $row['departemen_id'],
            'is_aktif' => $row['is_aktif'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.plant_id' => 'required|exists:plants,id',
            '*.departemen_id' => 'required|exists:departemens,id',
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
