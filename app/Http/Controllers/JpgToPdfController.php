<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use PDF;

class JpgToPdfController extends Controller {

    public function showForm() {
        return view('user.jpg2pdf');
    }

    public function convert(Request $request) {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,jpg,png',
        ]);

        $rotationData = collect(json_decode($request->input('rotation_data', '[]'), true))->keyBy('name');

        $uploadedImages = $request->file('images');
        $imageDataUrls = [];

        $manager = new ImageManager(new GdDriver());

        foreach ($uploadedImages as $i => $image) {
            $rotation = 0;
            $uploadedName = $image->getClientOriginalName();

            if ($rotationData->has($uploadedName)) {
                $rotation = $rotationData->get($uploadedName)['rotation'] ?? 0;
            }

            $img = $manager->read($image->getRealPath());

            $maxWidth = 550;
            $maxHeight = 750;

            // First: Resize image to fit inside A4 bounds
            $img->resize($maxWidth, $maxHeight, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Second: Rotate after resizing
            if ($rotation !== 0) {
                $img = $img->rotate(-$rotation);

                // Swap canvas dimensions if rotation is 90/270
                if (in_array($rotation % 360, [90, 270])) {
                    $canvasWidth = $img->height();
                    $canvasHeight = $img->width();
                } else {
                    $canvasWidth = $img->width();
                    $canvasHeight = $img->height();
                }

                // Optional: shrink canvas if larger than max A4 size
                $canvasWidth = min($canvasWidth, $maxWidth);
                $canvasHeight = min($canvasHeight, $maxHeight);

                $newCanvas = $manager->create($canvasWidth, $canvasHeight, 'ffffff');
                $newCanvas->place($img, 'center');
                $img = $newCanvas;
            }

            $encoded = $img->toJpeg()->toString();
            $base64 = base64_encode($encoded);
            $mime = 'image/jpeg';

            $imageDataUrls[] = [
                'src' => "data:$mime;base64,$base64",
                'rotation' => $rotation % 360
            ];
        }

        $pdf = PDF::loadView('user.pdf_template', ['imageDataUrls' => $imageDataUrls])
                ->setPaper('a4', 'portrait');

        return $pdf->download('converted_' . time() . '.pdf');
    }

}
