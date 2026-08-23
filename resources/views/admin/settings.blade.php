@extends('layouts.app', ['title' => 'Pengaturan Portal'])
@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="form-card card p-4 mb-4">
            <h5 class="fw-bold mb-4">Pengaturan Absensi & Portal</h5>
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf

                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">Status Absensi Vermuk</h6>
                            <p class="small text-secondary mb-0">Aktifkan agar siswa dan guru bisa melakukan absensi di mobile.</p>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="attendance_active" value="0">
                            <input class="form-check-input" type="checkbox" name="attendance_active" value="1" {{ $attendanceActive ? 'checked' : '' }} style="transform: scale(1.5);">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Jam Mulai Absen</label>
                        <input type="time" name="attendance_start_time" value="{{ $startTime }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Batas Telat</label>
                        <input type="time" name="attendance_late_time" value="{{ $lateTime }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Jam Selesai (Pulang)</label>
                        <input type="time" name="attendance_end_time" value="{{ $endTime }}" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Registrasi Mandiri</h6>
                    <div class="form-check">
                        <input type="hidden" name="registration_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="registration_enabled" value="1" {{ $registrationEnabled ? 'checked' : '' }} id="regCheck">
                        <label class="form-check-label small" for="regCheck">
                            Izinkan pendaftaran akun baru dari halaman login aplikasi.
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card p-4">
            <h6 class="fw-bold">Info Sistem</h6>
            <div class="mt-3">
                <div class="small text-secondary">Zona Waktu Server</div>
                <div class="fw-bold">{{ config('app.timezone') }}</div>
            </div>
            <hr>
            <div class="mt-2">
                <p class="small text-secondary mb-0">Pastikan jam server sinkron dengan jam lokal sekolah agar absensi tidak ada selisih waktu.</p>
            </div>
        </div>
    </div>
</div>
@endsection
