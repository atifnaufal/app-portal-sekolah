<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\PeminjamanBuku;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerpustakaanController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->session()->get('user_id') ?: Auth::guard('web')->id();
        $user = User::findOrFail($userId);

        $query = Buku::with('kategori');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                  ->orWhere('penulis', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('kategori')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('slug', $request->kategori);
            });
        }

        $bukus = $query->latest()->get();
        $kategoris = KategoriBuku::all();

        return view('mobile.perpustakaan.index', [
            'user' => $user,
            'bukus' => $bukus,
            'kategoris' => $kategoris,
            'title' => 'Perpustakaan Digital',
        ]);
    }

    public function show(Request $request, Buku $buku): View
    {
        $userId = $request->session()->get('user_id') ?: Auth::guard('web')->id();
        $user = User::findOrFail($userId);

        $isPinjam = PeminjamanBuku::where('user_id', $user->id)
            ->where('buku_id', $buku->id)
            ->where('status', 'pinjam')
            ->exists();

        return view('mobile.perpustakaan.show', [
            'user' => $user,
            'buku' => $buku,
            'isPinjam' => $isPinjam,
            'title' => $buku->judul,
        ]);
    }

    public function read(Request $request, Buku $buku): View
    {
        $userId = $request->session()->get('user_id') ?: Auth::guard('web')->id();
        $user = User::findOrFail($userId);

        // Track reading as borrowing if not already borrowed
        PeminjamanBuku::firstOrCreate([
            'user_id' => $user->id,
            'buku_id' => $buku->id,
            'status' => 'pinjam',
        ], [
            'tanggal_pinjam' => now(),
        ]);

        // Fix for Railway: If using dummy.pdf and it's missing, use a real public PDF for testing
        $pdfUrl = asset('storage/'.$buku->file_pdf);
        if ($buku->file_pdf === 'perpustakaan/dummy.pdf' && ! file_exists(public_path('storage/perpustakaan/dummy.pdf'))) {
            $pdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
        }

        return view('mobile.perpustakaan.read', [
            'user' => $user,
            'buku' => $buku,
            'pdfUrl' => $pdfUrl,
            'title' => 'Membaca: '.$buku->judul,
        ]);
    }
}
