<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()
                ->route("login")
                ->with("error", "Please login to access admin panel.");
        }

        // Check if user has admin role or any admin permissions
        $user = auth()->user();

        // Check legacy admin role first for backward compatibility
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any admin permissions via roles
        if ($user->can("admin.dashboard")) {
            return $next($request);
        }

        return redirect("/")->with(
            "error",
            "You do not have permission to access the admin panel.",
        );

        return $next($request);
    }
}
