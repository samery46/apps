<?php

use App\Http\Controllers\TemplateController;
use App\Imports\AssetsImport;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/import-asset', function () {
//     return Excel::download(new AssetsImport, 'template_import_asset.xlsx');
// })->name('import-asset');

Route::get(
    '/import-pinjams',
    [TemplateController::class, 'importPinjams']
)->name('import-pinjams');

Route::get(
    '/import-karyawans',
    [TemplateController::class, 'importKaryawans']
)->name('import-karyawans');

Route::get(
    '/import-perangkats',
    [TemplateController::class, 'importPerangkats']
)->name('import-perangkats');

Route::get(
    '/import-assets',
    [TemplateController::class, 'importAssets']
)->name('import-assets');

Route::get(
    '/import-uidsap',
    [TemplateController::class, 'importUidsap']
)->name('import-uidsap');

Route::get(
    '/import-material',
    [TemplateController::class, 'importMaterial']
)->name('import-material');

Route::get(
    '/import-software',
    [TemplateController::class, 'importSoftware']
)->name('import-software');


Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});
