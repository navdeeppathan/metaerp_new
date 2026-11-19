<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module Module name(s), can be pipe-separated e.g. "PRODUCT" or "DETACHED_NOTES|CONTRACT"
     * @param  string|null  $accessType 'FULL' or 'READ' (or null for general access)
     */
    public function handle(Request $request, Closure $next, string $module, ?string $accessType = null): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*') || $request->is('erp-api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Split multiple modules if provided with pipe, e.g. "DETACHED_NOTES|CONTRACT"
        $modules = explode('|', $module);

        if (!$user->hasPermission($modules, $accessType)) {
            if ($request->expectsJson() || $request->is('api/*') || $request->is('erp-api/*')) {
                return response()->json([
                    'message' => 'Forbidden: You do not have permission to perform this action.',
                    'module' => $module,
                    'required_access' => $accessType ?: 'READ'
                ], 403);
            }

            abort(403, "Forbidden: You do not have permission to access or modify this section ({$module}).");
        }

        return $next($request);
    }
}
