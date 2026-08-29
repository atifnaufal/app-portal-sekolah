<tr>
    <td>
        <div class="d-flex align-items-center gap-3">
            <div style="width: 40px; height: 40px; border-radius: 12px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #475569; flex-shrink: 0;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div class="fw-bold text-dark" style="font-size: 14px;">{{ $user->name }}</div>
                <div class="text-muted" style="font-size: 12px;">{{ $user->nik }} · {{ $user->email }}</div>
            </div>
        </div>
    </td>
    <td>
        @if($user->role === 'siswa')
            <span class="fw-semibold text-dark">{{ $user->kelas?->nama ?? '-' }}</span>
        @else
            @php
                // Kumpulkan kelas yang diampu guru secara unik & terurut per tingkat
                $guruClasses = collect([$user->kelas])
                    ->merge($user->mataPelajarans->pluck('kelas'))
                    ->filter()
                    ->unique('id')
                    ->sortBy([['tingkat', 'asc'], ['nama', 'asc']])
                    ->values();
            @endphp
            @if($guruClasses->isEmpty())
                <span class="text-muted">Belum ada penugasan kelas</span>
            @else
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @foreach($guruClasses as $availClass)
                        <span class="class-chip" title="{{ $availClass->nama }} · Tingkat {{ $availClass->tingkat }}">
                            <i class="bi bi-columns-gap"></i>
                            {{ $availClass->nama }}
                        </span>
                    @endforeach
                </div>
            @endif
        @endif
    </td>
    <td>
        @if($user->aktif)
            <span class="badge-premium badge-success-p"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
        @else
            <span class="badge-premium badge-warning-p"><i class="bi bi-exclamation-circle-fill me-1"></i> Menunggu</span>
        @endif
    </td>
    <td class="text-end">
        <div class="d-flex justify-content-end gap-2">
            @if(!$user->aktif)
                <form method="POST" action="{{ route('admin.user.toggle', $user) }}">
                    @csrf @method('PATCH')
                    <button class="btn-action btn-approve" title="Setujui Akun">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.user.toggle', $user) }}">
                    @csrf @method('PATCH')
                    <button class="btn-action" title="Nonaktifkan Akun">
                        <i class="bi bi-slash-circle"></i>
                    </button>
                </form>
            @endif

            <button class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}" title="Edit Detail">
                <i class="bi bi-pencil-square"></i>
            </button>

            <form method="POST" action="{{ route('admin.user.destroy', $user) }}" onsubmit="return confirm('Hapus akun ini secara permanen?')">
                @csrf @method('DELETE')
                <button class="btn-action btn-delete" title="Hapus Permanen">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
