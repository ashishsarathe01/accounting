<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hash;
use Session;
class AdminAuthController extends Controller{
   public function index(){
      if(Session::get('admin_id') && !empty(Session::get('admin_id'))){
         return redirect()->intended('admin/dashboard');
      }else{
         return view('admin-module.auth.login', ['url' => 'admin']);
      }
   }
   public function adminLogin(Request $request){
      $this->validate($request, [
         'email'   => 'required|email',
         'password' => 'required|min:6'
      ]);
      //echo $request->password;die;
      if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))){         
         $admin_data  = Auth::guard('admin')->user();
         if($admin_data->status!='1'){
            return back()->withError('Login details are not valid!');
         }
         Session::put([
            'admin_id' => $admin_data->id,
            'admin_name' => $admin_data->name,
            'admin_email' => $admin_data->email,
            'admin_mobile' => $admin_data->mobile,
            'address' => $admin_data->address,
            'type' => $admin_data->type
         ]);
         return redirect()->intended('/admin');
      }
      return back()->withError('Login details are not valid!');
   }
   public function logout(Request $request){
      Session::flush();
      Auth::guard('admin')->logout();
      return Redirect('admin')->withSuccess('Logout Successfully!');
   }
}
