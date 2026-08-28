@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isGuru = $user->role === 'guru';
    $pendingTugas = $pendingTugas ?? collect();
    $completedTugas = $completedTugas ?? collect();
    $expiredTugas = $expiredTugas ?? collect();
    $siswaCounts = $siswaCounts ?? collect();

    if ($isGuru) {
        $statTotal = $tugas->count();
        $statPending = (int) $tugas->sum('pending_count');
        $statRevisi = (int) $tugas->sum('revisi_count');
        $statExpired = $tugas->filter(fn ($item) => $item->isExpired())->count();
    } else {
        $statTotal = $tugas->count();
        $statPending = $pendingTugas->count();
        $statDone = $completedTugas->count();
        $statExpired = $expiredTugas->count();
    }
@endphp

<style>
    .page-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.88); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.7);
        padding: 12px 20px; display: flex; align-items: center; gap: 12px;
    }
    .page-container { padding-top: 70px; padding-bottom: 48px; }
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .stat-chip {
        background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.16);
        border-radius: 16px; padding: 10px 8px; text-align: center;
    }
    .stat-chip .num { font-size: 18px; font-weight: 800; color: #fff; line-height: 1.1; }
    .stat-chip .lbl { font-size: 9px; font-weight: 700; letter-spacing: .04em; color: rgba(255,255,255,.72); margin-top: 4px; text-transform: uppercase; }
    .search-wrap {
        background: #fff; border-radius: 16px; display: flex; align-items: center; gap: 10px;
        padding: 10px 14px; box-shadow: 0 8px 24px rgba(20,33,61,.06);
    }
    .search-wrap input { border: 0; outline: none; width: 100%; font-size: 14px; background: transparent; }
    .filter-row { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
    .filter-row::-webkit-scrollbar { display: none; }
    .filter-chip {
        border: 1px solid #e2e8f0; background: #fff; color: #64748b;
        border-radius: 999px; padding: 7px 14px; font-size: 12px; font-weight: 700;
        white-space: nowrap; cursor: pointer;
    }
    .filter-chip.active { background: #14213d; color: #fff; border-color: #14213d; }
    .ai-card {
        background: #fff; border: none; border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        position: relative; overflow: hidden;
    }
    .ai-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
        background: var(--blue); opacity: 0.85;
    }
    .ai-card.urgent::before { background: var(--danger); }
    .ai-card.completed::before { background: #10b981; }
    .ai-card.pending::before { background: #f59e0b; }
    .ai-card.revise::before { background: #f59e0b; }
    .glass-pill {
        background: rgba(36, 107, 254, 0.06); color: var(--blue);
        border: 1px solid rgba(36, 107, 254, 0.12);
        padding: 4px 10px; border-radius: 100px; font-size: 10px; font-weight: 800; letter-spacing: .02em;
    }
    .revise-badge {
        background: #fef3c7; color: #b45309; border: 1px solid #fde68a;
        padding: 3px 10px; border-radius: 100px; font-size: 10px; font-weight: 800;
    }
    .progress-slim { height: 7px; border-radius: 99px; background: #eef2f7; overflow: hidden; }
    .progress-slim > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #246bfe, #60a5fa); }
    .icon-action {
        width: 36px; height: 36px; border-radius: 12px; border: 1px solid #e2e8f0;
        background: #fff; display: inline-flex; align-items: center; justify-content: center;
        color: #475569; text-decoration: none;
    }
    .icon-action.danger { color: #d94b61; border-color: #f8d7de; background: #fff5f6; }
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
    .empty-box { text-align: center; padding: 48px 20px; background: #fff; border-radius: 24px; }
    @keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>

<div class="page-header">
    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
        <i class="bi bi-chevron-left h5 mb-0"></i>
    </a>
    <div class="fw-bold" style="font-size: 18px; letter-spacing: -0.4px;">Pusat Tugas</div>
    @if($isGuru)
        <a href="{{ route('tugas.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 ms-auto" style="font-weight: 700;">+ Buat</a>
    @endif
</div>

<div class="page-container">
    <header class="mobile-hero" style="border-radius: 0 0 28px 28px; margin-bottom: 20px; background: linear-gradient(135deg, #0f172a, #1e293b); padding: 32px 24px 28px;">
        <div class="eyebrow" style="color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;">
            {{ $user->kelas?->nama ?? ($isGuru ? 'Panel Pengajar' : 'Akademik') }}
        </div>
        <div class="hero-title mt-2 text-white" style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">{{ $isGuru ? 'Kelola Tugas Kelas' : 'Tugas Saya' }}</div>
        <p class="mb-3 mt-1" style="font-size: 12px; color: rgba(255,255,255,.6); line-height: 1.5;">
            {{ $isGuru ? 'Pantau pengumpulan, nilai, dan kelola instruksi tugas.' : 'Pantau deadline dan kirim jawaban tugas Anda tepat waktu.' }}
        </p>
        <div class="stat-grid" style="gap: 10px;">
            @if($isGuru)
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statTotal }}</div><div class="lbl">Total</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statPending }}</div><div class="lbl">Nilai</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statRevisi }}</div><div class="lbl">Revisi</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statExpired }}</div><div class="lbl">Lewat</div></div>
            @else
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statTotal }}</div><div class="lbl">Aktif</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statPending }}</div><div class="lbl">Review</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statDone }}</div><div class="lbl">Skor</div></div>
                <div class="stat-chip" style="background: rgba(255,255,255,0.05); border-radius: 14px; padding: 8px;"><div class="num" style="font-size: 16px;">{{ $statExpired }}</div><div class="lbl">Telat</div></div>
            @endif
        </div>
    </header>

    <main class="mobile-content px-3 pt-0">
        <div class="search-wrap mb-3">
            <i class="bi bi-search text-muted"></i>
            <input type="search" id="tugasSearch" placeholder="Cari judul atau kelas..." autocomplete="off">
        </div>

        <div class="filter-row mb-4" id="tugasFilters">
            <button type="button" class="filter-chip active" data-filter="all">Semua</button>
            @if($isGuru)
                <button type="button" class="filter-chip" data-filter="running">Berjalan</button>
                <button type="button" class="filter-chip" data-filter="soon">Deadline dekat</button>
                <button type="button" class="filter-chip" data-filter="expired">Terlewat</button>
                <button type="button" class="filter-chip" data-filter="review">Perlu dinilai</button>
            @else
                <button type="button" class="filter-chip" data-filter="active">Aktif</button>
                <button type="button" class="filter-chip" data-filter="pending">Menunggu</button>
                <button type="button" class="filter-chip" data-filter="done">Selesai</button>
                <button type="button" class="filter-chip" data-filter="expired">Terlewat</button>
            @endif
        </div>

        @if($isGuru)
            <div id="tugasList">
                @forelse($tugas as $item)
                    @php
                        $deadline = $item->deadlineStatus();
                        $totalSiswa = (int) ($siswaCounts[$item->kelas_id] ?? 0);
                        $submitted = (int) $item->pengumpulan_count;
                        $pct = $totalSiswa > 0 ? min(100, (int) round(($submitted / $totalSiswa) * 100)) : 0;
                        $filterKey = $item->isExpired() ? 'expired' : (($deadline['key'] === 'soon' || $deadline['key'] === 'today') ? 'soon' : 'running');
                        if ((int) $item->pending_count > 0) {
                            $filterKey .= ' review';
                        }
                        $toneClass = $item->isExpired() ? 'urgent' : ((int) $item->revisi_count > 0 ? 'revise' : '');
                    @endphp
                    <article class="card ai-card mb-3 {{ $toneClass }}" data-card data-filter="{{ $filterKey }}" data-search="{{ strtolower($item->judul.' '.$item->kelas?->nama) }}" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-4">
                            <a href="{{ route('tugas.show', $item) }}" class="text-decoration-none text-dark d-block">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="glass-pill">
                                            <i class="bi {{ $item->isForm() ? 'bi-ui-checks' : 'bi-file-earmark-text' }} me-1"></i>
                                            {{ $item->isForm() ? 'FORMULIR ONLINE' : 'PENGIRIMAN FILE' }}
                                        </div>
                                        <div class="glass-pill" style="color:#475569;background:#f8fafc;border-color:#e2e8f0;">{{ $item->kelas?->nama }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-bold text-{{ $deadline['tone'] === 'muted' ? 'muted' : $deadline['tone'] }}" style="font-size: 10px;">DEADLINE</div>
                                        <div class="fw-bold" style="font-size: 12px;">{{ $item->batas_pengumpulan?->format('d M Y') ?? 'Terbuka' }}</div>
                                    </div>
                                </div>
                                <h3 class="h6 fw-bold mb-1" style="font-size: 16px; line-height: 1.4;">{{ $item->judul }}</h3>
                                <p class="small text-secondary mb-3" style="line-height: 1.55;">{{ \Illuminate\Support\Str::limit($item->deskripsi ?: 'Tidak ada deskripsi tambahan.', 90) }}</p>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-{{ $deadline['tone'] === 'muted' ? 'muted' : $deadline['tone'] }}">{{ $deadline['label'] }}</span>
                                    <span class="text-muted">{{ $submitted }}/{{ $totalSiswa }} mengumpulkan</span>
                                </div>
                                <div class="progress-slim mb-1"><span style="width: {{ $pct }}%"></span></div>
                                <div class="d-flex gap-2 mt-2" style="font-size: 11px;">
                                    <span class="text-warning fw-bold">{{ (int) $item->pending_count }} menunggu</span>
                                    <span class="text-success fw-bold">{{ (int) $item->dinilai_count }} dinilai</span>
                                    @if((int) $item->revisi_count > 0)
                                        <span class="text-danger fw-bold">{{ (int) $item->revisi_count }} revisi</span>
                                    @endif
                                </div>
                            </a>
                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <a href="{{ route('tugas.show', $item) }}" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1">Kelola</a>
                                <a href="{{ route('tugas.edit', $item) }}" class="icon-action" title="Edit tugas"><i class="bi bi-pencil-square"></i></a>
                                <button type="button" class="icon-action danger" title="Hapus tugas"
                                    onclick="openDeleteTugas(@json(route('tugas.destroy', $item)), @json($item->judul), @json($submitted.' pengumpulan akan ikut terhapus'))">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-box">
                        <i class="bi bi-journal-plus h1 text-primary"></i>
                        <div class="fw-bold mt-2">Belum ada tugas</div>
                        <div class="small text-secondary mt-1 mb-3">Buat tugas pertama untuk kelas Anda.</div>
                        <a href="{{ route('tugas.create') }}" class="btn btn-primary rounded-pill px-4">+ Buat Tugas</a>
                    </div>
                @endforelse
            </div>
        @else
            <div id="tugasList">
                @foreach($tugas as $item)
                    @php
                        $deadline = $item->deadlineStatus();
                        $needsRevision = $item->pengumpulan->first()?->revisi_aktif;
                    @endphp
                    <a href="{{ route('tugas.show', $item) }}" class="card ai-card mb-3 text-decoration-none text-dark {{ $needsRevision ? 'revise' : '' }}" data-card data-filter="active" data-search="{{ strtolower($item->judul.' '.$item->kelas?->nama) }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="glass-pill">
                                        <i class="bi {{ $item->isForm() ? 'bi-ui-checks' : 'bi-file-earmark-text' }} me-1"></i>
                                        {{ $item->isForm() ? 'FORMULIR ONLINE' : 'PENGIRIMAN FILE' }}
                                    </div>
                                    @if($needsRevision)
                                        <span class="revise-badge"><i class="bi bi-arrow-repeat me-1"></i>PERLU REVISI</span>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="small fw-bold text-{{ $deadline['tone'] === 'muted' ? 'muted' : $deadline['tone'] }}" style="font-size: 10px;">DEADLINE</div>
                                    <div class="fw-bold" style="font-size: 13px;">{{ $item->batas_pengumpulan?->format('d M') ?? 'Terbuka' }}</div>
                                </div>
                            </div>
                            <h3 class="h6 fw-bold mb-2" style="font-size: 16px; line-height: 1.4;">{{ $item->judul }}</h3>
                            <p class="small text-secondary mb-3" style="line-height: 1.6;">{{ \Illuminate\Support\Str::limit($item->deskripsi ?: 'Buka modul untuk panduan lengkap.', 85) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-{{ $needsRevision ? 'warning' : 'primary' }}" style="font-size: 12px;">
                                    {{ $needsRevision ? 'Perbaiki & Kirim Ulang' : $deadline['label'] }}
                                </div>
                                <i class="bi bi-arrow-right-short h4 mb-0 text-muted"></i>
                            </div>
                        </div>
                    </a>
                @endforeach

                @foreach($pendingTugas as $item)
                    @php($submission = $item->pengumpulan->first())
                    <a href="{{ route('tugas.show', $item) }}" class="card ai-card pending mb-3 text-decoration-none" data-card data-filter="pending" data-search="{{ strtolower($item->judul) }}">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #fffbeb; color: #f59e0b;">
                                    <i class="bi bi-hourglass-split h6 mb-0"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $item->judul }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">Dikirim {{ $submission->dikumpulkan_pada?->format('d M Y, H:i') ?? 'Baru saja' }}</div>
                                </div>
                            </div>
                            <span class="badge rounded-pill" style="background: #fffbeb; color: #b45309; font-size: 10px; font-weight: 800;">MENUNGGU</span>
                        </div>
                    </a>
                @endforeach

                @foreach($completedTugas as $item)
                    @php($submission = $item->pengumpulan->first())
                    @php($gradeColor = $submission->nilai >= 85 ? '#16a34a' : ($submission->nilai >= 70 ? '#2563eb' : ($submission->nilai >= 55 ? '#d97706' : '#dc2626')))
                    @php($gradeBg = $submission->nilai >= 85 ? '#f0fdf4' : ($submission->nilai >= 70 ? '#eef4ff' : ($submission->nilai >= 55 ? '#fefce8' : '#fef2f2')))
                    <a href="{{ route('tugas.show', $item) }}" class="card ai-card completed mb-3 text-decoration-none" data-card data-filter="done" data-search="{{ strtolower($item->judul) }}" style="animation: slideUp 0.4s ease both;">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: {{ $gradeBg }}; color: {{ $gradeColor }};">
                                    <i class="bi bi-check2-circle h5 mb-0"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $item->judul }}</div>
                                    <div class="small text-muted" style="font-size: 11px;">Dinilai {{ $submission->dinilai_pada?->format('d M Y') ?? 'Baru saja' }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="h5 fw-bold mb-0" style="color: {{ $gradeColor }};">{{ $submission->nilai }}</div>
                                <div class="fw-bold" style="font-size: 9px; letter-spacing: 0.5px; color: {{ $gradeColor }}; opacity: 0.7;">SKOR</div>
                            </div>
                        </div>
                    </a>
                @endforeach

                @foreach($expiredTugas as $item)
                    <a href="{{ route('tugas.show', $item) }}" class="card ai-card urgent mb-3 text-decoration-none text-dark" data-card data-filter="expired" data-search="{{ strtolower($item->judul) }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between">
                                <div class="fw-bold">{{ $item->judul }}</div>
                                <span class="badge text-bg-danger rounded-pill" style="font-size: 10px;">TERLEWAT</span>
                            </div>
                            <div class="small text-secondary mt-1">Batas {{ $item->batas_pengumpulan?->format('d M Y') }} · Tugas tidak dapat dikumpulkan</div>
                        </div>
                    </a>
                @endforeach

                @if($tugas->isEmpty() && $pendingTugas->isEmpty() && $completedTugas->isEmpty() && $expiredTugas->isEmpty())
                    <div class="empty-box">
                        <i class="bi bi-stars h1 text-primary"></i>
                        <div class="fw-bold mt-2">Semua tugas selesai!</div>
                        <div class="small mt-1 text-secondary">Belum ada tugas baru untukmu.</div>
                    </div>
                @endif
            </div>
        @endif

        <div id="emptyFilter" class="empty-box d-none">
            <div class="fw-bold">Tidak ada tugas pada filter ini</div>
            <div class="small text-secondary mt-1">Coba kata kunci atau filter lain.</div>
        </div>
    </main>
</div>

<div class="tugas-modal" id="deleteModal" onclick="if(event.target===this)closeDeleteTugas()">
    <div class="tugas-modal-card">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fff5f6;color:#d94b61;">
                <i class="bi bi-trash3 h4 mb-0"></i>
            </div>
            <div>
                <div class="fw-bold">Hapus tugas?</div>
                <div class="small text-secondary">Tindakan ini tidak dapat dibatalkan.</div>
            </div>
        </div>
        <div class="p-3 rounded-4 mb-3" style="background:#f8fafc;">
            <div class="fw-bold" id="deleteTitle">Tugas</div>
            <div class="small text-danger mt-1" id="deleteMeta"></div>
        </div>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <button class="btn btn-danger w-100 py-2 rounded-pill fw-bold mb-2">Hapus permanen</button>
            <button type="button" class="btn btn-light w-100 py-2 rounded-pill" onclick="closeDeleteTugas()">Batal</button>
        </form>
    </div>
</div>

<script>
    function openDeleteTugas(url, title, meta) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('deleteTitle').innerText = title;
        document.getElementById('deleteMeta').innerText = meta;
        document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteTugas() {
        document.getElementById('deleteModal').classList.remove('open');
    }

    (function () {
        const search = document.getElementById('tugasSearch');
        const chips = document.querySelectorAll('#tugasFilters .filter-chip');
        const cards = document.querySelectorAll('[data-card]');
        const empty = document.getElementById('emptyFilter');
        let filter = 'all';

        function apply() {
            const q = (search.value || '').toLowerCase().trim();
            let visible = 0;
            cards.forEach(card => {
                const hay = card.getAttribute('data-search') || '';
                const keys = (card.getAttribute('data-filter') || '').split(/\s+/);
                const okFilter = filter === 'all' || keys.includes(filter);
                const okSearch = !q || hay.includes(q);
                const show = okFilter && okSearch;
                card.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            empty.classList.toggle('d-none', visible > 0 || cards.length === 0);
        }

        chips.forEach(chip => chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            filter = chip.getAttribute('data-filter');
            apply();
        }));
        search.addEventListener('input', apply);
    })();
</script>
@endsection
