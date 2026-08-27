<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Email verifikasi dengan template berbahasa Indonesia.
     *
     * Catatan: sengaja dikirim sinkron (tanpa antrean) karena deployment
     * Railway belum menjalankan queue worker — email berantrean tidak akan
     * pernah terkirim. Exception TIDAK ditangkap di sini agar pemanggil
     * (route/controller) dapat memberi tahu pengguna saat pengiriman gagal.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email - ' . config('app.name'))
            ->view('emails.verify-email', [
                'url' => $verificationUrl,
                'user' => $notifiable,
                'expire' => (int) config('auth.verification.expire', 60),
            ]);
    }
}
