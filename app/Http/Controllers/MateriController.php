<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function create(Request $request, MataPelajaran $mapel): View
    {
        $this->assertGuruOfMapel($request, $mapel);

        return view('mobile.materi-form', [
            'mapel' => $mapel,
            'materi' => new Materi(['mata_pelajaran_id' => $mapel->id, 'user_id' => $this->userId($request)]),
        ]);
    }

    public function store(Request $request, MataPelajaran $mapel): RedirectResponse
    {
        $this->assertGuruOfMapel($request, $mapel);

        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'deskripsi' => ['nullable'],
            'video_url' => ['nullable', 'url'],
            'file_materi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,xls,ppt,pptx,csv,txt,zip,mp4,mov', 'max:51200'],
        ], [
            'file_materi.*' => 'Dokumen atau video harus berupa file yang diizinkan (maks 50MB).',
        ]);

        $data['user_id'] = $this->userId($request);
        $data['mata_pelajaran_id'] = $mapel->id;

        if ($request->hasFile('file_materi')) {
            $data['file_materi'] = $request->file('file_materi')->store('materi', 'public');
            $data['file_nama'] = $request->file('file_materi')->getClientOriginalName();
        } else {
            unset($data['file_materi'], $data['file_nama']);
        }

        $materi = Materi::create($data);

        NotificationHelper::sendToClass(
            $mapel->kelas_id,
            'Materi Baru: '.$materi->judul,
            'Guru membagikan materi baru pada mata pelajaran '.$mapel->nama.'.',
            route('materi.show', [$mapel, $materi]),
            'book'
        );

        return redirect()->route('materi.show', [$mapel, $materi])->with('success', 'Materi berhasil dibagikan.');
    }

    public function show(Request $request, MataPelajaran $mapel, Materi $materi): View
    {
        $this->assertAccess($request, $mapel, $materi);

        return view('mobile.materi-detail', [
            'mapel' => $mapel,
            'materi' => $materi,
            'user' => User::findOrFail($this->userId($request)),
        ]);
    }

    public function edit(Request $request, MataPelajaran $mapel, Materi $materi): View
    {
        $this->assertOwner($request, $materi);

        return view('mobile.materi-form', [
            'mapel' => $mapel,
            'materi' => $materi,
        ]);
    }

    public function update(Request $request, MataPelajaran $mapel, Materi $materi): RedirectResponse
    {
        $this->assertOwner($request, $materi);

        $data = $request->validate([
            'judul' => ['required', 'max:255'],
            'deskripsi' => ['nullable'],
            'video_url' => ['nullable', 'url'],
            'file_materi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,xls,ppt,pptx,csv,txt,zip,mp4,mov', 'max:51200'],
        ]);

        $data['mata_pelajaran_id'] = $mapel->id;

        if ($request->boolean('hapus_file') && $materi->file_materi) {
            Storage::disk('public')->delete($materi->file_materi);
            $data['file_materi'] = null;
            $data['file_nama'] = null;
        }

        if ($request->hasFile('file_materi')) {
            if ($materi->file_materi) {
                Storage::disk('public')->delete($materi->file_materi);
            }
            $data['file_materi'] = $request->file('file_materi')->store('materi', 'public');
            $data['file_nama'] = $request->file('file_materi')->getClientOriginalName();
        }

        $materi->update($data);

        return redirect()->route('materi.show', [$mapel, $materi])->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, MataPelajaran $mapel, Materi $materi): RedirectResponse
    {
        $this->assertOwner($request, $materi);

        if ($materi->file_materi) {
            Storage::disk('public')->delete($materi->file_materi);
        }

        $materi->delete();

        NotificationHelper::sendToClass(
            $mapel->kelas_id,
            'Materi Dihapus',
            'Materi "'.$materi->judul.'" telah dihapus oleh guru.',
            route('mapel.show', $mapel),
            'book'
        );

        return redirect()->route('mapel.show', $mapel)->with('success', 'Materi berhasil dihapus.');
    }

    private function userId(Request $request): int
    {
        return (int) ($request->session()->get('user_id') ?: Auth::id());
    }

    private function assertGuruOfMapel(Request $request, MataPelajaran $mapel): void
    {
        $user = User::findOrFail($this->userId($request));
        abort_unless($user->role === 'guru' && (int) $mapel->guru_id === (int) $user->id, 403);
    }

    private function assertOwner(Request $request, Materi $materi): void
    {
        $user = User::findOrFail($this->userId($request));
        $isOwner = (int) $materi->user_id === (int) $user->id;
        $isAdmin = $user->role === 'admin';
        abort_unless($isOwner || $isAdmin, 403);
    }

    private function assertAccess(Request $request, MataPelajaran $mapel, Materi $materi): void
    {
        $user = User::findOrFail($this->userId($request));

        if ($user->role === 'admin') {
            return;
        }
        if ($user->role === 'guru') {
            abort_unless((int) $mapel->guru_id === (int) $user->id, 403);
        }
        if ($user->role === 'siswa') {
            abort_unless((int) $mapel->kelas_id === (int) $user->kelas_id, 403);
        }
    }
}
