<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'jenis_kelamin', 'kelas_id', 'school_id', 'foto', 'foto_posisi_x', 'foto_posisi_y', 'nik', 'no_hp', 'aktif', 'status', 'last_activity_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/'.$this->foto);
        }

        $name = urlencode($this->name);
        $backgrounds = ['0D8ABC', '55acee', 'ffac33', '7c3aed', '10b981', 'ef4444'];
        $bg = $backgrounds[$this->id % count($backgrounds)];

        // UI-Avatars Premium Look
        return "https://ui-avatars.com/api/?name={$name}&background={$bg}&color=fff&size=128&bold=true&rounded=true";
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function eskuls(): BelongsToMany
    {
        return $this->belongsToMany(Eskul::class, 'eskul_members')->withPivot(['is_admin', 'status'])->withTimestamps();
    }

    public function pengumumanPribadi(): BelongsToMany
    {
        return $this->belongsToMany(Pengumuman::class, 'pengumuman_user')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    public function chatGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_members')
            ->withPivot(['status', 'role'])
            ->wherePivot('status', 'approved');
    }

    public function mataPelajarans(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'guru_id');
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class, 'user_id');
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'guru_id');
    }

    /**
     * Akun harus disetujui admin sebelum bisa dipakai.
     * Kolom `aktif` menggantikan verifikasi email sebagai gerbang akses.
     */
    public function followers(){ return $this->hasMany(GlobalFollow::class,'followed_id'); }
    public function following(){ return $this->hasMany(GlobalFollow::class,'follower_id'); }
    public function isSuperAdmin(): bool
    {
        return $this->email === 'adminpusat@pusat.com' || ($this->role === 'admin' && $this->school_id === null);
    }
    public function isAwaitingApproval(): bool
    {
        return ! $this->aktif;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public function isOnline(): bool
    {
        if (! $this->aktif) return false;
        return $this->last_activity_at && $this->last_activity_at->diffInSeconds(now()) < 60;
    }

    public function isOffline(): bool
    {
        return ! $this->isOnline();
    }

    public function getLastSeenAttribute(): string
    {
        if ($this->isOnline()) return 'online';
        if (! $this->last_activity_at) return 'Belum pernah aktif';
        return 'Terakhir dilihat '.$this->last_activity_at->diffForHumans();
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->aktif) {
            return 'nonaktif';
        }
        if ($this->isOnline()) {
            return 'aktif';
        }

        return 'terdaftar';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'aktif' => 'green',
            'terdaftar' => 'blue',
            'nonaktif' => 'red',
            default => 'gray',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_label) {
            'aktif' => 'online',
            'terdaftar' => 'offline',
            'nonaktif' => 'offline',
            default => 'offline',
        };
    }
}
