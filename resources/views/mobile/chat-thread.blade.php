@php
    $hideNav = true;
    $isPrivate = $group->type === 'private';
    $isCustom = $group->type === 'custom';
    $other = $group->other_user ?? null;
    $hdrAvatar = $isPrivate && $other ? $other->avatar_url : ($group->avatar ? asset('storage/'.$group->avatar) : null);
@endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-thread { display: flex; flex-direction: column; height: 100vh; height: 100dvh; background: #f0f2f5; }

    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 2000;
        background: #fff; border-bottom: 1px solid #f1f5f9;
        padding: 10px 16px; padding-top: calc(10px + env(safe-area-inset-top));
        display: flex; align-items: center; gap: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .back-btn {
        width: 36px; height: 36px; border-radius: 12px; background: #f8fafc;
        display: flex; align-items: center; justify-content: center;
        color: var(--navy); text-decoration: none; transition: all 0.2s; border: 0; cursor: pointer;
    }
    .back-btn:active { transform: scale(0.9); background: #f1f5f9; }

    .hdr-avatar {
        width: 42px; height: 42px; border-radius: 14px; overflow: hidden;
        background: #f1f5f9; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800;
    }
    .hdr-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .hdr-info { flex: 1; min-width: 0; }
    .hdr-title { font-size: 15px; font-weight: 800; color: var(--navy); margin-bottom: 0px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hdr-status { font-size: 11px; color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 4px; }
    .hdr-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }
    .hdr-more { width: 36px; height: 36px; border-radius: 12px; background: #f8fafc; border: 0; color: var(--navy); display: flex; align-items: center; justify-content: center; font-size: 18px; }

    .chat-messages {
        flex: 1; padding: 76px 16px calc(72px + env(safe-area-inset-bottom));
        overflow-y: auto; display: flex; flex-direction: column;
        scroll-behavior: smooth;
    }

    .date-separator {
        text-align: center; margin: 16px 0 8px; position: relative;
    }
    .date-separator span {
        background: #e2e8f0; color: #64748b; font-size: 11px; font-weight: 700;
        padding: 4px 12px; border-radius: 10px; display: inline-block;
    }

    .msg-group { margin-bottom: 4px; display: flex; flex-direction: column; }
    .msg-item { max-width: 80%; margin-bottom: 4px; position: relative; }
    .msg-item.mine { align-self: flex-end; }
    .msg-item.other { align-self: flex-start; }

    .bubble {
        padding: 10px 14px; border-radius: 20px; font-size: 14px; line-height: 1.5;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04); position: relative; cursor: pointer;
        transition: background 0.15s;
    }
    .bubble:active { filter: brightness(0.95); }
    .bubble.deleted-bubble { font-style: italic; opacity: 0.6; cursor: default; background: #f1f5f9 !important; color: #94a3b8 !important; border: 0 !important; }
    .mine .bubble { background: var(--blue); color: #fff; border-bottom-right-radius: 4px; }
    .other .bubble { background: #fff; color: var(--navy); border-bottom-left-radius: 4px; border: 1px solid #f1f5f9; }

    .msg-info { font-size: 10px; margin-top: 4px; display: flex; align-items: center; gap: 4px; color: #94a3b8; font-weight: 600; }
    .mine .msg-info { justify-content: flex-end; color: rgba(255,255,255,0.7); }
    .edit-tag { font-size: 9px; font-style: italic; opacity: 0.85; }
    .sender-name { font-size: 11px; font-weight: 800; margin-bottom: 4px; color: #6366f1; }

    /* Bottom sheet */
    .sheet-overlay {
        position: fixed; inset: 0; z-index: 5000; background: rgba(15,23,42,.4);
        opacity: 0; pointer-events: none; transition: opacity .25s;
        display: flex; align-items: flex-end; justify-content: center;
    }
    .sheet-overlay.open { opacity: 1; pointer-events: auto; }
    .sheet {
        background: #fff; width: 100%; max-width: 480px; border-radius: 24px 24px 0 0;
        padding: 12px 18px calc(24px + env(safe-area-inset-bottom));
        transform: translateY(100%); transition: transform .3s cubic-bezier(.32,.72,.32,1.16);
        max-height: 78vh; overflow-y: auto;
    }
    .sheet-overlay.open .sheet { transform: translateY(0); }
    .sheet-handle { width: 40px; height: 4px; border-radius: 99px; background: #e2e8f0; margin: 6px auto 14px; }
    .sheet-title { font-weight: 800; font-size: 16px; color: var(--navy); margin-bottom: 14px; }
    .sheet-item {
        display: flex; align-items: center; gap: 16px; padding: 14px 10px;
        border-radius: 16px; cursor: pointer; transition: background .15s; width: 100%;
        border: 0; background: transparent; font-size: 14px; font-weight: 700; color: var(--navy); text-align: left;
    }
    .sheet-item:active { background: #f8fafc; }
    .sheet-item .si-icon {
        width: 40px; height: 40px; border-radius: 13px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
    }
    .si-divider { height: 1px; background: #f1f5f9; margin: 6px 0; }

    /* Context menu */
    .msg-context-menu {
        position: fixed; z-index: 6000; background: #fff; border-radius: 18px;
        box-shadow: 0 16px 40px rgba(0,0,0,.18); overflow: hidden;
        min-width: 190px; animation: popIn .2s ease;
    }
    @keyframes popIn { from { opacity: 0; transform: scale(.92); } to { opacity: 1; transform: scale(1); } }
    .ctx-item {
        display: flex; align-items: center; gap: 12px; padding: 13px 16px;
        font-size: 14px; font-weight: 700; color: var(--navy); cursor: pointer; border: 0; background: #fff; width: 100%; text-align: left;
    }
    .ctx-item:active { background: #f8fafc; }
    .ctx-item.delete { color: #ef4444; }

    /* Edit bar - DI ATAS chat-footer, z-index LOWER than header */
    .edit-bar {
        position: fixed; left: 0; right: 0; z-index: 1999;
        bottom: 0; padding-bottom: calc(68px + env(safe-area-inset-bottom));
        background: #fff; border-top: 1px solid #e2e8f0;
        transform: translateY(100%); transition: transform .25s;
        box-shadow: 0 -6px 24px rgba(0,0,0,0.06);
    }
    .edit-bar.open { transform: translateY(0); }
    .edit-bar-inner { display: flex; align-items: center; gap: 12px; padding: 10px 16px; }
    .edit-bar-label { font-size: 12px; font-weight: 800; color: var(--blue); white-space: nowrap; flex: 1; overflow: hidden; text-overflow: ellipsis; }

    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 2001;
        background: #fff; padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        border-top: 1px solid #f1f5f9; display: flex; align-items: flex-end; gap: 10px;
    }
    .input-wrap {
        flex: 1; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 24px; padding: 4px 16px; display: flex; align-items: center;
        min-height: 48px; transition: border-color 0.2s;
    }
    .input-wrap:focus-within { border-color: var(--blue); }
    .input-wrap textarea {
        flex: 1; background: transparent; border: 0; outline: 0;
        padding: 10px 0; font-size: 14px; color: var(--navy);
        max-height: 120px; resize: none;
    }
    .send-btn {
        width: 48px; height: 48px; border-radius: 50%;
        background: var(--blue); color: #fff; border: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        transition: all 0.2s;
    }
    .send-btn:active { transform: scale(0.9) rotate(-15deg); }
    .send-btn:disabled { opacity: 0.5; }
    .send-btn .spinner-grow { width: 18px; height: 18px; }
    .attachment-btn { width: 40px; height: 48px; color: #94a3b8; font-size: 22px; display: flex; align-items: center; justify-content: center; border: 0; background: transparent; flex-shrink: 0; }
    .file-preview {
        position: fixed; bottom: 80px; left: 16px; right: 16px;
        background: #fff; border-radius: 16px; padding: 10px 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: none; z-index: 1998; border: 1px solid #f1f5f9;
    }

    /* Scroll-to-bottom FAB */
    .scroll-fab {
        position: fixed; bottom: calc(80px + env(safe-area-inset-bottom)); right: 16px;
        width: 44px; height: 44px; border-radius: 50%; background: #fff;
        border: 0; box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        display: none; align-items: center; justify-content: center;
        z-index: 1997; color: var(--navy); font-size: 18px;
        transition: all 0.2s; cursor: pointer;
    }
    .scroll-fab:active { transform: scale(0.9); }

    /* Invite member bottom sheet */
    .invite-sheet-list { max-height: 45vh; overflow-y: auto; }
    .invite-member {
        display: flex; align-items: center; gap: 12px; padding: 10px 4px;
        border-bottom: 1px solid #f1f5f9; cursor: pointer; border-radius: 12px;
        transition: background 0.15s;
    }
    .invite-member:active { background: #f8fafc; }
    .invite-member img { width: 40px; height: 40px; border-radius: 12px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
    .invite-member .im-info { flex: 1; min-width: 0; }
    .invite-member .im-name { font-weight: 700; font-size: 14px; color: var(--navy); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .invite-member .im-role { font-size: 11px; color: #94a3b8; font-weight: 600; }
    .invite-send-btn {
        border: 0; border-radius: 14px; padding: 12px; font-weight: 800; font-size: 14px;
        color: #fff; background: var(--blue); width: 100%; margin-top: 12px;
        transition: opacity 0.2s;
    }
    .invite-send-btn:disabled { opacity: 0.5; }
</style>

<div class="chat-thread">
    <div class="chat-header">
        <a href="{{ route('chat.index') }}" class="back-btn"><i class="bi bi-chevron-left"></i></a>
        <div class="hdr-avatar" style="{{ $group->type !== 'private' ? 'background: linear-gradient(135deg, #10b981, #059669);' : '' }}">
            @if($hdrAvatar)
                <img src="{{ $hdrAvatar }}">
            @else
                {{ strtoupper(substr($group->name, 0, 1)) }}
            @endif
        </div>
        <div class="hdr-info">
            <div class="hdr-title">{{ $group->name }}</div>
            <div class="hdr-status" style="color:{{ $isPrivate && $other && $other->isOnline() ? '#22c55e' : '#94a3b8' }}">
                {{ $isPrivate ? ($other ? $other->last_seen : 'Offline') : ($group->approved_count ?? $group->members->count()).' Anggota' }}
            </div>
        </div>
        @if(!$isPrivate)
            <button class="hdr-more" onclick="openSheet()"><i class="bi bi-three-dots-vertical"></i></button>
        @endif
    </div>

    <div class="chat-messages" id="message-list">
        @php $lastDate = null; @endphp
        @forelse($messages as $msg)
            @php
                $isMine = $msg->user_id === $user->id;
                $msgDate = $msg->created_at->format('Y-m-d');
            @endphp
            @if($lastDate !== $msgDate)
                @php $lastDate = $msgDate; @endphp
                <div class="date-separator"><span>{{ \Carbon\Carbon::parse($msgDate)->isToday() ? 'Hari ini' : \Carbon\Carbon::parse($msgDate)->translatedFormat('d M Y') }}</span></div>
            @endif
            <div class="msg-group animate-up">
                <div class="msg-item {{ $isMine ? 'mine' : 'other' }}" data-msg-id="{{ $msg->id }}" data-msg-text="{{ e($msg->pesan) }}" @if($isMine) data-mine="1" @endif>
                    @if(!$isMine && !$isPrivate)
                        <div class="sender-name">{{ $msg->user->name }}</div>
                    @endif
                    <div class="bubble {{ $msg->isDeleted() ? 'deleted-bubble' : '' }}" @if(!$msg->isDeleted()) onclick="openCtx(event, {{ $msg->id }})" @endif>
                        @if($msg->isDeleted())
                            <i class="bi bi-trash3"></i> Pesan ini telah dihapus
                        @else
                            @if($msg->file)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$msg->file) }}" class="img-fluid rounded-3" style="max-height: 250px; width: 100%; object-fit: cover;">
                                </div>
                            @endif
                            @if($msg->pesan)
                                <div class="msg-text">{{ $msg->pesan }}</div>
                            @endif
                            <div class="msg-info">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($msg->isEdited()) <span class="edit-tag">diedit</span> @endif
                                @if($isMine) <i class="bi bi-check2-all"></i> @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="bg-white d-inline-block px-4 py-2 rounded-pill shadow-sm small fw-bold text-muted">Mulai percakapan cerdas di sini</div>
            </div>
        @endforelse
    </div>

    <!-- Scroll-to-bottom FAB -->
    <button class="scroll-fab" id="scrollFab" onclick="scrollToBottom()"><i class="bi bi-chevron-down"></i></button>

    <!-- File preview -->
    <div class="file-preview" id="filePreview">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2" style="min-width:0;flex:1;">
                <i class="bi bi-image text-primary" style="flex-shrink:0;"></i>
                <span class="small fw-bold text-muted" id="fileName" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Nama file...</span>
            </div>
            <button type="button" class="back-btn" style="width:28px;height:28px;flex-shrink:0;" onclick="clearFile()"><i class="bi bi-x-lg" style="font-size:11px;"></i></button>
        </div>
    </div>

    <!-- Edit bar -->
    <div class="edit-bar" id="editBar">
        <div class="edit-bar-inner">
            <i class="bi bi-pencil-square" style="color:var(--blue);font-size:16px;flex-shrink:0;"></i>
            <div class="edit-bar-label" id="editLabel">Edit pesan</div>
            <button type="button" class="back-btn" style="margin-left:auto; width:32px; height:32px;" onclick="closeEdit()"><i class="bi bi-x-lg" style="font-size:14px;"></i></button>
        </div>
    </div>

    <!-- Footer -->
    <form action="{{ route('chat.store') }}" method="POST" class="chat-footer" id="chatForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="chat_group_id" value="{{ $group->id }}">
        <input type="file" name="file" id="fileInput" class="d-none" accept="image/*">
        <button type="button" class="attachment-btn" onclick="document.getElementById('fileInput').click()"><i class="bi bi-plus-lg"></i></button>
        <div class="input-wrap">
            <textarea name="pesan" id="chatInput" rows="1" placeholder="Tulis pesan..." oninput="autoResize(this)"></textarea>
        </div>
        <button type="submit" class="send-btn" id="sendBtn"><i class="bi bi-send-fill" id="sendIcon"></i><span class="spinner-grow d-none" id="sendSpinner"></span></button>
    </form>

    <!-- Info grup / menu -->
    <div class="sheet-overlay" id="sheetOverlay" onclick="if(event.target===this)closeSheet()">
        <div class="sheet">
            <div class="sheet-handle"></div>
            <div class="sheet-title">
                <div class="hdr-avatar" style="width:52px;height:52px;border-radius:18px;background:{{ $group->type !== 'private' ? 'linear-gradient(135deg,#10b981,#059669)' : '#f1f5f9' }};display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:20px;margin-bottom:10px;">
                    @if($hdrAvatar)<img src="{{ $hdrAvatar }}" style="width:100%;height:100%;object-fit:cover;border-radius:18px;">@else{{ strtoupper(substr($group->name,0,1)) }}@endif
                </div>
                <div>{{ $group->name }}</div>
                <div style="font-size:12px;color:#94a3b8;font-weight:600;margin-top:2px;">{{ $group->approved_count ?? $group->members->count() }} anggota</div>
            </div>

            @if($isCustom)
                <button type="button" class="sheet-item" onclick="closeSheet(); setTimeout(openInviteSheet, 300);">
                    <span class="si-icon" style="background:#eef2ff;color:#6366f1;"><i class="bi bi-person-plus-fill"></i></span>
                    Undang Anggota
                </button>
            @endif

            @if($isCustom || $group->is_admin)
                <div class="si-divider"></div>
                <button type="button" class="sheet-item" onclick="leaveGroup()">
                    <span class="si-icon" style="background:#fef2f2;color:#ef4444;"><i class="bi bi-box-arrow-right"></i></span>
                    Keluar Grup
                </button>
            @endif
            <div class="si-divider"></div>
            <button type="button" class="sheet-item" onclick="closeSheet()">
                <span class="si-icon" style="background:#f1f5f9;color:#64748b;"><i class="bi bi-x-lg"></i></span>
                Tutup
            </button>
        </div>
    </div>

    <!-- Invite member bottom sheet -->
    <div class="sheet-overlay" id="inviteOverlay" onclick="if(event.target===this)closeInviteSheet()">
        <div class="sheet">
            <div class="sheet-handle"></div>
            <div class="sheet-title">Undang Anggota</div>
            <div class="invite-sheet-list" id="inviteList">
                @if(isset($candidates) && count($candidates) > 0)
                    @foreach($candidates as $c)
                        <div class="invite-member" data-uid="{{ $c->id }}" onclick="toggleInviteMember(this)">
                            <img src="{{ $c->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($c->name).'&background=random' }}">
                            <div class="im-info">
                                <div class="im-name">{{ $c->name }}</div>
                                <div class="im-role">{{ ucfirst($c->role) }}</div>
                            </div>
                            <div class="cg-check" style="width:24px;height:24px;border-radius:50%;border:2px solid #cbd5e1;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.2s;">
                                <i class="bi bi-check-lg" style="color:#fff;font-size:14px;"></i>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:30px;color:#94a3b8;font-size:13px;">Tidak ada calon anggota tersedia.</div>
                @endif
            </div>
            <button type="button" class="invite-send-btn" id="inviteSendBtn" disabled onclick="sendInvite()">Kirim Undangan</button>
        </div>
    </div>
</div>

<script>
(function(){
    var msgList = document.getElementById('message-list');
    if(msgList) msgList.scrollTop = msgList.scrollHeight;

    var chatForm = document.getElementById('chatForm');
    var chatInput = document.getElementById('chatInput');
    var fileInput = document.getElementById('fileInput');
    var filePreview = document.getElementById('filePreview');
    var fileName = document.getElementById('fileName');
    var editBar = document.getElementById('editBar');
    var editLabel = document.getElementById('editLabel');
    var sendBtn = document.getElementById('sendBtn');
    var sendIcon = document.getElementById('sendIcon');
    var sendSpinner = document.getElementById('sendSpinner');
    var scrollFab = document.getElementById('scrollFab');

    var currentUserId = @json((int) $user->id);
    var activeGroupId = @json((int) $group->id);
    var isAdmin = @json((bool) ($group->is_admin ?? false));
    var lastMsgId = @json($messages->last()?->id ?? 0);
    var editingId = null;
    var csrf = document.querySelector('meta[name=csrf-token]').content;

    // Auto-resize textarea
    window.autoResize = function(el) {
        el.style.height = '';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    };

    // Scroll-to-bottom FAB
    if(msgList) {
        msgList.addEventListener('scroll', function(){
            var distFromBottom = msgList.scrollHeight - msgList.scrollTop - msgList.clientHeight;
            if(scrollFab) scrollFab.style.display = distFromBottom > 200 ? 'flex' : 'none';
        });
    }
    window.scrollToBottom = function(){
        if(msgList) msgList.scrollTop = msgList.scrollHeight;
        if(scrollFab) scrollFab.style.display = 'none';
    };

    // File input
    if(fileInput) fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            if(fileName) fileName.textContent = this.files[0].name;
            if(filePreview) filePreview.style.display = 'block';
        }
    });
    window.clearFile = function() { fileInput.value = ''; if(filePreview) filePreview.style.display = 'none'; };

    function escapeHtml(s) { var d=document.createElement('div'); d.textContent=s==null?'':String(s); return d.innerHTML; }

    function setLoading(on) {
        if(sendIcon) sendIcon.classList.toggle('d-none', on);
        if(sendSpinner) sendSpinner.classList.toggle('d-none', !on);
        if(sendBtn) sendBtn.disabled = on;
    }

    function appendMessage(data, mine) {
        if (data.id && document.querySelector('[data-msg-id="'+data.id+'"]')) return;
        var group = document.createElement('div');
        group.className = 'msg-group animate-up';
        var item = document.createElement('div');
        item.className = 'msg-item ' + (mine ? 'mine' : 'other');
        if (data.id) item.setAttribute('data-msg-id', data.id);
        if (data.pesan) item.setAttribute('data-msg-text', data.pesan);
        if (mine) item.setAttribute('data-mine', '1');
        var html = '';
        @if(!$isPrivate)
            if (!mine) html += '<div class="sender-name">'+escapeHtml(data.nama)+'</div>';
        @endif
        if (data.deleted) {
            html += '<div class="bubble deleted-bubble"><i class="bi bi-trash3"></i> Pesan ini telah dihapus</div>';
        } else {
            html += '<div class="bubble" onclick="openCtx(event, '+data.id+')">';
            if (data.file_url) html += '<div class="mb-2"><img src="'+data.file_url+'" class="img-fluid rounded-3" style="max-height: 250px; width: 100%; object-fit: cover;"></div>';
            if (data.pesan) html += '<div class="msg-text">'+escapeHtml(data.pesan)+'</div>';
            html += '<div class="msg-info"><span>'+data.waktu+'</span>';
            if (data.edited) html += '<span class="edit-tag">diedit</span>';
            if (mine) html += ' <i class="bi bi-check2-all"></i>';
            html += '</div></div>';
        }
        item.innerHTML = html;
        group.appendChild(item);
        msgList.appendChild(group);
        msgList.scrollTop = msgList.scrollHeight;
    }

    // Context menu
    var ctxMenu = document.createElement('div');
    ctxMenu.className = 'msg-context-menu';
    ctxMenu.style.display = 'none';
    document.body.appendChild(ctxMenu);

    window.openCtx = function(e, msgId) {
        e.stopPropagation();
        if (editingId) closeEdit();
        var item = document.querySelector('[data-msg-id="'+msgId+'"]');
        if (!item) return;
        var mine = item.classList.contains('mine');
        if (item.querySelector('.deleted-bubble')) return;
        var html = '';
        if (mine) html += '<button type="button" class="ctx-item" id="ctxEdit"><i class="bi bi-pencil"></i> Edit</button>';
        if (mine || isAdmin) html += '<button type="button" class="ctx-item delete" id="ctxDelete"><i class="bi bi-trash3"></i> Hapus</button>';
        html += '<button type="button" class="ctx-item" id="ctxCancel"><i class="bi bi-x-lg"></i> Batal</button>';
        ctxMenu.innerHTML = html;
        ctxMenu.style.display = 'block';
        var r = item.getBoundingClientRect();
        var mw = 190;
        var itemCount = (mine ? 1 : 0) + (mine || isAdmin ? 1 : 0) + 1;
        var mh = itemCount * 48 + 16;
        var x = Math.min(r.left, window.innerWidth - mw - 12);
        var y = Math.min(r.bottom + 4, window.innerHeight - mh - 12);
        if (y < 60) y = Math.max(50, r.top - mh);
        ctxMenu.style.left = Math.max(12, x) + 'px';
        ctxMenu.style.top = y + 'px';
        ctxMenu._msgId = msgId;

        var ed = ctxMenu.querySelector('#ctxEdit');
        if (ed) ed.onclick = function(){ startEdit(msgId); };
        ctxMenu.querySelector('#ctxDelete').onclick = function(){ deleteMessage(msgId); };
        ctxMenu.querySelector('#ctxCancel').onclick = function(){ hideCtx(); };
    };

    function hideCtx() { ctxMenu.style.display = 'none'; }
    window.hideCtx = hideCtx;
    document.addEventListener('click', function(e) { if (!ctxMenu.contains(e.target)) hideCtx(); });

    function startEdit(msgId) {
        hideCtx();
        var item = document.querySelector('[data-msg-id="'+msgId+'"]');
        if(!item) return;
        var txt = (item.dataset.msgText || item.querySelector('.msg-text')?.textContent || '').trim();
        editingId = msgId;
        editBar.classList.add('open');
        chatInput.value = txt;
        autoResize(chatInput);
        chatInput.focus();
        editLabel.textContent = 'Edit pesan';
    }

    window.closeEdit = function() {
        editingId = null;
        editBar.classList.remove('open');
        chatInput.value = '';
        chatInput.style.height = '';
    };

    function deleteMessage(msgId) {
        hideCtx();
        if(!confirm('Hapus pesan ini untuk semua orang?')) return;
        fetch('/pesan/' + msgId, { method:'DELETE', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if(d.ok){
                    var item = document.querySelector('[data-msg-id="'+msgId+'"]');
                    if(item){
                        var b = item.querySelector('.bubble');
                        b.className = 'bubble deleted-bubble';
                        b.innerHTML = '<i class="bi bi-trash3"></i> Pesan ini telah dihapus';
                        b.onclick = null;
                    }
                }
            })
            .catch(function(){ alert('Gagal menghapus'); });
    }

    // Submit (kirim / edit)
    if(chatForm) chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (editingId) {
            var pesan = chatInput.value.trim();
            if (!pesan) return;
            setLoading(true);
            fetch('/pesan/' + editingId, { method:'PUT', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf, 'Content-Type':'application/x-www-form-urlencoded' }, body: 'pesan='+encodeURIComponent(pesan) })
                .then(function(r){ return r.json(); })
                .then(function(d){
                    setLoading(false);
                    if(d.ok){
                        var item = document.querySelector('[data-msg-id="'+editingId+'"]');
                        if(item){
                            item.dataset.msgText = d.pesan;
                            var b = item.querySelector('.bubble');
                            var existingImg = b.querySelector('img');
                            var imgHtml = existingImg ? '<div class="mb-2">'+existingImg.outerHTML+'</div>' : '';
                            var senderHtml = item.querySelector('.sender-name') ? item.querySelector('.sender-name').outerHTML : '';
                            b.innerHTML = senderHtml + imgHtml + '<div class="msg-text">'+escapeHtml(d.pesan)+'</div><div class="msg-info"><span>'+d.waktu+'</span><span class="edit-tag">diedit</span><i class="bi bi-check2-all"></i></div>';
                        }
                        closeEdit();
                    }
                })
                .catch(function(){ setLoading(false); alert('Gagal mengedit'); });
            return;
        }
        var pesan = (chatInput?chatInput.value:'').trim();
        var hasFile = fileInput && fileInput.files.length > 0;
        if (!pesan && !hasFile) return;
        setLoading(true);
        var tempFileUrl = null;
        if (hasFile) try{ tempFileUrl = URL.createObjectURL(fileInput.files[0]); }catch(ex){}
        appendMessage({ pesan: pesan, nama: '', waktu: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }), file_url: tempFileUrl }, true);
        var formData = new FormData(chatForm);
        if(chatInput){ chatInput.value = ''; chatInput.style.height = ''; }
        clearFile();
        fetch(chatForm.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData })
            .then(function(){ setLoading(false); })
            .catch(function(){ setLoading(false); });
    });

    // Sheet
    window.openSheet = function() { document.getElementById('sheetOverlay').classList.add('open'); };
    window.closeSheet = function() { document.getElementById('sheetOverlay').classList.remove('open'); };

    window.leaveGroup = function() {
        closeSheet();
        if(!confirm('Keluar dari grup ini?')) return;
        fetch('/chat/' + activeGroupId + '/leave', { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf } })
            .then(function(r){ return r.json(); })
            .then(function(d){ if(d.ok){ window.location = '/chat'; } })
            .catch(function(){ alert('Gagal keluar grup'); });
    };

    // Invite sheet
    var inviteSelected = [];
    window.openInviteSheet = function() {
        inviteSelected = [];
        document.querySelectorAll('#inviteList .invite-member').forEach(function(el){
            el.querySelector('.cg-check').classList.remove('on');
            el.querySelector('.cg-check').style.background = '';
            el.querySelector('.cg-check').style.borderColor = '#cbd5e1';
        });
        var btn = document.getElementById('inviteSendBtn');
        if(btn) btn.disabled = true;
        document.getElementById('inviteOverlay').classList.add('open');
    };
    window.closeInviteSheet = function() { document.getElementById('inviteOverlay').classList.remove('open'); };

    window.toggleInviteMember = function(el) {
        var uid = parseInt(el.dataset.uid);
        var idx = inviteSelected.indexOf(uid);
        var check = el.querySelector('.cg-check');
        if(idx >= 0) {
            inviteSelected.splice(idx, 1);
            check.classList.remove('on');
            check.style.background = '';
            check.style.borderColor = '#cbd5e1';
        } else {
            inviteSelected.push(uid);
            check.classList.add('on');
            check.style.background = 'var(--blue)';
            check.style.borderColor = 'var(--blue)';
        }
        var btn = document.getElementById('inviteSendBtn');
        if(btn) btn.disabled = inviteSelected.length === 0;
        if(btn) btn.textContent = inviteSelected.length > 0 ? 'Kirim Undangan ('+inviteSelected.length+')' : 'Kirim Undangan';
    };

    window.sendInvite = function() {
        if(inviteSelected.length === 0) return;
        var btn = document.getElementById('inviteSendBtn');
        btn.disabled = true;
        btn.textContent = 'Mengirim...';
        var done = 0;
        var total = inviteSelected.length;
        inviteSelected.forEach(function(uid){
            fetch('/chat/' + activeGroupId + '/invite', {
                method:'POST',
                headers:{ 'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf, 'Content-Type':'application/x-www-form-urlencoded' },
                body:'user_id='+uid
            })
            .then(function(r){ return r.json(); })
            .then(function(){ done++; if(done===total){ closeInviteSheet(); alert('Undangan terkirim ke '+total+' orang.'); } })
            .catch(function(){ done++; if(done===total){ closeInviteSheet(); alert('Sebagian undangan gagal dikirim.'); } });
        });
    };

    // Realtime: Echo + Poll
    function applyUpdate(e) {
        if (e.deleted) {
            var item = document.querySelector('[data-msg-id="'+e.id+'"]');
            if (item) {
                var b = item.querySelector('.bubble');
                b.className = 'bubble deleted-bubble';
                b.innerHTML = '<i class="bi bi-trash3"></i> Pesan ini telah dihapus';
                b.onclick = null;
            }
        } else if (e.edited) {
            var item = document.querySelector('[data-msg-id="'+e.id+'"]');
            if (item) {
                item.dataset.msgText = e.pesan;
                var b = item.querySelector('.bubble');
                var isMine = item.classList.contains('mine');
                var existingImg = b.querySelector('img');
                var imgHtml = existingImg ? '<div class="mb-2">'+existingImg.outerHTML+'</div>' : '';
                var senderHtml = item.querySelector('.sender-name') ? item.querySelector('.sender-name').outerHTML : '';
                b.innerHTML = senderHtml + imgHtml + '<div class="msg-text">'+escapeHtml(e.pesan)+'</div><div class="msg-info"><span>'+e.waktu+'</span><span class="edit-tag">diedit</span>'+(isMine ? '<i class="bi bi-check2-all"></i>':'')+'</div>';
            }
        }
    }

    if (window.Echo) {
        window.Echo.private('portal-chat-group.' + activeGroupId)
            .listen('.new-message', function(e) {
                if (e.action === 'updated' || e.action === 'deleted') { applyUpdate(e); return; }
                if (e.user_id !== currentUserId) {
                    appendMessage({ id: e.id, nama: e.nama, pesan: e.pesan, waktu: e.waktu, file_url: e.file_url, edited: e.edited, deleted: e.deleted }, false);
                    if (e.id > lastMsgId) lastMsgId = e.id;
                }
            });
    } else {
        setInterval(function() {
            fetch('{{ route('chat.poll') }}?group_id='+activeGroupId+'&last_id='+lastMsgId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(res){ return res.json(); })
            .then(function(data) {
                if (data.length > 0) {
                    data.forEach(function(msg) {
                        if (msg.deleted || msg.edited) { applyUpdate(msg); }
                        else if (msg.user_id !== currentUserId) { appendMessage(msg, false); }
                        if (msg.id > lastMsgId) lastMsgId = msg.id;
                    });
                }
            })
            .catch(function(){});
        }, 3000);
    }
})();
</script>
@endsection
