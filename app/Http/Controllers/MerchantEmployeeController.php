<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MerchantEmployee;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PrivilegesModule;
use App\Models\PrivilegesModuleMapping;
use App\Models\Companies;
use Validation;
use Session;
use Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
class MerchantEmployeeController extends Controller{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      Gate::authorize('view-module', 20);
      $employee = User::where('delete_status','0')->where('type','EMPLOYEE')->where('company_id',Session::get('user_company_id'))->get();
      return view('merchant_employee')->with('employee', $employee);
   }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
   */
   public function create(){
      Gate::authorize('action-module', 81);
      return view('merchant_employee_add');
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      Gate::authorize('action-module', 81);
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
      Gate::authorize('action-module', 34);
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
      Gate::authorize('action-module', 34);
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
      Gate::authorize('action-module', 35);
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
   public function employeePrivileges($id){
      
      Gate::authorize('action-module', 36);
      
      $assign_privilege = PrivilegesModuleMapping::where('employee_id',$id)->pluck('module_id')->toArray();
      $privileges = PrivilegesModule::select('id','module_name','parent_id')
                                       ->where('status',1)
                                       ->get()
                                       ->toArray();
      $tree = $this->buildTree($privileges);
      if(Session::get('user_type')=="OWNER"){
         $user_id = Session::get('user_id');
      }else if(Session::get('user_type')=="EMPLOYEE"){
         $user = Companies::select('user_id')->where('id', Session::get('user_company_id'))->first();
         $user_id = $user->user_id;
      }
      $company = Companies::select('id','company_name')
                           ->where('user_id', $user_id)
                           ->where('status','1')
                           ->where('delete','0')
                           ->get();
      
      return view('merchant_employee_privileges',["privileges"=>$tree,"employee_id"=>$id,"company"=>$company]);
   }
   function buildTree(array $elements, $parentId = null) {
      $branch = [];
      foreach ($elements as $element) {
         if ($element['parent_id'] == $parentId) {
               $children = $this->buildTree($elements, $element['id']);
               if ($children) {
                  $element['children'] = $children;
               }
               $branch[] = $element;
         }
      }
      return $branch;
   }
   public function setEmployeePrivileges(Request $request){
      Gate::authorize('action-module', 36);
      if($request->apply_all){
         $company = Companies::select('id')->where('user_id', Auth::id())->where('status','1')->where('delete','0')->get();
         foreach ($company as $k => $v) {
            PrivilegesModuleMapping::where('employee_id',$request->employee_id)->where('company_id',$v->id)->delete();
            if($request->privileges && count($request->privileges)>0){
            foreach ($request->privileges as $key => $value) {
               $pri = new PrivilegesModuleMapping;
               $pri->module_id = $value;
               $pri->employee_id = $request->employee_id;
               $pri->company_id = $v->id;
               $pri->created_at = Carbon::now();
               $pri->save();
            }
         }
         }         
      }else{
         PrivilegesModuleMapping::where('employee_id',$request->employee_id)->where('company_id',$request->company_id)->delete();
         if($request->privileges && count($request->privileges)>0){
            foreach ($request->privileges as $key => $value) {
               $pri = new PrivilegesModuleMapping;
               $pri->module_id = $value;
               $pri->employee_id = $request->employee_id;
               $pri->company_id = $request->company_id;
               $pri->created_at = Carbon::now();
               $pri->save();
            }
         }
         
      }
      
      return redirect('merchant-employee-privileges/'.$request->employee_id)->withSuccess('Privileges Updated Successfully.');
      //PrivilegesModuleMapping
   }
      
}
