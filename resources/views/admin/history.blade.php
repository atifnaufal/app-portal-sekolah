@extends('layouts.app')

@section('title', 'Riwayat Aktivitas User')

@section('content')
<div class="admin-container" style="padding-top:1.5rem;padding-bottom:3rem;">
    <h1 style="font-size:24px;font-weight:800;color:var(--navy);margin-bottom:1.5rem;">
        <i class="bi bi-clock-history me-2" style="color:var(--blue);"></i>Riwayat Aktivitas User
    </h1>

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
        <div class="card" style="padding:1.25rem;">
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">Total Log</div>
            <div style="font-size:28px;font-weight:900;color:var(--navy);margin-top:4px;">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="card" style="padding:1.25rem;">
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">Hari Ini</div>
            <div style="font-size:28px;font-weight:900;color:#22c55e;margin-top:4px;">{{ number_format($stats['today']) }}</div>
        </div>
        <div class="card" style="padding:1.25rem;">
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">User Aktif</div>
            <div style="font-size:28px;font-weight:900;color:#3b82f6;margin-top:4px;">{{ number_format($stats['activeUsers']) }}</div>
        </div>
        <div class="card" style="padding:1.25rem;">
            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">Login Hari Ini</div>
            <div style="font-size:28px;font-weight:900;color:#8b5cf6;margin-top:4px;">{{ number_format($stats['logins']) }}</div>
        </div>
    </div>

    {{-- Activity Chart --}}
    <div class="card" style="padding:1.25rem;margin-bottom:1.5rem;">
        <h3 style="font-size:16px;font-weight:800;color:var(--navy);margin-bottom:1rem;">
            <i class="bi bi-bar-chart-line me-2" style="color:var(--blue);"></i>Aktivitas 30 Hari Terakhir
        </h3>
        <div style="display:flex;align-items:flex-end;gap:3px;height:80px;">
            @php $maxCount = max($dailyActivity->pluck('count')->max() ?? 1, 1); @endphp
            @foreach($dailyActivity as $day)
                <div style="flex:1;height:{{ ($day->count / $maxCount) * 100 }}%;background:var(--blue);border-radius:3px 3px 0 0;opacity:0.7;min-width:6px;" title="{{ $day->date }}: {{ $day->count }}"></div>
            @endforeach
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:8px;">
            <span style="font-size:10px;color:var(--muted);font-weight:600;">30 hari lalu</span>
            <span style="font-size:10px;color:var(--muted);font-weight:600;">Hari ini</span>
        </div>
    </div>

    {{-- Filter & Activity List --}}
    <div class="card" style="padding:1.25rem;">
        <h3 style="font-size:16px;font-weight:800;color:var(--navy);margin-bottom:1rem;">
            <i class="bi bi-list-task me-2" style="color:#ea580c;"></i>Semua Riwayat
        </h3>

        <form method="GET" style="display:flex;gap:12px;margin-bottom:1.5rem;flex-wrap:wrap;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama user..." style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;flex:1;min-width:200px;">
            <select name="type" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;font-weight:600;">
                <option value="">Semua Tipe</option>
                @foreach($activityTypes as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;">
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="padding:10px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;">
            <button type="submit" style="padding:10px 20px;background:var(--blue);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;">Cari</button>
        </form>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);">
                        <th style="text-align:left;padding:10px 12px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;">User</th>
                        <th style="text-align:left;padding:10px 12px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;">Aktivitas</th>
                        <th style="text-align:left;padding:10px 12px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;">Deskripsi</th>
                        <th style="text-align:left;padding:10px 12px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;">Lokasi</th>
                        <th style="text-align:left;padding:10px 12px;font-size:12px;font-weight:800;color:var(--muted);text-transform:uppercase;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $h)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px;">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0;">
                                        <img src="{{ $h->user?->avatar_url }}" style="width:100%;height:100%;object-fit:cover;">
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:700;color:var(--navy);">{{ $h->user?->name ?? 'User #'.$h->user_id }}</div>
                                        <div style="font-size:10px;color:var(--muted);">{{ ucfirst($h->user?->role ?? '-') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px;">
                                <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:10px;font-weight:700;text-transform:uppercase;background:{{ match($h->activity_type) {
                                    'login' => '#dcfce7', 'logout' => '#fef2f2', 'absensi' => '#dbeafe',
                                    'profile_update' => '#fef3c7', default => '#f3e8ff'
                                } }};color:{{ match($h->activity_type) {
                                    'login' => '#166534', 'logout' => '#991b1b', 'absensi' => '#1e40af',
                                    'profile_update' => '#92400e', default => '#7c3aed'
                                } }};">
                                    {{ ucfirst(str_replace('_', ' ', $h->activity_type)) }}
                                </span>
                            </td>
                            <td style="padding:12px;font-size:13px;color:var(--navy);font-weight:600;">{{ $h->description }}</td>
                            <td style="padding:12px;font-size:11px;color:#94a3b8;">
                                @if($h->lat && $h->long)
                                    <span style="background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;">
                                        <i class="bi bi-geo-alt"></i> {{ round($h->lat, 4) }}, {{ round($h->long, 4) }}
                                    </span>
                                @endif
                                @if($h->device_info)
                                    <div style="font-size:10px;color:#94a3b8;margin-top:4px;">{{ $h->device_info }}</div>
                                @endif
                            </td>
                            <td style="padding:12px;font-size:12px;color:var(--muted);font-weight:600;">
                                <div>{{ $h->created_at->diffForHumans() }}</div>
                                <div style="font-size:10px;">{{ $h->created_at->format('d M Y, H:i') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">
                                <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                <div style="font-size:14px;font-weight:700;">Tidak ada riwayat ditemukan</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem;">
            {{ $histories->links() }}
        </div>
    </div>
</div>
@endsection
