<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectFailedStaffAuthentication
{
    public function handle($request, Closure $next)
    {
        // Check if the user is accessing a route guarded by the 'staff' guard
        if ($request->route()->getAction('guard') === 'staff') {
            // Check if authentication failed
            if (!Auth::guard('staff')->check()) {
                return redirect('/staff'); // Redirect to /staff
            }
        }

        return $next($request);
    }
}
