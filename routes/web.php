<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SplitController;
use App\Http\Controllers\CompressController;
use App\Http\Controllers\PdfToWordController;
use App\Http\Controllers\WordToPDFController;
use App\Http\Controllers\PPToPDFController;
use App\Http\Controllers\ExcelToPDFController;
use App\Http\Controllers\EditPdfController;
use App\Http\Controllers\PdfToImageController;
use App\Http\Controllers\JpgToPdfController;
use App\Http\Controllers\RotatePdfController;
use App\Http\Controllers\ProtectPdfController;
use App\Http\Controllers\UnlockPdfController;
use App\Http\Controllers\PdfNumberController;
use App\Http\Controllers\CropPdfController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/mergepdf', [PdfController::class, 'mergePdf'])->name('mergepdf');
Route::post('/downloadmergepdf', [PdfController::class, 'downloadMergePdf'])->name('downloadmergepdf');

Route::get('/splitpdf', [SplitController::class, 'splitPdf'])->name('splitpdf');
Route::post('/splitpdfprocess', [SplitController::class, 'splitPdfrocess'])->name('splitpdfprocess');

// Not worked without ILovePDF API
Route::get ('/compress',      [CompressController::class, 'index'])->name('compress');
Route::post('/compress-run',  [CompressController::class, 'run'  ])->name('compress.run');

// Not worked without LibreOffice or ILovePDF API
Route::get ('/pdftoword',      [PdfToWordController::class,'index'])->name('pdftoword');
Route::post('/pdf-to-word/run',  [PdfToWordController::class,'run' ])->name('pdf2word.run');

Route::get('wordtopdf', [WordToPDFController::class, 'index'])->name('wordtopdf');
Route::post('word-to-pdf', [WordToPDFController::class, 'store'])->name('word.to.pdf.store');

Route::get('pptppdf', [PPToPDFController::class, 'index'])->name('pptppdf');
Route::post('/ppt-to-pdf', [PPToPDFController::class, 'pptToPdf'])->name('ppt2pdf');

Route::get('exceltppdf', [ExcelToPDFController::class, 'index'])->name('exceltppdf');
Route::post('/excel-to-pdf', [ExcelToPDFController::class, 'excelToPdf'])->name('excel2pdf');

Route::get('/edit-pdf', [EditPdfController::class, 'index'])->name('editpdf');
Route::post('/upload-pdf', [EditPdfController::class, 'upload'])->name('upload.pdf');

Route::get('/pdftojpg', [PdfToImageController::class, 'showForm'])->name('pdftojpg');
Route::post('/pdf-to-jpg', [PdfToImageController::class, 'convert'])->name('pdf.convert');

Route::get('/jpgtopdf', [JpgToPdfController::class, 'showForm'])->name('jpgtopdf');
Route::post('/jpg-to-pdf', [JpgToPdfController::class, 'convert'])->name('jpg.convert');

Route::get('/rotatepdf', [RotatePdfController::class, 'showForm'])->name('rotatepdf');
Route::post('/downloadrotatepdf', [RotatePdfController::class, 'downloadRotatePdf'])->name('downloadrotatepdf');

Route::get('/protectpdf', [ProtectPdfController::class, 'showForm'])->name('protectpdf');
Route::post('/protect-pdf/download', [ProtectPdfController::class, 'download'])->name('protectpdf.download');

// Not worked
Route::get('/unlockpdf', [UnlockPdfController::class, 'showForm'])->name('unlockpdf');
Route::post('/unlock-pdf/download', [UnlockPdfController::class, 'download'])->name('unlockpdf.download');

Route::get('/addpagenumbers', [PdfNumberController::class, 'showForm'])->name('addpagenumbers');
Route::post('/add-page-numbers', [PdfNumberController::class, 'process'])->name('pdf.page_numbers.process');

Route::get('/croppdf', [CropPdfController::class, 'showForm'])->name('croppdf');
Route::post('/crop-pdf', [CropPdfController::class, 'process'])->name('pdf.crop.process');