@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Convert PDF to JPG</h2>
        <p class="text-gray-500 mt-2">Upload PDFs and preview before converting</p>
    </div>

    <form method="POST" action="{{ route('pdf.convert') }}" enctype="multipart/form-data" id="pdfForm">
        @csrf

        <!-- Upload -->
        <div class="flex justify-center mb-10">
            <label id="dropZone" for="pdfInput"
                   class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-pink-500 hover:bg-pink-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-pink-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-gray-600 font-medium">Click or drag & drop PDFs here</span>
                <input type="file" name="pdfs[]" id="pdfInput" class="hidden" accept="application/pdf" multiple required>
            </label>
        </div>

        <!-- Preview Area -->
        <div id="previewArea" class="flex flex-wrap gap-4 mt-4"></div>

        <!-- Convert Button -->
        <div class="text-center mt-10">
            <button type="submit" class="px-8 py-4 bg-pink-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Convert & Download
            </button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js"></script>

<script>
    
    
const dropZone = document.getElementById('dropZone');

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, e => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.add('border-pink-500', 'bg-pink-50');
    }, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, e => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.classList.remove('border-pink-500', 'bg-pink-50');
    }, false);
});

dropZone.addEventListener('drop', e => {
    e.preventDefault();
    e.stopPropagation();
    const droppedFiles = e.dataTransfer.files;
    if (droppedFiles.length > 0) {
        for (const file of droppedFiles) {
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                alert("Invalid file! Only PDF files are allowed.");
                return;
            }
        }
        fileInput.files = droppedFiles;
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

    
document.getElementById('pdfForm').addEventListener('submit', function () {
    // Show loader immediately
    document.getElementById("loaderOverlay").classList.remove("hidden");
    setTimeout(() => {
        fileList = new DataTransfer();
        fileInput.files = fileList.files;
        fileInput.value = '';

        previewArea.innerHTML = '';
        fileDataMap.clear();
        document.getElementById("loaderOverlay").classList.add("hidden");
    }, 2000);
});

let fileList = new DataTransfer();
const fileInput = document.getElementById('pdfInput');
const previewArea = document.getElementById('previewArea');
const fileDataMap = new Map(); // key: uniqueId, value: { file, rotation }


    
fileInput.addEventListener('change', function () {
    const filesArray = Array.from(this.files);
    for (const file of filesArray) {
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            alert("Invalid file! Only PDF files are allowed.");
            // Remove invalid file from file input
            this.value = '';
            return;
        }
    }
    
    // proceed with existing logic for valid files
    for (const file of filesArray) {
        if (![...fileDataMap.values()].some(f => f.file.name === file.name)) {
            const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9);
            fileList.items.add(file);
            fileDataMap.set(uniqueId, {file: file, rotation: 0});
            renderPreview(uniqueId);
        }
    }
    this.files = fileList.files;
});

function renderPreview(uniqueId) {
    const {file, rotation} = fileDataMap.get(uniqueId);
    const canvasId = `canvas-${uniqueId}`;
    const rotateInputId = `rotate-${uniqueId}`;

    const div = document.createElement('div');
    div.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
    div.setAttribute('data-id', uniqueId);

    // Top actions
    const topBar = document.createElement('div');
    topBar.className = "absolute top-2 left-2 right-2 flex justify-between items-center";

    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = "✕";
    closeBtn.className = "bg-pink-500 hover:bg-pink-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow";
    closeBtn.onclick = () => {
        fileDataMap.delete(uniqueId);
        updateFileList();
        div.remove();
    };
    topBar.appendChild(closeBtn);
    div.appendChild(topBar);

    // PDF canvas preview
    const canvasWrapper = document.createElement('div');
    canvasWrapper.className = "flex-1 flex items-center justify-center my-4";
    const canvas = document.createElement('canvas');
    canvas.className = "max-h-40 object-contain";
    canvas.id = canvasId;
    canvasWrapper.appendChild(canvas);
    div.appendChild(canvasWrapper);

    // Rotation controls
    const controls = document.createElement('div');
    controls.className = "flex justify-center items-center gap-3 mt-2";

    const rotateLeft = document.createElement('button');
    rotateLeft.type = "button"; // 👈 Add this
    rotateLeft.innerHTML = "⟲";
    rotateLeft.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
    rotateLeft.addEventListener('click', (e) => {
        e.preventDefault(); // 👈 Prevent form submission
        rotateFile(uniqueId, -90);
    });

    const degreeLabel = document.createElement('div');
    degreeLabel.textContent = "0°";
    degreeLabel.className = "bg-yellow-400 text-white font-semibold px-2 py-1 rounded-full text-sm";

    const rotateRight = document.createElement('button');
    rotateRight.type = "button"; // 👈 Add this
    rotateRight.innerHTML = "⟳";
    rotateRight.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
    rotateRight.addEventListener('click', (e) => {
        e.preventDefault();
        rotateFile(uniqueId, 90);
    });

    controls.appendChild(rotateLeft);
    controls.appendChild(degreeLabel);
    controls.appendChild(rotateRight);
    div.appendChild(controls);
    
    // Hidden rotation input
    const hiddenRotation = document.createElement('input');
    hiddenRotation.type = 'hidden';
    hiddenRotation.name = `rotate[${file.name}]`; // use filename as key like old code
    hiddenRotation.id = rotateInputId;
    hiddenRotation.value = rotation;
    div.appendChild(hiddenRotation);
    
    previewArea.appendChild(div);

    // Render first page preview
    const reader = new FileReader();
    reader.onload = function () {
        const typedArray = new Uint8Array(this.result);
        pdfjsLib.getDocument(typedArray).promise.then(pdf => {
            pdf.getPage(1).then(page => {
                drawCanvas(page, canvasId, rotation);
            });
        });
    };
    reader.readAsArrayBuffer(file);
}

function rotateFile(uniqueId, delta) {
    const data = fileDataMap.get(uniqueId);
    data.rotation = (data.rotation + delta + 360) % 360;
    document.getElementById(`rotate-${uniqueId}`).value = data.rotation;

    const {file, rotation} = data;
    const canvasId = `canvas-${uniqueId}`;
    const reader = new FileReader();
    reader.onload = function () {
        const typedArray = new Uint8Array(this.result);
        pdfjsLib.getDocument(typedArray).promise.then(pdf => {
            pdf.getPage(1).then(page => {
                drawCanvas(page, canvasId, rotation);
            });
        });
    };
    reader.readAsArrayBuffer(file);
}

function drawCanvas(page, canvasId, rotation = 0) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    const viewport = page.getViewport({scale: 0.5, rotation});
    canvas.width = viewport.width;
    canvas.height = viewport.height;
    page.render({canvasContext: ctx, viewport: viewport});
}

function updateFileList() {
    fileList = new DataTransfer();
    fileDataMap.forEach(({ file }) => fileList.items.add(file));
    fileInput.files = fileList.files;
}
</script>
@endsection
