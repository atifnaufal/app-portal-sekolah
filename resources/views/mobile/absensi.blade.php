@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $hadir = (int) ($absensiBulan['hadir'] ?? $absensiBulan['Hadir'] ?? 0);
    $izin = (int) ($absensiBulan['izin'] ?? $absensiBulan['Izin'] ?? 0);
    $sakit = (int) ($absensiBulan['sakit'] ?? $absensiBulan['Sakit'] ?? 0);
    $alpha = (int) ($absensiBulan['alpha'] ?? $absensiBulan['Alpha'] ?? 0);
    $totalAbsen = $hadir + $izin + $sakit + $alpha;
@endphp

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line-strong);
        padding: 12px 20px; padding-top: calc(12px + env(safe-area-inset-top));
        display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 80px; padding-bottom: 48px; }

    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .stat-chip {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.16);
        border-radius: var(--radius-sm); padding: 10px 8px; text-align: center;
    }
    .stat-chip .num { font-size: 18px; font-weight: 800; color: #fff; line-height: 1.1; }
    .stat-chip .lbl { font-size: 9px; font-weight: 700; letter-spacing: .04em; color: rgba(255,255,255,.72); margin-top: 4px; text-transform: uppercase; }

    .absen-card {
        background: var(--surface-card); border: none; border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        position: relative; overflow: hidden; margin-bottom: 16px;
    }
    .absen-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue);
    }
    .absen-card.success::before { background: #10b981; }
    .absen-card.warning::before { background: #f59e0b; }

    .icon-box {
        width: 48px; height: 48px; border-radius: var(--radius-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }

    /* Face Scanner UI */
    #camera-container {
        border-radius: var(--radius-md); position: relative; overflow: hidden;
        aspect-ratio: 3/4; background: #000; box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }
    .face-scanner {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 70%; height: 60%;
        border-radius: 50%;
        border: 2px dashed rgba(255,255,255,0.5);
        box-shadow: 0 0 0 1000px rgba(0,0,0,0.5);
        pointer-events: none;
    }
    .scanner-line {
        position: absolute; top: 0; left: 0; width: 100%; height: 2px;
        background: var(--blue); box-shadow: 0 0 15px var(--blue);
        animation: scan 3s infinite ease-in-out; z-index: 10;
    }
    @keyframes scan { 0% { top: 0% } 50% { top: 100% } 100% { top: 0% } }

    .btn-premium {
        padding: 16px; border-radius: var(--radius-sm); font-weight: 800; font-size: 15px;
        box-shadow: 0 8px 20px rgba(36, 107, 254, 0.2);
    }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px; color: var(--ink);">Kehadiran</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 40px 40px; margin-bottom: 24px; background: linear-gradient(135deg, var(--navy), var(--blue)); padding: 32px 24px 40px;">
        <div class="eyebrow" style="color: #94a3b8;">{{ $user->kelas?->nama ?? 'Akademik' }}</div>
        <div class="hero-title mt-2 text-white" style="font-size: 26px;">Absensi & Verifikasi Muka</div>
        <p class="mb-3 mt-1" style="font-size: 12px; color: rgba(255,255,255,.7);">
            Rekap kehadiran bulan ini: <strong>{{ $totalAbsen }} hari</strong>
        </p>
        <div class="stat-grid">
            <div class="stat-chip"><div class="num">{{ $hadir }}</div><div class="lbl">Hadir</div></div>
            <div class="stat-chip"><div class="num">{{ $sakit }}</div><div class="lbl">Sakit</div></div>
            <div class="stat-chip"><div class="num">{{ $izin }}</div><div class="lbl">Izin</div></div>
            <div class="stat-chip"><div class="num">{{ $alpha }}</div><div class="lbl">Alpha</div></div>
        </div>
    </header>

    <main class="mobile-content px-3">
        @if(!$attendanceActive)
            <div class="pui-empty">
                <i class="bi bi-exclamation-octagon ico"></i>
                <h4>Absensi Nonaktif</h4>
                <p>Fitur absensi digital sedang dinonaktifkan oleh Admin.</p>
            </div>
        @else
            <div class="card absen-card success">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-box" style="background:#f0fdf4;color:#16a34a"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <div class="small text-secondary">{{ now()->translatedFormat('l, d F Y') }}</div>
                            <h2 class="h6 fw-bold mb-0 mt-1">Verifikasi Kehadiran</h2>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 border rounded-4 {{ ($myAttendance && $myAttendance->waktu_masuk) ? 'bg-success-subtle border-success' : 'bg-light border-0' }}">
                                <div class="small text-secondary" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Masuk</div>
                                <div class="fw-bold h5 mb-0">{{ ($myAttendance && $myAttendance->waktu_masuk) ? substr($myAttendance->waktu_masuk, 0, 5) : '--:--' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded-4 {{ ($myAttendance && $myAttendance->waktu_pulang) ? 'bg-primary-subtle border-primary' : 'bg-light border-0' }}">
                                <div class="small text-secondary" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Pulang</div>
                                <div class="fw-bold h5 mb-0">{{ ($myAttendance && $myAttendance->waktu_pulang) ? substr($myAttendance->waktu_pulang, 0, 5) : '--:--' }}</div>
                            </div>
                        </div>
                    </div>

                    @php
                        $canMasuk = !$myAttendance || !$myAttendance->waktu_masuk;
                        $canPulang = $myAttendance && $myAttendance->waktu_masuk && !$myAttendance->waktu_pulang;
                    @endphp

                    @if($canMasuk || $canPulang)
                        <div id="camera-container" class="mb-4 d-none">
                            <video id="video" autoplay muted playsinline class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>
                            <div class="face-scanner"></div>
                            <div class="scanner-line"></div>
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 text-center text-white bg-dark bg-opacity-50">
                                <div id="camera-status" class="small fw-bold">Menyiapkan AI...</div>
                            </div>
                        </div>

                        <button id="open-camera-btn" type="button" class="btn btn-primary w-100 btn-premium">
                            <i class="bi bi-camera-fill me-2"></i> Buka Kamera verifikasi muka
                        </button>

                        <form method="POST" action="{{ route('absensi.store') }}" enctype="multipart/form-data" id="absensiForm" class="d-none mt-3">
                            @csrf
                            <input type="hidden" name="tipe" value="{{ $canMasuk ? 'masuk' : 'pulang' }}">
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="long" id="long">
                            <input type="file" name="foto" id="foto-input" class="d-none">

                            <button id="capture-btn" type="button" class="btn btn-success w-100 btn-premium" disabled>
                                Konfirmasi & Absen
                            </button>
                        </form>
                    @else
                        <div class="text-center py-3">
                            <div class="rounded-pill bg-success-subtle text-success px-4 py-2 d-inline-block fw-bold" style="font-size: 13px;">
                                <i class="bi bi-check-circle-fill me-2"></i> Absensi Selesai
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.10.0"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-detection@1.0.2"></script>

<script>
    let detector;
    let stream = null;
    let detecting = false;
    let submitting = false;
    let lastDetection = 0;

    const video = document.getElementById('video');
    const captureBtn = document.getElementById('capture-btn');
    const openCameraBtn = document.getElementById('open-camera-btn');
    const cameraContainer = document.getElementById('camera-container');
    const absensiForm = document.getElementById('absensiForm');
    const statusText = document.getElementById('camera-status');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('lat').value = pos.coords.latitude;
            document.getElementById('long').value = pos.coords.longitude;
        }, null, { enableHighAccuracy: true });
    }

    async function setupCamera() {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 960 } },
            audio: false
        });
        video.srcObject = stream;
        return new Promise(resolve => video.onloadedmetadata = () => resolve(video));
    }

    async function detectFace(timestamp) {
        if (!detecting) return;
        requestAnimationFrame(detectFace);
        if (!detector || timestamp - lastDetection < 300) return;
        lastDetection = timestamp;

        const faces = await detector.estimateFaces(video);
        if (faces.length > 0) {
            statusText.innerText = 'WAJAH TERDETEKSI';
            statusText.className = 'small fw-bold text-success';
            captureBtn.disabled = false;
        } else {
            statusText.innerText = 'POSISIKAN WAJAH DI TENGAH';
            statusText.className = 'small fw-bold text-warning';
            captureBtn.disabled = true;
        }
    }

    openCameraBtn.addEventListener('click', async () => {
        openCameraBtn.disabled = true;
        openCameraBtn.innerText = 'Memulai AI...';
        try {
            await setupCamera();
            detector = await faceDetection.createDetector(faceDetection.SupportedModels.MediaPipeFaceDetector, { runtime: 'tfjs' });
            cameraContainer.classList.remove('d-none');
            absensiForm.classList.remove('d-none');
            openCameraBtn.classList.add('d-none');
            detecting = true;
            requestAnimationFrame(detectFace);
        } catch (e) {
            alert("Gagal membuka kamera: " + e.message);
            openCameraBtn.disabled = false;
        }
    });

    captureBtn.addEventListener('click', () => {
        submitting = true; detecting = false;
        captureBtn.innerText = 'MENGIRIM...';
        captureBtn.disabled = true;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0); ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);

        canvas.toBlob(blob => {
            const file = new File([blob], 'absensi.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer(); dt.items.add(file);
            document.getElementById('foto-input').files = dt.files;
            document.getElementById('page-loader').style.display = 'flex';
            absensiForm.submit();
        }, 'image/jpeg', 0.9);
    });
</script>
@endsection
