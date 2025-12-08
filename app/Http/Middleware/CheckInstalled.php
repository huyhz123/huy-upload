<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if application is installed
        if (!file_exists(storage_path('installed')) && !$request->is('installer*')) {
            return redirect()->route('installer.index');
        }

        // If installed and trying to access installer, redirect to home
        if (file_exists(storage_path('installed')) && $request->is('installer*')) {
            return redirect('/')->with('info', 'Application is already installed.');
        }

        return $next($request);
    }
}
