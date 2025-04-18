<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MerchantEmployee;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validation;
use Session;
use Hash;
class MerchantEmployeeController extends Controller{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      $employee = User::where('delete_status','0')->where('type','EMPLOYEE')->where('company_id',Session::get('user_company_id'))->get();
      return view('merchant_employee')->with('employee', $employee);
   }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
   */
   public function create(){
      return view('merchant_employee_add');
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      $check = User::where('email',$request->input('email'))->first();
      if($check){
         return redirect('manage-merchant-employee')->withSuccess('Email already exists.');
      }
      $user = User::create([
         'name' => $request->input('name'),
         'email' => $request->input('email'),
         'mobile_no' => $request->input('mobile'),
         'password' => \Hash::make($request->input('mobile')),
         'address' => $request->input('address'),
         'type' => 'EMPLOYEE',
         'company_id' => Session::get('user_company_id'),
         'status' => $request->input('status'),
      ]);
      $token = $user->createToken('Token')->accessToken;
      if(!$token){
        return redirect('manage-merchant-employee')->withSuccess('User add Successfully.');
      }else{
         return redirect('manage-merchant-employee')->withSuccess('User add Successfully.');
      }
   }

   /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
   */
   public function show($id){
        //
   }

   /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
   */
   public function edit($id){
      $employee = User::find($id);
      return view('merchant_employee_edit',['employee'=>$employee]);
   }

   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
   */
   public function update(Request $request, $id){
      $employee = User::find($id);
      $employee->name = $request->input('name');
      $employee->mobile_no = $request->input('mobile');
      $employee->email = $request->input('email');
      $employee->address = $request->input('address');
      $employee->status = $request->input('status');
      $employee->updated_by = Session::get('user_id');
      if($employee->save()){
         return redirect('manage-merchant-employee')->withSuccess('User updated Successfully.');
      }else{
         return $this->failedMessage('Something went wrong.','manage-merchant-employee/create');
      }
   }

   /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
   */
   public function destroy($id){
      $delete = User::where('id',$id)->update(['status'=>'0','delete_status'=>'1','deleted_at'=>date('Y-m-d H:i:s'),"deleted_by"=>Session::get('user_id')]);
      if($delete){
         return redirect('manage-merchant-employee')->withSuccess('User deleted Successfully.');
      }else{
         return $this->failedMessage('Something went wrong.','manage-merchant-employee');
      }
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
}
