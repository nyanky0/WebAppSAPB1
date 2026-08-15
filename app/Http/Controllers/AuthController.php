<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt with Brute-Force Rate Limiting & Audit Logging.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 1. Rate Limiting Key based on username and IP address
        $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();

        // 2. Check if user exceeded 5 login attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            SystemLog::logAction('security_alert', 'Login Throttled', "Excessive failed login attempts for username '{$credentials['username']}' from IP {$request->ip()}");

            return back()->withErrors([
                'username' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('username');
        }

        // 3. Attempt Authentication with Parameterized PDO Queries (Safe against SQL Injection)
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            
            SystemLog::logAction('login', 'User Logged In', 'Successful login via web interface');

            return redirect()->intended('/dashboard');
        }

        // 4. Record failed login attempt and hit rate limiter
        RateLimiter::hit($throttleKey, 60);

        SystemLog::logAction('login_failed', 'Failed Login Attempt', "Failed login attempt for username '{$credentials['username']}' from IP {$request->ip()}");

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        SystemLog::logAction('login', 'User Logged Out', 'User triggered manual logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
