<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isSuspended()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', '⚠️ บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อนักศึกษาผู้ดูแลระบบ');
        }

        return $next($request);
    }
}
