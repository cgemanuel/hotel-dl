<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSuperuser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->rol !== 'superusuario') {
            abort(403, 'Acceso restringido: Solo superusuarios pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
