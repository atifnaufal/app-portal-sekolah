<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin | App Mahasiswa' }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{--navy:#14213d;--blue:#246bfe;--surface:#f4f7fb;--muted:#6d7a90}
        body{background:var(--surface);color:var(--navy);font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.admin-nav{background:var(--navy);min-height:68px}.brand-mark{width:38px;height:38px;border-radius:12px;background:var(--blue);display:grid;place-items:center;font-weight:800}.admin-nav .nav-link{color:#c8d2e4;font-size:14px;padding:.55rem .75rem;border-radius:8px}.admin-nav .nav-link:hover,.admin-nav .nav-link.active{background:#ffffff18;color:#fff}.admin-container{max-width:1240px}.page-heading{letter-spacing:-.02em}.stat-card,.table-card,.form-card{border:0;border-radius:14px;box-shadow:0 5px 20px #14213d0d}.stat-card{border-top:3px solid var(--blue)}.metric-label{color:var(--muted);font-size:12px}.metric-value{font-size:26px;font-weight:800}.table thead th{font-size:11px;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:700;border-bottom-width:1px}.table td{font-size:14px}.admin-footer{color:var(--muted);font-size:12px}@media(max-width:767px){.admin-nav .navbar-collapse{padding-top:12px}.admin-container{padding-left:16px;padding-right:16px}.table-card{overflow-x:auto}}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg admin-nav navbar-dark"><div class="container admin-container"><a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}"><span class="brand-mark">A</span><span>App Mahasiswa</span></a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-controls="adminMenu" aria-expanded="false" aria-label="Buka menu"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="adminMenu"><div class="navbar-nav ms-lg-4 me-auto">@if(session('user_role') === 'admin')<a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link {{ request()->routeIs('pengumuman.*') ? 'active' : '' }}" href="{{ route('pengumuman.index') }}">Pengumuman</a><a class="nav-link {{ request()->routeIs('spp.*') ? 'active' : '' }}" href="{{ route('spp.index') }}">SPP</a><a class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">Kelas</a><a class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">Akun</a><a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">Pengaturan</a>@else<a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link" href="{{ route('pengumuman.index') }}">Pengumuman</a><a class="nav-link" href="{{ route('tugas.index') }}">Tugas</a>@endif</div><div class="d-flex align-items-center gap-3 mt-3 mt-lg-0"><span class="text-white small">{{ session('admin_name') }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Keluar</button></form></div></div></div></nav>
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
