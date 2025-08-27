<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PPToPDFController extends Controller {

    public function index(Request $request) {
        return view('user.pptopdf');
    }

    public function pptToPdf(Request $request) {
        $request->validate([
            'file' => 'required|mimes:ppt,pptx|max:20480', // Max 20MB
        ]);

        $pptFile = $request->file('file');
        $fileName = time() . '.' . $pptFile->getClientOriginalExtension();
        $pptPath = storage_path('app/' . $fileName);
        $pptFile->move(storage_path('app'), $fileName);

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $pdfPath = storage_path("app/{$baseName}.pdf");

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf --outdir "' . storage_path('app') . '" "' . $pptPath . '"';
        } else {
            $soffice = '/usr/bin/libreoffice';
            $command = 'HOME=/tmp ' . $soffice
                    . ' --headless --nologo --convert-to pdf --outdir "'
                    . storage_path('app') . '" "'
                    . $pptPath . '"';
        }
        exec($command . ' 2>&1', $output, $resultCode);

        // Clean up PPT file
        if (file_exists($pptPath)) {
            unlink($pptPath);
        }

        // Check if conversion succeeded
        if (!file_exists($pdfPath)) {
            return back()->with('error', 'Conversion failed. LibreOffice output: ' . implode("\n", $output));
        }

        // Return PDF for download
        return response()->download($pdfPath, $baseName . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend(true);
    }

}
