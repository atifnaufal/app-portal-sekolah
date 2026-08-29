@extends('layouts.mobile-app')
@section('content')
<div class="pui-topbar" style="padding-top:20px;">
    <a href="javascript:history.back()" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1>Kontrol Portal</h1>
    <div class="spacer"></div>
</div>
<main class="mobile-content px-3">
    <div class="pui-card p-4" style="padding:20px;">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            <div class="pui-field">
                <label class="pui-label">Absensi Vermuk</label>
                <div class="pui-card d-flex justify-content-between align-items-center" style="padding:14px 16px; background:#f8fafc;">
                    <span class="small" style="color:var(--mist);">Status Aktif</span>
                    <input type="hidden" name="attendance_active" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="attendance_active" value="1" {{ $attendanceActive ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <div class="row g-3 pui-field">
                <div class="col-6">
                    <label class="pui-label" style="font-size:11.5px;">Jam Mulai</label>
                    <input type="time" name="attendance_start_time" value="{{ $startTime }}" class="pui-input">
                </div>
                <div class="col-6">
                    <label class="pui-label" style="font-size:11.5px;">Jam Selesai</label>
                    <input type="time" name="attendance_end_time" value="{{ $endTime }}" class="pui-input">
                </div>
            </div>

            <div class="pui-field">
                <label class="pui-label">Registrasi Guru</label>
                <div class="pui-card d-flex justify-content-between align-items-center" style="padding:14px 16px; background:#f8fafc;">
                    <span class="small" style="color:var(--mist);">Izinkan Pendaftaran Guru</span>
                    <input type="hidden" name="registration_guru_enabled" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="registration_guru_enabled" value="1" {{ $registrationGuruEnabled ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <div class="pui-field" style="margin-bottom:24px;">
                <label class="pui-label">Registrasi Siswa</label>
                <div class="pui-card d-flex justify-content-between align-items-center" style="padding:14px 16px; background:#f8fafc;">
                    <span class="small" style="color:var(--mist);">Izinkan Pendaftaran Siswa</span>
                    <input type="hidden" name="registration_siswa_enabled" value="0">
                    <input class="form-check-input ms-0" type="checkbox" name="registration_siswa_enabled" value="1" {{ $registrationSiswaEnabled ? 'checked' : '' }} style="transform: scale(1.3)">
                </div>
            </div>

            <button type="submit" class="pui-btn pui-btn-primary pui-btn-block">Simpan Perubahan</button>
        </form>
    </div>
</main>
@endsection
