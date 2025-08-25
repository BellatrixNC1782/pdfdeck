@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Rotate PDF</h2>
        <p class="text-gray-500 mt-2">Upload, preview, rotate and download your PDFs</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="fileInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-emerald-500 hover:bg-emerald-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PDFs here</span>
            <input type="file" id="fileInput" multiple accept=".pdf" class="hidden">
        </label>
    </div>

    <!-- Preview Area -->
    <div id="previewArea" class="flex flex-wrap gap-4 justify-center"></div>

    <!-- Rotate & Download Button -->
    <div class="text-center mt-10">
        <form id="rotateForm" method="POST" action="{{ route('downloadrotatepdf') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>
            <button type="submit" id="rotateBtn"
                    class="px-8 py-4 bg-emerald-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                Rotate & Download
            </button>
        </form>
    </div>
</div>

<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

const fileInput = document.getElementById('fileInput');
const finalInput = document.getElementById('finalInput');
const previewArea = document.getElementById('previewArea');
const fileNamesOrder = document.getElementById('fileNamesOrder');
const fileRotations = document.getElementById('fileRotations');
const rotateBtn = document.getElementById('rotateBtn');

let files = [];
let rotations = [];

function updateHiddenRotations() {
    document.getElementById('fileRotations').value = rotations.join(',');
}

fileInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(f => {
        files.push(f);
        rotations.push(0);
    });
    renderPreviews(files);
    updateRotateButton();
});

function renderPreviews(files) {
    previewArea.innerHTML = '';

    Array.from(files).forEach((file, index) => {
        const card = document.createElement('div');
        card.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
        card.dataset.index = index;

        // Top bar with close button
        const topBar = document.createElement('div');
        topBar.className = "absolute top-2 right-2";

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = "✕";
        closeBtn.className = "bg-emerald-500 hover:bg-emerald-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow";
        closeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            renderPreviews(files);
            updateRotateButton();
        };

        topBar.appendChild(closeBtn);
        card.appendChild(topBar);

        // File name
        const fileName = document.createElement('p');
        fileName.textContent = file.name;
        fileName.className = "text-xs mt-1 mb-2 text-gray-600 break-words text-center max-w-full";
        card.appendChild(fileName);

        // Canvas preview
        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = "flex-1 flex items-center justify-center my-2";
        const canvas = document.createElement('canvas');
        canvas.className = "max-h-40 object-contain transition-transform duration-300";
        canvasWrapper.appendChild(canvas);
        card.appendChild(canvasWrapper);

        // Rotation controls
        const controls = document.createElement('div');
        controls.className = "flex justify-center items-center gap-3 mt-2";

        const rotateLeft = document.createElement('button');
        rotateLeft.innerHTML = "⟲";
        rotateLeft.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
        rotateLeft.addEventListener('click', () => {
            rotations[index] = (rotations[index] - 90 + 360) % 360;
            canvas.style.transform = `rotate(${rotations[index]}deg)`;
            degreeLabel.textContent = `${rotations[index]}°`;
            updateHiddenRotations();
        });

        const degreeLabel = document.createElement('div');
        degreeLabel.textContent = "0°";
        degreeLabel.className = "bg-yellow-400 text-white font-semibold px-2 py-1 rounded-full text-sm";

        const rotateRight = document.createElement('button');
        rotateRight.innerHTML = "⟳";
        rotateRight.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
        rotateRight.addEventListener('click', () => {
            rotations[index] = (rotations[index] + 90) % 360;
            canvas.style.transform = `rotate(${rotations[index]}deg)`;
            degreeLabel.textContent = `${rotations[index]}°`;
            updateHiddenRotations();
        });

        controls.appendChild(rotateLeft);
        controls.appendChild(degreeLabel);
        controls.appendChild(rotateRight);
        card.appendChild(controls);

        previewArea.appendChild(card);

        // Render PDF preview (first page)
        const reader = new FileReader();
        reader.onload = function (e) {
            const loadingTask = pdfjsLib.getDocument({ data: e.target.result });
            loadingTask.promise.then(function (pdf) {
                pdf.getPage(1).then(function (page) {
                    const viewport = page.getViewport({ scale: 0.5 });
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    page.render({ canvasContext: context, viewport: viewport });
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });

    updateHiddenRotations();
}

function updateRotateButton() {
    rotateBtn.disabled = files.length === 0;
}

document.getElementById('rotateForm').addEventListener('submit', () => {
    // Show loader immediately
    document.getElementById("loaderOverlay").classList.remove("hidden");

    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f));
    finalInput.files = dt.files;

    fileNamesOrder.value = files.map(f => f.name).join(',');
    fileRotations.value = rotations.join(',');

    setTimeout(() => {
        files = [];
        rotations = [];
        fileInput.value = '';
        previewArea.innerHTML = '';
        updateRotateButton();
        document.getElementById("loaderOverlay").classList.add("hidden");
    }, 2000);
});
</script>
@endsection
