<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            // Get authenticated user
            $user = Auth::user();
            
            // Redirect based on role
            if ($request->is('login') || $request->is('/')) {
                if ($user->role === 'coordinator') {
                    return redirect()->route('coordinator.dashboard');
                } elseif ($user->role === 'admin') {
                    return redirect()->route('dashboard');
                }
                // Add other roles as needed
            }
        }
        
        return $next($request);
    }
}
