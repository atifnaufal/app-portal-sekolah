@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }
    .ai-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        position: relative; overflow: hidden; margin-bottom: 16px;
    }
    .ai-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue); opacity: 0.85;
    }
    .grade-badge {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 16px;
    }
    .subject-icon {
        width: 40px; height: 40px; border-radius: 12px;
        background: #f1f5f9; color: #475569;
        display: flex; align-items: center; justify-content: center;
    }
    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .tugas-modal {
        position: fixed; inset: 0; z-index: 2000; display: none;
        align-items: flex-end; justify-content: center;
        background: rgba(15, 23, 42, .45);
    }
    .tugas-modal.open { display: flex; }
    .tugas-modal-card {
        width: 100%; max-width: 680px; background: #fff;
        border-radius: 28px 28px 0 0; padding: 24px 20px 32px;
    }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Nilai Akademik</div>
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 32px 32px; margin-bottom: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 32px 24px 40px; box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15);">
        <div class="eyebrow" style="color: rgba(255,255,255,0.6); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em;">
            @if($isGuru)
                MANAJEMEN AKADEMIK
            @else
                {{ $user->kelas?->nama ?? 'INFORMASI AKADEMIK' }}
            @endif
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 26px; font-weight: 800; letter-spacing: -0.02em;">{{ $isGuru ? 'Penilaian Siswa' : 'Laporan Nilai' }}</div>
        @if($isGuru && $managedClass)
            <div class="mt-3">
                <a href="{{ route('nilai.recap', $managedClass->id) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak Rekap Kelas {{ $managedClass->nama }}
                </a>
            </div>
        @endif
        <div class="mt-2" style="font-size: 12px; color: rgba(255,255,255,0.7); line-height: 1.6;">
            {{ $isGuru ? 'Monitor dan evaluasi performa akademik siswa di kelas Anda secara real-time.' : 'Rekapitulasi pencapaian tugas, UTS, dan UAS Anda sepanjang semester ini.' }}
        </div>
    </header>

    <main class="mobile-content px-3">
        @if($isGuru)
            @if(!$selectedSubject)
                <div class="d-flex align-items-center gap-2 mb-3 px-1">
                    <div style="width: 4px; height: 16px; background: var(--blue); border-radius: 2px;"></div>
                    <h6 class="fw-bold mb-0" style="font-size: 14px; color: #475569;">MATA PELAJARAN ANDA</h6>
                </div>
                @forelse($mataPelajarans as $mp)
                    <a href="{{ route('nilai.index', ['subject_id' => $mp->id]) }}" class="card ai-card text-decoration-none text-dark" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="subject-icon" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                                <i class="bi bi-journal-text text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold" style="font-size: 15px; color: #1e293b;">{{ $mp->nama }}</div>
                                <div class="small text-muted fw-semibold" style="font-size: 11px;">{{ $mp->kelas?->nama ?? 'Umum' }} · {{ $mp->kode }}</div>
                            </div>
                            <div class="btn btn-light rounded-pill p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-chevron-right text-muted" style="font-size: 12px;"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center py-5 opacity-50">
                        <i class="bi bi-journal-x h1"></i>
                        <p class="mt-2 fw-bold">Belum ada mapel.</p>
                    </div>
                @endforelse
            @else
                <div class="d-flex align-items-center justify-content-between mb-4 px-2 py-3 bg-white rounded-4 border shadow-sm">
                    <div>
                        <div class="fw-bold" style="font-size: 15px;">{{ $selectedSubject->nama }}</div>
                        <div class="small text-muted fw-semibold">{{ $selectedSubject->kelas?->nama ?? 'Semua Kelas' }}</div>
                    </div>
                    <a href="{{ route('nilai.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm">Ganti Mapel</a>
                </div>

                @forelse($students as $siswa)
                    @php
                        $nilaiRecord = $siswa->nilai_records->first();
                    @endphp
                    <div class="card ai-card" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $siswa->foto ? asset('storage/'.$siswa->foto) : 'https://ui-avatars.com/api/?name='.urlencode($siswa->name).'&background=f1f5f9&color=94a3b8' }}"
                                     class="rounded-4" style="width: 44px; height: 44px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-bold" style="font-size: 14px; color: #1e293b;">{{ $siswa->name }}</div>
                                    <div class="small text-muted fw-bold" style="font-size: 10px;">NIS: {{ $siswa->nik ?? '-' }}</div>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3"
                                    onclick="openInputNilai(@json($siswa), @json($nilaiRecord))">
                                    Input
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                        <div class="fw-black text-primary" style="font-size: 15px;">{{ $nilaiRecord->tugas ?? '-' }}</div>
                                        <div class="text-muted fw-bold" style="font-size: 8px; text-transform: uppercase;">Tugas</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                        <div class="fw-black text-primary" style="font-size: 15px;">{{ $nilaiRecord->uts ?? '-' }}</div>
                                        <div class="text-muted fw-bold" style="font-size: 8px; text-transform: uppercase;">UTS</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                        <div class="fw-black text-primary" style="font-size: 15px;">{{ $nilaiRecord->uas ?? '-' }}</div>
                                        <div class="text-muted fw-bold" style="font-size: 8px; text-transform: uppercase;">UAS</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 opacity-25">
                        <i class="bi bi-people h1"></i>
                        <p class="mt-2 fw-bold">Data siswa kosong.</p>
                    </div>
                @endforelse
            @endif
        @else
            {{-- Siswa View --}}
            @forelse($nilais as $mpId => $mpNilais)
                @php
                    $mp = $mpNilais->first()->mataPelajaran;
                    $avg = $mpNilais->avg(function($n) {
                        return ($n->tugas + $n->uts + $n->uas) / 3;
                    });
                    $tone = $avg >= 85 ? 'success' : ($avg >= 75 ? 'primary' : ($avg >= 60 ? 'warning' : 'danger'));
                    $bgColor = match($tone) {
                        'success' => '#f0fdf4',
                        'primary' => '#eff6ff',
                        'warning' => '#fffbeb',
                        'danger' => '#fef2f2',
                    };
                    $textColor = match($tone) {
                        'success' => '#16a34a',
                        'primary' => '#2563eb',
                        'warning' => '#d97706',
                        'danger' => '#dc2626',
                    };
                @endphp
                <div class="card ai-card" style="animation: slideUp 0.4s ease both;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="fw-bold mb-1" style="font-size: 16px; color: #1e293b;">{{ $mp->nama }}</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-muted border fw-bold" style="font-size: 9px;">SEMESTER {{ $mpNilais->first()->semester }}</span>
                                    <span class="small text-muted fw-bold" style="font-size: 10px;">KKM: {{ $mp->kkm ?? 75 }}</span>
                                </div>
                            </div>
                            <div class="grade-badge shadow-sm border" style="background: {{ $bgColor }}; color: {{ $textColor }}; border-color: rgba(0,0,0,0.05) !important;">
                                {{ round($avg) }}
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                    <div class="fw-black" style="font-size: 15px; color: #1e293b;">{{ $mpNilais->avg('tugas') ?: '-' }}</div>
                                    <div class="text-muted fw-extrabold" style="font-size: 9px; text-transform: uppercase;">Tugas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                    <div class="fw-black" style="font-size: 15px; color: #1e293b;">{{ $mpNilais->avg('uts') ?: '-' }}</div>
                                    <div class="text-muted fw-extrabold" style="font-size: 9px; text-transform: uppercase;">UTS</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-2 rounded-4" style="background: #f8fafc;">
                                    <div class="fw-black" style="font-size: 15px; color: #1e293b;">{{ $mpNilais->avg('uas') ?: '-' }}</div>
                                    <div class="text-muted fw-extrabold" style="font-size: 9px; text-transform: uppercase;">UAS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-award h1 text-muted"></i>
                    <div class="fw-bold mt-2">Belum ada nilai</div>
                    <p class="small text-muted mt-1">Nilai Anda akan muncul di sini setelah diproses guru.</p>
                </div>
            @endforelse
        @endif
    </main>
