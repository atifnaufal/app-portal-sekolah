@extends('layouts.mobile-app')
@section('content')
<style>
    .au-page { padding: 18px 14px 100px; max-width: 640px; margin: 0 auto; }
    .au-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: var(--radius-lg); padding: 24px 20px; color: #fff; margin-bottom: 20px;
        position: relative; overflow: hidden;
    }
    .au-hero::before { content: ''; position: absolute; top: -30px; right: -30px; width: 140px; height: 140px; background: radial-gradient(circle, rgba(99,102,241,0.35) 0%, transparent 70%); }
    .au-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px; position: relative; z-index: 1; }
    .au-stat { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; padding: 12px 6px; text-align: center; }
    .au-stat .n { font-size: 18px; font-weight: 900; color: #fff; line-height: 1; }
    .au-stat .l { font-size: 8px; font-weight: 700; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-top: 4px; }

    .au-search { display: flex; gap: 8px; margin-bottom: 16px; }
    .au-search input { flex: 1; padding: 12px 16px; border-radius: 14px; border: 1px solid var(--line); font-size: 13px; font-weight: 600; background: #fff; }

    .au-user-card {
        background: #fff; border-radius: 18px; padding: 16px; margin-bottom: 12px;
        border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: all 0.2s;
    }
    .au-user-card:active { transform: scale(0.98); }

    .au-user-row { display: flex; align-items: center; gap: 14px; }
    .au-avatar {
        width: 48px; height: 48px; border-radius: 16px; flex-shrink: 0;
        overflow: hidden; position: relative;
    }
    .au-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .au-avatar .initial {
        width: 100%; height: 100%; display: grid; place-items: center;
        font-weight: 800; color: #fff; font-size: 18px;
    }
    .au-online-dot {
        position: absolute; bottom: -1px; right: -1px; width: 14px; height: 14px;
        border-radius: 50%; border: 2.5px solid #fff;
    }
    .au-online-dot.green { background: #22c55e; }
    .au-online-dot.blue { background: #3b82f6; }
    .au-online-dot.red { background: #ef4444; }
    .au-online-dot.gray { background: #94a3b8; }

    .au-user-info { flex: 1; min-width: 0; }
    .au-user-name { font-size: 14px; font-weight: 800; color: var(--navy); }
    .au-user-meta { font-size: 11px; color: #94a3b8; font-weight: 600; margin-top: 2px; }
    .au-last-seen { font-size: 9px; color: #94a3b8; font-weight: 600; margin-top: 1px; }
    .au-last-seen .dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 4px; }
    .au-last-seen .dot.online { background: #22c55e; }
    .au-last-seen .dot.offline { background: #94a3b8; }

    .au-status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.03em;
    }
    .au-status-badge.aktif { background: #dcfce7; color: #166534; }
    .au-status-badge.terdaftar { background: #dbeafe; color: #1e40af; }
    .au-status-badge.nonaktif { background: #fef2f2; color: #991b1b; }
    .au-status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; }
    .au-status-badge.aktif::before { background: #22c55e; }
    .au-status-badge.terdaftar::before { background: #3b82f6; }
    .au-status-badge.nonaktif::before { background: #ef4444; }

    .au-actions { display: flex; gap: 8px; margin-top: 12px; }
    .au-btn {
        flex: 1; padding: 10px; border-radius: 12px; border: none; font-size: 11px;
        font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .au-btn-approve { background: #dcfce7; color: #166534; }
    .au-btn-deactivate { background: #fef2f2; color: #991b1b; }
    .au-btn-edit { background: #f1f5f9; color: #475569; }
    .au-btn-delete { background: #fef2f2; color: #dc2626; }

    .au-empty { text-align: center; padding: 40px 20px; color: #94a3b8; }
</style>

<div class="au-page">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <a href="{{ route('admin.dashboard') }}" style="width:38px;height:38px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--ink);">
            <i class="bi bi-chevron-left"></i>
        </a>
        <div>
            <div style="font-size:18px;font-weight:800;color:var(--navy);">Manajemen User</div>
            <div style="font-size:11px;color:#94a3b8;font-weight:600;">Kelola akun guru & siswa</div>
        </div>
    </div>

    <div class="au-hero">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:rgba(255,255,255,.6);position:relative;z-index:1;">Status Akun</div>
        <div class="au-stat-grid">
            <div class="au-stat"><div class="n">{{ $totalGuru + $totalSiswa }}</div><div class="l">Total</div></div>
            <div class="au-stat"><div class="n" style="color:#4ade80;">{{ $users->where('status_label', 'aktif')->count() }}</div><div class="l">Online</div></div>
            <div class="au-stat"><div class="n" style="color:#fbbf24;">{{ $pendingUsers }}</div><div class="l">Pending</div></div>
        </div>
    </div>

    <form action="{{ route('admin.users') }}" method="GET" class="au-search">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, atau email...">
        <button type="submit" style="width:44px;height:44px;border-radius:14px;background:var(--blue);color:#fff;border:none;font-size:16px;"><i class="bi bi-search"></i></button>
    </form>

    @forelse($users as $u)
        @php
            $avatarBg = match($u->status_label) {
                'aktif' => 'linear-gradient(135deg, #22c55e, #16a34a)',
                'terdaftar' => 'linear-gradient(135deg, #3b82f6, #2563eb)',
                default => 'linear-gradient(135deg, #94a3b8, #64748b)',
            };
        @endphp
        <div class="au-user-card">
            <div class="au-user-row">
                <div class="au-avatar">
                    <div class="initial" style="background: {{ $avatarBg }};">
                        @if($u->foto)
                            <img src="{{ asset('storage/'.$u->foto) }}">
                        @else
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="au-online-dot {{ $u->status_color }}"></div>
                </div>
                <div class="au-user-info">
                    <div class="au-user-name">{{ $u->name }}</div>
                    <div class="au-user-meta">{{ ucfirst($u->role) }} {{ $u->kelas?->nama ? '· '.$u->kelas->nama : '' }}</div>
                    <div class="au-last-seen">
                        @if($u->isOnline())
                            <span class="dot online"></span> Aktif sekarang
                        @else
                            <span class="dot offline"></span> Terakhir aktif {{ $u->last_activity_at ? $u->last_activity_at->diffForHumans() : 'Belum pernah' }}
                        @endif
                    </div>
                </div>
                <span class="au-status-badge {{ $u->status_label }}">{{ ucfirst($u->status_label) }}</span>
            </div>

            <div class="au-actions">
                @if(!$u->aktif)
                    <form action="{{ route('admin.user.toggle', $u) }}" method="POST" style="flex:1;">
                        @csrf @method('PATCH')
                        <button class="au-btn au-btn-approve" type="submit"><i class="bi bi-check-circle"></i> Setujui</button>
                    </form>
                @else
                    <form action="{{ route('admin.user.toggle', $u) }}" method="POST" style="flex:1;">
                        @csrf @method('PATCH')
                        <button class="au-btn au-btn-deactivate" type="submit"><i class="bi bi-slash-circle"></i> Nonaktif</button>
                    </form>
                @endif
                <form action="{{ route('admin.user.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')" style="flex:0 0 auto;">
                    @csrf @method('DELETE')
                    <button class="au-btn au-btn-delete" type="submit" style="padding:10px 14px;"><i class="bi bi-trash3"></i></button>
                </form>
            </div>
        </div>
    @empty
        <div class="au-empty">
            <i class="bi bi-people" style="font-size:40px;display:block;margin-bottom:12px;"></i>
            <div style="font-size:14px;font-weight:700;">Tidak ada user ditemukan</div>
        </div>
    @endforelse
</div>
@endsection
