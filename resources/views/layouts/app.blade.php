<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin | App Mahasiswa' }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --navy: #0f172a;
            --blue: #246bfe;
            --blue-soft: #e8f0fe;
            --surface: #f8fafc;
            --muted: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            background: var(--surface);
            color: var(--navy);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .admin-nav {
            background: var(--navy);
            min-height: 72px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .brand-mark {
            width: 40px; height: 40px; border-radius: 12px;
            background: linear-gradient(135deg, var(--blue), #60a5fa);
            display: grid; place-items: center; font-weight: 800; color: #fff;
            box-shadow: 0 4px 12px rgba(36,107,254,0.3);
        }

        .admin-nav .nav-link {
            color: #94a3b8; font-size: 14px; font-weight: 500;
            padding: 0.6rem 1rem; border-radius: 10px; transition: all 0.2s;
        }

        .admin-nav .nav-link:hover, .admin-nav .nav-link.active {
            background: rgba(255,255,255,0.08); color: #fff;
        }

        .admin-container { max-width: 1280px; margin: 0 auto; padding: 0 1.5rem; }

        .card {
            border: 0; border-radius: var(--radius); background: #fff;
            box-shadow: var(--shadow); transition: box-shadow 0.3s ease;
        }
        .card:hover { box-shadow: var(--shadow-lg); }

        .btn-primary {
            background: var(--blue); border: 0; border-radius: 12px;
            padding: 0.6rem 1.25rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }

        .admin-footer { color: var(--muted); font-size: 13px; font-weight: 500; }

        @media(max-width:767px) {
            .admin-container { padding-left: 1rem; padding-right: 1rem; }
            .admin-nav .navbar-brand { font-size: 1.1rem; }
            #portal-toast { width: 92% !important; right: 4% !important; left: 4% !important; }
        }

        /* Toast UI Refinement */
        #portal-toast {
            position: fixed; top: 24px; right: 24px; width: 360px; z-index: 10000;
            background: #fff; border-radius: 18px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            border: 1px solid var(--border); border-left: 6px solid var(--blue); padding: 16px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg admin-nav navbar-dark">
    <div class="container admin-container">
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="{{ route('dashboard') }}">
            <div class="brand-mark">A</div>
            <span>App Mahasiswa</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminMenu">
            <div class="navbar-nav ms-lg-4 me-auto">
                @if(session('user_role') === 'admin')
                    @if(session('is_super_admin'))
                        {{-- Admin pusat: hanya menu global, menu sekolah disembunyikan --}}
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                        <a class="nav-link {{ request()->routeIs('global.portal*') ? 'active' : '' }}" href="{{ route('global.portal') }}">Global Portal</a>
                    @else
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">Pengumuman</a>
                    <a class="nav-link {{ request()->routeIs('spp.*') ? 'active' : '' }}" href="{{ route('spp.index') }}">SPP</a>
                    <a class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a>
                    <a class="nav-link {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}" href="{{ route('admin.jadwal.index') }}"><i class="bi bi-calendar3 me-1"></i>Jadwal</a>
                    <a class="nav-link {{ request()->routeIs('admin.perpustakaan.*') ? 'active' : '' }}" href="{{ route('admin.perpustakaan.index') }}">Perpustakaan</a>
                    <a class="nav-link {{ request()->routeIs('admin.eskul.*') ? 'active' : '' }}" href="{{ route('admin.eskul.index') }}">Eskul</a>
                    <a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">Akun</a>
                     <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">Pengaturan</a>
                     <a class="nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}" href="{{ route('admin.history') }}"><i class="bi bi-clock-history me-1"></i>Riwayat</a>
                    @endif
                @else
                    <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link" href="{{ route('pengumuman.index') }}">Pengumuman</a>
                    <a class="nav-link" href="{{ route('tugas.index') }}">Tugas</a>
                @endif
            </div>
            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <div class="d-flex flex-column text-end d-none d-lg-flex">
                    <span class="text-white small fw-bold">{{ session('admin_name') }}</span>
                    <span class="text-muted small" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Administrator</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-light rounded-pill px-3">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none;">
    <a id="toast-link" href="#" style="display:block; text-decoration:none; color:inherit;">
        <div class="d-flex align-items-center gap-3">
            <div id="toast-icon" style="width: 44px; height: 44px; background: var(--blue-soft); border-radius: 12px; display: grid; place-items: center; color: var(--blue); flex-shrink:0;">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div style="flex: 1; min-width:0;">
                <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px;">Notifikasi</div>
                <div id="toast-msg" class="text-muted" style="font-size: 13px; line-height:1.4;">Ada pesan baru untuk Anda.</div>
            </div>
            <button type="button" class="btn-close btn-close-sm" onclick="event.preventDefault();event.stopPropagation();document.getElementById('portal-toast').style.display='none';" style="flex-shrink:0;"></button>
        </div>
    </a>
</div>

<audio id="notif-sound" src="{{ asset('sounds/doorbell.mp3') }}" preload="auto"></audio>

<main class="container admin-container py-5">
    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm animate__animated animate__fadeIn">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 rounded-4 shadow-sm animate__animated animate__fadeIn">
            <i class="bi bi-exclamation-circle-fill me-2"></i> {{ session('error') }}
        </div>
    @endif
    @yield('content')
</main>

<footer class="container admin-container pb-5 text-center">
    <div class="admin-footer">
        App Mahasiswa &bull; Panel Administrasi Terintegrasi &bull; &copy; {{ date('Y') }}
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var portalToastEl = document.getElementById('portal-toast');
    var toastTimer = null;
    var lastSoundAt = 0;

    function playNotifSound() {
        var now = Date.now();
        var snd = document.getElementById('notif-sound');
        if (snd && (now - lastSoundAt) > 1500) {
            lastSoundAt = now;
            snd.currentTime = 0;
            snd.play().catch(e => {});
        }
    }

    function showNotification(title, message, url) {
        document.getElementById('toast-title').innerText = title || 'Notifikasi';
        document.getElementById('toast-msg').innerText = message || '';
        document.getElementById('toast-link').setAttribute('href', url || '#');
        playNotifSound();
        portalToastEl.style.display = 'block';
        portalToastEl.classList.remove('animate__fadeOutUp');
        portalToastEl.classList.add('animate__fadeInDown');
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            portalToastEl.classList.remove('animate__fadeInDown');
            portalToastEl.classList.add('animate__fadeOutUp');
            setTimeout(() => { portalToastEl.style.display = 'none'; }, 500);
        }, 6000);
    }

    // ===== Sesi realtime + Notifikasi langsung (seperti aplikasi native) =====
    (function () {
        var SESSION_URL = "{{ route('session.status') }}";
        var POLL_URL = "{{ route('notifications.poll') }}";
        var LOGIN_URL = "{{ route('login') }}";
        var MAX_RETRY = 3;
        var lastId = 0;
        var bootstrapped = false;
        var offlineRetry = 0;
        var stdout = null;

        function updateUnreadBadges(count) {
            document.querySelectorAll('[data-live-unread]').forEach(function (el) {
                var num = document.getElementById(el.getAttribute('data-live-unread'));
                if (!num) return;
                num.innerText = count > 99 ? '99+' : String(count);
                num.style.display = (count > 0) ? 'grid' : 'none';
            });
            document.querySelectorAll('[data-live-dot]').forEach(function (h) {
                h.innerText = count > 99 ? '99+' : String(count);
                h.style.display = (count > 0) ? 'grid' : 'none';
            });
        }

        function pollNotifications() {
            fetch(POLL_URL + '?last_id=' + lastId + '&t=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { if (!r.ok) throw new Error('poll'); return r.json(); })
                .then(function (d) {
                    offlineRetry = 0;
                    updateUnreadBadges(d.unread);

                    // Bootstrap: jadikan titik resume = notif terbaru, jangan
                    // popup/bunyi ulang notifikasi histori yang sudah ada.
                    if (!bootstrapped) {
                        bootstrapped = true;
                        lastId = (d.latest_id || 0);
                        return;
                    }

                    if (d.new_last_id && d.new_last_id > lastId) {
                        (d.items || []).forEach(function (it) { showNotification(it.judul, it.pesan, it.url || '#'); });
                        lastId = d.new_last_id;
                    }
                })
                .catch(function () { offlineRetry++; if (offlineRetry > MAX_RETRY) stopHeartbeat(); });
        }

        function heartbeat() {
            fetch(SESSION_URL + '?t=' + Date.now(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (!d.authenticated) { window.location.href = d.redirect || LOGIN_URL; return; }
                    updateUnreadBadges(d.unread);
                })
                .catch(function () {});
        }

        function startHeartbeat() {
            if (stdout) return;
            heartbeat();
            stdout = setInterval(heartbeat, 60000);
            pollNotifications();
            setInterval(pollNotifications, 15000);
        }
        function stopHeartbeat() { if (stdout) { clearInterval(stdout); stdout = null; } }

        if (window.Echo) {
            try {
                window.Echo.private('portal-notifications.' + @json((int) session('user_id')))
                    .listen('.new-notification', function (e) {
                        showNotification(e.title || 'Notifikasi', e.message || '', '#');
                        document.querySelectorAll('[data-live-dot]').forEach(function (h) { h.style.display = 'block'; });
                    });
            } catch (e) {}
        }
        startHeartbeat();
    })();
</script>
</body>
</html>
