<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next)
    {
        // Demo keeps route open if no auth installed
        if (!Auth::check()) return $next($request);
        if (Auth::user()->role !== 'teacher') abort(403);
        return $next($request);
    }
}
