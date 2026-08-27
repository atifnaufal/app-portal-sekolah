@extends('layouts.mobile-app')
@section('content')
<div class="p-3 pb-0">
    <a href="{{ route('profile.show') }}" class="text-decoration-none text-muted d-inline-flex align-items-center gap-2 small fw-bold">
        <i class="bi bi-chevron-left"></i>
        Kembali
    </a>
</div>

<style>
    .photo-editor {
        width: 120px; height: 120px; border-radius: 35px;
        overflow: hidden; background: #f0f7ff;
        position: relative; margin: auto;
        box-shadow: 0 12px 25px rgba(36, 107, 254, 0.15);
        border: 4px solid #fff;
    }
    .photo-editor img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
    .photo-placeholder { height: 100%; display: grid; place-items: center; color: #246bfe; font-size: 48px; font-weight: 800; }
</style>

<header class="mobile-hero mt-3" style="border-radius: 30px;">
    <div class="eyebrow">PENGATURAN PROFIL</div>
    <div class="hero-title mt-2">Edit Identitas</div>
    <p class="mb-0 mt-2 opacity-75 small">Perbarui foto dan data diri Anda agar selalu akurat.</p>
</header>

<main class="mobile-content">
    <div class="card mobile-card shadow-sm border-0" style="border-radius: 25px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="text-center mb-4">
                    <div class="photo-editor mb-3" id="photo-preview">
                        @if($user->foto)
                            <img src="{{ asset('storage/'.$user->foto) }}" alt="Preview" id="preview-image">
                        @else
                            <div class="photo-placeholder" id="preview-placeholder">{{ strtoupper(substr($user->name,0,1)) }}</div>
                            <img id="preview-image" alt="Preview" style="display:none">
                        @endif
                    </div>
                    <label class="btn btn-light btn-sm rounded-pill px-4 border shadow-sm">
                        <i class="bi bi-camera-fill me-2"></i> Ganti Foto
                        <input name="foto" id="foto-input" type="file" accept="image/jpeg,image/png,image/webp" hidden>
                    </label>
                    <div class="x-small text-muted mt-2">Wajah otomatis akan dipusatkan secara stabil.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label x-small fw-bold text-muted">NAMA LENGKAP</label>
                    <input name="name" value="{{ old('name',$user->name) }}" class="form-control" style="border-radius: 12px;" required>
                </div>

                <div class="mb-4">
                    <label class="form-label x-small fw-bold text-muted">ALAMAT EMAIL</label>
                    <input name="email" type="email" value="{{ old('email',$user->email) }}" class="form-control" style="border-radius: 12px;" required>
                    @if(!$user->hasVerifiedEmail())
                        <div class="x-small text-warning mt-1"><i class="bi bi-info-circle"></i> Email belum terverifikasi.</div>
                    @endif
                </div>

                <div class="p-3 rounded-4 mb-4" style="background: #f8fafc; border: 1px dashed #e2e8f0;">
                    <div class="small fw-bold mb-2">Ganti Password</div>
                    <div class="mb-3">
                        <input name="password" type="password" class="form-control form-control-sm" placeholder="Password baru" minlength="8" style="border-radius: 10px;">
                    </div>
                    <div>
                        <input name="password_confirmation" type="password" class="form-control form-control-sm" placeholder="Konfirmasi password baru" minlength="8" style="border-radius: 10px;">
                    </div>
                    <div class="x-small text-secondary mt-2">Biarkan kosong jika tidak ingin mengubah password.</div>
                </div>

                <button class="btn btn-primary w-100 py-3 shadow" style="border-radius: 18px; font-weight: 800;">
                    SIMPAN PERUBAHAN &rarr;
                </button>
            </form>
        </div>
    </div>
    <a href="{{ route('profile.show') }}" class="btn btn-link w-100 mt-3 text-decoration-none text-muted fw-bold">Batal</a>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('foto-input');
    const image = document.getElementById('preview-image');
    const placeholder = document.getElementById('preview-placeholder');

    input.addEventListener('change', function() {
        if (!input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            image.src = e.target.result;
            image.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    });
});
</script>
@endsection
