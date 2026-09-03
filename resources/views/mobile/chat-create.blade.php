@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .cg-app { min-height: 100vh; background: #f8fafc; display: flex; flex-direction: column; }
    .cg-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.85); backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--line);
        padding: 10px 16px; padding-top: calc(10px + env(safe-area-inset-top));
        display: flex; align-items: center; gap: 12px;
    }
    .cg-back {
        width: 38px; height: 38px; border-radius: 12px; background: var(--surface);
        display: flex; align-items: center; justify-content: center;
        color: var(--ink); text-decoration: none;
    }
    .cg-body { padding: 74px 20px calc(120px + env(safe-area-inset-bottom)); }
    .cg-avatar-lg {
        width: 88px; height: 88px; border-radius: 28px; margin: 6px auto 20px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 34px; font-weight: 900; box-shadow: 0 16px 32px rgba(99,102,241,.35);
    }
    .cg-field {
        background: #fff; border-radius: 18px; border: 1px solid var(--line);
        padding: 14px 16px; display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
    }
    .cg-field input { flex: 1; border: 0; outline: 0; font-size: 15px; font-weight: 600; color: var(--ink); background: transparent; }
    .cg-member {
        display: flex; align-items: center; gap: 14px; padding: 12px 6px;
        border-bottom: 1px solid #f1f5f9; cursor: pointer;
    }
    .cg-member:active { opacity: 0.7; }
    .cg-member img { width: 46px; height: 46px; border-radius: 15px; object-fit: cover; background: #f1f5f9; }
    .cg-check {
        width: 26px; height: 26px; border-radius: 50%; border: 2px solid #cbd5e1;
        display: flex; align-items: center; justify-content: center; color: #fff;
        transition: all 0.2s; flex-shrink: 0;
    }
    .cg-check.on { background: var(--blue); border-color: var(--blue); }
    .cg-create {
        position: fixed; bottom: calc(20px + env(safe-area-inset-bottom)); left: 20px; right: 20px; z-index: 1000;
        background: linear-gradient(135deg, var(--blue), #2563eb); color: #fff; border: 0;
        border-radius: 18px; padding: 16px; font-weight: 900; font-size: 15px;
        box-shadow: 0 16px 32px rgba(37,99,235,.35);
    }
    .cg-create:disabled { opacity: 0.5; }
</style>

<div class="cg-app">
    <div class="cg-header">
        <a href="{{ route('chat.index') }}" class="cg-back"><i class="bi bi-chevron-left"></i></a>
        <div style="font-weight: 800; font-size: 16px; color: var(--ink);">Buat Grup Baru</div>
    </div>

    <div class="cg-body">
        <div class="cg-avatar-lg"><i class="bi bi-people-fill"></i></div>

        <div class="cg-field">
            <i class="bi bi-chat-dots-fill" style="color: var(--blue); font-size: 18px;"></i>
            <input type="text" id="groupName" placeholder="Nama grup" maxlength="100" autocomplete="off">
        </div>

        <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing:.08em; color:#94a3b8; margin: 18px 6px 8px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-person-plus-fill"></i> Pilih anggota
            <span id="selCount" style="margin-left:auto; color:var(--blue); font-size:12px;">0 dipilih</span>
        </div>

        <div id="memberList">
            @forelse($candidates as $c)
                <div class="cg-member" data-id="{{ $c->id }}" onclick="toggleSel(this)">
                    <img src="{{ $c->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($c->name).'&background=random' }}">
                    <div style="flex:1; min-width:0;">
                        <div style="font-weight:700; font-size:14px; color:var(--navy);">{{ $c->name }}</div>
                        <div style="font-size:11px; color:#94a3b8; font-weight:600;">{{ ucfirst($c->role) }}{{ $c->kelas ? ' · '.$c->kelas->nama : '' }}</div>
                    </div>
                    <div class="cg-check"><i class="bi bi-check-lg"></i></div>
                </div>
            @empty
                <div style="text-align:center; padding:30px; color:#94a3b8; font-size:13px;">Tidak ada calon anggota ditemukan.</div>
            @endforelse
        </div>
    </div>

    <button class="cg-create" id="btnCreate" onclick="createGroup()">Buat Grup</button>
</div>

<script>
    let selected = [];
    const selCount = document.getElementById('selCount');
    const btnCreate = document.getElementById('btnCreate');
    const groupName = document.getElementById('groupName');

    function toggleSel(el) {
        const id = parseInt(el.dataset.id);
        const idx = selected.indexOf(id);
        if (idx >= 0) { selected.splice(idx, 1); el.classList.remove('on-checked'); el.querySelector('.cg-check').classList.remove('on'); }
        else { selected.push(id); el.querySelector('.cg-check').classList.add('on'); }
        selCount.textContent = selected.length + ' dipilih';
    }

    function createGroup() {
        const name = groupName.value.trim();
        if (!name) { groupName.focus(); groupName.style.borderColor = '#ef4444'; setTimeout(()=>groupName.style.borderColor='',1500); return; }
        btnCreate.disabled = true; btnCreate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membuat...';
        const fd = new FormData();
        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
        fd.append('name', name);
        selected.forEach(id => fd.append('member_ids[]', id));
        fetch("{{ route('chat.storeGroup') }}", { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body: fd })
            .then(r => r.json())
            .then(d => { if(d.ok){ window.location.href = "/chat/" + d.id; } else { alert('Gagal membuat grup'); btnCreate.disabled=false; btnCreate.textContent='Buat Grup'; } })
            .catch(() => { alert('Gagal membuat grup'); btnCreate.disabled=false; btnCreate.textContent='Buat Grup'; });
    }
</script>
@endsection
