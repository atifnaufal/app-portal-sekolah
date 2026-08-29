@php $hideNav = true; @endphp
@extends('layouts.mobile-app')

@section('content')
@php
    $isGuru = $user->role === 'guru';
    $deadline = $tugas->deadlineStatus();
    $submission = $submission ?? null;
    $siswaKelas = $siswaKelas ?? collect();
    $canSubmit = $canSubmit ?? false;
    $totalSiswa = $isGuru ? $siswaKelas->count() : 0;
    $totalSubmitted = $tugas->pengumpulan->count();
    $totalGraded = $tugas->pengumpulan->whereNotNull('nilai')->where('revisi_aktif', false)->count();
    $totalPending = $tugas->pengumpulan->whereNull('nilai')->where('revisi_aktif', false)->count();
    $totalRevisi = $tugas->pengumpulan->where('revisi_aktif', true)->count();
@endphp

<style>
    .td-body { padding: 62px 14px 100px; max-width: 640px; margin: 0 auto; }
    .td-card {
        background: var(--surface-card); border: 1px solid var(--line);
        border-radius: var(--radius-md); padding: 18px; margin-bottom: 14px;
        box-shadow: var(--shadow-card);
    }
    .td-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700;
    }
    .td-progress { height: 6px; border-radius: 99px; background: #eef2f7; overflow: hidden; display: flex; }
    .td-progress > span { display: block; height: 100%; border-radius: 99px; }
    .td-submission {
        background: var(--surface-card); border: 1px solid var(--line-strong);
        border-radius: var(--radius-md); padding: 14px; margin-bottom: 10px;
    }
    .td-grade-input {
        width: 70px; border: 1.5px solid var(--line-strong); border-radius: 10px;
        padding: 8px; font-size: 16px; font-weight: 800; text-align: center;
        -webkit-appearance: none; color: var(--ink); background: #fff;
    }
    .td-grade-input:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
    .td-feedback {
        width: 100%; border: 1.5px solid var(--line-strong); border-radius: 10px;
        padding: 8px 10px; font-size: 13px; resize: none; color: var(--ink); background: #fff;
        -webkit-appearance: none;
    }
    .td-feedback:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
    .td-toggle { position: relative; width: 40px; height: 22px; flex-shrink: 0; }
    .td-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .td-toggle-bg { position: absolute; inset: 0; background: #e2e8f0; border-radius: 99px; transition: 0.2s; cursor: pointer; }
    .td-toggle-bg::before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .td-toggle input:checked + .td-toggle-bg { background: var(--indigo); }
    .td-toggle input:checked + .td-toggle-bg::before { transform: translateX(18px); }
    .td-grade-circle {
        width: 100px; height: 100px; border-radius: 50%; margin: 0 auto;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .td-grade-circle .num { font-size: 32px; font-weight: 800; line-height: 1; }
    .td-grade-circle .lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 2px; }
    .del-modal {
        position: fixed; inset: 0; z-index: 2000; display: none;
        align-items: flex-end; justify-content: center; background: rgba(0,0,0,0.4);
    }
    .del-modal.open { display: flex; }
    .del-modal-card {
        width: 100%; max-width: 640px; background: var(--surface-card);
        border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: 24px 20px;
    }
    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    .fade-up { animation: fadeUp 0.35s ease both; }
</style>

<div class="pui-topbar">
    <a href="{{ route('tugas.index') }}" class="back"><i class="bi bi-chevron-left"></i></a>
    <h1 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ \Illuminate\Support\Str::limit($tugas->judul, 30) }}</h1>
    <span class="spacer"></span>
    @if($isGuru)
        <button type="button" onclick="document.getElementById('delModal').classList.add('open')" class="icon-action" style="width:40px;height:40px;border-radius:14px;background:#fff5f6;border:1px solid #fecdd3;color:#d94b61;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <i class="bi bi-trash3"></i>
        </button>
        <a href="{{ route('tugas.edit', $tugas) }}" style="width:40px;height:40px;border-radius:14px;background:#eef4ff;border:1px solid #bfdbfe;display:flex;align-items:center;justify-content:center;text-decoration:none;color:var(--blue);font-size:16px;">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endif
