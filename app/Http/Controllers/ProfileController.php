<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    public function index(){ return view('profile.index'); }


public function update(Request $request)
{
    $request->validate([
        'name'   => ['required','string','max:255'],
        'avatar' => ['nullable','image','mimes:png,jpg,jpeg','max:2048'],
    ]);

    $user = $request->user();
    $user->name = $request->name;

    if ($request->hasFile('avatar')) {
        // store on "public" disk => storage/app/public/avatars/xxx.png
        $path = $request->file('avatar')->store('avatars', 'public');
        // url becomes "/storage/avatars/xxx.png"
        $user->avatar_url = Storage::url($path);
    }

    $user->save();

    return back()->with('ok', 'Profile updated.');
}

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required','confirmed','min:8'],
        ]);

        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        return back()->with('status','Password updated.');
    }
}
