@extends('layouts.mobile-app')

@section('content')
<style>
    .pf-page { padding: 0 16px 120px; max-width: 640px; margin: 0 auto; }

    .pf-hero {
        background: linear-gradient(135deg, #1e293b, #246bfe);
        border-radius: 28px; padding: 28px 20px; margin-bottom: 16px;
        color: #fff; text-align: center; position: relative; overflow: hidden;
    }
    .pf-hero::before {
        content:''; position:absolute; top:-30px; right:-30px;
        width:100px; height:100px; border-radius:50%; background:rgba(255,255,255,0.06);
    }

    .pf-avatar {
        width: 90px; height: 90px; border-radius: 28px; margin: 0 auto 12px;
        overflow: hidden; background: transparent;
        display: flex; align-items: center; justify-content: center;
        position: relative;
        filter: drop-shadow(0 5px 14px rgba(0,0,0,0.18));
    }
    .pf-avatar img {
        width: 100%; height: 100%; object-fit: cover;
    }
    .pf-avatar .initial {
        font-size: 32px; font-weight: 800; color: rgba(255,255,255,0.8);
    }

    .pf-info-card {
        background: #fff; border-radius: 20px; padding: 18px;
        margin-bottom: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }
    .pf-info-row {
        display: flex; align-items: center; gap: 12px; padding: 10px 0;
    }
    .pf-info-row + .pf-info-row { border-top: 1px solid #f1f5f9; }
    .pf-info-icon {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 15px;
    }
    .pf-info-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
    .pf-info-value { font-size: 14px; font-weight: 700; color: #1e293b; }

    .pf-toast {
        position: fixed; top: 16px; left: 16px; right: 16px; z-index: 9999;
        background: #fff; border-radius: 14px; padding: 12px 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: none;
        max-width: 640px; margin: 0 auto;
    }
    .pf-toast.show { display: flex; animation: fadeDown 0.3s ease; }
    @keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
</style>

<div id="pfToast" class="pf-toast">
    <i id="pfToastIcon" class="bi bi-check-circle-fill" style="color:#16a34a;font-size:18px;"></i>
    <span id="pfToastMsg" style="flex:1;font-size:13px;font-weight:600;margin-left:8px;"></span>
</div>

<div class="pf-page" style="padding-top:16px;">
    {{-- Hero --}}
    <div class="pf-hero">
        <div class="pf-avatar" id="avatarDisplay">
            @if($user->foto)
                <img src="{{ asset('storage/'.$user->foto) }}" id="avatarImg" style="object-position: {{ $user->foto_posisi_x ?? 50 }}% {{ $user->foto_posisi_y ?? 50 }}%;">
            @else
                <span class="initial" id="avatarInitial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <img id="avatarImg" style="display:none;">
            @endif
        </div>
        <div style="font-size:22px;font-weight:800;" id="nameDisplay">{{ $user->name }}</div>
        <div style="font-size:11px;opacity:0.7;margin-top:4px;">
            <span style="background:rgba(255,255,255,0.15);padding:3px 10px;border-radius:6px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;">{{ $user->role }}</span>
            @if($user->kelas)
                <span style="margin-left:4px;">{{ $user->kelas->nama }}</span>
            @endif
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="pf-info-card">
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#eef4ff;color:#246bfe;"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="pf-info-label">Nama Lengkap</div>
                <div class="pf-info-value" id="infoName">{{ $user->name }}</div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-envelope-at"></i></div>
            <div>
                <div class="pf-info-label">Email</div>
                <div class="pf-info-value">
                    {{ $user->email }}
                    @if($user->hasVerifiedEmail())
                        <i class="bi bi-patch-check-fill" style="color:#16a34a;font-size:12px;"></i>
                    @endif
                </div>
            </div>
        </div>
        <div class="pf-info-row">
            <div class="pf-info-icon" style="background:#ede9fe;color:#7c3aed;"><i class="bi bi-mortarboard"></i></div>
            <div>
                <div class="pf-info-label">Kelas / Jabatan</div>
                <div class="pf-info-value">{{ $user->kelas?->nama ?? 'Staf Sekolah' }}</div>
            </div>
        </div>
    </div>

    @if($user->role === 'siswa' && !$user->hasVerifiedEmail())
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:16px;padding:14px;margin-bottom:12px;">
            <div style="display:flex;align-items:start;gap:10px;">
                <i class="bi bi-exclamation-triangle-fill" style="color:#b45309;font-size:18px;flex-shrink:0;margin-top:2px;"></i>
                <div>
                    <div style="font-size:13px;font-weight:700;color:#92400e;">Email Belum Diverifikasi</div>
                    <div style="font-size:11px;color:#b45309;margin-top:2px;">Beberapa fitur mungkin dibatasi.</div>
                    <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                        @csrf
                        <button style="padding:6px 14px;border-radius:8px;background:#f59e0b;color:#fff;font-size:11px;font-weight:700;border:none;cursor:pointer;">Kirim Ulang Link</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <a href="{{ route('profile.edit') }}" style="display:block;width:100%;padding:14px;border-radius:16px;background:#246bfe;color:#fff;font-weight:700;font-size:14px;text-decoration:none;text-align:center;margin-bottom:10px;">
        <i class="bi bi-pencil-square"></i> Edit Profil
    </a>

    {{-- Logout --}}
    <div style="background:#fff5f5;border:1px solid #fee2e2;border-radius:16px;padding:14px;display:flex;align-items:center;justify-content:space-between;margin-top:20px;">
        <div>
            <div style="font-size:13px;font-weight:700;color:#dc2626;">Keluar Akun?</div>
            <div style="font-size:11px;color:#94a3b8;">Hentikan sesi aktif.</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button style="padding:8px 20px;border-radius:10px;background:#dc2626;color:#fff;font-weight:700;font-size:12px;border:none;cursor:pointer;">KELUAR</button>
        </form>
    </div>
</div>

<script>
function showToast(msg, type) {
    var t = document.getElementById('pfToast');
    var icon = document.getElementById('pfToastIcon');
    var isError = type === 'error';

    document.getElementById('pfToastMsg').textContent = msg;
    icon.className = 'bi ' + (isError ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill');
    icon.style.color = isError ? '#dc2626' : '#16a34a';
    t.style.borderLeft = '4px solid ' + (isError ? '#dc2626' : '#16a34a');

    t.classList.add('show');
    setTimeout(function() { t.classList.remove('show'); }, 5000);
}
@if(session('success'))
showToast(@json(session('success')), 'success');
@endif
@if(session('error'))
showToast(@json(session('error')), 'error');
@endif
</script>
@endsection
