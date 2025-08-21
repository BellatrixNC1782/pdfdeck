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

        $command = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf --outdir "' . storage_path('app') . '" "' . $pptPath . '"';
        exec($command . ' 2>&1', $output, $resultCode);

        if (file_exists($pptPath)) {
            unlink($pptPath);
        }

        if (!file_exists($pdfPath)) {
            return back()->with('error', 'Conversion failed. LibreOffice output: ' . implode("\n", $output));
        }

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

}
