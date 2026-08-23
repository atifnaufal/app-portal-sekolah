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

                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 border rounded-4 {{ $myAttendance && $myAttendance->waktu_masuk ? 'bg-success-subtle border-success' : '' }}">
                            <div class="small text-secondary">Masuk</div>
                            <div class="fw-bold h5 mb-0">{{ $myAttendance->waktu_masuk ? substr($myAttendance->waktu_masuk, 0, 5) : '--:--' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-4 {{ $myAttendance && $myAttendance->waktu_pulang ? 'bg-primary-subtle border-primary' : '' }}">
                            <div class="small text-secondary">Pulang</div>
                            <div class="fw-bold h5 mb-0">{{ $myAttendance->waktu_pulang ? substr($myAttendance->waktu_pulang, 0, 5) : '--:--' }}</div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 mt-4 mb-0 small">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 mt-4 mb-0 small">{{ session('error') }}</div>
                @endif

                @php
                    $canMasuk = !$myAttendance || !$myAttendance->waktu_masuk;
                    $canPulang = $myAttendance && $myAttendance->waktu_masuk && !$myAttendance->waktu_pulang;
                @endphp

                @if($canMasuk || $canPulang)
                    <div class="mt-4 border-top pt-4">
                        <form method="POST" action="{{ route('absensi.store') }}" enctype="multipart/form-data" id="absensiForm">
                            @csrf
                            <input type="hidden" name="tipe" value="{{ $canMasuk ? 'masuk' : 'pulang' }}">
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="long" id="long">

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Verifikasi Muka (Vermuk)</label>
                                <input type="file" name="foto" accept="image/*" capture="user" class="form-control form-control-lg rounded-4" required>
                                <div class="form-text small">Ambil foto selfie untuk verifikasi.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 shadow-sm fw-bold">
                                Absen {{ $canMasuk ? 'Masuk' : 'Pulang' }} Sekarang
                            </button>
                        </form>
                    </div>
                @else
                    <div class="alert alert-info border-0 mt-4 mb-0 small text-center rounded-4">
                        Selamat! Anda sudah menyelesaikan absensi hari ini.
                    </div>
                @endif
            </div>
        </div>
    @endif
</main>

<script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('lat').value = position.coords.latitude;
            document.getElementById('long').value = position.coords.longitude;
        });
    }

    document.getElementById('absensiForm')?.addEventListener('submit', function() {
        // Tampilkan loader transisi yang sudah kita buat sebelumnya
        document.getElementById('page-loader').style.display = 'flex';
    });
</script>
@endsection
