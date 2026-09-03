@extends('layouts.app', ['title' => 'Pengaturan Portal'])
@section('content')
<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

@if(empty($isSuperAdmin) && !empty($school))
{{-- Admin sekolah: hanya pendaftaran sekolahnya sendiri --}}
<div class="row">
    <div class="col-md-8">
        <div class="form-card card p-4 mb-4">
            <h5 class="fw-bold mb-1">Pendaftaran {{ $school->name }}</h5>
            <p class="small text-secondary mb-4">Buka/tutup pendaftaran akun baru untuk sekolah Anda. Pendaftar baru masuk sebagai <b>menunggu persetujuan</b> dan harus disetujui admin.</p>
            @foreach([['role' => 'guru', 'label' => 'Guru', 'open' => $schoolRegGuru], ['role' => 'siswa', 'label' => 'Siswa', 'open' => $schoolRegSiswa]] as $reg)
            <form method="POST" action="{{ route('admin.registration.toggle') }}" class="mb-3 p-3 rounded-3 border d-flex justify-content-between align-items-center" style="background:#f8fafc;">
                @csrf @method('PATCH') <input type="hidden" name="role" value="{{ $reg['role'] }}">
                <div>
                    <div class="fw-bold">Pendaftaran {{ $reg['label'] }}</div>
                    <span class="badge rounded-pill {{ $reg['open'] ? 'bg-success' : 'bg-secondary' }}">{{ $reg['open'] ? 'DIBUKA' : 'DITUTUP' }}</span>
                </div>
                <button class="btn {{ $reg['open'] ? 'btn-outline-danger' : 'btn-success' }}" style="border-radius:12px;">{{ $reg['open'] ? 'Tutup' : 'Buka' }}</button>
            </form>
            @endforeach
            <a href="{{ route('admin.users') }}" class="btn btn-primary px-4 mt-2"><i class="bi bi-people me-2"></i>Kelola Persetujuan Akun</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card p-4">
            <h6 class="fw-bold">Info</h6>
            <p class="small text-secondary mb-0">Pengaturan absensi & sistem global dikelola Admin Pusat. Akun pendaftar baru berstatus nonaktif sampai Anda setujui di menu Akun.</p>
        </div>
    </div>
</div>
@else
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

                    <div class="form-check mb-3">
                        <input type="hidden" name="registration_guru_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="registration_guru_enabled" value="1" {{ $registrationGuruEnabled ? 'checked' : '' }} id="regGuruCheck">
                        <label class="form-check-label small" for="regGuruCheck">
                            Izinkan <strong>Guru</strong> mendaftar akun baru dari halaman login.
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="hidden" name="registration_siswa_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="registration_siswa_enabled" value="1" {{ $registrationSiswaEnabled ? 'checked' : '' }} id="regSiswaCheck">
                        <label class="form-check-label small" for="regSiswaCheck">
                            Izinkan <strong>Siswa</strong> mendaftar akun baru dari halaman login.
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
@endif

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
