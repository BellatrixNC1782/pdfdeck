<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .image-page {
            text-align: center;
        }
        .image-page img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    @foreach ($imageDataUrls as $imageDataUrl)
        <div class="image-page" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
            <img src="{{ $imageDataUrl['src'] }}">
        </div>
    @endforeach
</body>
</html>
