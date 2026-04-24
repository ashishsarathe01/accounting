<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\BillSundrys;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ParameterInfoValue;
use App\Models\SaleParameterInfo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\State;
use App\Models\Units;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Bank;
use App\Models\SaleInvoiceTermCondition;
use App\Models\EinvoiceToken;
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\AccountOtherAddress;
use App\Models\ItemParameterStock;
use App\Models\SaleOrder;
use App\Models\SaleOrderItem;
use App\Models\SaleOrderItemGsm;
use App\Models\SaleOrderItemWeight;
use App\Models\SaleOrderItemGsmSize;
use App\Models\ItemSizeStock;
use App\Models\AccountGroups;
use App\Models\MerchantModuleMapping;
use App\Models\ActivityLog;
use App\Models\Journal;
use App\Models\JournalDetails;
use App\Models\SaleVehicleTxn;
use App\Helpers\CommonHelper;
use App\Mail\SaleInvoiceMail;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Mail;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\GdImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\URL;
use DB;
use Session;
use DateTime;
use Gate;

class SalesController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    Gate::authorize('action-module', 10);
    $input = $request->all();
    // Initialize dates
    $from_date = null;
    $to_date = null;
    // Handle date input and session persistence
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['sales_from_date' => $from_date, 'sales_to_date' => $to_date]);
    } elseif (session()->has('sales_from_date') && session()->has('sales_to_date')) {
        $from_date = session('sales_from_date');
        $to_date = session('sales_to_date');
    }
    Session::put('redirect_url', '');
    $companyId = Session::get('user_company_id');
    // Financial year processing
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');
    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07', $from . '-08', $from . '-09',
        $from . '-10', $from . '-11', $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];
    // Base query
    $query = DB::table('sales')
        ->select(
            'sales.id as sales_id',
            'sales.date',
            'sales.voucher_no',
            'sales.voucher_no_prefix',
            'sales.total',
            'financial_year',
            'series_no',
            'e_invoice_status',
            'e_waybill_status',
            'sales.status',
            'sale_order_id',
            'approved_status',
            'approved_by',
            'approved_at',
            'created_by',
            DB::raw('(select account_name from accounts where accounts.id = sales.party limit 1) as account_name'),
            DB::raw('(select manual_numbering from voucher_series_configurations 
                      where voucher_series_configurations.company_id = ' . Session::get('user_company_id') . ' 
                      and configuration_for="SALE" 
                      and voucher_series_configurations.status=1 
                      and voucher_series_configurations.series = sales.series_no 
                      limit 1) as manual_numbering_status'),
            DB::raw("(SELECT name FROM users WHERE users.id = sales.approved_by LIMIT 1) as approved_by_name"),
            DB::raw("(SELECT name FROM users WHERE users.id = sales.created_by LIMIT 1) as created_by_name"),
            DB::raw('(select max(voucher_no) from sales as s
                      where s.company_id = ' . Session::get('user_company_id') . ' 
                      and s.delete="0" 
                      and s.series_no = sales.series_no 
                      and entry_source=1) as max_voucher_no')
                      
        )
        ->where('sales.company_id', Session::get('user_company_id'))
        ->where('sales.delete', '0');

    // Date filtering and sorting logic
    if ($from_date && $to_date) {
        // If date range provided
        $query->whereBetween(DB::raw("STR_TO_DATE(sales.date, '%Y-%m-%d')"), [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('sales.date', 'ASC')
              ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
    } else {
        $query->orderBy('sales.date', 'DESC')
              ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
              ->limit(10);
    }

    // Fetch results
    $sale = $query->get();

    if (!$from_date && !$to_date) {
        $sale = $sale->reverse()->values();
    }

    return view('sale')
        ->with('sale', $sale)
        ->with('month_arr', $month_arr)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

   public function create(Request $request){
      Gate::authorize('action-module',85);
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';   
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $config = SaleInvoiceConfiguration::where('company_id', Session::get('user_company_id'))->first();
      //Ashish Code Start Here
      // echo "<pre>";
      //invoice_prefix
      //Sale Order Data
      $bill_to_id     = $request->query('bill_to_id');     // 2
      $shipp_to_id = $request->query('shipp_to_id'); // 332
      $freight   = $request->query('freight');   // 1
      $sale_order_id = $request->query('sale_order_id');
      $new_order = $request->query('new_order');
      $sale_order_items = [];
      if($request->query('item_arr')){
         $sale_order_items = json_decode($request->query('item_arr'),true);
      }
      
      $sale_enter_data = [];
      if($request->query('item_arr')){
         $sale_enter_data = $request->query('sale_enter_data');
         // echo "<pre>";
         // print_r(json_decode($sale_enter_data,true));
         $merged = array_unique(
            array_merge(...array_column(json_decode($sale_enter_data,true), 'reel_weight_id'))
         );
         $check_size_status = ItemSizeStock::select('id')->whereIn('id',$merged)->where('status',0)->get();
         if(count($check_size_status)>0){
            return $this->failedMessage('Please Check Selected Weight Stock!','sale-order-start/'.$sale_order_id);
         }
         
      } 
      
      if($companyData->gst_config_type == "single_gst"){
         $GstSettings = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$GstSettings[0]->id])
                           ->get();
         if(count($branch)>0){
            $GstSettings = $GstSettings->merge($branch);
         }         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $GstSettings = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($GstSettings as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $GstSettings = $GstSettings->merge($branch);
            }
         }         
      }
     
      if(!$companyData->gst_config_type || empty($companyData->gst_config_type) || !$GstSettings){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }
      foreach ($GstSettings as $key => $value) {         
         $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$value->series)
               ->where('configuration_for','SALE')
               ->where('status','1')
               ->first();
         $voucher_no = Sales::select('bill_date')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('series_no','=',$value->series)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
         $last_bill_date = Sales::select('date')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('series_no','=',$value->series)
                        ->where('delete','=','0')
                        ->max("date");
         if(!$voucher_no){
            if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                     $GstSettings[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
                  }else{
                     $GstSettings[$key]->invoice_start_from =  $series_configuration->invoice_start;
                  }
            }else{
               $GstSettings[$key]->invoice_start_from =  "1";
            }
         }else{
            $invc = $voucher_no + 1;
            if ($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                  $invc = sprintf("%'03d", $invc);
            }
            $GstSettings[$key]->invoice_start_from =  $invc;
         }
         $GstSettings[$key]->last_bill_date =  $last_bill_date;         
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
                  $invoice_prefix .= '20' . $fy_parts[0] . '-' . $fy_parts[1];
               }
            }            
            if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
            }
            $invoice_prefix.=$GstSettings[$key]->invoice_start_from;
            if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
                  $invoice_prefix.=$series_configuration->separator_2;
            }
            if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!=""){
                  if($series_configuration->year_format=="YY-YY"){
                  $invoice_prefix.=Session::get('default_fy');
               }else if($series_configuration->year_format=="YYYY-YY"){
                  $default_fy = Session::get('default_fy');  // 23-24
                  $fy_parts = explode('-', $default_fy);     // [23, 24]
                  $invoice_prefix .= '20' . $fy_parts[0] . '-' . $fy_parts[1];

               }
            }        
            if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!="" && $series_configuration->separator_3!=""){
                  $invoice_prefix.=$series_configuration->separator_3;
            }
            if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!=""){
                  $invoice_prefix.=$series_configuration->suffix_value;
            } 
         }
         $GstSettings[$key]->manual_enter_invoice_no =  $manual_enter_invoice_no;
         $GstSettings[$key]->duplicate_voucher =  $duplicate_voucher;
         $GstSettings[$key]->blank_voucher =  $blank_voucher;
         $GstSettings[$key]->invoice_prefix =  $invoice_prefix;
         
         
      }
      // echo "<pre>";
      // print_r($GstSettings->toArray());
      // die;
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->orderBy('name')
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
      // print_r($GstSettings);die;
      // die;
      //Ashish Code End Here



      //Account List
      $top_groups = [3, 11,7,8];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $groups = array_unique($all_groups);
      $allowed_group_ids = array_unique($all_groups);

      $no_gst_groups = [7, 8];
      $no_gst_all_groups = [];
      foreach ($no_gst_groups as $gid) {
         $no_gst_all_groups[] = $gid;

         $no_gst_all_groups = array_merge(
            $no_gst_all_groups,
            CommonHelper::getAllChildGroupIds($gid, Session::get('user_company_id'))
         );
      }
      $cash_group_ids = array_unique($no_gst_all_groups);
      $allowedAccountGroups = DB::table('account_groups')
         ->select('id', 'name')
         ->whereIn('id', $allowed_group_ids)
         ->orderBy('name')
         ->get();
      $party_list = Accounts::with('otherAddress')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code','under_group')
                              ->orderBy('account_name')
                              ->get(); 
                                 
      //Item List
      $item = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->leftjoin('item_gst_rate','item_gst_rate.id','=','manage_items.gst_rate')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.id','manage_items.u_name','manage_items.gst_rate','manage_items.name','parameterized_stock_status','config_status','item_groups.id as group_id'])
            ->get(); 

      $credit_days = DB::table('manage_credit_days')
            ->where('status','1')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('days')
            ->get();
            
         $state_list = DB::table('states') ->orderBy('state_code') ->get();
               foreach($item as $key=>$row){
                  $item_in_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('in_weight');

                  $item_out_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('out_weight');

                  $available_item = $item_in_weight-$item_out_weight;
                  $item[$key]->available_item = $available_item;
               }


         $com_id = Session::get('user_company_id');

         // Item Groups
         $itemGroups = DB::table('item_groups')
            ->where('delete','0')
            ->where('company_id', $com_id)
            ->orderBy('group_name')
            ->get();

         // Units
         $accountunit = DB::table('units')
            ->where('delete', '0')
            ->where('company_id', $com_id)
            ->orderBy('name')
            ->get();

         // ✅ USE ALREADY MERGED GST SETTINGS
         if ($companyData->gst_config_type == "single_gst") {

            $series = DB::table('gst_settings')
               ->where([
                     'company_id' => Session::get('user_company_id'),
                     'gst_type' => "single_gst"
               ])
               ->select('id', 'series')
               ->get();

            $branch = GstBranch::select('branch_series as series')
               ->where([
                     'delete' => '0',
                     'company_id' => Session::get('user_company_id'),
                     'gst_setting_id' => $series[0]->id ?? null
               ])
               ->get();

            if ($branch->count()) {
               $series = $series->merge($branch);
            }

         } else {

            $series = DB::table('gst_settings_multiple')
               ->select('id', 'series')
               ->where([
                     'company_id' => Session::get('user_company_id'),
                     'gst_type' => "multiple_gst"
               ])
               ->get();

            foreach ($series as $value) {
               $branch = GstBranch::select('branch_series as series')
                     ->where([
                        'delete' => '0',
                        'company_id' => Session::get('user_company_id'),
                        'gst_setting_multiple_id' => $value->id
                     ])
                     ->get();

               if ($branch->count()) {
                     $series = $series->merge($branch);
               }
            }
         }

      //Check Production Module Permission

      $comp = Companies::select('user_id')->where('id',Session::get('user_company_id'))->first();
      $production_module_status = MerchantModuleMapping::where('module_id',4)->where('merchant_id',$comp->user_id)->where('company_id', Session()->get('user_company_id'))->first();
      $production_module_status = $production_module_status ? 1 : 0;

      //Sale Order Other Address
      $bill_to_address_id = "";
      $shipp_to_address_id = "";
      $shipp_to_other_address = "";
      $shipp_to_other_pincode = "";
      if(!empty($sale_order_id)){
         $saleOrderData = SaleOrder::find($sale_order_id);
         if($saleOrderData->bill_to_address_id){
            $bill_to_address_id = $saleOrderData->bill_to_address_id;
         }
         if($saleOrderData->shipp_to_address_id){
            $shipp_add = SaleOrder::with('shippToOtherAddress:id,address,pincode')->select('shipp_to_address_id')->find($sale_order_id);
            if($shipp_add){
               $shipp_to_other_address = $shipp_add->shippToOtherAddress->address;
               $shipp_to_other_pincode = $shipp_add->shippToOtherAddress->pincode;
            }
            $shipp_to_address_id = $saleOrderData->shipp_to_address_id;
         }
      }
    if(Session::get('user_company_id')==37 || Session::get('user_company_id')==1){
      return view('addsaleretail')->with('fy_start_date', $fy_start_date)->with('config', $config)->with('itemGroups', $itemGroups)->with('accountunit', $accountunit)->with('series', $series)->with('state_list', $state_list)->with('allowedAccountGroups', $allowedAccountGroups)->with('credit_days', $credit_days)->with('fy_end_date', $fy_end_date)->with('party_list', $party_list)->with('billsundry', $billsundry)->with('bill_date', $bill_date)->with('GstSettings', $GstSettings)->with('item', $item)->with('bill_to_id', $bill_to_id)->with('shipp_to_id', $shipp_to_id)->with('freight', $freight)->with('sale_order_id', $sale_order_id)->with('sale_order_items',$sale_order_items)->with('sale_enter_data',$sale_enter_data)->with('new_order',$new_order)->with('production_module_status',$production_module_status)->with('bill_to_address_id',$bill_to_address_id)->with('shipp_to_address_id',$shipp_to_address_id)->with('shipp_to_other_address',$shipp_to_other_address)->with('shipp_to_other_pincode',$shipp_to_other_pincode)->with('cash_group_ids',$cash_group_ids);
      }
      else{return view('addSale')->with('fy_start_date', $fy_start_date)->with('config', $config)->with('itemGroups', $itemGroups)->with('accountunit', $accountunit)->with('series', $series)->with('state_list', $state_list)->with('allowedAccountGroups', $allowedAccountGroups)->with('credit_days', $credit_days)->with('fy_end_date', $fy_end_date)->with('party_list', $party_list)->with('billsundry', $billsundry)->with('bill_date', $bill_date)->with('GstSettings', $GstSettings)->with('item', $item)->with('bill_to_id', $bill_to_id)->with('shipp_to_id', $shipp_to_id)->with('freight', $freight)->with('sale_order_id', $sale_order_id)->with('sale_order_items',$sale_order_items)->with('sale_enter_data',$sale_enter_data)->with('new_order',$new_order)->with('production_module_status',$production_module_status)->with('bill_to_address_id',$bill_to_address_id)->with('shipp_to_address_id',$shipp_to_address_id)->with('shipp_to_other_address',$shipp_to_other_address)->with('shipp_to_other_pincode',$shipp_to_other_pincode)->with('cash_group_ids',$cash_group_ids);
    }
   }
   
   public function store(Request $request){
      // echo "<pre>";
      // print_r($request->all());
      // die;
      Gate::authorize('action-module',85);
      $validated = $request->validate([
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party_id' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ]); 

    //   echo "<pre>";
    //   print_r($request->all());
    //   $sale_enter_data = json_decode($request->sale_enter_data,true);
    //   $grouped = [];
    //   foreach ($sale_enter_data as $item) {
    //       $key = $item['detail_row_id'];
    //       $grouped[$key][] = $item;
    //   }
    //   print_r($grouped);
      

    //   die;
      if($request->sale_order_id!=""){
         $sale_enter_data = $request->sale_enter_data;
         $merged = array_unique(
            array_merge(...array_column(json_decode($sale_enter_data,true), 'reel_weight_id'))
         );
         $check_size_status = ItemSizeStock::select('id')->whereIn('id',$merged)->where('status',0)->get();
         if(count($check_size_status)>0){      
            return $this->failedMessage('Please Check Selected Weight Stock!','sale-order-start/'.$request->sale_order_id);
         }
      }
      //Check Item Empty or not
      if($request->input('goods_discription')[0]=="" || $request->input('qty')[0]=="" || $request->input('price')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','sale/create');
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      //Check Dulicate Invoice Number
      $check_invoice = Sales::where('company_id',Session::get('user_company_id'))
                              ->where('voucher_no',$request->input('voucher_no'))
                              ->where('series_no',$request->input('series_no'))
                              ->where('financial_year','=',$financial_year)
                              ->where('delete','0')
                              ->first();
      if($check_invoice){
         return $this->failedMessage('Duplicate Invoice No.','sale/create');
      }
      $account = Accounts::where('id',$request->input('party_id'))->first();
      if($request->input('manual_enter_invoice_no')=='0'){
         $voucher_no = Sales::select('voucher_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('series_no',$request->input('series_no'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
         $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$request->input('series_no'))
               ->where('configuration_for','SALE')
               ->where('status','1')
               ->first();
         if(!$voucher_no){
               if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                     $voucher_no =  sprintf("%'03d",$series_configuration->invoice_start);
                  }else{
                     $voucher_no =  $series_configuration->invoice_start;
                  }
               }else{
                  $voucher_no = "1";
               }
         }else{
            $voucher_no++;
            if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != ""){
               $voucher_no = sprintf("%'03d", $voucher_no);
            }
         }
      }else{
         $voucher_no = $request->input('voucher_no');
      }

      $billing_address = $account->address;
      $billing_pincode = $account->pin_code;
      if($request->input('address') && !empty($request->input('address'))){
         $add = AccountOtherAddress::find($request->input('address'));
         $billing_address = $add->address.",".$add->pincode;
         $billing_pincode = $add->pincode;            
      } 
      $sale = new Sales;
      $sale->series_no = $request->input('series_no');
      $sale->po_no = $request->po_no;
      $sale->po_date = $request->po_date;
      $sale->company_id = Session::get('user_company_id');
      $sale->date = $request->input('date');
      $voucher_prefix = $request->input('voucher_prefix');
      $sale->voucher_no_prefix = $voucher_prefix;
      $sale->voucher_no = $voucher_no;
      $sale->party = $request->input('party_id');
      $sale->material_center = $request->input('material_center');
      $sale->address_id = $request->input('address');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->self_vehicle = $request->input('self_vehicle');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->merchant_gst = $request->input('merchant_gst');
      $sale->transport_name = $request->input('transport_name');
      $sale->reverse_charge = $request->input('reverse_charge');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->station = $request->input('station');
      $sale->ewaybill_no = $request->input('ewaybill_no');
      $sale->billing_name = $account->print_name;
      $sale->billing_address = $billing_address;
      $sale->billing_pincode = $billing_pincode;
      $sale->billing_gst = $account->gstin;
      $sale->billing_pan = $account->pan;
      $sale->billing_state = $account->state;
      $sale->shipping_name = $request->input('shipping_name');
      $sale->shipping_state = $request->input('shipping_state');
      $sale->shipping_address = $request->input('shipping_address');
      $sale->shipping_pincode = $request->input('shipping_pincode');
      $sale->shipping_gst = $request->input('shipping_gst'); 
      $sale->shipping_pan = $request->input('shipping_pan');
      $sale->financial_year = $financial_year;
      $sale->created_by = Session::get('user_id');
      
      $sale->narration = $request->input('narration'); 
      $roundoff = $request->input('total')-$request->input('taxable_amt');
      $sale->save();
      if($sale->id){
         $goods_discriptions = $request->input('goods_discription');
         $item_descriptions = $request->input('item_description');
         $description_lines = $request->input('description_lines'); // NEW
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         $item_parameters = $request->input('item_parameters');
         $desc_id_arr = [];$item_quantity_total = 0;
         $pricewithgst = $request->input('pricewithgst');
         $profit = $request->input('profit');
         if(Session::get('user_company_id')==1){
           //  dd($profit);
         }
         foreach($goods_discriptions as $key => $good){
            if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            $item_quantity_total = $item_quantity_total + $qtys[$key];
            $desc = new SaleDescription;
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $good;
            $desc->item_description = $item_descriptions[$key] ?? null;
            $desc->qty = $qtys[$key];
            $desc->unit = $units[$key];
            $desc->pricewithgst = $pricewithgst[$key] ?? 0;
            $desc->profit = $profit[$key] ?? 0;
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->company_id = Session::get('user_company_id');
            $desc->status = '1';
            $desc->save();
            array_push($desc_id_arr,$desc->id);
            //Item Description Lines
            if (isset($description_lines[$key]) && is_array($description_lines[$key])) {
               foreach ($description_lines[$key] as $lineIndex => $lineText) {
                  if (!empty($lineText)) {
                        DB::table('sale_description_lines')->insert([
                           'sale_id' => $sale->id,
                           'sale_description_id' => $desc->id,
                           'line_text' => $lineText,
                           'sort_order' => $lineIndex + 1,
                           'company_id' => Session::get('user_company_id'),
                           'created_at' => now(),
                           'updated_at' => now(),
                        ]);
                  }
               }
            }
            //ADD ITEM LEDGER
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $good;
            $item_ledger->out_weight = $qtys[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $prices[$key];
            $item_ledger->total_price = $amounts[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 1;
            $item_ledger->source_id = $sale->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            $ids = [];
            if(isset($request->input('item_size_info')[$key])){
               $item_size_info_raw = $request->input('item_size_info')[$key];
               $item_size_info = json_decode($item_size_info_raw, true); 
               if (is_array($item_size_info)) {
                  foreach ($item_size_info as $obj) {
                     if (isset($obj['id'])) {
                           $ids[] = $obj['id']; // extract just ID
                     }
                  }
               }
            }
            

            if (count($ids) > 0) {
               ItemSizeStock::whereIn('id', $ids)
                  ->update([
                        'status' => 0,
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id
                  ]);
            }

            //Parameter Info
            if($item_parameters[$key]!=""){
               $parameter = json_decode($item_parameters[$key],true);
               if(count($parameter)>0){                  
                  ItemParameterStock::whereIn('id',$parameter)->update(['status'=>0,'stock_out_id'=>$sale->id,'stock_out_type'=>'SALE']);
                  SaleDescription::where('id',$desc->id)->update(['parameter_ids'=>$item_parameters[$key]]);
               }
            }
         }
         $bill_sundrys = $request->input('bill_sundry');
         $tax_amts = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');
         foreach($bill_sundrys as $key => $bill){
            if($bill_sundry_amounts[$key]=="" || $bill==""){
               continue;
            }
            $sundry = new SaleSundry;
            $sundry->sale_id = $sale->id;
            $sundry->bill_sundry = $bill;
            $sundry->rate = $tax_amts[$key];
            $sundry->amount = $bill_sundry_amounts[$key];
            $sundry->company_id = Session::get('user_company_id');
            $sundry->status = '1';
            $sundry->save();
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();

            if($billsundry->adjust_sale_amt=='No'){
               $ledger = new AccountLedger();
               $ledger->account_id = $billsundry->sale_amt_account;
               if($billsundry->bill_sundry_type=='subtractive'){
                  $ledger->debit = $bill_sundry_amounts[$key];
               }else{
                  $ledger->credit = $bill_sundry_amounts[$key];
               }              
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 1;
               $ledger->entry_type_id = $sale->id;
               $ledger->map_account_id = $request->input('party_id');
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $roundoff = $roundoff - $bill_sundry_amounts[$key];
            }
         }
         //Average Calculation
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $sale_item_array = [];
         foreach($goods_discriptions as $key => $good){
            if($good=="" || $qtys[$key]==""){
               continue;
            }
            if(array_key_exists($good,$sale_item_array)){
               $sale_item_array[$good] = $sale_item_array[$good] + $qtys[$key];
            }else{
               $sale_item_array[$good] = $qtys[$key];
            }     
         }
         foreach ($sale_item_array as $key => $value) {
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $key;
            $average_detail->type = 'SALE';
            $average_detail->sale_id = $sale->id;
            $average_detail->sale_weight = $value;
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$key,$request->input('series_no')); 
            
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party_id');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = 35;//Sales Account
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $SaleLgr=0;
         if($sale->id){
            $goods_discriptions = $request->input('goods_discription');
            $qtys = $request->input('qty');
            $units = $request->input('units');
            $prices = $request->input('price');
            $amounts = $request->input('amount');
            $item_parameters = $request->input('item_parameters');
            foreach($goods_discriptions as $key => $good){
               if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
                  continue;
               }
               $SaleLgr += $amounts[$key];
               
            }
         }               
         $bill_sundrys = $request->input('bill_sundry');
         $tax_amts = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');
         foreach($bill_sundrys as $key => $bill){
            if($bill_sundry_amounts[$key]=="" || $bill==""){
               continue;
            }            
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();
            if($billsundry->adjust_sale_amt=='Yes'){
               if( $billsundry->bill_sundry_type=="additive"){
                  $SaleLgr += $bill_sundry_amounts[$key];
               }else if( $billsundry->bill_sundry_type=="subtractive"){
                  $SaleLgr -= $bill_sundry_amounts[$key];
               }
            }
         }
         $ledger = new AccountLedger();
         $ledger->account_id = 35;//Sales Account
         $ledger->credit = $SaleLgr;
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party_id');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();

         //Update Sale Order Id Code ...................
         if($request->sale_order_id!=""){
            Sales::where('id',$sale->id)->update(['sale_order_id'=>$request->sale_order_id]);
            $saleOrder = SaleOrder::with('items.gsms.details')
                                 ->where('id', $request->sale_order_id)
                                 ->first();
            if ($saleOrder) {
               // Update sale order
               $saleOrder->update(['status' => 1]);
               // Update items
               foreach ($saleOrder->items as $item) {
                  $item->update(['status' => 1]);
                  // Update GSMs
                  foreach ($item->gsms as $gsm) {
                        $gsm->update(['status' => 1]);
                        // Update GSM details
                        foreach ($gsm->details as $detail) {
                           $detail->update(['status' => 1]);
                        }
                  }
               }
            }
            $sale_enter_data = json_decode($request->sale_enter_data,true);
            $grouped = [];
            foreach ($sale_enter_data as $item) {
               $key = $item['detail_row_id'];
               $grouped[$key][] = $item;
            }
            $new_order_arr = [];
            //print_r($desc_id_arr);die;
            $group_index = 0;$group_index_arr = [];$max_groups = count($desc_id_arr);
            foreach($grouped as $k=>$val){
               $enter_qty = 0;
               foreach($val as $k1=>$val1){
                  if(!empty($val1['enter_qty'])){
                     // Assign group index only once per unique index
                     if (!isset($group_index_arr[$val1['index']])) {
                        $group_index_arr[$val1['index']] = $group_index;
                        $group_index++;
                     }

                     $current_group_index = $group_index_arr[$val1['index']];
                     if($val1['unit_type']=="REEL"){
                        $enter_qty = $enter_qty + $val1['enter_qty'];
                     }else if($val1['unit_type']=="KG"){
                        $enter_qty = $enter_qty + array_sum($val1['reel_weight_arr']);
                     }                        
                     foreach($val1['reel_weight_arr'] as $k3=>$val2){
                        $sale_order_item_weight = new SaleOrderItemWeight;
                        $sale_order_item_weight->sale_order_id = $request->sale_order_id;
                        $sale_order_item_weight->sale_order_item_row_id = $val1['detail_row_id'];
                        $sale_order_item_weight->weight = $val2;
                        $sale_order_item_weight->weight_id = $val1['reel_weight_id'][$k3];
                        $sale_order_item_weight->company_id = Session::get('user_company_id');
                        $sale_order_item_weight->created_at = Carbon::now();
                        $sale_order_item_weight->save();
                        if($val1['reel_weight_id'][$k3]){
                            ItemSizeStock::where('id',$val1['reel_weight_id'][$k3])->update(['status'=>0,'sale_order_id'=>$request->sale_order_id,'sale_id'=>$sale->id,"sale_description_id"=>$desc_id_arr[$current_group_index]]);
                        }
                        
                     }                       
                  }
               }
               $sale_order_gsm_size = SaleOrderItemGsmSize::find($k);
               $sale_order_gsm_size->sale_order_qty = $enter_qty;
               $sale_order_gsm_size->update();
               $remaining_qty = $sale_order_gsm_size->quantity - $enter_qty;
               if($remaining_qty>0){
                  array_push($new_order_arr,array("id"=>$k,"sale_order_item_id"=>$sale_order_gsm_size->sale_order_item_id,"sale_order_item_gsm_id"=>$sale_order_gsm_size->sale_order_item_gsm_id,"quantity"=>$remaining_qty));
               }
               //$group_index++;
            }
            if($request->new_order==1){
               if(count($new_order_arr)>0){
                  $sale_order = SaleOrder::find($request->sale_order_id);
                   
                  if (preg_match('/-(\d+)$/', $sale_order->sale_order_no, $matches)) {
                     // If found, increment the number
                     $nextNumber = $matches[1] + 1;
                     // Replace the old suffix with the new one
                     $new_sale_order_no = preg_replace('/-\d+$/', '-' . $nextNumber, $sale_order->sale_order_no);
                  } else {
                     // If no suffix found, start with -1
                     $new_sale_order_no = $sale_order->sale_order_no . '-1';
                  }
                  
                  $new_sale_order = new SaleOrder;
                  $new_sale_order->sale_order_no = $new_sale_order_no;
                  $new_sale_order->purchase_order_no = $sale_order->purchase_order_no;
                  $new_sale_order->purchase_order_date = $sale_order->purchase_order_date;
                  $new_sale_order->bill_to = $sale_order->bill_to;
                  $new_sale_order->shipp_to = $sale_order->shipp_to;
                  $new_sale_order->freight = $sale_order->freight;
                  $new_sale_order->parent_order_no = $sale_order->sale_order_no;
                  $new_sale_order->company_id = Session::get('user_company_id');
                  $new_sale_order->created_by = auth()->id();
                  $new_sale_order->created_at = Carbon::now();
                  if($new_sale_order->save()){
                     $item_check_arr = [];$gsm_check_arr = [];
                     foreach($new_order_arr as $nk=>$nval){
                        if(isset($item_check_arr[$nval['sale_order_item_id']]) && $item_check_arr[$nval['sale_order_item_id']]!=""){
                           $new_sale_order_item_id = $item_check_arr[$nval['sale_order_item_id']];
                        }else{
                           $sale_order_item = SaleOrderItem::find($nval['sale_order_item_id']);
                           $new_sale_order_item = new SaleOrderItem;
                           $new_sale_order_item->sale_order_id = $new_sale_order->id;
                           $new_sale_order_item->item_id = $sale_order_item->item_id;
                           $new_sale_order_item->price = $sale_order_item->price;
                           $new_sale_order_item->bill_price = $sale_order_item->bill_price;
                           $new_sale_order_item->unit = $sale_order_item->unit;
                           $new_sale_order_item->sub_unit = $sale_order_item->sub_unit;
                           $new_sale_order_item->company_id = Session::get('user_company_id');
                           $new_sale_order_item->created_at = Carbon::now();
                           $new_sale_order_item->save();
                           $item_check_arr[$nval['sale_order_item_id']] = $new_sale_order_item->id;
                           $new_sale_order_item_id = $new_sale_order_item->id;
                        }                  
                        if($new_sale_order_item_id){
                           if(isset($gsm_check_arr[$nval['sale_order_item_gsm_id']]) && $gsm_check_arr[$nval['sale_order_item_gsm_id']]!=""){
                              $new_sale_order_item_gsm_id = $gsm_check_arr[$nval['sale_order_item_gsm_id']];
                           }else{
                              $sale_order_item_gsm = SaleOrderItemGSM::find($nval['sale_order_item_gsm_id']);
                              $new_sale_order_item_gsm = new SaleOrderItemGSM;
                              $new_sale_order_item_gsm->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm->sale_order_item_id = $new_sale_order_item_id;
                              $new_sale_order_item_gsm->gsm = $sale_order_item_gsm->gsm;
                              $new_sale_order_item_gsm->company_id = Session::get('user_company_id');
                              $new_sale_order_item_gsm->created_at = Carbon::now();
                              $new_sale_order_item_gsm->save();
                              $gsm_check_arr[$nval['sale_order_item_gsm_id']] = $new_sale_order_item_gsm->id;
                              $new_sale_order_item_gsm_id = $new_sale_order_item_gsm->id;
                           }
                           if($new_sale_order_item_gsm_id){                        
                              $sale_order_item_gsm_size = SaleOrderItemGsmSize::find($nval['id']);
                              $new_sale_order_item_gsm_size = new SaleOrderItemGsmSize;
                              $new_sale_order_item_gsm_size->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm_size->sale_order_item_id = $new_sale_order_item->id;
                              $new_sale_order_item_gsm_size->sale_order_item_gsm_id = $new_sale_order_item_gsm_id;
                              $new_sale_order_item_gsm_size->size = $sale_order_item_gsm_size->size;
                              $new_sale_order_item_gsm_size->quantity = $nval['quantity'];
                              $new_sale_order_item_gsm_size->company_id = Session::get('user_company_id');
                              $new_sale_order_item_gsm_size->created_at = Carbon::now();
                              $new_sale_order_item_gsm_size->save();
                           }
                        }
                     }
                  }
               }
            }
         }
         //Store Vehicle Details
         if($request->input('vehicle_info_type')=="vehicle" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){

            SaleOrder::where('id',$request->sale_order_id)
                     ->update([
                        'freight_type'=>$request->input('vehicle_info_type'),
                        'freight_price'=>$request->input('vehicle_freight'),
                        'freight_vehicle_id'=>$request->input('vehicle_info'),
                        'other_freight_amount'=>''
                     ]);
            $vehicle_info = new SaleVehicleTxn;
            $vehicle_info->sale_id = $sale->id;
            $vehicle_info->sale_order_id = $request->sale_order_id;
            $vehicle_info->vehicle_id = $request->input('vehicle_info');
            $vehicle_info->vehicle_freight_price = $request->input('vehicle_freight');
            $vehicle_info->vehicle_freight_amount = $item_quantity_total * $request->input('vehicle_freight');
            $vehicle_info->company_id = Session::get('user_company_id');
            $vehicle_info->created_at = Carbon::now();
            $vehicle_info->created_by = Session::get('user_id');
            $vehicle_info->save();
         }
         if($request->input('vehicle_info_type')=="to_pay" && $request->sale_order_id!="" ){
            SaleOrder::where('id',$request->sale_order_id)
                     ->update([
                        'freight_type'=>$request->input('vehicle_info_type'),
                        'freight_price'=>$request->input('to_pay_freight'),
                        'other_freight_amount'=>$request->input('to_pay_other_charges')
                     ]);
         }
         if($request->input('vehicle_info_type')=="party_vehicle" && $request->sale_order_id!="" ){
            SaleOrder::where('id',$request->sale_order_id)
                     ->update([
                        'freight_type'=>$request->input('vehicle_info_type'),
                        'freight_price'=>"",
                        'other_freight_amount'=>""
                     ]);
         }
         //Transporter Journal Entry
         if($request->input('vehicle_info_type')=="transporter" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){
            $transporter_total_amount = ($item_quantity_total * $request->input('transporter_freight'))+$request->input('transporter_other_charges');
            $transporter_total_amount = round($transporter_total_amount);
            $location_name = $account->location;
            if(!empty($request->input('shipping_name'))){
               $shipp_account = Accounts::select('location')->find($request->input('shipping_name'));
               $location_name = $shipp_account->location;
            }
            //$financial_year = Session::get('default_fy');
            //Journal Entry For Transporter Voucher No
            $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
                                                               ->where('series', $request->input('series_no'))
                                                               ->where('configuration_for', 'JOURNAL') 
                                                               ->where('status', '1')
                                                               ->first();
            $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
            $lastNumber = DB::table('journals')
                              ->where('company_id', Session::get('user_company_id'))
                              ->where('financial_year', $financial_year)
                              ->where('series_no', $request->input('series_no'))
                              ->where('delete', '0')
                              ->max(DB::raw("cast(voucher_no as SIGNED)"));
            if (!$lastNumber) {
               if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
                  $journal_voucher_no = (int)$series_configuration->invoice_start;
               } else {
                  $journal_voucher_no = 1;
               }
            } else {
               $journal_voucher_no = ((int)$lastNumber) + 1;
            }
            //Voucher Series With Prefix/Suffix
            $journal_invoice_prefix = "";
            if ($series_configuration && $series_configuration->manual_numbering == "NO") {
               if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                  $journal_invoice_prefix .= $series_configuration->prefix_value;
               }
               if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
                  $journal_invoice_prefix .= $series_configuration->separator_1;
               }
               if ($series_configuration->year == "PREFIX TO NUMBER") {
                  if ($series_configuration->year_format == "YY-YY") {
                     $journal_invoice_prefix .= Session::get('default_fy');
                  } else {
                     $fy = explode('-', Session::get('default_fy'));
                     $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                  }
                  if ($series_configuration->separator_2 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_2;
                  }
               }
               $journal_invoice_prefix .= $journal_voucher_no;
               if ($series_configuration->year == "SUFFIX TO NUMBER") {
                  if ($series_configuration->separator_2 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_2;
                  }
                  if ($series_configuration->year_format == "YY-YY") {
                     $journal_invoice_prefix .= Session::get('default_fy');
                  } else {
                     $fy = explode('-', Session::get('default_fy'));
                     $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                  }
               }
               if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
                  $journal_invoice_prefix .= $series_configuration->separator_3;
               }
               if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
                  $journal_invoice_prefix .= $series_configuration->suffix_value;
               }
            }
            $journal_voucher_no = sprintf("%0" . $number_digit . "d", $journal_voucher_no);
            if($journal_invoice_prefix==""){
               $journal_invoice_prefix = $journal_voucher_no;
            }
            $journal = new Journal;
            $journal->date = $request->input('date');
            $journal->voucher_no = $journal_voucher_no;
            $journal->voucher_no_prefix = $journal_invoice_prefix;
            $journal->series_no = $request->input('series_no');
            $journal->long_narration = "Bill No : ".$voucher_prefix.", Vehicle No. : ".$request->input('vehicle_no').", Location : ".$location_name.", GR/PR No. : ".$request->input('gr_pr_no');
            $journal->company_id = Session::get('user_company_id');
            $journal->financial_year = $financial_year;
            $journal->claim_gst_status = 'NO';
            $journal->merchant_gst = $request->input('merchant_gst');
            if($journal->save()){
               SaleOrder::where('id',$request->sale_order_id)
                     ->update([
                        'freight_type'=>$request->input('vehicle_info_type'),
                        'freight_price'=>$request->input('transporter_freight'),
                        'freight_transporter_id'=>$request->input('vehicle_info'),
                        'other_freight_amount'=>$request->input('transporter_other_charges')
                     ]);
               Sales::where('id',$sale->id)->update(['transporter_journal_id'=>$journal->id]);
               //Add Transpoeter Account Credit
               $expense = DB::table('sale-order-settings')
                                 ->where('setting_type','EXPENSE_ACCOUNT')
                                 ->where('setting_for','SALE ORDER')
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->first();
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');
               $joundetail->type = "Credit";
               $joundetail->account_name = $request->input('vehicle_info');
               $joundetail->debit = '0';
               $joundetail->credit = $transporter_total_amount;            
               $joundetail->narration = "";
               $joundetail->status = '1';
               $joundetail->save();
               //Account Ledger
               $ledger = new AccountLedger();
               $ledger->account_id = $request->input('vehicle_info');               
               $ledger->credit = $transporter_total_amount;
               $ledger->map_account_id = $expense->expense_account_id;
               $ledger->series_no = $request->input('series_no');
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 7;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_narration = "";               
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               //Add Freight Account Debit
               
               $joundetail = new JournalDetails;
               $joundetail->journal_id = $journal->id;
               $joundetail->company_id = Session::get('user_company_id');;
               $joundetail->type = "Debit";
               $joundetail->account_name = $expense->expense_account_id;
               $joundetail->debit = $transporter_total_amount;
               $joundetail->credit = '0';
               $joundetail->narration = "";
               $joundetail->status = '1';
               $joundetail->save();
               //Account Ledger
               $ledger = new AccountLedger();
               $ledger->account_id = $expense->expense_account_id;
               $ledger->debit = $transporter_total_amount;
               $ledger->map_account_id = $request->input('vehicle_info');
               $ledger->series_no = $request->input('series_no');
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 7;
               $ledger->entry_type_id = $journal->id;
               $ledger->entry_narration = "";               
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
                  
            }
         }
         session(['previous_url' => URL::previous()]);
         return redirect('sale-invoice/'.$sale->id.'?source=sale')->withSuccess('Sale voucher added successfully!');
      }else{
         return $this->failedMessage('Something went wrong','sale/create');
         exit();
      }
   }
   public function edit(Request $request,$id){
      Gate::authorize('action-module',61);
      $bill_to_id     = $request->query('bill_to_id');     // 2
      $shipp_to_id = $request->query('shipp_to_id'); // 332
      $freight   = $request->query('freight');   // 1
      $sale_order_id = $request->query('sale_order_id');
      $new_order = $request->query('new_order');
      $sale_order_items = [];
      if($request->query('item_arr')){
         $sale_order_items = json_decode($request->query('item_arr'),true);
      }
      
      $sale_enter_data = [];
      if($request->query('item_arr')){
         $sale_enter_data = $request->query('sale_enter_data');
      }
      $sale = Sales::find($id);
      $SaleDescription = SaleDescription::join('units','sale_descriptions.unit','=','units.id')
                                          ->where('sale_id', $id)
                                          ->select(['sale_descriptions.*','units.s_name'])
                                          ->get();
      $SaleSundry = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','sale_sundries.*'])
                                 ->where('sale_sundries.sale_id', $id)
                                 ->get();
      $top_groups = [3, 11,7,8];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $groups = array_unique($all_groups);
      $allowed_group_ids = array_unique($all_groups);
      $no_gst_groups = [7, 8];
      $no_gst_all_groups = [];
      foreach ($no_gst_groups as $gid) {
         $no_gst_all_groups[] = $gid;

         $no_gst_all_groups = array_merge(
            $no_gst_all_groups,
            CommonHelper::getAllChildGroupIds($gid, Session::get('user_company_id'))
         );
      }
      $cash_group_ids = array_unique($no_gst_all_groups);
      $allowedAccountGroups = DB::table('account_groups')
         ->select('id', 'name')
         ->whereIn('id', $allowed_group_ids)
         ->orderBy('name')
         ->get();
      $party_list = Accounts::with(['otherAddress'])->select('accounts.*','states.state_code')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('accounts.delete', '=', '0')
                              ->where('accounts.status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->orderBy('account_name')
                              ->get();
      $manageitems = DB::table('manage_items')
         ->join('units', 'manage_items.u_name', '=', 'units.id')
         ->leftJoin('item_gst_rate', 'item_gst_rate.id', '=', 'manage_items.gst_rate')
         ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
         ->where('manage_items.delete', '0')
         ->where('manage_items.status', '1')
         ->where('manage_items.company_id', Session::get('user_company_id'))
         ->orderBy('manage_items.name')
         ->select([
            'units.s_name as unit',
            'manage_items.id',
            'manage_items.u_name',
            'manage_items.gst_rate',
            'manage_items.name',
            'item_groups.parameterized_stock_status',
            'item_groups.config_status',
            'item_groups.id as group_id'
         ])
         ->get();


      // Add available stock same as create
      foreach ($manageitems as $key => $row) {

         $item_in_weight = DB::table('item_ledger')
            ->where('status', '1')
            ->where('delete_status', '0')
            ->where('company_id', Session::get('user_company_id'))
            ->where('item_id', $row->id)
            ->sum('in_weight');

         $item_out_weight = DB::table('item_ledger')
            ->where('status', '1')
            ->where('delete_status', '0')
            ->where('company_id', Session::get('user_company_id'))
            ->where('item_id', $row->id)
            ->sum('out_weight');

         $available_item = $item_in_weight - $item_out_weight;
         $manageitems[$key]->available_item = $available_item;
      }

      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->mat_center = array();
      $GstSettings->series = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();

         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_prefix as invoice_prefix')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }else if($companyData->gst_config_type == "multiple_gst"){
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();

         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_prefix')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_prefix as invoice_prefix')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         } 
      }

      
      
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }      
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';       
      foreach ($mat_series as $key => $value) {
         if($sale->series_no==$value->series){
            $mat_series[$key]->invoice_start_from =  $sale->voucher_no;
         }else{
            $voucher_no = Sales::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('series_no','=',$value->series)
                           ->where('delete','=','0')
                           ->max(\DB::raw("cast(voucher_no as SIGNED)"));
            if(!$voucher_no){
               $mat_series[$key]->invoice_start_from =  "001";
            }else{
               $invc = $voucher_no + 1;
               $invc = sprintf("%'03d", $invc);
               $mat_series[$key]->invoice_start_from =  $invc;
            }
         }
      }
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->mat_center)) {
         $mat_center[] = array("branch_matcenter" => $GstSettings->mat_center);
      }
      
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)){
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->orderBy('name')
                                 ->get();
      $comp = Companies::select('user_id')
         ->where('id', Session::get('user_company_id'))
         ->first();

      $production_module_status = MerchantModuleMapping::where('module_id', 4)
         ->where('merchant_id', $comp->user_id)
         ->where('company_id', Session()->get('user_company_id'))
         ->first();

      $production_module_status = $production_module_status ? 1 : 0;


      foreach ($SaleDescription as $desc) {
         $desc->selected_sizes = DB::table('item_size_stocks')
            ->where('sale_id', $sale->id)
            ->where('item_id', $desc->goods_discription)
            ->select('id', 'size', 'weight', 'reel_no')
            ->get();
      }
      foreach ($SaleDescription as $desc) {
         $desc->lines = DB::table('sale_description_lines')
            ->where('sale_description_id', $desc->id)
            ->orderBy('sort_order')
            ->get();
      }
       $credit_days = DB::table('manage_credit_days')
            ->where('status','1')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('days')
            ->get();
            
      $state_list = DB::table('states') ->orderBy('state_code') ->get();
      $com_id = Session::get('user_company_id');
      // Item Groups
      $itemGroups = DB::table('item_groups')
            ->where('delete','0')
            ->where('company_id', $com_id)
            ->orderBy('group_name')
            ->get();

      // Units
      $accountunit = DB::table('units')
         ->where('delete', '0')
         ->where('company_id', $com_id)
         ->orderBy('name')
         ->get();

         // ✅ USE ALREADY MERGED GST SETTINGS
         if ($companyData->gst_config_type == "single_gst") {

            $series = DB::table('gst_settings')
               ->where([
                     'company_id' => Session::get('user_company_id'),
                     'gst_type' => "single_gst"
               ])
               ->select('id', 'series')
               ->get();

            $branch = GstBranch::select('branch_series as series')
               ->where([
                     'delete' => '0',
                     'company_id' => Session::get('user_company_id'),
                     'gst_setting_id' => $series[0]->id ?? null
               ])
               ->get();

            if ($branch->count()) {
               $series = $series->merge($branch);
            }

         } else {

            $series = DB::table('gst_settings_multiple')
               ->select('id', 'series')
               ->where([
                     'company_id' => Session::get('user_company_id'),
                     'gst_type' => "multiple_gst"
               ])
               ->get();

            foreach ($series as $value) {
               $branch = GstBranch::select('branch_series as series')
                     ->where([
                        'delete' => '0',
                        'company_id' => Session::get('user_company_id'),
                        'gst_setting_multiple_id' => $value->id
                     ])
                     ->get();

               if ($branch->count()) {
                     $series = $series->merge($branch);
               }
            }
         }
         $config = SaleInvoiceConfiguration::where('company_id', Session::get('user_company_id'))->first();
         if(Session::get('user_company_id')==37 || Session::get('user_company_id')==1){
              return view('editsaleretail')
            ->with('production_module_status', $production_module_status)
            ->with('fy_start_date', $fy_start_date)
            ->with('fy_end_date', $fy_end_date)
            ->with('party_list', $party_list)
            ->with('manageitems', $manageitems)
            ->with('billsundry', $billsundry)
            ->with('mat_center', $mat_center)
            ->with('GstSettings', $GstSettings)
            ->with('mat_series', $mat_series)
            ->with('sale', $sale)
            ->with('SaleDescription', $SaleDescription)
            ->with('SaleSundry', $SaleSundry)
            ->with('config',$config)
            ->with('itemGroups', $itemGroups)
            ->with('accountunit', $accountunit)
            ->with('series', $series)
            ->with('state_list', $state_list)
            ->with('allowedAccountGroups', $allowedAccountGroups)
            ->with('credit_days', $credit_days)
            ->with('bill_to_id', $bill_to_id)
            ->with('shipp_to_id', $shipp_to_id)
            ->with('freight', $freight)
            ->with('sale_order_id', $sale_order_id)
            ->with('sale_order_items',$sale_order_items)
            ->with('sale_enter_data',$sale_enter_data)
            ->with('new_order',$new_order)
            ->with('cash_group_ids',$cash_group_ids);
         }else{
      return view('editSale')
            ->with('production_module_status', $production_module_status)
            ->with('fy_start_date', $fy_start_date)
            ->with('fy_end_date', $fy_end_date)
            ->with('party_list', $party_list)
            ->with('manageitems', $manageitems)
            ->with('billsundry', $billsundry)
            ->with('mat_center', $mat_center)
            ->with('GstSettings', $GstSettings)
            ->with('mat_series', $mat_series)
            ->with('sale', $sale)
            ->with('SaleDescription', $SaleDescription)
            ->with('SaleSundry', $SaleSundry)
            ->with('config',$config)
            ->with('itemGroups', $itemGroups)
            ->with('accountunit', $accountunit)
            ->with('series', $series)
            ->with('state_list', $state_list)
            ->with('allowedAccountGroups', $allowedAccountGroups)
            ->with('credit_days', $credit_days)
            ->with('bill_to_id', $bill_to_id)
            ->with('shipp_to_id', $shipp_to_id)
            ->with('freight', $freight)
            ->with('sale_order_id', $sale_order_id)
            ->with('sale_order_items',$sale_order_items)
            ->with('sale_enter_data',$sale_enter_data)
            ->with('new_order',$new_order)
            ->with('cash_group_ids',$cash_group_ids);
         }
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function saleInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $items_detail = DB::table('sale_descriptions')->where('sale_id', $id)
            ->select(
               'sale_descriptions.id as sale_description_id',
               'units.s_name as unit',
               'units.id as unit_id',
               'sale_descriptions.qty',
               'sale_descriptions.price',
               'sale_descriptions.amount',
               'manage_items.p_name',
               'manage_items.name',
               'manage_items.id as item_id',
               'sales.*',
               'accounts.*',
               'manage_items.hsn_code',
               'manage_items.gst_rate'
            )
            ->join('units', 'sale_descriptions.unit', '=', 'units.id')
            ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('accounts', 'accounts.id', '=', 'sales.party')
            ->get();
      foreach ($items_detail as $item) {
         $item->lines = DB::table('sale_description_lines')
            ->where('sale_description_id', $item->sale_description_id)
            ->orderBy('sort_order')
            ->get();
      }
      $sale_detail = Sales::leftjoin('states','sales.billing_state','=','states.id')
                           ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
                           ->where('sales.id', $id)
                           ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name','states.eway_limit'])
                           ->first();
      $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
                     ->where('accounts.id', $sale_detail->party)
                     ->select(['accounts.*','states.name as sname'])            
                     ->first();
      $sale_sundry = DB::table('sale_sundries')
                        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('sale_id', $id)
                        ->select('sale_sundries.bill_sundry','sale_sundries.rate','sale_sundries.amount','bill_sundrys.name','nature_of_sundry','bill_sundry_type')
                        ->orderBy('sequence')
                        ->get();
      $gst_detail = DB::table('sale_sundries')
                        ->select('rate','amount')                     
                        ->where('sale_id', $id)
                        ->where('rate','!=','0')
                        ->distinct('rate')                       
                        ->get(); 
      $max_gst = DB::table('sale_sundries')
                        ->select('rate')                     
                        ->where('sale_id', $id)
                        ->where('rate','!=','0')
                        ->max(\DB::raw("cast(rate as SIGNED)"));
      if(count($gst_detail)>0){
         foreach ($gst_detail as $key => $value){
            $rate = $value->rate;      
            if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
               $rate = $rate*2;
               $max_gst = $max_gst*2;
            }
            $taxable_amount = 0;
            foreach($items_detail as $k1 => $item) {
               if($item->gst_rate==$rate){
                  $taxable_amount = $taxable_amount + $item->amount;
               }
            }
            $gst_detail[$key]->rate = $rate;
            if($max_gst==$rate){

               $sun = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                              ->select('amount','bill_sundry_type')
                              ->where('sale_id', $id)
                              ->where('nature_of_sundry','OTHER')
                              ->get();
               foreach ($sun as $k1 => $v1) {
                  if($v1->bill_sundry_type=="additive"){
                     $taxable_amount = $taxable_amount + $v1->amount;
                  }else if($v1->bill_sundry_type=="subtractive"){
                     $taxable_amount = $taxable_amount - $v1->amount;

                  }
               }
               // $freight = SaleSundry::select('amount')
               //             ->where('sale_id', $id)
               //             ->where('bill_sundry',4)
               //             ->first();
               // $insurance = SaleSundry::select('amount')
               //             ->where('sale_id', $id)
               //             ->where('bill_sundry',7)
               //             ->first();
               // $discount = SaleSundry::select('amount')
               //             ->where('sale_id', $id)
               //             ->where('bill_sundry',5)
               //             ->first();
               // if($freight && !empty($freight->amount)){
               //    $taxable_amount = $taxable_amount + $freight->amount;
               // }
               // if($insurance && !empty($insurance->amount)){
               //    $taxable_amount = $taxable_amount + $insurance->amount;
               // }
               // if($discount && !empty($discount->amount)){
               //    $taxable_amount = $taxable_amount - $discount->amount;
               // }
            }
            $gst_detail[$key]->taxable_amount = $taxable_amount;
         }
      }              
      $bank_detail = DB::table('banks')->where('company_id', Session::get('user_company_id'))
            ->select('banks.*')
            ->first();
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => $sale_detail->company_id,'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {         
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst])->first();
         //Seller Info
         $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                          
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                           ->first();
            $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
            $seller_info->sname = $state_info->name;
                          
         }         
      }
      if($GstSettings){
         if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
             $eway_limit = $sale_detail->eway_limit;
             if($eway_limit!=""){
                 if($sale_detail->$eway_limit){
                   $GstSettings->ewaybill = 0;
                }
             }            
         }else{
            if($sale_detail->total<50000){
               $GstSettings->ewaybill = 0;
            }
         }
      }else{
         $GstSettings = (object)NULL;
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
      }
      $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
      // echo "<pre>";
      // print_r($seller_info);die;
       Session::put('redirect_url', '');
    
        // Financial year processing
        $financial_year = Session::get('default_fy');      
        $y = explode("-", $financial_year);
        $from = DateTime::createFromFormat('y', $y[0])->format('Y');
        $to = DateTime::createFromFormat('y', $y[1])->format('Y');
        $month_arr = [
            $from.'-04', $from.'-05', $from.'-06', $from.'-07', $from.'-08', $from.'-09',
            $from.'-10', $from.'-11', $from.'-12', $to.'-01', $to.'-02', $to.'-03'
        ];
         // Fetch sale order data for challan
         $saleOrder = \App\Models\SaleOrder::with([
            'billTo:id,print_name as account_name,gstin,address,pin_code,state,pan',
            'shippTo:id,print_name as account_name,gstin,address,pin_code,state,pan',
            'orderCreatedBy:id,name',
            'items.item:id,name,hsn_code',
            'items.unitMaster:id,s_name',
         ])
         ->where('id', $sale_detail->sale_order_id)
         ->first();
         if ($saleOrder) {
            foreach ($saleOrder->items as $item) {
               $item->itemSize = \DB::table('item_size_stocks')
                     ->where('item_id', $item->item_id)
                     ->where(function ($query) use ($saleOrder, $id) {
                        $query->where('sale_order_id', $saleOrder->id)
                              ->Where('sale_id', $id);
                     })
                     ->select('reel_no', 'size', 'gsm', 'bf', 'weight', 'unit')
                     ->get();
            }
         } else {
            $saleItems = \DB::table('sale_descriptions')
               ->where('sale_id', $id)
               ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
               ->select('manage_items.id as item_id', 'manage_items.name as item_name')
               ->get();

            foreach ($saleItems as $item) {
               $item->itemSize = \DB::table('item_size_stocks')
                     ->where('item_id', $item->item_id)
                     ->where('sale_id', $id)
                     ->select('reel_no', 'size', 'gsm', 'bf', 'weight', 'unit')
                     ->get();
            }
         }
        $eway_bill_distance = "";
        $e_waybill_distance = Sales::select('e_waybill_distance')
                                    ->where('merchant_gst',$sale_detail->merchant_gst)
                                    ->where('billing_gst',$sale_detail->billing_gst)
                                    ->where('e_waybill_distance','!=','')
                                    ->first();
        if($e_waybill_distance){
            $eway_bill_distance = $e_waybill_distance->e_waybill_distance;
        }
        //Check Production Module Permission

          $comp = Companies::select('user_id')->where('id',Session::get('user_company_id'))->first();
          $production_module_status = MerchantModuleMapping::where('module_id',4)->where('merchant_id',$comp->user_id)->where('company_id', Session()->get('user_company_id'))->first();
          $production_module_status = $production_module_status ? 1 : 0;
        return view('saleInvoice')->with(['production_module_status'=>$production_module_status,'items_detail' => $items_detail, 'sale_sundry' => $sale_sundry, 'party_detail' => $party_detail,'month_arr' => $month_arr, 'company_data' => $company_data, 'sale_detail' => $sale_detail,'bank_detail' => $bank_detail,'gst_detail'=>$gst_detail,'einvoice_status'=>$GstSettings->einvoice,'ewaybill_status'=>$GstSettings->ewaybill,'configuration'=>$configuration,'seller_info'=>$seller_info,'saleOrder' => $saleOrder,"eway_bill_distance"=>$eway_bill_distance]);
    }
   public function delete(Request $request){
      Gate::authorize('action-module',62);
      $check_entry_in_cn_dn = DB::table('sales')
                  ->select(
                        DB::raw('(select count(*) from sales_returns where sales_returns.sale_bill_id = sales.id and voucher_type="SALE" and status="1" and sales_returns.delete="0")  as sale_return_count'),
                        DB::raw('(select count(*) from purchase_returns where purchase_returns.purchase_bill_id = sales.id and voucher_type="SALE" and status="1" and purchase_returns.delete="0")  as purchase_return_count')
                  )
                  ->where('id',$request->sale_id)
                  ->first();
      if($check_entry_in_cn_dn){
         if($check_entry_in_cn_dn->sale_return_count>0 || $check_entry_in_cn_dn->purchase_return_count>0){
            return back()->with('error', '❌ Action not allowed. Please delete or cancel the related Debit Note or Credit Note first.');
         }
      }
      $sale =  Sales::find($request->sale_id);
      $oldSnapshot = [
         'sale' => $sale->toArray(),

         'items' => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),

         'sundries' => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),

         'item_ledgers' => ItemLedger::where('source', 1)
            ->where('source_id', $sale->id)
            ->get()->toArray(),

         'account_ledgers' => AccountLedger::where('entry_type', 1)
            ->where('entry_type_id', $sale->id)
            ->get()->toArray(),

         'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)
            ->where('type', 'SALE')
            ->get()->toArray(),
      ];
      $sale->delete = '1';
      $sale->deleted_at = Carbon::now();
      $sale->deleted_by = Session::get('user_id');
      $sale->update();
      if($sale) {
         ItemAverageDetail::where('sale_id',$request->sale_id)
                           ->where('type','SALE')
                           ->delete();         
         $desc = SaleDescription::where('sale_id',$request->sale_id)
                              ->get();
         foreach ($desc as $key => $value) {
            CommonHelper::RewriteItemAverageByItem($sale->date,$value->goods_discription,$sale->series_no);
         }
         SaleDescription::where('sale_id',$request->sale_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',1)
                        ->where('entry_type_id',$request->sale_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         SaleSundry::where('sale_id',$request->sale_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         ItemLedger::where('source',1)
                     ->where('source_id',$request->sale_id)
                     ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);

         ItemParameterStock::where('stock_out_id',$request->sale_id)
                           ->where('stock_out_type','SALE')
                           ->where('status',0)
                           ->update(['status'=>1,'stock_out_id'=>null]);


         SaleVehicleTxn::where('sale_id',$request->sale_id)->delete();
         if($sale->transporter_journal_id){
            JournalDetails::where('journal_id',$sale->transporter_journal_id)->delete();
            Journal::where('id',$sale->transporter_journal_id)->delete();
            AccountLedger::where('entry_type',7)->where('entry_type_id',$sale->transporter_journal_id)->delete();

         }
         //Sale Order/Production Code
         if(!empty($sale->sale_order_id)){
            $saleOrder = SaleOrder::with('items.gsms.details')
                                 ->where('id', $sale->sale_order_id)
                                 ->first();
            if ($saleOrder) {
               // Update sale order
               $saleOrder->update(['status' => 0,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
               // Update items
               foreach ($saleOrder->items as $item) {
                  $item->update(['status' => 0]);
                  // Update GSMs
                  foreach ($item->gsms as $gsm) {
                     $gsm->update(['status' => 0]);
                     // Update GSM details
                     foreach ($gsm->details as $detail) {
                        $detail->update(['status' => 0]);
                     }
                  }
               }
               $item_stock_id = SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->pluck('weight_id')->toArray();
               ItemSizeStock::whereIn('id',$item_stock_id)->update(["status"=>1,'sale_order_id'=>"",'sale_id'=>"",'sale_description_id'=>""]);
               SaleOrderItemGsmSize::where("sale_orders_id",$sale->sale_order_id)->update(["sale_order_qty"=>""]);
               SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->delete();
               Sales::where('id',$request->sale_id)->update(["sale_order_id"=>""]);
            }
         }
         ItemSizeStock::where('sale_id', $request->sale_id)->update([
            'status' => 1,
            'sale_id' => null,
            'sale_description_id' => null
         ]);   
         ActivityLog::create([
            'module_type' => 'sale',
            'module_id'   => $sale->id,
            'action'      => 'delete',
            'old_data'    => $oldSnapshot,
            'new_data'    => null,
            'action_by'   => Session::get('user_id'),
            'company_id'  => Session::get('user_company_id'),
            'action_at'   => now(),
         ]);            
         return redirect('sale')->withSuccess('Sale deleted successfully!');
      }
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function update(Request $request){
      Gate::authorize('action-module',61);
      $validated = $request->validate([
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ]); 
      //Check Item Empty or not
      if($request->input('goods_discription')[0]==""){
         return $this->failedMessage('Plases Select Item','sale/create');
      }
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));

         $sale = Sales::find($request->input('sale_edit_id'));
      //Check Dulicate Invoice Number
     
      // echo "<pre>";
      // print_r($request->all());
      // $sale_enter_data = json_decode($request->sale_enter_data,true);
      //       $grouped = [];
      //       foreach ($sale_enter_data as $item) {
      //          $key = $item['detail_row_id'];
      //          $grouped[$key][] = $item;
      //       }
      //       print_r($grouped);
      //die;

      $account = Accounts::where('id',$request->input('party'))->first();
         
     
      $oldSnapshot = [
         'sale' => $sale->toArray(),

         'items' => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),

         'sundries' => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),

         'item_ledgers' => ItemLedger::where('source', 1)
            ->where('source_id', $sale->id)
            ->get()->toArray(),

         'account_ledgers' => AccountLedger::where('entry_type', 1)
            ->where('entry_type_id', $sale->id)
            ->get()->toArray(),

         'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)
            ->where('type', 'SALE')
            ->get()->toArray(),
      ];
      $last_date = $sale->date; 
      //If Same Series Edit
      $sale->series_no = $request->input('series_no');
      $sale->date = $request->input('date');
      $voucher_prefix = "";
      if(!empty($request->input('voucher_prefix'))){
         $voucher_prefix_arr = explode("/",$request->input('voucher_prefix'));
         if(count($voucher_prefix_arr)>1){
            $voucher_prefix = $voucher_prefix_arr[0]."/".$voucher_prefix_arr[1]."/";
         }else if(count($voucher_prefix_arr)==1){
            $voucher_prefix = "";
         }
      }
      $billing_address = $account->address;
      $billing_pincode = $account->pin_code;
      if($request->input('address') && !empty($request->input('address'))){
         $add = AccountOtherAddress::find($request->input('address'));
         $billing_address = $add->address.",".$add->pincode;
         $billing_pincode = $add->pincode;
      } 
      $sale->party = $request->input('party');
      $sale->material_center = $request->input('material_center');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->self_vehicle = $request->input('self_vehicle');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->address_id = $request->input('address');
      $sale->ewaybill_no = $request->input('ewaybill_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->reverse_charge = $request->input('reverse_charge');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->station = $request->input('station');
      $sale->billing_name = $account->print_name;
      $sale->billing_address = $billing_address;
      $sale->billing_pincode = $billing_pincode;
      $sale->billing_gst = $account->gstin;
      $sale->billing_pan = $account->pan;
      $sale->billing_state = $account->state;
      $sale->shipping_name = $request->input('shipping_name');
      $sale->shipping_state = $request->input('shipping_state');
      $sale->shipping_address = $request->input('shipping_address');
      $sale->shipping_pincode = $request->input('shipping_pincode');
      $sale->shipping_gst = $request->input('shipping_gst');
      $sale->shipping_pan = $request->input('shipping_pan');
      $sale->financial_year = $financial_year;
      $sale->updated_by = Session::get('user_id');
      $sale->narration = $request->input('narration');
      $sale->po_no = $request->input('po_no');
      $sale->po_date = $request->input('po_date');
      $sale->save();
      if ($sale->id) {
         $goods_discriptions = $request->input('goods_discription');
         $item_descriptions = $request->input('item_description');
         $description_lines = $request->input('description_lines');
         $qtys   = $request->input('qty');
         $units  = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         DB::table('sale_description_lines')
            ->where('sale_id', $sale->id)
            ->delete();
         $desc_item_arr = SaleDescription::where('sale_id',$sale->id)->pluck('goods_discription')->toArray();
         $old_size_ids = ItemSizeStock::where('sale_id', $sale->id)
                              ->pluck('id')
                              ->toArray();
         SaleDescription::where('sale_id', $sale->id)->delete();
         ItemLedger::where('source_id', $sale->id)->where('source', 1)->delete();
         ItemAverageDetail::where('sale_id', $sale->id)
                           ->where('type', 'SALE')
                           ->delete();
         $new_size_ids = [];$desc_id_arr = [];$item_quantity_total = 0;
         $pricewithgst = $request->input('pricewithgst');
         $profit = $request->input('profit');
         foreach ($goods_discriptions as $key => $good) {
            if ($good=="" || $qtys[$key]=="" || $units[$key]=="" || 
               $prices[$key]=="" || $amounts[$key]=="") {
               continue;
            }
            $item_quantity_total = $item_quantity_total + $qtys[$key];
            $desc = new SaleDescription;
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $good;
            $desc->item_description = $item_descriptions[$key] ?? '';
            $desc->qty = $qtys[$key];
            $desc->unit = $units[$key];
            $desc->pricewithgst = $pricewithgst[$key] ?? 0;
            $desc->profit = $profit[$key] ?? 0;
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->company_id = Session::get('user_company_id');
            $desc->status = '1';
            $desc->save();
            array_push($desc_id_arr,$desc->id);
            // Description lines
             if (isset($description_lines[$key]) && is_array($description_lines[$key])) {
               foreach ($description_lines[$key] as $lineIndex => $lineText) {
                  if (!empty($lineText)) {
                     DB::table('sale_description_lines')->insert([
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id,
                        'line_text' => $lineText,
                        'sort_order' => $lineIndex + 1,
                        'company_id' => Session::get('user_company_id'),
                        'created_at' => now(),
                        'updated_at' => now(),
                     ]);
                  }
               }
            }
            // Item ledger
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $good;
            $item_ledger->out_weight = $qtys[$key];
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $prices[$key];
            $item_ledger->total_price = $amounts[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 1;
            $item_ledger->source_id = $sale->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();

            if(isset($request->input('item_size_info')[$key])){
               $item_size_info_raw = $request->input('item_size_info')[$key] ?? "[]";
               $sizes = json_decode($item_size_info_raw, true);
               if (is_array($sizes)) {
                  foreach ($sizes as $row) {
                     if (!isset($row['id'])) continue;

                     $sid = (int)$row['id'];
                     $new_size_ids[] = $sid;

                     ItemSizeStock::where('id', $sid)->update([
                        'status' => 0,
                        'sale_id' => $sale->id,
                        'sale_description_id' => $desc->id
                     ]);
                  }
               }
            }            
         }
         $removed_size_ids = array_diff($old_size_ids, $new_size_ids);
         if (!empty($removed_size_ids)) {
            ItemSizeStock::whereIn('id', $removed_size_ids)->update([
                  'status' => 1,
                  'sale_id' => null,
                  'sale_description_id' => null
            ]);
         }
         $bill_sundrys = $request->input('bill_sundry');
         $tax_amts = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');
         SaleSundry::where('sale_id',$sale->id)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',1)->delete();
         foreach($bill_sundrys as $key => $bill){
            if($bill_sundry_amounts[$key]=="" || $bill==""){
               continue;
            }
            $sundry = new SaleSundry;
            $sundry->sale_id = $sale->id;
            $sundry->bill_sundry = $bill;
            $sundry->rate = $tax_amts[$key];
            $sundry->company_id = Session::get('user_company_id');
            $sundry->amount = $bill_sundry_amounts[$key];
            $sundry->status = '1';
            $sundry->save();
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();
            
            if($billsundry->adjust_sale_amt=='No'){
               $ledger = new AccountLedger();
               $ledger->account_id = $billsundry->sale_amt_account;
               if($billsundry->bill_sundry_type=='subtractive'){
               $ledger->debit = $bill_sundry_amounts[$key];
            }else{
               $ledger->credit = $bill_sundry_amounts[$key];
            }   
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 1;
               $ledger->entry_type_id = $sale->id;
               $ledger->map_account_id = $request->input('party');
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
            }
         }
         //Average Calculation
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $sale_item_array = [];
         foreach($goods_discriptions as $key => $good){
            if($good=="" || $qtys[$key]==""){
               continue;
            }
            if(array_key_exists($good,$sale_item_array)){
               $sale_item_array[$good] = $sale_item_array[$good] + $qtys[$key];
            }else{
               $sale_item_array[$good] = $qtys[$key];
            }     
         }
         foreach ($sale_item_array as $key => $value) {
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $key;
            $average_detail->type = 'SALE';
            $average_detail->sale_id = $sale->id;
            $average_detail->sale_weight = $value;
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$key,$request->input('series_no'));               
         }
         
         
         foreach ($desc_item_arr as $key => $value) {
            if(!array_key_exists($value, $sale_item_array)){
               CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
            }
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = 35;//Sales Account
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 35;//Sales Account
         $ledger->credit = $request->input('taxable_amt');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //Update Sale Order Id Code ...................
         if($request->sale_order_id!=""){
            SaleOrderItemWeight::where('sale_order_id',$request->sale_order_id)->delete();
            ItemSizeStock::where('sale_order_id',$request->sale_order_id)
                           ->where('sale_id',$sale->id)
                           ->update(['status'=>1,'sale_order_id'=>null,'sale_id'=>null,'sale_description_id'=>null]);
            Sales::where('id',$sale->id)->update(['sale_order_id'=>$request->sale_order_id]);
            $saleOrder = SaleOrder::with('items.gsms.details')
                                 ->where('id', $request->sale_order_id)
                                 ->first();
            if ($saleOrder) {
               // Update sale order
               $saleOrder->update(['status' => 1,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
               // Update items
               foreach ($saleOrder->items as $item) {
                  $item->update(['status' => 1]);
                  // Update GSMs
                  foreach ($item->gsms as $gsm) {
                        $gsm->update(['status' => 1]);
                        // Update GSM details
                        foreach ($gsm->details as $detail) {
                           $detail->update(['status' => 1]);
                        }
                  }
               }
            }
            $sale_enter_data = json_decode($request->sale_enter_data,true);
            $grouped = [];
            foreach ($sale_enter_data as $item) {
               $key = $item['detail_row_id'];
               $grouped[$key][] = $item;
            }
            
            $new_order_arr = [];$group_index = 0;$group_index_arr = [];$max_groups = count($desc_id_arr);
            foreach($grouped as $k=>$val){
               $enter_qty = 0;
               foreach($val as $k1=>$val1){
                  // Assign group index only once per unique index
                     if (!isset($group_index_arr[$val1['index']])) {
                        $group_index_arr[$val1['index']] = $group_index;
                        $group_index++;
                        //$group_index = ($group_index + 1) % $max_groups;
                     }

                     $current_group_index = $group_index_arr[$val1['index']];
                  if(!empty($val1['enter_qty'])){
                     if($val1['unit_type']=="REEL"){
                        $enter_qty = $enter_qty + $val1['enter_qty'];
                     }else if($val1['unit_type']=="KG"){
                        $enter_qty = $enter_qty + array_sum($val1['reel_weight_arr']);
                     }                        
                     foreach($val1['reel_weight_arr'] as $k3=>$val2){
                        $sale_order_item_weight = new SaleOrderItemWeight;
                        $sale_order_item_weight->sale_order_id = $request->sale_order_id;
                        $sale_order_item_weight->sale_order_item_row_id = $val1['detail_row_id'];
                        $sale_order_item_weight->weight = $val2;
                        $sale_order_item_weight->weight_id = $val1['reel_weight_id'][$k3];
                        $sale_order_item_weight->company_id = Session::get('user_company_id');
                        $sale_order_item_weight->created_at = Carbon::now();
                        $sale_order_item_weight->save();
                        // print_r($group_index);
                        if(isset($val1['reel_weight_id'][$k3])){
                        ItemSizeStock::where('id',$val1['reel_weight_id'][$k3])->update(['status'=>0,'sale_order_id'=>$request->sale_order_id,'sale_id'=>$sale->id,"sale_description_id"=>$desc_id_arr[$current_group_index]]);
                        }
                     }
                  }
                  
               }
               
               $sale_order_gsm_size = SaleOrderItemGsmSize::find($k);
               $sale_order_gsm_size->sale_order_qty = $enter_qty;
               $sale_order_gsm_size->update();
               $remaining_qty = $sale_order_gsm_size->quantity - $enter_qty;
               if($remaining_qty>0){
                  array_push($new_order_arr,array("id"=>$k,"sale_order_item_id"=>$sale_order_gsm_size->sale_order_item_id,"sale_order_item_gsm_id"=>$sale_order_gsm_size->sale_order_item_gsm_id,"quantity"=>$remaining_qty));
               }
            }
            if($request->new_order==1){
               if(count($new_order_arr)>0){
                  $sale_order = SaleOrder::find($request->sale_order_id);
                  
                  if (preg_match('/-(\d+)$/', $sale_order->sale_order_no, $matches)) {
                     // If found, increment the number
                     $nextNumber = $matches[1] + 1;
                     // Replace the old suffix with the new one
                     $new_sale_order_no = preg_replace('/-\d+$/', '-' . $nextNumber, $sale_order->sale_order_no);
                  } else {
                     // If no suffix found, start with -1
                     $new_sale_order_no = $sale_order->sale_order_no . '-1';
                  }
                  
                  $new_sale_order = new SaleOrder;
                  $new_sale_order->sale_order_no = $new_sale_order_no;
                  $new_sale_order->purchase_order_no = $sale_order->purchase_order_no;
                  $new_sale_order->purchase_order_date = $sale_order->purchase_order_date;
                  $new_sale_order->bill_to = $sale_order->bill_to;
                  $new_sale_order->shipp_to = $sale_order->shipp_to;
                  $new_sale_order->freight = $sale_order->freight;
                  $new_sale_order->parent_order_no = $sale_order->sale_order_no;
                  $new_sale_order->company_id = Session::get('user_company_id');
                  $new_sale_order->created_by = auth()->id();
                  $new_sale_order->created_at = Carbon::now();
                  if($new_sale_order->save()){
                     $item_check_arr = [];$gsm_check_arr = [];
                     foreach($new_order_arr as $nk=>$nval){
                        if(isset($item_check_arr[$nval['sale_order_item_id']]) && $item_check_arr[$nval['sale_order_item_id']]!=""){
                           $new_sale_order_item_id = $item_check_arr[$nval['sale_order_item_id']];
                        }else{
                           $sale_order_item = SaleOrderItem::find($nval['sale_order_item_id']);
                           $new_sale_order_item = new SaleOrderItem;
                           $new_sale_order_item->sale_order_id = $new_sale_order->id;
                           $new_sale_order_item->item_id = $sale_order_item->item_id;
                           $new_sale_order_item->price = $sale_order_item->price;
                           $new_sale_order_item->bill_price = $sale_order_item->bill_price;
                           $new_sale_order_item->unit = $sale_order_item->unit;
                           $new_sale_order_item->sub_unit = $sale_order_item->sub_unit;
                           $new_sale_order_item->company_id = Session::get('user_company_id');
                           $new_sale_order_item->created_at = Carbon::now();
                           $new_sale_order_item->save();
                           $item_check_arr[$nval['sale_order_item_id']] = $new_sale_order_item->id;
                           $new_sale_order_item_id = $new_sale_order_item->id;
                        }                  
                        if($new_sale_order_item_id){
                           if(isset($gsm_check_arr[$nval['sale_order_item_gsm_id']]) && $gsm_check_arr[$nval['sale_order_item_gsm_id']]!=""){
                              $new_sale_order_item_gsm_id = $gsm_check_arr[$nval['sale_order_item_gsm_id']];
                           }else{
                              $sale_order_item_gsm = SaleOrderItemGSM::find($nval['sale_order_item_gsm_id']);
                              $new_sale_order_item_gsm = new SaleOrderItemGSM;
                              $new_sale_order_item_gsm->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm->sale_order_item_id = $new_sale_order_item_id;
                              $new_sale_order_item_gsm->gsm = $sale_order_item_gsm->gsm;
                              $new_sale_order_item_gsm->company_id = Session::get('user_company_id');
                              $new_sale_order_item_gsm->created_at = Carbon::now();
                              $new_sale_order_item_gsm->save();
                              $gsm_check_arr[$nval['sale_order_item_gsm_id']] = $new_sale_order_item_gsm->id;
                              $new_sale_order_item_gsm_id = $new_sale_order_item_gsm->id;
                           }
                           if($new_sale_order_item_gsm_id){                        
                              $sale_order_item_gsm_size = SaleOrderItemGsmSize::find($nval['id']);
                              $new_sale_order_item_gsm_size = new SaleOrderItemGsmSize;
                              $new_sale_order_item_gsm_size->sale_orders_id = $new_sale_order->id;
                              $new_sale_order_item_gsm_size->sale_order_item_id = $new_sale_order_item->id;
                              $new_sale_order_item_gsm_size->sale_order_item_gsm_id = $new_sale_order_item_gsm_id;
                              $new_sale_order_item_gsm_size->size = $sale_order_item_gsm_size->size;
                              $new_sale_order_item_gsm_size->quantity = $nval['quantity'];
                              $new_sale_order_item_gsm_size->company_id = Session::get('user_company_id');
                              $new_sale_order_item_gsm_size->created_at = Carbon::now();
                              $new_sale_order_item_gsm_size->save();
                           }
                        }
                     }
                  }
               }
            }
            //Store Vehicle Details
            SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>'',
                           'freight_price'=>'',
                           'freight_transporter_id'=>'',
                           'other_freight_amount'=>'',
                           'freight_vehicle_id'=>'',
                        ]);
            SaleVehicleTxn::where('sale_order_id',$request->sale_order_id)->delete();
            if($sale->transporter_journal_id){
               JournalDetails::where('journal_id',$sale->transporter_journal_id)->delete();
               Journal::where('id',$sale->transporter_journal_id)->delete();
               AccountLedger::where('entry_type',7)->where('entry_type_id',$sale->transporter_journal_id)->delete();

            }
            Sales::where('id',$sale->id)->update(['transporter_journal_id'=>null]);

            
            if($request->input('vehicle_info_type')=="vehicle" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('vehicle_freight'),
                           'freight_vehicle_id'=>$request->input('vehicle_info'),
                           'other_freight_amount'=>''
                        ]);
               $vehicle_info = new SaleVehicleTxn;
               $vehicle_info->sale_id = $sale->id;
               $vehicle_info->sale_order_id = $request->sale_order_id;
               $vehicle_info->vehicle_id = $request->input('vehicle_info');
               $vehicle_info->vehicle_freight_price = $request->input('vehicle_freight');
               $vehicle_info->vehicle_freight_amount = $item_quantity_total * $request->input('vehicle_freight');
               $vehicle_info->company_id = Session::get('user_company_id');
               $vehicle_info->created_at = Carbon::now();
               $vehicle_info->created_by = Session::get('user_id');
               $vehicle_info->save();
            }
            if($request->input('vehicle_info_type')=="to_pay" && $request->sale_order_id!="" ){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('to_pay_freight'),
                           'other_freight_amount'=>$request->input('to_pay_other_charges')
                        ]);
            }
            if($request->input('vehicle_info_type')=="party_vehicle" && $request->sale_order_id!="" ){
               SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>"",
                           'other_freight_amount'=>""
                        ]);
            }
            //Transporter Journal Entry
            if($request->input('vehicle_info_type')=="transporter" && $request->sale_order_id!="" && $request->input('vehicle_info')!=""){
               $transporter_total_amount = ($item_quantity_total * $request->input('transporter_freight'))+$request->input('transporter_other_charges');
               $transporter_total_amount = round($transporter_total_amount);
               $location_name = $account->location;
               if(!empty($request->input('shipping_name'))){
                  $shipp_account = Accounts::select('location')->find($request->input('shipping_name'));
                  $location_name = $shipp_account->location;
               }
               
               //Journal Entry For Transporter Voucher No
               $series_configuration = VoucherSeriesConfiguration::where('company_id', Session::get('user_company_id'))
                                                                  ->where('series', $request->input('series_no'))
                                                                  ->where('configuration_for', 'JOURNAL') 
                                                                  ->where('status', '1')
                                                                  ->first();
               $number_digit = (!empty($series_configuration->number_digit)) ? (int)$series_configuration->number_digit : 3;
               $lastNumber = DB::table('journals')
                                 ->where('company_id', Session::get('user_company_id'))
                                 ->where('financial_year', $financial_year)
                                 ->where('series_no', $request->input('series_no'))
                                 ->where('delete', '0')
                                 ->max(DB::raw("cast(voucher_no as SIGNED)"));
               if (!$lastNumber) {
                  if ($series_configuration && $series_configuration->manual_numbering == "NO" && $series_configuration->invoice_start != "") {
                     $journal_voucher_no = (int)$series_configuration->invoice_start;
                  } else {
                     $journal_voucher_no = 1;
                  }
               } else {
                  $journal_voucher_no = ((int)$lastNumber) + 1;
               }
               //Voucher Series With Prefix/Suffix
               $journal_invoice_prefix = "";
               if ($series_configuration && $series_configuration->manual_numbering == "NO") {
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->prefix_value != "") {
                     $journal_invoice_prefix .= $series_configuration->prefix_value;
                  }
                  if ($series_configuration->prefix == "ENABLE" && $series_configuration->separator_1 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_1;
                  }
                  if ($series_configuration->year == "PREFIX TO NUMBER") {
                     if ($series_configuration->year_format == "YY-YY") {
                        $journal_invoice_prefix .= Session::get('default_fy');
                     } else {
                        $fy = explode('-', Session::get('default_fy'));
                        $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                     }
                     if ($series_configuration->separator_2 != "") {
                        $journal_invoice_prefix .= $series_configuration->separator_2;
                     }
                  }
                  $journal_invoice_prefix .= $journal_voucher_no;
                  if ($series_configuration->year == "SUFFIX TO NUMBER") {
                     if ($series_configuration->separator_2 != "") {
                        $journal_invoice_prefix .= $series_configuration->separator_2;
                     }
                     if ($series_configuration->year_format == "YY-YY") {
                        $journal_invoice_prefix .= Session::get('default_fy');
                     } else {
                        $fy = explode('-', Session::get('default_fy'));
                        $journal_invoice_prefix .= '20' . $fy[0] . '-' . $fy[1];
                     }
                  }
                  if ($series_configuration->suffix == "ENABLE" && $series_configuration->separator_3 != "") {
                     $journal_invoice_prefix .= $series_configuration->separator_3;
                  }
                  if ($series_configuration->suffix == "ENABLE" && $series_configuration->suffix_value != "") {
                     $journal_invoice_prefix .= $series_configuration->suffix_value;
                  }
               }
               $journal_voucher_no = sprintf("%0" . $number_digit . "d", $journal_voucher_no);
               if($journal_invoice_prefix==""){
                  $journal_invoice_prefix = $journal_voucher_no;
               }
               $journal = new Journal;
               $journal->date = $request->input('date');
               $journal->voucher_no = $journal_voucher_no;
               $journal->voucher_no_prefix = $journal_invoice_prefix;
               $journal->series_no = $request->input('series_no');
               $journal->long_narration = "Bill No : ".$sale->voucher_no_prefix.", Vehicle No. : ".$request->input('vehicle_no').", Location : ".$location_name.", GR/PR No. : ".$request->input('gr_pr_no');
               $journal->company_id = Session::get('user_company_id');
               $journal->financial_year = $financial_year;
               $journal->claim_gst_status = 'NO';
               $journal->merchant_gst = $request->input('merchant_gst');
               if($journal->save()){
                  SaleOrder::where('id',$request->sale_order_id)
                        ->update([
                           'freight_type'=>$request->input('vehicle_info_type'),
                           'freight_price'=>$request->input('transporter_freight'),
                           'freight_transporter_id'=>$request->input('vehicle_info'),
                           'other_freight_amount'=>$request->input('transporter_other_charges')
                        ]);
                  Sales::where('id',$sale->id)->update(['transporter_journal_id'=>$journal->id]);
                  //Add Transpoeter Account Credit
                  $expense = DB::table('sale-order-settings')
                                    ->where('setting_type','EXPENSE_ACCOUNT')
                                    ->where('setting_for','SALE ORDER')
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->first();
                  $joundetail = new JournalDetails;
                  $joundetail->journal_id = $journal->id;
                  $joundetail->company_id = Session::get('user_company_id');
                  $joundetail->type = "Credit";
                  $joundetail->account_name = $request->input('vehicle_info');
                  $joundetail->debit = '0';
                  $joundetail->credit = $transporter_total_amount;            
                  $joundetail->narration = "";
                  $joundetail->status = '1';
                  $joundetail->save();
                  //Account Ledger
                  $ledger = new AccountLedger();
                  $ledger->account_id = $request->input('vehicle_info');               
                  $ledger->credit = $transporter_total_amount;
                  $ledger->map_account_id = $expense->expense_account_id;
                  $ledger->series_no = $request->input('series_no');
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = $financial_year;
                  $ledger->entry_type = 7;
                  $ledger->entry_type_id = $journal->id;
                  $ledger->entry_narration = "";               
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //Add Freight Account Debit
                  
                  $joundetail = new JournalDetails;
                  $joundetail->journal_id = $journal->id;
                  $joundetail->company_id = Session::get('user_company_id');;
                  $joundetail->type = "Debit";
                  $joundetail->account_name = $expense->expense_account_id;
                  $joundetail->debit = $transporter_total_amount;
                  $joundetail->credit = '0';
                  $joundetail->narration = "";
                  $joundetail->status = '1';
                  $joundetail->save();
                  //Account Ledger
                  $ledger = new AccountLedger();
                  $ledger->account_id = $expense->expense_account_id;
                  $ledger->debit = $transporter_total_amount;
                  $ledger->map_account_id = $request->input('vehicle_info');
                  $ledger->series_no = $request->input('series_no');
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = $financial_year;
                  $ledger->entry_type = 7;
                  $ledger->entry_type_id = $journal->id;
                  $ledger->entry_narration = "";               
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                     
               }
            }
            //
         }
         $newSnapshot = [
            'sale' => Sales::find($sale->id)->toArray(),

            'items' => SaleDescription::where('sale_id', $sale->id)->get()->toArray(),

            'sundries' => SaleSundry::where('sale_id', $sale->id)->get()->toArray(),

            'item_ledgers' => ItemLedger::where('source', 1)
               ->where('source_id', $sale->id)
               ->get()->toArray(),

            'account_ledgers' => AccountLedger::where('entry_type', 1)
               ->where('entry_type_id', $sale->id)
               ->get()->toArray(),

            'item_average_details' => ItemAverageDetail::where('sale_id', $sale->id)
               ->where('type', 'SALE')
               ->get()->toArray(),
         ];
         ActivityLog::create([
            'module_type' => 'sale',
            'module_id'   => $sale->id,
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
            session(['previous_url_saleEdit' => URL::previous()]);
            return redirect('sale-invoice/'.$sale->id.'?source=sale')->withSuccess('Sale voucher updated successfully!');
         }
         
      }else{
         return $this->failedMessage('Something went wrong','sale/create');
         exit();
      }
      
   }
   public function saleImportView(Request $request){      
      return view('sale_import')->with('upload_log',0)->with('total_count',5)->with('success_count',3)->with('failed_count',2)->with('error_message',array(0 => array(0=>'Voucher A41 already exists - Invoice No. A41'),1 => array(0=>'Voucher A42 already exists - Invoice No. A42')));
      
   }
   public function saleImportProcess(Request $request) { 
      ini_set('max_execution_time', 0);
      ini_set('memory_limit', '1024M'); 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;
      $financial_year = Session::get('default_fy');  
      $financial_year;
      $fy = explode('-',$financial_year);
      $from_date = $fy[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $fy[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      $company_data = Companies::where('id', Session::get('user_company_id'))->first(); 
      $series_arr = [];$material_center_arr = [];$gst_no_arr = [];$all_error_arr = [];$error_arr = [];$item_arr = [];$data_arr = [];$voucher_arr = [];
      $already_exists_error_arr = [];$already_exists_voucher_arr = [];
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
               if($data[0]!="" && $data[2]!=""){
                  $series_no = $data[0];
                  $voucher_no = $data[2];                             
                  $check_invoice = Sales::select('id')
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->where('voucher_no',$voucher_no)
                                 ->where('series_no',$series_no)
                                 ->where('financial_year','=',$financial_year)
                                 ->where('delete','0')
                                 ->first();
                  if($check_invoice){
                     array_push($already_exists_error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                  }
                  if(in_array($series_no."_".$voucher_no, $already_exists_voucher_arr)){
                     array_push($already_exists_error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                  }
                  array_push($already_exists_voucher_arr,$series_no."_".$voucher_no);
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
      $series_no = "";
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
            if($data[0]!="" && $data[2]!=""){
               if($series_no!=""){
                  $akey = array_search($series_no, $series_arr);
                  $merchant_gst = $gst_no_arr[$akey];
                  array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"merchant_gst"=>$merchant_gst,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"error_arr"=>$error_arr));
               }               
               $item_arr = [];
               $error_arr = [];
               $slicedData = [];
               $series_no = $data[0];
               $date = $data[1];
               $voucher_no = $data[2];
               $party = $data[3];
               $material_center = $data[4];
               $grand_total = $data[5];
               $grand_total = str_replace(",","",$grand_total);
               $self_vehicle = $data[6];
               $vehicle_no = $data[7];
               $transport_name = $data[8];
               $reverse_charge = $data[9];
               $gr_pr_no = $data[10];
               $station = $data[11];
               $ewaybill_no = $data[12];            
               $shipping_name = $data[13];
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($date)))){                  
                  array_push($error_arr, 'Date '.$date.' not in Financial Year - Invoice No. '.$voucher_no);                  
               }
               if(!in_array($series_no, $series_arr)){
                  array_push($error_arr, 'Series No. '.$series_no.' not found in GST Configuration - Invoice No. '.$voucher_no); 
               }
               if(!in_array($material_center, $material_center_arr)){
                  array_push($error_arr, 'Material Center '.$material_center.' not found in GST Configuration - Invoice No. '.$voucher_no);
               }
               $account = Accounts::where('account_name',trim($party))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               if(!$account){
                  array_push($error_arr, 'Party Name '.$party.' not found - Invoice No. '.$voucher_no);
               } 
               if($shipping_name!=""){
                  $shipp = Accounts::where('account_name',trim($shipping_name))
                           ->where('company_id',trim(Session::get('user_company_id')))
                           ->first();      
                  if(!$shipp){
                     array_push($error_arr, 'Shipping Name '.$shipping_name.' not found - Invoice No. '.$voucher_no);
                  } 
               }
               $slicedData = array_slice($data,21,100);
               if(count($slicedData)>0){
                  foreach($slicedData as $key => $value){
                     $value = trim($value);
                     if($key%2==0){
                        if($value!="" && $value!='0'){
                           $bill_sundrys = BillSundrys::where('delete', '=', '0')
                                    ->where('status', '=', '1')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->where('name',$value)
                                    ->first();
                           if(!$bill_sundrys){
                              array_push($error_arr, 'Bill Sundry '.$value.' not found - Invoice No. '.$voucher_no);
                           }
                        }
                        
                     }                     
                  }
               }
               if($duplicate_voucher_status!=2){
                  $check_invoice = Sales::select('id')
                              ->where('company_id',Session::get('user_company_id'))
                              ->where('voucher_no',$voucher_no)
                              ->where('series_no',$series_no)
                              ->where('financial_year','=',$financial_year)
                              ->where('delete','0')
                              ->first();
                  if($check_invoice){
                     array_push($error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                  }
                  if(in_array($series_no."_".$voucher_no, $voucher_arr)){
                     array_push($error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                  }
                  array_push($voucher_arr,$series_no."_".$voucher_no);
               }
            }
            $item_name = $data[14]; 
            $item = ManageItems::select('id','hsn_code')
                        ->where('name',trim($item_name))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
            if(!$item){
               array_push($error_arr, 'Item Name '.$item_name.' not found - Invoice No. '.$voucher_no);
            }
            $item_weight = $data[15];
            $item_weight = str_replace(",","",$item_weight);
            $price = $data[16];
            $price = trim(str_replace(",","",$price));
            $amount = $data[17];
            $amount = trim(str_replace(",","",$amount));
            $cgst = $data[18];
            $cgst = trim(str_replace(",","",$cgst));
            $sgst = $data[19];
            $sgst = trim(str_replace(",","",$sgst));
            $igst = $data[20];
            $igst = trim(str_replace(",","",$igst));         
            array_push($item_arr,array("item_name"=>$item_name,"item_weight"=>$item_weight,"price"=>$price,"amount"=>$amount,"cgst"=>$cgst,"sgst"=>$sgst,"igst"=>$igst));
            if($index==$total_row){
               $akey = array_search($series_no, $series_arr);
               $merchant_gst = $gst_no_arr[$akey];
               array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"merchant_gst"=>$merchant_gst,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"error_arr"=>$error_arr));
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
            $override_average_data_arr = [];$new_average_data_arr = [];$smallestDate = null;
            foreach ($data_arr as $key => $value){
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }
               $date = date('Y-m-d',strtotime($value['date']));
               if ($smallestDate === null || strtotime($date) < strtotime($smallestDate)) {
                  $smallestDate = $date;
               }
               
               $series_no = $value['series_no'];
               
               $voucher_no = $value['voucher_no'];
               $party = $value['party'];
               $material_center = $value['material_center'];
               $grand_total = $value['grand_total'];
               $grand_total = str_replace(",","",$grand_total); 
               $self_vehicle = $value['self_vehicle'];
               $vehicle_no = $value['vehicle_no'];
               $transport_name = $value['transport_name'];
               $reverse_charge = $value['reverse_charge'];
               $gr_pr_no = $value['gr_pr_no'];
               $station = $value['station'];
               $ewaybill_no = $value['ewaybill_no'];
               $shipping_name = $value['shipping_name'];
               $item_arr = $value['item_arr'];
               $slicedData = $value['slicedData'];
               $merchant_gst = $value['merchant_gst'];
               if($duplicate_voucher_status==2){
                  $check_invoices = Sales::select('id')
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('voucher_no',$voucher_no)
                           ->where('series_no',$series_no)
                           ->where('financial_year','=',$financial_year)
                           ->where('delete','0')
                           ->where('status','1')
                           ->get();
                  foreach($check_invoices as $check_invoices_value){
                     $updated_sale = Sales::find($check_invoices_value->id);
                     $updated_sale->delete = '1';
                     $updated_sale->deleted_at = Carbon::now();
                     $updated_sale->deleted_by = Session::get('user_id');
                     $updated_sale->update();
                     if($updated_sale){
                        SaleDescription::where('sale_id',$check_invoices_value->id)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',1)
                                       ->where('entry_type_id',$check_invoices_value->id)
                                       ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        SaleSundry::where('sale_id',$check_invoices_value->id)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        ItemLedger::where('source',1)
                                    ->where('source_id',$check_invoices_value->id)
                                    ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        ItemAverageDetail::where('sale_id',$check_invoices_value->id)
                                          ->delete();
                        $itemKiId =  SaleDescription::where('sale_id',$check_invoices_value->id)
                                    ->select('sale_descriptions.goods_description as item_id');
                        foreach($itemKiId as $k){
                           //CommonHelper::RewriteItemAverageByItem($check_invoices_value->date,$k->item_id,$series_no);
                           array_push($override_average_data_arr,array("item_id"=>$k->item_id,"series"=>$series_no,"date"=>$check_invoices_value->date));
                        }
                     }
                  }
               }
               $item_taxable_amount = 0;
               //Insert Data In Sale Table
               $account = Accounts::where('account_name',trim($party))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               $shipp = Accounts::where('account_name',trim($shipping_name))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               $sale = new Sales;
               $sale->series_no = $series_no;
               $sale->company_id = Session::get('user_company_id');
               $sale->date = $date;
               $sale->voucher_no = $voucher_no;
               $sale->voucher_no_prefix = $voucher_no;
               $sale->party = $account->id;
               $sale->material_center = $material_center;
               $sale->merchant_gst = $merchant_gst; 
               //$sale->taxable_amt = $request->input('taxable_amt');//
               $sale->total = $grand_total;
               $sale->self_vehicle = $self_vehicle;
               $sale->vehicle_no = $vehicle_no;
               $sale->transport_name = $transport_name;
               $sale->reverse_charge = $reverse_charge;
               $sale->gr_pr_no = $gr_pr_no;
               $sale->station = $station;
               $sale->ewaybill_no = $ewaybill_no;
               $sale->billing_name = $account->account_name;
               $sale->billing_address = $account->address;
               $sale->billing_pincode = $account->pin_code;
               $sale->billing_gst = $account->gstin;
               $sale->billing_pan = $account->pan;
               $sale->billing_state = $account->state; 
               if($shipp){
                  $sale->shipping_name = $shipp->account_name;;
                  $sale->shipping_state = $shipp->state;
                  $sale->shipping_address = $shipp->address;
                  $sale->shipping_pincode = $shipp->pin_code;
                  $sale->shipping_gst = $shipp->gstin;
                  $sale->shipping_pan = $shipp->pan;
               }
               $sale->financial_year = $financial_year;
               $sale->entry_source = 2; 
               $sale->save();
               if($sale->id){  
                  //ITEM DATA INSERT
                  $tax_arr = [];
                  foreach ($item_arr as $k1 => $v1) {
                     $item = ManageItems::select('manage_items.id','manage_items.gst_rate')
                        ->where('manage_items.name',trim($v1['item_name']))
                        ->where('manage_items.company_id',trim(Session::get('user_company_id')))
                        ->first();
                     //TAX GST
                     if($v1['cgst']!="" && $v1['sgst']!=""){
                        array_push($tax_arr,array("gst_rate"=>$item->gst_rate,"cgst"=>$v1['cgst'],"sgst"=>$v1['sgst'],"igst"=>""));
                     }else if($v1['igst']!=""){
                        array_push($tax_arr,array("gst_rate"=>$item->gst_rate,"cgst"=>"","sgst"=>"","igst"=>$v1['igst']));
                     } 
                  }                  
                  $return = array();
                  foreach($tax_arr as $val) {
                     $return[$val['gst_rate']][] = $val;
                  }
                  foreach($return as $k5=>$v5) {
                     $tx_rate = $k5;
                     $cgst_rate = 0;$sgst_rate = 0;$igst_rate = 0;                     
                     foreach($v5 as $k6=>$v6) {                        
                        if(!empty($v6['cgst'])){
                           $cgst_rate = $cgst_rate + $v6['cgst'];
                        }
                        if(!empty($v6['sgst'])){
                           $sgst_rate = $sgst_rate + $v6['sgst'];
                        }
                        if(!empty($v6['igst'])){
                           $igst_rate = $igst_rate + $v6['igst'];
                        }
                     }
                     //TAX GST
                     if($cgst_rate!="" && $cgst_rate!=0 && $sgst_rate!="" && $sgst_rate!=0){
                        $bill_sundrys = BillSundrys::where('delete', '=', '0')
                                          ->where('status', '=', '1')
                                          ->where('nature_of_sundry', '=', 'CGST')
                                          ->where('company_id',Session::get('user_company_id'))
                                          ->first(); 
                        $sundry = new SaleSundry;
                        $sundry->sale_id = $sale->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate/2;
                        $sundry->amount = str_replace(",","",$cgst_rate);
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_sale_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->sale_amt_account;
                           $ledger->credit = str_replace(",","",$cgst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->series_no = $series_no;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 1;
                           $ledger->entry_type_id = $sale->id;
                           $ledger->map_account_id = $account->id;
                           $ledger->created_by = Session::get('user_id');
                           $ledger->created_at = date('d-m-Y H:i:s');
                           $ledger->save();
                        }
                        $bill_sundrys = BillSundrys::where('delete', '=', '0')
                                          ->where('status', '=', '1')
                                          ->where('nature_of_sundry', '=', 'SGST')
                                          ->where('company_id',Session::get('user_company_id'))
                                          ->first(); 
                        $sundry = new SaleSundry;
                        $sundry->sale_id = $sale->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate/2;
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->amount = str_replace(",","",$sgst_rate);
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN SCGST ACCOUNT     
                        if($bill_sundrys->adjust_sale_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->sale_amt_account;
                           $ledger->credit = str_replace(",","",$sgst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->series_no = $series_no;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 1;
                           $ledger->entry_type_id = $sale->id;
                           $ledger->map_account_id = $account->id;
                           $ledger->created_by = Session::get('user_id');
                           $ledger->created_at = date('d-m-Y H:i:s');
                           $ledger->save();
                        }
                     }else if($igst_rate!="" && $igst_rate!=0){
                        $bill_sundrys = BillSundrys::where('delete', '=', '0')
                                          ->where('status', '=', '1')
                                          ->where('nature_of_sundry', '=', 'IGST')
                                          ->where('company_id',Session::get('user_company_id'))
                                          ->first(); 
                        $sundry = new SaleSundry;
                        $sundry->sale_id = $sale->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate;
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->amount = str_replace(",","",$igst_rate);
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN IGST ACCOUNT     
                        if($bill_sundrys->adjust_sale_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->series_no = $series_no;
                           $ledger->account_id = $bill_sundrys->sale_amt_account;
                           $ledger->credit = str_replace(",","",$igst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 1;
                           $ledger->entry_type_id = $sale->id;
                           $ledger->map_account_id = $account->id;
                           $ledger->created_by = Session::get('user_id');
                           $ledger->created_at = date('d-m-Y H:i:s');
                           $ledger->save();
                        }
                     }
                  }
                  foreach ($item_arr as $k1 => $v1) {
                     if(!empty($v1['amount'])){
                        $item_taxable_amount = $item_taxable_amount + str_replace(",","",$v1['amount']);
                        $item = ManageItems::join('units','manage_items.u_name','=','units.id')
                           ->select('manage_items.id','manage_items.hsn_code','manage_items.gst_rate','units.s_name as unit','units.id as uid')
                           ->where('manage_items.name',trim($v1['item_name']))
                           ->where('manage_items.company_id',trim(Session::get('user_company_id')))
                           ->first();
                        $desc = new SaleDescription;
                        $desc->sale_id = $sale->id;
                        $desc->goods_discription = $item->id;
                        $desc->qty = $v1['item_weight'];
                        $desc->unit = $item->uid;
                        $desc->company_id = Session::get('user_company_id');
                        $desc->price = $v1['price'];
                        $desc->amount = str_replace(",","",$v1['amount']);
                        $desc->status = '1';
                        $desc->save();
                        //ADD ITEM LEDGER
                        $item_ledger = new ItemLedger();
                        $item_ledger->item_id = $item->id;
                        $item_ledger->out_weight = $v1['item_weight'];
                        $item_ledger->txn_date = $date;
                        $item_ledger->series_no = $series_no;
                        $item_ledger->price = $v1['price'];
                        $item_ledger->total_price = str_replace(",","",$v1['amount']);
                        $item_ledger->company_id = Session::get('user_company_id');
                        $item_ledger->source = 1;
                        $item_ledger->source_id = $sale->id;
                        $item_ledger->created_by = Session::get('user_id');
                        $item_ledger->created_at = date('d-m-Y H:i:s');
                        $item_ledger->save(); 
                     }
                                          
                  }
                  //Other Bill Sundry
                  $sundry_id = "";
                  $adjust_sale_amt = "";
                  $bill_sundry_amounts = "";
                  $sale_amt_account = "";
                  $nature_of_sundry = "";
                  $bill_sundry_type = "";
                  foreach($slicedData as $k2 => $v2){
                     $v2 = trim($v2);
                     if($v2!="" && $v2!='0'){                      
                        if($k2%2==0){
                           $bill_sundrys = BillSundrys::where('delete', '=', '0')
                                       ->where('status', '=', '1')
                                       ->where('name', '=', $v2)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();  
                           $sundry_id = $bill_sundrys->id;
                           $adjust_sale_amt = $bill_sundrys->adjust_sale_amt;
                           $nature_of_sundry = $bill_sundrys->nature_of_sundry;
                           $sale_amt_account = $bill_sundrys->sale_amt_account;
                           $bill_sundry_type = $bill_sundrys->bill_sundry_type;
                        }else if($k2%2!=0){
                           $v2 = trim(str_replace(",","",$v2));
                           $v2 = trim(str_replace(" ","",$v2));                           
                           if(!empty($v2)){
                              $sundry = new SaleSundry;
                              $sundry->sale_id = $sale->id;
                              $sundry->bill_sundry = $sundry_id   ;
                              $sundry->rate = 0;
                              $sundry->company_id = Session::get('user_company_id');
                              $sundry->amount = str_replace(",","",$v2);
                              $sundry->status = '1';
                              $sundry->save();
                              //ADD DATA BILL SUNDRY ACCOUNT 
                              if($adjust_sale_amt=='No'){
                                 $ledger = new AccountLedger();
                                 $ledger->account_id = $sale_amt_account;
                                 if($nature_of_sundry=='ROUNDED OFF (-)'){
                                    $ledger->debit = $v2;
                                 }else{
                                    $ledger->credit = $v2;
                                 }               
                                 $ledger->txn_date = $date;
                                 $ledger->series_no = $series_no;
                                 $ledger->company_id = Session::get('user_company_id');
                                 $ledger->financial_year = Session::get('default_fy');
                                 $ledger->entry_type = 1;
                                 $ledger->entry_type_id = $sale->id;
                                 $ledger->map_account_id = $account->id;
                                 $ledger->created_by = Session::get('user_id');
                                 $ledger->created_at = date('d-m-Y H:i:s');
                                 $ledger->save();
                              }
                              if($nature_of_sundry=='OTHER'){
                                 if($bill_sundry_type=='additive'){
                                    $item_taxable_amount = $item_taxable_amount + str_replace(",","",$v2);
                                 }else if($bill_sundry_type=='subtractive'){
                                    $item_taxable_amount = $item_taxable_amount - str_replace(",","",$v2);
                                 }
                              }
                              
                              
                           }
                           
                        }
                     }
                  }
                  foreach ($item_arr as $k1 => $v1) {
                     if(!empty($v1['amount'])){
                        //$item_taxable_amount = $item_taxable_amount + str_replace(",","",$v1['amount']);
                        $item = ManageItems::join('units','manage_items.u_name','=','units.id')
                           ->select('manage_items.id','manage_items.hsn_code','manage_items.gst_rate','units.s_name as unit','units.id as uid')
                           ->where('manage_items.name',trim($v1['item_name']))
                           ->where('manage_items.company_id',trim(Session::get('user_company_id')))
                           ->first();                 
                           //Add Data In Average Details table
                           $average_detail = new ItemAverageDetail;
                           $average_detail->entry_date = $sale->date;
                           $average_detail->series_no = $series_no;
                           $average_detail->item_id = $item->id;
                           $average_detail->type = 'SALE';
                           $average_detail->sale_id = $sale->id;
                           $average_detail->sale_weight = $v1['item_weight'];
                           $average_detail->company_id = Session::get('user_company_id');
                           $average_detail->created_at = Carbon::now();
                           $average_detail->save();
                           //CommonHelper::RewriteItemAverageByItem($sale->date,$item->id,$series_no);
                           array_push($new_average_data_arr,array("item_id"=>$item->id,"series"=>$series_no,"date"=>$sale->date));
                           
            
                     }
                  }
                  
                  //ADD DATA IN Customer ACCOUNT
                  $ledger = new AccountLedger();
                  $ledger->account_id = $account->id;
                  $ledger->debit = $grand_total;
                  $ledger->series_no = $series_no;
                  $ledger->txn_date = $date;
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 1;
                  $ledger->entry_type_id = $sale->id;
                  $ledger->map_account_id = 35;//Sales Account
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //ADD DATA IN Sale ACCOUNT
                  $ledger = new AccountLedger();
                  $ledger->account_id = 35;//Sales Account
                  $ledger->credit = $item_taxable_amount;
                  $ledger->series_no = $series_no;
                  $ledger->txn_date = $date;
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 1;
                  $ledger->entry_type_id = $sale->id;
                  $ledger->map_account_id = $account->id;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();

                  $update_sale = Sales::find($sale->id);
                  $update_sale->taxable_amt = $item_taxable_amount;
                  $update_sale->status = '1';
                  $update_sale->update();
                  $success_invoice_count++;
               }
            }
            if($duplicate_voucher_status==2){
               if(count($override_average_data_arr)>0){
                  $override_average_data_arr = array_map("unserialize", array_unique(array_map("serialize", $override_average_data_arr)));
                  foreach($override_average_data_arr as $value){
                     CommonHelper::RewriteItemAverageByItem($value['date'],$value['item_id'],$value['series']);
                  }
               }
            }
            if(count($new_average_data_arr)>0){
               $new_average_data_arr = array_map("unserialize", array_unique(array_map("serialize", $new_average_data_arr)));
               foreach($new_average_data_arr as $value){
                  CommonHelper::RewriteItemAverageByItem($value['date'],$value['item_id'],$value['series']);
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
      //return view('sale_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$all_error_arr);
   }
   public function generateEinvoice(Request $request){      
      $einvoice_username = ""; $einvoice_password = "";
      $einvoice_gst = ""; $einvoice_company = "";
      ini_set('serialize_precision','-1');
      $validated = $request->validate([
        'id' => 'required',
      ]);
      $sale = Sales::join('accounts','sales.party','=','accounts.id')
                    ->join('companies','sales.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('sales.id',$request->id)
                    ->first(['sales.*','accounts.print_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);
      if($sale->gst_config_type=="multiple_gst"){
         $gst_info = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'multiple_gst'])
                           ->get();
         foreach ($gst_info as $key => $value) {
            if($value->series==$sale->series_no){
               $st = State::select('name')->where('id',$value->state)->first();
               $seller_Gstin = $value->gst_no;
               $seller_LglNm = $sale->company_name;
               $seller_TrdNm= $sale->company_name;
               $seller_Addr1 = $value->address;
               $seller_Loc = $st->name;
               $seller_Pin = $value->pincode;
               $seller_Stcd = substr($value->gst_no,0,2);
               $einvoice_username = $value->einvoice_username; 
               $einvoice_password = $value->einvoice_password;
               $einvoice_gst = $value->gst_no;
               $einvoice_company = $value->id;
               break;
            }else{
               $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$value->id,'branch_series'=>$sale->series_no])
                           ->first();
               if($branch){
                  $st = State::select('name')->where('id',$value->state)->first();
                  $seller_Gstin = $branch->gst_number;
                  $seller_LglNm = $sale->company_name;
                  $seller_TrdNm= $sale->company_name;
                  $seller_Addr1 = $branch->branch_address;
                  $seller_Loc = $st->name;
                  $seller_Pin = $branch->branch_pincode;
                  $seller_Stcd = substr($branch->gst_number,0,2);
                  $einvoice_username = $value->einvoice_username; 
                  $einvoice_password = $value->einvoice_password;
                  $einvoice_gst = $branch->gst_number;
                  $einvoice_company = $value->id;
                  break;
               }
            }
         }
      }else if($sale->gst_config_type=="single_gst"){
         $gst_info = DB::table('gst_settings')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'single_gst'])
                           ->first();
         if($gst_info->series==$sale->series_no){
            $st = State::select('name')->where('id',$gst_info->state)->first();
            $seller_Gstin = $gst_info->gst_no;
            $seller_LglNm = $sale->company_name;
            $seller_TrdNm= $sale->company_name;
            $seller_Addr1 = $gst_info->address;
            $seller_Loc = $st->name;
            $seller_Pin = $gst_info->pincode;
            $seller_Stcd = substr($gst_info->gst_no,0,2);
         }else{
            $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$gst_info->id,'branch_series'=>$sale->series_no])
                           ->first();
            if($branch){
               $st = State::select('name')->where('id',$gst_info->state)->first();
               $seller_Gstin = $gst_info->gst_no;
               $seller_LglNm = $sale->company_name;
               $seller_TrdNm= $sale->company_name;
               $seller_Addr1 = $branch->branch_address;
               $seller_Loc = $st->name;
               $seller_Pin = $branch->branch_pincode;
               $seller_Stcd = substr($branch->gst_number,0,2);
            }
         }
         $einvoice_company = $gst_info->id;
         $einvoice_username = $gst_info->einvoice_username; 
         $einvoice_password = $gst_info->einvoice_password;
         $einvoice_gst = $gst_info->gst_no;
      }
      if($einvoice_username=="" || $einvoice_password==""){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"UserName,Password Required."
         );
         return json_encode($res);
      }
      
      if(!empty($sale->shipping_name) && !empty($sale->shipping_name)){
         $acc = Accounts::select('print_name')->where('id',$sale->shipping_name)->first();
         $shipp_name = $acc->print_name;
         $shipp_address = $sale->shipping_address;
         $shipp_gst = $sale->shipping_gst;
         $shipp_state = $sale->shipping_state;
         $shipp_pincode = $sale->shipping_pincode;        
      }else{
         $shipp_name = $sale->print_name;
         $shipp_address = $sale->billing_address;
         $shipp_gst = $sale->billing_gst;
         $shipp_state = $sale->name;
         $shipp_pincode = $sale->billing_pincode;
      }
      $CGST = null;$SGST = null;$IGST = null;$TCS = 0;
      $sundry = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                  ->select(['sale_sundries.rate','sale_sundries.amount','bill_sundry_type','adjust_sale_amt','nature_of_sundry','effect_gst_calculation'])
                  ->where('sale_id',$request->id)
                  ->get();
      $sundry_amount = 0;$roundOff = 0;$igst_status = 0;$cgst_status = 0;
      $CGST = 0;$SGST = 0;$IGST = 0;$gst_amount = 0;$TCS = 0;
      foreach ($sundry as $key => $value) {
         if($value->adjust_sale_amt=="Yes"){
            if($value->bill_sundry_type=="additive"){
               $sundry_amount = $sundry_amount + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $sundry_amount = $sundry_amount - $value->amount;
            }
         }
         if($value->nature_of_sundry=="ROUNDED OFF (+)" || $value->nature_of_sundry=="ROUNDED OFF (-)"){
            $roundOff = $value->amount;
         }
         if($value->nature_of_sundry=="IGST"){
            $IGST = $IGST + $value->amount;
            $igst_status = 1;
         }else if($value->nature_of_sundry=="CGST"){
            $CGST = $CGST + $value->amount;
            $SGST = $SGST + $value->amount;
            $cgst_status = 1;
         }   
         if($value->nature_of_sundry=="TCS"){
            $TCS = $value->amount;
         }
      }
      $total_item_price = SaleDescription::where('sale_id',$request->id)->sum('amount');
      $gst_amount = $gst_amount + $CGST + $SGST + $IGST + $TCS;
      $net_total = $total_item_price;
      $grand_total = $sale->total;
      $AssVal = $net_total + $sundry_amount;
      $OthChrg = $TCS + $roundOff;      
      $OthChrg = number_format((float)$OthChrg, 2, '.', '');   
      $SellerDtls = [];
      $DispDtls  = [];  
      $TotInvVal = $grand_total;
      $SellerDtls = array(
         "Gstin"=>$seller_Gstin,
         "LglNm"=>$seller_LglNm,
         "TrdNm"=>$seller_TrdNm,
         "Addr1"=>$seller_Addr1,
         "Addr2"=>null,
         "Loc"=>$seller_Loc,
         "Pin"=>(int)$seller_Pin,
         "Stcd"=>$seller_Stcd,
         "Ph"=>null,
         "Em"=>null,
      );
      $BuyerDtls = array(
         "Gstin"=>$sale->billing_gst,
         "LglNm"=>$sale->print_name,
         "TrdNm"=>$sale->print_name,
         "Pos"=>substr($sale->billing_gst,0,2),
         "Addr1"=>$sale->billing_address,
         "Addr2"=>null,
         "Loc"=>$sale->name,
         "Pin"=>(int)$sale->billing_pincode,
         "Stcd"=>substr($sale->billing_gst,0,2),
         "Ph"=>null,
         "Em"=>null
      );      
      $DispDtls = array(
         "Nm"=>$seller_LglNm,
         "Addr1"=>$seller_Addr1,
         "Addr2"=>null,
         "Loc"=>$seller_Loc,
         "Pin"=>(int)$seller_Pin,
         "Stcd"=>$seller_Stcd,
      );
      $ShipDtls = array(
         "Gstin"=> $shipp_gst,
         "LglNm"=> $shipp_name,
         "TrdNm"=>$shipp_name,
         "Addr1"=>$shipp_address,
         "Addr2"=>null,
         "Loc"=>$shipp_state,
         "Pin"=>(int)$shipp_pincode,
         "Stcd"=>substr($shipp_gst,0,2),
      );
      $ValDtls = array(
         "AssVal"=> (float)round($AssVal,2),
         "CgstVal"=> (float)round($CGST,2),
         "SgstVal"=> (float)round($SGST,2),
         "IgstVal"=>(float)round($IGST,2),
         "CesVal"=> null,
         "StCesVal"=> null,
         "Discount"=>0,
         "OthChrg"=>(float)round($OthChrg,2),
         "RndOffAmt"=>0,
         "TotInvVal"=>(float)round($TotInvVal,2),
         "TotInvValFc"=> null
      );
      $ItemList = [];
       
      $item_data = SaleDescription::join('manage_items','sale_descriptions.goods_discription','=','manage_items.id')
      ->join('units','manage_items.u_name','=','units.id')
                              ->where('sale_id',$request->id)
                              ->groupBy('hsn_code')
                              ->get( array(
                                DB::raw('SUM(qty) as tweight'),
                                DB::raw('SUM(amount) as tprice'),
                                DB::raw('hsn_code'),
                                DB::raw('manage_items.p_name as name'),
                                DB::raw('price'),
                                DB::raw('u_name'),
                                DB::raw('gst_rate'),
                                DB::raw('units.s_name as unit_name')
                              ));
      $i = 1;
      if(count($item_data)>0){
         foreach ($item_data as $key => $value) {
            $unit = $value->unit_name;
            $item_freight = 0;
            $item_discount = 0;
            $item_total = $value->tprice;            
            $average_freight = $sundry_amount / $total_item_price;
            $item_freight = $average_freight * $value->tprice;
            $item_total = $item_total + $item_freight;            
            $unit_price = $item_total / $value->tweight;
            $item_cgst = 0;$item_sgst = 0;$item_igst = 0;
            $itax = $value->gst_rate;
            $ctax = $itax/2;
            if(!empty($CGST) && $CGST!=0){
               $item_cgst = ($item_total*$ctax)/100;
               $item_sgst = ($item_total*$ctax)/100;
               $item_cgst = round($item_cgst,2,PHP_ROUND_HALF_UP);
               $item_sgst = round($item_sgst,2,PHP_ROUND_HALF_UP);
            }else if(!empty($IGST) && $IGST!=0){
               $item_igst = ($item_total*$itax)/100;
               $item_igst = round($item_igst,2,PHP_ROUND_HALF_UP);
            }
            $unit_price = round($unit_price,2);
            $item_total = round($item_total,2);
            $final_item_totol = $item_total + $item_cgst + $item_sgst + $item_igst;
            $final_item_totol = round($final_item_totol,2);
            array_push($ItemList,array(
               "SlNo"=> (String)$i,
               "IsServc"=> "N",
               "PrdDesc"=> $value->name,
               "HsnCd"=> $value->hsn_code,
               "Barcde"=> null,
               "BchDtls"=> array(
                 "Nm"=> "123",
                 "Expdt"=>null,
                 "wrDt"=>null
               ),
               "Qty"=> (float)$value->tweight,
               "FreeQty"=> null,
               "Unit"=> $unit,
               "UnitPrice"=>(float)round($unit_price,2),
               "TotAmt"=>(float)$item_total,
               "Discount"=> null,
               "PreTaxVal"=> null,
               "AssAmt"=> (float)$item_total,
               "GstRt"=> 18,
               "SgstAmt"=>(float)round($item_sgst,2),
               "IgstAmt"=>(float)round($item_igst,2),
               "CgstAmt"=>(float)round($item_cgst,2),
               "CesRt"=>null,
               "CesAmt"=>null,
               "CesNonAdvlAmt"=>null,
               "StateCesRt"=>null,
               "StateCesAmt"=>null,
               "StateCesNonAdvlAmt"=>null,
               "OthChrg"=>null,
               "TotItemVal"=>(float)round($final_item_totol,2),
               "OrdLineRef"=>null,
               "OrgCntry"=>null,
               "PrdSlNo"=> null,                     
            ));
            $i++;
         }
      }
      $RegRev = "N";
      $docno = $sale->voucher_no_prefix;
      $einvoice_requset = array(
         "Version"=>"1.1",
         "TranDtls"=>array(
            "TaxSch"=>"GST",
            "SupTyp"=>"B2B",
            "RegRev"=>$RegRev,
            "EcmGstin"=>null,
            "IgstOnIntra"=>"N"
         ),         
         "DocDtls"=>array(
            "Typ"=>"INV",
            "No"=>$docno,
            "Dt"=>date('d/m/Y',strtotime($sale->date))
         ),
         
         "SellerDtls"=>$SellerDtls,
         "BuyerDtls"=>$BuyerDtls,
         "DispDtls"=>$DispDtls,
         "ShipDtls"=>$ShipDtls,         
         "ValDtls"=>$ValDtls,
         "ItemList"=>$ItemList,     
      );
        // if($sale->company_id==18){
        //     echo "<pre>";
        //     echo json_encode($einvoice_requset);
        //     die;
        // }
          
      
      $etoken = DB::select(DB::raw("SELECT token FROM einvoice_tokens WHERE merchant_id='".$einvoice_company."' and STR_TO_DATE(token_expiry, '%Y-%m-%d %H:%i:%s')>=STR_TO_DATE('".date('Y-m-d H:i:s')."', '%Y-%m-%d %H:%i:%s')"));
      if($etoken){
         $token = $etoken[0]->token;
      }else{
         $token = $this->generateEinvoiceToken($einvoice_username,$einvoice_password,$einvoice_gst,$einvoice_company);
         if($token=='0'){
            $response = [
                           'success' => false,
                           'data'    => "",
                           'message' => "Token Not Generating ",
                        ];
            return response()->json($response, 200);
         }
      }      
      $curl = curl_init();
      curl_setopt_array($curl, array(
         CURLOPT_URL => 'https://api.mastergst.com/einvoice/type/GENERATE/version/V1_03?email=pram92500@gmail.com',
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS =>json_encode($einvoice_requset),
         CURLOPT_HTTPHEADER => array(
            'ip_address: 162.241.85.89',
            'client_id: 964759f3-5071-4e4f-a03c-88c56aa8bd6f',
            'client_secret: 35565aa5-3d2c-4507-b81f-3c3effd00238',
            'username:'.$einvoice_username,
            'auth-token:'.$token,
            'gstin:'.$einvoice_gst,
            'Content-Type: application/json'
         ),
      ));
      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);
      if($response){
         $result = json_decode($response);
         if(isset($result->status_cd) && $result->status_cd=='1'){
            $invoice_update = Sales::find($request->id);
            $invoice_update->einvoice_response = json_encode($result->data);
            $invoice_update->e_invoice_status = 1;
            $invoice_update->einvoice_by = Session::get('user_id');
            if($invoice_update->save()){
               $response = [
                              'success' => true,
                              'data'    => "",
                              'message' => "E-Invoice Generated Successfully",
                           ];
               return response()->json($response, 200);
            }else{
               $response = [
                              'success' => false,
                              'data'    => "",
                              'message' => "E Invoice Filed but Merchant portal not updated",
                           ];
               return response()->json($response, 200);
            }
         }else{
            if(isset($result->status_desc)){
               $error = json_decode($result->status_desc);
               $response = [
                              'success' => false,
                              'data'    => "",
                              'message' => $error[0]->ErrorMessage,
                           ];
               return response()->json($response, 200);
            }
         }
      }
   }
   public function generateEwayBill(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = Sales::join('accounts','sales.party','=','accounts.id')
                    ->join('companies','sales.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('sales.id',$request->id)
                    ->first(['sales.*','accounts.account_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);          
      if($sale->gst_config_type=="multiple_gst"){
         $gst_info = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'multiple_gst'])
                           ->get();
         foreach ($gst_info as $key => $value) {
            if($value->series==$sale->series_no){
               $st = State::select('name')->where('id',$value->state)->first();
               $fromGstin = $value->gst_no;
               $fromTrdName = $sale->company_name;
               $fromAddr1 = $value->address;
               $fromAddr2 = $value->address;
               $fromPlace = $st->name;
               $fromPincode = $value->pincode;
               $actFromStateCode = substr($value->gst_no,0,2);
               $fromStateCode = substr($value->gst_no,0,2); 
               $einvoice_username = $value->einvoice_username; 
               $einvoice_password = $value->einvoice_password;
               $einvoice_gst = $value->gst_no;
               $einvoice_company = $value->id;
               break;
            }else{
               $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$value->id,'branch_series'=>$sale->series_no])
                           ->first();
               if($branch){
                  $st = State::select('name')->where('id',$value->state)->first();
                  $fromGstin = $branch->gst_number;
                  $fromTrdName = $sale->company_name;
                  $fromAddr1 = $branch->branch_address;
                  $fromAddr2 = $branch->branch_address;
                  $fromPlace = $st->name;
                  $fromPincode = $branch->branch_pincode;
                  $actFromStateCode = substr($branch->gst_number,0,2);
                  $fromStateCode = substr($branch->gst_number,0,2);
                  if($value->einvoice==1){
                      $einvoice_username = $value->einvoice_username; 
                        $einvoice_password = $value->einvoice_password;
                  }else{
                      $einvoice_username = $value->ewaybill_username; 
                      $einvoice_password = $value->ewaybill_password;
                  }
                  
                  $einvoice_gst = $branch->gst_number;
                  $einvoice_company = $value->id;
                  break;
               }
            }
         }
      }else if($sale->gst_config_type=="single_gst"){
         $gst_info = DB::table('gst_settings')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'single_gst'])
                           ->first();
         if($gst_info->series==$sale->series_no){
            $st = State::select('name')->where('id',$gst_info->state)->first();
            $fromGstin = $gst_info->gst_no;
            $fromTrdName = $sale->company_name;
            $fromAddr1 = $gst_info->address;
            $fromAddr2 = $gst_info->address;
            $fromPlace = $st->name;
            $fromPincode = $gst_info->pincode;
            $actFromStateCode = substr($gst_info->gst_no,0,2);
            $fromStateCode = substr($gst_info->gst_no,0,2);
         }else{
            $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$gst_info->id,'branch_series'=>$sale->series_no])
                           ->first();
            if($branch){
               $st = State::select('name')->where('id',$gst_info->state)->first();
               $fromGstin = $gst_info->gst_no;
               $fromTrdName = $sale->company_name;
               $fromAddr1 = $branch->branch_address;
               $fromAddr2 = $branch->branch_address;
               $fromPlace = $st->name;
               $fromPincode = $branch->branch_pincode;
               $actFromStateCode = substr($branch->gst_number,0,2);
               $fromStateCode = substr($branch->gst_number,0,2);
            }
         }
         $einvoice_company = $gst_info->id;
         if($gst_info->einvoice==1){
                      $einvoice_username = $gst_info->einvoice_username; 
                        $einvoice_password = $gst_info->einvoice_password;
                  }else{
                      $einvoice_username = $gst_info->ewaybill_username; 
                      $einvoice_password = $gst_info->ewaybill_password;
                  }
        //  $einvoice_username = $gst_info->einvoice_username; 
        //  $einvoice_password = $gst_info->einvoice_password;
         $einvoice_gst = $gst_info->gst_no;
      }
      if($einvoice_username=="" || $einvoice_password==""){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"UserName,Password Required."
         );
         return json_encode($res);
      }
      $etoken = DB::select(DB::raw("SELECT token FROM einvoice_tokens WHERE merchant_id='".$einvoice_company."' and STR_TO_DATE(token_expiry, '%Y-%m-%d %H:%i:%s')>=STR_TO_DATE('".date('Y-m-d H:i:s')."', '%Y-%m-%d %H:%i:%s')"));
      if($etoken){
         $token = $etoken[0]->token;
      }else{
         $token = $this->generateEinvoiceToken($einvoice_username,$einvoice_password,$einvoice_gst,$einvoice_company);
         if($token=='0'){
            $response = [
                           'success' => false,
                           'data'    => "",
                           'message' => "Token Not Generating ",
                        ];
            return response()->json($response, 200);
         }
      }
      if($sale->e_invoice_status==1 && !empty($sale->einvoice_response)){
         $einvoice_data = json_decode($sale->einvoice_response);
         $Irn = $einvoice_data->Irn;
         $eway_bill_request = array(
            "Irn"=>$Irn,
            "Distance"=>(int)$request->distance,
            "TransMode"=>"1",
            "TransId"=>!empty($request->transporter_id) ? $request->transporter_id : null,
            "TransName"=>null,
            "TransDocDt"=>null,
            "TransDocNo"=>null,
            "VehNo"=>$request->vehicle_number,
            "VehType"=>"R"
         );
         // print_r($eway_bill_request);die;
         $curl = curl_init();
         curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mastergst.com/einvoice/type/GENERATE_EWAYBILL/version/V1_03?email=pram92500@gmail.com',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($eway_bill_request),
            CURLOPT_HTTPHEADER => array(
               'ip_address: 162.241.85.89',
               'client_id: 964759f3-5071-4e4f-a03c-88c56aa8bd6f',
               'client_secret: 35565aa5-3d2c-4507-b81f-3c3effd00238',
               'username:'.$einvoice_username,
               'auth-token:'.$token,
               'gstin:'.$einvoice_gst,
               'Content-Type: application/json'
            ),
         ));
         $response = curl_exec($curl);
         curl_close($curl);
         if($response){
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd=='1'){
               $data_array = [];
               $data_array['ewayBillNo'] = $result->data->EwbNo;
               $data_array['ewayBillDate'] = $result->data->EwbDt;
               $data_array['validUpto'] = $result->data->EwbValidTill;
               $invoice_update = Sales::find($request->id);
               $invoice_update->eway_bill_response = json_encode($data_array);
               $invoice_update->e_waybill_status = 1;
               $invoice_update->e_waybill_distance = $request->distance;
               $invoice_update->eway_bill_by = Session::get('user_id');
               if($invoice_update->save()){
                  $response = [
                     'success' => true,
                     'data'    => "",
                     'message' => "Eway Bill Generated Successfully",
                  ];
                  return response()->json($response, 200);
               }else{
                  $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => "Eway Bill Filed but Merchant portal not updated",
                  ];
                  return response()->json($response, 200);
               }            
            }else{
               if(isset($result->status_desc)){
                  $error = json_decode($result->status_desc);
                  $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => $error[0]->ErrorMessage,
                  ];
                  return response()->json($response, 200);
               }
            }
         }
      }else{
          $curl = curl_init();
          curl_setopt_array($curl, array(
             CURLOPT_URL => 'https://api.mastergst.com/ewaybillapi/v1.03/authenticate?email=pram92500@gmail.com&username='.trim($einvoice_username).'&password='.trim(decrypt($einvoice_password)),
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => '',
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 0,
             CURLOPT_FOLLOWLOCATION => true,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => 'GET',
             //CURLOPT_POSTFIELDS =>json_encode($eway_bill_request),
             CURLOPT_HTTPHEADER => array(
                'ip_address: 162.241.85.89',
                'client_id: 627652fe-675c-484e-8768-20a874d6c864',
                'client_secret: 2d73d024-4b08-4627-a219-99f027bcf77f',            
                'gstin: '.trim($einvoice_gst),
                'Content-Type: application/json'
             ),
          ));
          $response = curl_exec($curl);
          curl_close($curl);
          if($response){
             $result = json_decode($response);         
             if(isset($result->status_cd) && $result->status_cd=='1'){
                //echo json_encode(array("status"=>1,"message"=>"Token Generate Successfully"));
             }else{
                 echo "<pre>";print_r($result);die;
                $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => "Token Issue",
                  ];
                  return response()->json($response, 200);
             }
          }
         $CGST = null;$SGST = null;$IGST = null;$TCS = 0;
         $sundry = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                     ->select(['sale_sundries.rate','sale_sundries.amount','bill_sundry_type','adjust_sale_amt','nature_of_sundry','effect_gst_calculation'])
                     ->where('sale_id',$request->id)
                     ->get();
         $sundry_amount = 0;$roundOff = 0;$igst_status = 0;$cgst_status = 0;
         $CGST = 0;$SGST = 0;$IGST = 0;$gst_amount = 0;$TCS = 0;
         //echo "<pre>";print_r($sundry->toArray());
         foreach ($sundry as $key => $value) {
            if($value->adjust_sale_amt=="Yes"){
               if($value->bill_sundry_type=="additive"){
                  $sundry_amount = $sundry_amount + $value->amount;
               }else if($value->bill_sundry_type=="subtractive"){
                  $sundry_amount = $sundry_amount - $value->amount;
               }
            }
            if($value->nature_of_sundry=="ROUNDED OFF (+)" || $value->nature_of_sundry=="ROUNDED OFF (-)"){
               $roundOff = $value->amount;
            }
            if($value->nature_of_sundry=="IGST"){
               $IGST = $IGST + $value->amount;
               $igst_status = 1;
            }else if($value->nature_of_sundry=="CGST"){
               $CGST = $CGST + $value->amount;
               $SGST = $SGST + $value->amount;
               $cgst_status = 1;
            }   
            if($value->nature_of_sundry=="TCS"){
               $TCS = $value->amount;
            }
         }
        
         $gst_amount = $gst_amount + $CGST + $SGST + $IGST + $TCS;
         $net_total = SaleDescription::where('sale_id',$request->id)->sum('amount');
         $grand_total = $sale->total;
         $AssVal = $net_total + $sundry_amount;
         $OthChrg = $TCS + $roundOff;
         
         $OthChrg = number_format((float)$OthChrg, 2, '.', '');   
         $SellerDtls = [];
         $DispDtls  = [];  
         $TotInvVal = $grand_total;
         if(!empty($sale->shipping_name) && !empty($sale->shipping_name)){
            $transactionType = 2;
            $shipp_name = $sale->shipping_name;
            $shipp_address = $sale->shipping_address;  
            $shipp_name_new = $sale->shipping_name;
            $shipp_gst_state = $sale->shipping_gst;
            $shipp_gst_state_billtoshippto = $sale->shipping_gst;         
            $shipp_state = $sale->shipping_state;
            $shipp_city = $sale->shipping_address;
            $shipp_pincode = $sale->shipping_pincode;              
         }else{
            $transactionType = 1;
            $shipp_name = $sale->account_name;
            $shipp_address = $sale->address.','.$sale->name.','.$sale->pin_code;
            $shipp_name_new = ""; 
            $shipp_name1 = $sale->account_name;
            $shipp_address1 = $sale->address.','.$sale->name.','.$sale->pin_code;
            $shipp_name_ne1w = ""; 
            $shipp_gst_state = $sale->billing_gst;
            $shipp_gst_state_billtoshippto = "";         
            $shipp_state = $sale->name;
            $shipp_state1 = $sale->name;
            $shipp_city = $sale->address;
            $shipp_city1 = $sale->address;
            $shipp_pincode = $sale->pin_code;
            $shipp_pincode1 = $sale->pin_code;
         }
         $ItemList = [];
         $total_item_price = SaleDescription::where('sale_id',$request->id)->sum('amount'); 
         $item_data = SaleDescription::join('manage_items','sale_descriptions.goods_discription','=','manage_items.id')
                                 ->where('sale_id',$request->id)
                                 ->groupBy('hsn_code')
                                 ->get( array(
                                 DB::raw('SUM(qty) as tweight'),
                                 DB::raw('SUM(amount) as tprice'),
                                 DB::raw('hsn_code'),
                                    DB::raw('manage_items.p_name as name'),
                                 DB::raw('price'),
                                 DB::raw('u_name'),
                                 DB::raw('gst_rate')
                                 ));
         $i = 1;
         if(count($item_data)>0){
            foreach ($item_data as $key => $value) {
               $unit = $value->u_name;
               $qtyUnit = Units::select('s_name')->where('id',$unit)->first();
               $item_freight = 0;
               $item_discount = 0;
               $item_total = $value->tprice;            
               $average_freight = $sundry_amount / $total_item_price;
               $item_freight = $average_freight * $value->tprice;
               $item_total = $item_total + $item_freight;            
               $unit_price = $item_total / $value->tweight;
               $item_cgst = 0;$item_sgst = 0;$item_igst = 0;
               $cgst_rate = 0;$sgst_rate = 0;$igst_rate = 0;
               $itax = $value->gst_rate;
               $ctax = $itax/2;
               if(!empty($CGST) && $CGST!=0){
                  $item_cgst = ($item_total*$ctax)/100;
                  $item_sgst = ($item_total*$ctax)/100;
                  $item_cgst = round($item_cgst,2,PHP_ROUND_HALF_UP);
                  $item_sgst = round($item_sgst,2,PHP_ROUND_HALF_UP);
                  $cgst_rate = $ctax;
                    $sgst_rate = $ctax;
               }else if(!empty($IGST) && $IGST!=0){
                  $igst_rate = $itax;
                  $item_igst = ($item_total*$itax)/100;
                  $item_igst = round($item_igst,2,PHP_ROUND_HALF_UP);
               }
               $unit_price = round($unit_price,2);
               $item_total = round($item_total,2);
               $final_item_totol = $item_total + $item_cgst + $item_sgst + $item_igst;
               $final_item_totol = round($final_item_totol,2);
              
               array_push($ItemList,array(
                  "productName"=>$value->name,
                  "productDesc"=>$value->name,
                  "hsnCode"=>(int)$value->hsn_code,
                  "quantity"=>(float)$value->tweight,
                  "qtyUnit"=>$qtyUnit->s_name,
                  "taxableAmount"=>(float)$item_total,
                  "sgstRate"=>$cgst_rate,
                  "cgstRate"=>$sgst_rate,
                  "igstRate"=>$igst_rate,
                  "cessRate"=> 0                   
               ));
               $i++;
            }
         }
         $shipp_gst = $sale->billing_gst;
         $docType = "INV";
         $subSupplyType = "1";
         $supplyType = "O";
         $docNo = $sale->voucher_no_prefix;
        
         if($transactionType==1){
            $eway_bill_request = array(
               "supplyType"=>$supplyType,
               "subSupplyType"=>$subSupplyType,
               "subSupplyDesc"=>"",
               "docType"=>$docType,
               "docNo"=>$docNo,
               "docDate"=>date('d/m/Y',strtotime($sale->date)),
               "fromGstin"=>$fromGstin,
               "fromTrdName"=>$fromTrdName,
               "fromAddr1"=>$fromAddr1,
               "fromAddr2"=>$fromAddr2,
               "fromPlace"=>$fromPlace,
               "actFromStateCode"=>(int)$actFromStateCode,
               "fromPincode"=>(int)$fromPincode,
               "fromStateCode"=>(int)$fromStateCode,
               "toGstin"=>$shipp_gst,
               "toTrdName"=>$sale->account_name,
               "toAddr2"=>$shipp_city.','.$shipp_state.','.$shipp_pincode,
               "toPlace"=>$shipp_state,
               "toPincode"=>(int)$shipp_pincode,
               "actToStateCode"=>(int)substr($shipp_gst_state,0,2),
               "toStateCode"=>(int)substr($shipp_gst,0,2),
               "transactionType"=>$transactionType,
               "totalValue"=>(float)$AssVal,
               "cgstValue"=>(float)$CGST,
               "sgstValue"=>(float)$SGST,
               "igstValue"=>(float)$IGST,
               "cessValue"=>0,
               "cessNonAdvolValue"=>0,
               "totInvValue"=>(float)$TotInvVal,
               "transMode"=>"1",
               "transDistance"=>trim($request->distance),
               "transporterName"=>"",
               //"transporterId"=>!empty($request->transporter_id) ? $request->transporter_id : "",
               "transDocNo"=>"",
               "transDocDate"=>"",
               "vehicleNo"=>$request->vehicle_number,
               "vehicleType"=>"R",
               "itemList"=>$ItemList
            );
         }else if($transactionType==2){
            $eway_bill_request = array(
               "supplyType"=>$supplyType,
               "subSupplyType"=>$subSupplyType,
               "subSupplyDesc"=>"",
               "docType"=>$docType,
               "docNo"=>$docNo,
               "docDate"=>date('d/m/Y',strtotime($sale->date)),
               "fromGstin"=>$godown_data['gowdown_gst'],
               "fromTrdName"=>$fromGstin,
               "fromAddr1"=>$fromAddr1,
               "fromAddr2"=>$fromAddr2,
               "fromPlace"=>$fromPlace,
               "actFromStateCode"=>(int)$actFromStateCode,
               "fromPincode"=>(int)$fromPincode,
               "fromStateCode"=>(int)$fromStateCode,
               "toGstin"=>$shipp_gst,
               "toTrdName"=>$sale->account_name,
               "toAddr2"=>$shipp_address.','.$shipp_city.','.$shipp_state.','.$shipp_pincode,
               "toPlace"=>$shipp_state,
               "toPincode"=>(int)$shipp_pincode,
               "actToStateCode"=>(int)substr($shipp_gst_state,0,2),
               "toStateCode"=>(int)substr($shipp_gst,0,2),
               "transactionType"=>$transactionType,
               "shipToGSTIN"=>$shipp_gst_state_billtoshippto,
               "shipToTradeName"=>$shipp_name,
               "totalValue"=>(float)$AssVal,
               "cgstValue"=>(float)$CGST,
               "sgstValue"=>(float)$SGST,
               "igstValue"=>(float)$IGST,
               "cessValue"=>0,
               "cessNonAdvolValue"=>0,
               "totInvValue"=>(float)$TotInvVal,
               "transMode"=>"1",
               "transDistance"=>trim($request->distance),
               "transporterName"=>"",
               //"transporterId"=>!empty($request->transporter_id) ? $request->transporter_id : "",
               "transDocNo"=>"",
               "transDocDate"=>"",
               "vehicleNo"=>$request->vehicle_number,
               "vehicleType"=>"R",
               "itemList"=>$ItemList
            );
         }
        //  echo "<pre>";
        //  print_r(json_encode($eway_bill_request));
        //  die;
        //  $etoken = DB::select(DB::raw("SELECT token FROM einvoice_tokens WHERE merchant_id='".$einvoice_company."' and STR_TO_DATE(token_expiry, '%Y-%m-%d %H:%i:%s')>=STR_TO_DATE('".date('Y-m-d H:i:s')."', '%Y-%m-%d %H:%i:%s')"));
        //  if($etoken){
        //     $token = $etoken[0]->token;
        //  }else{
        //      echo "Token.....1";
        //     $token = $this->generateEinvoiceToken($einvoice_username,$einvoice_password,$einvoice_gst,$einvoice_company);
        //     echo "Token.....3";
        //     if($token=='0'){
        //         echo "Token.....2";
        //       $response = [
        //                       'success' => false,
        //                       'data'    => "",
        //                       'message' => "Token Not Generating ",
        //                   ];
        //       return response()->json($response, 200);
        //     }
        //  }
         
         $curl = curl_init();
         curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.mastergst.com/ewaybillapi/v1.03/ewayapi/genewaybill?email=pram92500@gmail.com',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($eway_bill_request),
            CURLOPT_HTTPHEADER => array(
               'ip_address: 162.241.85.89',
               'client_id: 627652fe-675c-484e-8768-20a874d6c864',
               'client_secret: 2d73d024-4b08-4627-a219-99f027bcf77f',
               'gstin:'.$einvoice_gst,
               'Content-Type: application/json'
            ),
         ));
         $response = curl_exec($curl);
         if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
                echo "<pre>";echo "---";print_r($error_msg);die;
            }

        // echo "<pre>";print_r($response);die;
         curl_close($curl);
         if($response){
            $result = json_decode($response);
            if(isset($result->status_cd) && $result->status_cd=='1'){
               $data_array = [];
               $data_array['ewayBillNo'] = $result->data->EwbNo;
               $data_array['ewayBillDate'] = $result->data->EwbDt;
               $data_array['validUpto'] = $result->data->EwbValidTill;
               $invoice_update = Sales::find($request->id);
               $invoice_update->eway_bill_response = json_encode($data_array);
               $invoice_update->e_waybill_status = 1;
               $invoice_update->eway_bill_by = Session::get('user_id');
               if($invoice_update->save()){
                  $response = [
                     'success' => true,
                     'data'    => "",
                     'message' => "Eway Bill Generated Successfully",
                  ];
                  return response()->json($response, 200);
               }else{
                  $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => "Eway Bill Filed but Merchant portal not updated",
                  ];
                  return response()->json($response, 200);
               }            
            }else{
               if(isset($result->status_desc)){
                  $error = json_decode($result->status_desc);
                  $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => $error[0]->ErrorMessage,
                  ];
                  return response()->json($response, 200);
               }
            }
         }
      }
      die;
      
   }
   public function generateEinvoiceToken($username,$password,$gstin,$einvoice_company){
      $curl = curl_init();
      curl_setopt_array($curl, array(
         CURLOPT_URL => 'https://api.mastergst.com/einvoice/authenticate?email=pram92500@gmail.com',
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'GET',
         CURLOPT_HTTPHEADER => array(
            'username:'.$username,
            'password:'.decrypt($password),
            'ip_address: 162.241.85.89',
            'client_id: 964759f3-5071-4e4f-a03c-88c56aa8bd6f',
            'client_secret: 35565aa5-3d2c-4507-b81f-3c3effd00238',
            'gstin:'.$gstin
         ),
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      if($response){
         $result = json_decode($response);
         if(isset($result->status_cd) && $result->status_cd=='Sucess'){
            $token_expiry = $result->data->TokenExpiry;
            $token = $result->data->AuthToken;
            $einvoice = new EinvoiceToken();
            $einvoice->token = $token;
            $einvoice->token_expiry = $token_expiry;
            $einvoice->merchant_id = $einvoice_company;
            //$einvoice->created_by = Session::get('user_id');
            $einvoice->created_at = date('d-m-Y H:i:s');
            if($einvoice->save()){
               return $token;
            }else{
               return 0;
            }
         }else{
            return 0;
         }
      } 
   }
   public function saleInvoiceConfiguration(Request $request){
      $configuration = SaleInvoiceConfiguration::with(['terms'])->where('company_id',Session::get('user_company_id'))->first();
      $bank = Bank::where('company_id',Session::get('user_company_id'))->where('status','1')->where('delete','0')->get();
      return view('sale_invoice_configuration',['configuration'=>$configuration,"banks"=>$bank]);
   }
   public function addSaleInvoiceConfiguration(Request $request){ 
      $check_conf = SaleInvoiceConfiguration::where('company_id',Session::get('user_company_id'))->first();
      if(!$check_conf){
         if($request->company_logo_status==1){
            $logo = "logo_".time().'.'.$request->company_logo->extension();
            $request->company_logo->move(public_path('images'), $logo);
         }else{
            $logo = "";
         }
         if($request->signature_status == 1 && $request->signature){
            $signature = "signature_".time().'.'.$request->signature->extension();
            $request->signature->move(public_path('images'), $signature);
         }else{
            $signature = "";
         }
         $logo_position_left = 0;
         if($request->logo_position_left){
            $logo_position_left = 1;
         }
         $logo_position_right = 0;
         if($request->logo_position_right){
            $logo_position_right = 1;
         }
         $conf = new SaleInvoiceConfiguration();         
         $conf->company_logo_status = $request->company_logo_status;
         $conf->company_logo = $logo;
         $conf->logo_position_left = $logo_position_left;
         $conf->logo_position_right = $logo_position_right;
         $conf->bank_detail_status = $request->bank_detail_status;
         $conf->bank_name = $request->bank_name;
         $conf->term_status = $request->term_status;
         $conf->invoice_header_text = $request->invoice_header_text;
         $conf->purchase_order_status = $request->purchase_order_status;
         $conf->purchase_order_info_show_in_ledger = $request->purchase_order_info_show_in_ledger;
         $conf->show_description = $request->show_description ? 1 : 0;
         $conf->show_item_name = $request->show_item_name ? 1 : 0;
         $conf->company_name_color = $request->company_name_color;
         $conf->company_name_font_size = $request->company_name_font_size;
         $conf->address_color = $request->address_color;
         $conf->signature_status = $request->signature_status;
         $conf->signature = $signature;
         $conf->company_id = Session::get('user_company_id');
         $conf->created_at = Carbon::now();
         if($conf->save()){
            foreach ($request->terms as $key => $value) {
               if(!empty($value)){
                  $term = new SaleInvoiceTermCondition();
                  $term->parent_id = $conf->id;
                  $term->term = $value;
                  $term->company_id = Session::get('user_company_id');
                  $term->save();
               }
               
            }            
         }
      }else{
         $logo = $check_conf->company_logo;
         $signature = $check_conf->signature;
         if($request->company_logo && !empty($request->company_logo)){
            $logo = "logo_".time().'.'.$request->company_logo->extension();
            $request->company_logo->move(public_path('images'), $logo);
         }
         if($request->signature && !empty($request->signature)){
            $signature = "signature_".time().'.'.$request->signature->extension();
            $request->signature->move(public_path('images'), $signature);
         }
         if($request->signature_status == 1 && $request->signature){
            // upload new signature
            $signature = "signature_".time().'.'.$request->signature->extension();
            $request->signature->move(public_path('images'), $signature);
        }
         if($request->company_logo_status==0){
            $logo = "";
         }
         $logo_position_left = 0;
         if($request->logo_position_left){
            $logo_position_left = 1;
         }
         $logo_position_right = 0;
         if($request->logo_position_right){
            $logo_position_right = 1;
         }
         $conf = SaleInvoiceConfiguration::find($check_conf->id);
         $conf->company_logo_status = $request->company_logo_status;
         $conf->company_logo = $logo;
         $conf->logo_position_left = $logo_position_left;
         $conf->logo_position_right = $logo_position_right;
         $conf->bank_detail_status = $request->bank_detail_status;
         $conf->bank_name = $request->bank_name;
         $conf->term_status = $request->term_status;
         $conf->invoice_header_text = $request->invoice_header_text;
         $conf->purchase_order_status = $request->purchase_order_status;
         $conf->purchase_order_info_show_in_ledger = $request->purchase_order_info_show_in_ledger;
         $conf->show_description = $request->show_description ? 1 : 0;
         $conf->show_item_name = $request->show_item_name ? 1 : 0;
         $conf->company_name_font_size = $request->company_name_font_size;
         $conf->company_name_color = $request->company_name_color;
         $conf->address_color = $request->address_color;
         $conf->signature_status = $request->signature_status;
         $conf->signature = $signature;
         $conf->updated_at = Carbon::now();
         if($conf->save()){
            SaleInvoiceTermCondition::where('company_id',Session::get('user_company_id'))->delete();
            foreach ($request->terms as $key => $value) {
               if(!empty($value)){
                  $term = new SaleInvoiceTermCondition();
                  $term->parent_id = $conf->id;
                  $term->term = $value;
                  $term->company_id = Session::get('user_company_id');
                  $term->save();
               }
               
            }
         }
      }
      return redirect('sale-invoice-configuration')->withSuccess('Sale Invoice Configuration Added Successfully!');
   }
   public function cancelEwayBill(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = Sales::join('companies','sales.company_id','=','companies.id')
                    ->where('sales.id',$request->id)
                    ->first(['sales.*','companies.gst_config_type']);          
      if($sale->gst_config_type=="multiple_gst"){
         $gst_info = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'multiple_gst'])
                           ->get();
         foreach ($gst_info as $key => $value) {
            if($value->series==$sale->series_no){               
               $einvoice_username = $value->einvoice_username; 
               $einvoice_password = $value->einvoice_password;
               $einvoice_gst = $value->gst_no;
               $einvoice_company = $value->id;
               break;
            }else{
               $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$value->id,'branch_series'=>$sale->series_no])
                           ->first();
               if($branch){
                  $einvoice_username = $value->einvoice_username; 
                  $einvoice_password = $value->einvoice_password;
                  $einvoice_gst = $branch->gst_number;
                  $einvoice_company = $value->id;
                  break;
               }
            }
         }
      }else if($sale->gst_config_type=="single_gst"){ 
          $gst_info = DB::table('gst_settings')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'single_gst'])
                           ->first();
         $einvoice_company = $gst_info->id;
         $einvoice_username = $gst_info->einvoice_username; 
         $einvoice_password = $gst_info->einvoice_password;
         $einvoice_gst = $gst_info->gst_no;
      }
      if($einvoice_username=="" || $einvoice_password==""){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"UserName,Password Required."
         );
         return json_encode($res);
      }
      
      $curl = curl_init();
      curl_setopt_array($curl, array(
         CURLOPT_URL => 'https://api.mastergst.com/ewaybillapi/v1.03/authenticate?email=pram92500@gmail.com&username='.$einvoice_username.'&password='.decrypt($einvoice_password),
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'GET',
         CURLOPT_HTTPHEADER => array(
            'ip_address: 162.241.85.89',
            'client_id: 627652fe-675c-484e-8768-20a874d6c864',
            'client_secret: 2d73d024-4b08-4627-a219-99f027bcf77f',            
            'gstin: '.$einvoice_gst,
            'Content-Type: application/json'
         ),
      ));
      $ress = curl_exec($curl);
      
      curl_close($curl);
      $ewaybill_data = json_decode($sale->eway_bill_response);
      $ewayBillNo = $ewaybill_data->ewayBillNo;
      $cancel_eway_request = array(
         "ewbNo"=>$ewayBillNo,
         "cancelRsnCode"=>1,
         "cancelRmrk"=>"Wrong entry"
      );
      $curl = curl_init();
      curl_setopt_array($curl, array(
         CURLOPT_URL => 'https://api.mastergst.com/ewaybillapi/v1.03/ewayapi/canewb?email=pram92500@gmail.com',
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS =>json_encode($cancel_eway_request),
         CURLOPT_HTTPHEADER => array(
            'ip_address: 162.241.85.89',
            'client_id: 627652fe-675c-484e-8768-20a874d6c864',
            'client_secret:   2d73d024-4b08-4627-a219-99f027bcf77f',
            'gstin:'.$einvoice_gst,
            'Content-Type: application/json'
         ),
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      if($response){
         $result = json_decode($response);
         if(isset($result->status_cd) && $result->status_cd=='1'){
            Sales::where('id',$request->id)->update(['e_waybill_status'=>0,'eway_bill_response'=>'']);
            $response = [
               'success' => true,
               'data'    => "",
               'message' => "Eway Bill Cancelled Successfully",
            ];
            return response()->json($response, 200);
         }else{
            if(isset($result->error)){
               $error = json_decode($result->error->message);
               if($error->errorCodes=='238'){
                  $response = [
                     'success' => false,
                     'data'    => "",
                     'message' => "Please Generate Token",
                  ];
                  return response()->json($response, 200);
               }else{
                  if(isset($error->errorCodes)){
                     $response = [
                        'success' => false,
                        'data'    => "",
                        'message' => $error->errorCodes,
                     ];
                     return response()->json($response, 200);
                  }else{
                     $response = [
                        'success' => false,
                        'data'    => "",
                        'message' => $error,
                     ];
                     return response()->json($response, 200);
                  }                  
               }               
            }
         }
      }
   }
   public function cancelEinvoice(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = Sales::join('companies','sales.company_id','=','companies.id')
                    ->where('sales.id',$request->id)
                    ->first(['sales.*','companies.gst_config_type']);          
      if($sale->gst_config_type=="multiple_gst"){
         $gst_info = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'multiple_gst'])
                           ->get();
         foreach ($gst_info as $key => $value) {
            if($value->series==$sale->series_no){               
               $einvoice_username = $value->einvoice_username; 
               $einvoice_password = $value->einvoice_password;
               $einvoice_gst = $value->gst_no;
               $einvoice_company = $value->id;
               break;
            }else{
               $branch = GstBranch::select('id','gst_number','branch_address','branch_pincode')
                           ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_setting_id'=>$value->id,'branch_series'=>$sale->series_no])
                           ->first();
               if($branch){
                  $einvoice_username = $value->einvoice_username; 
                  $einvoice_password = $value->einvoice_password;
                  $einvoice_gst = $branch->gst_number;
                  $einvoice_company = $value->id;
                  break;
               }
            }
         }
      }else if($sale->gst_config_type=="single_gst"){ 
         $gst_info = DB::table('gst_settings')
                           ->where(['company_id' => $sale->company_id, 'gst_type' => 'single_gst'])
                           ->first();        
         $einvoice_company = $gst_info->id;
         $einvoice_username = $gst_info->einvoice_username; 
         $einvoice_password = $gst_info->einvoice_password;
         $einvoice_gst = $gst_info->gst_no;
      }
      if($einvoice_username=="" || $einvoice_password==""){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"UserName,Password Required."
         );
         return json_encode($res);
      }
      $etoken = DB::select(DB::raw("SELECT token FROM einvoice_tokens WHERE merchant_id='".$einvoice_company."' and STR_TO_DATE(token_expiry, '%Y-%m-%d %H:%i:%s')>=STR_TO_DATE('".date('Y-m-d H:i:s')."', '%Y-%m-%d %H:%i:%s')"));
      if($etoken){
         $token = $etoken[0]->token;
      }else{
         $token = $this->generateEinvoiceToken($einvoice_username,$einvoice_password,$einvoice_gst,$einvoice_company);
         if($token=='0'){
            $response = [
                           'success' => false,
                           'data'    => "",
                           'message' => "Token Not Generating ",
                        ];
            return response()->json($response, 200);
         }
      }
      $einvoice_response = json_decode($sale->einvoice_response);
      // print_r($einvoice_response->Irn);die;
      $Irn = $einvoice_response->Irn;
      $cancel_einvoice_request = array(
         "Irn"=>$Irn,
         "CnlRsn"=>"1",
         "CnlRem"=>"Wrong entry"
      );
      $curl = curl_init();
      curl_setopt_array($curl, array(
         CURLOPT_URL => 'https://api.mastergst.com/einvoice/type/CANCEL/version/V1_03?email=pram92500@gmail.com',
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => '',
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 0,
         CURLOPT_FOLLOWLOCATION => true,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => 'POST',
         CURLOPT_POSTFIELDS =>json_encode($cancel_einvoice_request),
         CURLOPT_HTTPHEADER => array(
            'ip_address: 162.241.cancel_einvoice_request85.89',
            'client_id: 964759f3-5071-4e4f-a03c-88c56aa8bd6f',
            'client_secret: 35565aa5-3d2c-4507-b81f-3c3effd00238',
            'username:'.$einvoice_username,
            'auth-token:'.$token,
            'gstin:'.$einvoice_gst,
            'Content-Type: application/json'
         ),
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      if($response){
         $result = json_decode($response); 
         if(isset($result->status_cd) && $result->status_cd=='1'){
            Sales::where('id',$request->id)->update(['e_invoice_status'=>0,'status'=>'2','einvoice_response'=>'','total'=>'0']);
            SaleDescription::where('sale_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);            
            SaleSundry::where('sale_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',1)
                        ->where('entry_type_id',$request->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemLedger::where('source',1)
                     ->where('source_id',$request->id)
                     ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemAverageDetail::where('sale_id',$request->id)
                     ->where('type','SALE')
                     ->delete();         
            $desc = SaleDescription::where('sale_id',$request->id)
                                 ->get();
            foreach ($desc as $key => $value) {
               CommonHelper::RewriteItemAverageByItem($sale->date,$value->goods_discription,$sale->series_no);
            }
            SaleVehicleTxn::where('sale_id',$request->id)->delete();
            if($sale->transporter_journal_id){
               JournalDetails::where('journal_id',$sale->transporter_journal_id)->delete();
               Journal::where('id',$sale->transporter_journal_id)->delete();
               AccountLedger::where('entry_type',7)->where('entry_type_id',$sale->transporter_journal_id)->delete();

            }
            //Sale Order/Production Code
            if(!empty($sale->sale_order_id)){
               $saleOrder = SaleOrder::with('items.gsms.details')
                                    ->where('id', $sale->sale_order_id)
                                    ->first();
               if ($saleOrder) {
                  // Update sale order
                  $saleOrder->update(['status' => 0,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
                  // Update items
                  foreach ($saleOrder->items as $item) {
                     $item->update(['status' => 0]);
                     // Update GSMs
                     foreach ($item->gsms as $gsm) {
                        $gsm->update(['status' => 0]);
                        // Update GSM details
                        foreach ($gsm->details as $detail) {
                           $detail->update(['status' => 0]);
                        }
                     }
                  }
                  $item_stock_id = SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->pluck('weight_id')->toArray();
                  ItemSizeStock::whereIn('id',$item_stock_id)->update(["status"=>1,'sale_order_id'=>"",'sale_id'=>"","sale_description_id"=>""]);
                  SaleOrderItemGsmSize::where("sale_orders_id",$sale->sale_order_id)->update(["sale_order_qty"=>""]);
                  SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->delete();
                  Sales::where('id',$request->id)->update(["sale_order_id"=>""]);
               }
            }
            ItemSizeStock::where('sale_id', $request->id)->update([
               'status' => 1,
               'sale_id' => null,
               'sale_description_id' => null
            ]); 
            $response = [
               'success' => true,
               'data'    => "",
               'message' => "E-Invoice Cancelled Successfully",
            ]; 
            return response()->json($response, 200);        
         }else{
            if(isset($result->status_desc)){
               $error = json_decode($result->status_desc);
               return $response = [
                  'success' => false,
                  'data'    => "",
                  'message' => $error[0]->ErrorMessage,
               ]; 
               return response()->json($response, 200);
            }
         }
      } 
   }
   public function getItemSizeQuantity(Request $request){
      $size = ItemSizeStock::select('reel_no','size','weight','id')->where('item_id',$request->item_id)
                     ->where('status',1)
                     ->where('company_id',Session::get('user_company_id'))
                     ->get();
      return response()->json($size);
   }
   public function getItemSizeQuantityForEdit(Request $request)
   {
      $itemId = $request->item_id;
      $saleId = $request->sale_id; // passed from edit page

      $companyId = Session::get('user_company_id');

      // 🟩 Already assigned to this sale — show prefilled
      $assigned = ItemSizeStock::select('id', 'size', 'weight', 'reel_no')
         ->where('item_id', $itemId)
         ->where('company_id', $companyId)
         ->where('status', 0)
         ->where('sale_id', $saleId)
         ->get();

      // 🟦 Available for new selection
      $available = ItemSizeStock::select('id', 'size', 'weight', 'reel_no')
         ->where('item_id', $itemId)
         ->where('company_id', $companyId)
         ->where('status', 1)
         ->whereNull('sale_id')
         ->get();

      return response()->json([
         'assigned' => $assigned,
         'available' => $available
      ]);
   }
   public function cancel(Request $request)
   {
      try {
         if (!$request->id) {
               return response()->json(['success'=>false,'message'=>'Sale ID is missing']);
         }

         $sale = Sales::find($request->id);
         if (!$sale) {
               return response()->json(['success'=>false,'message'=>'Sale not found']);
         }

         \DB::beginTransaction();

         // Update sale
         $sale->update([
               'e_invoice_status' => 0,
               'status' => '2',
               'einvoice_response' => '',
               'total' => 0
         ]);

         SaleDescription::where('sale_id', $sale->id)
               ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);

         SaleSundry::where('sale_id', $sale->id)
               ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);

         AccountLedger::where('entry_type', 1)
               ->where('entry_type_id', $sale->id)
               ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);

         ItemLedger::where('source', 1)
               ->where('source_id', $sale->id)
               ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);

         ItemAverageDetail::where('sale_id', $sale->id)
               ->where('type', 'SALE')
               ->delete();

         $desc = SaleDescription::where('sale_id', $sale->id)->get();
         foreach ($desc as $value) {
               if(method_exists(CommonHelper::class, 'RewriteItemAverageByItem')){
                  CommonHelper::RewriteItemAverageByItem($sale->date, $value->goods_discription ?? '', $sale->series_no);
               }
         }
         SaleVehicleTxn::where('sale_id',$sale->id)->delete();
            if($sale->transporter_journal_id){
               JournalDetails::where('journal_id',$sale->transporter_journal_id)->delete();
               Journal::where('id',$sale->transporter_journal_id)->delete();
               AccountLedger::where('entry_type',7)->where('entry_type_id',$sale->transporter_journal_id)->delete();

            }
         //Sale Order/Production Code
         if(!empty($sale->sale_order_id)){
               $saleOrder = SaleOrder::with('items.gsms.details')
                                    ->where('id', $sale->sale_order_id)
                                    ->first();
               if ($saleOrder) {
                  // Update sale order
                  $saleOrder->update(['status' => 0,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
                  // Update items
                  foreach ($saleOrder->items as $item) {
                     $item->update(['status' => 0]);
                     // Update GSMs
                     foreach ($item->gsms as $gsm) {
                        $gsm->update(['status' => 0]);
                        // Update GSM details
                        foreach ($gsm->details as $detail) {
                           $detail->update(['status' => 0]);
                        }
                     }
                  }
                  $item_stock_id = SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->pluck('weight_id')->toArray();
                  ItemSizeStock::whereIn('id',$item_stock_id)->update(["status"=>1,'sale_order_id'=>"",'sale_id'=>"","sale_description_id"=>""]);
                  SaleOrderItemGsmSize::where("sale_orders_id",$sale->sale_order_id)->update(["sale_order_qty"=>""]);
                  SaleOrderItemWeight::where('sale_order_id',$sale->sale_order_id)->delete();
                  Sales::where('id',$request->id)->update(["sale_order_id"=>""]);
               }
            }
            ItemSizeStock::where('sale_id', $sale->id)->update([
               'status' => 1,
               'sale_id' => null,
               'sale_description_id' => null
            ]); 
         \DB::commit();

         return response()->json(['success'=>true,'message'=>'Invoice Cancelled Successfully']);

      } catch (\Exception $e) {
         \DB::rollBack(); 
         \Log::error('Sale Cancel Error: '.$e->getMessage(), ['sale_id'=>$request->id]);
         return response()->json(['success'=>false,'message'=>'Something went wrong: '.$e->getMessage()]);
      }
   }
   public function checkVoucherNo(Request $request)
   {
      $exists = \DB::table('sales')
         ->where('voucher_no_prefix', $request->voucher_no_prefix)
         ->exists();

      return response()->json(['exists' => $exists]);
   }
public function exportSales(Request $request)
   {
      $request->validate([
         'from_date' => 'required|date',
         'to_date'   => 'required|date',
         'sale_type' => 'required|in:LOCAL,CENTER',
      ]);

      $from     = $request->input('from_date');
      $to       = $request->input('to_date');
      $saleType = $request->input('sale_type');

      $company_id = Session::get('user_company_id');
    
      $sales = DB::table('sales')
         ->leftJoin('accounts', 'sales.party', '=', 'accounts.id')
         ->leftJoin('states', 'accounts.state', '=', 'states.id')
         ->where('sales.company_id', $company_id)
         ->where(function ($q) {
               $q->where('sales.delete', '0')
               ->orWhereNull('sales.delete');
         })
         //->where('voucher_no_prefix','1396/GST/25-26')
         ->whereBetween('sales.date', [$from, $to])
         ->select([
               'sales.id as sale_id',
               'sales.series_no',
               'sales.date',
               'sales.voucher_no_prefix',
               'sales.material_center',
               'sales.vehicle_no',
               'sales.transport_name',
               'sales.billing_gst',
               'sales.merchant_gst',
               'accounts.id as party_alias',
               'accounts.account_name as party_name',
               'accounts.gstin as party_gst',
               'accounts.address as party_address',
               'states.state_code as party_state_code',
               'states.name as party_state_name',
         ])
         ->orderBy('sales.date')
         ->get();

      $filename = "sales_{$from}_to_{$to}_" . strtolower($saleType) . ".csv";

      $headers = [
         'Content-Type'        => 'text/csv; charset=UTF-8',
         'Content-Disposition' => "attachment; filename=\"$filename\"",
      ];

      $callback = function () use ($sales, $company_id, $saleType) {

         $file = fopen('php://output', 'w');
         if($saleType=="LOCAL"){
            fputcsv($file, [
               'Series No','Bill Date','Bill No','Sale Type','Party Alias','Party Name','GSTIN',
               'Address','Material Center','Narration','Item Name','Qty (Reel)','Unit','Size','Reel No.','Weight',
               'Qty','P Alt Qty','Qty (KG)','Price','Amount',
               'Freight','Discount','CGST','SGST','TCS','Vehicle No','Transport'
            ]);
         }else if($saleType=="CENTER"){
            fputcsv($file, [
               'Series No','Bill Date','Bill No','Sale Type','Party Alias','Party Name','GSTIN',
               'Address','Material Center','Narration','Item Name','Qty (Reel)','Unit','Size','Reel No.','Weight',
               'Qty','P Alt Qty','Qty (KG)','Price','Amount',
               'Freight','Discount','IGST','TCS','Vehicle No','Transport'
            ]);
         }
         

         foreach ($sales as $sale) {

               $billingGst  = trim((string) $sale->billing_gst);
               $merchantGst = trim((string) $sale->merchant_gst);

               $billingState  = strlen($billingGst)  >= 2 ? substr($billingGst, 0, 2)  : null;
               $merchantState = strlen($merchantGst) >= 2 ? substr($merchantGst, 0, 2) : null;

               if ($billingState && $merchantState) {
                  $row_sale_type = ($billingState === $merchantState) ? 'LOCAL' : 'CENTER';
               } else {
                  $row_sale_type = 'CENTER';
               }

               if ($row_sale_type !== $saleType) {
                  continue;
               }

               $dateFormatted = date('d-m-Y', strtotime($sale->date));

               $sundries = DB::table('sale_sundries')
                  ->leftJoin('bill_sundrys', 'sale_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->where('sale_sundries.sale_id', $sale->sale_id)
                  ->select('bill_sundrys.name as sundry_name', 'bill_sundrys.id as bs_id', 'sale_sundries.amount')
                  ->get();

               $freight = $discount = $cgst = $sgst = $tcs = $igst = 0;

               foreach ($sundries as $sd) {
                  $n   = strtoupper(trim($sd->sundry_name));
                  $amt = floatval($sd->amount);

                  if (strpos($n, 'FREIGHT') !== false) $freight += $amt;
                  elseif (strpos($n, 'DISCOUNT') !== false) $discount += $amt;
                  elseif ($n === 'CGST') $cgst += $amt;
                  elseif ($n === 'SGST') $sgst += $amt;
                  elseif ($n === 'IGST') $igst += $amt;
                  elseif ($n === 'TCS') $tcs += $amt;
                  //if (in_array($sd->bs_id, [4, 10])) $tcs += $amt;
               }

               $descs = DB::table('sale_descriptions')
                  ->where('sale_id', $sale->sale_id)
                  ->where('status', '1')
                  ->get();

               $firstRow = true;

               foreach ($descs as $desc) {

                  $item = DB::table('manage_items')
                     ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
                     ->where('manage_items.id', $desc->goods_discription)
                     ->select('manage_items.name as item_name', 'units.s_name as unit_name')
                     ->first();

                  $item_name = $item->item_name ?? '';
                  $unit_name = $item->unit_name ?? '';

                  $weights = DB::table('item_size_stocks')
                     ->where('sale_id', $sale->sale_id)
                     ->where('sale_description_id', $desc->id)
                     ->where('item_id', $desc->goods_discription)
                     ->where('status', '0')
                     ->get();

                  $qty_reel = $weights->count();

                  if ($firstRow) {
                     $freight_col  = $freight;
                     $discount_col = $discount;
                     $cgst_col     = $cgst;
                     $sgst_col     = $sgst;
                     $igst_col     = $igst;
                     $tcs_col      = $tcs;
                     $veh_col      = $sale->vehicle_no;
                     $tran_col     = $sale->transport_name;
                  } else {
                     $freight_col = $discount_col = $cgst_col = $sgst_col = $tcs_col = $igst_col = '';
                     $veh_col = $tran_col = '';
                  }

                  if ($qty_reel == 0) {
                      continue;
                     if($saleType=="LOCAL"){
                        fputcsv($file, [
                           $sale->series_no,
                           $dateFormatted,
                           $sale->voucher_no_prefix,
                           $row_sale_type,
                           $sale->party_alias,
                           $sale->party_name,
                           $sale->party_gst,
                           $sale->party_address,
                           $sale->material_center,
                           '',
                           $item_name,
                           $desc->qty,
                           $unit_name,
                           '',
                           '',
                           $desc->qty,
                           $desc->qty,
                           '1',
                           $desc->qty,
                           $desc->price,
                           $desc->amount,
                           $freight_col, $discount_col, $cgst_col, $sgst_col, $tcs_col,
                           $veh_col, $tran_col
                        ]);
                        $firstRow = false;
                     }else{
                        fputcsv($file, [
                           $sale->series_no,
                           $dateFormatted,
                           $sale->voucher_no_prefix,
                           $row_sale_type,
                           $sale->party_alias,
                           $sale->party_name,
                           $sale->party_gst,
                           $sale->party_address,
                           $sale->material_center,
                           '',
                           $item_name,
                           $desc->qty,
                           $unit_name,
                           '',
                           '',
                           $desc->qty,
                           $desc->qty,
                           '1',
                           $desc->qty,
                           $desc->price,
                           $desc->amount,
                           $freight_col, $discount_col, $igst_col, $tcs_col,
                           $veh_col, $tran_col
                        ]);
                        $firstRow = false;
                     }
                     

                  } else {

                     foreach ($weights as $w) {
                        if($saleType=="LOCAL"){
                           fputcsv($file, [
                              $sale->series_no,
                              $dateFormatted,
                              $sale->voucher_no_prefix,
                              $row_sale_type,
                              $sale->party_alias,
                              $sale->party_name,
                              $sale->party_gst,
                              $sale->party_address,
                              $sale->material_center,
                              '',
                              $item_name,
                              $w->weight,
                              $unit_name,
                              $w->size,
                              $w->reel_no,
                              $w->weight,
                              $w->weight,
                              1,
                              $w->weight,
                              $desc->price,
                              $w->weight*$desc->price,
                              $freight_col, $discount_col, $cgst_col, $sgst_col, $tcs_col,
                              $veh_col, $tran_col
                           ]);
                           $firstRow = false;
                           $freight_col = $discount_col = $cgst_col = $sgst_col = $tcs_col = $igst_col = '';
                           $veh_col = $tran_col = '';
                        }else{
                           fputcsv($file, [
                              $sale->series_no,
                              $dateFormatted,
                              $sale->voucher_no_prefix,
                              $row_sale_type,
                              $sale->party_alias,
                              $sale->party_name,
                              $sale->party_gst,
                              $sale->party_address,
                              $sale->material_center,
                              '',
                              $item_name,
                              $w->weight,
                              $unit_name,
                              $w->size,
                              $w->reel_no,
                              $w->weight,
                              $w->weight,
                              1,
                              $w->weight,
                              $desc->price,
                              $w->weight*$desc->price,
                              $freight_col, $discount_col, $igst_col, $tcs_col,
                              $veh_col, $tran_col
                           ]);
                           $firstRow = false;
                           $freight_col = $discount_col = $cgst_col = $sgst_col = $tcs_col = $igst_col = '';
                           $veh_col = $tran_col = '';
                        }
                           
                     }
                  }
               }
         }

         fclose($file);
      };

      return response()->stream($callback, 200, $headers);
   }

public function saleInvoicePdf($id)
{
    $company_data = Companies::join('states','companies.state','=','states.id')
        ->where('companies.id', Session::get('user_company_id'))
        ->select(['companies.*','states.name as sname'])
        ->first();

    $items_detail = DB::table('sale_descriptions')->where('sale_id', $id)
         ->select(
            'sale_descriptions.id as sale_description_id',
            'units.s_name as unit',
            'units.id as unit_id',
            'sale_descriptions.qty',
            'sale_descriptions.price',
            'sale_descriptions.amount',
            'manage_items.p_name',
            'manage_items.name',
            'manage_items.id as item_id',
            'sales.*',
            'accounts.*',
            'manage_items.hsn_code',
            'manage_items.gst_rate'
         )
        ->join('units', 'sale_descriptions.unit', '=', 'units.id')
        ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
        ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
        ->join('accounts', 'accounts.id', '=', 'sales.party')
        ->get();
      foreach ($items_detail as $item) {
         $item->lines = DB::table('sale_description_lines')
            ->where('sale_description_id', $item->sale_description_id)
            ->orderBy('sort_order')
            ->get();
      }
    $sale_detail = Sales::leftjoin('states','sales.billing_state','=','states.id')
        ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
        ->where('sales.id', $id)
        ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name'])
        ->first();
        
     $einvoice_data = null;
    $qrBase64 = null;
    
    if ($sale_detail && $sale_detail->e_invoice_status == 1 && !empty($sale_detail->einvoice_response)) {
    
        $einvoice_data = json_decode($sale_detail->einvoice_response);
    
        if (!empty($einvoice_data->SignedQRCode)) {
    
            // ✅ SVG QR (no imagick, no gd)
            $svgQr = QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->generate($einvoice_data->SignedQRCode);
    
            // base64 encode SVG
            $qrBase64 = base64_encode($svgQr);
        }
    }
    

    $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
        ->where('accounts.id', $sale_detail->party)
        ->select(['accounts.*','states.name as sname'])
        ->first();

    $sale_sundry = DB::table('sale_sundries')
        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
        ->where('sale_id', $id)
        ->select('sale_sundries.bill_sundry','sale_sundries.rate','sale_sundries.amount','bill_sundrys.name','nature_of_sundry','bill_sundry_type')
        ->orderBy('sequence')
        ->get();

    $gst_detail = DB::table('sale_sundries')
        ->select('rate','amount')
        ->where('sale_id', $id)
        ->where('rate','!=','0')
        ->distinct('rate')
        ->get();

    $max_gst = DB::table('sale_sundries')
        ->select('rate')
        ->where('sale_id', $id)
        ->where('rate','!=','0')
        ->max(\DB::raw("cast(rate as SIGNED)"));

    if(count($gst_detail)>0){
        foreach ($gst_detail as $key => $value){
            $rate = $value->rate;
            if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
                $rate = $rate*2;
                $max_gst = $max_gst*2;
            }
            $taxable_amount = 0;
            foreach($items_detail as $item) {
                if($item->gst_rate==$rate){
                    $taxable_amount += $item->amount;
                }
            }
            $gst_detail[$key]->rate = $rate;

            if($max_gst==$rate){
                $sun = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                    ->select('amount','bill_sundry_type')
                    ->where('sale_id', $id)
                    ->where('nature_of_sundry','OTHER')
                    ->get();

                foreach ($sun as $v1) {
                    if($v1->bill_sundry_type=="additive"){
                        $taxable_amount += $v1->amount;
                    } else if($v1->bill_sundry_type=="subtractive"){
                        $taxable_amount -= $v1->amount;
                    }
                }
            }
            $gst_detail[$key]->taxable_amount = $taxable_amount;
        }
    }

    $bank_detail = DB::table('banks')->where('company_id', Session::get('user_company_id'))
        ->first();

    if($company_data->gst_config_type == "single_gst") {
        $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
        $seller_info = DB::table('gst_settings')
            ->join('states','gst_settings.state','=','states.id')
            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
            ->select(['gst_no','address','pincode','states.name as sname'])
            ->first();

        if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                ->where(['delete' => '0', 'company_id' => $sale_detail->company_id,'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                ->first();
            $state_info = DB::table('states')->where('id',$GstSettings->state)->first();
            $seller_info->sname = $state_info->name;
        }

    } else if($company_data->gst_config_type == "multiple_gst") {

        $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst])->first();

        $seller_info = DB::table('gst_settings_multiple')
            ->join('states','gst_settings_multiple.state','=','states.id')
            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_detail->merchant_gst,'series'=>$sale_detail->series_no])
            ->select(['gst_no','address','pincode','states.name as sname'])
            ->first();

        if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                ->where(['delete' => '0', 'company_id' => $sale_detail->company_id,'gst_number'=>$sale_detail->merchant_gst,'branch_series'=>$sale_detail->series_no])
                ->first();
            $state_info = DB::table('states')->where('id',$GstSettings->state)->first();
            $seller_info->sname = $state_info->name;
        }
    }

    if($GstSettings){
        if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
            if($sale_detail->total<100000){
                $GstSettings->ewaybill = 0;
            }
        } else {
            if($sale_detail->total<50000){
                $GstSettings->ewaybill = 0;
            }
        }
    } else {
        $GstSettings = (object)[];
        $GstSettings->ewaybill = 0;
        $GstSettings->einvoice = 0;
    }

    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();

    Session::put('redirect_url', '');

    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04', $from.'-05', $from.'-06', $from.'-07', $from.'-08', $from.'-09',
        $from.'-10', $from.'-11', $from.'-12', $to.'-01', $to.'-02', $to.'-03'
    ];

    $saleOrder = \App\Models\SaleOrder::with([
        'billTo:id,account_name,gstin,address,pin_code,state,pan',
        'shippTo:id,account_name,gstin,address,pin_code,state,pan',
        'orderCreatedBy:id,name',
        'items.item:id,name,hsn_code',
        'items.unitMaster:id,s_name',
    ])
    ->where('id', $sale_detail->sale_order_id)
    ->first();

    if ($saleOrder) {
        foreach ($saleOrder->items as $item) {
            $item->itemSize = \DB::table('item_size_stocks')
                ->where('item_id', $item->item_id)
                ->where(function ($query) use ($saleOrder, $id) {
                    $query->where('sale_order_id', $saleOrder->id)
                          ->Where('sale_id', $id);
                })
                ->select('reel_no', 'size', 'gsm', 'bf', 'weight', 'unit')
                ->get();
        }
    }
    
    $comp = Companies::select('user_id')->where('id',Session::get('user_company_id'))->first();
          $production_module_status = MerchantModuleMapping::where('module_id',4)->where('merchant_id',$comp->user_id)->where('company_id', Session()->get('user_company_id'))->first();
          $production_module_status = $production_module_status ? 1 : 0;
    $pdf = Pdf::loadView('saleInvoicePdf', [
        'items_detail' => $items_detail,
        'sale_sundry' => $sale_sundry,
        'party_detail' => $party_detail,
        'month_arr' => $month_arr,
        'company_data' => $company_data,
        'sale_detail' => $sale_detail,
        'bank_detail' => $bank_detail,
        'gst_detail'=>$gst_detail,
        'einvoice_status'=>$GstSettings->einvoice,
        'ewaybill_status'=>$GstSettings->ewaybill,
        'configuration'=>$configuration,
        'seller_info'=>$seller_info,
        'saleOrder' => $saleOrder,
        'production_module_status'=>$production_module_status,
'qrBase64' => $qrBase64,
'einvoice_data' => $einvoice_data,

    ])->setPaper('A4')
    ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
    ]);

    return $pdf->download('SaleInvoice-'.$sale_detail->voucher_no.'.pdf');
}

