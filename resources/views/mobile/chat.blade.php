@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    /* WhatsApp/Telegram Style UI */
    .chat-app { display: flex; flex-direction: column; height: 100vh; background: #f0f2f5; }

    /* Inbox / Group List */
    .inbox-header {
        padding: 20px 15px 10px; background: #fff;
        border-bottom: 1px solid #f0f0f0;
    }
    .inbox-list { flex: 1; overflow-y: auto; padding-bottom: 20px; background: #fff; }
    .group-item {
        display: flex; align-items: center; gap: 15px; padding: 12px 15px;
        border-bottom: 1px solid #f8f9fa; text-decoration: none; color: inherit;
        transition: background 0.2s;
    }
    .group-item:active { background: #f0f2f5; }
    .group-avatar {
        width: 50px; height: 50px; border-radius: 50%;
        background: linear-gradient(135deg, #0088cc, #00aaff);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: bold; font-size: 18px; flex-shrink: 0;
        box-shadow: 0 3px 10px rgba(0,136,204,0.2);
    }
    .group-avatar.class { background: linear-gradient(135deg, #25d366, #128c7e); }
    .group-avatar.eskul { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
    .group-info { flex: 1; min-width: 0; }
    .group-name { font-weight: 700; font-size: 15px; margin-bottom: 2px; color: #1c1e21; }
    .group-last-msg { font-size: 13px; color: #65676b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .group-meta { text-align: right; flex-shrink: 0; }
    .group-time { font-size: 11px; color: #8a8d91; }

    /* Chat Thread */
    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #f0f0f0;
        padding: 10px 15px; display: flex; align-items: center; gap: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .chat-header .back-btn { color: #0088cc; font-size: 20px; text-decoration: none; }
    .chat-header-info { flex: 1; min-width: 0; }
    .chat-header-name { font-weight: 700; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-header-status { font-size: 11px; color: #22c55e; font-weight: 600; }

    .chat-messages {
        flex: 1; padding: 75px 15px 90px;
        background-color: #e5ddd5;
        background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
        overflow-y: auto; display: flex; flex-direction: column;
    }

    .msg-row { display: flex; margin-bottom: 12px; max-width: 85%; }
    .msg-row.mine { align-self: flex-end; flex-direction: row-reverse; }
    .msg-row.other { align-self: flex-start; }

    .msg-bubble {
        padding: 8px 12px; border-radius: 12px; position: relative;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        font-size: 14px; line-height: 1.4;
    }
    .msg-row.mine .msg-bubble { background: #dcf8c6; color: #303030; border-top-right-radius: 2px; }
    .msg-row.other .msg-bubble { background: #fff; color: #303030; border-top-left-radius: 2px; }

    .msg-sender { font-size: 11px; font-weight: 700; color: #075e54; margin-bottom: 3px; }
    .msg-footer { display: flex; align-items: center; justify-content: flex-end; gap: 4px; margin-top: 4px; }
    .msg-time { font-size: 10px; color: #8a8d91; }

    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
        background: #f0f2f5; padding: 10px 15px; display: flex; align-items: center; gap: 10px;
    }
    .chat-input {
        flex: 1; background: #fff; border: none; border-radius: 20px;
        padding: 10px 15px; font-size: 14px; outline: none;
    }
    .send-btn {
        width: 40px; height: 40px; border-radius: 50%; border: none;
        background: #0088cc; color: #fff; display: grid; place-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
</style>

<div class="chat-app">
    @if(!$activeGroup)
        <!-- Group List / Inbox View -->
        <div class="inbox-header">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Chat</h4>
                <a href="{{ route('dashboard') }}" class="btn-close"></a>
            </div>
            <div class="input-group input-group-sm mb-2">
                <span class="input-group-text border-0 bg-light"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-0 bg-light" placeholder="Cari grup...">
            </div>
        </div>

        <div class="inbox-list">
            @forelse($groups as $g)
                <a href="{{ route('chat.index', ['group_id' => $g->id]) }}" class="group-item">
                    <div class="group-avatar {{ $g->type }}">
                        @if($g->avatar)
                            <img src="{{ asset('storage/'.$g->avatar) }}" class="rounded-circle w-100 h-100">
                        @else
                            {{ strtoupper(substr($g->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="group-info">
                        <div class="group-name">{{ $g->name }}</div>
                        <div class="group-last-msg">
                            @if($g->lastMessage)
                                <strong>{{ $g->lastMessage->user->id === $user->id ? 'Anda: ' : $g->lastMessage->user->name . ': ' }}</strong>
                                {{ $g->lastMessage->pesan }}
                            @else
                                Belum ada pesan
                            @endif
                        </div>
                    </div>
                    <div class="group-meta">
                        <div class="group-time">{{ $g->lastMessage ? $g->lastMessage->created_at->format('H:i') : '' }}</div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-chat-dots h1 d-block mb-3 opacity-25"></i>
                    Belum ada grup chat.
                </div>
            @endforelse
        </div>
    @else
        <!-- Chat Thread View -->
        <div class="chat-header">
            <a href="{{ route('chat.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i></a>
            <div class="group-avatar {{ $activeGroup->type }} " style="width:38px; height:38px; font-size:14px;">
                @if($activeGroup->avatar)
                    <img src="{{ asset('storage/'.$activeGroup->avatar) }}" class="rounded-circle w-100 h-100">
                @else
                    {{ strtoupper(substr($activeGroup->name, 0, 1)) }}
                @endif
            </div>
            <div class="chat-header-info">
                <div class="chat-header-name">{{ $activeGroup->name }}</div>
                <div class="chat-header-status">{{ $activeGroup->members->count() }} Anggota</div>
            </div>
        </div>

        <div class="chat-messages" id="message-list">
            @forelse($messages as $msg)
                @php $isMine = $msg->user_id === $user->id; @endphp
                <div class="msg-row {{ $isMine ? 'mine' : 'other' }}">
                    <div class="msg-bubble">
                        @if(!$isMine)
                            <div class="msg-sender">{{ $msg->user->name }}</div>
                        @endif
                        <div class="msg-text">{{ $msg->pesan }}</div>
                        <div class="msg-footer">
                            <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                            @if($isMine) <i class="bi bi-check2-all text-primary" style="font-size: 12px;"></i> @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5" style="opacity: 0.6;">
                    <span class="bg-white px-3 py-1 rounded-pill small fw-bold">Belum ada pesan. Mulai percakapan!</span>
                </div>
            @endforelse
        </div>

        <form action="{{ route('chat.store') }}" method="POST" class="chat-footer" id="chatForm">
            @csrf
            <input type="hidden" name="chat_group_id" value="{{ $activeGroup->id }}">
            <button type="button" class="btn btn-link text-secondary p-0"><i class="bi bi-emoji-smile h5 mb-0"></i></button>
            <input name="pesan" id="chatInput" autocomplete="off" class="chat-input" placeholder="Ketik pesan..." required>
            <button type="submit" class="send-btn"><i class="bi bi-send-fill"></i></button>
        </form>
    @endif
</div>

<script>
    @if($activeGroup)
    const msgList = document.getElementById('message-list');
    msgList.scrollTop = msgList.scrollHeight;

    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const currentUserId = @json((int) $user->id);
    const activeGroupId = @json((int) $activeGroup->id);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function appendMessage(data, mine) {
        const row = document.createElement('div');
        row.className = 'msg-row ' + (mine ? 'mine' : 'other');

        let inner = '<div class="msg-bubble">';
        if (!mine) {
            inner += '<div class="msg-sender">' + esc(data.nama || 'User') + '</div>';
        }
        inner += '<div class="msg-text">' + esc(data.pesan) + '</div>';
        inner += '<div class="msg-footer"><span class="msg-time">' + (data.waktu || '') + '</span>';
        if (mine) inner += ' <i class="bi bi-check2-all text-primary" style="font-size: 12px;"></i>';
        inner += '</div></div>';

        row.innerHTML = inner;
        msgList.appendChild(row);
        msgList.scrollTop = msgList.scrollHeight;
    }

    function esc(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const pesan = chatInput.value.trim();
        if(!pesan) return;

        // Optimistic UI
        appendMessage({ pesan: pesan, nama: '', waktu: new Date().toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit'}) }, true);
        chatInput.value = '';
        chatInput.focus();

        const formData = new FormData(chatForm);
        fetch(chatForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new URLSearchParams(formData)
        }).then(r => r.json()).then(data => {
            if(!data.ok) alert(data.message || 'Gagal mengirim pesan');
        });
    });

    if (window.Echo) {
        window.Echo.private('portal-chat-group.' + activeGroupId)
            .listen('.new-message', (e) => {
                if (e.user_id !== currentUserId) {
                    appendMessage({ nama: e.nama, pesan: e.pesan, waktu: e.waktu }, false);
                }
            });
    }
    @endif
</script>
@endsection
