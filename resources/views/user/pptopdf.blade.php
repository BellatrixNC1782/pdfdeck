@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">PowerPoint → PDF</h2>
        <p class="text-gray-500 mt-2">Upload your PowerPoint file (.ppt or .pptx) and convert it to PDF</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-6">
        <label id="dropZone" for="pptInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-orange-500 hover:bg-orange-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop PowerPoint file here</span>
            <input type="file" name="file" id="pptInput"
                   accept=".ppt,.pptx,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation"
                   class="hidden" required>
        </label>
    </div>

    <!-- File name preview -->
    <div class="text-center mb-6 hidden" id="fileNameWrapper">
        <p class="text-sm text-gray-700 font-medium">Selected file:</p>
        <p id="fileName" class="text-orange-600 font-semibold"></p>
    </div>

    <!-- Form -->
    <div class="text-center">
        <form action="{{ route('ppt2pdf') }}" method="POST" enctype="multipart/form-data" id="pptForm" target="downloadFrame">
            @csrf
            <input type="file" name="file" id="hiddenPptFile" hidden>
            <button type="submit" class="px-8 py-4 bg-orange-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed" id="convertBtn" disabled>
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
            dropZone.classList.add('border-orange-500', 'bg-orange-50');
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-orange-500', 'bg-orange-50');
        }, false);
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles.length > 0) {
            for (const file of droppedFiles) {
                if (!file.name.toLowerCase().match(/\.(ppt|pptx)$/) && 
                    !file.type.includes('powerpoint') && 
                    !file.type.includes('presentationml')) {
                    alert("Invalid file! Only PowerPoint files are allowed.");
                    return;
                }
            }
            pptInput.files = droppedFiles;
            pptInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    
    const pptInput = document.getElementById('pptInput');
    const hiddenPptFile = document.getElementById('hiddenPptFile');
    const convertBtn = document.getElementById('convertBtn');
    const fileNameWrapper = document.getElementById('fileNameWrapper');
    const fileName = document.getElementById('fileName');
    const pptForm = document.getElementById('pptForm');
    
    pptInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (!file.name.toLowerCase().match(/\.(ppt|pptx)$/) && 
                !file.type.includes('powerpoint') && 
                !file.type.includes('presentationml')) {
                alert("Invalid file! Only PowerPoint files are allowed.");
                resetSelection();
                return;
            }

            // show file name
            fileName.textContent = file.name;
            fileNameWrapper.classList.remove('hidden');

            // copy to hidden input
            const dt = new DataTransfer();
            dt.items.add(file);
            hiddenPptFile.files = dt.files;

            convertBtn.disabled = false;
        } else {
            resetSelection();
        }
    });

    // reset selection function
    function resetSelection() {
        pptInput.value = '';
        hiddenPptFile.value = '';
        fileNameWrapper.classList.add('hidden');
        fileName.textContent = '';
        convertBtn.disabled = true;
    }

    // clear selection after download starts
    pptForm.addEventListener('submit', () => {
        // Show loader immediately
        document.getElementById("loaderOverlay").classList.remove("hidden");
        setTimeout(() => {
            resetSelection();
            document.getElementById("loaderOverlay").classList.add("hidden");
        }, 3000);
    });
</script>
@endsection
