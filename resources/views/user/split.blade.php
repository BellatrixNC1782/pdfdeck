@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <h2 class="text-3xl font-semibold text-center mb-6">Split PDF</h2>

    {{-- Upload --}}
    <div class="flex flex-col items-center mb-6">
        <input type="file" id="pdfInput" accept=".pdf"
               class="block border px-4 py-2 rounded">
    </div>

    {{-- Thumbnails --}}
    <div id="thumbWrapper"
         class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-10"></div>

    {{-- Range builder --}}
    <div class="max-w-xl mx-auto bg-gray-50 p-4 rounded shadow">
        <h3 class="font-semibold text-lg mb-3">Custom Ranges</h3>

        <div id="rangeList" class="space-y-3"></div>

        <button id="addRangeBtn"
                class="mt-2 text-blue-600 hover:underline">+ Add Range</button>

<!--        <label class="flex items-center gap-2 mt-4">
            <input type="checkbox" id="mergeChk">
            <span>Merge all ranges into one PDF</span>
        </label>-->

        <form id="splitForm" method="POST" action="{{ route('splitpdfprocess') }}" enctype="multipart/form-data"
              class="mt-6 text-center hidden">
            @csrf
            <input type="file" name="pdf" id="pdfInput" accept=".pdf" hidden>

            <input type="hidden" name="ranges_json" id="rangesField">
            <input type="hidden" name="merge_output" id="mergeField">

            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-full hover:bg-red-700" id="splitBtn">Split PDF</button>
        </form>
        <!--<button id="choosePdfBtn" class="border px-4 py-2 rounded mb-6">Choose PDF</button>-->
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
    let ranges = [];        // [ [from,to], ... ]
    const thumbWrap = document.getElementById('thumbWrapper');
    const rangeList = document.getElementById('rangeList');
    const splitForm = document.getElementById('splitForm');
//    const fileField = document.getElementById('fileField');
    
//    document.getElementById('choosePdfBtn').onclick = () =>
    document.getElementById('pdfInput').click();
    
    document.getElementById('pdfInput').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file)
            return;
        // show loading
        thumbWrap.innerHTML = 'Loading...';

        // Load with pdf.js
        const data = await file.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument({data}).promise;
        pageTotal = pdfDoc.numPages;

        renderThumbs();
        initFirstRange();
        splitForm.classList.remove('hidden');
        // copy file into hidden field via DataTransfer
//        const dt = new DataTransfer();
//        dt.items.add(file);
//        fileField.files = dt.files;
    });

    function renderThumbs() {
        thumbWrap.innerHTML = '';
        for (let i = 1; i <= pageTotal; i++) {
            const canvas = document.createElement('canvas');
            canvas.className = 'border rounded shadow';
            canvas.dataset.page = i;
            thumbWrap.appendChild(canvas);
            renderPageToCanvas(i, canvas).then(() => highlightCanvas(canvas));
        }
    }

    async function renderPageToCanvas(num, canvas) {
        const page = await pdfDoc.getPage(num);
        const scale = 0.3;
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

    /* ---------- Range UI ---------- */
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
        // highlight
        thumbWrap.querySelectorAll('canvas').forEach(highlightCanvas);
    }

    /* ---------- Submit ---------- */
    splitForm.addEventListener('submit', async e => {
        e.preventDefault();                 // stop normal post

        const fd = new FormData();
        fd.append('pdf', document.getElementById('pdfInput').files[0]);
        fd.append('ranges_json', JSON.stringify(ranges));
//        fd.append('merge_output', document.getElementById('mergeChk').checked ? 'true' : '');

        const resp = await fetch(splitForm.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });

        if (!resp.ok) { alert('Split failed'); return; }

        // trigger download
        const blob = await resp.blob();
        const url  = URL.createObjectURL(blob);
        const a    = Object.assign(document.createElement('a'), {
            href: url,
            download: resp.headers.get('Content-Disposition')
                        ?.split('filename=')[1]?.replaceAll('"','') || 'split.pdf'
        });
        document.body.appendChild(a); a.click(); a.remove();
        URL.revokeObjectURL(url);
    });

</script>
@endsection
