<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('books.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('publisher')->where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['username' => '帳號或密碼錯誤'])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors(['username' => '此帳號已停用'])->withInput();
        }

        if ($user->isPublisherAdmin() && (!$user->publisher || !$user->publisher->is_active)) {
            return back()->withErrors(['username' => '所屬出版社已停用，無法登入'])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('books.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
