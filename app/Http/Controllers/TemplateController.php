<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{

    public function importPinjams()
    {
        $filePath = 'public/templates/template_import_pinjam.xlsx';
        $fileName = 'template_import_pinjam.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function importKaryawans()
    {
        $filePath = 'public/templates/template_import_karyawan.xlsx';
        $fileName = 'template_import_karyawan.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function importPerangkats()
    {
        $filePath = 'public/templates/template_import_perangkat.xlsx';
        $fileName = 'template_import_perangkat.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function importAssets()
    {
        $filePath = 'public/templates/template_import_asset.xlsx';
        $fileName = 'template_import_asset.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function importUidsap()
    {
        $filePath = 'public/templates/template_import_uidsap.xlsx';
        $fileName = 'template_import_uidsap.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }

    public function importMaterial()
    {
        $filePath = 'public/templates/template_import_material.xlsx';
        $fileName = 'template_import_material.xlsx';

        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $fileName);
        }

        return redirect()->back()->with('error', 'File template tidak ditemukan.');
    }
}
