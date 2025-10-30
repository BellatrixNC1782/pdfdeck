@extends('layouts.app')
 
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Convert Word → PDF</h2>
        <p class="text-gray-500 mt-2">Upload your Word document and convert it to PDF</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-6">
        <label id="dropZone" for="inputFile"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-blue-500 hover:bg-blue-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop Word file here</span>
            <input type="file" name="file" id="inputFile" 
                   accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
                   class="hidden" required>
        </label>
    </div>

    <!-- File name preview -->
    <div class="text-center mb-6 hidden" id="fileNameWrapper">
        <p class="text-sm text-gray-700 font-medium">Selected file:</p>
        <p id="fileName" class="text-blue-600 font-semibold"></p>
    </div>

    <!-- Form -->
    <div class="text-center">
        <form action="{{ route('word.to.pdf.store') }}" method="POST" enctype="multipart/form-data" id="wordForm" target="downloadFrame">
            @csrf
            <input type="file" name="file" id="hiddenFile" hidden>
            <button type="submit" class="px-8 py-4 bg-blue-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" id="convertBtn" disabled>
                Convert to PDF
            </button>
        </form>
        <!-- Hidden iframe to catch file download -->
        <iframe name="downloadFrame" class="hidden"></iframe>
    </div>
</div>

<script>
    
    const dropZone = document.getElementById('dropZone');

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles.length > 0) {
            for (const file of droppedFiles) {
                if (!file.name.toLowerCase().match(/\.(doc|docx)$/) && !file.type.includes('word')) {
                    alert("Invalid file! Only Word files are allowed.");
                    return; // Exit on first invalid file
                }
            }
            inputFile.files = droppedFiles;
            inputFile.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    
    const inputFile = document.getElementById('inputFile');
    const hiddenFile = document.getElementById('hiddenFile');
    const convertBtn = document.getElementById('convertBtn');
    const fileNameWrapper = document.getElementById('fileNameWrapper');
    const fileName = document.getElementById('fileName');
    const wordForm = document.getElementById('wordForm');
    
    inputFile.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (!file.name.toLowerCase().match(/\.(doc|docx)$/) && !file.type.includes('word')) {
                alert("Invalid file! Only Word files are allowed.");
                resetSelection();
                return;
            }
            // show selected file name
            fileName.textContent = file.name;
            fileNameWrapper.classList.remove('hidden');

            // copy file to hidden input
            const dt = new DataTransfer();
            dt.items.add(file);
            hiddenFile.files = dt.files;

            convertBtn.disabled = false;
        } else {
            resetSelection();
        }
    });


    // reset selection function
    function resetSelection() {
        inputFile.value = '';
        hiddenFile.value = '';
        fileNameWrapper.classList.add('hidden');
        fileName.textContent = '';
        convertBtn.disabled = true;
    }

    // after form submission (download starts), reset file selection
    wordForm.addEventListener('submit', () => {
        // Show loader immediately
        document.getElementById("loaderOverlay").classList.remove("hidden");
        setTimeout(() => {
            resetSelection();
            document.getElementById("loaderOverlay").classList.add("hidden");
        }, 2000);
    });
</script>
@endsection
