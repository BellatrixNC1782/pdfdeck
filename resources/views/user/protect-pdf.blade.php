@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Protect PDF</h2>
        <p class="text-gray-500 mt-2">Upload, rotate, and protect your PDFs with a password</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="fileInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-sky-500 hover:bg-sky-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-sky-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PDFs here</span>
            <input type="file" id="fileInput" multiple accept=".pdf" class="hidden">
        </label>
    </div>

    <!-- Preview Area -->
    <div id="previewArea" class="flex flex-wrap gap-4 mt-4"></div>

    <!-- Password Inputs -->
    <div class="max-w-sm mx-auto mb-6 text-center">
        <input type="password" id="password" placeholder="Type password"
               class="w-full mb-3 border px-4 py-2 rounded-lg shadow-sm focus:ring-2 focus:ring-sky-500" required>
        <input type="password" id="repeatPassword" placeholder="Repeat password"
               class="w-full border px-4 py-2 rounded-lg shadow-sm focus:ring-2 focus:ring-sky-500" required>
        <div id="passwordError" class="text-red-600 text-sm hidden mt-2">Passwords do not match.</div>
    </div>

    <!-- Protect Button -->
    <div class="text-center mt-10">
        <form id="protectForm" method="POST" action="{{ route('protectpdf.download') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="hidden" name="password" id="finalPassword">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>

            <button type="submit" id="protectBtn"
                    class="px-8 py-4 bg-sky-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                Protect & Download
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
const protectBtn = document.getElementById('protectBtn');
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
    renderPreviews();
    updateProtectButton();
});

function renderPreviews() {
    previewArea.innerHTML = '';

    files.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
        div.dataset.index = index;

        // Top actions (close button only for Protect PDF)
        const topBar = document.createElement('div');
        topBar.className = "absolute top-2 right-2";

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = "✕";
        closeBtn.className = "bg-sky-500 hover:bg-sky-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow";
        closeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            renderPreviews();
            updateProtectButton();
        };

        topBar.appendChild(closeBtn);
        div.appendChild(topBar);

        // PDF canvas preview
        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = "flex-1 flex items-center justify-center my-4";
        const canvas = document.createElement('canvas');
        canvas.className = "max-h-40 object-contain transition-transform duration-300 ease-in-out";
        canvasWrapper.appendChild(canvas);
        div.appendChild(canvasWrapper);

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
        degreeLabel.className = "bg-sky-400 text-white font-semibold px-2 py-1 rounded-full text-sm";

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

        div.appendChild(controls);
        previewArea.appendChild(div);

        // Render PDF first page
        const reader = new FileReader();
        reader.onload = function (e) {
            const loadingTask = pdfjsLib.getDocument({ data: e.target.result });
            loadingTask.promise.then(function (pdf) {
                pdf.getPage(1).then(function (page) {
                    const viewport = page.getViewport({ scale: 0.5 });
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    page.render({ canvasContext: context, viewport });
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });

    updateHiddenRotations();
}

function updateProtectButton() {
    protectBtn.disabled = files.length === 0;
}

protectForm.addEventListener('submit', function (e) {
    // Show loader immediately
    document.getElementById("loaderOverlay").classList.remove("hidden");
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

            setTimeout(() => {
                files = [];
                rotations = [];
                fileInput.value = '';
                previewArea.innerHTML = '';
                updateProtectButton();
                location.reload();
                document.getElementById("loaderOverlay").classList.add("hidden");
            }, 1500);
        });
    })
    .catch(err => {
        alert("Error: " + err.message);
    });
});
</script>
@endsection
