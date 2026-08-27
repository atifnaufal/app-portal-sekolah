<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'kelas_id', 'foto', 'foto_posisi_x', 'foto_posisi_y', 'nik', 'no_hp', 'aktif'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function kelas(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function notifikasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notifikasi::class);
    }

    /**
     * Send the email verification notification using a queue for better response.
     * Menggunakan sistem antrean jika dikonfigurasi, jika tidak kirim langsung.
     */
    public function sendEmailVerificationNotification(): void
    {
        try {
            // Jika di Railway belum ada worker, kita gunakan pengiriman langsung sementara
            // atau tetap gunakan Queued untuk kecepatan UI jika worker sudah jalan
            $this->notify(new \App\Notifications\QueuedVerifyEmail);
        } catch (\Exception $e) {
            // Log error jika pengiriman gagal
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email verifikasi: " . $e->getMessage());
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }
}
