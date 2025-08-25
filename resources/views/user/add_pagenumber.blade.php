@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Add Page Numbers</h2>
        <p class="text-gray-500 mt-2">Upload your PDFs and customize numbering</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="pdfInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-purple-500 hover:bg-purple-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PDFs here</span>
            <input type="file" id="pdfInput" accept="application/pdf" multiple class="hidden">
        </label>
    </div>

    <!-- Settings -->
    <form method="POST" action="{{ route('pdf.page_numbers.process') }}" enctype="multipart/form-data" id="pageNumberForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium mb-1">Position</label>
                <select name="position" id="position" class="w-full border rounded-lg px-3 py-2" required>
                    <option value="bottom_right">Bottom Right</option>
                    <option value="bottom_left">Bottom Left</option>
                    <option value="top_right">Top Right</option>
                    <option value="top_left">Top Left</option>
                </select>
            </div>
            <div>
                <label class="block font-medium mb-1">First Number</label>
                <input type="number" id="first_number" name="first_number" class="w-full border rounded-lg px-3 py-2" placeholder="e.g. 1">
            </div>
        </div>

        <div>
            <label class="block font-medium mb-1">Page Range</label>
            <input type="text" id="page_range" name="page_range" class="w-full border rounded-lg px-3 py-2" placeholder="e.g. 1-5 (optional)">
        </div>

        <input type="hidden" name="pdf_file_names" id="pdfFileNames">
        <input type="file" name="pdfs[]" id="finalPdfInput" multiple hidden>

        <!-- Preview Area -->
        <div id="previewArea" class="flex flex-wrap gap-4 mt-10"></div>

        <!-- Submit -->
        <div class="text-center">
            <button type="submit" class="px-8 py-4 bg-purple-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Add Page Numbers
            </button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
const pdfInput = document.getElementById('pdfInput');
const finalPdfInput = document.getElementById('finalPdfInput');
const previewArea = document.getElementById('previewArea');
const pdfFileNames = document.getElementById('pdfFileNames');

let pdfFiles = [];

pdfInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(f => {
        if (!pdfFiles.some(x => x.name === f.name && x.size === f.size)) {
            pdfFiles.push(f);
        }
    });
    renderPreviews();
    pdfInput.value = '';
});

function renderPreviews() {
    previewArea.innerHTML = '';

    pdfFiles.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
        div.dataset.index = index;

        // top bar with drag + close
        const topBar = document.createElement('div');
        topBar.className = "absolute top-2 left-2 right-2 flex justify-between items-center";

        const dragHandle = document.createElement('div');
        dragHandle.innerHTML = "⋮⋮";
        dragHandle.className = "drag-handle cursor-grab active:cursor-grabbing text-gray-400 text-lg";

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = "✕";
        closeBtn.className = "bg-purple-500 hover:bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow";
        closeBtn.onclick = () => {
            pdfFiles.splice(index, 1);
            renderPreviews();
        };

        topBar.appendChild(dragHandle);
        topBar.appendChild(closeBtn);
        div.appendChild(topBar);

        // canvas preview
        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = "flex-1 flex items-center justify-center my-4";
        const canvas = document.createElement('canvas');
        canvas.className = "max-h-40 object-contain";
        canvasWrapper.appendChild(canvas);
        div.appendChild(canvasWrapper);

        // filename
        const label = document.createElement('div');
        label.className = "text-center text-sm font-medium text-gray-600 mt-2 truncate w-full";
        label.textContent = file.name;
        div.appendChild(label);

        previewArea.appendChild(div);

        // render first page
        const reader = new FileReader();
        reader.onload = function (e) {
            const typedarray = new Uint8Array(e.target.result);
            pdfjsLib.getDocument(typedarray).promise.then(pdf => {
                pdf.getPage(1).then(page => {
                    const viewport = page.getViewport({scale: 0.5});
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    page.render({canvasContext: context, viewport: viewport});
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });

    // enable sorting
    Sortable.create(previewArea, {
        animation: 200,
        handle: ".drag-handle",
        onEnd: () => {
            const newFiles = [];
            document.querySelectorAll('#previewArea > div').forEach(div => {
                const i = parseInt(div.dataset.index);
                newFiles.push(pdfFiles[i]);
            });
            pdfFiles = newFiles;
            renderPreviews();
        }
    });

    pdfFileNames.value = pdfFiles.map(f => f.name).join(',');
}

document.getElementById('pageNumberForm').addEventListener('submit', (e) => {
    if (pdfFiles.length === 0) {
        e.preventDefault();
        alert('Please select at least one PDF.');
        return;
    }

    const dt = new DataTransfer();
    pdfFiles.forEach(f => dt.items.add(f));
    finalPdfInput.files = dt.files;

    setTimeout(() => {
        pdfFiles = [];
        pdfInput.value = '';
        previewArea.innerHTML = '';
        pdfFileNames.value = '';
        finalPdfInput.value = '';
    }, 1500);
});
</script>
<script>
document.getElementById("pageNumberForm").addEventListener("submit", function (e) {
    let form = this;

    // Show loader immediately
    document.getElementById("loaderOverlay").classList.remove("hidden");

    setTimeout(() => {
        // Reset form
        form.reset();

        document.getElementById("position").selectedIndex = 0;
        document.getElementById("first_number").value = "";
        document.getElementById("page_range").value = "";

        // Clear preview
        pdfFiles = [];
        pdfInput.value = '';
        previewArea.innerHTML = '';
        pdfFileNames.value = '';
        finalPdfInput.value = '';

        // Hide loader after processing
        document.getElementById("loaderOverlay").classList.add("hidden");
    }, 3000); // adjust timing if server takes longer
});
</script>

@endsection
