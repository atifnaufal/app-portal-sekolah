{{-- cPanel sidebar: include dengan @include('admin.partials.sidebar') di dalam @section('content').
     CSS di-scope ke .cp-shell agar tidak bentrok dengan style halaman lain. --}}
<style>
    .cp-shell { display: flex; gap: 24px; align-items: flex-start; }
    .cp-main { flex: 1; min-width: 0; }
    .admin-cp-sidebar {
        width: 250px; flex-shrink: 0; position: sticky; top: 24px;
        background: var(--navy); border-radius: 20px; padding: 20px 14px;
        display: flex; flex-direction: column; gap: 14px; max-height: calc(100vh - 48px);
    }
    .admin-cp-sidebar-brand { display: flex; align-items: center; padding: 4px 8px 12px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .admin-cp-sidebar-nav { display: flex; flex-direction: column; gap: 2px; overflow-y: auto; }
    .admin-cp-sidebar-nav a {
        display: flex; align-items: center; gap: 12px; padding: 10px 12px;
        border-radius: 12px; color: #94a3b8; font-size: 13.5px; font-weight: 600;
        text-decoration: none; transition: all .2s;
    }
    .admin-cp-sidebar-nav a i { font-size: 16px; width: 22px; text-align: center; }
    .admin-cp-sidebar-nav a:hover { background: rgba(255,255,255,.07); color: #fff; }
    .admin-cp-sidebar-nav a.active { background: var(--blue); color: #fff; box-shadow: 0 4px 12px rgba(36,107,254,.35); }
    .sidebar-section-title { font-size: 10px; font-weight: 800; letter-spacing: .1em; color: #475569; padding: 14px 12px 6px; }
    .admin-cp-sidebar-footer { border-top: 1px solid rgba(255,255,255,.08); padding-top: 12px; margin-top: auto; }
    .admin-cp-avatar {
        width: 36px; height: 36px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--blue), #60a5fa);
        display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 14px;
    }
    .cp-toggle-btn { display: none; }
    @media (max-width: 991px) {
        .cp-shell { flex-direction: column; }
        .admin-cp-sidebar {
            width: 100%; position: static; max-height: none;
        }
        .admin-cp-sidebar.collapsed .admin-cp-sidebar-nav,
        .admin-cp-sidebar.collapsed .admin-cp-sidebar-footer { display: none; }
        .admin-cp-sidebar-brand { border-bottom: 0; padding-bottom: 0; cursor: pointer; }
        .cp-toggle-btn { display: inline-flex; margin-left: auto; }
    }
</style>
@php
$adminName = (string) (session('admin_name') ?? 'Admin');
$isSuper = ($isSuperAdmin ?? null) ?? (bool) session('is_super_admin', false);
$sideSchoolId = (int) session('school_id');
@endphp
<aside class="admin-cp-sidebar" id="adminSidebar">
    <div class="admin-cp-sidebar-brand" onclick="document.getElementById('adminSidebar').classList.toggle('collapsed')">
        <div class="brand-mark" style="width:44px;height:44px;border-radius:14px;display:grid;place-items:center;">
            <span style="font-size:18px;font-weight:800;color:#fff;">A</span>
        </div>
        <div class="ms-2">
            <div class="fw-bold text-white" style="font-size:15px;letter-spacing:-0.02em;">Admin Panel</div>
            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:0.08em;">Control Center</div>
        </div>
        <span class="cp-toggle-btn btn btn-sm btn-outline-light ms-auto" style="border-radius:10px;"><i class="bi bi-list"></i></span>
    </div>

    <nav class="admin-cp-sidebar-nav">
        <div class="sidebar-section-title">MAIN</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i><span>Akun Pengguna</span>
        </a>
        <a href="{{ route('admin.schools.index') }}" class="{{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
            <i class="bi bi-buildings-fill"></i><span>Sekolah</span>
        </a>
        @if($isSuper)
        <a href="{{ route('admin.school-admins.index') }}" class="{{ request()->routeIs('admin.school-admins.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i><span>Admin Sekolah</span>
        </a>
        @endif

        <div class="sidebar-section-title">SYSTEM</div>
        <a href="{{ route('admin.features') }}" class="{{ request()->routeIs('admin.features*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i><span>Fitur</span>
        </a>
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i><span>Pengaturan</span>
        </a>
        <a href="{{ route('admin.history') }}" class="{{ request()->routeIs('admin.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i><span>Riwayat</span>
        </a>
        @if($isSuper)
        <a href="{{ route('admin.insights') }}" class="{{ request()->routeIs('admin.insights') ? 'active' : '' }}">
            <i class="bi bi-cpu-fill"></i><span>AI & Terminal</span>
        </a>
        @endif

        <div class="sidebar-section-title">CONTENT</div>
        @if($isSuper)
            {{-- Admin pusat: hanya Global Portal --}}
            <a href="{{ route('global.portal') }}" class="{{ request()->routeIs('global.portal*') ? 'active' : '' }}">
                <i class="bi bi-globe"></i><span>Global Portal</span>
            </a>
        @else
        @if($isSuper || \App\Helpers\FeatureHelper::forSchool($sideSchoolId, 'feature_perpustakaan_enabled'))
        <a href="{{ route('admin.perpustakaan.index') }}" class="{{ request()->routeIs('admin.perpustakaan.*') ? 'active' : '' }}">
            <i class="bi bi-book-fill"></i><span>Perpustakaan</span>
        </a>
        @endif
        @if($isSuper || \App\Helpers\FeatureHelper::forSchool($sideSchoolId, 'feature_eskul_enabled'))
        <a href="{{ route('admin.eskul.index') }}" class="{{ request()->routeIs('admin.eskul.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i><span>Ekstrakurikuler</span>
        </a>
        @endif
        @if($isSuper || \App\Helpers\FeatureHelper::forSchool($sideSchoolId, 'feature_jadwal_enabled'))
        <a href="{{ route('admin.jadwal.index') }}" class="{{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i><span>Jadwal</span>
        </a>
        @endif
        <a href="{{ route('pengumuman.index') }}" class="{{ request()->routeIs('pengumuman.*') ? 'active' : '' }}">
            <i class="bi bi-megaphone-fill"></i><span>Pengumuman</span>
        </a>
        <a href="{{ route('global.portal') }}" class="{{ request()->routeIs('global.portal*') ? 'active' : '' }}">
            <i class="bi bi-globe"></i><span>Global Portal</span>
        </a>
        @endif
    </nav>

    <div class="admin-cp-sidebar-footer">
        <div class="d-flex align-items-center gap-2 px-2">
            <div class="admin-cp-avatar">{{ strtoupper(substr($adminName, 0, 1)) }}</div>
            <div class="flex-fill" style="min-width:0;">
                <div class="text-white small fw-bold text-truncate">{{ $adminName }}</div>
                <div class="text-muted" style="font-size:10px;">Super Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="px-2 mt-2">
            @csrf
            <button class="btn btn-sm btn-outline-light w-100" style="border-radius:10px;font-size:12px;">
                <i class="bi bi-box-arrow-right me-1"></i> Keluar
            </button>
        </form>
    </div>
</aside>
