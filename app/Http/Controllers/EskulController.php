<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use App\Models\EskulMember;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EskulController extends Controller
{
    // Mobile: List Eskul untuk Siswa
    public function index(Request $request)
    {
        $user = User::findOrFail(session('user_id'));
        $eskuls = Eskul::withCount('members')->where('aktif', true)->get();
        $myEskuls = $user->eskuls()->pluck('eskul_id')->toArray();

        return view('mobile.eskul.index', compact('eskuls', 'myEskuls'));
    }

    // Mobile: Gabung Eskul
    public function join(Request $request, Eskul $eskul)
    {
        $userId = session('user_id');

        $exists = EskulMember::where('user_id', $userId)->where('eskul_id', $eskul->id)->exists();
        if ($exists) {
            EskulMember::where('user_id', $userId)->where('eskul_id', $eskul->id)->delete();

            // Remove from chat group as well
            $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
            if ($group) {
                $group->members()->detach($userId);
            }

            return back()->with('success', 'Berhasil keluar dari eskul ' . $eskul->nama);
        }

        EskulMember::create([
            'user_id' => $userId,
            'eskul_id' => $eskul->id,
            'is_admin' => false
        ]);

        // Add to chat group
        $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
        if ($group) {
            $group->members()->attach($userId);
        }

        return back()->with('success', 'Berhasil bergabung dengan eskul ' . $eskul->nama);
    }

    // Admin: List Eskul (Hanya Admin IT)
    public function adminIndex()
    {
        abort_unless(session('user_role') === 'admin', 403);
        $eskuls = Eskul::with('pembina')->withCount('members')->get();
        $gurus = User::where('role', 'guru')->get();
        return view('admin.eskul.index', compact('eskuls', 'gurus'));
    }

    public function store(Request $request)
    {
        abort_unless(session('user_role') === 'admin', 403);
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        $data = $request->only(['nama', 'deskripsi', 'pembina_id']);
        $data['slug'] = Str::slug($data['nama']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('eskul', 'public');
        }

        $eskul = Eskul::create($data);

        // Create Chat Group for this Eskul
        $group = ChatGroup::create([
            'name' => 'Group ' . $eskul->nama,
            'type' => 'eskul',
            'related_id' => $eskul->id
        ]);

        // If pembina exists, make them admin of this eskul and member of group
        if ($eskul->pembina_id) {
            EskulMember::create([
                'user_id' => $eskul->pembina_id,
                'eskul_id' => $eskul->id,
                'is_admin' => true
            ]);
            $group->members()->attach($eskul->pembina_id);
        }

        return back()->with('success', 'Eskul berhasil dibuat.');
    }

    public function update(Request $request, Eskul $eskul)
    {
        abort_unless(session('user_role') === 'admin', 403);
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        $data = $request->only(['nama', 'deskripsi', 'pembina_id']);
        $data['slug'] = Str::slug($data['nama']);

        if ($request->hasFile('logo')) {
            if ($eskul->logo) {
                Storage::disk('public')->delete($eskul->logo);
            }
            $data['logo'] = $request->file('logo')->store('eskul', 'public');
        }

        $eskul->update($data);

        // Update Chat Group Name
        $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
        if ($group) {
            $group->name = 'Group ' . $eskul->nama;
            $group->save();
        }

        return back()->with('success', 'Eskul berhasil diperbarui.');
    }

    public function destroy(Eskul $eskul)
    {
        abort_unless(session('user_role') === 'admin', 403);

        if ($eskul->logo) {
            Storage::disk('public')->delete($eskul->logo);
        }

        // Remove chat group and its members
        $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
        if ($group) {
            $group->members()->detach();
            $group->delete();
        }

        $eskul->delete();
        return back()->with('success', 'Eskul berhasil dihapus.');
    }

    public function toggle(Eskul $eskul)
    {
        abort_unless(session('user_role') === 'admin', 403);
        $eskul->aktif = !$eskul->aktif;
        $eskul->save();
        return back()->with('success', 'Status eskul berhasil diubah.');
    }

    public function setAdmin(Request $request, Eskul $eskul)
    {
        abort_unless(session('user_role') === 'admin', 403);
        $userId = $request->user_id;

        $member = EskulMember::where('eskul_id', $eskul->id)->where('user_id', $userId)->first();
        if ($member) {
            $member->is_admin = !$member->is_admin;
            $member->save();
        }

        return back()->with('success', 'Status admin eskul berhasil diubah.');
    }
}
