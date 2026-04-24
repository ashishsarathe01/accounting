<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Models\Companies;
use App\Models\PrivilegesModuleMapping;
use App\Models\User;
use Session;
use DB;
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
         if(Session::get('admin_id') && Session::get('admin_id')!=''){
            $assign_company = DB::table('assign_companies')
                        ->where('admin_users_id', Session::get('admin_id'))
                        ->where('merchant_id', $user_id)
                        ->pluck('comp_id')
                        ->toArray();
                        
            $company_list = Companies::where('user_id', $user_id)->where('delete','0')->whereIn('id',$assign_company)->get();
            
         }else{
            $login_user_mobile = User::find( $user_id);
            $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                                    ->where('type','OWNER')
                                    ->where('status','1')
                                    ->where('delete_status','0')
                                    ->pluck('id');
            $login_user_emp_comp = User::where('mobile_no',$login_user_mobile->mobile_no)
                                    ->where('type','!=','OWNER')
                                    ->where('status','1')
                                    ->where('delete_status','0')
                                    ->pluck('company_id');  
            $company_list_emp = Companies::whereIn('id', $login_user_emp_comp)->where('delete','0')->get();
            $company_list = Companies::whereIn('user_id', $login_user_id)->where('delete','0')->get();
            $company_list = $company_list
                                    ->merge($company_list_emp)
                                    ->unique()
                                    ->values();
         }
         
      }else if(Session::get('user_type')=="EMPLOYEE" || Session::get('user_type')=="OTHER" || Session::get('user_type')=="ACCOUNTANT" || Session::get('user_type')=="CA"){
          
         $login_user_mobile = User::find(Session('user_id'));
         $login_user_id = User::where('mobile_no',$login_user_mobile->mobile_no)
                           ->where('status','1')
                           ->where('delete_status','0')
                           ->pluck('id');
         $login_user_id_owner = User::where('mobile_no',$login_user_mobile->mobile_no)
                           ->where('type','OWNER')
                           ->where('status','1')
                           ->where('delete_status','0')
                           ->pluck('id');  
         $company_list_owner = Companies::whereIn('user_id', $login_user_id_owner)->where('delete','0')->get(); 
         $assign_company = PrivilegesModuleMapping::whereIn('employee_id',$login_user_id)
                                                         ->pluck('company_id')
                                                         ->toArray();         
         $user = Companies::select('user_id')
                           ->where('id', Session::get('user_company_id'))
                           ->first();
         $company_list = Companies::where('user_id', $user->user_id)
                                        ->where('delete','0')
                                    ->whereIn('id',$assign_company)
                                    ->get();
         $company_list = $company_list
                                    ->merge($company_list_owner)
                                    ->unique()
                                    ->values();
         
      }else{
          
         $user_id = Session::get('user_id');
         $company_list = Companies::where('user_id', $user_id)->where('delete','0')->get();
      }
      
      View::share('company_list', $company_list);
      return $next($request);
   }
}
