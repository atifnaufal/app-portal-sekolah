<?php

namespace App\Http\Controllers;

use App\Helpers\UserHistoryHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function resolveUser(Request $request): User
    {
        $userId = $request->session()->get('user_id');
        if (! $userId && Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        }

        return User::with('kelas')->findOrFail($userId);
    }

    public function show(Request $request): View
    {
        return view('mobile.profile', ['user' => $this->resolveUser($request)]);
    }

    public function edit(Request $request): View
    {
        return view('mobile.profile-edit', ['user' => $this->resolveUser($request)]);
    }

    public function update(Request $request)
    {
        $user = $this->resolveUser($request);

        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'foto_posisi_x' => ['nullable', 'integer', 'between:0,100'],
            'foto_posisi_y' => ['nullable', 'integer', 'between:0,100'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = $request->file('foto')->store('profile', 'public');
        }

        $user->update($data);
        $request->session()->put('admin_name', $user->name);

        UserHistoryHelper::logProfileUpdate($user->id, array_keys($data), $request);

        if ($request->expectsJson() || str_contains($request->header('Accept', ''), 'application/json')) {
            return response()->json([
                'ok' => true,
                'message' => 'Profil berhasil diperbarui.',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'foto' => $user->foto ? asset('storage/'.$user->foto) : null,
                    'foto_posisi_x' => $user->foto_posisi_x,
                    'foto_posisi_y' => $user->foto_posisi_y,
                    'role' => $user->role,
                    'kelas' => $user->kelas?->nama,
                ],
            ]);
        }

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }

    public function uploadFoto(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }

        $path = $request->file('foto')->store('profile', 'public');
        $user->update(['foto' => $path]);

        return response()->json([
            'ok' => true,
            'url' => asset('storage/'.$path),
            'message' => 'Foto berhasil diupload.',
        ]);
    }

    public function updateFotoPosisi(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $data = $request->validate([
            'foto_posisi_x' => ['required', 'integer', 'between:0,100'],
            'foto_posisi_y' => ['required', 'integer', 'between:0,100'],
        ]);

        $user->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Posisi foto diperbarui.',
        ]);
    }
}
