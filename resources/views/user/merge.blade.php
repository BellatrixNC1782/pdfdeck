@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Merge PDF</h2>
        <p class="text-gray-500 mt-2">Upload, reorder, rotate, and combine your PDFs</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="fileInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-red-500 hover:bg-red-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PDFs here</span>
            <input type="file" id="fileInput" multiple accept=".pdf" class="hidden">
        </label>
    </div>

    <!-- Controls -->
    <div class="flex justify-center gap-4 mb-6">
        <button id="sortAZ" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg text-sm font-medium">Sort A → Z</button>
        <button id="sortZA" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg text-sm font-medium">Sort Z → A</button>
    </div>

    <!-- Preview Area -->
    <div id="previewArea" class="flex flex-wrap gap-4 mt-4"></div>

    <!-- Merge Button -->
    <div class="text-center mt-10">
        <form id="mergeForm" method="POST" action="{{ route('downloadmergepdf') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="file_names_order" id="fileNamesOrder">
            <input type="hidden" name="file_rotations" id="fileRotations">
            <input type="file" name="pdfs[]" id="finalInput" multiple hidden>

            <button type="submit" id="mergeBtn" class="px-8 py-4 bg-red-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Merge & Download
            </button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<script>
const fileInput = document.getElementById('fileInput');
//    const addMoreBtn = document.getElementById('addMoreBtn');
const finalInput = document.getElementById('finalInput');
const previewArea = document.getElementById('previewArea');
const fileNamesOrder = document.getElementById('fileNamesOrder');
const fileRotations = document.getElementById('fileRotations');
const sortAZ = document.getElementById('sortAZ');
const sortZA = document.getElementById('sortZA');

let files = [];
let rotations = [];

fileInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(f => {
        files.push(f);
        rotations.push(0);
    });
    renderPreviews();
    updateMergeButtonState();
//        addMoreBtn.classList.remove('hidden');
});

//    addMoreBtn.addEventListener('click', () => fileInput.click());
function renderPreviews() {
    previewArea.innerHTML = '';

    files.forEach((file, index) => {
        const div = document.createElement('div');
        div.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
        div.dataset.index = index;

        // Top actions (drag handle + close button)
        const topBar = document.createElement('div');
        topBar.className = "absolute top-2 left-2 right-2 flex justify-between items-center";

        const dragHandle = document.createElement('div');
        dragHandle.innerHTML = "⋮⋮";
        dragHandle.className = "drag-handle cursor-grab active:cursor-grabbing text-gray-400 text-lg";


        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = "✕";
        closeBtn.className = "bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow";
        closeBtn.onclick = () => {
            files.splice(index, 1);
            rotations.splice(index, 1);
            renderPreviews();
            updateMergeButtonState();
        };

        topBar.appendChild(dragHandle);
        topBar.appendChild(closeBtn);
        div.appendChild(topBar);

        // PDF canvas preview
        const canvasWrapper = document.createElement('div');
        canvasWrapper.className = "flex-1 flex items-center justify-center my-4";
        const canvas = document.createElement('canvas');
        canvas.className = "max-h-40 object-contain";
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
        });

        controls.appendChild(rotateLeft);
        controls.appendChild(degreeLabel);
        controls.appendChild(rotateRight);

        div.appendChild(controls);
        previewArea.appendChild(div);

        // Render PDF first page using PDF.js
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

    // Enable drag sorting
    Sortable.create(previewArea, {
        animation: 200,
        handle: ".drag-handle", // only drag with this class
        onEnd: () => {
            const newFiles = [];
            const newRot = [];
            document.querySelectorAll('#previewArea > div').forEach(div => {
                const i = parseInt(div.dataset.index);
                newFiles.push(files[i]);
                newRot.push(rotations[i]);
            });
            files = newFiles;
            rotations = newRot;
            renderPreviews();
            updateMergeButtonState();
        }
    });

}


sortAZ.addEventListener('click', () => {
    const combined = files.map((f, i) => ({f, r: rotations[i]}));
    combined.sort((a, b) => a.f.name.localeCompare(b.f.name));
    files = combined.map(x => x.f);
    rotations = combined.map(x => x.r);
    renderPreviews();
});

sortZA.addEventListener('click', () => {
    const combined = files.map((f, i) => ({f, r: rotations[i]}));
    combined.sort((a, b) => b.f.name.localeCompare(a.f.name));
    files = combined.map(x => x.f);
    rotations = combined.map(x => x.r);
    renderPreviews();
});

document.getElementById('mergeForm').addEventListener('submit', () => {
    const dt = new DataTransfer();
    files.forEach(f => dt.items.add(f));
    finalInput.files = dt.files;
    fileNamesOrder.value = files.map(f => f.name).join(',');
    fileRotations.value = rotations.join(',');

    // ✅ Add these lines to reset after slight delay (download will trigger immediately)
    setTimeout(() => {
        files = [];
        rotations = [];
        fileInput.value = '';
        finalInput.value = '';
        previewArea.innerHTML = '';
        updateMergeButtonState();
    }, 1500); // wait for download to trigger
});
function updateMergeButtonState() {
    const mergeBtn = document.getElementById('mergeBtn');
    if (files.length >= 2) {
        mergeBtn.disabled = false;
    } else {
        mergeBtn.disabled = true;
    }
}

</script>
@endsection