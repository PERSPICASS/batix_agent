<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ((bool) $request->session()->get('growth_authenticated', false)) {
            return redirect()->route('growth.index');
        }

        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $throttleKey = Str::lower($credentials['username']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors(['username' => "Trop de tentatives. Réessayez dans {$seconds} seconde(s)."])
                ->onlyInput('username');
        }

        $expectedUser = (string) config('growth.admin_user');
        $expectedPassword = (string) config('growth.admin_password');

        if ($expectedUser === '' || $expectedPassword === '') {
            abort(503, 'ADMIN_USER / ADMIN_PASSWORD must be configured.');
        }

        $valid = hash_equals($expectedUser, $credentials['username'])
            && hash_equals($expectedPassword, $credentials['password']);

        if (! $valid) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withErrors(['username' => 'Identifiant ou mot de passe incorrect.'])
                ->onlyInput('username');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->put([
            'growth_authenticated' => true,
            'growth_admin' => $expectedUser,
        ]);

        return redirect()->intended(route('growth.index'))
            ->with('success', 'Connexion réussie.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['growth_authenticated', 'growth_admin']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous êtes déconnecté.');
    }
}
