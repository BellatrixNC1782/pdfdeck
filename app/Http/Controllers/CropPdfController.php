<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CropPdfController extends Controller
{
    public function showForm()
    {
        return view('user.crop');
    }

    public function process(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf',
            'crop_data' => 'required',
        ]);

        $cropData = json_decode($request->input('crop_data'), true);
        $scope = $request->input('crop_scope', 'all');
        $pdf = $request->file('pdf');

        $filename = 'cropped_' . Str::random(8) . '.pdf';
        $outputPath = storage_path("app/public/$filename");

        $fpdi = new Fpdi();
        $pageCount = $fpdi->setSourceFile($pdf->getPathname());

        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($templateId);
            $dpiRatio = 72 / 96;

            if ($scope === 'all') {
                $crop = $cropData;
            } else {
                $crop = $cropData[$i] ?? null;
            }

            if ($crop && $crop['width'] > 0 && $crop['height'] > 0) {
    $x = $crop['x'] * $dpiRatio;
    $y = $crop['y'] * $dpiRatio;
    $w = $crop['width'] * $dpiRatio;
    $h = $crop['height'] * $dpiRatio;

    // Apply cropped dimensions
    $fpdi->AddPage('P', [$w, $h]);
    $fpdi->useTemplate($templateId, -$x, -$y);
} else {
    // Full page
    $fpdi->AddPage('P', [$size['width'], $size['height']]);
    $fpdi->useTemplate($templateId);
}


            $fpdi->AddPage();
            $fpdi->useTemplate($templateId, -$x, -$y, $size['width'], $size['height']);
        }

        $fpdi->Output($outputPath, 'F');

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
