<?php include 'config.php'; ?>
<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry - VehiSecure</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/tesseract.js@v5.1.0/dist/tesseract.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-zinc-950 text-white min-h-screen">
<div class="max-w-4xl mx-auto p-8">
    <a href="index.php" class="inline-block mb-6 text-emerald-400 hover:text-emerald-300 font-medium">← Dashboard</a>
    <h1 class="text-4xl font-bold text-center mb-8">🚘 Vehicle Entry Scanner</h1>

    <div class="bg-zinc-900 rounded-3xl p-8 glass shadow-2xl">
        <video id="video" class="w-full rounded-2xl mb-6 bg-black" autoplay playsinline></video>
        <canvas id="canvas" class="hidden"></canvas>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
            <button onclick="startCamera()" class="bg-emerald-600 hover:bg-emerald-700 px-8 py-5 rounded-2xl font-bold text-lg">📸 Start Camera</button>
            <button onclick="capturePhoto()" class="bg-blue-600 hover:bg-blue-700 px-8 py-5 rounded-2xl font-bold text-lg">Capture Photo</button>
        </div>

        <div class="text-center mb-8">
            <p class="text-zinc-400 mb-3">— OR Upload Photo —</p>
            <input type="file" id="upload" accept="image/*" class="block mx-auto text-sm file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-zinc-700 file:text-white hover:file:bg-zinc-600" onchange="handleUpload(event)">
        </div>

        <div id="previewSection" class="hidden mt-10">
            <img id="preview" class="w-full max-h-[500px] object-contain rounded-2xl border-4 border-zinc-700 mb-6">

            <button onclick="runOCR()" class="bg-yellow-500 hover:bg-yellow-600 text-black px-10 py-5 rounded-2xl font-bold text-lg w-full mb-6">🔍 Extract Number Plate (OCR)</button>

            <div class="mb-6">
                <label class="block text-sm mb-2 text-zinc-300">Plate Number (editable):</label>
                <input id="plateInput" type="text" class="w-full bg-zinc-800 text-3xl font-mono p-6 rounded-2xl text-center border border-zinc-700 focus:border-emerald-500 outline-none" placeholder="TS05AB1234" maxlength="15">
            </div>

            <button onclick="saveEntry()" class="bg-emerald-600 hover:bg-emerald-700 w-full py-6 text-xl font-bold rounded-3xl">✅ SAVE ENTRY</button>
        </div>
    </div>
</div>

<script>
let video, canvas, worker = null;
let isProcessing = false;

function showStatus(msg, isError = false) {
    let el = document.getElementById('ocrStatus');
    if (!el) {
        el = document.createElement('p');
        el.id = 'ocrStatus';
        el.className = isError ? 'text-red-400 mt-4 text-center font-bold' : 'text-yellow-400 mt-4 text-center font-medium';
        document.getElementById('previewSection')?.appendChild(el) || document.body.appendChild(el);
    }
    el.textContent = msg;
    return el;
}

async function initOCR() {
    if (worker) return;
    showStatus('Loading OCR (first time 10–40s)...');

    try {
        worker = await Tesseract.createWorker({
            logger: m => { if (m.status === 'recognizing text') showStatus(`OCR: ${Math.round(m.progress*100)}%`); },
            workerPath: 'https://unpkg.com/tesseract.js@v5.1.0/dist/worker.min.js',           // ← change to local later: '/vehisecure/assets/tesseract/worker.min.js'
            langPath: 'https://tessdata.projectnaptha.com/4.0.0',                             // ← change to local: '/vehisecure/assets/tesseract/'
            corePath: 'https://unpkg.com/tesseract.js-core@v5.1.0/tesseract-core.wasm.js',   // ← change to local: '/vehisecure/assets/tesseract/tesseract-core.wasm.js'
        });

        await worker.load();
        await worker.loadLanguage('eng');
        await worker.initialize('eng');
        await worker.setParameters({
            tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            tessedit_pageseg_mode: '7',
            user_defined_dpi: '300',
            preserve_interword_spaces: '0'
        });

        let status = document.getElementById('ocrStatus');
        if (status) status.remove();
    } catch (err) {
        console.error(err);
        showStatus('OCR load failed. Use mobile hotspot or place files locally.', true);
    }
}

initOCR();

async function startCamera() {
    video = document.getElementById('video');
    try {
        video.srcObject = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
    } catch (e) {
        alert("Camera permission needed. Allow it.");
    }
}

function capturePhoto() {
    canvas = document.getElementById('canvas');
    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    document.getElementById('preview').src = canvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('previewSection').classList.remove('hidden');
}

function handleUpload(e) {
    let file = e.target.files[0];
    if (!file) return;
    let reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('preview').src = ev.target.result;
        document.getElementById('previewSection').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

async function enhanceImage(img) {
    let c = document.createElement('canvas');
    c.width = img.naturalWidth; c.height = img.naturalHeight;
    let ctx = c.getContext('2d');
    ctx.drawImage(img, 0, 0);
    ctx.filter = 'contrast(1.4) brightness(1.15)';
    ctx.drawImage(c, 0, 0);
    return c.toDataURL('image/jpeg', 0.9);
}

async function runOCR() {
    if (isProcessing || !worker) { alert("OCR loading... wait."); return; }
    isProcessing = true;
    let btn = document.querySelector('button[onclick="runOCR()"]');
    btn.disabled = true; btn.textContent = "Processing...";

    try {
        let img = document.getElementById('preview');
        let enhanced = await enhanceImage(img);
        let { data: { text, confidence } } = await worker.recognize(enhanced);

        let plate = text.replace(/[^A-Z0-9]/gi, '').toUpperCase().trim();

        if (plate.length < 5) {
            let orig = await worker.recognize(img.src);
            plate = orig.data.text.replace(/[^A-Z0-9]/gi, '').toUpperCase().trim();
            confidence = orig.data.confidence;
        }

        document.getElementById('plateInput').value = plate || "Not detected - type manually";

        if (confidence < 65 && plate) alert(`Low confidence (${Math.round(confidence)}%). Closer photo helps.`);
    } catch (e) {
        alert("OCR error: " + e.message);
    } finally {
        isProcessing = false;
        btn.disabled = false; btn.textContent = "🔍 Extract Number Plate (OCR)";
    }
}

async function saveEntry() {
    let plate = document.getElementById('plateInput').value.trim().toUpperCase();
    if (!plate || plate.length < 5) { alert("Enter valid plate!"); return; }

    let form = new FormData();
    form.append('plate', plate);
    form.append('photo', document.getElementById('preview').src);

    try {
        let res = await fetch('process_entry.php', {method: 'POST', body: form});
        let msg = await res.text();
        alert(msg);
        if (msg.includes('success') || msg.includes('recorded')) location.href = 'index.php';
    } catch (e) {
        alert("Save failed.");
    }
}
</script>
</body>
</html>