public function exportSalesView()
{
    return view('sale_export');
}
public function exportSaleBillView() {
    return view('sale_bill_export');
}

public function exportSaleBill(Request $request)
   {
      $request->validate([
         'from_date' => 'required|date',
         'to_date'   => 'required|date',
         'sale_type' => 'required|in:LOCAL,CENTER',
      ]);

      $from = $request->input('from_date');
      $to   = $request->input('to_date');
      $saleType = $request->input('sale_type');

      $company_id = Session::get('user_company_id');
      $company = DB::table('companies')->where('id', $company_id)->first();
      $company_state_code = substr($company->gst, 0, 2);

      $sales = DB::table('sales')
         ->leftJoin('accounts', 'sales.party', '=', 'accounts.id')
         ->leftJoin('states', 'accounts.state', '=', 'states.id')
         ->where('sales.company_id', $company_id)
         ->where(function($q){
               $q->where('sales.delete', '0')->orWhereNull('sales.delete');
         })
         ->whereBetween('sales.date', [$from, $to])
         ->select(
               'sales.*',
               'accounts.account_name as party_name',
               'accounts.gstin as party_gst',
               'accounts.address as party_address',
               'accounts.id as party_alias',
               'states.state_code as party_state_code',
               'states.name as party_state_name'
         )
         ->orderBy('sales.date')
         ->get();

      $filename = "sale_bill_{$from}_to_{$to}_" . strtolower($saleType) . ".csv";

      $headers = [
         "Content-Type" => "text/csv; charset=UTF-8",
         "Content-Disposition" => "attachment; filename=\"$filename\""
      ];

      $callback = function() use ($sales, $company_id, $saleType) {

         $out = fopen('php://output', 'w');

         fputcsv($out, [
               'Series','Date','Voucher No','Sale Type','Alias','Party Name','GSTIN','Address',
               'Material Center','Narration','Item Name','Qty (KG)','Unit','Price','Amount',
               'Freight','Discount','CGST','SGST','IGST','TCS',
               'Transport','GR','GR Date','Vehicle No','Station'
         ]);

         foreach ($sales as $sale)
         {
               $billingGst  = trim((string)$sale->billing_gst);
               $merchantGst = trim((string)$sale->merchant_gst);

               $billingState  = strlen($billingGst)  >= 2 ? substr($billingGst, 0, 2)  : null;
               $merchantState = strlen($merchantGst) >= 2 ? substr($merchantGst, 0, 2) : null;

               if ($billingState && $merchantState) {
                     $row_sale_type = ($billingState === $merchantState) ? 'LOCAL' : 'CENTER';
               } else {
                     $row_sale_type = 'CENTER';
               }

               if ($row_sale_type !== $saleType) {
                     continue;
               }

               $dateFormatted = date('d-m-Y', strtotime($sale->date));

               $sundries = DB::table('sale_sundries')
                  ->leftJoin('bill_sundrys', 'sale_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->where('sale_sundries.sale_id', $sale->id)
                  ->where('sale_sundries.company_id', $company_id)
                  ->select('bill_sundrys.name as name', 'sale_sundries.amount')
                  ->get();

               $freight = $discount = $cgst = $sgst = $igst = $tcs = 0;

               foreach ($sundries as $sd) {
                  $n = strtoupper(trim($sd->name));
                  $amt = floatval($sd->amount);

                  if (strpos($n,'FREIGHT') !== false) $freight += $amt;
                  elseif (strpos($n,'DISCOUNT') !== false) $discount += $amt;
                  elseif ($n == 'CGST') $cgst += $amt;
                  elseif ($n == 'SGST') $sgst += $amt;
                  elseif ($n == 'IGST') $igst += $amt;
                  elseif ($n == 'TCS')  $tcs  += $amt;
               }

               $descs = DB::table('sale_descriptions')
                  ->where('sale_id', $sale->id)
                  ->where('company_id', $company_id)
                  ->where(function($q){
                     $q->where('status', '1')->orWhereNull('status');
                  })
                  ->get();

               $firstRow = true;

               foreach ($descs as $desc)
               {
                  $item = DB::table('manage_items')
                     ->leftJoin('units','manage_items.u_name','=','units.id')
                     ->where('manage_items.id', $desc->goods_discription)
                     ->select('manage_items.name as item_name', 'units.s_name as unit_name')
                     ->first();

                  $item_name = $item->item_name ?? '';
                  $unit_name = $item->unit_name ?? '';

                  $qty_kg = DB::table('item_size_stocks')
                     ->where('sale_id', $sale->id)
                     ->where('sale_description_id', $desc->id)
                     ->where('company_id', $company_id)
                     ->where('status','0')
                     ->sum(DB::raw('CAST(weight AS DECIMAL(18,3))'));

                  if ($qty_kg == 0) $qty_kg = (float)$desc->qty;

                  if ($firstRow) {
                     $freight_col  = $freight;
                     $discount_col = $discount;
                     $cgst_col     = $cgst;
                     $sgst_col     = $sgst;
                     $igst_col     = $igst;
                     $tcs_col      = $tcs;
                     $transport_col = $sale->transport_name;
                     $vehicle_col   = $sale->vehicle_no;
                     $gr_col        = $sale->gr_pr_no;
                     $gr_date_col   = ''; 
                  } else {
                     $freight_col = $discount_col = $cgst_col = $sgst_col = $igst_col = $tcs_col = '';
                     $transport_col = $vehicle_col = $gr_col = $gr_date_col = '';
                  }

                  fputcsv($out, [
                     $sale->series_no,
                     $dateFormatted,
                     $sale->voucher_no_prefix,  
                     $row_sale_type,
                     $sale->party_alias,
                     $sale->party_name,
                     $sale->party_gst,
                     $sale->party_address,
                     $sale->material_center,
                     $sale->narration ?? '',
                     $item_name,
                     $qty_kg,
                     $unit_name,
                     $desc->price,
                     $desc->amount,
                     $freight_col,$discount_col,$cgst_col,$sgst_col,$igst_col,$tcs_col,
                     $transport_col,$gr_col,$gr_date_col,$vehicle_col,$sale->station
                  ]);

                  $firstRow = false;
               }
         }

         fclose($out);
      };

      return response()->stream($callback, 200, $headers);
   }
    public function downloadEwayBill($id)
    {
        $sale = Sales::leftjoin('states','sales.billing_state','=','states.id')
                           ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
                           ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name'])
                           ->find($id);
        if (!$sale || $sale->e_waybill_status != 1) {
            return redirect()->back()->withErrors('Eway Bill not available for this sale.');
        }
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        if($company_data->gst_config_type == "single_gst") {
            $GstSettings = DB::table('gst_settings')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                            ->first();
            $seller_info = DB::table('gst_settings')
                               ->join('states','gst_settings.state','=','states.id')
                               ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale->merchant_gst,'series'=>$sale->series_no])
                               ->select(['gst_no','address','pincode','states.name as sname'])
                               ->first();
            if(!$seller_info){
                $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                               ->where(['delete' => '0', 'company_id' => $sale->company_id,'gst_number'=>$sale->merchant_gst,'branch_series'=>$sale->series_no])
                               ->first();
                $state_info = DB::table('states')
                               ->where('id',$GstSettings->state)
                               ->first();
                $seller_info->sname = $state_info->name;
            }
        }else if($company_data->gst_config_type == "multiple_gst") {         
             $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale->merchant_gst])->first();
             //Seller Info
             $seller_info = DB::table('gst_settings_multiple')
                               ->join('states','gst_settings_multiple.state','=','states.id')
                               ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale->merchant_gst,'series'=>$sale->series_no])
                               ->select(['gst_no','address','pincode','states.name as sname'])
                               ->first();
            if(!$seller_info){
                $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                               ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale->merchant_gst,'branch_series'=>$sale->series_no])
                               ->first();
                $state_info = DB::table('states')
                                     ->where('id',$GstSettings->state)
                                     ->first();
                $seller_info->sname = $state_info->name;
            }         
        }
        $items_detail = DB::table('sale_descriptions')->where('sale_id', $id)
                ->select('units.s_name as unit', 'units.id as unit_id', 'sale_descriptions.qty', 'sale_descriptions.price', 'sale_descriptions.amount', 'manage_items.p_name as items_name', 'manage_items.id as item_id', 'sales.*', 'accounts.*','manage_items.hsn_code','manage_items.gst_rate')
                ->join('units', 'sale_descriptions.unit', '=', 'units.id')
                ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
                ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
                ->join('accounts', 'accounts.id', '=', 'sales.party')
                ->get();
        $sale_sundry = DB::table('sale_sundries')
                        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('sale_id', $id)
                        ->select('sale_sundries.bill_sundry','sale_sundries.rate','sale_sundries.amount','bill_sundrys.name','nature_of_sundry','bill_sundry_type')
                        ->orderBy('sequence')
                        ->get();
        
      return view('sale_ewaybill', compact('sale','items_detail','sale_sundry','company_data','seller_info'));
   }
   
   
