<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('auth_user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('role')
            ->where('username', $request->username)
            ->where('status', 'active')
            ->first();

        if (!$user || !password_verify($request->password, $user->password_hash)) {
            return back()->withErrors(['login' => 'Invalid username or password.'])->withInput(['username' => $request->username]);
        }

        session([
            'auth_user_id' => $user->user_id,
            'auth_role'    => $user->role->role_name ?? 'Unknown',
        ]);

        return redirect()->route('dashboard');
    }

    public function logout()
    {
        session()->flush();
        return redirect()->route('login');
    }
}
