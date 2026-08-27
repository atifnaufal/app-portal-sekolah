<!DOCTYPE html>
<html lang="id">
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { background: #246bfe; color: #fff; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .content { padding: 20px; }
        .footer { font-size: 12px; text-align: center; color: #777; margin-top: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: #246bfe; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .note { font-size: 13px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verifikasi Email Anda</h1>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $user->name }}</strong>!</p>
            <p>Terima kasih telah mendaftar di {{ config('app.name') }}. Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $url }}" class="btn">Verifikasi Email Sekarang</a>
            </p>
            <p class="note">Tautan ini berlaku selama <strong>{{ $expire }} menit</strong>. Jika tautan sudah kedaluwarsa, Anda dapat meminta tautan baru dari aplikasi.</p>
            <p class="note">Jika Anda tidak merasa membuat akun ini, abaikan email ini dan tidak ada tindakan lebih lanjut yang diperlukan.</p>
        </div>
        <div class="footer">
            <p>Pesan ini dikirim secara otomatis oleh sistem {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
