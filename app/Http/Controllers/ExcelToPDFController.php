<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf as PdfWriter;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExcelToPDFController extends Controller
{
    public function index(Request $request)
    {
        return view('user.excel2pdf');
    }

    public function excelToPdf(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:20480',
        ]);

        $excelFile = $request->file('file');
        $fileName  = time() . '.' . $excelFile->getClientOriginalExtension();
        $filePath  = storage_path('app/' . $fileName);
        $excelFile->move(storage_path('app'), $fileName);

        // Load Excel
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Add borders (gridlines)
        $range = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Detect number of columns
        $highestColumn = $sheet->getHighestColumn();
        $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // Set page orientation based on number of columns
        $orientation = $colCount > 8 ? 'landscape' : 'portrait';
        $sheet->getPageSetup()->setOrientation(
            $orientation === 'landscape'
                ? \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                : \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
        );

        // Set paper size (A4)
        $sheet->getPageSetup()->setPaperSize(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
        );

        // Save PDF
        $pdfFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = storage_path('app/' . $pdfFileName);

        $writer = new PdfWriter($spreadsheet);
        $writer->save($pdfPath);

        // Delete original Excel
        unlink($filePath);

        // Download PDF
        return response()->download($pdfPath, $pdfFileName, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
