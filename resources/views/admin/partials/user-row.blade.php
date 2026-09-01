<tr>
    <td>
        <div class="d-flex align-items-center gap-3">
            <div style="width: 40px; height: 40px; border-radius: 12px; overflow: hidden; position: relative; flex-shrink: 0;">
                @if($user->foto)
                    <img src="{{ asset('storage/'.$user->foto) }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg, #3b82f6, #2563eb);display:grid;place-items:center;color:#fff;font-weight:800;font-size:16px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div style="position:absolute;bottom:-1px;right:-1px;width:12px;height:12px;border-radius:50%;border:2px solid #fff;background: {{ $user->status_color === 'green' ? '#22c55e' : ($user->status_color === 'blue' ? '#3b82f6' : '#ef4444') }};"></div>
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
        @php
            $statusLabel = $user->status_label;
            $statusColor = match($statusLabel) {
                'aktif' => 'success-p',
                'terdaftar' => 'primary-p',
                'nonaktif' => 'warning-p',
                default => 'secondary-p',
            };
        @endphp
        <span class="badge-premium badge-{{ $statusColor }}">
            <i class="bi {{ match($statusLabel) {
                'aktif' => 'bi-circle-fill',
                'terdaftar' => 'bi-person-check-fill',
                'nonaktif' => 'bi-person-x-fill',
                default => 'bi-person-fill'
            } }} me-1" style="font-size:8px;"></i>
            {{ ucfirst($statusLabel) }}
        </span>
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
