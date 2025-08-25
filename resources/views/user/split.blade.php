@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Split PDF</h2>
        <p class="text-gray-500 mt-2">Upload your PDF and create custom ranges</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="pdfInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-red-500 hover:bg-red-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PDF here</span>
            <input type="file" id="pdfInput" accept=".pdf" class="hidden">
        </label>
    </div>

    <!-- Thumbnails -->
    <div id="thumbWrapper" class="flex flex-wrap gap-4 justify-center mb-10"></div>

    <!-- Range builder -->
    <div class="max-w-xl mx-auto bg-gray-50 p-6 rounded-2xl shadow">
        <h3 class="font-semibold text-lg mb-3">Custom Ranges</h3>

        <div id="rangeList" class="space-y-3"></div>

        <button id="addRangeBtn"
                class="mt-2 text-blue-600 hover:underline">+ Add Range</button>

        <form id="splitForm" method="POST" action="{{ route('splitpdfprocess') }}" enctype="multipart/form-data"
              class="mt-6 text-center hidden">
            @csrf
            <input type="file" name="pdf" id="pdfInputHidden" accept=".pdf" hidden>

            <input type="hidden" name="ranges_json" id="rangesField">
            <input type="hidden" name="merge_output" id="mergeField">

            <button type="submit" class="px-8 py-4 bg-red-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-red-700" id="splitBtn">
                Split PDF
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist/build/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdn.jsdelivr.net/npm/pdfjs-dist/build/pdf.worker.min.js';
</script>

<script>
    let pdfDoc = null;
    let pageTotal = 0;
    let ranges = [];
    const thumbWrap = document.getElementById('thumbWrapper');
    const rangeList = document.getElementById('rangeList');
    const splitForm = document.getElementById('splitForm');
    const fileInput = document.getElementById('pdfInput');
    const fileInputHidden = document.getElementById('pdfInputHidden');

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        thumbWrap.innerHTML = 'Loading...';

        const data = await file.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument({data}).promise;
        pageTotal = pdfDoc.numPages;

        renderThumbs();
        initFirstRange();
        splitForm.classList.remove('hidden');

        // copy file to hidden field
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInputHidden.files = dt.files;
    });

    function renderThumbs() {
        thumbWrap.innerHTML = '';
        for (let i = 1; i <= pageTotal; i++) {
            const div = document.createElement('div');
            div.className = "relative bg-white rounded-2xl shadow-md w-40 p-2 flex flex-col items-center";

            const canvas = document.createElement('canvas');
            canvas.className = "max-h-32 object-contain border rounded";
            canvas.dataset.page = i;

            div.appendChild(canvas);
            thumbWrap.appendChild(div);

            renderPageToCanvas(i, canvas).then(() => highlightCanvas(canvas));
        }
    }

    async function renderPageToCanvas(num, canvas) {
        const page = await pdfDoc.getPage(num);
        const scale = 0.4;
        const viewport = page.getViewport({scale});
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');
        await page.render({canvasContext: ctx, viewport}).promise;
    }

    function highlightCanvas(canvas) {
        const pageNum = parseInt(canvas.dataset.page);
        const inRange = ranges.some(([f, t]) => pageNum >= f && pageNum <= t);
        canvas.classList.toggle('ring-4', inRange);
        canvas.classList.toggle('ring-red-500', inRange);
    }

    function addRangeRow(from = '', to = '') {
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';
        div.innerHTML = `
            <input type="number" min="1" max="${pageTotal}" value="${from}"
                   class="from w-1/2 border p-2 rounded" placeholder="From">
            <input type="number" min="1" max="${pageTotal}" value="${to}"
                   class="to w-1/2 border p-2 rounded" placeholder="To">
            <button class="remove text-red-500">&times;</button>
        `;
        rangeList.appendChild(div);

        div.querySelectorAll('input').forEach(inp =>
            inp.addEventListener('input', updateRanges));

        div.querySelector('.remove').onclick = () => {
            div.remove();
            updateRanges();
        };
    }

    function initFirstRange() {
        rangeList.innerHTML = '';
        addRangeRow(1, pageTotal);
        updateRanges();
    }

    document.getElementById('addRangeBtn').onclick = () => addRangeRow();

    function updateRanges() {
        ranges = [];
        rangeList.querySelectorAll('div').forEach(row => {
            const f = parseInt(row.querySelector('.from').value);
            const t = parseInt(row.querySelector('.to').value);
            if (!isNaN(f) && !isNaN(t) && f <= t)
                ranges.push([f, t]);
        });
        thumbWrap.querySelectorAll('canvas').forEach(highlightCanvas);
    }

    splitForm.addEventListener('submit', async e => {
        e.preventDefault();

        // Show loader
        document.getElementById('loaderOverlay').classList.remove('hidden');

        const fd = new FormData();
        fd.append('pdf', fileInputHidden.files[0]);
        fd.append('ranges_json', JSON.stringify(ranges));

        try {
            const resp = await fetch(splitForm.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            if (!resp.ok) {
                alert('Split failed');
                return;
            }

            const blob = await resp.blob();
            const url  = URL.createObjectURL(blob);
            const a    = Object.assign(document.createElement('a'), {
                href: url,
                download: resp.headers.get('Content-Disposition')
                            ?.split('filename=')[1]?.replaceAll('"','') || 'split.pdf'
            });
            document.body.appendChild(a); 
            a.click(); 
            a.remove();
            URL.revokeObjectURL(url);

            // ✅ Reset UI after download
            resetUI();
        } catch (err) {
            alert('Something went wrong');
            console.error(err);
        } finally {
            // Hide loader
            document.getElementById('loaderOverlay').classList.add('hidden');
        }
    });

    function resetUI() {
        // Clear thumbnails
        thumbWrap.innerHTML = '';
        // Clear ranges
        rangeList.innerHTML = '';
        // Reset inputs
        fileInput.value = '';
        fileInputHidden.value = '';
        ranges = [];
        pdfDoc = null;
        pageTotal = 0;
        // Hide form again
        splitForm.classList.add('hidden');
    }
</script>
@endsection
