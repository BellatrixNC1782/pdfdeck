<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;
use App\Helpers\TcpdiHelper;

class CompressController extends Controller {

    public function index() {
        return view('user.compress'); // Your Blade form
    }

    public function run(Request $req) {
        $req->validate([
            'pdfs' => 'required',
            'pdfs.*' => 'file|mimes:pdf|max:20480', // 20MB
            'rotations' => 'required|string'           // e.g., "0,90,180"
        ]);

        $angles = array_map('intval', explode(',', $req->rotations));
        $uploads = $req->file('pdfs');
        $outputs = [];

        foreach ($uploads as $idx => $file) {
            $src = $file->getRealPath();
            $angle = $angles[$idx] ?? 0;

            $pdf = new TcpdiHelper();

            $pageCount = $pdf->setSourceFile($src);
            for ($n = 1; $n <= $pageCount; $n++) {
                $tpl = $pdf->importPage($n);
                $size = $pdf->getTemplateSize($tpl);

                $pdf->AddPage('P', [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);
                $pdf->setPageRotationCompat($angle);
            }

            $final = storage_path('app/' . Str::random(6) . '_' .
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                    '_rotated.pdf');

            $pdf->Output($final, 'F');
            $outputs[] = $final;
        }

        if (count($outputs) === 1) {
            return response()->download($outputs[0])->deleteFileAfterSend(true);
        }

        $zipPath = storage_path('app/rotated_' . time() . '.zip');
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
