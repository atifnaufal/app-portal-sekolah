@extends('layouts.mobile-page')
@section('content')
<header class="mobile-hero">
    <div class="eyebrow">KEHADIRAN HARI INI</div>
    <div class="hero-title mt-2">Absensi & Vermuk</div>
    <div class="class-pill mt-3">{{ $user->kelas?->nama ?? 'Staf sekolah' }}</div>
</header>
<main class="mobile-content">
    @if(!$attendanceActive)
        <div class="alert alert-warning border-0 rounded-4 p-4 text-center">
            <div class="h1 mb-3">&#9888;</div>
            <h5 class="fw-bold">Absensi Dinonaktifkan</h5>
            <p class="small mb-0">Admin sekolah saat ini menonaktifkan fitur absensi.</p>
        </div>
    @else
        <div class="card mobile-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box" style="background:#e5f7ef;color:#198754">&#10003;</div>
                    <div>
                        <div class="small text-secondary">{{ now()->format('l, d F Y') }}</div>
                        <h1 class="h5 fw-bold mb-0 mt-1">Status Kehadiran</h1>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="p-3 border rounded-4 {{ ($myAttendance && $myAttendance->waktu_masuk) ? 'bg-success-subtle border-success' : '' }}">
                            <div class="small text-secondary">Masuk</div>
                            <div class="fw-bold h5 mb-0">{{ ($myAttendance && $myAttendance->waktu_masuk) ? substr($myAttendance->waktu_masuk, 0, 5) : '--:--' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-4 {{ ($myAttendance && $myAttendance->waktu_pulang) ? 'bg-primary-subtle border-primary' : '' }}">
                            <div class="small text-secondary">Pulang</div>
                            <div class="fw-bold h5 mb-0">{{ ($myAttendance && $myAttendance->waktu_pulang) ? substr($myAttendance->waktu_pulang, 0, 5) : '--:--' }}</div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 mt-2 mb-3 small">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 mt-2 mb-3 small">{{ session('error') }}</div>
                @endif

                @php
                    $canMasuk = !$myAttendance || !$myAttendance->waktu_masuk;
                    $canPulang = $myAttendance && $myAttendance->waktu_masuk && !$myAttendance->waktu_pulang;
                @endphp

                @if($canMasuk || $canPulang)
                    <div id="camera-container" class="position-relative overflow-hidden rounded-4 shadow-lg mb-3 d-none" style="aspect-ratio: 3/4; background: #000;">
                        <video id="video" autoplay muted playsinline class="w-100 h-100" style="object-fit: cover;"></video>

                        <!-- Scanning Animation Overlays -->
                        <div class="face-scanner"></div>
                        <div class="scanner-line"></div>
                        <div class="corner-border tl"></div>
                        <div class="corner-border tr"></div>
                        <div class="corner-border bl"></div>
                        <div class="corner-border br"></div>

                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center text-white bg-dark bg-opacity-50">
                            <div id="camera-status" class="small fw-bold">Memuat Kamera...</div>
                        </div>
                    </div>

                    <button id="open-camera-btn" type="button" class="btn btn-primary w-100 py-3 rounded-4 shadow-sm fw-bold">
                        Buka Kamera Vermuk
                    </button>

                    <form method="POST" action="{{ route('absensi.store') }}" enctype="multipart/form-data" id="absensiForm" class="d-none">
                        @csrf
                        <input type="hidden" name="tipe" value="{{ $canMasuk ? 'masuk' : 'pulang' }}">
                        <input type="hidden" name="lat" id="lat">
                        <input type="hidden" name="long" id="long">
                        <input type="file" name="foto" id="foto-input" class="d-none">

                        <div class="alert alert-info border-0 rounded-4 x-small mt-3">
                            <i class="opacity-75">Pastikan wajah berada di tengah area deteksi.</i>
                        </div>

                        <button id="capture-btn" type="button" class="btn btn-success w-100 py-3 rounded-4 shadow-sm fw-bold mt-2" disabled>
                            Konfirmasi & Absen
                        </button>
                    </form>
                @else
                    <div class="alert alert-info border-0 mt-4 mb-0 small text-center rounded-4">
                        Selamat! Anda sudah menyelesaikan absensi hari ini.
                    </div>
                @endif
            </div>
        </div>
    @endif
</main>

<style>
    /* Professional Scanner UI */
    .face-scanner {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 280px; height: 280px;
        border-radius: 50%;
        border: 2px dashed rgba(255,255,255,0.5);
        box-shadow: 0 0 0 1000px rgba(0,0,0,0.4);
        pointer-events: none;
    }
    .scanner-line {
        position: absolute; top: 0; left: 0; width: 100%; height: 2px;
        background: linear-gradient(to right, transparent, #246bfe, transparent);
        box-shadow: 0 0 15px #246bfe;
        animation: scan 3s infinite ease-in-out;
        z-index: 10;
    }
    .corner-border {
        position: absolute; width: 40px; height: 40px;
        border: 4px solid #246bfe; z-index: 20;
    }
    .tl { top: 20px; left: 20px; border-right: 0; border-bottom: 0; border-top-left-radius: 12px; }
    .tr { top: 20px; right: 20px; border-left: 0; border-bottom: 0; border-top-right-radius: 12px; }
    .bl { bottom: 20px; left: 20px; border-right: 0; border-top: 0; border-bottom-left-radius: 12px; }
    .br { bottom: 20px; right: 20px; border-left: 0; border-top: 0; border-bottom-right-radius: 12px; }

    @keyframes scan { 0% { top: 0% } 50% { top: 100% } 100% { top: 0% } }
    .ls-tight { letter-spacing: -0.5px; }
    .x-small { font-size: 11px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.10.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-detection@1.0.2"></script>

<script>
    let detector;
    let stream;
    const video = document.getElementById('video');
    const captureBtn = document.getElementById('capture-btn');
    const openCameraBtn = document.getElementById('open-camera-btn');
    const cameraContainer = document.getElementById('camera-container');
    const absensiForm = document.getElementById('absensiForm');
    const statusText = document.getElementById('camera-status');

    // Load Geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('lat').value = position.coords.latitude;
            document.getElementById('long').value = position.coords.longitude;
        }, function(err) {
            console.warn("Geo error:", err);
        }, { enableHighAccuracy: true });
    }

    async function setupCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            video.srcObject = stream;
            return new Promise((resolve) => {
                video.onloadedmetadata = () => resolve(video);
            });
        } catch (err) {
            alert("Izin kamera ditolak atau tidak tersedia.");
            throw err;
        }
    }

    async function detectFace() {
        if (!detector || !video.srcObject || video.paused) return;

        try {
            const faces = await detector.estimateFaces(video, { flipHorizontal: false });

            if (faces.length > 0) {
                const face = faces[0];
                // Pastikan wajah cukup dekat dan terpusat (opsional logic)
                statusText.innerText = "WAJAH TERDETEKSI";
                statusText.className = "small fw-bold text-success";
                captureBtn.disabled = false;
            } else {
                statusText.innerText = "POSISIKAN WAJAH DI TENGAH";
                statusText.className = "small fw-bold text-warning";
                captureBtn.disabled = true;
            }
        } catch (e) {
            console.error("Detection loop error:", e);
        }

        requestAnimationFrame(detectFace);
    }

    openCameraBtn.addEventListener('click', async () => {
        openCameraBtn.innerText = "Menyiapkan Sensor...";
        openCameraBtn.disabled = true;

        try {
            await setupCamera();

            // Gunakan MediaPipe Face Detector (lebih canggih/akurat)
            const model = faceDetection.SupportedModels.MediaPipeFaceDetector;
            detector = await faceDetection.createDetector(model, {
                runtime: 'tfjs',
                maxFaces: 1
            });

            cameraContainer.classList.remove('d-none');
            absensiForm.classList.remove('d-none');
            openCameraBtn.classList.add('d-none');

            detectFace();
        } catch (err) {
            console.error("AI Init Error:", err);
            alert("Gagal memuat sistem Vermuk. Coba gunakan browser Chrome terbaru.");
            openCameraBtn.innerText = "Buka Kamera Vermuk";
            openCameraBtn.disabled = false;
        }
    });

    captureBtn.addEventListener('click', () => {
        // Visual feedback
        cameraContainer.style.filter = 'brightness(2)';
        captureBtn.innerText = "MENGIRIM...";
        captureBtn.disabled = true;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        canvas.toBlob((blob) => {
            const file = new File([blob], "absensi.jpg", { type: "image/jpeg" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('foto-input').files = dataTransfer.files;

            // Success Vibration (if supported)
            if (navigator.vibrate) navigator.vibrate(50);

            // Submit form
            document.getElementById('page-loader').style.display = 'flex';
            absensiForm.submit();
        }, 'image/jpeg', 0.9);
    });

    // Clean up camera on page hide
    window.addEventListener('pagehide', () => {
        if (stream) stream.getTracks().forEach(track => track.stop());
    });
</script>
@endsection
