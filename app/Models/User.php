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

#[Fillable(['name', 'email', 'password', 'role', 'kelas_id', 'foto', 'foto_posisi_x', 'foto_posisi_y', 'nik', 'no_hp', 'aktif'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        return $this->belongsToMany(ChatGroup::class, 'chat_group_members');
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
        ];
    }
}
