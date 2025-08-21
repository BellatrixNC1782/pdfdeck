@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <h2 class="text-3xl font-semibold text-center mt-6 mb-4">Unlock PDF</h2>

    <div class="flex flex-col items-center">
        <input type="file" id="fileInput" multiple accept=".pdf"
               class="mb-6 block max-w-md text-sm border rounded file:bg-blue-600 file:text-white file:px-4 file:py-2">
    </div>

    <div id="previewArea" class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8"></div>

    <div class="text-center">
        <form id="unlockForm" method="POST" action="{{ route('unlockpdf.download') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>
            <input type="hidden" name="passwords" id="passwords">
            <button type="submit" id="unlockBtn"
                    class="bg-green-600 text-white px-6 py-3 rounded-full hover:bg-green-700" disabled>
                Unlock PDF
            </button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

const fileInput = document.getElementById('fileInput');
const finalInput = document.getElementById('finalInput');
const previewArea = document.getElementById('previewArea');
const fileNamesOrder = document.getElementById('fileNamesOrder');
const fileRotations = document.getElementById('fileRotations');
const passwords = document.getElementById('passwords');
const unlockBtn = document.getElementById('unlockBtn');
const unlockForm = document.getElementById('unlockForm');

let files = [];
let rotations = [];
let passwordInputs = [];

function updateHiddenFields() {
    fileRotations.value = rotations.join(',');
    passwords.value = passwordInputs.map(input => input.value || '').join('|:|');
}

fileInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(f => {
        files.push(f);
        rotations.push(0);
        passwordInputs.push(document.createElement('input')); // create blank input field
    });
    renderPreviews(files);
    unlockBtn.disabled = files.length === 0;
    updateHiddenFields();
});

function renderPreviews(files) {
    previewArea.innerHTML = '';
    files.forEach((file, index) => {
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
            updateHiddenFields();
        };

        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '❌';
        removeBtn.className = 'ml-3 mt-3 text-red-500';
        removeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            passwordInputs.splice(index, 1);
            renderPreviews(files);
            unlockBtn.disabled = files.length === 0;
            updateHiddenFields();
        };

        const passwordField = document.createElement('input');
        passwordField.placeholder = 'Password (if locked)';
        passwordField.className = 'w-full px-2 py-1 border mt-2 text-sm rounded';
        passwordField.oninput = () => updateHiddenFields();
        passwordInputs[index] = passwordField;

        div.appendChild(fileName);
        div.appendChild(canvasWrapper);
        div.appendChild(rotateBtn);
        div.appendChild(removeBtn);
        div.appendChild(passwordField);
        previewArea.appendChild(div);

        const reader = new FileReader();
        reader.onload = function (e) {
            const loadingTask = pdfjsLib.getDocument({ data: e.target.result });
            loadingTask.promise.then(function (pdf) {
                pdf.getPage(1).then(function (page) {
                    const viewport = page.getViewport({ scale: 1.0 });
                    const scale = Math.min(280 / viewport.width, 380 / viewport.height);
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
            }).catch(() => {
                passwordField.style.borderColor = 'red';
                passwordField.placeholder = 'Enter password to unlock';
            });
        };
        reader.readAsArrayBuffer(file);
    });

    updateHiddenFields();
}

unlockForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f));
    finalInput.files = dt.files;

    fileNamesOrder.value = files.map(f => f.name).join(',');
    updateHiddenFields();
    unlockForm.submit();
    setTimeout(() => {
        location.reload();
    }, 1000);
});
</script>
@endsection
