<?php

namespace App\Http\Controllers\manageitem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManageItems;
use App\Models\AccountGroups;
use App\Models\ItemGroups;
use App\Models\Units;
use App\Models\ItemLedger;
use App\Models\StockJournal;
use App\Models\StockJournalDetail;
use App\Models\Companies;
use App\Models\VoucherSeriesConfiguration;
use App\Models\GstBranch;
use App\Models\ItemBalanceBySeries;
use App\Models\ItemAverageDetail;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Gate;
class ManageItemsController extends Controller
{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
      Gate::authorize('view-module', 8);
      $com_id = Session::get('user_company_id');
      $status = ['1','0'];
      if($request->filter){
         if($request->filter=="Disable"){
            $status = ['0'];
         }else if($request->filter=="Enable"){
            $status = ['1'];
         }
      }
      if($request->filter=="InComplete"){
         $manageitems = DB::table('manage_items')
        ->select('units.name as unit_name', 'item_groups.group_name','manage_items.*')
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->where(function($q){
            $q->orWhere('manage_items.u_name', '');
            $q->orWhere('manage_items.hsn_code', '');
            $q->orWhere('manage_items.gst_rate', '');
            $q->orWhere('manage_items.gst_rate',null);
            $q->orWhere('manage_items.u_name',null);
            $q->orWhere('manage_items.hsn_code',null);
         })
        ->whereIn('manage_items.status',$status)
        ->get();
      }else{
         $manageitems = DB::table('manage_items')
        ->select('units.name as unit_name', 'item_groups.group_name','manage_items.*')
        ->join('units', 'units.id', '=', 'manage_items.u_name')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->where('manage_items.company_id', $com_id)
        ->where('manage_items.delete', '0')
        ->whereIn('manage_items.status',$status)
        ->get();
      }
      $manageitems = $manageitems->map(function ($item, $key) {
         $item->series_open = ItemBalanceBySeries::where('item_id',$item->id)->get();
         $item->item_delete_btn_view = 1;
         $exist = ItemLedger::where('item_id', $item->id)
                           ->where('source', '!=', '-1')
                           ->where('delete_status', '=', '0')
                           ->first();
         if($exist){
            $item->item_delete_btn_view = 0;
         }
         return $item;
     });  
      return view('manageitem/accountManageItem')->with('manageitems', $manageitems);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){   
      Gate::authorize('view-module', 79);   
      $com_id = Session::get('user_company_id');
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }         
      }
      $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
      $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        return view('manageitem/addAccountManageItem')->with('accountunit', $accountunit)->with('itemGroups', $itemGroups)->with('series',$series);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      Gate::authorize('view-module', 79);
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      // echo "<pre>";
      // print_r($request->all());die;
      $items = new ManageItems;
      $items->company_id =  Session::get('user_company_id');
      $items->name = $request->input('name');
      $items->p_name = $request->input('p_name');
      $items->g_name = $request->input('g_name');
      $items->u_name = $request->input('u_name');
      $items->hsn_code = $request->input('hsn_code');
      $items->gst_rate = $request->input('gst_rate');
      $items->item_type = $request->input('item_type');
      $items->status = $request->input('status');
      $items->section = $request->input('section');
      $items->rate_of_tcs = $request->input('rate_of_tcs');      
      // $items->opening_balance_qty = $request->input('opening_balance_qty');
      // $items->opening_balance_qt_type = $request->input('opening_balance_qt_type');
      // $items->opening_balance = $request->input('opening_balance');
      // $items->opening_balance_type = $request->input('opening_balance_type');
      $items->save();
      if($items->id) {
         $series = $request->input('series');
         $opening_amount = $request->input('opening_amount');
         $opening_qty = $request->input('opening_qty');
         $opening_balance_type = $request->input('opening_balance_type');
         foreach ($series as $key => $value) {
            $opening_amount[$key] = trim(str_replace(",","",$opening_amount[$key]));
            $opening_qty[$key] = trim(str_replace(",","",$opening_qty[$key]));
            if(!empty($opening_amount[$key]) && !empty($opening_qty[$key])){
               $series_balance = new ItemBalanceBySeries;
               $series_balance->item_id = $items->id;
               $series_balance->series = $value;
               $series_balance->opening_amount = $opening_amount[$key];
               $series_balance->opening_quantity = $opening_qty[$key];
               $series_balance->type = $opening_balance_type[$key];
               $series_balance->company_id = Session::get('user_company_id');
               $series_balance->created_at =  Carbon::now();;
               $series_balance->save();
               //Add In Item Ledger
               $ledger = new ItemLedger();
               $ledger->item_id = $items->id;
               if($opening_balance_type[$key]=='Debit'){
                  $ledger->in_weight = $opening_qty[$key];
               }else if($opening_balance_type[$key]=='Credit'){
                  $ledger->out_weight = $opening_qty[$key];
               }
               $ledger->series_no = $value;
               $ledger->total_price = $opening_amount[$key];
               $ledger->company_id = Session::get('user_company_id');
               $ledger->source = -1;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $default_fy = explode("-",Session::get('default_fy'));
               $txn_date = $default_fy[0]."-04-01";
               $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
               $ledger->save();
            }
         }
         return redirect('account-manage-item')->withSuccess('Items added successfully!');
      }else{
         $this->failedMessage();
      }
   }
   public function edit($id){
      Gate::authorize('view-module', 51);
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
      if($companyData->gst_config_type == "single_gst"){
         $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
         if(count($branch)>0){
            $series = $series->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series as $key => $value) {
            $branch = GstBranch::select('id','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $series = $series->merge($branch);
            }
         }         
      }
        $manageitems = ManageItems::find($id);
        $series_open = ItemBalanceBySeries::select('series','opening_amount','opening_quantity','type')->where('item_id',$id)->get();
      $grouped = $series_open->groupBy('series')->toArray();
      foreach ($series as $key => $value) {
         if(isset($grouped[$value->series])){
            $series[$key]->opening_amount = $grouped[$value->series][0]['opening_amount'];
            $series[$key]->opening_quantity = $grouped[$value->series][0]['opening_quantity'];
            $series[$key]->type = $grouped[$value->series][0]['type'];
         }else{
            $series[$key]->opening_amount = "";
            $series[$key]->opening_quantity = "";
            $series[$key]->type = "";
         }
      }
        $com_id = Session::get('user_company_id');
        $itemGroups = ItemGroups::where('delete', '=', '0')->where('company_id', $com_id)->get();
        $accountunit = Units::where('delete', '=', '0')->where('company_id', $com_id)->get();
        return view('manageitem/editAccountManageItems')->with('accountunit', $accountunit)->with('itemGroups', $itemGroups)->with('manageitems', $manageitems)->with('series',$series);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      Gate::authorize('view-module', 51);
      $validator = Validator::make($request->all(), [
         'name' => 'required|string',
      ], [
         'name.required' => 'Name is required.',
      ]);
      if ($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $items =  ManageItems::find($request->mangeitem_id);
      $items->name = $request->input('name');
      $items->p_name = $request->input('p_name');
      $items->g_name = $request->input('g_name');
      $items->u_name = $request->input('u_name');
      // $items->opening_balance_qty = $request->input('opening_balance_qty');
      // $items->opening_balance_qt_type = $request->input('opening_balance_qt_type');
      // $items->opening_balance = $request->input('opening_balance');
      // $items->opening_balance_type = $request->input('opening_balance_type');
      $items->gst_rate = $request->input('gst_rate');
      $items->item_type = $request->input('item_type');
      $items->hsn_code = $request->input('hsn_code');
      $items->status = $request->input('status');
      $items->updated_at = Carbon::now();
      if($items->update()){
         ItemBalanceBySeries::where('item_id',$items->id)->delete();
         ItemLedger::where('item_id',$items->id)->where('source','-1')->delete();
         $series = $request->input('series');
         $opening_amount = $request->input('opening_amount');
         $opening_qty = $request->input('opening_qty');
         $opening_balance_type = $request->input('opening_balance_type');
         foreach ($series as $key => $value) {
            $opening_amount[$key] = trim(str_replace(",","",$opening_amount[$key]));
            $opening_qty[$key] = trim(str_replace(",","",$opening_qty[$key]));
            if(!empty($opening_amount[$key]) && !empty($opening_qty[$key])){
               $series_balance = new ItemBalanceBySeries;
               $series_balance->item_id = $items->id;
               $series_balance->series = $value;
               $series_balance->opening_amount = $opening_amount[$key];
               $series_balance->opening_quantity = $opening_qty[$key];
               $series_balance->type = $opening_balance_type[$key];
               $series_balance->company_id = Session::get('user_company_id');
               $series_balance->created_at =  Carbon::now();;
               $series_balance->save();
               //Add In Item Ledger
               $ledger = new ItemLedger();
               $ledger->item_id = $items->id;
               if($opening_balance_type[$key]=='Debit'){
                  $ledger->in_weight = $opening_qty[$key];
               }else if($opening_balance_type[$key]=='Credit'){
                  $ledger->out_weight = $opening_qty[$key];
               }
               $ledger->series_no = $value;
               $ledger->total_price = $opening_amount[$key];
               $ledger->company_id = Session::get('user_company_id');
               $ledger->source = -1;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $default_fy = explode("-",Session::get('default_fy'));
               $txn_date = $default_fy[0]."-04-01";
               $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
               $ledger->save();
            }
         }
      }
      return redirect('account-manage-item')->withSuccess('item updated successfully!');
   }
   public function delete(Request $request){
      Gate::authorize('view-module', 52);
      $exist = ItemLedger::where('item_id', $request->heading_id)
      ->where('source', '!=', -1)
      ->where('delete_status', '=', '0')
      ->first();
      // If no ItemLedger exists except source = -1
      if (!$exist) {
         $account = ManageItems::find($request->heading_id);
         if ($account) {
            $account->delete = '1'; // Optional custom flag, avoid using "delete" as column name
            $account->deleted_at = Carbon::now();
            $account->update(); // Use save() to persist model changes
         }
         $del = ItemLedger::where('item_id', $request->heading_id)
               ->where('source', -1)
               ->first();
         if ($del) {
            $del->delete_status = '1'; // Optional custom flag
            $del->deleted_at = Carbon::now();
            $del->update(); // Persist changes
         }
         return redirect('account-manage-item')->withSuccess('Item deleted successfully!');
      } else {
         return redirect('account-manage-item')->withErrors('Item cannot be deleted. Transactions exist.');
      }
   }
   public function failedMessage(){
      return response()->json([
         'code' => 422,
         'message' => 'Something went wrong, please try again after some time.',
      ]);
   }
public function stockJournal(Request $request)
{
    Gate::authorize('view-module', 30);

    $input = $request->all();
    $from_date = null;
    $to_date = null;

    // Handle date selection from input or session
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('Y-m-d', strtotime($input['from_date']));
        $to_date = date('Y-m-d', strtotime($input['to_date']));
        session(['stockJournal_from_date' => $from_date, 'stockJournal_to_date' => $to_date]);
    } elseif (session()->has('stockJournal_from_date') && session()->has('stockJournal_to_date')) {
        $from_date = session('stockJournal_from_date');
        $to_date = session('stockJournal_to_date');
    }

    Session::put('redirect_url', '');

    // Financial Year Month Array
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07',
        $from . '-08', $from . '-09', $from . '-10', $from . '-11',
        $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];

    $company_id = Session::get('user_company_id');

    // Start base query
    $query = DB::table('stock_journal_detail')
        ->leftJoin('manage_items', 'stock_journal_detail.consume_item', '=', 'manage_items.id')
        ->leftJoin('manage_items as new', 'stock_journal_detail.new_item', '=', 'new.id')
        ->select(
            'stock_journal_detail.parent_id as id',
            'journal_date',
            'consume_weight',
            'new_weight',
            'manage_items.name',
            'new.name as new_item',
            'consume_price',
            'consume_amount',
            'new_price',
            'new_amount'
        )
        ->where('stock_journal_detail.status', 1)
        ->where('stock_journal_detail.company_id', $company_id);

    // Apply date filter or limit to 10
    if ($from_date && $to_date) {
        $query->whereRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') >= STR_TO_DATE(?, '%Y-%m-%d')", [$from_date])
              ->whereRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') <= STR_TO_DATE(?, '%Y-%m-%d')", [$to_date])
              ->orderBy('journal_date', 'asc');
    } else {
        $query->orderByRaw("STR_TO_DATE(journal_date, '%Y-%m-%d') desc")->limit(10);
    }

    $journal = $query->get()->reverse()->values(); // Show oldest first

    return view('manageitem/stock-journal')
        ->with('journals', $journal)
        ->with('month_arr', $month_arr)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

   public function addStockJournal(){
      Gate::authorize('view-module', 86);
      $financial_year = Session::get('default_fy');
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*','units.s_name as unit'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->where('manage_items.status','1')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();
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
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      if($companyData->gst_config_type == "single_gst"){
         $series_list = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series_list[0]->id])
                           ->get();
         if(count($branch)>0){
            $series_list = $series_list->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series_list = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series_list as $key => $value) {
               $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
               if(count($branch)>0){
                  $series_list = $series_list->merge($branch);
               }
         }         
      }
      foreach ($series_list as $key => $value) {         
         $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$value->series)
               ->where('configuration_for','STOCK JOURNAL')
               ->where('status','1')
               ->first();
         $voucher_no = StockJournal::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',Session::get('default_fy'))
                           ->where('series_no','=',$value->series)
                           ->max(\DB::raw("cast(voucher_no as SIGNED)"));
                           if(!$voucher_no){
                              if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                                 $series_list[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
                              }else{
                                 $series_list[$key]->invoice_start_from =  "001";
                              }            
                           }else{
                              $invc = $voucher_no + 1;
                              $invc = sprintf("%'03d", $invc);
                              $series_list[$key]->invoice_start_from =  $invc;
                           }
         $invoice_prefix = "";
         $duplicate_voucher = "";
         $blank_voucher = "";
         $manual_enter_invoice_no = "0";
         if($series_configuration && $series_configuration->manual_numbering=="YES"){
            $manual_enter_invoice_no = "1";
            $duplicate_voucher = $series_configuration->duplicate_voucher;
            $blank_voucher = $series_configuration->blank_voucher;
         }
         if($series_configuration && $series_configuration->manual_numbering=="NO"){
               $manual_enter_invoice_no = "0";
               if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!=""){
                  $invoice_prefix.=$series_configuration->prefix_value;
               }        
               if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!="" && $series_configuration->separator_1!=""){
                  $invoice_prefix.=$series_configuration->separator_1;
               }
               if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!=""){
                  if($series_configuration->year_format=="YY-YY"){
                     $invoice_prefix.=Session::get('default_fy');
                  }else if($series_configuration->year_format=="YYYY-YY"){
                     $default_fy = Session::get('default_fy');  // 23-24
                     $fy_parts = explode('-', $default_fy);     // [23, 24]
                     $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
                  }
               }            
               if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
               }
               $invoice_prefix.=$series_list[$key]->invoice_start_from;
               if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!="" &&  $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
               }
               if($series_configuration->year=="SUFFIX TO NUMBER" &&                        $series_configuration->year_format!=""){
                  if($series_configuration->year_format=="YY-YY"){
                     $invoice_prefix.=Session::get('default_fy');
                  }else if($series_configuration->year_format=="YYYY-YY"){
                     $default_fy = Session::get('default_fy');  // 23-24
                     $fy_parts = explode('-', $default_fy);     // [23, 24]
                     $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];   
                  }
               }       
               if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!="" && $series_configuration->separator_3!=""){
                  $invoice_prefix.=$series_configuration->separator_3;
               }
               if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!=""){
                  $invoice_prefix.=$series_configuration->suffix_value;
               } 
         }
         $series_list[$key]->manual_enter_invoice_no =  $manual_enter_invoice_no;
         $series_list[$key]->duplicate_voucher =  $duplicate_voucher;
         $series_list[$key]->blank_voucher =  $blank_voucher;
         $series_list[$key]->invoice_prefix =  $invoice_prefix;
      }
      return view('manageitem/add-stock-journal')->with('items', $items)->with('date', $bill_date)->with('series_list', $series_list);
   }
   public function saveStockJournal(Request $request){
      Gate::authorize('view-module', 86);
      // echo "<pre>";
      // print_r($request->all());
      // die;
      $financial_year = Session::get('default_fy');
      $date = $request->input('date');
      $narration = $request->input('narration');
      $series_no = $request->input('series_no');
      $voucher_prefix = $request->input('voucher_prefix');
      $voucher_no = $request->input('voucher_no');
      $manual_enter_invoice_no = $request->input('manual_enter_invoice_no');
      $material_center = $request->input('material_center');

      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');
      $consume_units = $request->input('consume_units');
      $consume_unit_name = $request->input('consume_unit_name');

      $generated_item = $request->input('generated_item');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');
      $generated_units = $request->input('generated_units');
      $generated_unit_name = $request->input('generated_unit_name');

      $stockjournal = new StockJournal;
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->series_no = $series_no;
      $stockjournal->material_center = $material_center;
      $stockjournal->voucher_no_prefix = $voucher_prefix;
      $stockjournal->voucher_no = $voucher_no;
      $stockjournal->company_id = Session::get('user_company_id');
      $stockjournal->created_by = Session::get('user_id');
      $stockjournal->financial_year = $financial_year;
      $stockjournal->created_at = date('d-m-Y H:i:s');
      if($stockjournal->save()){
         foreach ($consume_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
            $stockjournaldetail->consume_item_unit = $consume_units[$key];
            $stockjournaldetail->consume_item_unit_name = $consume_unit_name[$key];
            $stockjournaldetail->consume_weight = $consume_weight[$key];
            $stockjournaldetail->consume_price = $consume_price[$key];
            $stockjournaldetail->consume_amount = $consume_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $consume_item[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->out_weight = $consume_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $consume_price[$key];
            $item_ledger->total_price = $consume_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $consume_item[$key];
            $average_detail->type = 'STOCK JOURNAL CONSUME';
            $average_detail->stock_journal_out_id = $stockjournal->id;
            $average_detail->stock_journal_out_weight = $consume_weight[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$consume_item[$key],$request->input('series_no'));
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->new_item = $generated_item[$key];
            $stockjournaldetail->new_item_unit = $generated_units[$key];
            $stockjournaldetail->new_item_unit_name = $generated_unit_name[$key];
            $stockjournaldetail->new_weight = $generated_weight[$key];
            $stockjournaldetail->new_price = $generated_price[$key];
            $stockjournaldetail->new_amount = $generated_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->in_weight = $generated_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $generated_price[$key];
            $item_ledger->total_price = $generated_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->entry_date = $request->date;
            $average_detail->item_id = $generated_item[$key];
            $average_detail->type = 'STOCK JOURNAL GENERATE';
            $average_detail->stock_journal_in_id = $stockjournal->id;
            $average_detail->stock_journal_in_weight = $generated_weight[$key];
            $average_detail->stock_journal_in_amount = $generated_amount[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$generated_item[$key],$request->input('series_no'));
         }
         return redirect('stock-journal')->withSuccess('Stock Journal Added Successfully!'); 
      }
   }
   public function deleteStockJournal(Request $request){
      Gate::authorize('view-module', 64);
      $id = $request->input('del_id');
      $stock_journal = StockJournal::find($id);
      $delete = StockJournal::where('id',$id)->delete();
      if($delete){
         ItemAverageDetail::where('stock_journal_in_id',$id)
                           ->where('type','STOCK JOURNAL GENERATE')
                           ->delete();    
         ItemAverageDetail::where('stock_journal_out_id',$id)
                        ->where('type','STOCK JOURNAL CONSUME')
                        ->delete();
         $desc = StockJournalDetail::where('parent_id',$id)->get();
         foreach ($desc as $key => $value) {
            if(!empty($value->consume_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->consume_item,$stock_journal->series_no);
            }else if(!empty($value->new_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->new_item,$stock_journal->series_no);
            }               
         }
         StockJournalDetail::where('parent_id',$id)->delete();
         ItemLedger::where('source',3)
                     ->where('source_id',$id)
                     ->delete();
         return redirect('stock-journal')->withSuccess('Stock Journal Deleted Successfully!');          
      }      
   }  
   public function editStockJournal(Request $request,$id){
      Gate::authorize('view-module', 63);
      $journal = StockJournal::find($id); 
      $journal_details = StockJournalDetail::where('parent_id',$id)
                                             ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      if($companyData->gst_config_type == "single_gst"){
         $series_list = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series_list[0]->id])
                           ->get();
         if(count($branch)>0){
            $series_list = $series_list->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $series_list = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($series_list as $key => $value) {
               $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
               if(count($branch)>0){
                  $series_list = $series_list->merge($branch);
               }
         }         
      }   
      $items = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['manage_items.*','units.s_name as unit'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->where('manage_items.status','1')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();     
      return view('manageitem/edit-stock-journal')->with('journal', $journal)->with('items', $items)->with('journal_details', $journal_details)->with('series_list', $series_list);     
   }
   public function updateStockJournal(Request $request){
      Gate::authorize('view-module', 63);
      $date = $request->input('date');
      $narration = $request->input('narration');
      $series_no = $request->input('series_no');
      $voucher_prefix = $request->input('voucher_prefix');
      $voucher_no = $request->input('voucher_no');
      $material_center = $request->input('material_center');
      
      $consume_item = $request->input('consume_item');
      $consume_weight = $request->input('consume_weight');
      $consume_price = $request->input('consume_price');
      $consume_amount = $request->input('consume_amount');
      $consume_units = $request->input('consume_units');
      $consume_unit_name = $request->input('consume_unit_name');

      $generated_item = $request->input('generated_item');
      $generated_weight = $request->input('generated_weight');
      $generated_price = $request->input('generated_price');
      $generated_amount = $request->input('generated_amount');
      $generated_units = $request->input('generated_units');
      $generated_unit_name = $request->input('generated_unit_name');

      $stockjournal = StockJournal::find($request->input('edit_id'));
      $last_date =  $stockjournal->jdate;
      $stockjournal->jdate = $date;
      $stockjournal->narration = $narration;
      $stockjournal->series_no = $series_no;
      $stockjournal->material_center = $material_center;
      $stockjournal->voucher_no_prefix = $voucher_prefix;
      $stockjournal->voucher_no = $voucher_no;
      $stockjournal->updated_by = Session::get('user_id');
      $stockjournal->updated_at = date('d-m-Y H:i:s');

      
      if($stockjournal->save()){  
         //$desc_item_arr = StockJournalDetail::where('parent_id',$id)->pluck('goods_discription')->toArray();

         ItemAverageDetail::where('stock_journal_in_id',$id)
                           ->where('type','STOCK JOURNAL GENERATE')
                           ->delete();    
         ItemAverageDetail::where('stock_journal_out_id',$id)
                        ->where('type','STOCK JOURNAL CONSUME')
                        ->delete();
         $desc = StockJournalDetail::where('parent_id',$id)->get();
         foreach ($desc as $key => $value) {
            if(!empty($value->consume_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->consume_item,$stock_journal->series_no);
            }else if(!empty($value->new_item)){
               CommonHelper::RewriteItemAverageByItem($stock_journal->jdate,$value->new_item,$stock_journal->series_no);
            }               
         }

         StockJournalDetail::where('parent_id',$request->input('edit_id'))->delete();
         ItemLedger::where('source',3)->where('source_id',$request->input('edit_id'))->delete();

         foreach ($consume_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->consume_item = $consume_item[$key];
            $stockjournaldetail->consume_item_unit = $consume_units[$key];
            $stockjournaldetail->consume_item_unit_name = $consume_unit_name[$key];
            $stockjournaldetail->consume_weight = $consume_weight[$key];
            $stockjournaldetail->consume_price = $consume_price[$key];
            $stockjournaldetail->consume_amount = $consume_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $consume_item[$key];
            $item_ledger->out_weight = $consume_weight[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $consume_price[$key];
            $item_ledger->total_price = $consume_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $consume_item[$key];
            $average_detail->type = 'STOCK JOURNAL CONSUME';
            $average_detail->stock_journal_out_id = $stockjournal->id;
            $average_detail->stock_journal_out_weight = $consume_weight[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$consume_item[$key],$request->input('series_no'));
         }
         foreach ($generated_item as $key => $value){
            $stockjournaldetail = new StockJournalDetail;
            $stockjournaldetail->journal_date = $date;
            $stockjournaldetail->parent_id = $stockjournal->id;
            $stockjournaldetail->new_item = $generated_item[$key];
            $stockjournaldetail->new_item_unit = $generated_units[$key];
            $stockjournaldetail->new_item_unit_name = $generated_unit_name[$key];
            $stockjournaldetail->new_weight = $generated_weight[$key];
            $stockjournaldetail->new_price = $generated_price[$key];
            $stockjournaldetail->new_amount = $generated_amount[$key];
            $stockjournaldetail->company_id = Session::get('user_company_id');
            $stockjournaldetail->created_by = Session::get('user_id');;
            $stockjournaldetail->created_at = date('d-m-Y H:i:s');
            $stockjournaldetail->save();
            //ADD IN Stock
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $generated_item[$key];
            $item_ledger->in_weight = $generated_weight[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->price = $generated_price[$key];
            $item_ledger->total_price = $generated_amount[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 3;
            $item_ledger->source_id = $stockjournal->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();

            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->entry_date = $request->date;
            $average_detail->item_id = $generated_item[$key];
            $average_detail->type = 'STOCK JOURNAL GENERATE';
            $average_detail->stock_journal_in_id = $stockjournal->id;
            $average_detail->stock_journal_in_weight = $generated_weight[$key];
            $average_detail->stock_journal_in_amount = $generated_amount[$key];
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$generated_item[$key],$request->input('series_no'));
         }
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('stock-journal')->withSuccess('Stock Journal Updated Successfully!');
         }
          
      }
   }
   public function itemImportView(Request $request){      
      return view('manageitem/item_import_view');
   }
   public function itemImportProcess(Request $request) {       
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;      
           
      $already_exists_error_arr = [];$already_exists_item_arr = [];$error_arr = [];$data_arr = [];
      $all_error_arr = [];
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
               if($data[0]!="" && $data[2]!=""){
                  $name = $data[0];
                  $item = ManageItems::select('id')
                                       ->where('name',trim($name))
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($item){
                     array_push($already_exists_error_arr, 'Item '.$name.' already exists');                     
                     // if(in_array($name, $already_exists_item_arr)){
                     //    array_push($already_exists_error_arr, 'Item '.$name.' already exists');
                     // }
                     // array_push($already_exists_item_arr,$name);
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
            $error_arr = [];
            $name = $data[0];
            $group = $data[1];
            $amount = $data[2];
            $quantity = $data[3];
            $unit = $data[4];
            $gst_rate = $data[5];
            $hsn_code = $data[6];
            if($name=="" && $group=="" && $amount=="" && $quantity=="" && $unit=="" && $gst_rate=="" && $hsn_code==""){
               $index++;
               continue;                  
            }
            if($name=="" || $group==""){
               array_push($error_arr, 'Item Name or Group Name Not Found In Row -'.$index);
            }               
            $groups = ItemGroups::where('group_name',trim($group))
                     ->where('company_id',trim(Session::get('user_company_id')))
                     ->first();
            if(!$groups){
               array_push($error_arr, 'Group Name - '.$group.' not found');
            } 
            $units = Units::where('s_name',trim($unit))
                     ->where('company_id',trim(Session::get('user_company_id')))
                     ->first();
            if(!$units){
               array_push($error_arr, 'Unit - '.$unit.' not found');
            }                
            if($duplicate_voucher_status!=2){
               $check_item = ManageItems::select('id')
                           ->where('name',trim($name))
                           ->where('company_id',trim(Session::get('user_company_id')))
                           ->first();
               if($check_item){
                  array_push($error_arr, 'Item '.$name.' already exists');
               }
            }
            array_push($data_arr,array("name"=>$name,"group"=>$group,"amount"=>$amount,"quantity"=>$quantity,"unit"=>$unit,"gst_rate"=>$gst_rate,"hsn_code"=>$hsn_code,"error_arr"=>$error_arr));            
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
               $name = $value['name'];
               $group = $value['group'];
               $amount = $value['amount'];
               $amount = trim(str_replace(",","",$amount)); 
               $quantity = $value['quantity'];
               $quantity = trim(str_replace(",","",$quantity));
               $unit = $value['unit'];
               $gst_rate = $value['gst_rate'];
               $hsn_code = $value['hsn_code'];
               $groups = ItemGroups::where('group_name',trim($group))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();               
               $units = Units::where('s_name',trim($unit))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               $opening_balance_qty = "";
               $opening_balance_qt_type = "";
               $opening_balance = "";
               $opening_balance_type = "";
               if(!empty($amount) && $amount!=0){
                  if($amount<0){
                     $opening_balance = abs($amount);
                     $opening_balance_type = "Credit";
                  }else{
                     $opening_balance = $amount;
                     $opening_balance_type = "Debit";
                  }                  
               }
               $quantity = (float)$quantity;
               if(!empty($quantity) && $quantity!=0){
                  if($quantity<0){
                     $opening_balance_qty = abs($quantity);
                     $opening_balance_qt_type = "Credit";
                  }else{
                     $opening_balance_qty = $quantity;
                     $opening_balance_qt_type = "Debit"; 
                  }
                  
               }
               if($duplicate_voucher_status==2){
                  $check_items = ManageItems::select('id')
                              ->where('name',trim($name))
                              ->where('company_id',trim(Session::get('user_company_id')))
                              ->first();
                  if($check_items){                 
                     $items = ManageItems::find($check_items->id);
                     $items->company_id =  Session::get('user_company_id');
                     $items->name = $name;
                     $items->p_name = $name;
                     $items->g_name = $groups->id;
                     $items->u_name = $units->id;
                     $items->hsn_code = $hsn_code;
                     $items->gst_rate = $gst_rate;
                     $items->opening_balance_qty = $opening_balance_qty;
                     $items->opening_balance_qt_type = $opening_balance_qt_type;
                     $items->opening_balance = $opening_balance;
                     $items->opening_balance_type = $opening_balance_type;
                     $items->updated_at = Carbon::now();
                     $items->update();
                     if($items){
                        $success_invoice_count++;
                        if(!empty($opening_balance_qty) && !empty($opening_balance_qt_type)){
                           $check = ItemLedger::where('item_id',$check_items->id)
                                                   ->where('source','-1')
                                                   ->first();
                           if($check){          
                              $ledger = ItemLedger::find($check->id);
                              if($opening_balance_qt_type=='Debit'){
                                 $ledger->in_weight = $opening_balance_qty;
                                 $ledger->out_weight = "";
                              }else if($opening_balance_qt_type=='Credit'){
                                 $ledger->out_weight = $opening_balance_qty;
                                 $ledger->in_weight = "";
                              }
                              $ledger->total_price = $opening_balance;
                              $ledger->updated_by = Session::get('user_id');
                              $ledger->updated_at = date('d-m-Y H:i:s');
                              $ledger->save();
                           }else{
                              $ledger = new ItemLedger();
                              $ledger->item_id = $check_items->id;
                              if($opening_balance_qt_type=='Debit'){
                                 $ledger->in_weight = $opening_balance_qty;
                              }else if($opening_balance_qt_type=='Credit'){
                                 $ledger->out_weight = $opening_balance_qty;
                              }
                              $ledger->total_price = $opening_balance;
                              $ledger->company_id = Session::get('user_company_id');
                              $ledger->source = -1;
                              $default_fy = explode("-",Session::get('default_fy'));
                              $txn_date = $default_fy[0]."-04-01";
                              $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
                              $ledger->created_by = Session::get('user_id');
                              $ledger->created_at = date('d-m-Y H:i:s');
                              $ledger->save();
                           }
                        }else{
                           $check = ItemLedger::where('item_id',$check_items->id)
                                                   ->where('source','-1')
                                                   ->first();
                           if($check){
                              ItemLedger::where('source','-1')
                                             ->where('item_id',$check_items->id)
                                             ->delete();
                           }
                        }
                        continue;
                     }
                  }                  
               }
               $items = new ManageItems;
               $items->company_id =  Session::get('user_company_id');
               $items->name = $name;
               $items->p_name = $name;
               $items->g_name = $groups->id;
               $items->u_name = $units->id;
               $items->hsn_code = $hsn_code;
               $items->gst_rate = $gst_rate;
               $items->opening_balance_qty = $opening_balance_qty;
               $items->opening_balance_qt_type = $opening_balance_qt_type;
               $items->opening_balance = $opening_balance;
               $items->opening_balance_type = $opening_balance_type;      
               $items->status = '1';
               $items->save();
               if ($items->id){
                  if(!empty($opening_balance_qty) && !empty($opening_balance_qt_type)){
                     $ledger = new ItemLedger();
                     $ledger->item_id = $items->id;
                     if($opening_balance_qt_type=='Debit'){
                        $ledger->in_weight = $opening_balance_qty;
                     }else if($opening_balance_qt_type=='Credit'){
                        $ledger->out_weight = $opening_balance_qty;
                     }
                     $ledger->total_price = $opening_balance;
                     $ledger->company_id = Session::get('user_company_id');
                     $ledger->source = -1;
                     $ledger->created_by = Session::get('user_id');
                     $ledger->created_at = date('d-m-Y H:i:s');
                     $default_fy = explode("-",Session::get('default_fy'));
                     $txn_date = $default_fy[0]."-04-01";
                     $ledger->txn_date = date('Y-m-d',strtotime($txn_date));
                     $ledger->save();
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
