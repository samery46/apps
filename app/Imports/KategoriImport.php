<?php

namespace App\Imports;

use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class KategoriImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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
        // $tglPerolehan = $row['tgl_perolehan'] ? Carbon::parse($row['tgl_perolehan'])->format('Y-m-d') : null;

        return new Kategori([
            'kode' => $row['kode'],
            'nama' => $row['nama'],
            'keterangan' => $row['keterangan'],
            'kategori_id' => $row['kategori_id'],
            'is_barang' => $row['is_barang'],
            'satuan' => $row['satuan'],
            'stok_minimum' => $row['stok_minimum'],
            'is_aktif' => $row['is_aktif'],
        ]);
    }

    public function rules(): array
    {
        return [
            // '*.kategori_id' => 'required|exists:plants,id',
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
