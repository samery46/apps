<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetRequest;
use Barryvdh\DomPDF\Facade\Pdf;


class AssetRequestExportController extends Controller
{
    public function export($id)
    {
        // $assetRequest = AssetRequest::with(['plant', 'assetGroup', 'costCenter', 'user'])->findOrFail($id);
        $assetRequest = AssetRequest::with(['plant', 'assetGroup', 'costCenter', 'user', 'approvals.user'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.asset-request-bulk', compact('assetRequest'))->setPaper('A4', 'landscape');
        return $pdf->download("AssetRequest_{$assetRequest->document_number}.pdf");
    }
}
