<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;

class HomeController extends Controller {

    public function index() {
        return view('user.home');
    }

}
