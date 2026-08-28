@extends(session('user_role') === 'admin' ? 'layouts.app' : 'layouts.mobile-app')

@section('content')
<style>
    :root {
        --f-ink: #0f172a;
        --f-soft: #64748b;
        --f-faint: #94a3b8;
        --f-line: rgba(15, 23, 42, 0.08);
        --f-indigo: #4f46e5;
        --f-blue: #2563eb;
        --f-muted-bg: #f1f5f9;
    }

    .f-shell {
        max-width: 640px; margin: 0 auto; width: 100%;
        padding: 0 16px 40px;
    }

    /* ===== Header premium ===== */
    .f-hero {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 22px 2px 18px;
    }
    .f-back {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--f-soft); font-weight: 800; font-size: 13px; text-decoration: none;
        padding: 8px 12px; border-radius: 12px; background: rgba(255,255,255,.9);
        border: 1px solid var(--f-line); backdrop-filter: blur(8px);
        transition: all .2s;
    }
    .f-back:active { background: #fff; transform: scale(.97); }
    .f-title-wrap { flex: 1; min-width: 0; }
    .f-eyebrow {
        font-size: 11px; letter-spacing: .14em; font-weight: 800;
        text-transform: uppercase; color: #818cf8; display: inline-flex; align-items: center; gap: 6px;
    }
    .f-main-title {
        font-size: clamp(22px, 6vw, 28px); font-weight: 800; letter-spacing: -.02em;
        color: var(--f-ink); margin: 5px 0 0; line-height: 1.15;
    }
    .f-sub { color: var(--f-soft); font-weight: 500; font-size: 13px; margin: 4px 0 0; }

    /* ===== Kartu form ===== */
    .f-card {
        background: #fff; border-radius: 22px; border: 1px solid var(--f-line);
        box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        padding: 20px 18px;
    }
    .f-sec { margin-bottom: 22px; }
    .f-sec:last-child { margin-bottom: 4px; }
    .f-label {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        font-size: 12.5px; font-weight: 800; color: var(--f-ink); margin-bottom: 8px; letter-spacing: -.01em;
    }
    .f-label .req { color: #dc2626; }
    .f-hint { font-size: 11.5px; color: var(--f-faint); font-weight: 600; }

    /* Input premium */
    .f-input, .f-textarea {
        width: 100%; border: 1.5px solid var(--f-line); background: var(--f-muted-bg);
        border-radius: 14px; padding: 13px 15px; font-size: 14px; font-weight: 600;
        color: var(--f-ink); outline: none; transition: all .2s;
        -webkit-appearance: none; appearance: none;
    }
    .f-input::placeholder, .f-textarea::placeholder { color: #b6c0cc; font-weight: 500; }
    .f-input:focus, .f-textarea:focus {
        background: #fff; border-color: var(--f-indigo);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .12);
    }

    /* ===== Target = segmented radio cards ===== */
    .f-targets { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .f-target {
        position: relative; cursor: pointer;
        border: 1.5px solid var(--f-line); background: var(--f-muted-bg);
        border-radius: 16px; padding: 13px 12px;
        display: flex; align-items: center; gap: 10px;
        transition: all .18s;
    }
    .f-target .t-ico {
        width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
        display: grid; place-items: center; font-size: 17px; color: var(--f-indigo);
        background: #eef2ff; transition: all .18s;
    }
    .f-target b { display: block; font-size: 13px; color: var(--f-ink); line-height: 1.2; }
    .f-target span { display: block; font-size: 11px; color: var(--f-faint); font-weight: 600; margin-top: 1px; }
    .f-target .t-check {
        margin-left: auto; width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;
        border: 2px solid #cbd5e1; background: #fff; display: grid; place-items: center;
        font-size: 12px; color: #fff; transition: all .18s;
    }
    .f-target:active { transform: scale(.97); }
    .f-target.on { border-color: var(--f-indigo); background: #eef2ff; }
    .f-target.on .t-ico { background: #fff; }
    .f-target.on .t-check { background: var(--f-indigo); border-color: var(--f-indigo); }
    .f-target.on .t-check::before { content: '\F26E'; font-family: 'bootstrap-icons'; font-size: 12px; }

    /* ===== Siswa = chips ===== */
    .f-chips { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; max-height: 260px; overflow-y: auto; padding: 2px; }
    .f-chip {
        cursor: pointer; position: relative;
        border: 1.5px solid var(--f-line); background: var(--f-muted-bg);
        border-radius: 12px; padding: 11px 12px;
        display: flex; align-items: center; gap: 9px; transition: all .18s;
    }
    .f-chip .c-av {
        width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
        display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 12px;
        background: linear-gradient(135deg, #6366f1, #2563eb);
    }
    .f-chip .c-name { font-size: 12.5px; font-weight: 700; color: var(--f-ink); line-height: 1.2; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .f-chip .c-box {
        margin-left: auto; width: 20px; height: 20px; border-radius: 7px; flex-shrink: 0;
        border: 2px solid #cbd5e1; background: #fff; display: grid; place-items: center;
        font-size: 11px; color: #fff; transition: all .18s;
    }
    .f-chip.on { border-color: var(--f-indigo); background: #eef2ff; }
    .f-chip.on .c-box { background: var(--f-indigo); border-color: var(--f-indigo); }
    .f-chip.on .c-box::before { content: '\F26E'; font-family: 'bootstrap-icons'; }

    /* ===== Row dua kolom (tanggal + gambar) ===== */
    .f-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .f-upload {
        border: 1.5px dashed #c7d2fe; background: #f8faff;
        border-radius: 16px; padding: 14px; text-align: center; cursor: pointer;
        transition: all .2s; position: relative; overflow: hidden;
    }
    .f-upload:hover { border-color: var(--f-indigo); background: #eef2ff; }
    .f-upload .up-ico { font-size: 26px; color: var(--f-indigo); display: block; margin-bottom: 6px; }
    .f-upload .up-txt { font-size: 12px; font-weight: 700; color: var(--f-soft); }
    .f-upload input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .f-preview { position: relative; margin-top: 10px; border-radius: 14px; overflow: hidden; display: none; }
    .f-preview img { width: 100%; height: 140px; object-fit: cover; display: block; }
    .f-preview .rm {
        position: absolute; top: 8px; right: 8px; width: 28px; height: 28px; border: 0;
        border-radius: 10px; background: rgba(15,23,42,.65); color: #fff; font-size: 14px;
        display: grid; place-items: center; cursor: pointer;
    }

    /* ===== Tombol submit ===== */
    .f-submit {
        width: 100%; border: 0; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 9px;
        padding: 16px; border-radius: 16px; color: #fff;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 55%, #2563eb 100%);
        box-shadow: 0 10px 24px rgba(79, 70, 229, .35), inset 0 1px 0 rgba(255,255,255,.25);
        font-weight: 800; font-size: 15px; letter-spacing: -.01em;
        position: relative; overflow: hidden; transition: transform .18s, box-shadow .18s, filter .18s;
    }
    .f-submit::after {
        content: ''; position: absolute; top: -40%; left: -30%; width: 60%; height: 180%;
        transform: rotate(25deg); background: linear-gradient(90deg, transparent, rgba(255,255,255,.3), transparent);
        transition: transform .5s ease; opacity: 0;
    }
    .f-submit:hover { transform: translateY(-2px); filter: brightness(1.05); box-shadow: 0 14px 30px rgba(79,70,229,.45); }
    .f-submit:active { transform: scale(.97); }
    .f-submit:hover::after { opacity: 1; transform: rotate(25deg) translateX(180%); }

    /* Error */
    .f-err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
        border-radius: 12px; padding: 11px 13px; font-size: 12.5px; font-weight: 700;
        display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }

    .d-none { display: none !important; }

    @media (min-width: 640px) {
        .f-shell { padding: 0 20px 50px; }
        .f-card { padding: 26px 28px; border-radius: 26px; }
        .f-targets { grid-template-columns: repeat(4, 1fr); }
        .f-chips { grid-template-columns: repeat(2, 1fr); max-height: 320px; }
        .f-row2 { grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="f-shell">
    <div class="f-hero">
        <a href="{{ route('pengumuman.index') }}" class="f-back"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div class="f-title-wrap" style="text-align:right;">
            <span class="f-eyebrow"><i class="bi bi-megaphone-fill"></i> Siarkan Informasi</span>
            <h1 class="f-main-title">{{ $pengumuman->exists ? 'Edit Pengumuman' : 'Buat Pengumuman' }}</h1>
            <p class="f-sub">Jangkau kelas, eskul, atau seluruh sekolah.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="f-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Mohon periksa kembali isian Anda sebelum mengirim.</span>
        </div>
    @endif

    <form class="f-card" method="POST" action="{{ $pengumuman->exists ? route('pengumuman.update', $pengumuman) : route('pengumuman.store') }}" enctype="multipart/form-data">
        @csrf
        @if($pengumuman->exists) @method('PUT') @endif

        @php
            $isPrivateEdit = $pengumuman->exists && method_exists($pengumuman,'isPrivate') && $pengumuman->isPrivate();
            $currentTarget = old('target', $isPrivateEdit ? 'private'
                : ($pengumuman->eskul_id ? 'eskul:'.$pengumuman->eskul_id
                : ($pengumuman->kelas_id ? 'class'
                : (!$pengumuman->exists ? '' : 'general'))));
            $opts = [];
            if (session('user_role') === 'admin')
                $opts[] = ['v'=>'general','i'=>'bi-globe2','t'=>'Umum','d'=>'Seluruh sekolah'];
            if (isset($isWaliKelas) && $isWaliKelas)
                $opts[] = ['v'=>'class','i'=>'bi-people-fill','t'=>'Kelas','d'=>$isWaliKelas->nama];
            foreach ($adminEskuls ?? [] as $ae)
                $opts[] = ['v'=>'eskul:'.$ae->id,'i'=>'bi-stars','t'=>'Eskul','d'=>$ae->nama];
            if (session('user_role') === 'admin' || (isset($isWaliKelas) && $isWaliKelas))
                $opts[] = ['v'=>'private','i'=>'bi-lock-fill','t'=>'Pribadi','d'=>'Siswa tertentu'];
        @endphp

        <div class="f-sec">
            <label class="f-label">Target Pengumuman <span class="req">*</span></label>
            <input type="hidden" name="target" id="targetInput" value="{{ $currentTarget }}">
            <div class="f-targets">
                @foreach($opts as $o)
                    <div class="f-target {{ $currentTarget === $o['v'] ? 'on' : '' }}" data-v="{{ $o['v'] }}">
                        <span class="t-ico"><i class="bi {{ $o['i'] }}"></i></span>
                        <span style="min-width:0;">
                            <b>{{ $o['t'] }}</b>
                            <span>{{ $o['d'] }}</span>
                        </span>
                        <span class="t-check"></span>
                    </div>
                @endforeach
            </div>
            <div class="f-hint" style="margin-top:8px;">Pilih jangkauan informasi yang akan Anda bagikan.</div>
        </div>

        <div class="f-sec d-none" id="privateBox">
            <label class="f-label">Pilih Siswa Penerima <span class="req">*</span></label>
            <div class="f-chips" id="siswaChips">
                @foreach($siswaList ?? collect() as $s)
                    @php $on = in_array($s->id, old('siswa_ids', $selectedSiswa ?? []), true); @endphp
                    <label class="f-chip {{ $on ? 'on' : '' }}">
                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="d-none" {{ $on ? 'checked' : '' }}>
                        <span class="c-av">{{ strtoupper(substr($s->name, 0, 1)) }}</span>
                        <span class="c-name">{{ $s->name }}</span>
                        <span class="c-box"></span>
                    </label>
                @endforeach
            </div>
            <div class="f-hint" style="margin-top:8px;">Pengumuman privat hanya terlihat oleh siswa yang dipilih.</div>
        </div>

        <div class="f-sec">
            <label class="f-label">Judul <span class="req">*</span></label>
            <input name="judul" value="{{ old('judul',$pengumuman->judul) }}" class="f-input" required maxlength="255"
                   placeholder="Ketik judul pengumuman...">
        </div>

        <div class="f-sec">
            <label class="f-label">Isi Informasi <span class="req">*</span></label>
            <textarea name="isi" rows="6" class="f-textarea" required
                      placeholder="Tuliskan detail informasi di sini...">{{ old('isi',$pengumuman->isi) }}</textarea>
        </div>

        <div class="f-sec">
            <div class="f-row2">
                <div>
                    <label class="f-label">Tanggal Agenda <span class="f-hint" style="font-weight:600;">opsional</span></label>
                    <input name="tanggal_acara" type="date" value="{{ old('tanggal_acara',$pengumuman->tanggal_acara?->format('Y-m-d')) }}" class="f-input">
                </div>
                <div>
                    <label class="f-label">Gambar Pendukung <span class="f-hint" style="font-weight:600;">opsional</span></label>
                    <label class="f-upload">
                        <input type="file" name="gambar" id="gambarInput" accept="image/*">
                        <i class="bi bi-image up-ico"></i>
                        <span class="up-txt">Pilih gambar</span>
                        <span class="f-hint" style="display:block;margin-top:2px;">Maks 5 MB</span>
                    </label>
                    <div class="f-preview" id="gambarPreview">
                        <img id="gambarImg" alt="Preview">
                        <button type="button" class="rm" onclick="clearGambar()"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
            </div>
        </div>

        @if(session('user_role') === 'admin')
        <div class="f-sec">
            <label class="f-chip" style="width:100%;">
                <input name="is_landing" type="checkbox" value="1" id="is_landing" class="d-none" @checked(old('is_landing',$pengumuman->is_landing))>
                <span class="c-av" style="background:linear-gradient(135deg,#f59e0b,#dc2626);"><i class="bi bi-megaphone-fill"></i></span>
                <span class="c-name">Tampilkan di Banner Landing Page</span>
                <span class="c-box"></span>
            </label>
        </div>
        @endif

        <button type="submit" class="f-submit">
            <i class="bi bi-send-fill"></i>
            {{ $pengumuman->exists ? 'Simpan Perubahan' : 'Terbitkan Sekarang' }}
        </button>
    </form>
</div>

<script>
(function () {
    var currentTa = {!! json_encode($currentTarget) !!};

    // ===== Target segmented =====
    var targetInput = document.getElementById('targetInput');
    var boxes = Array.prototype.slice.call(document.querySelectorAll('#privateBox'));
    var allTargets = Array.prototype.slice.call(document.querySelectorAll('.f-target'));

    function pickTarget(v) {
        var needPrivate = v === 'private';
        allTargets.forEach(function (t) {
            var active = t.getAttribute('data-v') === v;
            t.classList.toggle('on', active);
        });
        boxes.forEach(function (b) { b.classList.toggle('d-none', !needPrivate); });
    }

    allTargets.forEach(function (t) {
        t.addEventListener('click', function () {
            targetInput.value = t.getAttribute('data-v');
            pickTarget(t.getAttribute('data-v'));
        });
    });

    // ===== Siswa chips =====
    Array.prototype.slice.call(document.querySelectorAll('#siswaChips .f-chip')).forEach(function (c) {
        var cb = c.querySelector('input[type=checkbox]');
        c.addEventListener('click', function (e) {
            if (e.target === cb) return;
            cb.checked = !cb.checked;
            c.classList.toggle('on', cb.checked);
        });
    });

    // ===== Preview gambar =====
    var gInput = document.getElementById('gambarInput');
    var gPrev = document.getElementById('gambarPreview');
    var gImg = document.getElementById('gambarImg');
    if (gInput) gInput.addEventListener('change', function () {
        var f = gInput.files && gInput.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (e) { gImg.src = e.target.result; gPrev.style.display = 'block'; };
        r.readAsDataURL(f);
    });
    window.clearGambar = function () {
        if (gInput) gInput.value = '';
        if (gPrev) gPrev.style.display = 'none';
    };

    pickTarget(currentTa);
})();
</script>
@endsection
