<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExcelToPDFController extends Controller {

    public function index(Request $request) {
        return view('user.excel2PDF');
    }

    public function excelToPdf(Request $request) {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:20480', // Max 20MB
        ]);

        $excelFile = $request->file('file');
        $fileName = time() . '.' . $excelFile->getClientOriginalExtension();
        $excelPath = storage_path('app/' . $fileName);
        $excelFile->move(storage_path('app'), $fileName);

        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $pdfPath = storage_path("app/{$baseName}.pdf");

        // Convert using LibreOffice
        $command = '"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf --outdir "' . storage_path('app') . '" "' . $excelPath . '"';
        exec($command . ' 2>&1', $output, $resultCode);

        // Delete Excel file
        if (file_exists($excelPath)) {
            unlink($excelPath);
        }

        // Check for output
        if (!file_exists($pdfPath)) {
            return back()->with('error', 'Conversion failed. LibreOffice output: ' . implode("\n", $output));
        }

        // Download and auto-delete after
        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

}
