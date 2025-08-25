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

        // LibreOffice command
        $command = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf --outdir "' . storage_path('app') . '" "' . $tempPath . '"';
        exec($command, $output, $resultCode);

        if (!file_exists($pdfPath)) {
            // Clean up Word file if conversion failed
            if (file_exists($tempPath))
                unlink($tempPath);
            return back()->with('error', 'PDF conversion failed.');
        }

        if (file_exists($tempPath))
            unlink($tempPath);

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

}
