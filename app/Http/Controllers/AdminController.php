<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
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
}
