@extends('layouts.mobile-page')
@section('content')
<header class="mobile-hero">
    <div class="eyebrow">PENGATURAN</div>
    <div class="hero-title mt-2">Kontrol Portal</div>
    <div class="class-pill mt-3">Sistem Sekolah</div>
</header>
<main class="mobile-content">
    <div class="card mobile-card p-4">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-bold">Absensi Vermuk</label>
                <div class="form-check form-switch p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Status Aktif</span>
                    <input type="hidden" name="attendance_active" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="attendance_active" value="1" {{ $attendanceActive ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label small fw-bold">Jam Mulai</label>
                    <input type="time" name="attendance_start_time" value="{{ $startTime }}" class="form-control rounded-3">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Jam Selesai</label>
                    <input type="time" name="attendance_end_time" value="{{ $endTime }}" class="form-control rounded-3">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Registrasi Mandiri</label>
                <div class="form-check form-switch p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Izinkan Daftar</span>
                    <input type="hidden" name="registration_enabled" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="registration_enabled" value="1" {{ $registrationEnabled ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-sm">Simpan Perubahan</button>
        </form>
    </div>
</main>
@endsection
