@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">Add Page Numbers</h3>

    <form method="POST" action="{{ route('pdf.page_numbers.process') }}" enctype="multipart/form-data" id="pageNumberForm">
        @csrf

        <div class="mb-3">
            <input type="file" id="pdfInput" accept="application/pdf" multiple>
        </div>

        <div class="mb-3" id="pdfSelectors" style="display:none;">
            <label>Select PDF:</label>
            <select id="selectedPdf" class="form-select w-auto d-inline-block"></select>
            <button type="button" class="btn btn-link text-danger" id="deleteSelectedPdf">Delete</button>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Position</label>
                <select name="position" class="form-control" required>
                    <option value="bottom_right">Bottom Right</option>
                    <option value="bottom_left">Bottom Left</option>
                    <option value="top_right">Top Right</option>
                    <option value="top_left">Top Left</option>
                </select>
            </div>
            <div class="col-md-6">
                <label>First Number</label>
                <input type="number" name="first_number" class="form-control" placeholder="e.g. 1">
            </div>
        </div>

        <div class="row mb-3">
<!--            <div class="col-md-6">
                <label>Text Template</label>
                <input type="text" name="text_template" class="form-control" placeholder="e.g. Page {n}">
            </div>-->
            <div class="col-md-6">
                <label>Page Range</label>
                <input type="text" name="page_range" class="form-control" placeholder="e.g. 1-5 (optional)">
            </div>
        </div>

        <input type="hidden" name="pdf_file_names" id="pdfFileNames">
        <input type="file" name="pdfs[]" id="finalPdfInput" multiple hidden>

        <button type="submit" class="btn btn-danger">Add Page Numbers</button>
    </form>

    <div class="row mt-4" id="pdfPreviews"></div>
</div>

<script>
    const pdfInput = document.getElementById('pdfInput');
    const finalPdfInput = document.getElementById('finalPdfInput');
    const pdfSelectors = document.getElementById('pdfSelectors');
    const selectedPdf = document.getElementById('selectedPdf');
    const deleteSelectedPdf = document.getElementById('deleteSelectedPdf');
    const pdfFileNames = document.getElementById('pdfFileNames');
    const pdfPreviews = document.getElementById('pdfPreviews');

    let pdfFiles = [];

    pdfInput.addEventListener('change', (e) => {
        const newFiles = Array.from(e.target.files);

        newFiles.forEach(file => {
            if (!pdfFiles.some(f => f.name === file.name && f.size === file.size)) {
                pdfFiles.push(file);
            }
        });

        refreshPdfDropdown();
        renderPdfPreviews();
        pdfInput.value = '';
    });

    function refreshPdfDropdown() {
        selectedPdf.innerHTML = '';
        pdfFiles.forEach((file, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = file.name;
            selectedPdf.appendChild(option);
        });

        pdfSelectors.style.display = pdfFiles.length > 0 ? 'block' : 'none';
        pdfFileNames.value = pdfFiles.map(f => f.name).join(',');
    }

    function renderPdfPreviews() {
        pdfPreviews.innerHTML = '';
        pdfFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-3';

                const card = document.createElement('div');
                card.className = 'border p-2';

                const label = document.createElement('div');
                label.className = 'mb-2 small text-center';
                label.textContent = file.name;

                const iframe = document.createElement('iframe');
                iframe.src = e.target.result;
                iframe.style.width = '100%';
                iframe.style.height = '300px';
                iframe.style.border = '1px solid #ccc';

                card.appendChild(label);
                card.appendChild(iframe);
                col.appendChild(card);
                pdfPreviews.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    }

    deleteSelectedPdf.addEventListener('click', () => {
        const index = parseInt(selectedPdf.value);
        pdfFiles.splice(index, 1);
        refreshPdfDropdown();
        renderPdfPreviews();
    });

    const form = document.getElementById('pageNumberForm');

    form.addEventListener('submit', function (e) {
        if (pdfFiles.length === 0) {
            e.preventDefault();
            alert('Please select at least one PDF.');
            return;
        }

        // ✅ Create DataTransfer and assign to finalPdfInput.files
        const dt = new DataTransfer();
        pdfFiles.forEach(file => dt.items.add(file));
        finalPdfInput.files = dt.files;

        // ✅ Allow regular form submit to go through with populated hidden input
        setTimeout(() => {
            pdfFiles = [];
            pdfInput.value = '';
            pdfPreviews.innerHTML = '';
            selectedPdf.innerHTML = '';
            pdfSelectors.style.display = 'none';
            pdfFileNames.value = '';
            finalPdfInput.value = '';
        }, 1500); // Adjust delay if needed
    });
</script>
@endsection
