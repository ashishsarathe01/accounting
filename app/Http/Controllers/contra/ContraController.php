<?php

namespace App\Http\Controllers\contra;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Contra;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use App\Models\ContraDetails;
use App\Models\AccountLedger;
use App\Models\Companies;
use App\Models\GstBranch;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
class ContraController extends Controller
{
     /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
        $input = $request->all();
      // Default date range (first day of current month to today)
$from_date = session('contra_from_date', "01-" . date('m-Y'));
$to_date = session('contra_to_date', date('d-m-Y'));

// Check if user has selected a date range
if (!empty($input['from_date']) && !empty($input['to_date'])) {
    $from_date = date('d-m-Y', strtotime($input['from_date']));
    $to_date = date('d-m-Y', strtotime($input['to_date']));
    
    // Store in session so it persists after refresh
    session(['contra_from_date' => $from_date, 'contra_to_date' => $to_date]);
}
      Session::put('redirect_url','');
      $financial_year = Session::get('default_fy');      
      $y =  explode("-",$financial_year);
      $from = $y[0];
      $from = DateTime::createFromFormat('y', $from);
      $from = $from->format('Y');
      $to = $y[1];
      $to = DateTime::createFromFormat('y', $to);
      $to = $to->format('Y');
      $month_arr = array($from.'-04',$from.'-05',$from.'-06',$from.'-07',$from.'-08',$from.'-09',$from.'-10',$from.'-11',$from.'-12',$to.'-01',$to.'-02',$to.'-03');
      $com_id = Session::get('user_company_id');
      $contra = DB::table('contra_details')
            ->select('contras.series_no','contras.id as con_id', 'contras.date', 'accounts.account_name as acc_name', 'contra_details.*','contras.mode as m','contras.voucher_no')
            ->join('contras', 'contra_details.contra_id', '=', 'contras.id')
            ->join('accounts', 'contra_details.account_name', '=', 'accounts.id')
            ->where('contra_details.company_id', $com_id)
            ->where('contras.delete','0')
            ->whereRaw("STR_TO_DATE(contras.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(contras.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->orderBy('contras.date', 'asc')
         ->orderBy('contras.voucher_no','asc')
            ->get();
      return view('contra/contra')->with('contra', $contra)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
   }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      $financial_year = Session::get('default_fy');
      $com_id = Session::get('user_company_id');
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])                   ->where('delete', '=', '0')
                           ->whereIn('under_group', [7,8])
                           ->where('under_group_type', '=', 'group')
                           ->orderBy('account_name')
                           ->get();
      $bill_date = Contra::where('company_id',Session::get('user_company_id'))
                           ->where('delete','0')
                           ->where('financial_year',$financial_year)
                           ->max('date');
      if(!$bill_date){
         $bill_date = date('Y-m-d');
         if(date('m')<=3){
            $current_year = (date('y')-1) . '-' . date('y');
         }else{
            $current_year = date('y') . '-' . (date('y') + 1);
         }
         if($financial_year!=$current_year){
            $y =  explode("-",$financial_year);
            $bill_date = $y[1]."-03-31";
            $bill_date = date('Y-m-d',strtotime($bill_date));
         }
      }
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      return view('contra/addContra')->with('party_list', $party_list)->with('date', $bill_date)->with('mat_series', $mat_series);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      $financial_year = Session::get('default_fy');
      $con = new Contra;
      $con->date = $request->input('date');
      $con->company_id = Session::get('user_company_id');
      $con->voucher_no = $request->input('voucher_no');
      $con->mode = $request->input('mode');
      $con->cheque_no = $request->input('cheque_no');
      $con->series_no = $request->input('series_no');
      $con->long_narration = $request->input('long_narration');
      $con->financial_year = $financial_year;
      $con->save();
      if($con->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');
         
         $narrations = $request->input('narration');
         $i=0;
         foreach($types as $key => $type){
            $contype = new ContraDetails;
            $contype->contra_id = $con->id;
            $contype->company_id = Session::get('user_company_id');
            $contype->type = $type;
            $contype->account_name = $account_names[$key];
            $contype->debit = isset($debits[$key]) ? $debits[$key] :'0';
            $contype->credit = isset($credits[$key]) ? $credits[$key] :'0';
           
            $contype->narration = $narrations[$key];
            $contype->status = '1';
            $contype->save();
            //ADD DATA IN Customer ACCOUNT
            if($i==0){
                  $map_account_id = $account_names['1'];
               }else{
                  $map_account_id = $account_names['0'];
               }
            $ledger = new AccountLedger();
            $ledger->account_id = $account_names[$key];
            if(isset($debits[$key]) && !empty($debits[$key])){
               $ledger->debit = $debits[$key];
            }else{
               $ledger->credit = $credits[$key];
            }            
            $ledger->txn_date = $request->input('date');
            $ledger->series_no = $request->input('series_no');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 8;
            $ledger->entry_type_id = $con->id;
            $ledger->entry_type_detail_id = $contype->id;
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
         session(['previous_url_contra' => URL::previous()]);
         return redirect('contra')->withSuccess('Contra added successfully!');
      }else{
         $this->failedMessage();
      }        
   }
   public function edit($id){
      $com_id = Session::get('user_company_id');
      $contra = Contra::find($id);
      $contra_detail = ContraDetails::where('contra_id', '=', $id)->where('delete', '=', '0')->get();
      $party_list = Accounts::whereIn('company_id', [Session::get('user_company_id'),0])                   ->where('delete', '=', '0')
                           ->where('under_group_type', '=', 'group')
                           ->whereIn('under_group', [7,8])
                           ->orderBy('account_name')
                           ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();$mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      // $mat_series = array();
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      return view('contra/editContra')->with('contra', $contra)->with('party_list', $party_list)->with('contra_detail', $contra_detail)->with('mat_series', $mat_series);
   }
   public function delete(Request $request){
      $contra =  Contra::find($request->contra_id);
      $contra->delete = '1';
      $contra->deleted_at = Carbon::now();
      $contra->deleted_by = Session::get('user_id');
      $contra->update();
      if($contra){
         ContraDetails::where('contra_id',$request->contra_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',8)
                        ->where('entry_type_id',$request->contra_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         return redirect('contra')->withSuccess('Contra deleted successfully!');
      }
   }
   public function update(Request $request){
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',

      ], [
         'date.required' => 'Date is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $contra =  Contra::find($request->contra_id);
      $contra->date = $request->input('date');
      $contra->voucher_no = $request->input('voucher_no');
      $contra->mode = $request->input('mode');
      $contra->series_no = $request->input('series_no');
      $contra->cheque_no = $request->input('cheque_no');
      $contra->long_narration = $request->input('long_narration');
      $contra->save();
      $contra_detail = ContraDetails::where('contra_id', '=', $request->contra_id)->delete();
      AccountLedger::where('entry_type',8)
                     ->where('entry_type_id',$request->contra_id)
                     ->delete();
      $types = $request->input('type');
      $account_names = $request->input('account_name');
      $debits = $request->input('debit');
      $credits = $request->input('credit');
      $narrations = $request->input('narration');
      $i=0;
      foreach($types as $key => $type) {
         $paytype = new ContraDetails;
         $paytype->contra_id = $request->contra_id;
         $paytype->company_id = Session::get('user_company_id');
         $paytype->type = $type;
         $paytype->account_name = $account_names[$key];
         $paytype->debit = isset($debits[$key]) ? $debits[$key] : 0;
         $paytype->credit = isset($credits[$key]) ? $credits[$key] : 0;         
         $paytype->narration = $narrations[$key];
         $paytype->status = '1';
         $paytype->save();
         //ADD DATA IN Customer ACCOUNT
         if($i==0){
            $map_account_id = $account_names['1'];
         }else{
            $map_account_id = $account_names['0'];
         }
         $ledger = new AccountLedger();
         $ledger->account_id = $account_names[$key];
         if(isset($debits[$key]) && !empty($debits[$key])){
            $ledger->debit = $debits[$key];
         }else{
            $ledger->credit = $credits[$key];
         }            
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 8;
         $ledger->entry_type_id = $contra->id;
         $ledger->entry_type_detail_id = $paytype->id;
         $ledger->map_account_id = $map_account_id;
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         $i++;
      }
      if(!empty(Session::get('redirect_url'))){
         return redirect(Session::get('redirect_url'));
      }else{
         return redirect('contra')->withSuccess('Contra detail updated successfully!');
      }       
   }
   public function contraImportView(Request $request){      
      return view('contra/import_contra_view');
   }
   public function contraImportProcess(Request $request) {       
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;
      $financial_year = Session::get('default_fy');
      $fy = explode('-',$financial_year);
      $from_date = $fy[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $fy[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      $company_data = Companies::where('id', Session::get('user_company_id'))->first(); 
      $already_exists_error_arr = [];
      $already_exists_item_arr = [];
      $error_arr = [];
      $data_arr = [];
      $all_error_arr = [];
      $mode_arr = ['NEFT','RGTS','IMPS','CHEQUE','CASH'];
      if($duplicate_voucher_status==0){
         $file = $request->file('csv_file');  
         $filePath = $file->getRealPath();      
         $final_result = array();
         if(($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 10000, ",");
            $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
            $index = 1;
            $series_no = "";
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
               if($data[0]!="" && $data[1]!="" && $data[2]!=""){                  
                  $series = $data[1];
                  $bill_no = $data[2];
                  $receipt = Contra::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($receipt){
                     array_push($already_exists_error_arr, 'Contra on bill no. - '.$bill_no.' already exists');
                  }
               }
            }
         }
         if(count($already_exists_error_arr)>0){
            $res = array(
               'status' => false,
               'data' => $already_exists_error_arr,
               "message"=>"Already Exists."
            );
            return json_encode($res);
         }
      }
      if($company_data->gst_config_type == "single_gst"){
         $gst_data = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$gst_data[0]->id])
                           ->get();
         if(count($branch)>0){
            $gst_data = $gst_data->merge($branch);
         }         
      }else if($company_data->gst_config_type == "multiple_gst"){
         $gst_data = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($gst_data as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $gst_data = $gst_data->merge($branch);
            }
         }         
      }
      foreach ($gst_data as $key => $value) {
         $series_arr[] = $value->series;
         $material_center_arr[] = $value->mat_center;
         $gst_no_arr[] = $value->gst_no;
      }
      $bill_date = "";
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_row = $total_row - 1;
         $success_row = 0;
         $index = 1;
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if($data[0]=="" && $data[1]=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;                  
            }
            if($data[0]!="" && $data[1]!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $txn_arr = [];
               $error_arr = [];
               $bill_date = $data[0];
               $series = $data[1];
               $bill_no = $data[2];
               $mode = $data[3];
               if($mode!=""){
                  if(!in_array($mode,$mode_arr)){
                     array_push($error_arr, "Mode should be ['NEFT','IMPS','RTGS','CASH','CHEQUE'] - Row ".$index);
                  }
               }
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($bill_date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($bill_date)))){                  
                  array_push($error_arr, 'Date '.$bill_date.' Not In Financial Year - Row '.$index);                  
               }
               if(!in_array($series, $series_arr)){
                  array_push($error_arr, 'Series No. '.$series.' Not Found - Row '.$index); 
               }
               if($duplicate_voucher_status!=2 && !empty($bill_no)){
                  $check_receipt = Contra::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('financial_year',$financial_year)
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($check_receipt){
                     array_push($error_arr, 'Contra on bill no. - '.$bill_no.' already exists');
                  }
               }
            }
            $account = $data[4];
            $check_account = Accounts::where('account_name',trim($account))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_account){
               array_push($error_arr, 'Account Name '.$account.' Not Found - Row '.$index);
            }
            $debit = $data[5];
            $debit = str_replace(",","",$debit);
            $credit = $data[6];
            $credit = str_replace(",","",$credit);
            if($debit=="" && $credit==""){
               array_push($error_arr, 'Debit/Credit Cannot - Row '.$index);
            }
            if($check_account){
               array_push($txn_arr,array("account"=>$check_account->id,"debit"=>$debit,"credit"=>$credit));
            }else{
               array_push($txn_arr,array("account"=>$account,"debit"=>$debit,"credit"=>$credit));
            }
            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
            }   
            $index++;
         } 
         fclose($handle);
         $total_invoice_count = count($data_arr);
         // echo "<pre>";
         // print_r($data_arr);
         // die;
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         if(count($data_arr)>0){
            foreach ($data_arr as $key => $value) {
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }               
               $bill_date = $value['bill_date'];
               $series = $value['series'];
               $bill_no = $value['bill_no'];
               $mode = $value['mode'];
               $txn_arr = $value['txn_arr'];
               if($mode=="CHEQUE"){
                  $success_invoice_count++;
                  continue;
               }
               if($duplicate_voucher_status==2){
                  $check_rec = Contra::select('id')
                                             ->where('voucher_no',$bill_no)
                                             ->where('series_no',trim($series))
                                             ->where('financial_year',$financial_year)
                                             ->where('company_id',trim(Session::get('user_company_id')))
                                             ->first();
                  if($check_rec){              
                     $updated_payment = Contra::find($check_rec->id);
                     $updated_payment->delete = '1';
                     $updated_payment->deleted_at = Carbon::now();
                     $updated_payment->deleted_by = Session::get('user_id');
                     $updated_payment->update();
                     if($updated_payment){
                        ContraDetails::where('contra_id',$check_rec->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',8)
                        ->where('entry_type_id',$check_rec->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                     }
                  }                  
               }
               if($mode=="IMPS" || $mode=="NEFT" || $mode=="RTGS"){
                  $mode = 0;
               }else if($mode=="CASH"){
                  $mode = 1;
               }else{
                  $mode = 0;
               }
               $receipt = new Contra;
               $receipt->date = date('Y-m-d',strtotime($bill_date));
               $receipt->voucher_no = $bill_no;
               $receipt->mode = $mode;
               $receipt->series_no = $series;  
               $receipt->company_id = Session::get('user_company_id');
               $receipt->financial_year = $financial_year;
               $i = 0;
              
               if($receipt->save()){
                  foreach($txn_arr as $key => $data){
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $type = "Debit";
                     }else{
                        $type = "Credit";
                     }
                     $paytype = new ContraDetails;
                     $paytype->contra_id = $receipt->id;
                     $paytype->company_id = Session::get('user_company_id');;
                     $paytype->type = $type;
                     $paytype->account_name = $data['account'];
                     $paytype->debit = $data['debit'];
                     $paytype->credit = $data['credit'];
                     $paytype->status = '1';
                     $paytype->save();
                     //ADD DATA IN Customer ACCOUNT
                     if($i==0){
                        $map_account_id = $txn_arr[1]['account'];
                     }else{
                        $map_account_id = $txn_arr[0]['account'];
                     }                    
                     $ledger = new AccountLedger();
                     if($data['debit'] && $data['debit']!="" && $data['debit']!="0"){
                        $ledger->debit = $data['debit'];
                     }else{
                        $ledger->credit = $data['credit'];
                     }
                     $ledger->account_id = $data['account'];
                     $ledger->series_no = $series;
                     $ledger->txn_date = date('Y-m-d',strtotime($bill_date));
                     $ledger->company_id = Session::get('user_company_id');
                     $ledger->financial_year = Session::get('default_fy');
                     $ledger->entry_type = 8;
                     $ledger->entry_type_id = $receipt->id;
                     $ledger->entry_type_detail_id = $paytype->id;
                     $ledger->map_account_id = $map_account_id;
                     $ledger->created_by = Session::get('user_id');
                     $ledger->created_at = date('d-m-Y H:i:s');
                     $ledger->save();
                     $i++;
                  }
                  $success_invoice_count++;
               }         
            }
         }
      }
      $res = array("total_count"=>$total_invoice_count,"success_count"=>$success_invoice_count,"failed_count"=>$failed_invoice_count,"error_message"=>$all_error_arr);
      $res = array(
         'status' => true,
         'data' => $res,
         "message"=>"Uploaded Successfully."
      );
      return json_encode($res);
      
   }
}
