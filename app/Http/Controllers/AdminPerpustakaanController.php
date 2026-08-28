<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\KategoriBuku;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPerpustakaanController extends Controller
{
    public function index(): View
    {
        $bukus = Buku::with('kategori')->latest()->get();

        return view('admin.perpustakaan.index', compact('bukus'));
    }

    public function create(): View
    {
        $kategoris = KategoriBuku::all();

        return view('admin.perpustakaan.form', compact('kategoris'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => 'required|max:255',
            'kategori_buku_id' => 'required|exists:kategori_bukus,id',
            'penulis' => 'nullable|max:255',
            'penerbit' => 'nullable|max:255',
            'tahun_terbit' => 'nullable|integer',
            'deskripsi' => 'nullable',
            'stok' => 'required|integer|min:0',
            'cover' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_pdf' => 'required|mimes:pdf|max:20480',
        ]);

        $data['slug'] = Str::slug($data['judul']).'-'.rand(100, 999);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('perpustakaan/covers', 'public');
        }

        if ($request->hasFile('file_pdf')) {
            $data['file_pdf'] = $request->file('file_pdf')->store('perpustakaan/pdfs', 'public');
        }

        Buku::create($data);

        return redirect()->route('admin.perpustakaan.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(Buku $buku): View
    {
        $kategoris = KategoriBuku::all();

        return view('admin.perpustakaan.form', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $data = $request->validate([
            'judul' => 'required|max:255',
            'kategori_buku_id' => 'required|exists:kategori_bukus,id',
            'penulis' => 'nullable|max:255',
            'penerbit' => 'nullable|max:255',
            'tahun_terbit' => 'nullable|integer',
            'deskripsi' => 'nullable',
            'stok' => 'required|integer|min:0',
            'cover' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_pdf' => 'nullable|mimes:pdf|max:20480',
        ]);

        if ($request->hasFile('cover')) {
            if ($buku->cover) {
                Storage::disk('public')->delete($buku->cover);
            }
            $data['cover'] = $request->file('cover')->store('perpustakaan/covers', 'public');
        }

        if ($request->hasFile('file_pdf')) {
            if ($buku->file_pdf) {
                Storage::disk('public')->delete($buku->file_pdf);
            }
            $data['file_pdf'] = $request->file('file_pdf')->store('perpustakaan/pdfs', 'public');
        }

        $buku->update($data);

        return redirect()->route('admin.perpustakaan.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku): RedirectResponse
    {
        if ($buku->cover) {
            Storage::disk('public')->delete($buku->cover);
        }
        if ($buku->file_pdf) {
            Storage::disk('public')->delete($buku->file_pdf);
        }
        $buku->delete();

        return back()->with('success', 'Buku berhasil dihapus.');
    }

    public function kategoriIndex(): View
    {
        $kategoris = KategoriBuku::all();

        return view('admin.perpustakaan.kategori', compact('kategoris'));
    }

    public function kategoriStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|max:255|unique:kategori_bukus,nama',
        ]);
        $data['slug'] = Str::slug($data['nama']);
        KategoriBuku::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function kategoriUpdate(Request $request, KategoriBuku $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|max:255|unique:kategori_bukus,nama,'.$kategori->id,
        ]);
        $data['slug'] = Str::slug($data['nama']);
        $kategori->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function kategoriDestroy(KategoriBuku $kategori): RedirectResponse
    {
        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
