<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin | App Mahasiswa' }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    @vite(['resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root{--navy:#14213d;--blue:#246bfe;--surface:#f4f7fb;--muted:#6d7a90}
        body{background:var(--surface);color:var(--navy);font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.admin-nav{background:var(--navy);min-height:68px}.brand-mark{width:38px;height:38px;border-radius:12px;background:var(--blue);display:grid;place-items:center;font-weight:800}.admin-nav .nav-link{color:#c8d2e4;font-size:14px;padding:.55rem .75rem;border-radius:8px}.admin-nav .nav-link:hover,.admin-nav .nav-link.active{background:#ffffff18;color:#fff}.admin-container{max-width:1240px}.page-heading{letter-spacing:-.02em}.stat-card,.table-card,.form-card{border:0;border-radius:14px;box-shadow:0 5px 20px #14213d0d}.stat-card{border-top:3px solid var(--blue)}.metric-label{color:var(--muted);font-size:12px}.metric-value{font-size:26px;font-weight:800}.table thead th{font-size:11px;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:700;border-bottom-width:1px}.table td{font-size:14px}.admin-footer{color:var(--muted);font-size:12px}@media(max-width:767px){.admin-nav .navbar-collapse{padding-top:12px}.admin-container{padding-left:16px;padding-right:16px}.table-card{overflow-x:auto}}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg admin-nav navbar-dark"><div class="container admin-container"><a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}"><span class="brand-mark">A</span><span>App Mahasiswa</span></a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-controls="adminMenu" aria-expanded="false" aria-label="Buka menu"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="adminMenu"><div class="navbar-nav ms-lg-4 me-auto">@if(session('user_role') === 'admin')<a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">Pengumuman</a><a class="nav-link {{ request()->routeIs('spp.*') ? 'active' : '' }}" href="{{ route('spp.index') }}">SPP</a><a class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a><a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">Akun</a><a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">Pengaturan</a>@else<a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link" href="{{ route('pengumuman.index') }}">Pengumuman</a><a class="nav-link" href="{{ route('tugas.index') }}">Tugas</a>@endif</div><div class="d-flex align-items-center gap-3 mt-3 mt-lg-0"><span class="text-white small">{{ session('admin_name') }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Keluar</button></form></div></div></div></nav>

<!-- Global Notification UI -->
<div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none; position:fixed; top:20px; right:20px; width: 350px; z-index: 10000; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-left: 5px solid #246bfe; padding: 15px;">
    <div class="d-flex align-items-center gap-3">
        <div id="toast-icon" style="width: 40px; height: 40px; background: #e8f0fe; border-radius: 10px; display: grid; place-items: center; color: #246bfe;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/></svg>
        </div>
        <div style="flex: 1;">
            <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px;">Notifikasi</div>
            <div id="toast-msg" class="text-muted" style="font-size: 13px;">Ada pesan baru untuk Anda.</div>
        </div>
        <button onclick="document.getElementById('portal-toast').style.display='none'" class="btn-close btn-close-sm"></button>
    </div>
</div>

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<script>
    function showNotification(title, message) {
        const toast = document.getElementById('portal-toast');
        document.getElementById('toast-title').innerText = title;
        document.getElementById('toast-msg').innerText = message;
        document.getElementById('notif-sound').play().catch(e => console.log("Sound interaction required"));

        toast.style.display = 'block';
        setTimeout(() => {
            toast.classList.remove('animate__fadeInDown');
            toast.classList.add('animate__fadeOutUp');
            setTimeout(() => {
                toast.style.display = 'none';
                toast.classList.remove('animate__fadeOutUp');
                toast.classList.add('animate__fadeInDown');
            }, 500);
        }, 5000);
    }

    window.addEventListener('load', () => {
        if (window.Echo) {
            const userId = @json((int) session('user_id'));
            window.Echo.private('portal-notifications.' + userId)
                .listen('.new-notification', (e) => {
                    showNotification(e.title, e.message);

                    // Permission Check
                    if (Notification.permission === 'default') {
                        Notification.requestPermission();
                    }
                });
        }
    });
</script>
<main class="container admin-container py-4">@if(session('success'))<div class="alert alert-success border-0">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-danger border-0">{{ session('error') }}</div>@endif @if($errors->any())<div class="alert alert-danger border-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</main><footer class="container admin-container pb-4 admin-footer">App Mahasiswa · Panel administrasi sekolah</footer><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
</body>
</html>
