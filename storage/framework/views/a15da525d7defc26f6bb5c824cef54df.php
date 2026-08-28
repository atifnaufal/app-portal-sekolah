<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Admin | App Mahasiswa'); ?></title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#246bfe">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
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
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="<?php echo e(route('dashboard')); ?>">
            <div class="brand-mark">A</div>
            <span>App Mahasiswa</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminMenu">
            <div class="navbar-nav ms-lg-4 me-auto">
                <?php if(session('user_role') === 'admin'): ?>
                    <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                    <a class="nav-link <?php echo e(request()->routeIs('pengumuman.*') ? 'active' : ''); ?>" href="<?php echo e(route('pengumuman.index')); ?>">Pengumuman</a>
                    <a class="nav-link <?php echo e(request()->routeIs('spp.*') ? 'active' : ''); ?>" href="<?php echo e(route('spp.index')); ?>">SPP</a>
                    <a class="nav-link <?php echo e(request()->routeIs('kelas.*') ? 'active' : ''); ?>" href="<?php echo e(route('kelas.index')); ?>">Kelas</a>
                    <a class="nav-link <?php echo e(request()->routeIs('admin.jadwal.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.jadwal.index')); ?>"><i class="bi bi-calendar3 me-1"></i>Jadwal</a>
                    <a class="nav-link <?php echo e(request()->routeIs('admin.perpustakaan.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.perpustakaan.index')); ?>">Perpustakaan</a>
                    <a class="nav-link <?php echo e(request()->routeIs('admin.eskul.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.eskul.index')); ?>">Eskul</a>
                    <a class="nav-link <?php echo e(request()->routeIs('admin.users') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users')); ?>">Akun</a>
                    <a class="nav-link <?php echo e(request()->routeIs('admin.settings') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings')); ?>">Pengaturan</a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                    <a class="nav-link" href="<?php echo e(route('pengumuman.index')); ?>">Pengumuman</a>
                    <a class="nav-link" href="<?php echo e(route('tugas.index')); ?>">Tugas</a>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <div class="d-flex flex-column text-end d-none d-lg-flex">
                    <span class="text-white small fw-bold"><?php echo e(session('admin_name')); ?></span>
                    <span class="text-muted small" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em;">Administrator</span>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="btn btn-sm btn-outline-light rounded-pill px-3">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div id="portal-toast" class="animate__animated animate__fadeInDown" style="display:none;">
    <div class="d-flex align-items-center gap-3">
        <div id="toast-icon" style="width: 44px; height: 44px; background: var(--blue-soft); border-radius: 12px; display: grid; place-items: center; color: var(--blue);">
            <i class="bi bi-bell-fill"></i>
        </div>
        <div style="flex: 1;">
            <div id="toast-title" class="fw-bold text-dark" style="font-size: 14px;">Notifikasi</div>
            <div id="toast-msg" class="text-muted" style="font-size: 13px;">Ada pesan baru untuk Anda.</div>
        </div>
        <button onclick="document.getElementById('portal-toast').style.display='none'" class="btn-close btn-close-sm"></button>
    </div>
</div>

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<main class="container admin-container py-5">
    <?php if(session('success')): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm animate__animated animate__fadeIn">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger border-0 rounded-4 shadow-sm animate__animated animate__fadeIn">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="container admin-container pb-5 text-center">
    <div class="admin-footer">
        App Mahasiswa &bull; Panel Administrasi Terintegrasi &bull; &copy; <?php echo e(date('Y')); ?>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showNotification(title, message) {
        const toast = document.getElementById('portal-toast');
        document.getElementById('toast-title').innerText = title;
        document.getElementById('toast-msg').innerText = message;
        document.getElementById('notif-sound').play().catch(e => {});

        toast.style.display = 'block';
        setTimeout(() => {
            toast.classList.replace('animate__fadeInDown', 'animate__fadeOutUp');
            setTimeout(() => {
                toast.style.display = 'none';
                toast.classList.replace('animate__fadeOutUp', 'animate__fadeInDown');
            }, 500);
        }, 5000);
    }

    if (window.Echo) {
        const userId = <?php echo json_encode((int) session('user_id'), 15, 512) ?>;
        window.Echo.private('portal-notifications.' + userId)
            .listen('.new-notification', (e) => showNotification(e.title, e.message));
    }
</script>
</body>
</html>
<?php /**PATH C:\laragon\www\app-portal-sekolah\resources\views\layouts\app.blade.php ENDPATH**/ ?>