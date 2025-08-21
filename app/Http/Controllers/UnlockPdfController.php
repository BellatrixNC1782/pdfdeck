<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TcpdiHelper;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class UnlockPdfController extends Controller {

    public function showForm() {
        return view('user.unlockpdf');
    }

    public function download(Request $request) {
        $request->validate([
            'pdfs.*' => 'required|file|mimes:pdf|max:10240',
            'file_rotations' => 'required|string',
            'file_names_order' => 'required|string',
            'passwords' => 'nullable|string',
        ]);

        $rotations = explode(',', $request->input('file_rotations'));
        $fileNames = explode(',', $request->input('file_names_order'));
        $passwords = explode('|:|', $request->input('passwords'));
        $uploadedFiles = $request->file('pdfs');

        $processed = [];

        foreach ($uploadedFiles as $index => $file) {
            $rotation = ((int) $rotations[$index]) % 360;
            $password = $passwords[$index] ?? null;
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $outputName = $originalName . '_unlocked.pdf';
            $outputPath = storage_path('app/' . Str::random(10) . '.pdf');

            try {
                $pdf = new TcpdiHelper();

                // set the password only if needed
                $pageCount = $pdf->setSourceFile($file->getRealPath(), $password);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $tpl = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($tpl);
                    $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
                    $pdf->setPageRotationCompat($rotation);
                }

                // Save new unlocked PDF
                $pdf->Output($outputPath, 'F');
                $processed[] = ['path' => $outputPath, 'name' => $outputName];
            } catch (\Throwable $e) {
                $fallback = storage_path('app/' . Str::random(10) . '_original.pdf');
                copy($file->getRealPath(), $fallback);
                $processed[] = [
                    'path' => $fallback,
                    'name' => $originalName . '_as_is.pdf',
                    'error' => $e->getMessage(), // optional: log this for debug
                ];
            }
        }

        // Return single file or ZIP
        if (count($processed) === 1) {
            return response()->download($processed[0]['path'], $processed[0]['name'])->deleteFileAfterSend(true);
        }

        $zipName = 'unlocked_pdfs_' . uniqid() . '.zip';
        $zipPath = storage_path('app/' . $zipName);
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($processed as $file) {
                $zip->addFile($file['path'], $file['name']);
            }
            $zip->close();

            foreach ($processed as $file) {
                unlink($file['path']);
            }

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        }

        return back()->withErrors(['pdfs' => 'Failed to create ZIP archive.']);
    }

}
