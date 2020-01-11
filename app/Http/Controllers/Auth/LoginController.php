<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only(['email', 'password']))) {
            return redirect('/login')->withInput(request(['email']))->withErrors([
                'email' => ['These credentials does not match '],
            ]);
        }

        return redirect('/backstage/concerts/new');
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}
