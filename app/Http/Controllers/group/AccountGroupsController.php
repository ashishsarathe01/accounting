<?php

namespace App\Http\Controllers\group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountGroups;
use App\Models\AccountHeading;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;

class AccountGroupsController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      $com_id = Session::get('user_company_id');
      $accountgroup = AccountGroups::whereIn('company_id', [$com_id,0])
                                    ->where('delete', '=', '0')
                                    ->get();
      return view('group/accountGroup')->with('accountgroup', $accountgroup);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      $com_id = Session::get('user_company_id');
      $heading = AccountHeading::whereIn('company_id', [$com_id,0])
               ->where('delete', '=', '0')
               ->orderBy('name')
               ->get();
      $accountgroup = AccountGroups::where('delete', '=', '0')
                                    ->whereIn('company_id', [$com_id,0])
                                    ->get();
      return view('group/addAccountGroups')->with('heading', $heading)->with('accountgroups', $accountgroup);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $com_id = Session::get('user_company_id');
      $check = AccountGroups::select('id')
                        ->where('name',$request->input('name'))
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [$com_id,0])
                        ->get();
      if(count($check)>0){
         return $this->failedMessage('Group Already Created.');
         exit();
      }
      $account = new AccountGroups;
      $account->name = $request->input('name');
      $account->company_id =  Session::get('user_company_id');
      $account->primary = $request->input('primary');
      $account->heading = $request->input('heading');
      $account->heading_type = $request->input('heading_type');
      $account->heading_as_sch_type = $request->input('heading_as_sch_type');
      $account->bs_profile = $request->input('bs_profile');
      $account->name_as_sch = $request->input('name_as_sch');
      $account->primary_as_sch = $request->input('primary_as_sch');
      $account->heading_as_sch = $request->input('heading_as_sch');
      $account->bs_profile_as_sch = $request->input('bs_profile_as_sch');
      $account->status = $request->input('status');
      $account->save();
      if($account->id){
         return redirect('account-group')->withSuccess('Account group Created successfully!');
      }else{
         return $this->failedMessage('Something went wrong, please try again after some time.');
      }
   }

   public function edit($id){
      $com_id = Session::get('user_company_id');
      $editGroup = AccountGroups::find($id);
      $heading = AccountHeading::where('delete', '=', '0')
                                 ->whereIn('company_id', [$com_id,0])
                                 ->get();
      $accountgroup = AccountGroups::where('delete', '=', '0')
                                    ->whereIn('company_id', [$com_id,0])
                                    ->get();
      return view('group/editAccountGroups')->with('editGroup', $editGroup)->with('heading', $heading)->with('accountgroup', $accountgroup);
   }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $account =  AccountGroups::find($request->group_id);
      $account->name = $request->input('name');
      $account->primary = $request->input('primary');
      $account->heading = $request->input('heading');
      $account->heading_type = $request->input('heading_type');
      $account->heading_as_sch_type = $request->input('heading_as_sch_type');
      $account->bs_profile = $request->input('bs_profile');
      $account->name_as_sch = $request->input('name_as_sch');
      $account->primary_as_sch = $request->input('primary_as_sch');
      $account->heading_as_sch = $request->input('heading_as_sch');
      $account->bs_profile_as_sch = $request->input('bs_profile_as_sch');
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->update();
      return redirect('account-group')->withSuccess('Account group updated successfully!');
   }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
   public function delete(Request $request){
      $account =  AccountGroups::find($request->group_id);
      $account->delete = '1';
      $account->deleted_at = Carbon::now();
      $account->update();
      if($account) {
         return redirect('account-heading')->withSuccess('Account group deleted successfully!');
      }
   }
   /**
     * Generates failed response and message.
   */
   public function failedMessage($msg){
      return redirect('account-group')->withError($msg);
   }
}
