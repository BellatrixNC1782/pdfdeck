@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Convert JPGs to PDF</h2>
   
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('jpg.convert') }}" enctype="multipart/form-data" id="convertForm">
        @csrf

        <div class="mb-3">
            <label class="form-label">Upload JPG or PNG Images</label>
            <input type="file" name="images[]" id="imageInput" class="form-control" accept="image/*" multiple>
        </div>

        <div class="row" id="previewContainer"></div>

        <input type="hidden" name="rotation_data" id="rotationDataInput">

        <button type="submit" class="btn btn-primary mt-3">Convert to PDF</button>
    </form>
</div>

<style>
    .preview-box {
        position: relative;
        margin: 10px;
    }

    .card {
        position: relative;
        overflow: hidden;
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-box img {
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .btn-group {
        position: absolute;
        top: 5px;
        right: 5px;
        z-index: 1;
    }
</style>
<script>
    let imagesData = [];
    let imageCounter = 0;
    let storedFiles = []; // NEW: store File objects

    document.getElementById('imageInput').addEventListener('change', function (e) {
        const files = Array.from(e.target.files);

        files.forEach((file) => {
            const reader = new FileReader();
            const currentIndex = imageCounter++;

            reader.onload = function (event) {
                const box = document.createElement('div');
                box.className = 'col-md-3 preview-box';
                box.innerHTML = `
                    <div class="card">
                        <img src="${event.target.result}" class="preview-img" data-index="${currentIndex}" id="img_${currentIndex}" style="transform: rotate(0deg);">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-warning rotate-btn" data-index="${currentIndex}">⟳</button>
                            <button type="button" class="btn btn-sm btn-danger remove-btn" data-index="${currentIndex}">✖</button>
                        </div>
                    </div>
                `;
                document.getElementById('previewContainer').appendChild(box);
            };

            imagesData.push({name: file.name, index: currentIndex, rotation: 0});
            storedFiles.push(file); // Save the File object
            reader.readAsDataURL(file);
        });

        // Clear input so same files can be re-selected if needed
        e.target.value = '';
    });

    document.getElementById('previewContainer').addEventListener('click', function (e) {
        if (e.target.classList.contains('rotate-btn')) {
            const index = e.target.getAttribute('data-index');
            const img = document.querySelector(`img[data-index="${index}"]`);
            const data = imagesData.find(i => i.index == index);
            if (data && img) {
                data.rotation = (data.rotation + 90) % 360;
                img.style.transform = `rotate(${data.rotation}deg)`;
            }
        }

        if (e.target.classList.contains('remove-btn')) {
            const index = parseInt(e.target.getAttribute('data-index'));
            e.target.closest('.preview-box').remove();

            imagesData = imagesData.filter(i => i.index !== index);
            storedFiles = storedFiles.filter((_, i) => i !== index); // remove matching file
        }
    });

    document.getElementById('convertForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Stop default for now

        const formData = new FormData(this);
        storedFiles.forEach(file => formData.append('images[]', file));
        formData.append('rotation_data', JSON.stringify(imagesData.map(({ name, rotation }) => ({name, rotation}))));
        if (storedFiles.length === 0) {
            alert('Please upload at least one image.');
            return;
        }

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
                });

        // Reset UI
        document.getElementById('previewContainer').innerHTML = '';
        imagesData = [];
        storedFiles = [];
        imageCounter = 0;
    });

</script>

@endsection
