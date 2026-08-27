<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailSimulatorController extends Controller
{
    /**
     * Menampilkan daftar email simulasi yang "terkirim"
     * Digunakan untuk bypass verifikasi di lingkungan tanpa SMTP
     */
    public function index(Request $request)
    {
        // Hanya izinkan admin atau jika fitur simulator aktif
        // Untuk kemudahan, kita ambil user terbaru yang belum verifikasi
        $pendingUsers = User::whereNull('email_verified_at')
            ->where('role', '!=', 'admin')
            ->latest()
            ->take(5)
            ->get();

        return view('auth.email-simulator', compact('pendingUsers'));
    }

    /**
     * Melakukan verifikasi instan (Admin/Developer Tool)
     */
    public function instantVerify(User $user)
    {
        $user->markEmailAsVerified();

        return back()->with('success', "Email {$user->email} berhasil diverifikasi secara instan!");
    }
}