</div>

@if($isGuru && $selectedSubject)
    <div class="tugas-modal" id="inputNilaiModal" onclick="if(event.target===this)closeInputNilai()">
        <div class="tugas-modal-card">
            <div class="fw-bold h5 mb-3">Input Nilai: <span id="modalSiswaName"></span></div>
            <form action="{{ route('nilai.upsert') }}" method="POST">
                @csrf
                <input type="hidden" name="siswa_id" id="modalSiswaId">
                <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedSubject->id }}">
                <input type="hidden" name="semester" value="1"> {{-- Default semester --}}

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <label class="small fw-bold text-muted mb-1 d-block text-uppercase">Tugas</label>
                        <input type="number" name="tugas" id="modalTugas" class="form-control rounded-4 border-2" min="0" max="100" placeholder="0">
                    </div>
                    <div class="col-4">
                        <label class="small fw-bold text-muted mb-1 d-block text-uppercase">UTS</label>
                        <input type="number" name="uts" id="modalUts" class="form-control rounded-4 border-2" min="0" max="100" placeholder="0">
                    </div>
                    <div class="col-4">
                        <label class="small fw-bold text-muted mb-1 d-block text-uppercase">UAS</label>
                        <input type="number" name="uas" id="modalUas" class="form-control rounded-4 border-2" min="0" max="100" placeholder="0">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">Simpan Nilai</button>
                <button type="button" class="btn btn-light w-100 py-3 rounded-pill mt-2" onclick="closeInputNilai()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openInputNilai(siswa, nilai) {
            document.getElementById('modalSiswaId').value = siswa.id;
            document.getElementById('modalSiswaName').innerText = siswa.name;
            document.getElementById('modalTugas').value = nilai ? nilai.tugas : '';
            document.getElementById('modalUts').value = nilai ? nilai.uts : '';
            document.getElementById('modalUas').value = nilai ? nilai.uas : '';
            document.getElementById('inputNilaiModal').classList.add('open');
        }
        function closeInputNilai() {
            document.getElementById('inputNilaiModal').classList.remove('open');
        }
    </script>
@endif
@endsection
