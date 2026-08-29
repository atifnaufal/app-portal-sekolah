@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isGuru = $user->role === 'guru' && (int) $materi->user_id === (int) $user->id;
    $videoId = null;
    if ($materi->video_url) {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/', $materi->video_url, $m)) {
            $videoId = $m[1];
        }
    }
@endphp

<style>
    .lms-topbar {
        position: sticky; top: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line);
        padding: 12px 16px; display: flex; align-items: center; gap: 10px;
    }
    .lms-body { max-width: 640px; margin: 0 auto; padding: 16px 16px 60px; }

    .lms-card {
        background: var(--surface-card); border: 1px solid var(--line);
        border-radius: var(--radius-md); padding: 18px;
        margin-bottom: 14px; box-shadow: var(--shadow-card);
    }
    .lms-file {
        display: flex; align-items: center; gap: 12px; padding: 14px;
        background: var(--surface); border-radius: var(--radius-sm); border: 1px solid var(--line);
        text-decoration: none; color: var(--ink); transition: all 0.2s;
    }
    .lms-file:active { transform: scale(0.98); filter: brightness(0.97); }
    .file-ico {
        width: 42px; height: 42px; border-radius: var(--radius-sm); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }

    .icon-btn {
        width: 40px; height: 40px; border-radius: var(--radius-sm); border: 1px solid var(--line-strong);
        display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;
    }

    .sheet {
        position: fixed; inset: 0; z-index: 2000; display: none;
        align-items: flex-end; justify-content: center;
        background: rgba(0,0,0,0.4);
    }
    .sheet-card {
        width: 100%; max-width: 640px; background: var(--surface-card);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 24px 20px;
    }

    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="lms-topbar">
    <a href="{{ route('mapel.show', $mapel) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="padding:0;width:36px;height:36px;">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div style="font-weight:800;font-size:16px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Illuminate\Support\Str::limit($materi->judul, 26) }}</div>
    @if($isGuru)
        <button type="button" class="icon-btn" onclick="document.getElementById('mtDel').style.display='flex'" style="background:#fff5f6;border-color:#fecdd3;color:#d94b61;">
            <i class="bi bi-trash3"></i>
        </button>
        <a href="{{ route('materi.edit', [$mapel, $materi]) }}" class="icon-btn" style="background:#eef4ff;border-color:#bfdbfe;color:#246bfe;">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endif
</div>

<div class="lms-body">
    {{-- Hero --}}
    <div class="lms-card" style="background:var(--grad-hero);color:#fff;border:none;padding:22px 20px;">
        <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap;">
            <span class="hero-chip" style="background:rgba(255,255,255,0.08);padding:4px 10px;border-radius:8px;font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;">
                <i class="bi bi-journal-bookmark"></i> {{ $mapel->nama }}
            </span>
            <span class="hero-chip" style="background:rgba(255,255,255,0.08);padding:4px 10px;border-radius:8px;font-size:9px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;">
                <i class="bi bi-calendar3"></i> {{ $materi->created_at->translatedFormat('d M Y') }}
            </span>
        </div>
        <div style="font-size:20px;font-weight:800;line-height:1.25;letter-spacing:-0.02em;">{{ $materi->judul }}</div>
        <div style="font-size:12px;opacity:0.6;margin-top:10px;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-person-fill"></i> {{ $materi->guru->name }}
        </div>
    </div>

    {{-- Deskripsi --}}
    @if($materi->deskripsi)
        <div class="lms-card">
            <div style="font-size:13px;font-weight:700;margin-bottom:8px;"><i class="bi bi-card-text" style="color:var(--blue);"></i> Deskripsi</div>
            <div style="font-size:13.5px;color:var(--mist);line-height:1.7;white-space:pre-line;">{{ $materi->deskripsi }}</div>
        </div>
    @endif

    {{-- Video --}}
    @if($videoId)
        <div class="lms-card" style="padding:0;overflow:hidden;border:none;">
            <div style="position:relative;width:100%;padding-top:56.25%;background:#000;">
                <iframe src="https://www.youtube.com/embed/{{ $videoId }}" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allowfullscreen loading="lazy"></iframe>
            </div>
        </div>
    @elseif($materi->video_url)
        <div class="lms-card">
            <a href="{{ $materi->video_url }}" target="_blank" class="lms-file">
                <div class="file-ico" style="background:#fef2f2;color:#dc2626;">
                    <i class="bi bi-play-circle-fill"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:700;">Tonton Video</div>
                    <div style="font-size:11px;color:var(--faint);">Buka di browser</div>
                </div>
                <i class="bi bi-box-arrow-up-right" style="color:var(--faint);"></i>
            </a>
        </div>
    @endif

    {{-- File --}}
    @if($materi->file_materi)
        <div class="lms-card">
            <div style="font-size:13px;font-weight:700;margin-bottom:10px;"><i class="bi bi-paperclip" style="color:var(--blue);"></i> Lampiran</div>
            <a href="{{ asset('storage/'.$materi->file_materi) }}" target="_blank" class="lms-file">
                <div class="file-ico" style="background:linear-gradient(135deg,var(--surface),#dbeafe);color:var(--blue);">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $materi->file_nama ?: 'Download Materi' }}</div>
                    <div style="font-size:11px;color:var(--faint);">Tap untuk unduh / buka</div>
                </div>
                <i class="bi bi-download" style="color:var(--blue);"></i>
            </a>
        </div>
    @endif

    @if(!$materi->deskripsi && !$materi->file_materi && !$materi->video_url)
        <div class="pui-empty" style="padding:30px;">
            <i class="bi bi-journal-x ico"></i>
            <h4>Materi ini belum memiliki lampiran.</h4>
        </div>
    @endif
</div>

@if($isGuru)
<div id="mtDel" onclick="if(event.target===this)this.style.display='none'" style="position:fixed;inset:0;z-index:2000;display:none;align-items:flex-end;justify-content:center;background:rgba(0,0,0,0.4);">
    <div class="sheet-card">
        <div style="font-size:16px;font-weight:800;margin-bottom:4px;">Hapus materi?</div>
        <div style="font-size:12px;color:var(--faint);margin-bottom:16px;">"{{ $materi->judul }}" akan dihapus permanen.</div>
        <form method="POST" action="{{ route('materi.destroy', [$mapel, $materi]) }}">
            @csrf @method('DELETE')
            <button type="submit" class="pui-btn pui-btn-danger pui-btn-block mb-2">Hapus Permanen</button>
            <button type="button" class="pui-btn pui-btn-ghost pui-btn-block" onclick="document.getElementById('mtDel').style.display='none'">Batal</button>
        </form>
    </div>
</div>
@endif
@endsection
