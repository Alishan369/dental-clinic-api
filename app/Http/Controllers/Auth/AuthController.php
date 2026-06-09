<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $email = strtolower($request->email);
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $roles = Role::whereIn('name', ['doctor', 'receptionist', 'admin', 'accountant'])->pluck('id')->toArray();
        if (!in_array($user->role_id, $roles)) {
            return $this->errorResponse('Access denied', 403);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Account is not active. Current status: ' . $user->status, 403);
        }

        $token = $user->createToken('clinic-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role ? $user->role->name : null,
                'status'=> $user->status,
            ]
        ], 'Logged in successfully');
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return $this->successResponse([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role ? $user->role->name : null,
            'status'=> $user->status,
        ], 'Profile retrieved successfully');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return $this->successResponse([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role ? $user->role->name : null,
            'status'=> $user->status,
        ], 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password does not match.', 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return $this->successResponse(null, 'Password changed successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }
}
