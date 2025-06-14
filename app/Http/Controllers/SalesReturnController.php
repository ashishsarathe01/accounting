<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesReturn;
use App\Models\SaleReturnDescription;
use App\Models\SaleReturnSundry;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\BillSundrys;
use App\Models\GstBranch;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\AccountGroups;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Bank;
use App\Models\State;
use App\Models\SaleInvoiceTermCondition;
use App\Models\SaleReturnWithoutGstEntry;
use App\Models\ItemAverageDetail;
use App\Helpers\CommonHelper;
use Session;
use DateTime;
use Gate;
class SalesReturnController extends Controller
{

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
      Gate::authorize('action-module',12);
      $input = $request->all();
  
      // Default date range (first day of current month to today)
      $from_date = null;
      $to_date = null;
  
      // Check if user has selected a date range
      if (!empty($input['from_date']) && !empty($input['to_date'])) {
          $from_date = date('d-m-Y', strtotime($input['from_date']));
          $to_date = date('d-m-Y', strtotime($input['to_date']));
  
          // Store in session
          session([
              'salesReturn_from_date' => $from_date,
              'salesReturn_to_date' => $to_date
          ]);
      }
  
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
  
      // Base query
      $query = DB::table('sales_returns')
          ->select(
              'sr_prefix', 'sr_nature', 'sr_type',
              'sales_returns.id as sales_returns_id',
              'sales_returns.date',
              'sales_returns.series_no',
              'sales_returns.financial_year',
              'sales_returns.invoice_no',
              'sale_return_no',
              'sales_returns.total',
              DB::raw('(select account_name from accounts where accounts.id=sales_returns.party limit 1) as account_name')
          )
          ->where('company_id', Session::get('user_company_id'))
          ->where('delete', '0');
  
      // Apply date filter if dates are set by user
      if (!empty($input['from_date']) && !empty($input['to_date'])) {
          $query->whereRaw("
              STR_TO_DATE(sales_returns.date, '%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "', '%Y-%m-%d')
              AND STR_TO_DATE(sales_returns.date, '%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "', '%Y-%m-%d')
          ")
          ->orderBy(DB::raw("cast(sale_return_no as SIGNED)"), 'ASC')
          ->orderBy('sales_returns.created_at', 'ASC');
      } else {
          // No date selected, show last 10 entries
          $query->orderBy('financial_year', 'desc')
              ->orderBy(DB::raw("cast(sale_return_no as SIGNED)"), 'desc')
              ->limit(10);
      }
  
      $sale = $query->get()->reverse()->values(); // Optional reverse if needed for ascending display
  
      return view('saleReturn')
          ->with('sale', $sale)
          ->with('month_arr', $month_arr)
          ->with("from_date", $from_date)
          ->with("to_date", $to_date);
   }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     return view('addSaleReturn');
    // }

