<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class AssetsImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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
        $tglPerolehan = $row['tgl_perolehan'] ? Carbon::parse($row['tgl_perolehan'])->format('Y-m-d') : null;

        return new Asset([

            'nomor' => $row['nomor'],
            'sub' => $row['sub'],
            'tipe' => $row['tipe'],
            'plant_id' => $row['plant_id'],
            'nama' => $row['nama'],
            'tgl_perolehan' => $tglPerolehan,
            'harga' => $row['harga'],
            'nbv' => $row['nbv'],
            'serial_number' => $row['serial_number'],
            'status' => $row['status'],
            'qty_sap' => $row['qty_sap'],
            'qty_aktual' => $row['qty_aktual'],
            'kondisi' => $row['kondisi'],
            'karyawan_id' => $row['karyawan_id'],
            'lokasi' => $row['lokasi'],
            'keterangan' => $row['keterangan'],
            'foto' => $row['foto'],
            'user_id' => $row['user_id'],
            'is_aktif' => $row['is_aktif'],
        ]);
    }

    public function rules(): array
    {
        return [
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
