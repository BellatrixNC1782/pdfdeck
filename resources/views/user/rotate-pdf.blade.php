@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <h2 class="text-3xl font-semibold text-center mt-6 mb-4">Rotate PDF</h2>

    @if ($errors->has('pdfs'))
    <div class="text-red-500 text-sm mt-2">
        {{ $errors->first('pdfs') }}
    </div>
    @endif

    <!-- Upload -->
    <div class="flex flex-col items-center">
        <input type="file" id="fileInput" multiple accept=".pdf"
               class="mb-6 block max-w-md text-sm border rounded file:bg-blue-600 file:text-white file:px-4 file:py-2">
    </div>

    <!-- Previews -->
    <div id="previewArea" class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8"></div>

    <!-- Rotate Button -->
    <div class="text-center">
        <form id="rotateForm" method="POST" action="{{ route('downloadrotatepdf') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>
            <button type="submit" id="rotateBtn" class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700" disabled>Rotate & Download</button>
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
    const previewArea = document.getElementById('previewArea');
    previewArea.innerHTML = '';

    Array.from(files).forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'p-4 bg-white rounded shadow-md flex flex-col items-center justify-center w-[360px] mx-auto mb-4';

        const fileName = document.createElement('p');
        fileName.textContent = file.name;
        fileName.className = 'text-sm mb-2 break-words text-center max-w-full';

        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = 'relative w-[300px] h-[400px] border rounded overflow-hidden flex items-center justify-center bg-gray-100';

        const canvas = document.createElement('canvas');
        canvas.className = 'max-w-full max-h-full object-contain transition-transform duration-300 ease-in-out';
        canvasWrapper.appendChild(canvas);

        const rotateBtn = document.createElement('button');
        rotateBtn.innerHTML = '⟳ 0°';
        rotateBtn.className = 'bg-yellow-500 text-white px-3 py-1 mt-3 rounded';
        rotateBtn.onclick = () => {
            rotations[index] = (rotations[index] + 90) % 360;
            rotateBtn.innerHTML = `⟳ ${rotations[index]}°`;
            canvas.style.transform = `rotate(${rotations[index]}deg)`;
            updateHiddenRotations();
        };

        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '❌';
        removeBtn.className = 'ml-3 mt-3 text-red-500';
        removeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            renderPreviews(files);
            updateRotateButton();
        };

        div.appendChild(fileName);
        div.appendChild(canvasWrapper);
        div.appendChild(rotateBtn);
        div.appendChild(removeBtn);
        previewArea.appendChild(div);

        const reader = new FileReader();
        reader.onload = function (e) {
            const loadingTask = pdfjsLib.getDocument({ data: e.target.result });
            loadingTask.promise.then(function (pdf) {
                pdf.getPage(1).then(function (page) {
                    const viewport = page.getViewport({ scale: 1.0 });
                    const scale = Math.min(280 / viewport.width, 380 / viewport.height); // Fit canvas inside box
                    const scaledViewport = page.getViewport({ scale: scale });

                    const context = canvas.getContext('2d');
                    canvas.height = scaledViewport.height;
                    canvas.width = scaledViewport.width;

                    const renderContext = {
                        canvasContext: context,
                        viewport: scaledViewport
                    };
                    page.render(renderContext);
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });

    updateHiddenRotations();
}

function removeFileFromList(fileList, indexToRemove) {
    const dt = new DataTransfer();
    Array.from(fileList).forEach((file, index) => {
        if (index !== indexToRemove) {
            dt.items.add(file);
        }
    });
    files = Array.from(dt.files);
    return dt.files;
}

function updateRotateButton() {
    rotateBtn.disabled = files.length === 0;
}

document.getElementById('rotateForm').addEventListener('submit', () => {
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
    }, 1500);
});
</script>
@endsection
