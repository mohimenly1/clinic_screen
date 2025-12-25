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
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // إذا كان المستخدم مدير، لديه جميع الصلاحيات
        if ($user && $user->is_admin) {
            return $next($request);
        }

        // التحقق من الصلاحية
        if ($user && $user->hasPermission($permission)) {
            return $next($request);
        }

        // إذا لم يكن لديه الصلاحية، قم بإيقافه
        abort(403, 'UNAUTHORIZED ACCESS - Missing permission: ' . $permission);
    }
}
