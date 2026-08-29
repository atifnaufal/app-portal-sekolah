@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .lms-topbar {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 12px 16px; display: flex; align-items: center; gap: 12px;
    }
    .lms-body { max-width: 640px; margin: 0 auto; padding: 16px 16px 48px; }
    .lms-avatar {
        width: 44px; height: 44px; border-radius: var(--radius-sm);
        object-fit: cover; flex-shrink: 0;
    }
    .tab-pane { animation: fadeUp 0.3s ease both; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

    /* Modal bottom sheet */
    .sheet {
        position: fixed; inset: 0; z-index: 2000; display: none;
        align-items: flex-end; justify-content: center;
        background: rgba(15, 23, 42, .45);
    }
    .sheet.open { display: flex; }
    .sheet-card {
        width: 100%; max-width: 640px; background: var(--surface-card);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        padding: 24px 20px 32px;
    }
</style>

<div class="lms-topbar">
    <a href="{{ route('dashboard') }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="padding:0;width:40px;height:40px;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div class="fw-bold" style="font-size:18px;letter-spacing:-0.4px;">Nilai Akademik</div>
</div>

<div class="lms-body">
    <header class="mobile-hero" style="margin-bottom:20px;">
        <div class="eyebrow">
            @if($isGuru)
                MANAJEMEN AKADEMIK
            @else
                {{ $user->kelas?->nama ?? 'INFORMASI AKADEMIK' }}
            @endif
        </div>
        <div class="hero-title mt-2">{{ $isGuru ? 'Penilaian Siswa' : 'Laporan Nilai' }}</div>
        <div class="mt-2" style="font-size:12px;color:rgba(255,255,255,.7);line-height:1.6;">
            {{ $isGuru ? 'Monitor dan evaluasi performa akademik siswa di kelas Anda secara real-time.' : 'Rekapitulasi pencapaian tugas, UTS, dan UAS Anda sepanjang semester ini.' }}
        </div>
    </header>

    <main class="mobile-content">
        @if($isGuru)
            @if($managedClass)
                {{-- Card Rekap per Semester untuk Kelas Binaan --}}
                <div class="pui-card mb-3" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;">
                    <div style="padding:20px;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-award-fill" style="color:#fbbf24;"></i>
                            <div class="fw-bold" style="font-size:15px;">Rekap Nilai Kelas Binaan</div>
                        </div>
                        <div class="small mb-3" style="color:rgba(255,255,255,.6);">
                            Unduh rekap nilai seluruh siswa kelas <b>{{ $managedClass->nama }}</b>.
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select id="rekapSemester" class="pui-select" style="width:auto;background:#fff;border:none;color:#0f172a !important;">
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                            <a href="#" id="btnRecapPdf" onclick="goRecap('pdf'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#f59e0b;color:#0f172a;">
                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                            </a>
                            <a href="#" id="btnRecapExcel" onclick="goRecap('excel'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#22c55e;color:#0f172a;">
                                <i class="bi bi-file-earmark-excel-fill"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
                <script>
                    function goRecap(type) {
                        var sem = document.getElementById('rekapSemester').value;
                        var base = '{{ route('nilai.recap', $managedClass->id) }}';
                        var url = base + (type === 'excel' ? '/excel' : '') + '?semester=' + sem;
                        puiExportFile(url, 'Rekap Nilai Kelas Binaan', type);
                    }
                </script>
            @endif
            {{-- Rekap Bulanan / Tahunan (lintas mapel) --}}
            <div class="pui-card pui-card-hero mb-3" style="background:linear-gradient(135deg,#7c3aed 0%,#a78bfa 100%);color:#fff;overflow:hidden;">
                <div style="padding:20px;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-calendar3" style="color:#fde68a;"></i>
                        <div class="fw-bold" style="font-size:15px;">Rekap Bulanan & Tahun</div>
                    </div>
                    <div class="small mb-3" style="color:rgba(255,255,255,.75);">
                        Unduh rekap nilai seluruh siswa (lintas mapel) berdasarkan periode bulan atau tahun dalam format PDF / Excel.
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <select id="perRecapPeriode" class="pui-select" style="width:auto;background:#fff;border:none;">
                            <option value="bulanan">Bulanan</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                        <select id="perRecapTahun" class="pui-select" style="width:auto;background:#fff;border:none;">
                            @for($y = now()->year; $y >= now()->year - 4; $y--)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <select id="perRecapBulan" class="pui-select" style="width:auto;background:#fff;border:none;">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select id="perRecapTA" class="pui-select" style="width:auto;background:#fff;border:none;display:none;">
                            @for($y = now()->year; $y >= now()->year - 3; $y--)
                                <option value="{{ $y }}/{{ $y + 1 }}">{{ $y }}/{{ $y + 1 }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" onclick="goPerRecap('pdf'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#f59e0b;color:#0f172a;"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
                        <button type="button" onclick="goPerRecap('excel'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#22c55e;color:#0f172a;"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                    </div>
                </div>
            </div>
            <script>
                var perRecapPeriode = document.getElementById('perRecapPeriode');
                function syncPerRecap() {
                    document.getElementById('perRecapTA').style.display = perRecapPeriode.value === 'tahunan' ? '' : 'none';
                    document.getElementById('perRecapBulan').parentElement.style.display = perRecapPeriode.value === 'bulanan' ? '' : 'none';
                }
                perRecapPeriode.addEventListener('change', syncPerRecap);
                syncPerRecap();
                function goPerRecap(type) {
                    var periode = perRecapPeriode.value;
                    var tahun = document.getElementById('perRecapTahun').value;
                    var bulan = document.getElementById('perRecapBulan').value;
                    var ta = document.getElementById('perRecapTA').value;
                    var url = "{{ route('nilai.recap.periode') }}"
                        + (type === 'excel' ? '/excel' : '')
                        + '?periode=' + periode;
                    if (periode === 'bulanan') url += '&tahun=' + tahun + '&bulan=' + bulan;
                    else url += '&tahun_ajaran=' + encodeURIComponent(ta);
                    puiExportFile(url, 'Rekap Bulanan & Tahunan', type);
                }
            </script>

            @if(!$selectedSubject)
                <div class="pui-section">
                    <h3>Mata Pelajaran Anda</h3>
                </div>
                @forelse($mataPelajarans as $mp)
                    <a href="{{ route('nilai.index', ['subject_id' => $mp->id]) }}" class="pui-card pui-row text-decoration-none mb-2" style="padding:14px 16px;text-decoration:none;color:var(--ink);">
                        <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:linear-gradient(135deg,#f1f5f9,#e2e8f0);display:flex;align-items:center;justify-content:center;color:var(--blue);flex-shrink:0;">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="grow">
                            <div class="t" style="font-size:15px;color:var(--ink);">{{ $mp->nama }}</div>
                            <div class="s fw-semibold">{{ $mp->kelas?->nama ?? 'Umum' }} · {{ $mp->kode }}</div>
                        </div>
                        <div class="pui-btn pui-btn-ghost pui-btn-sm" style="padding:0;width:32px;height:32px;border-radius:999px;">
                            <i class="bi bi-chevron-right" style="font-size:12px;"></i>
                        </div>
                    </a>
                @empty
                    <div class="pui-empty">
                        <i class="bi bi-journal-x ico"></i>
                        <h4>Belum ada mapel</h4>
                    </div>
                @endforelse
            @else
                <div class="pui-card d-flex align-items-center justify-content-between mb-3" style="padding:14px 16px;">
                    <div>
                        <div class="fw-bold" style="font-size:15px;">{{ $selectedSubject->nama }}</div>
                        <div class="small fw-semibold" style="color:var(--faint);">{{ $selectedSubject->kelas?->nama ?? 'Semua Kelas' }}</div>
                    </div>
                    <a href="{{ route('nilai.index') }}" class="pui-btn pui-btn-primary pui-btn-sm pui-btn-round">Ganti Mapel</a>
                </div>

                {{-- Premium Rekap & Unduhan Guru per Mapel --}}
                <div class="pui-card mb-3" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%);color:#fff;overflow:hidden;">
                    <div style="padding:20px;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-file-earmark-bar-graph-fill" style="color:#fbbf24;"></i>
                            <div class="fw-bold" style="font-size:15px;">Rekap & Unduhan Nilai</div>
                        </div>
                        <div class="small mb-3" style="color:rgba(255,255,255,.65);">
                            Unduh rekap nilai siswa untuk mapel <b class="text-white">{{ $selectedSubject->nama }}</b> dalam format PDF atau Excel.
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <select id="rekapMapelSemester" class="pui-select" style="width:auto;background:#fff;border:none;color:#0f172a !important;">
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                            <a href="#" onclick="goRecapMapel('pdf'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#f59e0b;color:#0f172a;">
                                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                            </a>
                            <a href="#" onclick="goRecapMapel('excel'); return false;" class="pui-btn pui-btn-sm pui-btn-round" style="background:#22c55e;color:#0f172a;">
                                <i class="bi bi-file-earmark-excel-fill"></i> Excel
                            </a>
                        </div>
                    </div>
                </div>
                <script>
                    function goRecapMapel(type) {
                        var sem = document.getElementById('rekapMapelSemester').value;
                        var base = '{{ route('nilai.recap.mapel', $selectedSubject->id) }}';
                        puiExportFile(base + (type === 'excel' ? '/excel' : '') + '?semester=' + sem, 'Rekap Nilai Mapel', type);
                    }
                </script>

                @forelse($students as $siswa)
                    @php
                        $nilaiRecord = $siswa->nilai_records->first();
                    @endphp
                    <div class="pui-card mb-3">
                        <div style="padding:16px;">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $siswa->foto ? asset('storage/'.$siswa->foto) : '' }}" data-name="{{ $siswa->name }}"
                                     onerror="nilaiAvatarFallback(this);"
                                     class="lms-avatar" alt="{{ $siswa->name }}">
                                <div class="grow" style="flex:1;min-width:0;">
                                    <div class="fw-bold" style="font-size:14px;color:var(--ink);">{{ $siswa->name }}</div>
                                    <div class="small fw-bold" style="font-size:10px;color:var(--faint);">NIS: {{ $siswa->nik ?? '-' }}</div>
                                </div>
                                <button type="button" class="pui-btn pui-btn-primary pui-btn-sm pui-btn-round"
                                    onclick="openInputNilai(@json($siswa), @json($siswa->nilai_records->values()->all()))">
                                    Input
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                        <div class="num" style="font-size:15px;color:var(--blue);">{{ $nilaiRecord->tugas ?? '-' }}</div>
                                        <div class="lb">Tugas</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                        <div class="num" style="font-size:15px;color:var(--blue);">{{ $nilaiRecord->uts ?? '-' }}</div>
                                        <div class="lb">UTS</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                        <div class="num" style="font-size:15px;color:var(--blue);">{{ $nilaiRecord->uas ?? '-' }}</div>
                                        <div class="lb">UAS</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pui-empty">
                        <i class="bi bi-people ico"></i>
                        <h4>Data siswa kosong</h4>
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
                <div class="pui-card mb-3">
                    <div style="padding:16px;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="fw-bold mb-1" style="font-size:16px;color:var(--ink);">{{ $mp->nama }}</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="pui-chip" style="font-size:9px;">SEMESTER {{ $mpNilais->first()->semester }}</span>
                                    <span class="small fw-bold" style="font-size:10px;color:var(--faint);">KKM: {{ $mp->kkm ?? 75 }}</span>
                                </div>
                            </div>
                            <div class="grade-badge" style="width:44px;height:44px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;background:{{ $bgColor }};color:{{ $textColor }};">
                                {{ round($avg) }}
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                    <div class="num" style="font-size:15px;color:var(--ink);">{{ $mpNilais->avg('tugas') ?: '-' }}</div>
                                    <div class="lb">Tugas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                    <div class="num" style="font-size:15px;color:var(--ink);">{{ $mpNilais->avg('uts') ?: '-' }}</div>
                                    <div class="lb">UTS</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="pui-stat" style="background:var(--surface);border:1px solid var(--line);box-shadow:none;">
                                    <div class="num" style="font-size:15px;color:var(--ink);">{{ $mpNilais->avg('uas') ?: '-' }}</div>
                                    <div class="lb">UAS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="pui-empty">
                    <i class="bi bi-award ico"></i>
                    <h4>Belum ada nilai</h4>
                    <p>Nilai Anda akan muncul di sini setelah diproses guru.</p>
                </div>
            @endforelse
        @endif
    </main>
</div>

@if($isGuru && $selectedSubject)
    <div class="sheet" id="inputNilaiModal" onclick="if(event.target===this)closeInputNilai()">
        <div class="sheet-card">
            <div class="fw-bold h5 mb-3">Input Nilai: <span id="modalSiswaName"></span></div>
            <form action="{{ route('nilai.upsert') }}" method="POST">
                @csrf
                <input type="hidden" name="siswa_id" id="modalSiswaId">
                <input type="hidden" name="mata_pelajaran_id" value="{{ $selectedSubject->id }}">

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="pui-label">Semester</label>
                        <select name="semester" id="modalSemester" class="pui-select">
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="pui-label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" id="modalTahunAjaranInput" value="{{ $selectedSubject->kelas?->tahun_ajaran ?? '' }}" class="pui-input" placeholder="2025/2026">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <label class="pui-label">Tugas</label>
                        <input type="number" name="tugas" id="modalTugas" class="pui-input" min="0" max="100" placeholder="0">
                    </div>
                    <div class="col-4">
                        <label class="pui-label">UTS</label>
                        <input type="number" name="uts" id="modalUts" class="pui-input" min="0" max="100" placeholder="0">
                    </div>
                    <div class="col-4">
                        <label class="pui-label">UAS</label>
                        <input type="number" name="uas" id="modalUas" class="pui-input" min="0" max="100" placeholder="0">
                    </div>
                </div>

                <button type="submit" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round" style="padding:15px;">Simpan Nilai</button>
                <button type="button" class="pui-btn pui-btn-ghost pui-btn-block pui-btn-round mt-2" onclick="closeInputNilai()">Batal</button>
            </form>
        </div>
    </div>

    <script>
        var currentSiswaRecords = [];
        function setNilaiFields() {
            var sem = parseInt(document.getElementById('modalSemester').value);
            var rec = currentSiswaRecords.find(function(r){ return parseInt(r.semester) === sem; });
            document.getElementById('modalTugas').value = rec ? (rec.tugas ?? '') : '';
            document.getElementById('modalUts').value = rec ? (rec.uts ?? '') : '';
            document.getElementById('modalUas').value = rec ? (rec.uas ?? '') : '';
            if (rec && rec.tahun_ajaran) {
                document.getElementById('modalTahunAjaranInput').value = rec.tahun_ajaran;
            }
        }
        function openInputNilai(siswa, records) {
            currentSiswaRecords = records || [];
            document.getElementById('modalSiswaId').value = siswa.id;
            document.getElementById('modalSiswaName').innerText = siswa.name;
            var sem = currentSiswaRecords.length ? currentSiswaRecords[0].semester : 1;
            document.getElementById('modalSemester').value = sem;
            setNilaiFields();
            document.getElementById('inputNilaiModal').classList.add('open');
        }
        function closeInputNilai() {
            document.getElementById('inputNilaiModal').classList.remove('open');
        }
        document.getElementById('modalSemester').addEventListener('change', setNilaiFields);
    </script>
@endif
<script>
function nilaiAvatarFallback(el) {
    var name = el.getAttribute('data-name') || 'U';
    var letter = (name.charAt(0) || 'U').toUpperCase();
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">'
        + '<defs><linearGradient id="ng" x1="0" y1="0" x2="1" y2="1">'
        + '<stop offset="0%" stop-color="#eef2ff"/><stop offset="100%" stop-color="#e0e7ff"/>'
        + '</linearGradient></defs>'
        + '<rect width="100%" height="100%" fill="url(#ng)"/>'
        + '<text x="50%" y="54%" font-family="sans-serif" font-size="46" font-weight="800" fill="#4f46e5" text-anchor="middle" dominant-baseline="middle">' + letter + '</text></svg>';
    el.onerror = null;
    el.src = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}
</script>
@endsection
