<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function register(array $data): User
    {
        $role = Role::findOrFail($data['role_id']);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => 'pending',
        ]);

        // Ensure role relationship is available in response
        return $user->load('role');
    }
}

