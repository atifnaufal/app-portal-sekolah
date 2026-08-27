@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="javascript:history.back()" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
        Kembali
    </a>
</div>
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

            <div class="mb-3">
                <label class="form-label fw-bold">Registrasi Guru</label>
                <div class="form-check form-switch p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Izinkan Pendaftaran Guru</span>
                    <input type="hidden" name="registration_guru_enabled" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="registration_guru_enabled" value="1" {{ $registrationGuruEnabled ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Registrasi Siswa</label>
                <div class="form-check form-switch p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                    <span class="small text-secondary">Izinkan Pendaftaran Siswa</span>
                    <input type="hidden" name="registration_siswa_enabled" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="registration_siswa_enabled" value="1" {{ $registrationSiswaEnabled ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-sm">Simpan Perubahan</button>
        </form>
    </div>
</main>
@endsection
