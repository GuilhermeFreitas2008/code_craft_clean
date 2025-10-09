<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        // 1. Divide a string de roles em um array (ex: "admin,user" vira ['admin', 'user'])
        $requiredRoles = explode(',', $roles);

        // 2. Verifica se o utilizador está autenticado
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // 3. Verifica se o role do utilizador está no array de roles necessários
        $userRole = $request->user()->role->name;

        if (!in_array($userRole, $requiredRoles)) {
            // Se o role do utilizador NÃO estiver na lista, retorna erro 403
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }

        // Se o role for válido, prossegue
        return $next($request);
    }
}