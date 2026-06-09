<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $userRoleName = $user ? $user->role->name : null;

        if (!$user || !in_array($userRoleName, $roles)) {
            $errorMessage = 'Unauthorized action. Required role(s): ' . implode(', ', $roles);
            if ($user && $userRoleName) {
                $errorMessage .= " (User has role: {$userRoleName})";
            } else {
                $errorMessage .= ' (No user authenticated)';
            }
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 403);
        }

        return $next($request);
    }
}
