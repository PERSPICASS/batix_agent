<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController
{
    public function index(): Response
    {
        return Inertia::render('Admins/Index', [
            'admins' => AdminUser::query()->latest()->get([
                'id', 'name', 'username', 'is_active', 'last_login_at', 'created_at',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|alpha_dash|unique:admin_users,username',
            'password' => 'required|string|min:12|max:255',
        ]);

        AdminUser::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Administrateur créé.');
    }

    public function update(Request $request, AdminUser $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', Rule::unique('admin_users', 'username')->ignore($admin->id)],
            'password' => 'nullable|string|min:12|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $currentAdminId = $request->session()->get('growth_admin_id');
        if ($currentAdminId === $admin->id && array_key_exists('is_active', $data) && ! $data['is_active']) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return back()->with('success', 'Administrateur mis à jour.');
    }
}
