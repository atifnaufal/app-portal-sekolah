@php $hideNav = true; $title = 'Fitur Terkunci'; $msg = $msg ?? session('locked_msg') ?? 'Fitur ini sedang dinonaktifkan oleh admin.'; @endphp
@extends('layouts.mobile-app')
@section('content')
<style>
    .lock-wrap { max-width: 640px; margin: 0 auto; padding: 48px 24px 40px; text-align: center; }
    .lock-orb {
        width: 120px; height: 120px; border-radius: 36px; margin: 0 auto 24px;
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #4f46e5 100%);
        display: grid; place-items: center; color: #fff;
        box-shadow: 0 20px 50px rgba(79,70,229,.35);
        animation: floaty 3s ease-in-out infinite;
    }
    @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    .lock-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;
        font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
        padding: 7px 14px; border-radius: 999px; margin-bottom: 14px;
    }
    .lock-title { font-size: 24px; font-weight: 900; letter-spacing: -.02em; }
    .lock-msg { font-size: 14px; color: var(--mist); line-height: 1.7; margin-top: 8px; }
    .lock-card {
        background: #fff; border: 1px solid var(--line); border-radius: var(--radius-md);
        box-shadow: var(--shadow-card); padding: 16px; margin-top: 22px; text-align: left;
    }
    .lock-step { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; }
    .lock-step + .lock-step { border-top: 1px solid var(--line); }
    .lock-num {
        width: 28px; height: 28px; border-radius: 10px; flex-shrink: 0;
        background: #eef2ff; color: #4f46e5; font-weight: 800; font-size: 13px;
        display: grid; place-items: center;
    }
</style>
<div class="lock-wrap stagger">
    <div class="lock-orb"><i class="bi bi-lock-fill" style="font-size:48px;"></i></div>
    <div class="lock-badge"><i class="bi bi-shield-lock-fill"></i> Fitur Terkunci</div>
    <h1 class="lock-title">Belum Bisa Diakses</h1>
    <p class="lock-msg">{{ $msg }}</p>
    <div class="lock-card">
        <div class="lock-step"><div class="lock-num">1</div><div style="font-size:13px;"><b>Hubungi admin sekolah</b><br><span style="color:var(--mist);">Minta agar fitur/pendaftaran dibuka untuk sekolahmu.</span></div></div>
        <div class="lock-step"><div class="lock-num">2</div><div style="font-size:13px;"><b>Tunggu persetujuan akun</b><br><span style="color:var(--mist);">Akun baru harus disetujui admin sebelum bisa dipakai.</span></div></div>
        <div class="lock-step"><div class="lock-num">3</div><div style="font-size:13px;"><b>Coba lagi nanti</b><br><span style="color:var(--mist);">Kembali ke halaman ini setelah ada kabar dari admin.</span></div></div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <a href="{{ url()->previous() }}" class="pui-btn pui-btn-ghost flex-fill"><i class="bi bi-arrow-left"></i> Kembali</a>
        <a href="{{ route('dashboard') }}" class="pui-btn pui-btn-primary flex-fill"><i class="bi bi-house-door"></i> Beranda</a>
    </div>
</div>
@endsection
