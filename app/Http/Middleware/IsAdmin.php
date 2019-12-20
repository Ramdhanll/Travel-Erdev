<?php

namespace App\Http\Middleware;

use Closure;
use  Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::user() && Auth::user()->roles == 'ADMIN'){ // setelah dibuat fungsi ini, lalu didaftarkan di kernel.php
            return $next($request);
        }
        return redirect('/');
    }
}
