<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>MyPDF Tool</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        <!-- HEADER -->
        <header class="bg-gradient-to-r from-[#ba3336] to-[#114880] shadow fixed top-0 left-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="text-2xl font-bold text-white">❤️ MyPDF</a>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex space-x-6 text-white font-medium">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Home</a>
                    <a href="{{ route('mergepdf') }}" class="{{ request()->routeIs('mergepdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Merge PDF</a>
                    <a href="{{ route('splitpdf') }}" class="{{ request()->routeIs('splitpdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Split PDF</a>
                    <a href="{{ route('wordtopdf') }}" class="{{ request()->routeIs('wordtopdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Word to PDF</a>
                    <a href="{{ route('pptppdf') }}" class="{{ request()->routeIs('pptppdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Power Point to PDF</a>
                    <a href="{{ route('exceltppdf') }}" class="{{ request()->routeIs('pptppdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Excel to PDF</a>
                    <a href="{{ route('pdftojpg') }}" class="{{ request()->routeIs('pdftojpg') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">PDF to JPG</a>
                    <a href="{{ route('jpgtopdf') }}" class="{{ request()->routeIs('jpgtopdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">JPG to PDF</a>
                    <a href="{{ route('rotatepdf') }}" class="{{ request()->routeIs('rotatepdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Rotate PDF</a>
                    <a href="{{ route('addpagenumbers') }}" class="{{ request()->routeIs('addpagenumbers') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Page Numbers</a>
                    <!--<a href="{{ route('croppdf') }}" class="{{ request()->routeIs('croppdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}"> Crop PDF</a>-->
                    <!--<a href="{{ route('unlockpdf') }}" class="{{ request()->routeIs('unlockpdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Unlock PDF</a>-->
                    <!--<a href="{{ route('editpdf') }}" class="{{ request()->routeIs('editpdf') ? 'border-b-2 border-yellow-400 pb-1 text-yellow-400 font-bold hover:text-yellow-400' : 'hover:text-yellow-400 hover:border-b-2 hover:border-yellow-400 pb-1' }}">Edit PDF</a>-->
                </nav>
                <!-- Mobile Menu Button -->
                <button id="menuBtn" class="md:hidden text-gray-700 focus:outline-none">
                    ☰
                </button>
            </div>

            <!-- Mobile Nav -->
            <div id="mobileMenu" class="hidden bg-gray-100 md:hidden px-4 pb-4">
                <a href="{{ route('home') }}" class="block py-2 hover:text-indigo-600">Home</a>
                <a href="{{ route('mergepdf') }}" class="block py-2 hover:text-indigo-600">Merge PDF</a>
                <a href="{{ route('splitpdf') }}" class="block py-2 hover:text-indigo-600">Split PDF</a>
                <a href="{{ route('wordtopdf') }}" class="block py-2 hover:text-indigo-600">Word to PDF</a>
                <a href="{{ route('pptppdf') }}" class="block py-2 hover:text-indigo-600">Power Point to PDF</a>
                <a href="{{ route('exceltppdf') }}" class="block py-2 hover:text-indigo-600">Excel to PDF</a>
                <a href="{{ route('pdftojpg') }}" class="block py-2 hover:text-indigo-600">PDF to JPG</a>
                <a href="{{ route('jpgtopdf') }}" class="block py-2 hover:text-indigo-600">JPG to PDF</a>
                <a href="{{ route('rotatepdf') }}" class="block py-2 hover:text-indigo-600">Rotate PDF</a>
                <a href="{{ route('addpagenumbers') }}" class="block py-2 hover:text-indigo-600">Page Numbers</a>
                <!--                <a href="{{ route('croppdf') }}" class="block py-2 hover:text-indigo-600">Crop PDF</a>
                                <a href="{{ route('unlockpdf') }}" class="block py-2 hover:text-indigo-600">Unlock PDF</a>
                                <a href="{{ route('editpdf') }}" class="block py-2 hover:text-indigo-600">Edit PDF</a>-->
            </div>
        </header>

        <!-- MAIN -->
        <main class="pt-24 pb-20 flex-grow">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="bg-gradient-to-r from-[#ba3336] to-[#114880] text-gray-100 py-10 mt-auto">
        <!--<footer class="bg-gray-900 text-gray-300 py-10 mt-auto">-->
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- About -->
                <div>
                    <h4 class="text-white text-lg font-semibold mb-3">MyPDF Tools</h4>
                    <p class="text-sm">Your all-in-one solution for working with PDFs. Merge, split, compress, convert, and more — free and easy to use.</p>
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
                        <a href="#" class="hover:text-white">🌐</a>
                        <a href="#" class="hover:text-white">🐦</a>
                        <a href="#" class="hover:text-white">📘</a>
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
