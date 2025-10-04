<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // تحقق إذا كان المستخدم مسجل دخول وهو مدير
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->is_admin) {
            // إذا كان مديرًا، اسمح له بالمرور
            return $next($request);
        }

        // إذا لم يكن مديرًا، قم بإيقافه وإظهار صفحة خطأ 403
        abort(403, 'UNAUTHORIZED ACCESS');
    }
}
