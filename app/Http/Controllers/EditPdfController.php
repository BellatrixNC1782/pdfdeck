<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;

class EditPdfController extends Controller
{
    public function index() {
        return view('user.edit-pdf');
    }

    public function upload(Request $request) {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10240',
        ]);

        $file = $request->file('pdf');
        $name = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $name);

        return response()->json([
                    'url' => asset('uploads/' . $name)
        ]);
    }

}
