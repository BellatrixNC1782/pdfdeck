@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Convert JPGs → PDF</h2>
        <p class="text-gray-500 mt-2">Upload, rotate, and convert images into a single PDF</p>
    </div>

    <!-- Upload -->
    <div class="flex justify-center mb-10">
        <label for="imageInput"
               class="cursor-pointer border-2 border-dashed border-gray-300 rounded-2xl px-10 py-8 flex flex-col items-center hover:border-gray-500 hover:bg-gray-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-gray-600 font-medium">Click or drag & drop images here</span>
            <input type="file" id="imageInput" name="images[]" multiple accept="image/*" class="hidden">
        </label>
    </div>

    <!-- Preview Area -->
    <div id="previewContainer" class="flex flex-wrap gap-4"></div>

    <!-- Convert Button -->
    <div class="text-center mt-10">
        <form method="POST" action="{{ route('jpg.convert') }}" enctype="multipart/form-data" id="convertForm">
            @csrf
            <input type="hidden" name="rotation_data" id="rotationDataInput">
                <button type="submit" class="px-8 py-4 bg-gray-600 text-white text-lg font-semibold rounded-full shadow-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Convert & Download PDF
                </button>
        </form>
    </div>
</div>

<style>
    .preview-wrapper {
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 12px;
        background: #f1f5f9;   /* light gray so rotation is visible */
        border: 1px solid #e2e8f0; /* subtle border */
        position: relative;
        margin-bottom: 12px;   /* space for degree label/buttons */
    }

    .preview-img {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        transition: transform 0.3s ease;
        transform-origin: center center;
    }
    .degree-label {
        margin-bottom: 8px;
        background: #facc15; /* amber-400 */
        color: #1f2937;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 0.875rem;
    }
    .remove-btn {
        position: absolute;
        top: -8px;   /* pull it up */
        right: -8px; /* pull it right */
        background: #ef4444; /* gray-500 */
        color: #fff;
        border-radius: 9999px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        line-height: 1;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: background 0.2s;
    }
    .remove-btn:hover {
        background: #dc2626; /* darker gray */
    }

</style>

<script>
    let imagesData = [];
    let imageCounter = 0;
    let storedFiles = [];

    document.getElementById('imageInput').addEventListener('change', function (e) {
        const files = Array.from(e.target.files);

        files.forEach((file) => {
            const reader = new FileReader();
            const currentIndex = imageCounter++;

            reader.onload = function (event) {
                const card = document.createElement('div');
                card.className = "relative bg-white rounded-2xl shadow-md w-52 p-4 flex flex-col items-center justify-between";
                card.dataset.index = currentIndex;

                // Top bar with remove
                const removeBtn = document.createElement('button');
                removeBtn.innerHTML = "✕";
                removeBtn.className = "remove-btn";

                removeBtn.onclick = () => {
                    card.remove();
                    imagesData = imagesData.filter(i => i.index !== currentIndex);
                    storedFiles = storedFiles.filter((_, i) => i !== currentIndex);
                };

                card.appendChild(removeBtn);


                // Image preview wrapper
                const wrapper = document.createElement('div');
                wrapper.className = "preview-wrapper";

                const img = document.createElement('img');
                img.src = event.target.result;
                img.className = "preview-img";
                img.dataset.index = currentIndex;
                img.id = `img_${currentIndex}`;
                img.style.transform = "rotate(0deg)";

                wrapper.appendChild(img);
                card.appendChild(wrapper);

                // Degree label
                const degreeLabel = document.createElement('div');
                degreeLabel.textContent = "0°";
                degreeLabel.className = "degree-label";

                // Controls
                const controls = document.createElement('div');
                controls.className = "flex justify-center items-center gap-3 mt-2";

                const rotateLeft = document.createElement('button');
                rotateLeft.innerHTML = "⟲";
                rotateLeft.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
                rotateLeft.addEventListener('click', () => {
                    const data = imagesData.find(i => i.index == currentIndex);
                    data.rotation = (data.rotation - 90 + 360) % 360;
                    img.style.transform = `rotate(${data.rotation}deg)`;
                    degreeLabel.textContent = `${data.rotation}°`;
                });

                const rotateRight = document.createElement('button');
                rotateRight.innerHTML = "⟳";
                rotateRight.className = "bg-gray-800 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-gray-700";
                rotateRight.addEventListener('click', () => {
                    const data = imagesData.find(i => i.index == currentIndex);
                    data.rotation = (data.rotation + 90) % 360;
                    img.style.transform = `rotate(${data.rotation}deg)`;
                    degreeLabel.textContent = `${data.rotation}°`;
                });

                controls.appendChild(rotateLeft);
                controls.appendChild(rotateRight);

                card.appendChild(degreeLabel);
                card.appendChild(controls);

                document.getElementById('previewContainer').appendChild(card);
            };

            imagesData.push({name: file.name, index: currentIndex, rotation: 0});
            storedFiles.push(file);
            reader.readAsDataURL(file);
        });

        e.target.value = '';
    });

    document.getElementById('convertForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        storedFiles.forEach(file => formData.append('images[]', file));
        formData.append('rotation_data', JSON.stringify(imagesData.map(({ name, rotation }) => ({name, rotation}))));
        if (storedFiles.length === 0) {
            alert('Please upload at least one image.');
            return;
        }

        // Show loader
        document.getElementById('loaderOverlay').classList.remove('hidden');

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
            .then(res => res.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'converted.pdf';
                link.click();
                window.URL.revokeObjectURL(url);
            })
            .finally(() => {
                // Hide loader
                document.getElementById('loaderOverlay').classList.add('hidden');

                // Reset UI
                document.getElementById('previewContainer').innerHTML = '';
                imagesData = [];
                storedFiles = [];
                imageCounter = 0;
            });
    });
</script>

@endsection
