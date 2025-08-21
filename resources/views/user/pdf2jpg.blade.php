@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Convert PDF to JPG</h2>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('pdf.convert') }}" enctype="multipart/form-data" id="pdfForm">
        @csrf

        <div class="mb-3">
            <label for="pdfInput" class="form-label">Upload PDFs</label>
            <input type="file" name="pdfs[]" id="pdfInput" class="form-control" accept="application/pdf" multiple required>
        </div>

        <div id="previewArea" class="row gy-4"></div>

        <button type="submit" class="btn btn-primary mt-4">Convert & Download</button>
    </form>
</div>

<script>
document.getElementById('pdfForm').addEventListener('submit', function () {
    setTimeout(() => {
        fileList = new DataTransfer();
        fileInput.files = fileList.files;
        fileInput.value = '';

        previewArea.innerHTML = '';
        fileDataMap.clear();
    }, 100);
});
    
let fileList = new DataTransfer();
const fileInput = document.getElementById('pdfInput');
const previewArea = document.getElementById('previewArea');
const fileDataMap = new Map(); // key: uniqueId, value: { file, rotation }

fileInput.addEventListener('change', function () {
    for (const file of this.files) {
        if (![...fileDataMap.values()].some(f => f.file.name === file.name)) {
            const uniqueId = Date.now() + Math.random().toString(36).substr(2, 9);
            fileList.items.add(file);
            fileDataMap.set(uniqueId, { file: file, rotation: 0 });
            renderPreview(uniqueId);
        }
    }
    this.files = fileList.files;
});

function renderPreview(uniqueId) {
    const { file, rotation } = fileDataMap.get(uniqueId);
    const canvasId = `canvas-${uniqueId}`;
    const rotateInputId = `rotate-${uniqueId}`;

    const col = document.createElement('div');
    col.className = 'col-md-3';
    col.setAttribute('data-id', uniqueId);

    col.innerHTML = `
        <div class="card shadow-sm h-100">
            <div class="card-header text-truncate small" title="${file.name}">
                ${file.name}
            </div>
            <div class="card-body p-2 d-flex justify-content-center align-items-center" style="min-height:220px;">
                <canvas id="${canvasId}" class="border rounded" style="max-width: 100%; height: auto;"></canvas>
                <input type="hidden" name="rotate[${file.name}]" id="${rotateInputId}" value="${rotation}">
            </div>
            <div class="card-footer d-flex justify-content-between px-3 pb-3">
                <button type="button" class="btn btn-sm btn-outline-primary rotate-btn" data-id="${uniqueId}" title="Rotate">🔄</button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-btn" data-id="${uniqueId}" title="Remove">❌</button>
            </div>
        </div>
    `;
    previewArea.appendChild(col);

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

    col.querySelector('.rotate-btn').addEventListener('click', () => {
        const data = fileDataMap.get(uniqueId);
        const newRotation = (data.rotation + 90) % 360;
        data.rotation = newRotation;
        document.getElementById(rotateInputId).value = newRotation;

        const reader2 = new FileReader();
        reader2.onload = function () {
            const typedArray = new Uint8Array(this.result);
            pdfjsLib.getDocument(typedArray).promise.then(pdf => {
                pdf.getPage(1).then(page => {
                    drawCanvas(page, canvasId, newRotation);
                });
            });
        };
        reader2.readAsArrayBuffer(file);
    });

    col.querySelector('.remove-btn').addEventListener('click', () => {
        fileDataMap.delete(uniqueId);
        updateFileList();
        document.querySelector(`[data-id="${uniqueId}"]`).remove();
    });
}

function drawCanvas(page, canvasId, rotation = 0) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    const scale = 1;

    const viewport = page.getViewport({ scale, rotation });
    canvas.width = viewport.width;
    canvas.height = viewport.height;

    page.render({
        canvasContext: ctx,
        viewport: viewport
    });
}

function updateFileList() {
    fileList = new DataTransfer();
    fileDataMap.forEach(({ file }) => fileList.items.add(file));
    fileInput.files = fileList.files;
}
</script>

@endsection
