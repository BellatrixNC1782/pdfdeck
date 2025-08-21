<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class PdfToImageController extends Controller {

    public function showForm() {
        return view('user.pdf2jpg');
    }

    public function convert(Request $request) {
        $request->validate([
            'pdfs.*' => 'required|file|mimes:pdf',
        ]);

        $rotateData = $request->input('rotate', []);
        $files = $request->file('pdfs');
        $tempDir = storage_path('app/temp/' . Str::random(10));
        mkdir($tempDir, 0755, true);

        $allImages = [];

        foreach ($files as $i => $pdf) {
            $pdfPath = $pdf->getRealPath();
            $rotation = isset($rotateData[$pdf->getClientOriginalName()]) ? (int) $rotateData[$pdf->getClientOriginalName()] : 0;

            $filenameBase = 'page_' . time() . "_$i";
            $outputPattern = $tempDir . '/' . $filenameBase . '_%03d.jpg';

            $gsPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"' : 'gs';
            $cmd = "$gsPath -dNOPAUSE -dBATCH -sDEVICE=jpeg -r150 -sOutputFile=\"$outputPattern\" \"$pdfPath\"";

            exec($cmd . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                \Log::error("Ghostscript error", ['file' => $pdf->getClientOriginalName(), 'output' => $output]);
                continue;
            }

            // Get generated image(s)
            $converted = glob($tempDir . '/' . $filenameBase . '_*.jpg');

            // Apply rotation via Imagick
            $imageManager = new ImageManager(new GdDriver());
            foreach ($converted as $imgPath) {
                if ($rotation !== 0) {
                    $image = $imageManager->read($imgPath)->rotate(360 - $rotation);
                    $image->toJpeg(quality: 90)->save($imgPath);
                }
            }


            $allImages = array_merge($allImages, $converted);
        }

        if (count($allImages) === 0) {
            return back()->withErrors(['error' => 'No PDFs were successfully converted.']);
        }

        // One image: direct download
        if (count($allImages) === 1) {
            return response()->download($allImages[0], basename($allImages[0]))->deleteFileAfterSend(true);
        }

        // Zip multiple images
        $zipName = 'pdf_images_' . time() . '.zip';
        $zipPath = $tempDir . '/' . $zipName;
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($allImages as $img) {
                $zip->addFile($img, basename($img));
            }
            $zip->close();
        }

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

}
