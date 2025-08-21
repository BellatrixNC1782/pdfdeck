<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use ZipArchive;
use File;

class PdfNumberController extends Controller {

    public function showForm() {
        return view('user.add_pagenumber');
    }

    public function process(Request $request) {
        $request->validate([
            'pdfs' => 'required',
            'pdfs.*' => 'file|mimes:pdf',
            'position' => 'required',
        ]);

        $uploadedFiles = $request->file('pdfs');
        $position = $request->position;
        $firstNumber = (int) ($request->first_number ?? 1);
        $template = $request->text_template ?? '{n}';
        $range = $request->page_range ?? '';

        $processedFiles = [];

        foreach ($uploadedFiles as $file) {
            $pdf = new Fpdi();
//            $totalPages = $pdf->setSourceFile($file->getPathname());
            
            $decompressed = self::decompressPdf($file->getPathname());
            $totalPages = $pdf->setSourceFile($decompressed);

            $number = $firstNumber;

            for ($i = 1; $i <= $totalPages; $i++) {
                if ($range && !self::pageInRange($i, $range)) {
                    $templatePageId = $pdf->importPage($i);
                    $pdf->AddPage();
                    $pdf->useTemplate($templatePageId);
                    continue;
                }

                $templatePageId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templatePageId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templatePageId);

                $pdf->SetFont('Helvetica');
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFontSize(10);

                $text = str_replace('{n}', $number, $template);
                $x = $position == 'bottom_right' ? $size['width'] - 30 :
                        ($position == 'bottom_left' ? 10 :
                        ($position == 'top_right' ? $size['width'] - 30 : 10));
                $y = in_array($position, ['bottom_right', 'bottom_left']) ? $size['height'] - 10 : 10;

                $pdf->Text($x, $y, $text);
                $number++;
            }

            $newFileName = 'numbered_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf';
            $newFilePath = storage_path("app/public/{$newFileName}");
            $pdf->Output($newFilePath, 'F');
            $processedFiles[] = $newFilePath;
        }

        // Single PDF case
        if (count($processedFiles) === 1) {
            $response = response()->download($processedFiles[0])->deleteFileAfterSend(true);
            $response->headers->set('Refresh', '3;url=' . route('addpagenumbers'));
            return $response;
        }

        // Multiple PDF case: create ZIP
        $zipFileName = 'numbered_pdfs_' . time() . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->withErrors(['Unable to create ZIP archive']);
        }

        foreach ($processedFiles as $filePath) {
            $zip->addFile($filePath, basename($filePath));
        }

        $zip->close();

        $response = response()->download($zipPath)->deleteFileAfterSend(true);
        $response->headers->set('Refresh', '3;url=' . route('addpagenumbers'));
        return $response;
    }

    private static function pageInRange($page, $range) {
        $segments = explode(',', $range);
        foreach ($segments as $segment) {
            if (strpos($segment, '-') !== false) {
                [$start, $end] = explode('-', $segment);
                if ($page >= (int) $start && $page <= (int) $end)
                    return true;
            } else {
                if ($page == (int) $segment)
                    return true;
            }
        }
        return false;
    }

    public static function decompressPdf($inputPath) {
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
