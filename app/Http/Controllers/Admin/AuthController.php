<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the admin login form
     */
    public function showLogin()
    {
        return view("admin.auth.login");
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $credentials = $request->only("email", "password");

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if user is admin
            if ($user->hasAnyRole("admin", "manager", "user")) {
                $request->session()->regenerate();
                return redirect()
                    ->intended(route("admin.dashboard"))
                    ->with("success", "Welcome to admin panel!");
            } else {
                Auth::logout();
                throw ValidationException::withMessages([
                    "email" => "You do not have admin access.",
                ]);
            }
        }

        throw ValidationException::withMessages([
            "email" => "The provided credentials do not match our records.",
        ]);
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route("login")
            ->with("success", "You have been logged out successfully.");
    }
}
