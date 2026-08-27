@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.9); backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(15,23,42,0.06);
        padding: 12px 15px; display: flex; align-items: center; gap: 13px;
    }
    .chat-header .back-btn {
        width: 38px; height: 38px; border-radius: 13px; flex-shrink: 0;
        background: #f1f5f9; display: flex; align-items: center; justify-content: center;
        color: #334155; text-decoration: none; font-size: 18px;
    }
    .chat-header-avatar {
        width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;
        overflow: hidden; background: linear-gradient(135deg,#4f46e5,#7c3aed);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:800; font-size:15px;
        box-shadow: 0 5px 14px rgba(79,70,229,0.3);
    }
    .chat-header-avatar img { width:100%; height:100%; object-fit:cover; }
    .chat-online-dot { display:inline-block; width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 0 3px rgba(34,197,94,0.2); }

    .chat-container {
        padding-top: 74px; padding-bottom: 90px; min-height: 100vh;
        background:
            radial-gradient(1200px 400px at 50% -50px, rgba(99,102,241,0.07), transparent 70%),
            #f7f8fc;
        display: flex; flex-direction: column;
    }
    .chat-thread { display: flex; flex-direction: column; padding: 6px 14px; }

    .chat-row { display: flex; align-items: flex-end; gap: 9px; margin-bottom: 10px; }
    .chat-row.mine { flex-direction: row-reverse; }

    .chat-avatar {
        width: 30px; height: 30px; border-radius: 10px; flex-shrink: 0;
        overflow: hidden; background: linear-gradient(135deg,#e0e7ff,#c7d2fe);
        display:flex; align-items:center; justify-content:center;
        color:#4f46e5; font-size:12px; font-weight:800;
    }
    .chat-avatar img { width:100%; height:100%; object-fit:cover; }
    .chat-row.mine .chat-avatar { display:none; }

    .chat-bubble-wrap { max-width: 78%; display: flex; flex-direction: column; }
    .chat-sender {
        font-size: 10.5px; font-weight: 800; color: #6366f1;
        margin: 0 4px 4px; letter-spacing: 0.01em;
    }
    .chat-row.mine .chat-sender { display:none; }

    .chat-bubble {
        padding: 10px 14px; font-size: 14px; line-height: 1.5;
        border-radius: 18px; position: relative; word-break: break-word;
    }
    .chat-row.other .chat-bubble {
        background: #fff; color: #14213d;
        border-top-left-radius: 6px; border: 1px solid rgba(15,23,42,0.05);
        box-shadow: 0 4px 14px rgba(15,23,42,0.06);
    }
    .chat-row.mine .chat-bubble {
        background: linear-gradient(135deg,#4f46e5,#6366f1); color: #fff;
        border-top-right-radius: 6px;
        box-shadow: 0 6px 16px rgba(79,70,229,0.35);
    }
    .chat-time {
        font-size: 10px; opacity: 0.6; margin-top: 4px; font-weight: 600;
    }
    .chat-row.mine .chat-time { text-align: right; }

    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
        background: rgba(255,255,255,0.94); backdrop-filter: blur(16px);
        border-top: 1px solid rgba(15,23,42,0.06);
        padding: 10px 15px; display: flex; flex-direction: column;
    }
    .composer-row { display: flex; align-items: center; gap: 10px; }
    .composer-input {
        flex: 1; border: none; background: #f1f4f9;
        border-radius: 25px; padding: 12px 18px; font-size: 14px;
        box-shadow: inset 0 0 0 1px rgba(15,23,42,0.04);
    }
    .composer-input:focus { outline: none; background: #e8ecf3; }
    .action-btn {
        width: 42px; height: 42px; border-radius: 50%; border: none;
        background: transparent; color: #64748b; display: grid; place-items: center;
        transition: all 0.2s;
    }
    .action-btn:active { background: #f1f4f9; transform: scale(0.9); }
    .send-btn { background: linear-gradient(135deg,#4f46e5,#6366f1); color: #fff; box-shadow: 0 6px 14px rgba(79,70,229,0.35); }

    /* Emoji & GIF Picker UI */
    #extra-panel {
        display: none; padding: 15px; background: #f8fafc;
        border-radius: 15px 15px 0 0; border-bottom: 1px solid #edf2f7;
    }
    .panel-tab {
        display: flex; gap: 20px; margin-bottom: 15px; border-bottom: 2px solid #edf2f7;
    }
    .tab-item {
        padding-bottom: 8px; font-weight: bold; cursor: pointer; color: #64748b;
    }
    .tab-item.active { color: #4f46e5; border-bottom: 2px solid #4f46e5; margin-bottom: -2px; }

    #emoji-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; max-height: 180px; overflow-y: auto; }
    #gif-grid { display: none; grid-template-columns: repeat(2, 1fr); gap: 10px; max-height: 180px; overflow-y: auto; }
    .gif-item { width: 100%; height: 80px; border-radius: 8px; background: #e2e8f0; object-fit: cover; cursor: pointer; }
    .emoji-item { font-size: 22px; cursor: pointer; text-align: center; }
</style>

<div class="chat-header">
    <a href="{{ route('dashboard') }}" class="back-btn"><i class="bi bi-chevron-left"></i></a>
    <div class="chat-header-avatar" id="chatHeaderAvatar">
        @if($user->foto)
            <img src="{{ asset('storage/'.$user->foto) }}">
        @else
            {{ strtoupper(substr($user->name, 0, 1)) }}
        @endif
    </div>
    <div>
        <div class="fw-bold" style="font-size: 15px;">Grup {{ $user->kelas->nama }}</div>
        <div class="d-flex align-items-center gap-2" style="font-size: 11px; color:#7c8794;font-weight:600;">
            <span class="chat-online-dot"></span> Online
        </div>
    </div>
</div>

<div class="chat-container">
    <div class="text-center py-4">
        <span class="badge rounded-pill px-3 py-2" style="font-size: 10px; background:rgba(15,23,42,0.05); color:#64748b; font-weight:600;">
            Obrolan Kelas {{ $user->kelas->nama }}
        </span>
    </div>

    <div class="chat-thread" id="message-list">
        @forelse($messages as $message)
            @php $isMine = $message->user_id === $user->id; @endphp
            <div class="chat-row {{ $isMine ? 'mine' : 'other' }}">
                <div class="chat-avatar">
                    @if($message->user && $message->user->foto)
                        <img src="{{ asset('storage/'.$message->user->foto) }}">
                    @else
                        {{ $message->user ? strtoupper(substr($message->user->name, 0, 1)) : '?' }}
                    @endif
                </div>
                <div class="chat-bubble-wrap">
                    @if(!$isMine)
                        <div class="chat-sender">{{ $message->user?->name }}</div>
                    @endif
                    <div class="chat-bubble">
                        <div style="white-space: pre-wrap;">{{ $message->pesan }}</div>
                        <div class="chat-time">{{ $message->created_at->format('H:i') }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted small">Belum ada percakapan. Mulai obrolan!</div>
        @endforelse
    </div>
</div>

<form method="POST" action="{{ route('chat.store') }}" class="chat-footer" id="chatForm">
    @csrf
    <div id="extra-panel">
        <div class="panel-tab">
            <div class="tab-item active" id="tab-emoji">EMOJI</div>
            <div class="tab-item" id="tab-gif">GIF</div>
        </div>
        <div id="emoji-grid">
            @php $emojis = ['😊','😂','❤️','👍','🙌','🔥','👏','✨','🎉','🤔','😢','😍','😎','🙏','💯','📍','🚀','💡','📚','🎓','🎨','⚽','🍕','☕','🌈','🌟','🍦','🍩','🍔','🍟','🧁','🎂']; @endphp
            @foreach($emojis as $emoji)
                <div class="emoji-item">{{ $emoji }}</div>
            @endforeach
        </div>
        <div id="gif-grid">
            <!-- Simulated GIFs -->
            <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExNHJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqJmVwPXYxX2ludGVybmFsX2dpZl9ieV9pZCZjdD1n/3o7TKMGpx4gWlWGA5a/giphy.gif" class="gif-item">
            <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExNHJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqJmVwPXYxX2ludGVybmFsX2dpZl9ieV9pZCZjdD1n/l0MYEqEzwMWFCg8rm/giphy.gif" class="gif-item">
            <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExNHJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqJmVwPXYxX2ludGVybmFsX2dpZl9ieV9pZCZjdD1n/3o7TKVUn7iM8FMEU24/giphy.gif" class="gif-item">
            <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExNHJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqbzJqJmVwPXYxX2ludGVybmFsX2dpZl9ieV9pZCZjdD1n/xT9IgzoKnwFNmISR8I/giphy.gif" class="gif-item">
        </div>
    </div>

    <div class="composer-row">
        <button type="button" class="action-btn" id="extra-toggle"><i class="bi bi-plus-lg h5 mb-0"></i></button>
        <input name="pesan" id="chatInput" autocomplete="off" class="composer-input" placeholder="Ketik pesan..." required>
        <button type="submit" class="action-btn send-btn"><i class="bi bi-send-fill"></i></button>
    </div>
</form>

<script>
    // Scroll to bottom
    const msgList = document.getElementById('message-list');
    msgList.scrollIntoView({ block: 'end' });

    const extraPanel = document.getElementById('extra-panel');
    const extraToggle = document.getElementById('extra-toggle');
    const chatInput = document.getElementById('chatInput');
    const chatForm = document.getElementById('chatForm');
    const tabEmoji = document.getElementById('tab-emoji');
    const tabGif = document.getElementById('tab-gif');
    const emojiGrid = document.getElementById('emoji-grid');
    const gifGrid = document.getElementById('gif-grid');

    const currentUserId = @json((int) $user->id);
    const kelasId = @json((int) $user->kelas_id);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function appendMessage(data, mine) {
        const row = document.createElement('div');
        row.className = 'chat-row ' + (mine ? 'mine' : 'other');

        const avatar = document.createElement('div');
        avatar.className = 'chat-avatar';
        if (data.foto) {
            avatar.innerHTML = '<img src="' + escAttr(data.foto) + '">';
        } else {
            avatar.textContent = (data.nama || '?').charAt(0).toUpperCase();
        }

        const wrap = document.createElement('div');
        wrap.className = 'chat-bubble-wrap';

        let inner = '';
        if (!mine) {
            inner += '<div class="chat-sender">' + esc(data.nama || 'Pengguna') + '</div>';
        }
        inner += '<div class="chat-bubble"><div style="white-space:pre-wrap;">' + esc(data.pesan) + '</div>';
        inner += '<div class="chat-time">' + (data.waktu || '') + '</div></div>';
        wrap.innerHTML = inner;

        row.appendChild(avatar);
        row.appendChild(wrap);
        msgList.appendChild(row);
        msgList.scrollIntoView({ block: 'end' });
    }

    function escAttr(text) {
        return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function esc(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Kirim via fetch tanpa reload halaman
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const pesan = chatInput.value.trim();
        if (!pesan) return;

        const body = new URLSearchParams();
        body.append('pesan', pesan);
        body.append('_token', csrfToken);

        fetch(chatForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function () {
            chatInput.value = '';
            chatInput.focus();
        }).catch(function () {
            window.location.href = chatForm.action;
        });
    });

    // Real-time: pesan masuk dari pengguna lain di kelas
    if (window.Echo) {
        window.Echo.private('portal-chat.' + kelasId)
            .listen('.new-message', (e) => {
                if (e.user_id !== currentUserId) {
                    appendMessage({ nama: e.nama, foto: e.foto, pesan: e.pesan, waktu: e.waktu }, false);
                }
            });
    }

    extraToggle.addEventListener('click', () => {
        extraPanel.style.display = extraPanel.style.display === 'block' ? 'none' : 'block';
    });

    tabEmoji.addEventListener('click', () => {
        tabEmoji.classList.add('active');
        tabGif.classList.remove('active');
        emojiGrid.style.display = 'grid';
        gifGrid.style.display = 'none';
    });

    tabGif.addEventListener('click', () => {
        tabGif.classList.add('active');
        tabEmoji.classList.remove('active');
        emojiGrid.style.display = 'none';
        gifGrid.style.display = 'grid';
    });

    document.querySelectorAll('.emoji-item').forEach(item => {
        item.addEventListener('click', () => {
            chatInput.value += item.innerText;
            chatInput.focus();
        });
    });

    document.querySelectorAll('.gif-item').forEach(item => {
        item.addEventListener('click', () => {
            chatInput.value += "[GIF: " + item.src + "]";
            chatInput.focus();
            extraPanel.style.display = 'none';
        });
    });
</script>
@endsection
