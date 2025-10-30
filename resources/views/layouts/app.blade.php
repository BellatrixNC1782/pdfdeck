<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>MyPDF Tool</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" href="{{ asset('public/favicon.ico') }}" type="image/x-icon">
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            .drag-handle {
                cursor: grab;
            }
            .drag-handle:active {
                cursor: grabbing;
            }

        </style>
        
    </head>
    <body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
        
        <?php $current_route = Route::currentRouteName();?>

        <!-- HEADER -->
        <!-- HEADER -->
        <header class="bg-gradient-to-r from-[#ba3336] to-[#114880] shadow fixed top-0 left-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

                <!-- Logo (Left) -->
                @if($current_route != 'home')
                <a href="{{ route('home') }}" class="text-2xl font-bold text-white flex items-center space-x-1 mr-8">
                    <span>❤️</span>
                    <span>MyPDF</span>
                </a>
                @else
                <a class="text-2xl font-bold text-white flex items-center space-x-1 mr-8">
                    <span>❤️</span>
                    <span>MyPDF</span>
                </a>
                @endif

                <!-- Desktop Nav (Right) -->
                <nav class="hidden md:flex items-center space-x-6 text-white font-medium text-sm lg:text-base whitespace-nowrap">
                    @php
                    $menu = [
                    'home' => 'Home',
                    'mergepdf' => 'Merge PDF',
                    'splitpdf' => 'Split PDF',
                    'wordtopdf' => 'Word to PDF',
                    'pptppdf' => 'Power Point to PDF',
                    'exceltppdf' => 'Excel to PDF',
                    'pdftojpg' => 'PDF to JPG',
                    'jpgtopdf' => 'JPG to PDF',
                    'rotatepdf' => 'Rotate PDF',
                    'protectpdf' => 'Protect PDF',
                    'addpagenumbers' => 'Page Numbers',
                    ];
                    @endphp

                    @foreach($menu as $route => $label)
                    <a href="{{ route($route) }}"
                       class="relative pb-1 transition-all duration-200 {{ request()->routeIs($route) 
                        ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                <!-- Mobile Menu Button (Right on small screens) -->
                <button id="menuBtn" class="md:hidden text-white text-2xl focus:outline-none">
                    ☰
                </button>
            </div>

            <!-- Mobile Nav -->
            <div id="mobileMenu" class="hidden bg-white text-gray-800 md:hidden px-6 py-4 space-y-3 shadow">
                @foreach($menu as $route => $label)
                <a href="{{ route($route) }}"
                   class="block py-2 px-2 rounded hover:bg-gray-100 {{ request()->routeIs($route) ? 'text-yellow-500 font-semibold' : '' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </header>

        <!-- MAIN -->
        <main class="pt-24 pb-20 flex-grow">
            @yield('content')
            <!-- Loader Overlay -->
            <div id="loaderOverlay" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="flex flex-col items-center">
                    <svg class="animate-spin h-12 w-12 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <p class="mt-3 text-white font-medium">Processing PDF...</p>
                </div>
            </div>

        </main>

        <!-- FOOTER -->
        <footer class="bg-gradient-to-r from-[#ba3336] to-[#114880] text-gray-100 py-10 mt-auto">
            <!--<footer class="bg-gray-900 text-gray-300 py-10 mt-auto">-->
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- About -->
                <div>
                    <h4 class="text-white text-lg font-semibold mb-3">MyPDF Tools</h4>
                    <p class="text-sm">Your all-in-one PDF toolbox. Merge, split, convert, protect, and more — everything you need to work with PDFs quickly, securely, and for free.</p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-white text-lg font-semibold mb-3">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('mergepdf') }}" class="hover:text-white">Merge PDF</a></li>
                        <li><a href="{{ route('splitpdf') }}" class="hover:text-white">Split PDF</a></li>
                        <li><a href="{{ route('wordtopdf') }}" class="hover:text-white">Word to PDF</a></li>
                        <li><a href="{{ route('pdftojpg') }}" class="hover:text-white">PDF to JPG</a></li>
                    </ul>
                </div>

                <!-- Contact / Social -->
                <div>
                    <h4 class="text-white text-lg font-semibold mb-3">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="mailto:business@bellatrixnc.com" class="text-yellow-500 hover:text-yellow-500">business@bellatrixnc.com</a>
                    </div>
                    <p class="mt-3 text-sm">© {{ date('Y') }} MyPDF Tools. All Rights Reserved.</p>
                </div>

            </div>
        </footer>

        <script>
// Mobile menu toggle
document.getElementById('menuBtn').addEventListener('click', () => {
    document.getElementById('mobileMenu').classList.toggle('hidden');
});
        </script>
    </body>
</html>
