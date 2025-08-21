@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4">
    <h2 class="text-3xl font-semibold text-center mt-6 mb-4">Protect PDF</h2>

    <!-- File Upload -->
    <div class="flex flex-col items-center">
        <input type="file" id="fileInput" multiple accept=".pdf"
               class="mb-6 block max-w-md text-sm border rounded file:bg-blue-600 file:text-white file:px-4 file:py-2">
    </div>

    <!-- Preview Area -->
    <div id="previewArea" class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8"></div>

    <!-- Password Inputs -->
    <div class="max-w-sm mx-auto mb-6">
        <input type="password" id="password" placeholder="Type password"
               class="w-full mb-3 border px-4 py-2 rounded" required>
        <input type="password" id="repeatPassword" placeholder="Repeat password"
               class="w-full border px-4 py-2 rounded" required>
        <div id="passwordError" class="text-red-600 text-sm hidden mt-2">Passwords do not match.</div>
    </div>

    <!-- Protect Button -->
    <div class="text-center">
        <form id="protectForm" method="POST" action="{{ route('protectpdf.download') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="hidden" name="password" id="finalPassword">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>
            <button type="submit" id="protectBtn"
                    class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700" disabled>
                Protect PDF
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
const rotateBtn = document.getElementById('protectBtn');
const protectForm = document.getElementById('protectForm');
const password = document.getElementById('password');
const repeatPassword = document.getElementById('repeatPassword');
const finalPassword = document.getElementById('finalPassword');
const passwordError = document.getElementById('passwordError');

let files = [];
let rotations = [];

function updateHiddenRotations() {
    fileRotations.value = rotations.join(',');
}

fileInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(f => {
        files.push(f);
        rotations.push(0);
    });
    renderPreviews(files);
    updateProtectButton();
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
            updateHiddenRotations();
        };

        const removeBtn = document.createElement('button');
        removeBtn.innerHTML = '❌';
        removeBtn.className = 'ml-3 mt-3 text-red-500';
        removeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            renderPreviews(files);
            updateProtectButton();
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
            });
        };
        reader.readAsArrayBuffer(file);
    });

    updateHiddenRotations();
}

function updateProtectButton() {
    rotateBtn.disabled = files.length === 0;
}

protectForm.addEventListener('submit', function (e) {
    e.preventDefault();

    if (password.value !== repeatPassword.value || password.value.trim() === "") {
        passwordError.classList.remove('hidden');
        return;
    } else {
        passwordError.classList.add('hidden');
    }

    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f));
    finalInput.files = dt.files;

    fileNamesOrder.value = files.map(f => f.name).join(',');
    fileRotations.value = rotations.join(',');
    finalPassword.value = password.value;

    const formData = new FormData(protectForm);

    // Fetch to handle blob download
    fetch(protectForm.action, {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Failed to protect PDFs");

        return res.blob().then(blob => {
            const contentDisposition = res.headers.get('Content-Disposition');
            const filenameMatch = contentDisposition && contentDisposition.match(/filename="?([^"]+)"?/);
            const filename = filenameMatch ? filenameMatch[1] : 'protected.zip';

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.click();
            URL.revokeObjectURL(link.href);

            // Reset form UI and reload
            setTimeout(() => {
                files = [];
                rotations = [];
                fileInput.value = '';
                previewArea.innerHTML = '';
                updateProtectButton();

                // Reload page after download starts
                location.reload();
            }, 1500);
        });
    })
    .catch(err => {
        alert("Error: " + err.message);
    });
});
</script>
@endsection