   public function create(){
      Gate::authorize('action-module',76);
      $groups = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
      $groups->push(3);
      $groups->push(11);
      $party_list = Accounts::leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get();  
      $manageitems = DB::table('manage_items')->where('manage_items.company_id', Session::get('user_company_id'))
            ->select('units.s_name as unit', 'manage_items.*')
            ->where('manage_items.delete', '0')
            ->where('manage_items.status', '1')
            ->where('manage_items.u_name', '!=', '')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->orderBy('manage_items.name')
            ->get();

      $companyData = Companies::where('id', Session::get('user_company_id'))->first();

      $GstSettings = (object)NULL;
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
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst"){
         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }        
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->get();
      $financial_year = Session::get('default_fy');      
      foreach ($mat_series as $key => $value) {
         $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$value->series)
               ->where('configuration_for','CREDIT NOTE')
               ->where('status','1')
               ->first();
         //With GST
         $sale_return_no = SalesReturn::select('sale_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('sr_nature','!=',"WITHOUT GST") 
                        ->where('series_no','=',$value->series)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
         if(!$sale_return_no){
            if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
               $mat_series[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
            }else{
               $mat_series[$key]->invoice_start_from =  "001";
            }
         }else{
            $invc = $sale_return_no + 1;
            $invc = sprintf("%'03d", $invc);
            $mat_series[$key]->invoice_start_from =  $invc;
         }
         //Without GST
         $sale_return_no_without = SalesReturn::select('sale_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('sr_nature','=',"WITHOUT GST")
                        ->where('series_no','=',$value->series)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
         if(!$sale_return_no_without){
            $mat_series[$key]->without_invoice_start_from =  "001";
         }else{
            $invc = $sale_return_no_without + 1;
            $invc = sprintf("%'03d", $invc);
            $mat_series[$key]->without_invoice_start_from =  $invc;
         }
         $invoice_prefix = "";
         $invoice_prefix_wt = "";
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
               $invoice_prefix_wt.=$series_configuration->prefix_value."WT";
            }        
            if($series_configuration->prefix=="ENABLE" && $series_configuration->prefix_value!="" && $series_configuration->separator_1!=""){
                  $invoice_prefix.=$series_configuration->separator_1;
                  $invoice_prefix_wt.=$series_configuration->separator_1;
            }
            if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!=""){
               if($series_configuration->year_format=="YY-YY"){
                  $invoice_prefix.=Session::get('default_fy');
                  $invoice_prefix_wt.=Session::get('default_fy');
               }else if($series_configuration->year_format=="YYYY-YY"){
                  $default_fy = Session::get('default_fy');  // 23-24
                  $fy_parts = explode('-', $default_fy);     // [23, 24]
                  $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
                  $invoice_prefix_wt.='20' . $fy_parts[0] . '-20' . $fy_parts[1];
               }
            }            
            if($series_configuration->year=="PREFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
               $invoice_prefix.=$series_configuration->separator_2;
               $invoice_prefix_wt.=$series_configuration->separator_2;
            }
            $invoice_prefix.=$mat_series[$key]->invoice_start_from;
            $invoice_prefix_wt.=$mat_series[$key]->without_invoice_start_from;
            if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!="" && $series_configuration->separator_2!=""){
               $invoice_prefix.=$series_configuration->separator_2;
               $invoice_prefix_wt.=$series_configuration->separator_2;
            }
            if($series_configuration->year=="SUFFIX TO NUMBER" && $series_configuration->year_format!=""){
               if($series_configuration->year_format=="YY-YY"){
                  $invoice_prefix.=Session::get('default_fy');
                  $invoice_prefix_wt.=Session::get('default_fy');
               }else if($series_configuration->year_format=="YYYY-YY"){
                  $default_fy = Session::get('default_fy');  // 23-24
                  $fy_parts = explode('-', $default_fy);     // [23, 24]
                  $invoice_prefix .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
                  $invoice_prefix_wt .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
               }
            }        
            if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!="" && $series_configuration->separator_3!=""){
               $invoice_prefix.=$series_configuration->separator_3;
               $invoice_prefix_wt.=$series_configuration->separator_3;
            }
            if($series_configuration->suffix=="ENABLE" && $series_configuration->suffix_value!=""){
               $invoice_prefix.=$series_configuration->suffix_value;
               $invoice_prefix_wt.=$series_configuration->suffix_value;
            } 
         }
         $mat_series[$key]->manual_enter_invoice_no =  $manual_enter_invoice_no;
         $mat_series[$key]->duplicate_voucher =  $duplicate_voucher;
         $mat_series[$key]->blank_voucher =  $blank_voucher;
         $mat_series[$key]->invoice_prefix =  $invoice_prefix;
         $mat_series[$key]->invoice_prefix_wt =  $invoice_prefix_wt;
         
      }                
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
      
      //Vendor
      $vendors = Accounts::select('id','account_name','gstin')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->where('status','1')
               ->where('delete','0')
               ->where('gstin','!=','')
               ->orderBy('account_name')
               ->get();
      //Item
      $fixed_asset_group = AccountGroups::where('heading','6')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type',null)
                                       ->where('heading_type','')
                                       ->pluck('id');
      $fixed_asset_group->push(12);//DIRECT EXPENSE
      $fixed_asset_group->push(15);//INDIRECT EXPENSE
      $fixed_asset_group->push(6);//UNSECURED LOANS
      $fixed_asset_group->push(13);//DIRECT INCOME
      $fixed_asset_group->push(14);//INDIRECT INCOME
      $sub_group = AccountGroups::whereIn('heading',$fixed_asset_group)
                                       ->where('heading_type',"group")
                                       ->pluck('id');
      $fixed_asset_group->merge($sub_group);
      $items = Accounts::select('id','account_name')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->whereIn('under_group',$fixed_asset_group)
               ->where('status','1')
               ->where('delete','0')
               ->orderBy('account_name')
               ->get();
      
      $all_account_list = Accounts::leftjoin('states','accounts.state','=','states.id')
               ->where('delete', '=', '0')
               ->where('status', '=', '1')
               ->where('tax_type', '=', 'TDS/TCS')
               ->whereIn('company_id', [Session::get('user_company_id'),0])
               ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
               ->orderBy('account_name')
               ->get();  
      return view('addSaleReturn')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('GstSettings', $GstSettings)->with('billsundry', $billsundry)->with('mat_series', $mat_series)->with('bill_date', $bill_date)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      Gate::authorize('action-module',76);
      $validated = $request->validate([         
         'date' => 'required',
         'nature' => 'required',     
         'goods_discription' => 'required|array|min:1',
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      //Check Item Empty or not
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
            return $this->failedMessage('Plases Select Item','sale-return/create');
         }
      }
      // echo "<pre>";
      // print_r($request->all());die;
      $financial_year = Session::get('default_fy');
      if($request->input('manual_enter_invoice_no')=='0'){
         if($request->nature!="WITHOUT GST"){
            $sale_return_no = SalesReturn::select('sale_return_no')                   
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','!=',"WITHOUT GST")
                           ->where('delete','=','0')
                           ->where('series_no',$request->input('series_no'))
                           ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
            if(!$sale_return_no){
               $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$request->input('series_no'))
               ->where('configuration_for','CREDIT NOTE')
               ->where('status','1')
               ->first();
               if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  $sale_return_no =  sprintf("%'03d",$series_configuration->invoice_start);
               }else{
                  $sale_return_no = "001";
               }
            }else{
               $sale_return_no++;
               $sale_return_no = sprintf("%'03d", $sale_return_no);
            }
         }else{
            $sale_return_no = SalesReturn::select('sale_return_no')                   
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','=',"WITHOUT GST")
                           ->where('delete','=','0')
                           ->where('series_no',$request->input('series_no'))
                           ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
            if(!$sale_return_no){
               $sale_return_no = "001";
            }else{
               $sale_return_no++;
               $sale_return_no = sprintf("%'03d", $sale_return_no);
            }
         }  
      }else{
         $sale_return_no = $request->input('voucher_no');
      } 
      $account = Accounts::where('id',$request->input('party_id'))->first();
      $sale = new SalesReturn;
      $sale->date = $request->input('date');
      $sale->company_id = Session::get('user_company_id');
      $sale->invoice_no = $request->input('voucher_no');
      $sale->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $sale->taxable_amt = $request->input('taxable_amt');
         $sale->total = $request->input('total');  
          $sale->remark = $request->input('narration_withgst');    
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $sale->taxable_amt = $request->input('net_amount');
         $sale->total = $request->input('total_amount');
         $sale->remark = $request->input('remark');
      }
      $sale->voucher_type = $request->input('voucher_type');
      $sale->sr_nature = $request->input('nature');
      $sale->sr_type = $request->input('type');
      $voucher_prefix = $request->input('voucher_prefix');
      $sale->sr_prefix = $voucher_prefix;
      $sale->series_no = $request->input('series_no');
      $sale->material_center = $request->input('material_center');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->station = $request->input('station');
      $sale->tax_cgst = $request->input('cgst');
      $sale->tax_sgst = $request->input('sgst');
      $sale->tax_igst = $request->input('igst');
      $sale->merchant_gst = $request->input('merchant_gst');
      $sale->billing_gst = $account->gstin;
      $sale->billing_state = $account->state;
      $sale->station = $request->input('station');
      $sale->other_invoice_no = $request->input('other_invoice_no');
      $sale->other_invoice_date = $request->input('other_invoice_date');
      $sale->other_invoice_against = $request->input('other_invoice_against');
      $sale->sale_return_no = $sale_return_no;
     
      $sale->financial_year = $financial_year;
      $sale->sale_bill_id = $request->input('sale_bill_id');
      $sale->save();
      if($sale->id){
         if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
            $goods_discriptions = $request->input('goods_discription');
            $qtys = $request->input('qty');
            $units = $request->input('units');
            $prices = $request->input('price');
            $amounts = $request->input('amount');
            foreach ($goods_discriptions as $key => $good) {
               if($good=="" || $amounts[$key]==""){
                  continue;
               }
               $desc = new SaleReturnDescription;
               $desc->sale_return_id = $sale->id;
               $desc->goods_discription = $good;
               $desc->qty = $qtys[$key];
               $desc->unit = $units[$key];
               $desc->price = $prices[$key];
               $desc->amount = $amounts[$key];
               $desc->status = '1';
               $desc->save();
               //ADD ITEM LEDGER
               if($qtys[$key]!="" && $prices[$key]!="" && $prices[$key]!=0 && $qtys[$key]!=0){
                  if($request->input('type')=="WITH ITEM"){
                     $item_ledger = new ItemLedger();
                     $item_ledger->item_id = $good;
                     $item_ledger->in_weight = $qtys[$key];
                     $item_ledger->txn_date = $request->input('date');
                     $item_ledger->series_no = $request->input('series_no');
                     $item_ledger->price = $prices[$key];
                     $item_ledger->total_price = $amounts[$key];
                     $item_ledger->company_id = Session::get('user_company_id');
                     $item_ledger->source = 4;
                     $item_ledger->source_id = $sale->id;
                     $item_ledger->created_by = Session::get('user_id');
                     $item_ledger->created_at = date('d-m-Y H:i:s');
                     $item_ledger->save();
                  }
               }
            }
            $bill_sundrys = $request->input('bill_sundry');
            $tax_rate = $request->input('tax_rate');
            $bill_sundry_amounts = $request->input('bill_sundry_amount');
            foreach ($bill_sundrys as $key => $bill) {
               if($bill_sundry_amounts[$key]=="" || $bill==""){
                  continue;
               }
               $sundry = new SaleReturnSundry;
               $sundry->sale_return_id = $sale->id;
               $sundry->bill_sundry = $bill;
               $sundry->rate = $tax_rate[$key];
               $sundry->amount = $bill_sundry_amounts[$key];
               $sundry->status = '1';
               $sundry->save();
               //ADD DATA IN CGST ACCOUNT
               $billsundry = BillSundrys::where('id', $bill)->first();
               if($billsundry->adjust_sale_amt=='No'){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $billsundry->sale_amt_account;
                  if($billsundry->nature_of_sundry=='subtractive'){
                     $ledger->credit = $bill_sundry_amounts[$key];
                  }else{
                     $ledger->debit = $bill_sundry_amounts[$key];
                  }
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->map_account_id = $request->input('party_id');
                  $ledger->entry_type = 3;
                  $ledger->entry_type_id = $sale->id;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //$roundoff = $roundoff - $bill_sundry_amounts[$key];
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
               $average_detail->type = 'SALE RETURN';
               $average_detail->sale_return_id = $sale->id;
               $average_detail->sale_return_weight = $value;
               $average_detail->company_id = Session::get('user_company_id');
               $average_detail->created_at = Carbon::now();
               $average_detail->save();
               CommonHelper::RewriteItemAverageByItem($request->date,$key,$request->input('series_no')); 
            }
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->credit = $request->input('total');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 3;
            $ledger->entry_type_id = $sale->id;
            $ledger->map_account_id = 35;//Sale
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            //ADD DATA IN Sale ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = 35;//Sale
            $ledger->debit = $request->input('taxable_amt');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->map_account_id = $request->input('party_id');
            $ledger->entry_type = 3;
            $ledger->entry_type_id = $sale->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save(); 
            $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
            
            return redirect('sale-return-invoice/'.$sale->id)->withSuccess('Sale return added successfully!');
         }else if($request->input('nature')=="WITH GST" && $request->input('type')=="WITHOUT ITEM"){
            //
            //Ledger Entry
            $account_info = Accounts::select('under_group')->where('id',$request->input('party_id'))->first();
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->credit = $request->input('total_amount');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 10;
            $ledger->map_account_id = $request->input('item')[0];
            $ledger->entry_type_id = $sale->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($request->input('item') as $key => $item){
               $percentage = $request->input('percentage')[$key];
               $amount = $request->input('without_item_amount')[$key];
               $hsn = $request->input('hsn')[$key];
               $unit_code = $request->input('unit_code')[$key];
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->type = "Debit";
               $sale_return_without->account_name = $item;
               $sale_return_without->debit = $amount;
               $sale_return_without->percentage = $percentage;  
               $sale_return_without->hsn_code = $hsn;  
               $sale_return_without->unit_code = $unit_code;
               $sale_return_without->status = '1';
               $sale_return_without->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $item;
               $ledger->debit = $amount;                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $request->input('party_id');
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            if(!empty($request->input('igst'))){
               if($account_info->under_group==3){
                  $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $sundry = BillSundrys::select('sale_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->sale_amt_account;
                  }
               }else{
                  $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->purchase_amt_account;
                  }
               }               
               
               //detor sale
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $account_name;
               $ledger->debit = $request->input('igst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }else{
               //CGST
               $cgst_account_name = "";
               if($account_info->under_group==3){
                  $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $cgst_sundry = BillSundrys::select('sale_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->sale_amt_account;
                  }
               }else{
                  $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->purchase_amt_account;
                  }
               }
               //SGST 
               $sgst_account_name = "";
               if($account_info->under_group==3){
                  $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $sgst_sundry = BillSundrys::select('sale_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->sale_amt_account;
                  }
               }else{
                  $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->purchase_amt_account;
                  }
               }               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $cgst_account_name;
               $ledger->debit = $request->input('cgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->debit = $request->input('sgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            return redirect('sale-return-without-item-invoice/'.$sale->id)->withSuccess('Sale return added successfully!');
         }else if($request->input('nature')=="WITHOUT GST"){
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');        
            $narrations = $request->input('narration');
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->account_name = $account;
               $sale_return_without->debit = isset($debits[$key]) ? $debits[$key] : '0';
               $sale_return_without->narration = $narrations[$key];
               $sale_return_without->status = '1';
               $sale_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party_id');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
               $ledger->debit = $debits[$key];                          
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 9;
               $ledger->entry_type_id = $sale->id;
               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();

               $debit_total = $debit_total + $debits[$key];
               $i++;
            }
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->credit = $debit_total;                          
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 9;
            $ledger->entry_type_id = $sale->id;
            //$ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            SalesReturn::where('id',$sale->id)->update(['total'=>$debit_total]);
         }     
         return redirect('sale-return-without-gst-invoice/'.$sale->id)->withSuccess('Sale return added successfully!');
      }else{
         return $this->failedMessage('Something went wrong','sale-return/create');
      }
   }
   public function delete(Request $request){
      Gate::authorize('action-module',70);
      $sale_return =  SalesReturn::find($request->sale_return_id);
      $sale_return->delete = '1';
      $sale_return->status = '0';
      $sale_return->deleted_at = Carbon::now();
      $sale_return->deleted_by = Session::get('user_id');
      $sale_return->update();
      if($sale_return) {
         if($sale_return->sr_nature=="WITH GST" && ($sale_return->sr_type=="WITH ITEM" || $sale_return->sr_type=="RATE DIFFERENCE")){

            if($sale_return->sr_type=="WITH ITEM"){
               ItemAverageDetail::where('sale_return_id',$request->sale_return_id)
                           ->where('type','SALE RETURN')
                           ->delete();         
               $desc = SaleReturnDescription::where('sale_return_id',$request->sale_return_id)
                              ->get();
               foreach ($desc as $key => $value) {
                  CommonHelper::RewriteItemAverageByItem($sale_return->date,$value->goods_discription,$sale_return->series_no);
               }
            }
            SaleReturnDescription::where('sale_return_id',$request->sale_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',3)
                           ->where('entry_type_id',$request->sale_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            SaleReturnSundry::where('sale_return_id',$request->sale_return_id)
                           ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemLedger::where('source',4)
                           ->where('source_id',$request->sale_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }else if($sale_return->sr_nature=="WITH GST" && $sale_return->sr_type=="WITHOUT ITEM"){
            SaleReturnWithoutGstEntry::where('sale_return_id',$request->sale_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',10)
                           ->where('entry_type_id',$request->sale_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }else if($sale_return->sr_nature=="WITHOUT GST"){
            SaleReturnWithoutGstEntry::where('sale_return_id',$request->sale_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',9)
                           ->where('entry_type_id',$request->sale_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }         
         return redirect('sale-return')->withSuccess('Sale Return deleted successfully!');
      }
   }
   public function saleReturnInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_ret = SalesReturn::where('id',$id)->first();
      if($sale_ret->voucher_type=="PURCHASE"){
         $sale_return = SalesReturn::join('purchases','sales_returns.sale_bill_id','=','purchases.id')
                                 ->leftjoin('accounts','sales_returns.party','=','accounts.id')
                                 ->leftjoin('states','purchases.billing_state','=','states.id')
                                 ->where('sales_returns.id',$id)
                                 ->select(['sales_returns.date','sales_returns.id','sales_returns.invoice_no','sales_returns.remark as narration','sales_returns.merchant_gst','sales_returns.total','sales_returns.remark as narration','purchases.billing_name','purchases.billing_address','purchases.billing_pincode','purchases.billing_gst','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.billing_gst','sales_returns.gr_pr_no','sales_returns.transport_name','sales_returns.station','purchases.voucher_no as voucher_no_prefix','purchases.date as sale_date','purchases.series_no','purchases.financial_year','sales_returns.series_no as dr_series_no','sales_returns.financial_year as dr_financial_year','sales_returns.series_no as sr_series_no','sr_nature','sr_type','sr_prefix','accounts.address as party_address'])
                                 ->first();  
      }else if($sale_ret->voucher_type=="SALE"){
         $sale_return = SalesReturn::leftjoin('sales','sales_returns.sale_bill_id','=','sales.id')
                                 ->leftjoin('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','sales.billing_state','=','states.id')
                                 ->where('sales_returns.id',$id)
                                 ->select(['sales_returns.date','sales_returns.id','sales_returns.invoice_no','sales_returns.remark as narration','sales_returns.merchant_gst','sales_returns.total','sales.billing_name','sales.billing_address','sales.billing_pincode','sales.billing_gst','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.gr_pr_no','sales_returns.billing_gst','sales_returns.transport_name','sales_returns.station','sales.voucher_no','sales.date as sale_date','sales.series_no','sales.financial_year','sales_returns.series_no as sr_series_no','sales_returns.financial_year as sr_financial_year','sr_nature','sr_type','sr_prefix','sales.merchant_gst','accounts.address as party_address','voucher_no_prefix'])
                                 ->first();    
      }else if($sale_ret->voucher_type=="OTHER"){
         $sale_return = SalesReturn::leftjoin('states','sales_returns.billing_state','=','states.id')
                                 ->join('accounts','sales_returns.party','=','accounts.id')
                                 ->where('sales_returns.id',$id)
                                 ->select(['sales_returns.date','sales_returns.invoice_no','sales_returns.total','sales_returns.remark as narration','sales_returns.merchant_gst','accounts.account_name as billing_name','accounts.address as billing_address','accounts.pin_code as billing_pincode','sales_returns.billing_gst','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.gr_pr_no','sales_returns.transport_name','sales_returns.station','sales_returns.series_no as sr_series_no','sales_returns.financial_year as sr_financial_year','sr_prefix','sales_returns.id','sales_returns.voucher_type','other_invoice_no','other_invoice_date','merchant_gst','sales_returns.series_no'])
                                 ->first();
      }
      
       $items_detail = DB::table('sale_return_descriptions')
    ->where('sale_return_descriptions.sale_return_id', $id)
    ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
    ->where('sales_returns.sr_type', 'WITH ITEM')
    ->select(
        'units.s_name as unit',
        'units.id as unit_id',
        'sale_return_descriptions.qty',
        'sale_return_descriptions.price',
        'sale_return_descriptions.amount',
        'manage_items.name as items_name',
        'manage_items.id as item_id',
        'manage_items.hsn_code',
        'manage_items.gst_rate'
    )
    ->join('units', 'sale_return_descriptions.unit', '=', 'units.id')
    ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
    ->get();

      $items_detail1 = DB::table('sale_return_descriptions')
    ->where('sale_return_descriptions.sale_return_id', $id)
    ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
    ->where('sales_returns.sr_type', 'RATE DIFFERENCE')
    ->select(
        DB::raw("''  as unit"),
        'units.id as unit_id',
        DB::raw("'' as qty"),
        DB::raw("'' as price"),
        'sale_return_descriptions.amount',
        'manage_items.name as items_name',
        'manage_items.id as item_id',
        'manage_items.hsn_code',
        'manage_items.gst_rate'
    )
    ->join('units', 'sale_return_descriptions.unit', '=', 'units.id')
    ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
    ->get();

    
    
// Merge both collections
$items_detail = $items_detail->merge($items_detail1);     
      $sale_sundry = DB::table('sale_return_sundries')
                        ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('sale_return_id', $id)
                        ->select('sale_return_sundries.bill_sundry','sale_return_sundries.rate','sale_return_sundries.amount','bill_sundrys.name')
                        ->orderBy('sequence')
                        ->get();
      $gst_detail = DB::table('sale_return_sundries')
                        ->select('rate','amount')                     
                        ->where('sale_return_id', $id)
                        ->where('rate','!=','0')
                        ->distinct('rate')                       
                        ->get(); 
      $max_gst = DB::table('sale_return_sundries')
                        ->select('rate')                     
                        ->where('sale_return_id', $id)
                        ->where('rate','!=','0')
                        ->max(\DB::raw("cast(rate as SIGNED)"));
      if(count($gst_detail)>0){
         foreach ($gst_detail as $key => $value){
            $rate = $value->rate;      
            if(substr($company_data->gst,0,2)==substr($sale_return->billing_gst,0,2)){
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

               $freight = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $id)
                           ->where('bill_sundry',4)
                           ->first();
               $insurance = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $id)
                           ->where('bill_sundry',7)
                           ->first();
               $discount = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $id)
                           ->where('bill_sundry',5)
                           ->first();
               if($freight && !empty($freight->amount)){
                  $taxable_amount = $taxable_amount + $freight->amount;
               }
               if($insurance && !empty($insurance->amount)){
                  $taxable_amount = $taxable_amount + $insurance->amount;
               }
               if($discount && !empty($discount->amount)){
                  $taxable_amount = $taxable_amount - $discount->amount;
               }
            }
            $gst_detail[$key]->taxable_amount = $taxable_amount;
         }
      }
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
         if($sale_ret->voucher_type=="PURCHASE"){
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series' => $sale_return->series_no])
                           ->first();
                     //Seller Info         
            $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                        
            if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
            } 
         }else{
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])
                           ->first();
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){
                  
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
               } 
         }
         
                 
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
      if($GstSettings){
         // if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
         //    if($sale_detail->total<100000){
         //       $GstSettings->ewaybill = 0;
         //    }
         // }else{
         //    if($sale_detail->total<50000){
         //       $GstSettings->ewaybill = 0;
         //    }
         // }
      }else{
         $GstSettings = (object)NULL;
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
      }
      if($sale_ret->voucher_type!="SALE"){
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
      }
      $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
      return view('saleReturnInvoice')->with(['items_detail' => $items_detail, 'month_arr' => $month_arr,'configuration'=>$configuration, 'sale_sundry' => $sale_sundry,'company_data' => $company_data,'gst_detail'=>$gst_detail,'sale_return'=>$sale_return,'einvoice_status'=>$GstSettings->einvoice,'seller_info'=>$seller_info,'ewaybill_status'=>$GstSettings->ewaybill]);
   }
   public function saleReturnWithoutItemInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('sales_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname','sales_returns.merchant_gst','sales_returns.series_no')
                                 ->where('sales_returns.id',$id)
                                 ->first();   
      $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
                                 ->where('sale_return_id', $id)
                                 ->select('debit','percentage','sale_return_without_gst_entry.hsn_code','accounts.account_name')
                                 ->get();
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($company_data->gst_config_type == "multiple_gst") {         
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])->first();
      }  
      if(!$GstSettings){
         $GstSettings = (object)NULL;
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
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
      
      
      
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings1= DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                           
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                          
            $state_info = DB::table('states')
                           ->where('id',$GstSettings1->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
         if($sale_ret->voucher_type=="PURCHASE"){
            $GstSettings1 = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series' => $sale_return->series_no])
                           ->first();
                     //Seller Info         
            $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                        
            if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings1->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
            } 
         }else{
            $GstSettings1 = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])
                           ->first();
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){
                  
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings1->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
               }
         }
          
      }
       $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
      return view('sale_return_without_item_invoice')->with(['company_data' => $company_data,'configuration'=>$configuration,'month_arr' => $month_arr,'seller_info'=>$seller_info,'sale_return'=>$sale_return,'items'=>$items,'einvoice_status'=>$GstSettings->einvoice,'ewaybill_status'=>$GstSettings->ewaybill]);
   }
   public function saleReturnWithoutGstInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('sales_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname','sales_returns.merchant_gst','sales_returns.series_no')
                                 ->where('sales_returns.id',$id)
                                 ->first();   
      $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
                                 ->where('sale_return_id', $id)
                                 ->select('debit','percentage','sale_return_without_gst_entry.hsn_code','accounts.account_name')
                                 ->get();  
                                 
                                 if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                           
                           
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                          
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
         if($sale_ret->voucher_type=="PURCHASE"){
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series' => $sale_return->series_no])
                           ->first();
                     //Seller Info         
            $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                        
            if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
            } 
         }else{
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])
                           ->first();
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){
                  
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
               }
         }
          
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
       $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
      return view('sale_return_without_gst_invoice')->with(['items' => $items,'company_data' => $company_data,'month_arr' => $month_arr,'seller_info'=>$seller_info,'configuration'=>$configuration,'sale_return'=>$sale_return]);
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function edit($id){
      Gate::authorize('action-module',69);
      $sale_return =  SalesReturn::find($id);
      $sale_return_description =  SaleReturnDescription::join('units','sale_return_descriptions.unit','=','units.id')
                  ->select(['sale_return_descriptions.*','units.s_name'])
                  ->where('sale_return_id',$id)
                  ->get();
      $sale_return_sundry =  SaleReturnSundry::join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','sale_return_sundries.*'])
                                             ->where('sale_return_id',$id)
                                             ->get();
      $groups = DB::table('account_groups')
                     ->whereIn('heading', [3,11])
                     ->where('heading_type','group')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id',Session::get('user_company_id'))
                     ->pluck('id');
      $groups->push(3);
      $groups->push(11);
      $party_list = Accounts::select('accounts.*','states.state_code')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('accounts.delete', '=', '0')
                              ->where('accounts.status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $groups)
                              ->orderBy('account_name')
                              ->get();      
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $GstSettings = (object)NULL;
      $GstSettings->mat_center = array();
      $mat_series = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();

         $mat_series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();

         $mat_series = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($mat_series as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }
         }
      }
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale-return');
      }
      $financial_year = Session::get('default_fy'); 
      foreach ($mat_series as $key => $value) {
         if($sale_return->series_no==$value->series){
            $mat_series[$key]->invoice_start_from =  $sale_return->sale_return_no;
            $mat_series[$key]->without_invoice_start_from =  $sale_return->sale_return_no;
            $mat_series[$key]->invoice_prefix =  $sale_return->sr_prefix;
         }else{
            $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$value->series)
               ->where('configuration_for','CREDIT NOTE')
               ->where('status','1')
               ->first();
            $voucher_no = SalesReturn::select('sale_return_no')                   
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('sr_nature','!=',"WITHOUT GST")
                        ->where('delete','=','0')
                        ->where('series_no',$value->series)
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
            
            if(!$voucher_no){
               $mat_series[$key]->invoice_start_from =  "001";
            }else{
               $invc = $voucher_no + 1;
               $invc = sprintf("%'03d", $invc);
               $mat_series[$key]->invoice_start_from =  $invc;
            }
            //Without GST
            $sale_return_no_without = SalesReturn::select('sale_return_no')                     
                                                ->where('company_id',Session::get('user_company_id'))
                                                ->where('financial_year','=',$financial_year)
                                                ->where('sr_nature','=',"WITHOUT GST")
                                                ->where('series_no','=',$value->series)
                                                ->where('delete','=','0')
                                                ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
            if(!$sale_return_no_without){
               $mat_series[$key]->without_invoice_start_from =  "001";
            }else{
               $invc = $sale_return_no_without + 1;
               $invc = sprintf("%'03d", $invc);
               $mat_series[$key]->without_invoice_start_from =  $invc;
            }
         }
         
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->get();
      //Vendor
      $vendors = Accounts::select('id','account_name','gstin')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->where('status','1')
               ->where('delete','0')
               ->where('gstin','!=','')
               ->orderBy('account_name')
               ->get();
      //Item
      $fixed_asset_group = AccountGroups::where('heading','6')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type',null)
                                       ->where('heading_type','')
                                       ->pluck('id');
      $fixed_asset_group->push(12);//DIRECT EXPENSE
      $fixed_asset_group->push(15);//INDIRECT EXPENSE
      $fixed_asset_group->push(6);//UNSECURED LOANS
      $fixed_asset_group->push(13);//DIRECT INCOME
      $fixed_asset_group->push(14);//INDIRECT INCOME
      $sub_group = AccountGroups::whereIn('heading',$fixed_asset_group)
                                       ->where('heading_type',"group")
                                       ->pluck('id');
      $fixed_asset_group->merge($sub_group);
      $items = Accounts::select('id','account_name')
               ->whereIn('company_id',[Session::get('user_company_id'),0])
               ->whereIn('under_group',$fixed_asset_group)
               ->where('status','1')
               ->where('delete','0')
               ->orderBy('account_name')
               ->get();
      $all_account_list = Accounts::leftjoin('states','accounts.state','=','states.id')
               ->where('delete', '=', '0')
               ->where('status', '=', '1')
               ->where('tax_type', '=', 'TDS/TCS')
               ->whereIn('company_id', [Session::get('user_company_id'),0])
               ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
               ->orderBy('account_name')
               ->get();   
      //Withoyt GST and Without Item Data
      $without_gst = SaleReturnWithoutGstEntry::where('sale_return_id',$id)->get();   
      $manageitems = DB::table('manage_items')->where('manage_items.company_id', Session::get('user_company_id'))
            ->select('units.s_name as unit', 'manage_items.*')
            ->where('manage_items.delete', '0')
            ->where('manage_items.status', '1')
            ->where('manage_items.u_name', '!=', '')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->orderBy('manage_items.name')
            ->get();           
      return view('editSaleReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('mat_series', $mat_series)->with('sale_return', $sale_return)->with('sale_return_description', $sale_return_description)->with('sale_return_sundry', $sale_return_sundry)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list)->with('without_gst', $without_gst)->with('merchant_gst', $sale_return->merchant_gst)->with('manageitems', $manageitems);
   }
   public function update(Request $request){
      Gate::authorize('action-module',69);
      // echo "<pre>";
      // print_r($request->all());die;
      $validated = $request->validate([
         'date' => 'required',
         'party' => 'required',    
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      //Check Item Empty or not      
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
            return $this->failedMessage('Plases Select Item','sale-return-edit/'.$request->input('sale_return_edit_id'));
         }
      }      
      $account = Accounts::where('id',$request->input('party'))->first();
      $financial_year = Session::get('default_fy');
      $sale = SalesReturn::find($request->input('sale_return_edit_id'));
      $last_date = $sale->date;
      $sale->date = $request->input('date');
      $voucher_no = $request->input('voucher_no');
      if($request->input('voucher_no')=="OTHER"){
         $voucher_no = "";
      }
      $sale->invoice_no = $voucher_no;
      $sale->party = $request->input('party');

      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $sale->taxable_amt = $request->input('taxable_amt');
         $sale->total = $request->input('total'); 
         $sale->remark = $request->input('narration_withgst'); 
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $sale->taxable_amt = $request->input('net_amount');
         $sale->total = $request->input('total_amount');
         $sale->remark = $request->input('remark');
      }
      $sale->voucher_type = $request->input('voucher_type');
      $voucher_prefix = $request->input('voucher_prefix');      
      $sale->sr_nature = $request->input('nature');
      $sale->sr_type = $request->input('type');
      $sale->sr_prefix = $voucher_prefix;
      $sale->series_no = $request->input('series_no');
      $sale->material_center = $request->input('material_center');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->station = $request->input('station');
      $sale->sale_return_no = $request->input('sale_return_no');
      $sale->tax_cgst = $request->input('cgst');
      $sale->tax_sgst = $request->input('sgst');
      $sale->tax_igst = $request->input('igst');
        $sale->billing_gst = $account->gstin;
      $sale->billing_state = $account->state;
      $sale->financial_year = $financial_year;
      $sale->other_invoice_no = $request->input('other_invoice_no');
      $sale->other_invoice_date = $request->input('other_invoice_date');
      $sale->other_invoice_against = $request->input('other_invoice_against');
      $sale->sale_bill_id = $request->input('sale_bill_id');
      $sale->save();
      if($sale->id){
         $desc_item_arr = SaleReturnDescription::where('sale_return_id',$sale->id)
                                                   ->pluck('goods_discription')
                                                   ->toArray();
         SaleReturnDescription::where('sale_return_id',$sale->id)->delete();
         ItemLedger::where('source_id',$sale->id)->where('source',4)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',3)->delete();
         SaleReturnSundry::where('sale_return_id',$sale->id)->delete();
         SaleReturnWithoutGstEntry::where('sale_return_id',$sale->id)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',9)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',10)->delete();
         ItemAverageDetail::where('sale_return_id',$sale->id)
                           ->where('type','SALE RETURN')
                           ->delete();
         $update_item_arr = [];
         if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
            $goods_discriptions = $request->input('goods_discription');
            $qtys = $request->input('qty');
            $units = $request->input('units');
            $prices = $request->input('price');
            $amounts = $request->input('amount');            
            foreach ($goods_discriptions as $key => $good) {
               if($good=="" || $amounts[$key]==""){
                  continue;
               }
               $desc = new SaleReturnDescription;
               $desc->sale_return_id = $sale->id;
               $desc->goods_discription = $good;
               $desc->qty = $qtys[$key];
               $desc->unit = $units[$key];
               $desc->price = $prices[$key];
               $desc->amount = $amounts[$key];
               $desc->status = '1';
               $desc->save();
               //ADD ITEM LEDGER
               if($request->input('type')=="WITH ITEM"){
                  if($qtys[$key]!="" && $prices[$key]!="" && $prices[$key]!=0 && $qtys[$key]!=0){
                     $item_ledger = new ItemLedger();
                     $item_ledger->item_id = $good;
                     $item_ledger->in_weight = $qtys[$key];
                     $item_ledger->txn_date = $request->input('date');
                     $item_ledger->series_no = $request->input('series_no');
                     $item_ledger->price = $prices[$key];
                     $item_ledger->total_price = $amounts[$key];
                     $item_ledger->company_id = Session::get('user_company_id');
                     $item_ledger->source = 4;
                     $item_ledger->source_id = $sale->id;
                     $item_ledger->created_by = Session::get('user_id');
                     $item_ledger->created_at = date('d-m-Y H:i:s');
                     $item_ledger->save();
                  }
               }
            }
            $bill_sundrys = $request->input('bill_sundry');
            $tax_rate = $request->input('tax_rate');
            $bill_sundry_amounts = $request->input('bill_sundry_amount');
            foreach ($bill_sundrys as $key => $bill) {
               if($bill_sundry_amounts[$key]=="" || $bill==""){
                  continue;
               }
               $sundry = new SaleReturnSundry;
               $sundry->sale_return_id = $sale->id;
               $sundry->bill_sundry = $bill;
               $sundry->rate = $tax_rate[$key];
               $sundry->amount = $bill_sundry_amounts[$key];
               $sundry->status = '1';
               $sundry->save();
               //ADD DATA IN CGST ACCOUNT
               $billsundry = BillSundrys::where('id', $bill)->first();
               if($billsundry->adjust_sale_amt=='No'){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $billsundry->sale_amt_account;
                  if($billsundry->nature_of_sundry=='subtractive'){
                     $ledger->credit = $bill_sundry_amounts[$key];
                  }else{
                     $ledger->debit = $bill_sundry_amounts[$key];
                  }
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->map_account_id = $request->input('party');
                  $ledger->entry_type = 3;
                  $ledger->entry_type_id = $sale->id;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //$roundoff = $roundoff - $bill_sundry_amounts[$key];
               }
            }
             //Average Calculation
            if($request->input('type')=="WITH ITEM"){
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
                  array_push($update_item_arr,$good); 
               }
               foreach ($sale_item_array as $key => $value) {
                  //Add Data In Average Details table
                  $average_detail = new ItemAverageDetail;
                  $average_detail->entry_date = $request->date;
                  $average_detail->series_no = $request->input('series_no');
                  $average_detail->item_id = $key;
                  $average_detail->type = 'SALE RETURN';
                  $average_detail->sale_return_id = $sale->id;
                  $average_detail->sale_return_weight = $value;
                  $average_detail->company_id = Session::get('user_company_id');
                  $average_detail->created_at = Carbon::now();
                  $average_detail->save();
                  CommonHelper::RewriteItemAverageByItem($request->date,$key,$request->input('series_no'));               
               }
            }
           
            
            
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party');
            $ledger->credit = $request->input('total');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 3;
            $ledger->entry_type_id = $sale->id;
            $ledger->map_account_id = 35;//Sale
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            //ADD DATA IN Sale ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = 35;//Sale
            $ledger->debit = $request->input('taxable_amt');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->map_account_id = $request->input('party');
            $ledger->entry_type = 3;
            $ledger->entry_type_id = $sale->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($desc_item_arr as $key => $value) {
               if(!array_key_exists($value, $update_item_arr)){
                  CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
               }
            }
            return redirect('sale-return-invoice/'.$sale->id)->withSuccess('Sale return added successfully!'); 
         }else if($request->input('nature')=="WITH GST" && $request->input('type')=="WITHOUT ITEM"){
            //Ledger Entry
            
            $account_info = Accounts::select('under_group')->where('id',$request->input('party'))->first();
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party');
            $ledger->credit = $request->input('total_amount');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 10;
            $ledger->map_account_id = $request->input('item')[0];
            $ledger->entry_type_id = $sale->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($request->input('item') as $key => $item){
               $percentage = $request->input('percentage')[$key];
               $amount = $request->input('without_item_amount')[$key];
               $hsn = $request->input('hsn')[$key];
               $unit_code = $request->input('unit_code')[$key];
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->type = "Debit";
               $sale_return_without->account_name = $item;
               $sale_return_without->debit = $amount;
               $sale_return_without->percentage = $percentage;  
               $sale_return_without->hsn_code = $hsn;
               $sale_return_without->unit_code = $unit_code;
               $sale_return_without->status = '1';
               $sale_return_without->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $item;
               $ledger->debit = $amount;                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $request->input('party');
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            if(!empty($request->input('igst'))){
               if($account_info->under_group==3){
                  $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $sundry = BillSundrys::select('sale_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->sale_amt_account;
                  }
               }else{
                  $sundry = BillSundrys::select('purchase_amt_account')
                                       ->where('nature_of_sundry','IGST')
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
                  $account_name = "";
                  if($sundry){
                     $account_name = $sundry->purchase_amt_account;
                  }
               }               
               
               //detor sale
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $account_name;
               $ledger->debit = $request->input('igst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }else{
               //CGST
               $cgst_account_name = "";
               if($account_info->under_group==3){
                  $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $cgst_sundry = BillSundrys::select('sale_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->sale_amt_account;
                  }
               }else{
                  $cgst_sundry = BillSundrys::select('purchase_amt_account')
                        ->where('nature_of_sundry','CGST')
                        ->where('company_id',Session::get('user_company_id'))
                        ->first();
                  if($cgst_sundry){
                     $cgst_account_name = $cgst_sundry->purchase_amt_account;
                  }
               }
               //SGST 
               $sgst_account_name = "";
               if($account_info->under_group==3){
                  $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->purchase_amt_account;
                  }
               }else if($account_info->under_group==11){
                  $sgst_sundry = BillSundrys::select('sale_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->sale_amt_account;
                  }
               }else{
                  $sgst_sundry = BillSundrys::select('purchase_amt_account')
                           ->where('nature_of_sundry','SGST')
                           ->where('company_id',Session::get('user_company_id'))
                           ->first();               
                  if($sgst_sundry){
                     $sgst_account_name = $sgst_sundry->purchase_amt_account;
                  }
               }               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $cgst_account_name;
               $ledger->debit = $request->input('cgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->debit = $request->input('sgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 10;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $sale->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            foreach ($desc_item_arr as $key => $value) {
               if(!array_key_exists($value, $update_item_arr)){
                  CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
               }
            }
            return redirect('sale-return-without-item-invoice/'.$sale->id)->withSuccess('Sale return added successfully!');
         }else if($request->input('nature')=="WITHOUT GST"){
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');        
            $narrations = $request->input('narration');
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->account_name = $account;
               $sale_return_without->debit = isset($debits[$key]) ? $debits[$key] : '0';
               $sale_return_without->narration = $narrations[$key];
               $sale_return_without->status = '1';
               $sale_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
               $ledger->debit = $debits[$key];                          
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 9;
               $ledger->entry_type_id = $sale->id;
               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();

               $debit_total = $debit_total + $debits[$key];
               $i++;
            }
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party');
            $ledger->credit = $debit_total;                          
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 9;
            $ledger->entry_type_id = $sale->id;
            //$ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            SalesReturn::where('id',$sale->id)->update(['total'=>$debit_total]);
         }
         foreach ($desc_item_arr as $key => $value) {
            if(!array_key_exists($value, $update_item_arr)){
               CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
            }
         }
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('sale-return-without-gst-invoice/'.$sale->id)->withSuccess('Sale return updated successfully!');
         }     
         
      }else{
         return $this->failedMessage('Something went wrong','purchase/create');
      }
   }
   public function generateEinvoice(Request $request){      
      $einvoice_username = ""; $einvoice_password = "";
      $einvoice_gst = ""; $einvoice_company = "";
      ini_set('serialize_precision','-1');
      $validated = $request->validate([
        'id' => 'required',
      ]);
      $sale = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                    ->join('companies','sales_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('sales_returns.id',$request->id)
                    ->first(['sales_returns.*','accounts.print_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);
      if(!$sale){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"Sale Return Not Found."
         );
         return json_encode($res);
      }
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
      $shipp_name = $sale->print_name;
      $shipp_address = $sale->address;
      $shipp_gst = $sale->gstin;
      $shipp_state = $sale->name;
      $shipp_pincode = $sale->pin_code;
      if($sale->sale_bill_id && !empty($sale->sale_bill_id)){
         $sale_info = Sales::find($sale->sale_bill_id);
         if(!empty($sale_info->shipping_name) && !empty($sale_info->shipping_name)){
            $acc = Accounts::select('print_name')->where('id',$sale_info->shipping_name)->first();
            $shipp_name = $acc->print_name;
            $shipp_address = $sale_info->shipping_address;
            $shipp_gst = $sale_info->shipping_gst;
            $shipp_state = $sale_info->shipping_state;
            $shipp_pincode = $sale_info->shipping_pincode; 
         }         
      }else{

      }      
      $CGST = null;$SGST = null;$IGST = null;$TCS = 0;
      $sundry = SaleReturnSundry::join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
                  ->select(['sale_return_sundries.rate','sale_return_sundries.amount','bill_sundry_type','adjust_sale_amt','nature_of_sundry','effect_gst_calculation'])
                  ->where('sale_return_id',$request->id)
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
      $total_item_price = SaleReturnDescription::where('sale_return_id',$request->id)->sum('amount');
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
         "Gstin"=>$sale->gstin,
         "LglNm"=>$sale->print_name,
         "TrdNm"=>$sale->print_name,
         "Pos"=>substr($sale->gstin,0,2),
         "Addr1"=>$sale->address,
         "Addr2"=>null,
         "Loc"=>$sale->name,
         "Pin"=>(int)$sale->pin_code,
         "Stcd"=>substr($sale->gstin,0,2),
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
      $item_data = SaleReturnDescription::join('manage_items','sale_return_descriptions.goods_discription','=','manage_items.id')
      ->join('units','manage_items.u_name','=','units.id')
                              ->where('sale_return_id',$request->id)
                              ->groupBy('hsn_code')
                              ->get( array(
                                DB::raw('SUM(qty) as tweight'),
                                DB::raw('SUM(amount) as tprice'),
                                DB::raw('hsn_code'),
                                DB::raw('manage_items.name'),
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
               "GstRt"=> 12,
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
      $docno = $sale->sr_prefix;
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
            "Typ"=>"CRN",
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
      //echo "<pre>";
      // $res = array(
      //    'status' => true,
      //    'data' => $einvoice_requset
      // );
      echo json_encode($einvoice_requset);
      die;
      
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
            $invoice_update = SalesReturn::find($request->id);
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
   public function generateEinvoiceWithoutItem(Request $request){      
      $einvoice_username = ""; $einvoice_password = "";
      $einvoice_gst = ""; $einvoice_company = "";
      ini_set('serialize_precision','-1');
      $validated = $request->validate([
        'id' => 'required',
      ]);
      $sale = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                    ->join('companies','sales_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('sales_returns.id',$request->id)
                    ->first(['sales_returns.*','accounts.print_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);
      if(!$sale){
         $res = array(
            'status' => false,
            'data' => "",
            "message"=>"Sale Return Not Found."
         );
         return json_encode($res);
      }
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
      $shipp_name = $sale->print_name;
      $shipp_address = $sale->address;
      $shipp_gst = $sale->gstin;
      $shipp_state = $sale->name;
      $shipp_pincode = $sale->pin_code;
      if($sale->sale_bill_id && !empty($sale->sale_bill_id)){
         $sale_info = Sales::find($sale->sale_bill_id);
         if(!empty($sale_info->shipping_name) && !empty($sale_info->shipping_name)){
            $acc = Accounts::select('print_name')->where('id',$sale_info->shipping_name)->first();
            $shipp_name = $acc->print_name;
            $shipp_address = $sale_info->shipping_address;
            $shipp_gst = $sale_info->shipping_gst;
            $shipp_state = $sale_info->shipping_state;
            $shipp_pincode = $sale_info->shipping_pincode; 
         }         
      }else{

      }      
      $CGST = null;$SGST = null;$IGST = null;$TCS = 0;$roundOff = 0;
      $CGST = $sale->tax_cgst;
      $SGST = $sale->tax_sgst;
      $IGST = $sale->tax_igst;
      
      $total_item_price = SaleReturnWithoutGstEntry::where('sale_return_id',$request->id)->sum('debit');
      $gst_amount = $CGST + $SGST + $IGST + $TCS;
      $net_total = $total_item_price;
      $grand_total = $sale->total;
      $AssVal = $net_total;
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
         "Gstin"=>$sale->gstin,
         "LglNm"=>$sale->print_name,
         "TrdNm"=>$sale->print_name,
         "Pos"=>substr($sale->gstin,0,2),
         "Addr1"=>$sale->address,
         "Addr2"=>null,
         "Loc"=>$sale->name,
         "Pin"=>(int)$sale->pin_code,
         "Stcd"=>substr($sale->gstin,0,2),
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
      $item_data = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
                              ->where('sale_return_id',$request->id)
                              ->groupBy('hsn_code')
                              ->get( array(
                                DB::raw('SUM(debit) as tprice'),
                                DB::raw('sale_return_without_gst_entry.hsn_code'),
                                DB::raw('accounts.print_name as name'),
                                DB::raw('percentage')
                              ));
      $i = 1;
      if(count($item_data)>0){
         foreach ($item_data as $key => $value) {
            $unit = "Unit Name..";            
            $item_total = $value->tprice;
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
            $unit_price = "Unit Price..";;
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
               "Qty"=> "Quntity..",
               "FreeQty"=> null,
               "Unit"=> $unit,
               "UnitPrice"=>(float)round($unit_price,2),
               "TotAmt"=>(float)$item_total,
               "Discount"=> null,
               "PreTaxVal"=> null,
               "AssAmt"=> (float)$item_total,
               "GstRt"=> $value->percentage,
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
      $docno = $sale->sr_prefix;
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
            "Typ"=>"CRN",
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
      //echo "<pre>";
      // $res = array(
      //    'status' => true,
      //    'data' => $einvoice_requset
      // );
      echo json_encode($einvoice_requset);
      die;
      
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
            $invoice_update = SalesReturn::find($request->id);
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
   public function generateEwaybillSaleReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                    ->join('companies','sales_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('sales_returns.id',$request->id)
                    ->first(['sales_returns.*','accounts.account_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);          
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
      $einvoice_data = json_decode($sale->einvoice_response);
      $Irn = $einvoice_data->Irn;
      $eway_bill_request = array(
         "Irn"=>$Irn,
         "Distance"=>(int)$request->distance,
         "TransMode"=>"1",
         "TransId"=>null,
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
            $invoice_update = SalesReturn::find($request->id);
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
   public function cancelEwaybillSaleReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = SalesReturn::join('companies','sales_returns.company_id','=','companies.id')
                    ->where('sales_returns.id',$request->id)
                    ->first(['sales_returns.*','companies.gst_config_type']);          
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
         CURLOPT_URL => 'https://api.mastergst.com/ewaybillapi/v1.03/authenticate?email=pram92500@gmail.com&username='.$einvoice_username.'&password='.$einvoice_password,
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
      curl_exec($curl);
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
            SalesReturn::where('id',$request->id)->update(['e_waybill_status'=>0,'eway_bill_response'=>'']);
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
   public function cancelEinvoiceSaleReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = SalesReturn::join('companies','sales_returns.company_id','=','companies.id')
                    ->where('sales_returns.id',$request->id)
                    ->first(['sales_returns.*','companies.gst_config_type']);          
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
            SalesReturn::where('id',$request->id)->update(['e_invoice_status'=>0,'status'=>'2','einvoice_response'=>'','total'=>'0']);
            if($sale->sr_nature=="WITH GST" && ($sale->sr_type=="WITH ITEM" || $sale->sr_type=="Rate Difference")){
               SaleReturnDescription::where('sale_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);            
               SaleReturnSundry::where('sale_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
               AccountLedger::where('entry_type',3)
                        ->where('entry_type_id',$request->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
               if($sale->sr_type=="WITH ITEM"){
                  ItemLedger::where('source',4)
                     ->where('source_id',$request->id)
                     ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                  ItemAverageDetail::where('sale_return_id',$request->id)
                     ->where('type','SALE RETURN')
                     ->delete();         
                  $desc = SaleReturnDescription::where('sale_return_id',$request->id)
                                 ->get();
                  foreach ($desc as $key => $value) {
                     CommonHelper::RewriteItemAverageByItem($sale->date,$value->goods_discription,$sale->series_no);
                  }
               }
               
            }else if($sale->sr_nature=="WITH GST" && $sale->sr_type=="WITHOUT ITEM"){
               SaleReturnWithoutGstEntry::where('sale_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);  
               AccountLedger::where('entry_type',10)
                        ->where('entry_type_id',$request->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            }            
           
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
}
