@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="text-center py-16 bg-gradient-to-b from-white to-gray-50">
    <h1 class="text-4xl md:text-5xl font-bold text-gray-800">Every tool you need to work with PDFs in one place</h1>
    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
        Merge, split, compress, convert, rotate, unlock, and watermark PDFs with just a few clicks — easy and free.
    </p>
</section>

<!-- Tools Grid -->
<section class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    
    <!-- Merge PDF -->
    <a href="{{ route('mergepdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-red-400">
            <div class="mb-4 flex justify-center">
                <!-- Animated Icon from Lordicon -->
                <lord-icon
                    src="https://cdn.lordicon.com/wloilxuq.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#e11d48,secondary:#fbbf24"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Merge PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Combine PDFs in the order you want with the easiest PDF merger.</p>
        </div>
    </a>

    <!-- Split PDF -->
    <a href="{{ route('splitpdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-red-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/mecwbjnp.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#dc2626,secondary:#facc15"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Split PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Separate one page or a whole set for easy conversion into independent files.</p>
        </div>
    </a>

    <!-- Compress PDF -->
    <a href="{{ route('compress') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-green-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/qhgmphtg.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#16a34a,secondary:#22d3ee"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Compress PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Reduce file size while keeping high quality.</p>
        </div>
    </a>

    <!-- PDF to Word -->
    <a href="{{ route('pdftoword') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/iltqorsz.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#2563eb,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">PDF to Word</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Convert your PDF files into editable Word documents.</p>
        </div>
    </a>

</section>

<!-- Lordicon Script -->
<script src="https://cdn.lordicon.com/lordicon.js"></script>
@endsection
