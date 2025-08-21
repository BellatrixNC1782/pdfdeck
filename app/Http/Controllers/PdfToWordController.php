<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

class PdfToWordController extends Controller {

    public function index() {
        return view('user.pdf2word');     // Blade below
    }

    public function run(Request $req) {
        $req->validate([
            'pdfs' => 'required',
            'pdfs.*' => 'file|mimes:pdf|max:30720', // 30 MB each
        ]);

        $uploads = $req->file('pdfs');
        $outputs = [];

        // Detect LibreOffice executable
        $soffice = stripos(PHP_OS, 'WIN') !== false ? 'soffice.exe' : 'soffice';
        $soffice = escapeshellcmd($soffice);   // safe

        foreach ($uploads as $file) {

            $src = $file->getRealPath();
            $outDir = storage_path('app');      // write docx here
            // 1️⃣  convert
            $cmd = sprintf(
                    '%s --headless --convert-to docx:"MS Word 2007 XML" --outdir %s %s',
                    $soffice,
                    escapeshellarg($outDir),
                    escapeshellarg($src)
            );

            $proc = Process::fromShellCommandline($cmd);
            $proc->run();

            // 2️⃣  locate output file
            $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $docx = $outDir . '/' . $base . '.docx';

            // LibreOffice sometimes appends ".pdf.docx"; handle that
            if (!file_exists($docx)) {
                $alt = $outDir . '/' . $base . '.pdf.docx';
                if (file_exists($alt))
                    $docx = $alt;
            }

            // Rename to unique file
            if (file_exists($docx)) {
                $unique = storage_path('app/' . Str::random(6) . '_' . $base . '.docx');
                rename($docx, $unique);
                $outputs[] = $unique;
            }
        }

        if (!$outputs) {
            return back()->withErrors('Conversion failed ‑ check LibreOffice installation.');
        }

        /* return single docx or zip */
        if (count($outputs) === 1) {
            return response()->download($outputs[0])->deleteFileAfterSend(true);
        }

        $zipPath = storage_path('app/docx_' . time() . '.zip');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        foreach ($outputs as $p)
            $zip->addFile($p, basename($p));
        $zip->close();
        foreach ($outputs as $p)
            @unlink($p);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

}
