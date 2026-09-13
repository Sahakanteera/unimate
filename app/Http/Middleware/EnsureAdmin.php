<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้าสำหรับผู้ดูแลระบบ (สำหรับ Admin เท่านั้น)');
        }

        return $next($request);
    }
}
