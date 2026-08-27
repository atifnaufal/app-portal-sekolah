@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $lastDate = '';
    $lastUserId = null;
@endphp

<style>
    * { box-sizing: border-box; }

    .wa-header {
        position:fixed; top:0; left:0; right:0; z-index:1000;
        background:#075e54; color:#fff; padding:10px 14px;
        display:flex; align-items:center; gap:10px;
    }
    .wa-header-back {
        width:32px; height:32px; display:flex; align-items:center; justify-content:center;
        text-decoration:none; color:#fff; border-radius:50%; flex-shrink:0;
    }
    .wa-header-avatar {
        width:38px; height:38px; border-radius:50%; overflow:hidden;
        background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center;
        font-weight:800; font-size:14px; flex-shrink:0;
    }
    .wa-header-avatar img { width:100%; height:100%; object-fit:cover; }
    .wa-header-info { flex:1; min-width:0; }
    .wa-header-name { font-size:15px; font-weight:700; }
    .wa-header-sub { font-size:11px; opacity:0.75; }

    .wa-body {
        padding: 58px 8px 72px;
        min-height: 100vh;
        background-color: #ece5dd;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4ccc4' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .wa-date-sep {
        text-align:center; margin:12px 0;
    }
    .wa-date-sep span {
        background:#d9e7ed; color:#4a4a4a; font-size:11px; font-weight:600;
        padding:4px 12px; border-radius:8px; display:inline-block;
    }

    .wa-msg-row {
        display:flex; gap:6px; margin-bottom:2px; max-width:85%;
    }
    .wa-msg-row.own { margin-left:auto; flex-direction:row-reverse; }
    .wa-msg-row.continued { margin-bottom:2px; }
    .wa-msg-row.first { margin-top:6px; }

    .wa-msg-avatar {
        width:30px; height:30px; border-radius:50%; overflow:hidden;
        flex-shrink:0; align-self:flex-end; background:#d4ccc4;
        display:flex; align-items:center; justify-content:center;
        font-size:11px; font-weight:800; color:#fff;
    }
    .wa-msg-avatar img { width:100%; height:100%; object-fit:cover; }
    .wa-msg-avatar.hidden { visibility:hidden; }

    .wa-bubble {
        padding:7px 10px 4px; border-radius:10px; position:relative;
        font-size:14px; line-height:1.45; word-wrap:break-word;
        box-shadow:0 1px 1px rgba(0,0,0,0.08);
    }
    .wa-bubble.other {
        background:#fff; border-top-left-radius:2px; color:#1a1a1a;
    }
    .wa-bubble.own {
        background:#dcf8c6; border-top-right-radius:2px; color:#1a1a1a;
    }

    .wa-bubble-name {
        font-size:12px; font-weight:700; margin-bottom:2px;
    }
    .wa-bubble-name.role-guru { color:#075e54; }
    .wa-bubble-name.role-siswa { color:#246bfe; }

    .wa-bubble-footer {
        display:flex; justify-content:flex-end; align-items:center; gap:3px;
        margin-top:-2px;
    }
    .wa-bubble-time {
        font-size:10px; color:rgba(0,0,0,0.4);
    }

    .wa-footer {
        position:fixed; bottom:0; left:0; right:0; z-index:1000;
        background:#f0f0f0; padding:6px 8px;
        display:flex; align-items:flex-end; gap:6px;
    }
    .wa-input-wrap {
        flex:1; background:#fff; border-radius:22px; padding:8px 14px;
        display:flex; align-items:center;
    }
    .wa-input {
        flex:1; border:none; outline:none; font-size:14px;
        background:transparent; resize:none; max-height:100px; line-height:1.4;
    }
    .wa-send-btn {
        width:42px; height:42px; border-radius:50%; border:none;
        background:#075e54; color:#fff; display:flex; align-items:center;
        justify-content:center; cursor:pointer; flex-shrink:0;
        font-size:18px; transition: transform 0.1s;
    }
    .wa-send-btn:active { transform:scale(0.9); }

    .wa-emoji-btn {
        background:none; border:none; font-size:20px; cursor:pointer;
        padding:0 4px; flex-shrink:0;
    }

    .wa-emoji-panel {
        display:none; background:#fff; border-radius:12px; padding:10px;
        margin:0 8px 6px; box-shadow:0 -2px 8px rgba(0,0,0,0.06);
    }
    .wa-emoji-panel.open { display:block; }
    .wa-emoji-grid {
        display:grid; grid-template-columns:repeat(8,1fr); gap:6px;
        max-height:140px; overflow-y:auto;
    }
    .wa-emoji-item {
        font-size:20px; text-align:center; cursor:pointer; padding:4px;
        border-radius:8px;
    }
    .wa-emoji-item:active { background:#f0f0f0; }

    .wa-empty {
        text-align:center; padding:40px 20px; color:#8a8a8a;
    }
    .wa-empty i { font-size:40px; opacity:0.3; }
</style>

{{-- Header --}}
<div class="wa-header">
    <a href="{{ route('dashboard') }}" class="wa-header-back">
        <i class="bi bi-chevron-left" style="font-size:20px;"></i>
    </a>
    <div class="wa-header-avatar">
        <i class="bi bi-people-fill"></i>
    </div>
    <div class="wa-header-info">
        <div class="wa-header-name">Grup {{ $user->kelas->nama }}</div>
        <div class="wa-header-sub">{{ $memberCount }} anggota</div>
    </div>
</div>

{{-- Body --}}
<div class="wa-body" id="chatBody">
    @if($messages->isEmpty())
        <div class="wa-empty">
            <i class="bi bi-chat-dots"></i>
            <div style="font-size:14px;font-weight:600;margin-top:10px;">Belum ada pesan</div>
            <div style="font-size:12px;margin-top:4px;">Mulai obrolan dengan kelas {{ $user->kelas->nama }}</div>
        </div>
    @endif

    <div id="msgList">
        @foreach($messages as $msg)
            @php
                $isOwn = $msg->user_id === $user->id;
                $dateLabel = $msg->created_at->isToday() ? 'Hari ini' : $msg->created_at->format('d M Y');
                $showDate = $dateLabel !== $lastDate;
                $sameUser = $msg->user_id === $lastUserId;
                $lastDate = $dateLabel;
                $lastUserId = $msg->user_id;
            @endphp

            @if($showDate)
                <div class="wa-date-sep"><span>{{ $dateLabel }}</span></div>
            @endif

            <div class="wa-msg-row {{ $isOwn ? 'own' : '' }} {{ $sameUser ? 'continued' : 'first' }}">
                @if(!$isOwn)
                    <div class="wa-msg-avatar {{ $sameUser ? 'hidden' : '' }}">
                        @if($msg->user->foto)
                            <img src="{{ asset('storage/'.$msg->user->foto) }}">
                        @else
                            {{ strtoupper(substr($msg->user->name, 0, 1)) }}
                        @endif
                    </div>
                @endif
                <div>
                    @if(!$isOwn && !$sameUser)
                        <div class="wa-bubble-name {{ $msg->user->role === 'guru' ? 'role-guru' : 'role-siswa' }}">
                    {{ $msg->user->name }}
                            @if($msg->user->role === 'guru')
                                <span style="font-size:9px;background:#075e54;color:#fff;padding:1px 5px;border-radius:4px;margin-left:4px;font-weight:600;">GURU</span>
                            @endif
                        </div>
                    @endif
                    <div class="wa-bubble {{ $isOwn ? 'own' : 'other' }}">
                        <div style="white-space:pre-wrap;">{{ $msg->pesan }}</div>
                        <div class="wa-bubble-footer">
                            <span class="wa-bubble-time">{{ $msg->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Emoji Panel --}}
<div class="wa-emoji-panel" id="emojiPanel">
    <div class="wa-emoji-grid">
        @php $emojis = ['😊','😂','❤️','👍','🙌','🔥','👏','✨','🎉','🤔','😢','😍','😎','🙏','💯','📚','🎓','👋','🤝','💪','🎨','⚽','🍕','☕','🌈','🌟','🎂','📝','✅','⭐','🏆','💡']; @endphp
        @foreach($emojis as $e)
            <div class="wa-emoji-item" onclick="insertEmoji('{{ $e }}')">{{ $e }}</div>
        @endforeach
    </div>
</div>

{{-- Footer --}}
<div class="wa-footer" id="chatFooter">
    <button type="button" class="wa-emoji-btn" onclick="toggleEmoji()">😊</button>
    <div class="wa-input-wrap">
        <textarea class="wa-input" id="chatInput" rows="1" placeholder="Ketik pesan..." autocomplete="off"></textarea>
    </div>
    <button type="button" class="wa-send-btn" id="sendBtn" onclick="kirimPesan()">
        <i class="bi bi-send-fill"></i>
    </button>
</div>

<script>
var csrfToken = '{{ csrf_token() }}';
var lastMsgId = {{ $messages->last()->id ?? 0 }};
var myId = {{ $user->id }};
var pollUrl = '{{ route("chat.poll") }}';
var sendUrl = '{{ route("chat.store") }}';
var scrollLocked = true;

var chatBody = document.getElementById('chatBody');
var msgList = document.getElementById('msgList');
var chatInput = document.getElementById('chatInput');

// Scroll to bottom on load
setTimeout(function() { chatBody.scrollTop = chatBody.scrollHeight; }, 100);

// Auto-resize textarea
chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
});

// Enter to send (Shift+Enter for newline)
chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        kirimPesan();
    }
});

