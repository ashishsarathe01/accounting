<?php

namespace App\Http\Controllers\contra;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Contra;
use Illuminate\Support\Facades\Validator;
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
      $from_date = "01-".date('m-Y');
      $to_date = date('d-m-Y');
      if(!empty($input['from_date']) && !empty($input['to_date'])){
         $from_date = date('d-m-Y',strtotime($input['from_date']));
         $to_date = date('d-m-Y',strtotime($input['to_date']));
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
            ->select('contras.series_no','contras.id as con_id', 'contras.date', 'accounts.account_name as acc_name', 'contra_details.*','contras.mode as m')
            ->join('contras', 'contra_details.contra_id', '=', 'contras.id')
            ->join('accounts', 'contra_details.account_name', '=', 'accounts.id')
            ->where('contra_details.company_id', $com_id)
            ->where('contras.delete','0')
            ->whereRaw("STR_TO_DATE(contras.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(contras.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->orderBy('contras.date', 'asc')
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)) {
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
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
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 8;
            $ledger->entry_type_id = $con->id;
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
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
                           ->whereIn('under_group', [7,8])
                           ->orderBy('account_name')
                           ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)) {
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
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
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 8;
         $ledger->entry_type_id = $contra->id;
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
}
