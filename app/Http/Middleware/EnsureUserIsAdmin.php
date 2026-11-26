<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        if (! $request->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini hanya dapat diakses oleh Admin.');
        }

        return $next($request);
    }
}
