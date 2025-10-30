@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Excel → PDF</h2>
        <p class="text-gray-500 mt-2">Upload your Excel file (.xls or .xlsx) and convert it to PDF</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-6">
        <label id="dropZone" for="excelInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-green-500 hover:bg-green-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop Excel file here</span>
            <input type="file" name="file" id="excelInput"
                   accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                   class="hidden" required>
        </label>
    </div>

    <!-- File name preview -->
    <div class="text-center mb-6 hidden" id="fileNameWrapper">
        <p class="text-sm text-gray-700 font-medium">Selected file:</p>
        <p id="fileName" class="text-green-600 font-semibold"></p>
    </div>

    <!-- Form -->
    <div class="text-center">
        <form action="{{ route('excel2pdf') }}" method="POST" enctype="multipart/form-data" id="excelForm" target="downloadFrame">
            @csrf
            <input type="file" name="file" id="hiddenExcelFile" hidden>
            <button type="submit" class="px-8 py-4 bg-green-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" id="convertBtn" disabled>
                Convert to PDF
            </button>
        </form>
        <!-- Hidden iframe to catch download -->
        <iframe name="downloadFrame" class="hidden"></iframe>

        @if(session('error'))
        <div class="text-red-600 mt-4">{{ session('error') }}</div>
        @endif
    </div>
</div>

<script>
    
    
    const dropZone = document.getElementById('dropZone');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-green-500', 'bg-green-50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-green-500', 'bg-green-50');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles.length > 0) {
            for (const file of droppedFiles) {
                if (!file.name.toLowerCase().match(/\.(xls|xlsx)$/) &&
                    !file.type.includes('spreadsheet') &&
                    !file.type.includes('excel')) {
                    alert("Invalid file! Only Excel files are allowed.");
                    return;
                }
            }
            excelInput.files = droppedFiles;
            excelInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    
    
    const excelInput = document.getElementById('excelInput');
    const hiddenExcelFile = document.getElementById('hiddenExcelFile');
    const convertBtn = document.getElementById('convertBtn');
    const fileNameWrapper = document.getElementById('fileNameWrapper');
    const fileName = document.getElementById('fileName');
    const excelForm = document.getElementById('excelForm');

    excelInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (!file.name.toLowerCase().match(/\.(xls|xlsx)$/) &&
                !file.type.includes('spreadsheet') &&
                !file.type.includes('excel')) {
                alert("Invalid file! Only Excel files are allowed.");
                resetSelection();
                return;
            }
            // show file name
            fileName.textContent = file.name;
            fileNameWrapper.classList.remove('hidden');

            // copy file to hidden input
            const dt = new DataTransfer();
            dt.items.add(file);
            hiddenExcelFile.files = dt.files;

            convertBtn.disabled = false;
        } else {
            resetSelection();
        }
    });

    // reset selection function
    function resetSelection() {
        excelInput.value = '';
        hiddenExcelFile.value = '';
        fileNameWrapper.classList.add('hidden');
        fileName.textContent = '';
        convertBtn.disabled = true;
    }

    // clear selection after download starts
    excelForm.addEventListener('submit', () => {
        // Show loader immediately
        document.getElementById("loaderOverlay").classList.remove("hidden");
        setTimeout(() => {
            resetSelection();
            document.getElementById("loaderOverlay").classList.add("hidden");
        }, 2000);
    });
</script>
@endsection
