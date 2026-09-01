@php
    $hideNav = true;
    $isPrivate = $group->type === 'private';
    $other = $group->other_user ?? null;

    // Avatar logic for header
    $hdrAvatar = $isPrivate && $other ? $other->avatar_url : ($group->avatar ? asset('storage/'.$group->avatar) : null);
@endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-thread { display: flex; flex-direction: column; height: 100vh; background: #f0f2f5; }

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
        color: var(--navy); text-decoration: none; transition: all 0.2s;
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
    .hdr-title { font-size: 15px; font-weight: 800; color: var(--navy); margin-bottom: 0px; }
    .hdr-status { font-size: 11px; color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 4px; }
    .hdr-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #10b981; }

    .chat-messages {
        flex: 1; padding: 76px 16px calc(86px + env(safe-area-inset-bottom));
        overflow-y: auto; display: flex; flex-direction: column;
        scroll-behavior: smooth;
    }

    .msg-group { margin-bottom: 16px; display: flex; flex-direction: column; }
    .msg-item { max-width: 80%; margin-bottom: 4px; position: relative; }
    .msg-item.mine { align-self: flex-end; }
    .msg-item.other { align-self: flex-start; }

    .bubble {
        padding: 10px 14px; border-radius: 20px; font-size: 14px; line-height: 1.5;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04); position: relative;
    }
    .mine .bubble {
        background: var(--blue); color: #fff;
        border-bottom-right-radius: 4px;
    }
    .other .bubble {
        background: #fff; color: var(--navy);
        border-bottom-left-radius: 4px;
        border: 1px solid #f1f5f9;
    }

    .msg-info { font-size: 10px; margin-top: 4px; display: flex; align-items: center; gap: 4px; color: #94a3b8; font-weight: 600; }
    .mine .msg-info { justify-content: flex-end; }

    .sender-name { font-size: 11px; font-weight: 800; margin-bottom: 4px; color: #6366f1; }

    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 2000;
        background: #fff; padding: 12px 16px calc(24px + env(safe-area-inset-bottom));
        border-top: 1px solid #f1f5f9; display: flex; align-items: flex-end; gap: 10px;
    }
    .input-wrap {
        flex: 1; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 24px; padding: 4px 16px; display: flex; align-items: center;
        min-height: 48px;
    }
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

    .attachment-btn {
        width: 40px; height: 48px; color: #94a3b8; font-size: 22px;
        display: flex; align-items: center; justify-content: center;
    }

    .file-preview {
        position: absolute; bottom: 90px; left: 16px; right: 16px;
        background: #fff; border-radius: 16px; padding: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: none;
        z-index: 10; border: 1px solid #f1f5f9;
    }
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
            <div class="hdr-title text-truncate">{{ $group->name }}</div>
            <div class="hdr-status">{{ $isPrivate ? 'Online' : $group->members->count().' Anggota' }}</div>
        </div>
    </div>

    <div class="chat-messages" id="message-list">
        @forelse($messages as $msg)
            @php $isMine = $msg->user_id === $user->id; @endphp
            <div class="msg-group animate-up">
                <div class="msg-item {{ $isMine ? 'mine' : 'other' }}" data-msg-id="{{ $msg->id }}">
                    @if(!$isMine && !$isPrivate)
                        <div class="sender-name">{{ $msg->user->name }}</div>
                    @endif
                    <div class="bubble">
                        @if($msg->file)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$msg->file) }}" class="img-fluid rounded-3" style="max-height: 250px; width: 100%; object-fit: cover;">
                            </div>
                        @endif
                        @if($msg->pesan)
                            <div>{{ $msg->pesan }}</div>
                        @endif
                        <div class="msg-info">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($isMine) <i class="bi bi-check2-all text-white-50"></i> @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="bg-white d-inline-block px-4 py-2 rounded-pill shadow-sm small fw-bold text-muted">Mulai percakapan cerdas di sini</div>
            </div>
        @endforelse
    </div>

    <div class="file-preview" id="filePreview">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-image text-primary"></i>
                <span class="small fw-bold text-muted" id="fileName">Nama file...</span>
            </div>
            <button class="btn-close btn-sm" onclick="clearFile()"></button>
        </div>
    </div>

    <form action="{{ route('chat.store') }}" method="POST" class="chat-footer" id="chatForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="chat_group_id" value="{{ $group->id }}">
        <input type="file" name="file" id="fileInput" class="d-none" accept="image/*">

        <button type="button" class="attachment-btn" onclick="document.getElementById('fileInput').click()">
            <i class="bi bi-plus-lg"></i>
        </button>

        <div class="input-wrap">
            <textarea name="pesan" id="chatInput" rows="1" placeholder="Tulis pesan..." oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
        </div>

        <button type="submit" class="send-btn">
            <i class="bi bi-send-fill"></i>
        </button>
    </form>
