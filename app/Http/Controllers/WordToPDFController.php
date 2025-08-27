<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WordToPDFController extends Controller {

    public function index(Request $request) {
        return view('user.word2pdf');
    }

    public function store(Request $request) {
        $request->validate([
            'file' => 'required|mimes:doc,docx|max:10240'
        ]);

        $wordFile = $request->file('file');
        $originalName = time() . '.' . $wordFile->getClientOriginalExtension();
        $tempPath = storage_path('app/' . $originalName);

        // Move to temp path for conversion
        $wordFile->move(storage_path('app/'), $originalName);

        $pdfName = pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = storage_path('app/' . $pdfName);

        // Detect OS and build command
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $command = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf --outdir "' . storage_path('app') . '" "' . $tempPath . '"';
            exec($command, $output, $resultCode);
        } else {
            // Linux / Ubuntu
            $soffice = '/usr/bin/libreoffice';
            $command = 'HOME=/tmp ' . $soffice
                    . ' --headless --nologo --convert-to pdf --outdir "'
                    . storage_path('app') . '" "'
                    . $tempPath . '"';
            exec($command . ' 2>&1', $output, $resultCode);
        }

        if (!file_exists($pdfPath)) {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            return back()->with('error', 'PDF conversion failed.');
        }

        // Delete temp word file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // Download PDF
        return response()->download($pdfPath, $pdfName, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
