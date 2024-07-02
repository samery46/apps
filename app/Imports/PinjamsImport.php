<?php

namespace App\Imports;

use App\Models\Pinjam;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class PinjamsImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
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
        $tglPinjam = $row['tgl_pinjam'] ? Carbon::parse($row['tgl_pinjam'])->format('Y-m-d') : null;
        $tglKembali = $row['tgl_kembali'] ? Carbon::parse($row['tgl_kembali'])->format('Y-m-d') : null;


        return new Pinjam([
            'karyawan_id' => $row['karyawan_id'],
            'tgl_pinjam' => $tglPinjam,
            'tgl_kembali' => $tglKembali,
            'user_id' => $row['user_id'],
            'keterangan' => $row['keterangan'],
            'is_complete' => $row['is_complete'],
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
