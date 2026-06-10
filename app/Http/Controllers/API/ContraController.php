<?php

namespace App\Http\Controllers\API;
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
     * Display a listing of the contra resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
{
    $input = $request->all();

    $from_date = null;
    $to_date = null;

    // Date range filter
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
    }

    // Get values from request only
    $financial_year = $request->input('financial_year');
    $com_id = $request->input('company_id');

    // Validation
    if (!$financial_year) {
        return response()->json([
            'success' => false,
            'message' => 'Financial year is required.'
        ], 400);
    }

    if (!$com_id) {
        return response()->json([
            'success' => false,
            'message' => 'Company ID is required.'
        ], 400);
    }

    // Generate Financial Year Months Array
    $y = explode("-", $financial_year);

    $fromFormat = strlen($y[0]) === 4 ? 'Y' : 'y';
    $toFormat = strlen($y[1]) === 4 ? 'Y' : 'y';

    $from = DateTime::createFromFormat($fromFormat, $y[0])->format('Y');
    $to = DateTime::createFromFormat($toFormat, $y[1])->format('Y');

    $month_arr = [
        $from . '-04',
        $from . '-05',
        $from . '-06',
        $from . '-07',
        $from . '-08',
        $from . '-09',
        $from . '-10',
        $from . '-11',
        $from . '-12',
        $to . '-01',
        $to . '-02',
        $to . '-03'
    ];

    // Build query
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

    // Apply date filter if provided
    if ($from_date && $to_date) {

        $query->whereRaw(
            "
            STR_TO_DATE(contras.date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')
            AND STR_TO_DATE(contras.date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')
            ",
            [
                date('Y-m-d', strtotime($from_date)),
                date('Y-m-d', strtotime($to_date))
            ]
        );

        $query->orderBy('contras.date', 'asc')
              ->orderBy('contras.voucher_no', 'asc');

    } else {

        // Same behavior as web version
        $query->orderBy(DB::raw("CAST(contras.voucher_no AS SIGNED)"), 'desc')
              ->orderBy('contras.date', 'desc')
              ->limit(10);
    }

    $contra = $query->get()->reverse()->values();

    return response()->json([
        'success' => true,
        'data' => [
            'contra' => $contra,
            'month_arr' => $month_arr,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]
    ], 200);
}


 public function create(Request $request){
      // Gate::authorize('action-module',75);
       $validator = Validator::make($request->all(), [
         'financial_year' => 'required',
         'user_id' => 'required',
         'company_id' => 'required',
      ], [
            'financial_year.required' => 'Financial year is required.',
            'user_id.required' => 'User ID is required.',
            'company_id.required' => 'Company ID is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }

      $financial_year = $request->input('financial_year');
      $com_id = $request->input('company_id');
      $party_list = Accounts::whereIn('company_id', [$com_id, 0])                   ->where('delete', '=', '0')
                           ->whereIn('under_group', [7,8])
                           ->where('under_group_type', '=', 'group')
                           ->orderBy('account_name')
                           ->get();
      $bill_date = Contra::where('company_id', $com_id)
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
      $companyData = Companies::where('id', $com_id)->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => $com_id, 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => $com_id, 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => $com_id,'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => $com_id, 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => $com_id, 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => $com_id,'gst_setting_multiple_id'=>$value->id])
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

               $fy = explode('-',$financial_year);
               $year = ($series_configuration->year_format == "YY-YY")
                  ? $financial_year
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

               $fy = explode('-',$financial_year);
               $year = ($series_configuration->year_format == "YY-YY")
                  ? $financial_year
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
      return response()->json([
         'code' => 200,
         'data' => [
            'party_list' => $party_list,
            'date' => $bill_date,
            'mat_series' => $mat_series
         ]
      ], 200);
     
   }







   public function store(Request $request){

    //   Gate::authorize('action-module',75);
$validator = Validator::make($request->all(), [
    'company_id'   => 'required',
    'user_id'      => 'required',
    'date'         => 'required',
    'voucher_no'   => 'required',
    'party'        => 'required',
    'series_no'    => 'required',
    'account_name' => 'required|array',
    'account_name.*' => 'required|exists:accounts,id',
    'debit'        => 'required|array',
    'credit'       => 'required|array',
]);

if ($validator->fails()) {
    return response()->json([
        'code' => 400,
        'message' => $validator->errors()->first()
    ], 400);
}
      $financial_year_session = $request->input('financial_year');
      $company_id = $request->input('company_id');
      $user_id = $request->input('user_id');
      //Session::get('default_fy');

      [$startYY, $endYY] = explode('-', $financial_year_session);

      $fy_start_date = '20' . $startYY . '-04-01';

      $fy_end_date   = '20' . $endYY   . '-03-31';

      if(
         $request->input('date') < $fy_start_date
         ||
         $request->input('date') > $fy_end_date
      ){
        return response()->json([
            'code' => 400,
            'message' => 'Selected date is outside the current financial year.'
        ], 400);
       
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $series_configuration = VoucherSeriesConfiguration::where('company_id', $company_id)
         ->where('series', $request->input('series_no'))
         ->where('configuration_for', 'CONTRA')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } else {
         $last_voucher_no = DB::table('contras')
            ->where('company_id', $company_id)
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
      $con->company_id = $company_id;
      $con->voucher_no_prefix = $request->input('voucher_prefix');
      $con->voucher_no = $voucher_no;
      $con->series_no = $request->input('series_no');
      $con->long_narration = $request->input('long_narration');
      $con->financial_year = $financial_year;
      $con->created_by = $user_id;
      $con->save();
      if($con->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');
         
         $i=0;
         foreach($types as $key => $type){
            $contype = new ContraDetails;
            $contype->contra_id = $con->id;
            $contype->company_id = $company_id;
            $contype->type = $type;
            $contype->account_name = $account_names[$key];
            $contype->debit = isset($debits[$key]) ? $debits[$key] :'0';
            $contype->credit = isset($credits[$key]) ? $credits[$key] :'0';
           
            $contype->status = '1';
            $contype->save();
            //ADD DATA IN Customer ACCOUNT
            if(count($account_names) >= 2){
               if($i == 0){
                  $map_account_id = $account_names[1];
               }else{
                  $map_account_id = $account_names[0];
               }
            }else{
               $map_account_id = 0;
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
            $ledger->company_id = $company_id;
            $ledger->financial_year = $financial_year;
            $ledger->entry_type = 8;
            $ledger->entry_type_id = $con->id;
            $ledger->entry_type_detail_id = $contype->id;
            $ledger->entry_narration = $request->input('long_narration');
            $ledger->map_account_id = $map_account_id;
            $ledger->created_by = $user_id;
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            $i++;
         }
         return response()->json([
            'success' => true,
            'message' => 'Contra added successfully!',
            'data' => [
               'contra_id' => $con->id,
               'voucher_no' => $voucher_no,
               'series_no' => $request->input('series_no'),
               'date' => $request->input('date'),
               'long_narration' => $request->input('long_narration'),
            ]
         ], 201);
   
      }else{
        $failedMessage = $this->failedMessage();

        return response()->json([
            'success' => false,
            'message' => 'Failed to add contra. Please try again. ' . $failedMessage
        ], 400);
      }        
   }


  public function edit(request $request){
    try {
        $validator = Validator::make($request->all(), [
         'edit_id' => 'required|exists:contras,id',
         'user_id' => 'required',
         'company_id' => 'required',
      ], [
            'edit_id.required' => 'Contra ID is required.',
            'edit_id.exists' => 'Contra not found.',
            'user_id.required' => 'User ID is required.',
            'company_id.required' => 'Company ID is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
        $id = $request->input('edit_id');
        $user_id = $request->input('user_id');
        
    //   Gate::authorize('action-module',45);
      $com_id = request()->input('company_id');
      $financial_year = request()->input('financial_year');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01';

      $fy_end_date   = '20' . $endYY   . '-03-31';
      $contra = Contra::find($id);
      $contra_detail = ContraDetails::where('contra_id', '=', $id)->where('delete', '=', '0')->get();
      $party_list = Accounts::whereIn('company_id', [$com_id,0])                   ->where('delete', '=', '0')
                           ->where('under_group_type', '=', 'group')
                           ->whereIn('under_group', [7,8])
                           ->orderBy('account_name')
                           ->get();
      $companyData = Companies::where('id', $com_id)->first();
      $GstSettings = (object)NULL;
      $GstSettings->series = array();$mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => $com_id, 'gst_type' => "single_gst"])->first();
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => $com_id, 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => $com_id,'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => $com_id, 'gst_type' => "multiple_gst"])->first();
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => $com_id, 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => $com_id,'gst_setting_multiple_id'=>$value->id])
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
                  $invoice_prefix .= $financial_year_session;
               } else {
                  $fy = explode('-', $financial_year_session);
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
                  $invoice_prefix .= $financial_year_session;
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
      return response()->json([
         'code' => 200,
         'data' => [
            'contra' => $contra,
            'contra_detail' => $contra_detail,
            'party_list' => $party_list,
            'mat_series' => $mat_series,
            'fy_start_date' => $fy_start_date,
            'fy_end_date' => $fy_end_date
         ]
      ], 200);
}catch (\Exception $e) {
      return response()->json([
         'code' => 400,
         'message' => 'An error occurred while fetching the contra details: ' . $e->getMessage()
      ], 500);
   }
  }

public function update(Request $request){
    try {
    //   Gate::authorize('action-module',45);
      $validator = Validator::make($request->all(), [
         'date' => 'required|string',
         'user_id' => 'required',
         'company_id' => 'required',
         'contra_id' => 'required|exists:contras,id',
         'series_no' => 'required',
      ], [
         'date.required' => 'Date is required.',
         'user_id.required' => 'User ID is required.',
         'company_id.required' => 'Company ID is required.',
            'contra_id.required' => 'Contra ID is required.',
            'contra_id.exists' => 'Contra not found.',
         'series_no.required' => 'Series number is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $financial_year_session = $request->input('financial_year');
      $user_id = $request->input('user_id');
      $company_id = $request->input('company_id');
      $series_no = $request->input('series_no');

      [$startYY, $endYY] = explode('-', $financial_year_session);

      $fy_start_date = '20' . $startYY . '-04-01';

      $fy_end_date   = '20' . $endYY   . '-03-31';

      if(
         $request->input('date') < $fy_start_date
         ||
         $request->input('date') > $fy_end_date
      ){
         return response()->json([
            "code" => 422,
            'message' =>
            'Selected date is outside the current financial year.'
         ], 422);
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $contra =  Contra::find($request->contra_id);
      if (!$contra) {
    return response()->json([
        'code' => 404,
        'message' => 'The requested Contra voucher could not be found.'
    ], 404);
}
      $oldSnapshot = [
         'contra' => $contra->toArray(),
         'details' => ContraDetails::where('contra_id', $contra->id)
                        ->where('delete', '0')
                        ->get()
                        ->toArray(),
      ];
      $contra->date = $request->input('date');
      $contra->voucher_no_prefix = $request->input('voucher_prefix');
      $contra->updated_by = $user_id;;
      $series_changed = ($contra->series_no != $series_no);
      $series_configuration = VoucherSeriesConfiguration::where('company_id', $company_id)
         ->where('series', $series_no)
         ->where('configuration_for', 'CONTRA')
         ->where('status', '1')
         ->first();
      $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
      $voucher_no = $contra->voucher_no;
      if ($series_configuration && $series_configuration->manual_numbering == "YES") {
         $voucher_no = $request->input('voucher_no') ?: $request->input('voucher_prefix');
      } elseif ($series_changed) {
         $last_voucher_no = DB::table('contras')
            ->where('company_id', $company_id)
            ->where('series_no', $series_no)
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
      $contra->series_no = $series_no;
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
      $i=0;
      foreach($types as $key => $type) {
         $paytype = new ContraDetails;
         $paytype->contra_id = $request->contra_id;
         $paytype->company_id = $company_id;
         $paytype->type = $type;
         $paytype->account_name = $account_names[$key];
         $paytype->debit = isset($debits[$key]) ? $debits[$key] : 0;
         $paytype->credit = isset($credits[$key]) ? $credits[$key] : 0;         
         $paytype->status = '1';
         $paytype->save();
         //ADD DATA IN Customer ACCOUNT
         if(count($account_names) >= 2){

            if($i == 0){
               $map_account_id = $account_names[1];
            }else{
               $map_account_id = $account_names[0];
            }

         }else{

            $map_account_id = 0;

         }
         $ledger = new AccountLedger();
         $ledger->account_id = $account_names[$key];
         if(isset($debits[$key]) && !empty($debits[$key])){
            $ledger->debit = $debits[$key];
         }else{
            $ledger->credit = $credits[$key];
         }            
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $series_no;
         $ledger->company_id = $company_id;
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 8;
         $ledger->entry_type_id = $contra->id;
         $ledger->entry_type_detail_id = $paytype->id;
         $ledger->map_account_id = $map_account_id;
         $ledger->entry_narration = $request->input('long_narration');
         $ledger->created_by = $user_id;
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
         'action_by'   => $user_id,
         'company_id'  => $company_id,
         'action_at'   => now(),
      ]);
      return response()->json([
         'code' => 200,
         'message' => 'Contra updated successfully!',
         'data' => [
            'contra_id' => $contra->id,
            'voucher_no' => $voucher_no,
            'series_no' => $series_no,
            'date' => $request->input('date'),
            'long_narration' => $request->input('long_narration'),
         ]
      ], 200);
          
   }catch (\Exception $e) {
      return response()->json([
         'code' => 500,
         'message' => 'An error occurred while updating the contra: ' . $e->getMessage()
      ], 500);
   }
}
}