function toggleEmoji() {
    document.getElementById('emojiPanel').classList.toggle('open');
}

function insertEmoji(e) {
    chatInput.value += e;
    chatInput.focus();
}

function kirimPesan() {
    var text = chatInput.value.trim();
    if (!text) return;

    chatInput.value = '';
    chatInput.style.height = 'auto';
    document.getElementById('emojiPanel').classList.remove('open');

    var fd = new FormData();
    fd.append('pesan', text);
    fd.append('_token', csrfToken);

    fetch(sendUrl, {
        method: 'POST',
        headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken},
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            appendMessage(data.message, true);
            lastMsgId = data.message.id;
            scrollBottom();
        }
    })
    .catch(function() {});
}

function appendMessage(msg, isOwn) {
    var isOwn = msg.user.id == myId;
    var list = document.getElementById('msgList');
    var lastRow = list.lastElementChild;
    var sameUser = lastRow && lastRow.dataset && lastRow.dataset.uid == msg.user.id;

    var row = document.createElement('div');
    row.className = 'wa-msg-row ' + (isOwn ? 'own' : '') + (sameUser ? ' continued' : ' first');
    row.dataset.uid = msg.user.id;

    var inner = '';

    if (!isOwn) {
        var avatarContent = msg.user.foto
            ? '<img src="' + msg.user.foto + '">'
            : msg.user.initial;
        inner += '<div class="wa-msg-avatar' + (sameUser ? ' hidden' : '') + '">' + avatarContent + '</div>';
    }

    inner += '<div>';

    if (!isOwn && !sameUser) {
        var nameClass = msg.user.role === 'guru' ? 'role-guru' : 'role-siswa';
        var badge = msg.user.role === 'guru' ? ' <span style="font-size:9px;background:#075e54;color:#fff;padding:1px 5px;border-radius:4px;margin-left:4px;font-weight:600;">GURU</span>' : '';
        inner += '<div class="wa-bubble-name ' + nameClass + '">' + escHtml(msg.user.name) + badge + '</div>';
    }

    inner += '<div class="wa-bubble ' + (isOwn ? 'own' : 'other') + '">';
    inner += '<div style="white-space:pre-wrap;">' + escHtml(msg.pesan) + '</div>';
    inner += '<div class="wa-bubble-footer"><span class="wa-bubble-time">' + msg.created_at + '</span></div>';
    inner += '</div></div>';

    row.innerHTML = inner;
    list.appendChild(row);
}

function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

function scrollBottom() {
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Poll for new messages every 3 seconds
setInterval(function() {
    fetch(pollUrl + '?after=' + lastMsgId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.messages && data.messages.length > 0) {
            var wasAtBottom = chatBody.scrollHeight - chatBody.scrollTop - chatBody.clientHeight < 80;
            for (var i = 0; i < data.messages.length; i++) {
                appendMessage(data.messages[i], false);
                lastMsgId = data.messages[i].id;
            }
            if (wasAtBottom) scrollBottom();
        }
    })
    .catch(function() {});
}, 3000);
</script>
@endsection
