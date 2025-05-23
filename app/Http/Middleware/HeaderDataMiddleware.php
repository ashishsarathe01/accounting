<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Companies; 
use Session;

class HeaderDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
   public function handle(Request $request, Closure $next){
      if(Session::get('user_type')=="OWNER"){
         $user_id = Session::get('user_id');
         $company_list = Companies::where('user_id', $user_id)->get();
      }else if(Session::get('user_type')=="EMPLOYEE"){
         $user = Companies::select('user_id')->where('id', Session::get('user_company_id'))->first();
         $company_list = Companies::where('user_id', $user->user_id)->get();
      }else{
         $user_id = Session::get('user_id');
         $company_list = Companies::where('user_id', $user_id)->get();
      }
      
      View::share('company_list', $company_list);


      return $next($request);
   }
}
