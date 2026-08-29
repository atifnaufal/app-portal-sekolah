<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function faq()
    {
        $faqs = [
            [
                'cat' => 'Akun & Login',
                'q' => 'Bagaimana jika saya lupa password?',
                'a' => 'Anda dapat menggunakan fitur "Lupa Password" di halaman login. Masukkan email terdaftar, dan sistem akan mengirimkan link reset. Jika email tidak aktif, silakan hubungi Guru Pembimbing atau bagian IT sekolah.'
            ],
            [
                'cat' => 'Akun & Login',
                'q' => 'Apakah akun saya bisa dibuka di dua perangkat sekaligus?',
                'a' => 'Ya, namun demi keamanan kami menyarankan untuk tetap login di satu perangkat utama. Setiap sesi login baru akan tercatat di sistem keamanan kami.'
            ],
            [
                'cat' => 'Absensi',
                'q' => 'Mengapa lokasi saya tidak terdeteksi saat absensi?',
                'a' => 'Pastikan GPS perangkat aktif dan Anda telah memberikan izin lokasi (High Accuracy) kepada aplikasi. Jika masih terkendala, coba buka Google Maps sejenak untuk memperbarui koordinat GPS Anda.'
            ],
            [
                'cat' => 'Absensi',
                'q' => 'Apa yang harus dilakukan jika gagal melakukan Vermuk (Verifikasi Muka)?',
                'a' => 'Pastikan wajah berada di area terang (cukup cahaya), tidak menggunakan masker atau kacamata hitam yang menutupi area mata/hidung. Gunakan latar belakang yang polos jika memungkinkan.'
            ],
            [
                'cat' => 'Tugas & LMS',
                'q' => 'Bagaimana cara mengunggah tugas dalam bentuk file besar?',
                'a' => 'Batas maksimal unggahan file adalah 10MB. Jika file Anda lebih besar dari itu, kami menyarankan untuk mengunggahnya ke Google Drive/OneDrive lalu mencantumkan link-nya di kolom deskripsi tugas.'
            ],
            [
                'cat' => 'Tugas & LMS',
                'q' => 'Tugas sudah dikirim tapi statusnya masih "Belum Dinilai"?',
                'a' => 'Status "Belum Dinilai" berarti tugas Anda sudah masuk ke sistem guru, namun guru yang bersangkutan belum memberikan evaluasi. Anda akan menerima notifikasi otomatis saat nilai sudah diberikan.'
            ],
            [
                'cat' => 'Notifikasi',
                'q' => 'Mengapa saya tidak menerima notifikasi tugas baru?',
                'a' => 'Periksa pengaturan notifikasi di Profil > Pengaturan Notifikasi. Pastikan "Polling Otomatis" aktif dan aplikasi tidak dibatasi oleh fitur "Penghemat Baterai" sistem Android Anda.'
            ],
            [
                'cat' => 'Keamanan',
                'q' => 'Apakah fitur Biometrik (Sidik Jari) aman digunakan?',
                'a' => 'Sangat aman. Aplikasi tidak menyimpan data sidik jari Anda, melainkan hanya memverifikasi kunci yang sudah ada di sistem Android perangkat Anda.'
            ],
            [
                'cat' => 'Teknis',
                'q' => 'Aplikasi terasa lambat atau sering tertutup sendiri (Force Close)?',
                'a' => 'Coba bersihkan cache aplikasi melalui Pengaturan Android > Aplikasi > Portal Sekolah > Hapus Cache. Pastikan Anda juga menggunakan versi aplikasi terbaru.'
            ],
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
