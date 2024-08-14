<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStaff
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->type === 'staff') {
            return $next($request);
        }

        // If not a staff member, you can redirect or take other actions.
        return redirect()->route('home'); // Adjust 'home' to your actual route.
    }
    
}