</div>

<div class="td-body">
    {{-- Info Card --}}
    <div class="td-card fade-up" style="background: var(--grad-hero); color: #fff; padding: 24px 20px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <span style="background:rgba(255,255,255,0.08); padding:4px 10px; border-radius:8px; font-size:9px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;">
                <i class="bi bi-journal-bookmark me-1"></i> {{ $tugas->mataPelajaran?->nama ?? 'Umum' }}
            </span>
            <span style="background:rgba(255,255,255,0.08); padding:4px 10px; border-radius:8px; font-size:9px; font-weight:800; letter-spacing:0.05em; text-transform:uppercase;">
                <i class="bi bi-people me-1"></i> {{ $tugas->kelas->nama }}
            </span>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div>
                <div style="font-size:20px; font-weight:800; line-height:1.2; margin-bottom:6px; letter-spacing:-0.02em;">{{ $tugas->judul }}</div>
                <div style="font-size:12px; opacity:0.6; font-weight:500;">Oleh: {{ $tugas->user->name }}</div>
            </div>
            @if(!$isGuru)
                <a href="{{ route('chat.startPrivate', $tugas->user_id) }}" style="width:44px; height:44px; border-radius:14px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; color:#fff; text-decoration:none;">
                    <i class="bi bi-chat-dots-fill"></i>
                </a>
            @endif
        </div>

        <div style="margin-top:16px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:9px; font-weight:800; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:2px;">Deadline</div>
                <div style="font-size:13px; font-weight:700;">{{ $tugas->batas_pengumpulan?->format('d M Y, H:i') ?? 'Tidak ada batas' }}</div>
            </div>
            <div class="text-end">
                <div style="font-size:9px; font-weight:800; color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:2px;">Status</div>
                <div style="font-size:13px; font-weight:700; color:{{ $deadline['tone'] === 'danger' ? '#f87171' : '#4ade80' }}">{{ $deadline['label'] }}</div>
            </div>
        </div>
    </div>

    {{-- Deskripsi & Lampiran --}}
    <div class="td-card fade-up" style="animation-delay:0.05s;">
        <div class="fw-bold mb-2" style="font-size:13px;color:var(--ink);"><i class="bi bi-info-circle" style="color:var(--indigo);"></i> Instruksi</div>
        <div style="font-size:13px;color:var(--mist);line-height:1.6;white-space:pre-line;">{{ $tugas->deskripsi ?: 'Tidak ada deskripsi.' }}</div>
        @if($tugas->lampiran)
            <a href="{{ asset('storage/'.$tugas->lampiran) }}" target="_blank" style="display:flex;align-items:center;gap:10px;padding:10px;background:#f8fafc;border-radius:12px;margin-top:10px;text-decoration:none;color:var(--ink);">
                <i class="bi bi-file-earmark-fill" style="font-size:20px;color:var(--indigo);"></i>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $tugas->lampiran_nama }}</div>
                    <div style="font-size:10px;color:var(--faint);">Tap untuk buka</div>
                </div>
                <i class="bi bi-box-arrow-up-right" style="font-size:12px;color:var(--faint);"></i>
            </a>
        @endif
    </div>

    @if($isGuru)
        {{-- ===== GURU: Monitoring & Review ===== --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin:16px 0 10px;">
            <div style="font-size:14px;font-weight:800;color:var(--ink);">Pengumpulan Siswa</div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('tugas.export.pdf', $tugas) }}" class="pui-chip pui-chip-red"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <a href="{{ route('tugas.export.excel', $tugas) }}" class="pui-chip pui-chip-green"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            </div>
        </div>

        {{-- Progress --}}
        @if($totalSiswa > 0)
            <div class="td-progress" style="margin-bottom:14px;">
                <span style="width:{{ round(($totalGraded/$totalSiswa)*100) }}%;background:#16a34a;"></span>
                <span style="width:{{ round(($totalPending/$totalSiswa)*100) }}%;background:#f59e0b;"></span>
                <span style="width:{{ round(($totalRevisi/$totalSiswa)*100) }}%;background:#d94b61;"></span>
            </div>
        @endif

        @forelse($tugas->pengumpulan as $item)
            <div class="td-submission fade-up">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:36px;height:36px;border-radius:12px;background:#eef4ff;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--indigo);font-size:13px;flex-shrink:0;">
                        {{ strtoupper(substr($item->siswa->name ?? '?', 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:var(--ink);">{{ $item->siswa->name ?? 'Siswa' }}</div>
                        <div style="font-size:10px;color:var(--faint);">{{ $item->dikumpulkan_pada?->diffForHumans() ?? 'Baru saja' }}</div>
                    </div>
                    <a href="{{ route('chat.startPrivate', $item->siswa_id) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="width:36px;height:36px;padding:0;flex-shrink:0;">
                        <i class="bi bi-chat-text"></i>
                    </a>
                    @if($item->revisi_aktif)
                        <span class="td-badge" style="background:#fef3c7;color:#b45309;">Revisi</span>
                    @elseif($item->nilai !== null)
                        <span class="td-badge" style="background:#dcfce7;color:#15803d;">{{ $item->nilai }}</span>
                    @else
                        <span class="td-badge" style="background:#dbeafe;color:#1d4ed8;">Pending</span>
                    @endif
                </div>

                {{-- File jawaban --}}
                @if($item->jawaban_file)
                    <a href="{{ asset('storage/'.$item->jawaban_file) }}" target="_blank" class="d-flex align-items-center gap-2" style="padding:8px 10px;background:#f8fafc;border-radius:10px;margin-bottom:8px;text-decoration:none;color:var(--ink);">
                        <i class="bi bi-file-earmark-fill" style="color:var(--indigo);"></i>
                        <span style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;">{{ $item->jawaban_nama ?: 'File Jawaban' }}</span>
                        <i class="bi bi-box-arrow-up-right" style="font-size:10px;color:var(--faint);"></i>
                    </a>
                @endif

                {{-- Jawaban form --}}
                @if($item->jawaban_form)
                    @php
                        $answers = is_array($item->jawaban_form) ? $item->jawaban_form : (json_decode($item->jawaban_form ?: '[]', true) ?: []);
                        $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []);
                    @endphp
                    <div style="font-size:10px;font-weight:800;color:var(--indigo);letter-spacing:0.05em;margin:2px 0 6px;"><i class="bi bi-ui-checks-grid"></i> JAWABAN FORMULIR SISWA</div>
                    <div style="background:#f8fafc;border-radius:10px;padding:10px;margin-bottom:8px;">
                        @foreach($formData as $qi => $q)
                            @php $ans = $answers[$qi] ?? null; @endphp
                            <div style="margin-bottom:6px;">
                                <div style="font-size:11px;font-weight:700;color:var(--ink);">{{ $qi+1 }}. {{ $q['text'] ?? '' }}</div>
                                <div style="font-size:11px;color:var(--mist);white-space:pre-line;">{{ is_array($ans) ? implode(', ', array_filter($ans)) : ($ans ?: 'tidak dijawab') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($item->catatan && !$item->jawaban_form)
                    <div style="font-size:11px;color:var(--mist);font-style:italic;margin-bottom:8px;">"{{ $item->catatan }}"</div>
                @endif

                {{-- Review Form --}}
                <form method="POST" action="{{ route('tugas.review', $item) }}" style="border-top:1px solid var(--line);padding-top:10px;">
                    @csrf
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label style="font-size:11px;font-weight:700;color:var(--mist);flex-shrink:0;">Nilai:</label>
                        <input type="number" name="nilai" class="td-grade-input" min="0" max="100" step="1" value="{{ $item->nilai ?? '' }}" placeholder="0-100" required>
                    </div>
                    <textarea name="feedback_guru" class="td-feedback" rows="2" placeholder="Feedback untuk siswa...">{{ $item->feedback_guru }}</textarea>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size:11px;color:var(--mist);">Minta revisi</span>
                            <label class="td-toggle">
                                <input type="checkbox" name="revisi_aktif" value="1" @checked($item->revisi_aktif)>
                                <span class="td-toggle-bg"></span>
                            </label>
                        </div>
                        <button type="submit" class="pui-btn pui-btn-primary pui-btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="td-card" style="text-align:center;padding:30px;">
                <i class="bi bi-inbox" style="font-size:32px;color:var(--faint);"></i>
                <div style="font-size:13px;font-weight:600;color:var(--faint);margin-top:8px;">Belum ada pengumpulan</div>
            </div>
        @endforelse

        {{-- Belum mengumpulkan --}}
        @php $submittedIds = $tugas->pengumpulan->pluck('siswa_id'); $belum = $siswaKelas->whereNotIn('id', $submittedIds); @endphp
        @if($belum->count() > 0)
            <div style="margin-top:16px;font-size:12px;font-weight:700;color:var(--faint);">Belum Mengumpulkan ({{ $belum->count() }})</div>
            @foreach($belum as $siswa)
                <div class="d-flex align-items-center gap-2" style="padding:8px 0;border-bottom:1px solid var(--line);">
                    <div style="width:28px;height:28px;border-radius:8px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-weight:700;color:#dc2626;font-size:11px;">{{ strtoupper(substr($siswa->name,0,1)) }}</div>
                    <span style="flex:1;font-size:12px;font-weight:600;color:var(--ink);">{{ $siswa->name }}</span>
                    <a href="{{ route('chat.startPrivate', $siswa->id) }}" class="pui-btn pui-btn-ghost pui-btn-sm pui-btn-round" style="width:32px;height:32px;padding:0;flex-shrink:0;">
                        <i class="bi bi-chat-text" style="font-size:12px;"></i>
                    </a>
                </div>
            @endforeach
        @endif

    @else
        {{-- ===== SISWA VIEW ===== --}}

        @if($submission && !$submission->revisi_aktif && $submission->nilai !== null)
            @php
                $gc = $submission->nilai >= 85 ? 'background:linear-gradient(135deg,#f0fdf4,#dcfce7);color:#15803d;' : ($submission->nilai >= 70 ? 'background:linear-gradient(135deg,#eef4ff,#dbeafe);color:#1d4ed8;' : ($submission->nilai >= 55 ? 'background:linear-gradient(135deg,#fefce8,#fef9c3);color:#a16207;' : 'background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#dc2626;'));
                $ge = $submission->nilai >= 85 ? 'Luar Biasa!' : ($submission->nilai >= 70 ? 'Bagus!' : ($submission->nilai >= 55 ? 'Cukup' : 'Perlu Belajar'));
            @endphp
            <div class="td-card fade-up" style="text-align:center;animation-delay:0.1s;">
                <div style="font-size:10px;font-weight:700;color:var(--faint);letter-spacing:0.08em;margin-bottom:12px;">NILAI KAMU</div>
                <div class="td-grade-circle" style="{{ $gc }}">
                    <div class="num">{{ $submission->nilai }}</div>
                    <div class="lbl">dari 100</div>
                </div>
                <div style="font-size:16px;font-weight:800;margin-top:12px;color:var(--ink);">{{ $ge }}</div>
                @if($submission->feedback_guru)
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:12px;margin-top:14px;text-align:left;">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div style="width:20px;height:20px;border-radius:6px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;">{{ strtoupper(substr($tugas->user->name,0,1)) }}</div>
                            <span style="font-size:12px;font-weight:700;color:var(--ink);">{{ $tugas->user->name }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--mist);line-height:1.6;white-space:pre-line;">{{ $submission->feedback_guru }}</div>
                    </div>
                @endif
            </div>
        @elseif($submission && $submission->revisi_aktif)
            <div class="td-card fade-up" style="border:2px solid #fde68a;animation-delay:0.1s;">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:40px;height:40px;border-radius:12px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;"><i class="bi bi-arrow-repeat" style="font-size:18px;"></i></div>
                    <div><div style="font-size:14px;font-weight:700;color:var(--ink);">Perlu Revisi</div><div style="font-size:11px;color:var(--faint);">Perbaiki jawaban kamu</div></div>
                </div>
                @if($submission->feedback_guru)
                    <div style="background:#fffbeb;border-radius:10px;padding:10px;font-size:12px;line-height:1.6;white-space:pre-line;">{{ $submission->feedback_guru }}</div>
                @endif
            </div>
        @elseif($submission && $submission->nilai === null)
            <div class="td-card fade-up" style="text-align:center;animation-delay:0.1s;">
                <div style="width:50px;height:50px;border-radius:16px;background:#dbeafe;color:var(--blue);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="bi bi-hourglass-split" style="font-size:22px;"></i></div>
                <div style="font-size:14px;font-weight:700;color:var(--ink);">Menunggu Penilaian</div>
                <div style="font-size:11px;color:var(--faint);margin-top:4px;">Dikirim {{ $submission->dikumpulkan_pada?->format('d M Y, H:i') ?? 'baru saja' }}</div>
            </div>
        @endif

        @if($canSubmit)
            @if($tugas->tipe === 'form')
                @php $formData = is_array($tugas->form_data) ? $tugas->form_data : (json_decode($tugas->form_data ?: '[]', true) ?: []); @endphp
                <div class="td-card fade-up" style="animation-delay:0.15s;">
                    <div class="fw-bold mb-3" style="font-size:14px;color:var(--ink);"><i class="bi bi-ui-checks-grid" style="color:var(--indigo);"></i> Formulir Pengerjaan</div>
                    <form method="POST" action="{{ route('tugas.submit', $tugas) }}">
                        @csrf
                        @foreach($formData as $idx => $q)
                            @php $req = $q['required'] ?? true; @endphp
                            <div style="background:var(--surface);border-radius:var(--radius-sm);padding:14px;margin-bottom:10px;">
                                <label style="font-size:13px;font-weight:700;display:block;margin-bottom:8px;color:var(--ink);">
                                    {{ $idx+1 }}. {{ $q['text'] }} @if($req)<span style="color:#dc2626;">*</span>@endif
                                </label>
                                @if($q['type']==='text')
                                    <input type="text" name="jawaban[{{ $idx }}]" class="pui-input pui-input-sm" style="border-radius:10px;padding:10px;font-size:13px;" @if($req) required @endif>
                                @elseif($q['type']==='essay')
                                    <textarea name="jawaban[{{ $idx }}]" rows="3" class="pui-textarea" style="border-radius:10px;padding:10px;font-size:13px;resize:none;" @if($req) required @endif></textarea>
                                @elseif($q['type']==='multiple')
                                    @foreach($q['options'] ?? [] as $oi => $opt)
                                        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:var(--surface-card);border-radius:8px;margin-bottom:4px;font-size:13px;cursor:pointer;color:var(--ink);">
                                            <input type="radio" name="jawaban[{{ $idx }}]" value="{{ $opt }}" @if($req) required @endif> {{ $opt }}
                                        </label>
                                    @endforeach
                                @elseif($q['type']==='checkbox')
                                    @foreach($q['options'] ?? [] as $oi => $opt)
                                        <label style="display:flex;align-items:center;gap:8px;padding:8px;background:var(--surface-card);border-radius:8px;margin-bottom:4px;font-size:13px;cursor:pointer;color:var(--ink);">
                                            <input type="checkbox" name="jawaban[{{ $idx }}][]" value="{{ $opt }}" @if($req) required @endif> {{ $opt }}
                                        </label>
                                    @endforeach
                                @elseif($q['type']==='dropdown')
                                    <select name="jawaban[{{ $idx }}]" class="pui-select" style="border-radius:10px;padding:10px;font-size:13px;" @if($req) required @endif>
                                        <option value="">-- Pilih --</option>
                                        @foreach($q['options'] ?? [] as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach
                                    </select>
                                @endif
                            </div>
                        @endforeach
                        <button type="submit" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round mt-1">
                            <i class="bi bi-send-fill"></i> Kirim Jawaban
                        </button>
                    </form>
                </div>
            @else
                <div class="td-card fade-up" style="animation-delay:0.15s;">
                    <div class="fw-bold mb-3" style="font-size:14px;color:var(--ink);"><i class="bi bi-cloud-arrow-up" style="color:var(--indigo);"></i> Kirim Jawaban</div>
                    @if($submission && $submission->jawaban_file)
                        <div class="d-flex align-items-center gap-2" style="padding:10px;background:#f0fdf4;border-radius:10px;margin-bottom:10px;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                            <div style="flex:1;"><div style="font-size:12px;font-weight:600;color:var(--ink);">Terkirim</div><div style="font-size:10px;color:var(--faint);">{{ $submission->dikumpulkan_pada?->format('d M Y, H:i') }}</div></div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('tugas.submit', $tugas) }}" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom:10px;">
                            <label style="font-size:11px;font-weight:700;color:var(--mist);margin-bottom:4px;display:block;">CATATAN</label>
                            <textarea name="catatan" rows="2" class="pui-textarea" style="border-radius:10px;padding:10px;font-size:13px;resize:none;" placeholder="Catatan pengerjaan..." required>{{ $submission?->catatan }}</textarea>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="font-size:11px;font-weight:700;color:var(--mist);margin-bottom:4px;display:block;">FILE JAWABAN</label>
                            <input type="file" name="jawaban_file" class="pui-input" style="padding:10px;" {{ $submission && $submission->jawaban_file ? '' : 'required' }} accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.csv,.txt,.zip">
                            <div style="font-size:10px;color:var(--faint);margin-top:4px;">PDF, Word, Excel, PPT, Gambar, ZIP (Maks 10MB)</div>
                        </div>
                        <button type="submit" class="pui-btn pui-btn-primary pui-btn-block pui-btn-round">
                            <i class="bi bi-send-fill"></i> {{ $submission ? 'Perbarui' : 'Kirim' }}
                        </button>
                    </form>
                </div>
            @endif
        @elseif(!$submission && $tugas->isExpired())
            <div class="td-card fade-up" style="text-align:center;">
                <i class="bi bi-x-circle" style="font-size:32px;color:#dc2626;"></i>
                <div style="font-size:14px;font-weight:700;margin-top:8px;color:var(--ink);">Batas Waktu Terlewat</div>
            </div>
        @endif
    @endif
</div>

{{-- Delete Modal (guru only) --}}
@if($isGuru)
<div id="delModal" class="del-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="del-modal-card">
        <div style="font-size:16px;font-weight:800;margin-bottom:4px;color:var(--ink);">Hapus tugas?</div>
        <div style="font-size:12px;color:var(--faint);margin-bottom:16px;">{{ $tugas->pengumpulan->count() }} pengumpulan akan ikut terhapus.</div>
        <form method="POST" action="{{ route('tugas.destroy', $tugas) }}">
            @csrf @method('DELETE')
            <button type="submit" class="pui-btn pui-btn-danger pui-btn-block pui-btn-round mb-2">Hapus Permanen</button>
            <button type="button" class="pui-btn pui-btn-ghost pui-btn-block pui-btn-round" onclick="document.getElementById('delModal').classList.remove('open')">Batal</button>
        </form>
    </div>
</div>
@endif
@endsection
