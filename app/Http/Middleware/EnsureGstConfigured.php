<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;
use Session;
class EnsureGstConfigured
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
        $user = auth()->user();

        // Skip if not logged in (optional, depends on your app)
        if (!$user) {
            return $next($request);
        }
        // Skip GST settings page to avoid redirect loop
        if ($request->routeIs('gst-setting.index')) {
            return $next($request);
        }
        // Check GST configuration (adjust condition as per your schema)
        $gst1 = DB::table('gst_settings')
                    ->where('company_id', Session::get('user_company_id'))                    
                    ->exists();

        $gst2 = DB::table('gst_settings_multiple')
                    ->where('company_id', Session::get('user_company_id'))
                    ->exists();
        if (!$gst1 && !$gst2) {
            return redirect()
                ->route('gst-setting.index')
                ->with('error', 'GST Configuration is pending. Please configure it first.');
        }
        return $next($request);
    }
}
