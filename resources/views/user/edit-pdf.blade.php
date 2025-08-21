<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit PDF – Like iLovePDF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.12.313/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

<!-- Header -->
<header class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-semibold">Edit PDF</h1>
    <div class="space-x-2">
        <button onclick="addText()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Text</button>
        <button onclick="triggerImage()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add Image</button>
        <button onclick="exportPDF()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Export PDF</button>
        <input type="file" id="imgUploader" accept="image/*" class="hidden">
    </div>
</header>

<!-- Body -->
<div class="flex h-[calc(100vh-72px)]">
    <!-- Sidebar -->
    <aside class="bg-white w-40 p-4 border-r overflow-y-auto">
        <form id="uploadForm" class="space-y-2">
            <input type="file" name="pdf" id="pdfFile" accept=".pdf" class="w-full text-sm" required>
            <button type="submit" class="w-full bg-gray-800 text-white py-1 rounded">Upload PDF</button>
        </form>
        <div id="page-thumbnails" class="mt-4 space-y-2 text-center text-xs text-gray-600"></div>
    </aside>

    <!-- Canvas -->
    <main class="flex-1 flex justify-center items-start overflow-auto p-4">
        <canvas id="pdf-canvas" class="shadow border"></canvas>
    </main>
</div>

<script>
let pdfUrl = null;
let pdfDoc = null;
let pageNum = 1;
let fabricCanvas = new fabric.Canvas('pdf-canvas');
let scale = 1.5;

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('pdf', document.getElementById('pdfFile').files[0]);

    fetch('{{ route('upload.pdf') }}', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(res => res.json())
    .then(data => {
        pdfUrl = data.url;
        loadPDF(pdfUrl);
    });
});

function loadPDF(url) {
    const loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(function(pdf) {
        pdfDoc = pdf;
        renderPage(1);
        renderThumbnails();
    });
}

function renderThumbnails() {
    const container = document.getElementById('page-thumbnails');
    container.innerHTML = '';
    for (let i = 1; i <= pdfDoc.numPages; i++) {
        const thumb = document.createElement('div');
        thumb.innerText = `Page ${i}`;
        thumb.classList = "cursor-pointer hover:underline";
        thumb.onclick = () => renderPage(i);
        container.appendChild(thumb);
    }
}

function renderPage(num) {
    pageNum = num;
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: scale });
        const canvas = document.getElementById('pdf-canvas');
        const context = canvas.getContext('2d');

        canvas.width = viewport.width;
        canvas.height = viewport.height;
        fabricCanvas.setWidth(viewport.width);
        fabricCanvas.setHeight(viewport.height);
        fabricCanvas.clear();

        const renderContext = {
            canvasContext: context,
            viewport: viewport
        };

        page.render(renderContext).promise.then(function() {
            const dataUrl = canvas.toDataURL("image/png");
            fabric.Image.fromURL(dataUrl, function(img) {
                fabricCanvas.setBackgroundImage(img, fabricCanvas.renderAll.bind(fabricCanvas));
            });
        });
    });
}

function addText() {
    const textbox = new fabric.Textbox('Edit text', {
        left: 50,
        top: 50,
        width: 200,
        fontSize: 18,
        fill: 'black'
    });
    fabricCanvas.add(textbox).setActiveObject(textbox);
}

function triggerImage() {
    document.getElementById('imgUploader').click();
}

document.getElementById('imgUploader').addEventListener('change', function(e) {
    const reader = new FileReader();
    reader.onload = function(f) {
        fabric.Image.fromURL(f.target.result, function(img) {
            img.scaleToWidth(150);
            img.set({ left: 100, top: 100 });
            fabricCanvas.add(img);
        });
    };
    reader.readAsDataURL(e.target.files[0]);
});

function exportPDF() {
    html2canvas(fabricCanvas.lowerCanvasEl).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const pdf = new jspdf.jsPDF({
            orientation: "portrait",
            unit: "pt",
            format: [canvas.width, canvas.height]
        });

        pdf.addImage(imgData, 'PNG', 0, 0, canvas.width, canvas.height);
        pdf.save("edited.pdf");
    });
}
</script>
</body>
</html>
