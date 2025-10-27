<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\ProfileVisitor;
use App\Models\Appurls;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HomeController extends Controller {

    public function index(Request $request) {
        $response = Http::get('http://ip-api.com/json/' . $request->ip());
        if ($response->ok()) {
            $geo = $response->json();

            ProfileVisitor::create([
                'ip' => $request->ip(),
                'browser' => $request->header('User-Agent'),
                'system' => php_uname(),
                'country' => $geo['country'] ?? null,
                'country_code' => $geo['countryCode'] ?? null,
                'region' => $geo['regionName'] ?? null,
                'city' => $geo['city'] ?? null,
                'timezone' => $geo['timezone'] ?? null,
                'lat' => is_numeric($geo['lat'] ?? null) ? floatval($geo['lat']) : null,
                'lng' => is_numeric($geo['lon'] ?? null) ? floatval($geo['lon']) : null,
                'isp' => $geo['isp'] ?? null,
                'organization' => $geo['org'] ?? null,
                'guid' => \Str::uuid(),
                'page_type' => 'home',
            ]);
        }
        return view('user.home');
    }

}
