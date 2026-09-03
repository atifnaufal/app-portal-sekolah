{{-- AI Analyst & Terminal — khusus Super Admin. Terminal hanya allowlist + diaudit. --}}
@extends('layouts.app', ['title' => 'AI Analyst & Terminal'])
@section('content')
<style>
    .cp-page-header {
        background: linear-gradient(135deg, var(--navy) 0%, #1e293b 100%);
        border-radius: 24px; padding: 32px 36px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 24px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
    }
    .cp-page-header::after {
        content: ''; position: absolute; top: -70px; right: -70px;
        width: 220px; height: 220px; border-radius: 50%;
        background: radial-gradient(circle, rgba(36,107,254,0.18) 0%, transparent 70%);
    }
    .cp-page-title { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; position: relative; z-index: 1; }
    .cp-page-sub { font-size: 13px; color: #94a3b8; position: relative; z-index: 1; }
    .ins-card { border-radius: 20px; border: 1px solid var(--border); background: #fff; box-shadow: var(--shadow); overflow: hidden; }
    .ins-card-head { padding: 18px 24px; border-bottom: 1px solid var(--border); }
    .ins-card-title { font-size: 15.5px; font-weight: 800; color: var(--navy); margin: 0; }
    .metric { background: #f8fafc; border-radius: 14px; padding: 14px; text-align: center; }
    .metric .num { font-size: 24px; font-weight: 800; color: var(--navy); }
    .metric .lb { font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    .insight { display: flex; gap: 12px; padding: 12px 0; align-items: flex-start; }
    .insight + .insight { border-top: 1px solid #f1f5f9; }
    .tone-dot { width: 12px; height: 12px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
    .tone-success { background: #22c55e; } .tone-warning { background: #f59e0b; }
    .tone-danger { background: #ef4444; } .tone-info { background: #3b82f6; }
    .term-box { background: #0f172a; color: #e2e8f0; border-radius: 14px; padding: 18px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12.5px; white-space: pre-wrap; max-height: 380px; overflow-y: auto; line-height: 1.6; }
    .term-ok { color: #4ade80; font-weight: 800; } .term-fail { color: #f87171; font-weight: 800; }
    .nav-premium { border-bottom: 2px solid #f1f5f9; gap: 8px; }
    .nav-premium .nav-link { border: none; color: #64748b; font-weight: 700; font-size: 14px; padding: 12px 20px; border-radius: 12px 12px 0 0; }
    .nav-premium .nav-link.active { color: var(--blue); position: relative; }
    .nav-premium .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 3px; background: var(--blue); border-radius: 99px; }
    @media (max-width: 768px) {
        .cp-page-header { padding: 24px; border-radius: 20px; }
        .cp-page-title { font-size: 22px; }
    }
</style>

<div class="cp-shell">
@include('admin.partials.sidebar')
<div class="cp-main">

<div class="cp-page-header">
    <div class="position-relative" style="z-index:1;">
        <div class="small fw-bold" style="letter-spacing:.1em;color:#94a3b8;">SUPER ADMIN ONLY</div>
        <h1 class="cp-page-title">AI Analyst & Terminal</h1>
        <p class="cp-page-sub mb-0">Insight kesehatan sistem + terminal diagnostik allowlist (diaudit). Tanpa shell mentah.</p>
    </div>
</div>

<div class="ins-card mb-4">
    <div class="ins-card-head">
        <ul class="nav nav-tabs nav-premium border-0" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-analyst" type="button"><i class="bi bi-cpu me-1"></i>Analyst</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-terminal" type="button"><i class="bi bi-terminal me-1"></i>Terminal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-github" type="button"><i class="bi bi-github me-1"></i>GitHub</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-server" type="button"><i class="bi bi-hdd-network me-1"></i>Server</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-keys" type="button"><i class="bi bi-key me-1"></i>Integrasi</button></li>
        </ul>
    </div>
    <div class="tab-content p-4">
        {{-- ANALYST --}}
        <div class="tab-pane fade show active" id="tab-analyst">
            <div class="row g-3 mb-4">
                @foreach([['Sekolah', $metrics['sekolah']], ['Guru', $metrics['guru']], ['Siswa', $metrics['siswa']], ['Pending', $metrics['pending_approval']], ['Login Hari Ini', $metrics['login_hari_ini']], ['Error Hari Ini', $metrics['error_log_hari_ini']]] as $mt)
                <div class="col-6 col-md-4 col-xl-2"><div class="metric"><div class="num">{{ number_format($mt[1]) }}</div><div class="lb">{{ $mt[0] }}</div></div></div>
                @endforeach
            </div>
            <h6 class="fw-bold mb-2"><i class="bi bi-activity me-1 text-primary"></i>Insight Otomatis (tanpa AI key)</h6>
            <div class="mb-4">
                @foreach($insights as $ins)
                <div class="insight"><span class="tone-dot tone-{{ $ins['tone'] }}"></span>
                    <div><div class="fw-bold" style="font-size:13.5px;">{{ $ins['title'] }}</div><div class="text-muted small">{{ $ins['desc'] }}</div></div>
                </div>
                @endforeach
            </div>
            @if($aiResult)
            <div class="alert border-0 shadow-sm" style="border-radius:16px;background:#eef2ff;white-space:pre-wrap;font-size:13px;">{{ $aiResult }}</div>
            @endif
            @if($hasGeminiKey)
            <form method="POST" action="{{ route('admin.insights.analyze') }}">@csrf
                <button class="btn btn-primary fw-bold" style="border-radius:12px;"><i class="bi bi-stars me-1"></i> Analisis dengan AI (Gemini)</button>
            </form>
            @else
            <div class="alert alert-light border small mb-0" style="border-radius:14px;">Ingin analisis naratif AI? Isi <b>Gemini API key</b> di tab Integrasi (gratis di Google AI Studio).</div>
            @endif
            @if(!empty($metrics['log_tail']))
            <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-file-text me-1 text-primary"></i>5 Baris Log Terakhir</h6>
            <div class="term-box">@foreach($metrics['log_tail'] as $line){{ mb_substr($line, 0, 220) }}
@endforeach</div>
            @endif
        </div>

        {{-- TERMINAL --}}
        <div class="tab-pane fade" id="tab-terminal">
            <div class="alert alert-warning border-0 small" style="border-radius:14px;"><i class="bi bi-shield-lock me-1"></i> Hanya 7 perintah diagnostik allowlist. Setiap eksekusi dicatat di Riwayat (tipe terminal).</div>
            <form method="POST" action="{{ route('admin.insights.terminal') }}" class="d-flex gap-2 flex-wrap mb-3">@csrf
                <select name="command" class="form-select" style="border-radius:12px;max-width:320px;" required>
                    @foreach($commands as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                </select>
                <button class="btn btn-dark fw-bold" style="border-radius:12px;"><i class="bi bi-play-fill me-1"></i> Jalankan</button>
            </form>
            @if($terminalResult)
            <div class="mb-2 small fw-bold">{{ $terminalResult['label'] }} — <span class="{{ $terminalResult['ok'] ? 'term-ok' : 'term-fail' }}">{{ $terminalResult['ok'] ? 'OK' : 'GAGAL' }}</span></div>
            <div class="term-box">{{ $terminalResult['output'] }}</div>
            @else
            <div class="term-box text-muted">Pilih perintah lalu Jalankan. Output muncul di sini.</div>
            @endif
        </div>

        {{-- GITHUB --}}
        <div class="tab-pane fade" id="tab-github">
            <p class="small text-muted">Sinkronisasi status repo: via <b>GitHub API</b> bila token diisi, atau <b>git lokal</b> sebagai fallback.</p>
            <form method="POST" action="{{ route('admin.insights.github') }}" class="mb-3">@csrf
                <button class="btn btn-dark fw-bold" style="border-radius:12px;"><i class="bi bi-arrow-repeat me-1"></i> Cek Status Repo</button>
            </form>
            @if($githubResult)
            <div class="small fw-bold mb-2">{{ $githubResult['mode'] }} — {{ $githubResult['repo'] }}</div>
            @if(!empty($githubResult['error']))
            <div class="alert alert-danger small" style="border-radius:12px;">{{ $githubResult['error'] }}</div>
            @endif
            @if(!empty($githubResult['commits']))
            <div class="list-group" style="border-radius:14px;overflow:hidden;">
                @foreach($githubResult['commits'] as $c)
                <div class="list-group-item"><span class="badge bg-light text-dark border me-2" style="font-family:monospace;">{{ $c['sha'] }}</span><b style="font-size:13px;">{{ mb_substr($c['msg'], 0, 80) }}</b><div class="small text-muted">{{ $c['by'] }} • {{ $c['at'] }}</div></div>
                @endforeach
            </div>
            @endif
            @if(!empty($githubResult['local']))
            <div class="term-box">{{ $githubResult['local'] }}</div>
            @endif
            @endif
        </div>

        {{-- SERVER --}}
        <div class="tab-pane fade" id="tab-server">
            <div class="list-group" style="border-radius:14px;overflow:hidden;">
                @foreach($health as $h)
                <div class="list-group-item d-flex align-items-center gap-3">
                    <span class="tone-dot tone-{{ $h[2] ? 'success' : 'danger' }}"></span>
                    <b style="font-size:13.5px;">{{ $h[0] }}</b>
                    <span class="small text-muted ms-auto text-end">{{ $h[1] }}</span>
                </div>
                @endforeach
            </div>
            <p class="small text-muted mt-3 mb-0">Diperbarui {{ now()->translatedFormat('d M Y, H:i') }}. Detail deploy/log container tetap lewat dashboard Railway.</p>
        </div>

        {{-- INTEGRASI --}}
        <div class="tab-pane fade" id="tab-keys">
            <form method="POST" action="{{ route('admin.insights.keys') }}" class="row g-3" style="max-width:640px;">@csrf
                <div class="col-12">
                    <label class="small fw-bold">Gemini API Key <span class="text-muted">(opsional — untuk Analisis AI)</span></label>
                    <input name="gemini_api_key" type="password" class="form-control" style="border-radius:12px;" placeholder="AIza..." autocomplete="off" value="{{ $hasGeminiKey ? '•••••••• (tersimpan)' : '' }}">
                    <div class="small text-muted mt-1">Dapatkan gratis di Google AI Studio. Key tersimpan di tabel settings server.</div>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">GitHub Token <span class="text-muted">(opsional)</span></label>
                    <input name="github_token" type="password" class="form-control" style="border-radius:12px;" autocomplete="off" value="{{ $hasGithubToken ? '•••••••• (tersimpan)' : '' }}">
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">GitHub Repo</label>
                    <input name="github_repo" class="form-control" style="border-radius:12px;" value="{{ $githubRepo }}" placeholder="user/repo">
                </div>
                <div class="col-12"><button class="btn btn-primary fw-bold" style="border-radius:12px;">Simpan</button></div>
            </form>
            <hr>
            <div class="small text-muted" style="max-width:640px;line-height:1.7;">
                <b>Catatan jujur:</b> Firebase project ini hanya dipakai untuk Storage (tidak ada AI di sana).
                Railway tidak menyediakan API bawaan ke aplikasi — monitoring Railway tetap lewat dashboard Railway.
                Terminal di sini hanya allowlist diagnostik, <b>bukan shell bebas</b>, demi keamanan server produksi.
            </div>
        </div>
    </div>
</div>

</div>{{-- /.cp-main --}}
</div>{{-- /.cp-shell --}}
@endsection
