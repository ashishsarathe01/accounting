<?php

namespace App\Http\Controllers\heading;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountHeading;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use Gate;

class AccountHeadingController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      Gate::authorize('action-module', 2);
      $com_id = Session::get('user_company_id');
      $accountheading = AccountHeading::whereIn('company_id', [$com_id,0])
                                       ->where('delete', '=', '0')
                                       ->get();
      return view('heading/accountHeading')->with('accountheading', $accountheading);
   }
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function create(){
      Gate::authorize('action-module', 72);
      return view('heading/addAccountHeading');
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      Gate::authorize('action-module', 72);
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
         'bs_profile' => 'required|string',
         'name_sch_three' => 'required|string',
         'bs_profile_three' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
         'bs_profile.required' => 'B/S Profile is required.',
         'name_sch_three.required' => 'Name As Sch is required.',
         'bs_profile_three.required' => 'B/S Profile is required.',
      ]);
      if($validator->fails()) {
         return redirect()->route('account-heading.create')
            ->withErrors($validator)
            ->withInput();
      }
      $com_id = Session::get('user_company_id');
      $check = AccountHeading::select('id')
                        ->where('name',$request->input('name'))
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [$com_id,0])
                        ->get();
      if(count($check)>0){
         return $this->failedMessage('Heading Already Created.');
         exit();
      }
      $account = new AccountHeading;
      $account->company_id =  Session::get('user_company_id');
      $account->name = $request->input('name');
      $account->bs_profile = $request->input('bs_profile');
      $account->name_sch_three = $request->input('name_sch_three');
      $account->bs_profile_three = $request->input('bs_profile_three');
      $account->status = $request->input('status');
      $account->save();
      if($account->id) {
         return redirect('account-heading')->withSuccess('Account Heading Created successfully!');
      }else{
         return $this->failedMessage('Something went wrong, please try again after some time.');
      }
   }
   public function edit($id){
      Gate::authorize('action-module', 37);
      $editheading = AccountHeading::find($id);
      return view('heading/editAccountHeading')->with('editheading', $editheading);
   }
   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountHeading  $fooditem
     * @return \Illuminate\Http\Response
   */
   public function update(Request $request){
      Gate::authorize('action-module', 37);
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
         'bs_profile' => 'required|string',
         'name_sch_three' => 'required|string',
         'bs_profile_three' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
         'bs_profile.required' => 'B/S Profile is required.',
         'name_sch_three.required' => 'Name As Sch is required.',
         'bs_profile_three.required' => 'B/S Profile is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $account =  AccountHeading::find($request->heading_id);
      $account->name = $request->input('name');
      $account->bs_profile = $request->input('bs_profile');
      $account->name_sch_three = $request->input('name_sch_three');
      $account->bs_profile_three = $request->input('bs_profile_three');
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->update();
      return redirect('account-heading')->withSuccess('Account Heading updated successfully!');
   }
   /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
   */
   public function delete(Request $request){
      Gate::authorize('action-module', 38);
      $account =  AccountHeading::find($request->heading_id);
      $account->delete = '1';
      $account->deleted_at = Carbon::now();
      $account->update();
      if($account) {
         return redirect('account-heading')->withSuccess('Account Heading deleted successfully!');
      }
   }
   /**
     * Generates failed response and message.
   */
   public function failedMessage($msg){
      return redirect('account-heading')->withError($msg);
   }
}
