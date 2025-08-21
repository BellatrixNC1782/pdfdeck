@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-4">Crop PDF (iLovePDF Style)</h2>

    <form id="cropForm" method="POST" action="{{ route('pdf.crop.process') }}" enctype="multipart/form-data">
        @csrf

        <input type="file" name="pdf" id="pdfInput" accept=".pdf" class="mb-4 block" required>

        <div class="mb-4">
            <label><input type="radio" name="crop_scope" value="all" checked> Apply to all pages</label>
            <label class="ml-4"><input type="radio" name="crop_scope" value="current"> Apply to current page only</label>
        </div>

        <div id="pdfPreviewWrapper" class="grid gap-4 max-h-[80vh] overflow-y-scroll border p-2 bg-gray-100 mb-4">
            <!-- Canvases will be rendered here -->
        </div>

        <input type="hidden" name="crop_data" id="cropData">
        <input type="hidden" name="total_pages" id="totalPages">

        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Crop PDF</button>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';

    const wrapper = document.getElementById('pdfPreviewWrapper');
    const pdfInput = document.getElementById('pdfInput');
    const cropDataField = document.getElementById('cropData');
    const totalPagesField = document.getElementById('totalPages');

    let cropMap = {};
    let scale = 1.5;
    let pdfDoc = null;

    pdfInput.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file || file.type !== "application/pdf") return;

        const reader = new FileReader();
        reader.onload = async function (e) {
            const typedarray = new Uint8Array(e.target.result);
            pdfDoc = await pdfjsLib.getDocument(typedarray).promise;

            wrapper.innerHTML = '';
            cropMap = {};
            totalPagesField.value = pdfDoc.numPages;

            for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale });

                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.dataset.page = pageNum;

                const ctx = canvas.getContext('2d');
                await page.render({ canvasContext: ctx, viewport }).promise;

                wrapper.appendChild(canvas);

                const cropper = new Cropper(canvas, {
                    viewMode: 1,
                    autoCrop: false,
                    background: false,
                    cropend() {
                        cropMap[pageNum] = cropper.getData(true);
                    }
                });
            }
        };
        reader.readAsArrayBuffer(file);
    });

    document.getElementById('cropForm').addEventListener('submit', function (e) {
        const scope = document.querySelector('input[name=\"crop_scope\"]:checked').value;
        const cleaned = {};
        for (const [page, crop] of Object.entries(cropMap)) {
            if (crop?.width > 0 && crop?.height > 0) {
                cleaned[page] = crop;
            }
        }

        if (scope === 'all') {
            if (!cleaned[1]) {
                e.preventDefault();
                alert("Please crop page 1 to apply to all.");
                return;
            }
            cropDataField.value = JSON.stringify(cleaned[1]);
        } else {
            if (Object.keys(cleaned).length === 0) {
                e.preventDefault();
                alert("Please crop at least one page.");
                return;
            }
            cropDataField.value = JSON.stringify(cleaned);
        }
    });
</script>
@endsection
