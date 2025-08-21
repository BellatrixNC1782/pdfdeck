<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TcpdiHelper;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Illuminate\Support\Str;
use ZipArchive;

class RotatePdfController extends Controller {

    public function showForm() {
        return view('user.rotate-pdf');
    }

    public function downloadRotatePdf(Request $request) {
        $request->validate([
            'pdfs.*' => 'required|file|mimes:pdf|max:10240',
            'file_rotations' => 'required|string',
            'file_names_order' => 'required|string',
        ]);

        $rotations = explode(',', $request->input('file_rotations'));
        $fileNames = explode(',', $request->input('file_names_order'));
        $uploadedFiles = $request->file('pdfs');

        if (count($uploadedFiles) !== count($rotations)) {
            return back()->withErrors(['pdfs' => 'Mismatch between files and rotations.']);
        }

        $rotatedPaths = [];

        foreach ($uploadedFiles as $index => $file) {
            $rotation = ((int) $rotations[$index]) % 360;
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = 'rotated_' . Str::random(8) . '.pdf';
            $path = storage_path('app/' . $filename);

            $srcPath = $file->getRealPath();
            $triedFix = false;

            retry:
            try {
                $pdf = new TcpdiHelper();
                $pageCount = $pdf->setSourceFile($srcPath);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);
                    $pdf->setPageRotationCompat($rotation); // same as in version 1
                }

                $pdf->Output($path, 'F');
                $rotatedPaths[] = ['path' => $path, 'name' => $originalName . '_rotated.pdf'];
            } catch (\Throwable $e) {
                if (!$triedFix) {
                    $srcPath = $this->preprocessPdf($srcPath);
                    $triedFix = true;
                    goto retry;
                } else {
                    return back()->withErrors([
                                'pdfs' => 'One or more PDF files could not be processed.',
                    ]);
                }
            }
        }

        // Only one file
        if (count($rotatedPaths) === 1) {
            return response()->download($rotatedPaths[0]['path'], $rotatedPaths[0]['name'])->deleteFileAfterSend(true);
        }

        // Zip multiple files
        $zipName = 'rotated_pdfs_' . uniqid() . time() . '.zip';
        $zipPath = storage_path('app/' . $zipName);
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($rotatedPaths as $file) {
                $zip->addFile($file['path'], $file['name']);
            }
            $zip->close();

            foreach ($rotatedPaths as $file) {
                unlink($file['path']);
            }

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return back()->withErrors(['pdfs' => 'Zip creation failed.']);
    }

    private function preprocessPdf(string $inputPath): string {
        $outputPath = storage_path('app/fixed_' . uniqid() . '.pdf');
        $gsPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"' : 'gs';

        $cmd = "$gsPath -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($outputPath) . " " . escapeshellarg($inputPath);

        exec($cmd, $output, $code);
        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException("Ghostscript failed to preprocess PDF: " . implode("\n", $output));
        }

        return $outputPath;
    }

}
