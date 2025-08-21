<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use ZipArchive;

class SplitController extends Controller {

    public function splitPdf() {
        return view('user.split');   // Blade file below
    }

    /* ---------- Split logic ---------- */

    public function splitPdfrocess(Request $request) {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240',
            'ranges_json' => 'required|string',
            'merge_output' => 'nullable|boolean',
        ]);

        $ranges = json_decode($request->input('ranges_json'), true);
        $mergeOutput = $request->boolean('merge_output');
        $file = $request->file('pdf');
        $originalPath = $file->getRealPath();

        // ✅ Preprocess to make it FPDI-compatible
        $srcPath = $this->preprocessPdf($originalPath);

        // Safety: ensure ranges exist
        if (!is_array($ranges) || count($ranges) === 0) {
            return back()->withErrors('Please add at least one valid page range.');
        }

        /* ---------- Split each range ---------- */
        $splitPaths = [];
        $fpdi = new Fpdi();
        $pageTotal = $fpdi->setSourceFile($srcPath);  // only to read page count

        foreach ($ranges as $idx => $range) {
            [$from, $to] = $range;
            $from = max(1, (int) $from);
            $to = min($pageTotal, (int) $to);
            if ($from > $to)
                continue;

            $outPdf = new Fpdi();
            $outPdf->SetMargins(0, 0, 0);
            $outPdf->SetAutoPageBreak(false, 0);
            $outPdf->setSourceFile($srcPath);

            for ($p = $from; $p <= $to; $p++) {
                $tpl = $outPdf->importPage($p, '/MediaBox');
                $size = $outPdf->getTemplateSize($tpl);
                $outPdf->AddPage('P', [$size['width'], $size['height']]);
                $outPdf->useTemplate($tpl);
            }

            $name = "split_part_" . ($idx + 1) . "_" . Str::random(6) . ".pdf";
            $path = storage_path("app/$name");
            $outPdf->Output($path, 'F');
            $splitPaths[] = $path;
        }

        /* ---------- Return result ---------- */
        if ($mergeOutput && count($splitPaths) > 1) {
            $merged = new Fpdi();
            foreach ($splitPaths as $p) {
                $pgs = $merged->setSourceFile($p);
                for ($i = 1; $i <= $pgs; $i++) {
                    $tpl = $merged->importPage($i);
                    $size = $merged->getTemplateSize($tpl);
                    $merged->AddPage('P', [$size['width'], $size['height']]);
                    $merged->useTemplate($tpl);
                }
            }
            $finalName = 'split_merged_' . time() . '.pdf';
            $finalPath = storage_path("app/$finalName");
            $merged->Output($finalPath, 'F');

            // cleanup
            foreach ($splitPaths as $p)
                unlink($p);

            return response()->download($finalPath)->deleteFileAfterSend(true);
        }

        /* zip individual parts */
        if (count($splitPaths) === 1) {
            return response()->download($splitPaths[0])->deleteFileAfterSend(true);
        }

        $zipName = 'split_parts_' . time() . '.zip';
        $zipPath = storage_path("app/$zipName");
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($splitPaths as $p) {
                $zip->addFile($p, basename($p));
            }
            $zip->close();
        }
        foreach ($splitPaths as $p)
            unlink($p);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function preprocessPdf(string $inputPath): string {
        $outputPath = storage_path('app/fixed_' . uniqid() . '.pdf');
        $gsPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? '"C:\\Program Files\\gs\\gs10.05.1\\bin\\gswin64c.exe"' : 'gs';

        $cmd = "$gsPath -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/printer -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($outputPath) . " " . escapeshellarg($inputPath);
        exec($cmd, $output, $code);

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException("Ghostscript failed to preprocess PDF: " . implode("\n", $output));
        }

        return $outputPath;
    }

}
