<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function edit()
    {
        return view('profil', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'password_lama' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:6|confirmed',
        ];
        if ($user->isAdmin()) {
            $rules['name'] = 'required|string|max:255';
        }
        $data = $request->validate($rules, [], [
            'name' => 'Nama', 'password' => 'Password baru', 'password_lama' => 'Password lama',
        ]);

        if (! empty($data['password'])) {
            if (! Hash::check($data['password_lama'], $user->password)) {
                return back()->with('error', 'Password lama tidak sesuai.');
            }
            $user->password = $data['password'];
        }
        if ($user->isAdmin()) {
            $user->name = $data['name'];
        }
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
