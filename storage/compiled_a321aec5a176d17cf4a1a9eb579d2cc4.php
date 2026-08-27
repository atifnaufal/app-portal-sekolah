<?php $__env->startSection('content'); ?>
<header class="mobile-hero">
    <div class="eyebrow">KEHADIRAN HARI INI</div>
    <div class="hero-title mt-2">Absensi & Vermuk</div>
    <div class="class-pill mt-3"><?php echo e($user->kelas?->nama ?? 'Staf sekolah'); ?></div>
</header>
<main class="mobile-content">
    <?php if(!$attendanceActive): ?>
        <div class="alert alert-warning border-0 rounded-4 p-4 text-center">
            <div class="h1 mb-3">&#9888;</div>
            <h5 class="fw-bold">Absensi Dinonaktifkan</h5>
            <p class="small mb-0">Admin sekolah saat ini menonaktifkan fitur absensi.</p>
        </div>
    <?php else: ?>
        <div class="card mobile-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box" style="background:#e5f7ef;color:#198754">&#10003;</div>
                    <div>
                        <div class="small text-secondary"><?php echo e(now()->format('l, d F Y')); ?></div>
                        <h1 class="h5 fw-bold mb-0 mt-1">Status Kehadiran</h1>
                    </div>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="p-3 border rounded-4 <?php echo e(($myAttendance && $myAttendance->waktu_masuk) ? 'bg-success-subtle border-success' : ''); ?>">
                            <div class="small text-secondary">Masuk</div>
                            <div class="fw-bold h5 mb-0"><?php echo e(($myAttendance && $myAttendance->waktu_masuk) ? substr($myAttendance->waktu_masuk, 0, 5) : '--:--'); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-4 <?php echo e(($myAttendance && $myAttendance->waktu_pulang) ? 'bg-primary-subtle border-primary' : ''); ?>">
                            <div class="small text-secondary">Pulang</div>
                            <div class="fw-bold h5 mb-0"><?php echo e(($myAttendance && $myAttendance->waktu_pulang) ? substr($myAttendance->waktu_pulang, 0, 5) : '--:--'); ?></div>
                        </div>
                    </div>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success border-0 mt-2 mb-3 small"><?php echo e(session('success')); ?></div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="alert alert-danger border-0 mt-2 mb-3 small"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <?php
                    $canMasuk = !$myAttendance || !$myAttendance->waktu_masuk;
                    $canPulang = $myAttendance && $myAttendance->waktu_masuk && !$myAttendance->waktu_pulang;
                ?>

                <?php if($canMasuk || $canPulang): ?>
                    <div id="camera-container" class="position-relative overflow-hidden rounded-4 shadow-lg mb-3 d-none" style="aspect-ratio: 3/4; background: #000;">
                        <video id="video" autoplay muted playsinline class="w-100 h-100" style="object-fit: cover; transform: scaleX(-1);"></video>

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

                    <form method="POST" action="<?php echo e(route('absensi.store')); ?>" enctype="multipart/form-data" id="absensiForm" class="d-none">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="tipe" value="<?php echo e($canMasuk ? 'masuk' : 'pulang'); ?>">
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
                <?php else: ?>
                    <div class="alert alert-info border-0 mt-4 mb-0 small text-center rounded-4">
                        Selamat! Anda sudah menyelesaikan absensi hari ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
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

    // Load Geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('lat').value = position.coords.latitude;
            document.getElementById('long').value = position.coords.longitude;
        }, function(err) {
            console.warn("Geo error:", err);
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    // Pesan error kamera yang spesifik dan mudah dipahami.
    function cameraErrorMessage(err) {
        switch (err && err.name) {
            case 'NotAllowedError':
            case 'SecurityError':
                return 'Izin kamera ditolak. Buka pengaturan perangkat, aktifkan izin kamera untuk aplikasi ini, lalu coba lagi.';
            case 'NotFoundError':
            case 'DevicesNotFoundError':
                return 'Kamera tidak ditemukan pada perangkat ini.';
            case 'NotReadableError':
            case 'TrackStartError':
                return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.';
            case 'OverconstrainedError':
                return 'Kamera depan tidak mendukung resolusi yang diminta. Silakan coba lagi.';
            default:
                return 'Kamera gagal dijalankan (' + ((err && err.name) || 'kesalahan tidak diketahui') + '). Silakan coba lagi.';
        }
    }

    // Setup kamera: resolusi HD dengan fallback bertahap agar selalu berhasil menyala.
    async function setupCamera() {
        const attempts = [
            { video: { facingMode: { exact: 'user' }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false },
            { video: { facingMode: 'user' }, audio: false },
            { video: true, audio: false }
        ];

        let lastError = null;
        for (const constraints of attempts) {
            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                break;
            } catch (err) {
                lastError = err;
                // Izin ditolak tidak perlu dicoba ulang dengan konfigurasi lain.
                if (err && ['NotAllowedError', 'SecurityError'].includes(err.name)) break;
            }
        }

        if (!stream) throw lastError;

        video.srcObject = stream;

        await new Promise((resolve, reject) => {
            video.onloadedmetadata = () => resolve();
            video.onerror = () => reject(new Error('Video gagal dimuat.'));
            // Pengaman bila metadata sudah termuat lebih dulu.
            if (video.readyState >= 1) resolve();
        });

        try { await video.play(); } catch (_) { /* autoplay muted: aman diabaikan */ }

        // Autofokus kontinu bila perangkat mendukung (fitur kamera tingkat lanjut).
        try {
            const [track] = stream.getVideoTracks();
            const capabilities = typeof track.getCapabilities === 'function' ? track.getCapabilities() : {};
            if (Array.isArray(capabilities.focusMode) && capabilities.focusMode.includes('continuous')) {
                await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
            }
        } catch (_) { /* tidak semua perangkat mendukung */ }

        return video;
    }

    // Loop deteksi wajah — dijalankan maksimal 4x/detik agar stabil & hemat baterai.
    async function detectFace(timestamp) {
        if (!detecting) return;
        requestAnimationFrame(detectFace);

        if (!detector || !stream || video.readyState < 2) return;
        if (timestamp - lastDetection < 250) return;
        lastDetection = timestamp;

        try {
            const faces = await detector.estimateFaces(video);
            if (!detecting) return;

            if (faces.length > 0) {
                statusText.innerText = 'WAJAH TERDETEKSI';
                statusText.className = 'small fw-bold text-success';
                if (!submitting) captureBtn.disabled = false;
            } else {
                statusText.innerText = 'POSISIKAN WAJAH DI TENGAH';
                statusText.className = 'small fw-bold text-warning';
                if (!submitting) captureBtn.disabled = true;
            }
        } catch (e) {
            // Kesalahan transien diabaikan; frame berikutnya akan dicoba ulang.
        }
    }

    function startDetection() {
        detecting = true;
        lastDetection = 0;
        requestAnimationFrame(detectFace);
    }

    function stopStream() {
        detecting = false;
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    openCameraBtn.addEventListener('click', async () => {
        openCameraBtn.innerText = 'Menyiapkan Sensor...';
        openCameraBtn.disabled = true;

        try {
            await setupCamera();
        } catch (err) {
            console.error('Camera Error:', err);
            alert(cameraErrorMessage(err));
            openCameraBtn.innerText = 'Buka Kamera Vermuk';
            openCameraBtn.disabled = false;
            stopStream();
            return;
        }

        try {
            // MediaPipe Face Detector via TensorFlow.js (deteksi wajah real-time tercanggih di browser).
            const model = faceDetection.SupportedModels.MediaPipeFaceDetector;
            detector = await faceDetection.createDetector(model, {
                runtime: 'tfjs',
                maxFaces: 1
            });
        } catch (err) {
            console.error('AI Init Error:', err);
            alert('Gagal memuat sistem deteksi wajah. Pastikan koneksi internet stabil, lalu coba lagi.');
            openCameraBtn.innerText = 'Buka Kamera Vermuk';
            openCameraBtn.disabled = false;
            stopStream();
            return;
        }

        cameraContainer.classList.remove('d-none');
        absensiForm.classList.remove('d-none');
        openCameraBtn.classList.add('d-none');
        statusText.innerText = 'MEMULAI DETEKSI...';
        startDetection();
    });

    captureBtn.addEventListener('click', () => {
        // Jangan ambil foto sebelum frame video benar-benar siap.
        if (!video.videoWidth || !video.videoHeight) return;

        submitting = true;
        detecting = false;
        captureBtn.innerText = 'MENGIRIM...';
        captureBtn.disabled = true;
        cameraContainer.style.filter = 'brightness(2)';

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');

        // Pratinjau ditampilkan cermin (mode selfie alami). Foto ikut dicerminkan
        // sehingga hasil akhir SELALU identik dengan yang dilihat pengguna (anti terbalik-balik).
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);

        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Gagal memproses foto. Silakan coba lagi.');
                submitting = false;
                captureBtn.innerText = 'Konfirmasi & Absen';
                cameraContainer.style.filter = '';
                startDetection();
                return;
            }

            const file = new File([blob], 'absensi.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('foto-input').files = dataTransfer.files;

            // Success Vibration (if supported)
            if (navigator.vibrate) navigator.vibrate(50);

            // Submit form
            document.getElementById('page-loader').style.display = 'flex';
            absensiForm.submit();
        }, 'image/jpeg', 0.92);
    });

    // Hentikan analisis saat aplikasi berpindah ke latar belakang.
    document.addEventListener('visibilitychange', () => {
        if (!stream) return;
        if (document.hidden) {
            detecting = false;
        } else if (detector && !submitting) {
            startDetection();
        }
    });

    // Clean up camera on page hide
    window.addEventListener('pagehide', () => {
        stopStream();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.mobile-app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>