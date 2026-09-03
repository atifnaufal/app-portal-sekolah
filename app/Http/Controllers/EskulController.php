<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserContextHelper;
use App\Models\ChatGroup;
use App\Models\Eskul;
use App\Models\EskulMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EskulController extends Controller
{
    // Mobile: List Eskul untuk Siswa
    public function index(Request $request)
    {
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $userId = $user->id;
        $eskuls = Eskul::with('pembina')->withCount(['members' => function ($q) {
            $q->where('eskul_members.status', 'approved');
        }])->where('aktif', true)->get();
        $myEskuls = $user->eskuls()->pluck('eskul_id')->toArray();

        // Jumlah eskul aktif (approved/pending) yang sedang diikuti siswa,
        // dipakai untuk memblokir join saat sudah mencapai batas 3.
        $myCount = $user->role === 'siswa'
            ? EskulMember::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'pending'])
                ->count()
            : 0;
        $maxEskul = 3;

        return view('mobile.eskul.index', compact('eskuls', 'myEskuls', 'myCount', 'maxEskul'));
    }

    // Mobile: Gabung Eskul
    public function join(Request $request, Eskul $eskul)
    {
        $userId = UserContextHelper::id($request);

        $member = EskulMember::where('user_id', $userId)->where('eskul_id', $eskul->id)->first();
        if ($member) {
            $member->delete();

            // Remove from chat group as well
            $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
            if ($group) {
                $group->members()->detach($userId);
            }

            return back()->with('success', 'Berhasil membatalkan permintaan gabung/keluar dari eskul '.$eskul->nama);
        }

        // Enforce max 3 approved/pending eskul per student
        if (session('user_role') === 'siswa') {
            $currentCount = EskulMember::where('user_id', $userId)
                ->whereIn('status', ['approved', 'pending'])
                ->count();
            if ($currentCount >= 3) {
                return back()->with('error', 'Anda hanya dapat mengikuti maksimal 3 eskul. Silakan keluar dari salah satu eskul terlebih dahulu.');
            }
        }

        EskulMember::create([
            'user_id' => $userId,
            'eskul_id' => $eskul->id,
            'is_admin' => false,
            'status' => 'pending', // Default pending, wait for admin eskul approval
        ]);

        // Notify Pembina
        if ($eskul->pembina_id) {
            NotificationHelper::send(
                $eskul->pembina_id,
                'Permintaan Gabung Eskul',
                'Siswa '.User::find($userId)->name.' ingin bergabung dengan eskul '.$eskul->nama,
                route('eskul.members', $eskul),
                'eskul'
            );
        }

        return back()->with('success', 'Permintaan bergabung dengan eskul '.$eskul->nama.' telah dikirim. Menunggu persetujuan admin.');
    }

    // Mobile: Lihat & Kelola Member Eskul (Hanya untuk Admin Eskul)
    public function members(Request $request, Eskul $eskul)
    {
        $userId = UserContextHelper::id($request);
        $isEskulAdmin = EskulMember::where('eskul_id', $eskul->id)
            ->where('user_id', $userId)
            ->where('is_admin', true)
            ->exists();

        abort_unless($isEskulAdmin || UserContextHelper::role($request) === 'admin', 403);

        $members = EskulMember::with('user')->where('eskul_id', $eskul->id)->latest()->get();

        return view('mobile.eskul.members', compact('eskul', 'members'));
    }

    public function approveMember(Request $request, EskulMember $member)
    {
        $userId = UserContextHelper::id($request);
        $isEskulAdmin = EskulMember::where('eskul_id', $member->eskul_id)
            ->where('user_id', $userId)
            ->where('is_admin', true)
            ->exists();

        abort_unless($isEskulAdmin || UserContextHelper::role($request) === 'admin', 403);

        $member->status = 'approved';
        $member->save();

        // Notify User
        NotificationHelper::send(
            $member->user_id,
            'Eskul Disetujui',
            'Anda telah disetujui bergabung dengan eskul '.$member->eskul->nama,
            route('eskul.index'),
            'eskul'
        );

        // Add to chat group after approval (create group if it does not exist)
        $group = ChatGroup::firstOrCreate(
            ['type' => 'eskul', 'related_id' => $member->eskul_id],
            ['name' => 'Group '.$member->eskul->nama]
        );
        $group->members()->syncWithoutDetaching([$member->user_id]);

        return back()->with('success', 'Member berhasil disetujui.');
    }

    public function rejectMember(Request $request, EskulMember $member)
    {
        $userId = UserContextHelper::id($request);
        $isEskulAdmin = EskulMember::where('eskul_id', $member->eskul_id)
            ->where('user_id', $userId)
            ->where('is_admin', true)
            ->exists();

        abort_unless($isEskulAdmin || UserContextHelper::role($request) === 'admin', 403);

        // Remove from chat group
        $group = ChatGroup::where('type', 'eskul')->where('related_id', $member->eskul_id)->first();
        if ($group) {
            $group->members()->detach($member->user_id);
        }

        $member->delete();

        return back()->with('success', 'Permintaan member ditolak.');
    }

    // Admin: List Eskul (Hanya Admin IT)
    public function adminIndex(Request $request)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);
        $eskuls = Eskul::with(['pembina', 'members'])->withCount('members')->get();
        $gurus = User::where('role', 'guru')->get();

        // Guru yang sudah menjadi admin di (setidaknya) satu eskul => tidak boleh ditunjuk lagi
        $adminGuruIds = EskulMember::where('is_admin', true)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return view('admin.eskul.index', compact('eskuls', 'gurus', 'adminGuruIds'));
    }

    public function store(Request $request)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'pembina_id']);
        $data['slug'] = Str::slug($data['nama']);

        // Guru hanya boleh menjadi admin di satu eskul
        if ($request->pembina_id) {
            $pembina = User::find($request->pembina_id);
            $isAdminElsewhere = $pembina && $pembina->role === 'guru' && EskulMember::where('user_id', $pembina->id)
                ->where('is_admin', true)
                ->exists();
            if ($isAdminElsewhere) {
                return back()->with('error', 'Guru '.$pembina->name.' sudah menjadi admin eskul lain. Satu guru hanya boleh menjadi admin 1 eskul.')->withInput();
            }
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('eskul', 'public');
        }

        $eskul = Eskul::create($data);

        // Create Chat Group for this Eskul
        $group = ChatGroup::create([
            'name' => 'Group '.$eskul->nama,
            'type' => 'eskul',
            'related_id' => $eskul->id,
        ]);

        // If pembina exists, make them admin of this eskul and member of group
        if ($eskul->pembina_id) {
            EskulMember::create([
                'user_id' => $eskul->pembina_id,
                'eskul_id' => $eskul->id,
                'is_admin' => true,
                'status' => 'approved',
            ]);
            $group->members()->attach($eskul->pembina_id);
        }

        return back()->with('success', 'Eskul berhasil dibuat.');
    }

    public function update(Request $request, Eskul $eskul)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $data = $request->only(['nama', 'deskripsi', 'pembina_id']);
        $data['slug'] = Str::slug($data['nama']);

        // Guru hanya boleh menjadi admin di satu eskul (periksa eskul lain)
        if ($request->pembina_id) {
            $pembina = User::find($request->pembina_id);
            $isAdminElsewhere = $pembina && $pembina->role === 'guru'
                && EskulMember::where('user_id', $pembina->id)
                    ->where('is_admin', true)
                    ->where('eskul_id', '!=', $eskul->id)
                    ->exists();
            if ($isAdminElsewhere) {
                return back()->with('error', 'Guru '.$pembina->name.' sudah menjadi admin eskul lain. Satu guru hanya boleh menjadi admin 1 eskul.')->withInput();
            }
        }

        if ($request->hasFile('logo')) {
            if ($eskul->logo && Storage::disk('public')->exists($eskul->logo)) {
                Storage::disk('public')->delete($eskul->logo);
            }
            $data['logo'] = $request->file('logo')->store('eskul', 'public');
        }

        $eskul->update($data);

        // Update Chat Group Name
        $group = ChatGroup::where('type', 'eskul')->where('related_id', $eskul->id)->first();
        if ($group) {
            $group->name = 'Group '.$eskul->nama;
            $group->save();
        }

        return back()->with('success', 'Eskul berhasil diperbarui.');
    }

    public function destroy(Request $request, Eskul $eskul)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);

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

    public function toggle(Request $request, Eskul $eskul)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);
        $eskul->aktif = ! $eskul->aktif;
        $eskul->save();

        return back()->with('success', 'Status eskul berhasil diubah.');
    }

    public function setAdmin(Request $request, Eskul $eskul)
    {
        abort_unless(UserContextHelper::role($request) === 'admin', 403);
        $userId = $request->user_id;

        $member = EskulMember::where('eskul_id', $eskul->id)->where('user_id', $userId)->first();
        if ($member) {
            if ($member->status !== 'approved') {
                return back()->with('error', 'Hanya anggota yang sudah disetujui yang bisa dijadikan admin.');
            }

            $becomingAdmin = ! $member->is_admin;
            if ($becomingAdmin) {
                $user = User::find($userId);
                $isAdminElsewhere = EskulMember::where('user_id', $userId)
                    ->where('is_admin', true)
                    ->where('eskul_id', '!=', $eskul->id)
                    ->exists();
                if ($isAdminElsewhere) {
                    $label = $user && $user->role === 'guru' ? 'Guru ini' : 'Anggota ini';

                    return back()->with('error', $label.' sudah menjadi admin eskul lain. Seseorang hanya boleh menjadi admin 1 eskul.');
                }
            }

            $member->is_admin = ! $member->is_admin;
            $member->save();

            return back()->with('success', 'Status admin eskul berhasil diubah.');
        }

        return back()->with('error', 'Anggota tidak ditemukan.');
    }
}
