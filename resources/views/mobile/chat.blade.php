@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
<style>
    .chat-header {
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-bottom: 1px solid #edf2f7;
        padding: 12px 15px; display: flex; align-items: center; gap: 15px;
    }
    .chat-container {
        padding-top: 70px; padding-bottom: 85px; min-height: 100vh;
        background: #fdfdfd; display: flex; flex-direction: column;
    }
    .chat-bubble {
        max-width: 85%; padding: 10px 14px; border-radius: 18px;
        font-size: 14px; line-height: 1.5; margin-bottom: 8px;
        position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    }
    .chat-bubble.mine {
        background: #246bfe; color: #fff; align-self: flex-end;
        border-bottom-right-radius: 4px;
    }
    .chat-bubble.other {
        background: #fff; color: #14213d; align-self: flex-start;
        border-bottom-left-radius: 4px; border: 1px solid #f0f0f0;
    }
    .chat-footer {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;
        background: #fff; border-top: 1px solid #edf2f7;
        padding: 10px 15px; display: flex; flex-direction: column;
    }
    .composer-row { display: flex; align-items: center; gap: 10px; }
    .composer-input {
        flex: 1; border: none; background: #f1f4f9;
        border-radius: 25px; padding: 10px 18px; font-size: 14px;
    }
    .composer-input:focus { outline: none; background: #e8ecf3; }
    .action-btn {
        width: 40px; height: 40px; border-radius: 50%; border: none;
        background: transparent; color: #64748b; display: grid; place-items: center;
        transition: all 0.2s;
    }
    .action-btn:active { background: #f1f4f9; transform: scale(0.9); }
    .send-btn { background: #246bfe; color: #fff; }

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
    .tab-item.active { color: #246bfe; border-bottom: 2px solid #246bfe; margin-bottom: -2px; }

    #emoji-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; max-height: 180px; overflow-y: auto; }
    #gif-grid { display: none; grid-template-columns: repeat(2, 1fr); gap: 10px; max-height: 180px; overflow-y: auto; }
    .gif-item { width: 100%; height: 80px; border-radius: 8px; background: #e2e8f0; object-fit: cover; cursor: pointer; }
    .emoji-item { font-size: 22px; cursor: pointer; text-align: center; }
</style>

<div class="chat-header">
    <a href="{{ route('dashboard') }}" class="text-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/></svg>
    </a>
    <div class="d-flex align-items-center gap-2">
        <div class="avatar" style="width:36px; height:36px; font-size: 14px; background: var(--blue); color: #fff;">GC</div>
        <div>
            <div class="fw-bold" style="font-size: 15px;">Grup Chat</div>
            <div class="text-success small fw-bold" style="font-size: 11px;">{{ $user->kelas->nama }} · Aktif</div>
        </div>
    </div>
</div>

<div class="chat-container px-3">
    <div class="text-center py-4">
        <span class="badge bg-light text-muted fw-normal rounded-pill px-3 py-2" style="font-size: 10px;">Obrolan Kelas {{ $user->kelas->nama }}</span>
    </div>

    <div class="d-flex flex-column" id="message-list">
        @forelse($messages as $message)
            <div class="chat-bubble {{ $message->user_id === $user->id ? 'mine' : 'other' }}">
                @if($message->user_id !== $user->id)
                    <div class="fw-bold mb-1" style="font-size: 11px; color: var(--blue);">{{ $message->user->name }}</div>
                @endif
                <div style="white-space: pre-wrap;">{{ $message->pesan }}</div>
                <div class="text-end mt-1" style="font-size: 10px; opacity: 0.6;">{{ $message->created_at->format('H:i') }}</div>
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
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + (mine ? 'mine' : 'other');

        let inner = '';
        if (!mine) {
            inner += '<div class="fw-bold mb-1" style="font-size: 11px; color: var(--blue);">' + (data.nama || 'Pengguna') + '</div>';
        }
        inner += '<div style="white-space: pre-wrap;">' + esc(data.pesan) + '</div>';
        inner += '<div class="text-end mt-1" style="font-size: 10px; opacity: 0.6;">' + (data.waktu || '') + '</div>';

        bubble.innerHTML = inner;
        msgList.appendChild(bubble);
        msgList.scrollIntoView({ block: 'end' });
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
                    appendMessage({ nama: e.nama, pesan: e.pesan, waktu: e.waktu }, false);
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
