<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class LogSlowRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
         
        $start = microtime(true);

        $response = $next($request);

        $time = microtime(true) - $start;

        if ($time > 10) {
            Log::warning('Slow Request', [
                'url'    => $request->fullUrl(),
                'method' => $request->method(),
                'time'   => round($time, 2) . ' sec'
            ]);
        }

        return $response;
    }
}
