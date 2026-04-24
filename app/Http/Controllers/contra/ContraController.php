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
use App\Models\ActivityLog;
use App\Models\VoucherSeriesConfiguration;
use DB;
use Carbon\Carbon;
use Session;
use DateTime;
use Gate;
use App\Helpers\CommonHelper;
class ContraController extends Controller
{
     /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    Gate::authorize('action-module', 29);

    $input = $request->all();
    $from_date = null;
    $to_date = null;

    // If user has selected a date range
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['contra_from_date' => $from_date, 'contra_to_date' => $to_date]);
    } elseif (session()->has('contra_from_date') && session()->has('contra_to_date')) {
        $from_date = session('contra_from_date');
        $to_date = session('contra_to_date');
    }

    Session::put('redirect_url', '');

    // Prepare month array from financial year
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07',
        $from . '-08', $from . '-09', $from . '-10', $from . '-11',
        $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];

    $com_id = Session::get('user_company_id');

    // Build base query
    $query = DB::table('contra_details')
        ->select(
            'contras.series_no',
            'contras.id as con_id',
            'contras.date',
            'accounts.account_name as acc_name',
            'contra_details.*',
            'contras.mode as m',
            'contras.voucher_no',
            'contras.created_by',
            'contras.approved_by',
            'contras.approved_at',
            'contras.approved_status',

            'created_user.name as created_by_name',
            'approved_user.name as approved_by_name'
        )
        ->join('contras', 'contra_details.contra_id', '=', 'contras.id')
        ->join('accounts', 'contra_details.account_name', '=', 'accounts.id')
        ->leftJoin('users as created_user', 'created_user.id', '=', 'contras.created_by')
        ->leftJoin('users as approved_user', 'approved_user.id', '=', 'contras.approved_by')
        ->where('contra_details.company_id', $com_id)
        ->where('contras.delete', '0');

    // If filtered by date
    if ($from_date && $to_date) {
        $query->whereRaw("
            STR_TO_DATE(contras.date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "','%Y-%m-%d')
            AND STR_TO_DATE(contras.date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "','%Y-%m-%d')
        ");
        $query->orderBy('contras.date', 'asc')
              ->orderBy('contras.voucher_no', 'asc');
    } else {
        // No filter - show last 10 contra entries
        $query->orderBy(DB::raw("CAST(contras.voucher_no AS SIGNED)"), 'desc')
              ->orderBy('contras.date', 'desc')
              ->limit(10);
    }

    $contra = $query->get()->reverse()->values();

    return view('contra/contra')
        ->with('contra', $contra)
        ->with('month_arr', $month_arr)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',75);
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
      foreach ($mat_series as $key => $value){

         $series_configuration = VoucherSeriesConfiguration::where('company_id',$com_id)
            ->where('series',$value->series)
            ->where('configuration_for','CONTRA') 
            ->where('status','1')
            ->first();

         $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
         $lastNumber = DB::table('contras')
            ->where('company_id',$com_id)
            ->where('financial_year',$financial_year)
            ->where('series_no',$value->series)
            ->where('delete','0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));

         if (!$lastNumber) {
            if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
               $next = (int)$series_configuration->invoice_start;
            } else {
               $next = 1;
            }
         } else {
            $next = ((int)$lastNumber) + 1;
         }

         $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d",$next);

         $invoice_prefix = "";
         $manual_enter_invoice_no = "";

         if($series_configuration){
            $manual_enter_invoice_no = ($series_configuration->manual_numbering == "YES") ? "1" : "0";
         }

         if($series_configuration && $series_configuration->manual_numbering == "NO"){

            if($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value){
               $invoice_prefix .= $series_configuration->prefix_value;
            }

            if($series_configuration->separator_1){
               $invoice_prefix .= $series_configuration->separator_1;
            }

            if($series_configuration->year == "PREFIX TO NUMBER"){

               $fy = explode('-',Session::get('default_fy'));
               $year = ($series_configuration->year_format == "YY-YY")
                  ? Session::get('default_fy')
                  : '20'.$fy[0].'-'.$fy[1];

               $invoice_prefix .= $year;

               if($series_configuration->separator_2){
                  $invoice_prefix .= $series_configuration->separator_2;
               }
            }
            $invoice_prefix .= $mat_series[$key]->invoice_start_from;

            if($series_configuration->year == "SUFFIX TO NUMBER"){

               if($series_configuration->separator_2){
                  $invoice_prefix .= $series_configuration->separator_2;
               }

               $fy = explode('-',Session::get('default_fy'));
               $year = ($series_configuration->year_format == "YY-YY")
                  ? Session::get('default_fy')
                  : '20'.$fy[0].'-'.$fy[1];

               $invoice_prefix .= $year;
            }

            if($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3){
               $invoice_prefix .= $series_configuration->separator_3;
            }

            if($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value){
               $invoice_prefix .= $series_configuration->suffix_value;
            }
         }

         $mat_series[$key]->invoice_prefix = $invoice_prefix;
         $mat_series[$key]->manual_enter_invoice_no = $manual_enter_invoice_no;
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
      Gate::authorize('action-module',75);
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
         ->where('series', $request->input('series_no'))
         ->where('configuration_for', 'CONTRA')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } else {
         $last_voucher_no = DB::table('contras')
            ->where('company_id', Session::get('user_company_id'))
            ->where('series_no', $request->input('series_no'))
            ->where('financial_year', $financial_year)
            ->where('delete', '0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));
         if (!$last_voucher_no) {
            if ($series_configuration && $series_configuration->invoice_start != "") {
               $voucher_no = sprintf("%0" . $number_digit . "d", (int)$series_configuration->invoice_start);
            } else {
               $voucher_no = sprintf("%0" . $number_digit . "d", 1);
            }
         } else {
            $voucher_no = sprintf("%0" . $number_digit . "d", ((int)$last_voucher_no + 1));
         }
      }
      $con = new Contra;
      $con->date = $request->input('date');
      $con->company_id = Session::get('user_company_id');
      $con->voucher_no_prefix = $request->input('voucher_prefix');
      $con->voucher_no = $voucher_no;
      $con->mode = $request->input('mode');
      $con->cheque_no = $request->input('cheque_no');
      $con->series_no = $request->input('series_no');
      $con->long_narration = $request->input('long_narration');
      $con->financial_year = $financial_year;
      $con->created_by = Session::get('user_id');
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
            $ledger->financial_year = $financial_year;
            $ledger->entry_type = 8;
            $ledger->entry_type_id = $con->id;
            $ledger->entry_type_detail_id = $contype->id;
            $ledger->entry_narration = $narrations[$key];
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
      Gate::authorize('action-module',45);
      $com_id = Session::get('user_company_id');
      $financial_year = Session::get('default_fy');
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
      foreach ($mat_series as $key => $value) {

         $series_configuration = VoucherSeriesConfiguration::where('company_id', $com_id)
            ->where('series', $value->series)
            ->where('configuration_for', 'CONTRA') 
            ->where('status','1')
            ->first();

         if ($contra->series_no == $value->series) {

            $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
            $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d", (int)$contra->voucher_no);

         } else {

            $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
            $lastNumber = DB::table('contras')
               ->where('company_id',$com_id)
               ->where('financial_year',$financial_year)
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->max(DB::raw("cast(voucher_no as SIGNED)"));

            if (!$lastNumber) {
               if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
                  $next = (int)$series_configuration->invoice_start;
               } else {
                  $next = 1;
               }
            } else {
               $next = ((int)$lastNumber) + 1;
            }

            $mat_series[$key]->invoice_start_from = sprintf("%0" . $number_digit . "d",$next);
         }

         $invoice_prefix = "";
         $manual_enter_invoice_no = "";

         if ($series_configuration) {
            $manual_enter_invoice_no = ($series_configuration->manual_numbering == "YES") ? "1" : "0";
         }

         if ($series_configuration && $series_configuration->manual_numbering == "NO") {

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
               $invoice_prefix .= $series_configuration->prefix_value;
            }

            if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
               $invoice_prefix .= $series_configuration->separator_1;
            }

            if ($series_configuration->year == "PREFIX TO NUMBER") {

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20'.$fy[0].'-'.$fy[1];
               }

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }
            }
            $invoice_prefix .= $mat_series[$key]->invoice_start_from;

            if ($series_configuration->year == "SUFFIX TO NUMBER") {

               if ($series_configuration->separator_2 != "") {
                  $invoice_prefix .= $series_configuration->separator_2;
               }

               if ($series_configuration->year_format == "YY-YY") {
                  $invoice_prefix .= Session::get('default_fy');
               } else {
                  $fy = explode('-', Session::get('default_fy'));
                  $invoice_prefix .= '20'.$fy[0].'-'.$fy[1];
               }
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
               $invoice_prefix .= $series_configuration->separator_3;
            }

            if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
               $invoice_prefix .= $series_configuration->suffix_value;
            }
         }

         $mat_series[$key]->invoice_prefix = $invoice_prefix;
         $mat_series[$key]->manual_enter_invoice_no = $manual_enter_invoice_no;
      }
      return view('contra/editContra')->with('contra', $contra)->with('party_list', $party_list)->with('contra_detail', $contra_detail)->with('mat_series', $mat_series);
   }
   public function delete(Request $request){
      Gate::authorize('action-module',46);
      $contra =  Contra::find($request->contra_id);
      $oldSnapshot = [
         'contra' => $contra->toArray(),
         'details' => ContraDetails::where('contra_id', $contra->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];
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
         ActivityLog::create([
            'module_type' => 'contra',
            'module_id'   => $contra->id,
            'action'      => 'delete',
            'old_data'    => $oldSnapshot,
            'new_data'    => null,
            'action_by'   => Session::get('user_id'),
            'company_id'  => Session::get('user_company_id'),
            'action_at'   => now(),
         ]);
         return redirect('contra')->withSuccess('Contra deleted successfully!');
      }
   }
   public function update(Request $request){
      Gate::authorize('action-module',45);
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',

      ], [
         'date.required' => 'Date is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $contra =  Contra::find($request->contra_id);
      $oldSnapshot = [
         'contra' => $contra->toArray(),
         'details' => ContraDetails::where('contra_id', $contra->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];
      $contra->date = $request->input('date');
      $contra->voucher_no_prefix = $request->input('voucher_prefix');
      $contra->updated_by = Session::get('user_id');
      $series_changed = ($contra->series_no != $request->input('series_no'));
      $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
         ->where('series', $request->input('series_no'))
         ->where('configuration_for', 'CONTRA')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      $voucher_no = $contra->voucher_no;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } elseif ($series_changed) {
         $last_voucher_no = DB::table('contras')
            ->where('company_id', Session::get('user_company_id'))
            ->where('series_no', $request->input('series_no'))
            ->where('financial_year', $financial_year)
            ->where('delete', '0')
            ->max(DB::raw("cast(voucher_no as SIGNED)"));
         if (!$last_voucher_no) {
            if ($series_configuration && $series_configuration->invoice_start != "") {
               $voucher_no = sprintf("%0" . $number_digit . "d", (int)$series_configuration->invoice_start);
            } else {
               $voucher_no = sprintf("%0" . $number_digit . "d", 1);
            }
         } else {
            $voucher_no = sprintf("%0" . $number_digit . "d", ((int)$last_voucher_no + 1));
         }
      }
      $contra->voucher_no = $voucher_no;
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
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 8;
         $ledger->entry_type_id = $contra->id;
         $ledger->entry_type_detail_id = $paytype->id;
         $ledger->map_account_id = $map_account_id;
         $ledger->entry_narration = $narrations[$key];
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         $i++;
      }
      $newSnapshot = [
         'contra' => Contra::find($contra->id)->toArray(),
         'details' => ContraDetails::where('contra_id', $contra->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];

      ActivityLog::create([
         'module_type' => 'contra',
         'module_id'   => $contra->id,
         'action'      => 'edit',
         'old_data'    => $oldSnapshot,
         'new_data'    => $newSnapshot,
         'action_by'   => Session::get('user_id'),
         'company_id'  => Session::get('user_company_id'),
         'action_at'   => now(),
      ]);
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
               $data = array_map('trim', $data);
               if($data[0]!="" && $data[1]!="" && $data[2]!=""){                  
                  $series = $data[1];
                  $bill_no = $data[2];
                  $receipt = Contra::select('id')
                                       ->where('voucher_no',$bill_no)
                                       ->where('series_no',trim($series))
                                       ->where('delete','0')
                                       ->where('status','1')
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
            $data = array_map('trim', $data);
            if($data[0]=="" && $data[1]=="" && $data[2]=="" && $data[3]=="" && $data[4]=="" && $data[5]=="" && $data[6]==""){
               $index++;
               continue;                  
            }
            if($data[0]!="" && $data[1]!=""){
               if($bill_date!=""){
                  array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"remark"=>$remark,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
               }
               $credit_count = 0;$debit_count = 0;
               $txn_arr = [];
               $error_arr = [];
               $bill_date = $data[0];
               $series = $data[1];
               $bill_no = $data[2];
               $mode = $data[3];
               $remark = $data[4];
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
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($check_receipt){
                     array_push($error_arr, 'Contra on bill no. - '.$bill_no.' already exists');
                  }
               }
            }
            $account = $data[5];
            $check_account = Accounts::where('account_name',trim($account))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$check_account){
               array_push($error_arr, 'Account Name '.$account.' Not Found - Row '.$index);
            }
            $debit = $data[6];
            $debit = str_replace(",","",$debit);
            $credit = $data[7];
            $credit = str_replace(",","",$credit);
            if($debit=="" && $credit==""){
               array_push($error_arr, 'Debit/Credit Cannot - Row '.$index);
            }
            if($debit!="" && $credit!=0){
               $debit_count++;
            }
            if($credit!="" && $credit!=0){
               $credit_count++;
            }
            if($debit_count>1 || $credit_count>1){
               array_push($error_arr, 'Debit/Credit Only 1 entry Allowed - Row '.$index);
            }
            if($check_account){
               array_push($txn_arr,array("account"=>$check_account->id,"debit"=>$debit,"credit"=>$credit));
            }else{
               array_push($txn_arr,array("account"=>$account,"debit"=>$debit,"credit"=>$credit));
            }
            
            if($index==$total_row){
               array_push($data_arr,array("bill_date"=>$bill_date,"series"=>$series,"bill_no"=>$bill_no,"mode"=>$mode,"remark"=>$remark,"txn_arr"=>$txn_arr,"error_arr"=>$error_arr));
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
               $receipt->long_narration = $value['remark'];
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

public function exportView()
{
    return view('contra.export');
}

public function export(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'required|date',
    ]);

    $companyId = Session::get('user_company_id');

    $contras = DB::table('contras')
        ->where('company_id', $companyId)
        ->where('status', '1')
        ->where(function ($q) {
            $q->where('delete', '0')
              ->orWhereNull('delete');
        })
        ->whereDate('date', '>=', $request->from_date)
        ->whereDate('date', '<=', $request->to_date)
        ->orderBy('date')
        ->orderBy('voucher_no')
        ->get();

    if ($contras->isEmpty()) {
        return back()->with('error', 'No Contra data found');
    }

    $filename = "contra_export_" . now()->format('Ymd_His') . ".csv";

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function () use ($contras, $companyId) {

        $file = fopen('php://output', 'w');

        // CSV Header
        fputcsv($file, [
            'DATE',
            'BRANCH',
            'VCH. NO',
            'ACCOUNT NAME',
            'DEBIT AMOUNT',
            'CREDIT AMOUNT',
            'NARRATION'
        ]);

        foreach ($contras as $contra) {

            $details = DB::table('contra_details')
                ->leftJoin('accounts', 'accounts.id', '=', 'contra_details.account_name')
                ->where('contra_details.contra_id', $contra->id)
                ->where('contra_details.company_id', $companyId)
                ->where(function ($q) {
                    $q->where('contra_details.delete', '0')
                      ->orWhereNull('contra_details.delete');
                })
                ->select(
                    'accounts.account_name as acc_name',
                    'contra_details.debit',
                    'contra_details.credit',
                    'contra_details.narration'
                )
                ->get();

            $firstRow = true;

            foreach ($details as $d) {

                fputcsv($file, [

                    $firstRow ? date('d-m-Y', strtotime($contra->date)) : '',
                    $firstRow ? $contra->series_no : '',
                    $firstRow ? $contra->voucher_no : '',

                    $d->acc_name ?? '',
                    $d->debit ?? 0,
                    $d->credit ?? 0,
                    $d->narration ?? $contra->long_narration ?? ''

                ]);

                $firstRow = false;
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
