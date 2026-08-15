<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class GrowthLoginController
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->session()->has('growth_admin_id')) {
            return redirect()->route('growth.index');
        }

        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $admin = AdminUser::query()->where('username', $credentials['username'])->first();
        $valid = $admin?->is_active && Hash::check($credentials['password'], $admin->password);

        if (! $valid) {
            return back()->withErrors(['username' => 'Identifiant ou mot de passe incorrect.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        $request->session()->put('growth_admin_id', $admin->id);
        $admin->update(['last_login_at' => now()]);

        return redirect()->intended(route('growth.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('growth_admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('growth.login');
    }
}
