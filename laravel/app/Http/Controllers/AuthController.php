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
        return response()->json(['message' => 'Render login form at /XX_module_d/login']);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('publisher')->where('username', $data['username'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account disabled'], 401);
        }

        if ($user->isPublisherAdmin() && (!$user->publisher || !$user->publisher->is_active)) {
            return response()->json(['message' => 'Publisher disabled'], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['message' => 'Login success']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout success']);
    }
}
