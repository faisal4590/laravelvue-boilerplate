<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StudentAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Add role check here if needed
        // if (!auth()->user()->hasRole('admin')) {
        //     return redirect()->route('dashboard')->with('error', 'Unauthorized access');
        // }

        // Log access attempts
        Log::info('Student management access', [
            'user' => auth()->user()->email,
            'action' => $request->method(),
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}