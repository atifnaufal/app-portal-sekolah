<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function faq()
    {
        $faqs = [
            [
                'q' => 'Bagaimana cara melakukan absensi?',
                'a' => 'Buka menu "Absen" di navigasi bawah, pastikan GPS Anda aktif, lalu tekan tombol "Hadir".'
            ],
            [
                'q' => 'Mengapa notifikasi tidak muncul?',
                'a' => 'Pastikan izin notifikasi sudah diaktifkan di pengaturan profil dan aplikasi tidak dibatasi oleh penghemat baterai.'
            ],
            [
                'q' => 'Bagaimana cara mengganti foto profil?',
                'a' => 'Buka halaman profil, klik pada foto Anda, pilih foto baru, lalu seret foto untuk menyesuaikan posisi.'
            ],
            [
                'q' => 'Saya lupa kata sandi, apa yang harus dilakukan?',
                'a' => 'Gunakan fitur "Lupa Password" di halaman login untuk mengatur ulang kata sandi melalui email.'
            ]
        ];

        return view('mobile.faq', compact('faqs'));
    }

    public function about()
    {
        return view('mobile.about');
    }

    public function security()
    {
        return view('mobile.security');
    }

    public function notificationSettings()
    {
        return view('mobile.notification-settings');
    }
}
