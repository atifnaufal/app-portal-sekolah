<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('mobile.profile', ['user' => User::with('kelas')->findOrFail($request->session()->get('user_id'))]);
    }

    public function edit(Request $request): View
    {
        return view('mobile.profile-edit', ['user' => User::with('kelas')->findOrFail($request->session()->get('user_id'))]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('user_id'));
        $data = $request->validate([
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'foto_posisi_x' => ['required', 'integer', 'between:0,100'],
            'foto_posisi_y' => ['required', 'integer', 'between:0,100'],
        ]);
        if (blank($data['password'])) unset($data['password']); else $data['password'] = Hash::make($data['password']);
        if ($request->hasFile('foto')) $data['foto'] = $request->file('foto')->store('profile', 'public');
        $user->update($data);
        $request->session()->put('admin_name', $user->name);
        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
