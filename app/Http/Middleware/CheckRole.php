<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        $userRole = $request->user()->rol->nombre_rol;

        if ($userRole !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Rol requerido: ' . $role . ', Rol actual: ' . $userRole
            ], 403);
        }

        return $next($request);
    }
} 