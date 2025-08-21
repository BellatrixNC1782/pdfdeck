<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TcpdiHelper;

class PdfController extends Controller {

    public function mergePdf() {
        return view('user.merge');
    }

    public function downloadMergePdf(Request $request) {
        $request->validate([
            'pdfs' => 'required',
            'pdfs.*' => 'mimes:pdf|max:10240',
            'file_names_order' => 'required|string',
            'file_rotations' => 'required|string',
        ]);

        $ordered = explode(',', $request->input('file_names_order'));
        $rotations = array_map('intval', explode(',', $request->input('file_rotations')));
        $uploaded = $request->file('pdfs');

        $queue = [];
        foreach ($ordered as $idx => $name) {
            foreach ($uploaded as $file) {
                if ($file->getClientOriginalName() === $name) {
                    $queue[] = [
                        'file' => $file,
                        'rotation' => $rotations[$idx] ?? 0,
                    ];
                    break;
                }
            }
        }

        $pdf = new TcpdiHelper();

        foreach ($queue as $item) {
            $srcPath = $item['file']->getRealPath();
            $cleanPath = $this->preprocessPdf($srcPath);

            $rotateDeg = $item['rotation'];
            $pageCount = $pdf->setSourceFile($cleanPath);
//            $pageCount = $pdf->setSourceFile($srcPath);

            for ($n = 1; $n <= $pageCount; $n++) {
                $tpl = $pdf->importPage($n);
                $size = $pdf->getTemplateSize($tpl);

                // keep the **original** MediaBox – no swapping
                $pdf->AddPage('P', [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);

                // mark the page as rotated (iLovePDF style)
                $pdf->setPageRotationCompat($rotateDeg);
            }
            @unlink($cleanPath);
        }

        // --- save + return ----------------------------------------
        $fileName = 'merged_' . time() . '.pdf';
        $outputPath = storage_path("app/{$fileName}");

        $pdf->Output($outputPath, 'F');

        return response()->download($outputPath)->deleteFileAfterSend(true);
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
