<?php

namespace App\Http\Controllers\group;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountGroups;
use App\Models\Accounts;
use App\Models\AccountHeading;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use Gate;

class AccountGroupsController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
 public function index(){
      Gate::authorize('action-module',3);
      $com_id = Session::get('user_company_id');
      $accountgroup = AccountGroups::whereIn('company_id', [$com_id,0])
                                    ->where('delete', '=', '0')
                                    ->get();
    $accountheading = AccountHeading::whereIn('company_id', [$com_id,0])
                                       ->where('delete', '=', '0')
                                       ->get();
                                       
                                       $accountgroup = $accountgroup->concat($accountheading);
                                       
      return view('group/accountGroup')->with('accountgroup', $accountgroup)->with('accountheading', $accountheading);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',71);
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
      Gate::authorize('action-module',71);
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
      Gate::authorize('action-module',39);
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
      Gate::authorize('action-module',39);
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
  public function delete(Request $request)
{
    Gate::authorize('action-module', 40);

    $companyId = Session::get('user_company_id');
    $groupId   = $request->group_id;

    // 🔍 Check if any account exists under this group
    $accountExists = Accounts::where('company_id', $companyId)
        ->where('under_group', $groupId)
        ->exists();

    if ($accountExists) {
           return redirect('account-group')
    ->with('error', 'Account group cannot be deleted as accounts under this group exist!');

    }

    // 🔍 Fetch account group safely
    $accountGroup = AccountGroups::where('company_id', $companyId)
        ->where('id', $groupId)
        ->first();

    if (!$accountGroup) {
        return redirect('account-group')
            ->with('error','Account group not found!');
    }

    // 🗑 Soft delete logic
    $accountGroup->update([
        'delete'     => '1',
        'deleted_at'=> now(),
    ]);

    return redirect('account-group')
        ->withSuccess('Account group deleted successfully!');
}

   
   
   public function accountGroupImportView(Request $request){
      return view('group/account_group_import')->with('upload_log',0);
   }
   public function accountGroupImportProcess(Request $request) { 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $financial_year = Session::get('default_fy');      
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_invoice_count = $total_row - 1;
         $success_row = 0;
         $index = 1;
         $error_arr = [];$all_error_arr = [];
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $name = $data[0];
            $primary = $data[1];
            $under_group = $data[2];
            if($name=="" && $primary==""){
               $failed_invoice_count++;
               $index++;
               continue;
            }
            if($name=="" || $primary==""){
               array_push($error_arr, 'Name and Primary is required - Row No. '.$index);
               array_push($all_error_arr,array("error_title"=>"Required Field","mesaage"=>'Name and Primary is required - Row No. '.$index));
            }
            if($primary=="N"){
               if($under_group==""){
                  array_push($error_arr, 'Under Group is required - Row No. '.$index);
                  array_push($all_error_arr,array("error_title"=>"Required Field","mesaage"=>'Under Group is required - Row No. '.$index));
               }
               $head_check = AccountHeading::where('name',$under_group)
                        ->whereIn('company_id',[Session::get('user_company_id'),0])
                        ->where('delete','0')
                        ->where('status','1')
                        ->first();
               $group_check = AccountGroups::where('name',$under_group)
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->where('delete','0')
               ->where('status','1')
               ->first();
               if(!$head_check && !$group_check){
                  array_push($error_arr, 'Under Group '.$under_group.' not found - Row No. '.$index);
                  array_push($all_error_arr,array("error_title"=>"Group Not Found","mesaage"=>'Under Group '.$under_group.' not found - Row No. '.$index));
               }
               if($head_check){
                  $heading = $head_check->id;
                  $heading_type = "head";
               }
               if($group_check){
                  $heading = $group_check->id;
                  $heading_type = "group";
               }
            }
            $check = AccountGroups::select('id')
                        ->where('name',$name)
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [Session::get('user_company_id'),0])
                        ->get();
            if(count($check)>0){
               array_push($error_arr, 'Group Name '.$name.' already exists - Row No. '.$index);
               array_push($all_error_arr,array("error_title"=>"Group_Already_Exists","mesaage"=>'Group Name '.$name.' already exists - Row No. '.$index));
            }
            if(count($error_arr)>0){
               //array_push($all_error_arr,$error_arr);
               $error_arr = [];
               $index++;
               $failed_invoice_count++;
               continue;
            }
            
            if($primary=="Y"){
               $primary = "Yes";
            }
            if($primary=="N"){
               $primary = "No";
            }
            $account = new AccountGroups;
            $account->name = $name;
            $account->company_id =  Session::get('user_company_id');
            $account->primary = $primary;
            if($primary=="No"){
               $account->heading = $heading;
               $account->heading_type = $heading_type; 
            }                  
            $account->status = '1';
            $account->save();
            $success_invoice_count++;
            $index++;
         } 
         fclose($handle);
      }      
      $return = array();
      foreach($all_error_arr as $val) {
         $return[$val['error_title']][] = $val;
      }
      // echo "<pre>";
      // print_r($return);die;
      return view('group/account_group_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$return);
   }
   public function exportAccountGroups()
{
    $company_id = Session::get('user_company_id');

    $groups = AccountGroups::where('delete','0')
        ->where('status','1')
        ->where('company_id',$company_id)
        ->get();

    $filename = "account_groups_export.csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $file = fopen('php://output', 'w');

    fputcsv($file, ['Name','Primary','Under Group']);

    $groupNames = AccountGroups::whereIn('company_id',[$company_id,0])
                    ->pluck('name','id');

    foreach($groups as $group){

        $primary = ($group->primary == 'Yes') ? 'Y' : 'N';

        $under_group = '';

        if($group->heading && $group->heading_type == 'group'){
            $under_group = $groupNames[$group->heading] ?? '';
        }

        fputcsv($file, [
            $group->name,
            $primary,
            $under_group
        ]);
    }

    fclose($file);
    exit;
}
   
   /**
     * Generates failed response and message.
   */
   public function failedMessage($msg){
      return redirect('account-group')->withError($msg);
   }
}
