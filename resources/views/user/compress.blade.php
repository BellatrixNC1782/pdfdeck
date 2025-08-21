@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    <button id="chooseBtn" class="border px-4 py-2 rounded mb-6">Add PDF(s)</button>
    <input type="file" id="picker" multiple accept=".pdf" hidden>

    <!-- thumbnails -->
    <div id="thumbWrap" class="flex flex-wrap gap-6 mb-10"></div>

    <!-- target size -->
    <div class="max-w-md bg-gray-50 p-4 rounded shadow mb-8">
        <h3 class="font-semibold text-lg mb-3">Target File Size</h3>
        <div class="flex gap-2 items-center">
            <input type="number" id="sizeInput" min="1"
                   class="border rounded px-2 py-1 w-32" placeholder="e.g. 500">
            <select id="unitInput" class="border rounded px-2 py-1">
                <option value="KB">KB</option>
                <option value="MB">MB</option>
            </select>
        </div>
    </div>

    <!-- form -->
    <form id="compressForm" action="{{ route('compress.run') }}" method="POST"
          enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="hidden" name="rotations"    id="rotField">
        <input type="hidden" name="target_bytes" id="bytesField">
        <input type="file"  name="pdfs[]"        id="realInput" multiple hidden>

        <button type="submit"
                class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700">
            Compress PDF
        </button>
    </form>
</div>

<!-- pdf.js (legacy v3) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>

<script>
    /* ---------- state ---------- */
    const files = [];
    const angles = [];
    const picker = document.getElementById('picker');
    const realInp = document.getElementById('realInput');
    const form = document.getElementById('compressForm');
    const thumb = document.getElementById('thumbWrap');

    /* ---------- helpers ---------- */
    function sync() {
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        realInp.files = dt.files;
        document.getElementById('rotField').value = angles.join(',');
        form.classList.toggle('hidden', files.length === 0);
    }

    function renderPage(file, canvas) {
        file.arrayBuffer().then(buf =>
            pdfjsLib.getDocument({data: buf}).promise.then(pdf =>
                pdf.getPage(1).then(page => {
                    const vp = page.getViewport({scale: 0.25});
                    canvas.width = vp.width;
                    canvas.height = vp.height;
                    page.render({canvasContext: canvas.getContext('2d'), viewport: vp});
                })
            )
        );
    }

    function refreshThumbs() {
        thumb.innerHTML = '';
        files.forEach((file, i) => {
            const box = document.createElement('div');
            box.className = 'relative group w-40';
            box.style.transform = `rotate(${angles[i]}deg)`;

            const cv = document.createElement('canvas');
            cv.className = 'border rounded shadow w-full';
            box.appendChild(cv);
            renderPage(file, cv);

            const tag = document.createElement('span');
            tag.textContent = (file.size / 1024).toFixed(1) + ' KB';
            tag.className = 'absolute top-1 left-1 text-[10px] bg-black text-white px-1 rounded opacity-0 group-hover:opacity-90';
            box.appendChild(tag);

            const rot = document.createElement('button');
            rot.textContent = '↻';
            rot.title = 'Rotate 90°';
            rot.className = 'absolute -top-3 -right-3 bg-yellow-400 text-white w-6 h-6 rounded-full';
            rot.onclick = () => {
                angles[i] = (angles[i] + 90) % 360;
                box.style.transform = `rotate(${angles[i]}deg)`;
                sync();
            };
            box.appendChild(rot);

            const del = document.createElement('button');
            del.textContent = '✕';
            del.className = 'absolute -bottom-3 -right-3 bg-red-600 text-white w-6 h-6 rounded-full';
            del.onclick = () => {
                files.splice(i, 1);
                angles.splice(i, 1);
                refreshThumbs();
                sync();
            };
            box.appendChild(del);

            thumb.appendChild(box);
        });
    }

    /* ---------- events ---------- */
    document.getElementById('chooseBtn').onclick = () => picker.click();

    picker.addEventListener('change', () => {
        [...picker.files].forEach(f => {
            files.push(f);
            angles.push(0);
        });
        refreshThumbs();
        sync();
        picker.value = '';
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const size = parseFloat(document.getElementById('sizeInput').value);
        const unit = document.getElementById('unitInput').value;
        if (isNaN(size) || size <= 0) {
            alert('Enter target size');
            return;
        }
        document.getElementById('bytesField').value =
                Math.floor(size * (unit === 'MB' ? 1024 * 1024 : 1024));

        const fd = new FormData(form);
        const r = await fetch(form.action, {method: 'POST', body: fd,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}});
        if (!r.ok) {
            alert('Compression failed');
            return;
        }

        const blob = await r.blob();
        const type = r.headers.get('Content-Type');
        let ext = type.includes('zip') ? 'zip' : 'pdf';
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'compressed.' + ext;
        document.body.appendChild(a);
        a.click();
        a.remove();
    });
</script>
@endsection
