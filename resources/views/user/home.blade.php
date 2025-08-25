@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="text-center py-16 bg-gradient-to-b from-white to-gray-50">
    <h1 class="text-4xl md:text-5xl font-bold text-gray-800">All the Free PDF Tools You’ll Ever Need in One Place</h1>
    <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">
        Choose a tool below and get it done instantly — no sign-up, no limits, completely free.
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
            <p class="text-gray-500 text-sm mt-2 text-center">Combine multiple PDFs into a single file in seconds.</p>
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
            <p class="text-gray-500 text-sm mt-2 text-center">Extract pages or split large PDFs into smaller files easily.</p>
        </div>
    </a>

    <!-- Word to PDF -->
    <a href="{{ route('wordtopdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-green-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/qhgmphtg.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#0000FF,secondary:#22d3ee"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Word to PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Convert Word documents to high-quality PDFs instantly.</p>
        </div>
    </a>

    <!-- PowerPoint to PDF -->
    <a href="{{ route('pptppdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/abwrkdvl.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#2563eb,secondary:#FFA500"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">PowerPoint to PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Turn PowerPoint presentations into shareable PDF files fast.</p>
        </div>
    </a>

    <!-- Excel to PDF -->
    <a href="{{ route('exceltppdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/dklbhvrt.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#008000,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Excel to PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Save Excel sheets as PDFs while keeping the formatting intact.</p>
        </div>
    </a>

    <!-- PDF to JPG -->
    <a href="{{ route('pdftojpg') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/zyzoecaw.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#FFC0CB,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">PDF to JPG</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Turn PDF pages into high-quality JPG images in just seconds.</p>
        </div>
    </a>

    <!-- JPG to PDF -->
    <a href="{{ route('jpgtopdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/oyclgnwc.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#808080,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">JPG to PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Convert images into PDF files with just one click.</p>
        </div>
    </a>

    <!-- Rotate PDF -->
    <a href="{{ route('rotatepdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/zpxybbhl.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#50C878,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Rotate PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Rotate pages inside your PDF and save them the way you need.</p>
        </div>
    </a>

    <!-- Protect PDF -->
    <a href="{{ route('protectpdf') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/tyounuzx.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#87CEEB,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Protect PDF</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Secure your PDF with a password and keep your files safe from unauthorized access.</p>
        </div>
    </a>

    <!-- Add Page Numbers -->
    <a href="{{ route('addpagenumbers') }}" class="block">
        <div class="group bg-white shadow-md rounded-xl p-6 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 hover:border-blue-400">
            <div class="mb-4 flex justify-center">
                <lord-icon
                    src="https://cdn.lordicon.com/lomfljuq.json"
                    trigger="loop"
                    delay="1000"
                    colors="primary:#A020F0,secondary:#60a5fa"
                    style="width:50px;height:50px">
                </lord-icon>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 text-center">Add Page Numbers</h3>
            <p class="text-gray-500 text-sm mt-2 text-center">Insert page numbers into your PDF for better organization.</p>
        </div>
    </a>

</section>

<!-- Lordicon Script -->
<script src="https://cdn.lordicon.com/lordicon.js"></script>
@endsection
