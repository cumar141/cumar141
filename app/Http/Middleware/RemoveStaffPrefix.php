<?php

namespace App\Http\Middleware;

use Closure;

class RemoveStaffPrefix
{
    public function handle($request, Closure $next)
    {
        $prefix = trim(config('adminPrefix'), '/');
        
        if ($request->is("{$prefix}/*")) {
            $path = ltrim(substr($request->getPathInfo(), strlen($prefix)), '/');
            $request->server->set('REQUEST_URI', $path);
        }

        return $next($request);
    }
}
