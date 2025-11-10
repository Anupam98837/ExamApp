<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     * @param  string[]  ...$roles    // e.g. 'admin', 'student'
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1) Get raw token: prefer Bearer, fallback to plain 'token' header
        $raw = $request->bearerToken()
             ?: $request->header('token')
             ?: $request->header('Authorization');

        if (! $raw) {
            return response()->json(['error' => 'Authorization token required'], 401);
        }

        // If they sent "Bearer xyz" in the fallback header, strip it
        if (str_starts_with($raw, 'Bearer ')) {
            $raw = substr($raw, 7);
        }

        $hashed = hash('sha256', $raw);

        // 2) Check personal_access_tokens for any of the allowed roles
        foreach ($roles as $role) {
            $found = DB::table('personal_access_tokens')
                ->where('tokenable_type', $role)
                ->where('token', $hashed)
                ->first();

            if ($found) {
                // Optionally you can attach the tokenable_id to the request:
                // $request->attributes->set('auth_id', $found->tokenable_id);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