</div>

<script>
    const msgList = document.getElementById('message-list');
    if(msgList) msgList.scrollTop = msgList.scrollHeight;

    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');

    const currentUserId = @json((int) $user->id);
    const activeGroupId = @json((int) $group->id);
    let lastMsgId = @json($messages->last()?->id ?? 0);

    if(fileInput) fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            if(fileName) fileName.textContent = this.files[0].name;
            if(filePreview) filePreview.style.display = 'block';
        }
    });

    function clearFile() {
        fileInput.value = '';
        filePreview.style.display = 'none';
    }

    function appendMessage(data, mine) {
        if (data.id && document.querySelector(`[data-msg-id="${data.id}"]`)) return;

        const group = document.createElement('div');
        group.className = 'msg-group animate-up';

        const item = document.createElement('div');
        item.className = 'msg-item ' + (mine ? 'mine' : 'other');
        if (data.id) item.setAttribute('data-msg-id', data.id);

        let html = '';
        @if(!$isPrivate)
            if (!mine) html += '<div class="sender-name">'+data.nama+'</div>';
        @endif

        html += '<div class="bubble">';
        if (data.file_url) {
            html += '<div class="mb-2"><img src="'+data.file_url+'" class="img-fluid rounded-3" style="max-height: 250px; width: 100%; object-fit: cover;"></div>';
        }
        if (data.pesan) html += '<div>'+data.pesan+'</div>';
        html += '<div class="msg-info"><span>'+data.waktu+'</span>';
        if (mine) html += ' <i class="bi bi-check2-all text-white-50"></i>';
        html += '</div></div>';

        item.innerHTML = html;
        group.appendChild(item);
        msgList.appendChild(group);
        msgList.scrollTop = msgList.scrollHeight;
    }

    if(chatForm) chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var pesan = (chatInput?chatInput.value:'').trim();
        var hasFile = fileInput && fileInput.files.length > 0;
        if (!pesan && !hasFile) return;
        var tempFileUrl = null;
        if (hasFile) try{ tempFileUrl = URL.createObjectURL(fileInput.files[0]); }catch(e){}
        appendMessage({ pesan, nama: '', waktu: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }), file_url: tempFileUrl }, true);
        var formData = new FormData(chatForm);
        if(chatInput){ chatInput.value = ''; chatInput.style.height = ''; }
        clearFile();
        fetch(chatForm.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData }).catch(function(){});
    });

    if (window.Echo) {
        window.Echo.private('portal-chat-group.' + activeGroupId)
            .listen('.new-message', (e) => {
                if (e.user_id !== currentUserId) {
                    appendMessage({ id: e.id, nama: e.nama, pesan: e.pesan, waktu: e.waktu, file_url: e.file_url }, false);
                    if (e.id > lastMsgId) lastMsgId = e.id;
                }
            });
    } else {
        setInterval(() => {
            fetch(`{{ route('chat.poll') }}?group_id=${activeGroupId}&last_id=${lastMsgId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(msg => {
                        if (msg.user_id !== currentUserId) appendMessage(msg, false);
                        if (msg.id > lastMsgId) lastMsgId = msg.id;
                    });
                }
            });
        }, 3000);
    }
</script>
@endsection
