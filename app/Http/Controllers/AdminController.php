<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Get all users.
     */
    public function index()
    {
        $users = User::with('role')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : null,
                'status' => $user->status,
            ];
        });

        return $this->successResponse($users, 'Users retrieved successfully.');
    }

    /**
     * Accept a pending user.
     */
    public function acceptUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        return $this->successResponse($user, 'User accepted successfully.');
    }

    /**
     * Reject a pending user.
     */
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'inactive']);

        return $this->successResponse($user, 'User rejected successfully.');
    }

    /**
     * Toggle user status.
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return $this->successResponse($user, "User status changed to {$newStatus}.");
    }
}