public function emailInvoice($id)
{
    $sale_detail = Sales::findOrFail($id);

    $party_detail = Accounts::find($sale_detail->party);

    if (!$party_detail || empty($party_detail->email)) {
        return back()->with('error', 'Party email not found!');
    }

    // 🔥 Get company of this sale
$companyId = Session::get('user_company_id');
$company = Companies::findOrFail($companyId);

    // 🔥 Apply company SMTP dynamically
    MailHelper::setCompanyMailConfig($company);

    // Get raw PDF content
    $pdfContent = $this->saleInvoicePdf($id);

    Mail::to($party_detail->email)
        ->send(new SaleInvoiceMail($pdfContent, $sale_detail));

    return back()->with('success', 'Invoice emailed successfully!');
}

    public function saleInvoicePreview(){
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => $sale_detail->company_id])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {         
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
         //Seller Info
         $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                          
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])
                           ->first();
            $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
            $seller_info->sname = $state_info->name;
                          
         }         
      }
        $bank_detail = DB::table('banks')->where('company_id', Session::get('user_company_id'))
            ->select('banks.*')
            ->first();
        $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->first();
      
        return view('Sale/sale_invoice_preview')->with([ 'company_data' => $company_data, 'bank_detail' => $bank_detail,'configuration'=>$configuration,'seller_info'=>$seller_info]);
    }
    
    public function getLatestCost(Request $request)
{
    $itemId = $request->item_id;
    $date = $request->date;
    $series = $request->series;

    $cost = DB::table('item_averages')
        ->where('item_id', $itemId)
        ->where('series_no', $series) // 🔥 important
        ->whereDate('stock_date', '<=', $date)
        ->orderBy('stock_date', 'desc') // latest
        ->value('price'); // 🔥 use price column

    return response()->json([
        'cost' => $cost ?? 0
    ]);
}

}
