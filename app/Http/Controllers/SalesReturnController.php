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
use App\Models\SaleInvoiceTermCondition;
use App\Models\SaleReturnWithoutGstEntry;

use Session;
use DateTime;

class SalesReturnController extends Controller
{

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
       $input = $request->all();
      // Default date range (first day of current month to today)
$from_date = session('salesReturn_from_date', "01-" . date('m-Y'));
$to_date = session('salesReturn_to_date', date('d-m-Y'));

// Check if user has selected a date range
if (!empty($input['from_date']) && !empty($input['to_date'])) {
    $from_date = date('d-m-Y', strtotime($input['from_date']));
    $to_date = date('d-m-Y', strtotime($input['to_date']));
    
    // Store in session so it persists after refresh
    session(['salesReturn_from_date' => $from_date, 'salesReturn_to_date' => $to_date]);
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
      $sale = DB::table('sales_returns')
            ->select('sr_prefix','sr_nature','sr_type','sales_returns.id as sales_returns_id', 'sales_returns.date','sales_returns.series_no','sales_returns.financial_year', 'sales_returns.invoice_no','sale_return_no', 'sales_returns.total', DB::raw('(select account_name from accounts where accounts.id=sales_returns.party limit 1) as account_name'))
            ->whereRaw("STR_TO_DATE(sales_returns.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(sales_returns.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->where('company_id',Session::get('user_company_id'))
            ->where('delete','0')
            ->orderBy(\DB::raw("cast(sale_return_no as SIGNED)"), 'ASC')
            ->orderBy('sales_returns.created_at', 'ASC')
            ->get();
      return view('saleReturn')->with('sale', $sale)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
      
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
        
      $sale = new SalesReturn;
      $sale->date = $request->input('date');
      $sale->company_id = Session::get('user_company_id');
      $sale->invoice_no = $request->input('voucher_no');
      $sale->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $sale->taxable_amt = $request->input('taxable_amt');
         $sale->total = $request->input('total');         
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $sale->taxable_amt = $request->input('net_amount');
         $sale->total = $request->input('total_amount');
      }
      
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
      $sale->remark = $request->input('remark');
      $sale->station = $request->input('station');

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
                  if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
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
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->type = "Debit";
               $sale_return_without->account_name = $item;
               $sale_return_without->debit = $amount;
               $sale_return_without->percentage = $percentage;  
               $sale_return_without->hsn_code = $hsn;  
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
      $sale_return =  SalesReturn::find($request->sale_return_id);
      $sale_return->delete = '1';
      $sale_return->deleted_at = Carbon::now();
      $sale_return->deleted_by = Session::get('user_id');
      $sale_return->update();
      if($sale_return) {
         if($sale_return->sr_nature=="WITH GST" && ($sale_return->sr_type=="WITH ITEM" || $sale_return->sr_type=="RATE DIFFERENCE")){
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
      $sale_return = SalesReturn::leftjoin('sales','sales_returns.sale_bill_id','=','sales.id')
                                 ->leftjoin('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','sales.billing_state','=','states.id')
                                 ->where('sales_returns.id',$id)
                                 ->select(['sales_returns.date','sales_returns.id','sales_returns.invoice_no','sales_returns.total','sales.billing_name','sales.billing_address','sales.billing_pincode','sales.billing_gst','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.gr_pr_no','sales_returns.transport_name','sales_returns.station','sales.voucher_no','sales.date as sale_date','sales.series_no','sales.financial_year','sales_returns.series_no as sr_series_no','sales_returns.financial_year as sr_financial_year','sr_nature','sr_type','sr_prefix','sales.merchant_gst','accounts.address as party_address'])
                                 ->first();      
      $items_detail = DB::table('sale_return_descriptions')->where('sale_return_id', $id)
            ->select('units.s_name as unit', 'units.id as unit_id', 'sale_return_descriptions.qty', 'sale_return_descriptions.price', 'sale_return_descriptions.amount', 'manage_items.name as items_name', 'manage_items.id as item_id','manage_items.hsn_code','manage_items.gst_rate')
            ->join('units', 'sale_return_descriptions.unit', '=', 'units.id')
            ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->get();      
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
                           ->where(['delete' => '0', 'company_id' => $sale_return->company_id,'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {         
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])->first();
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
      return view('saleReturnInvoice')->with(['items_detail' => $items_detail, 'month_arr' => $month_arr,'configuration'=>$configuration, 'sale_sundry' => $sale_sundry,'company_data' => $company_data,'gst_detail'=>$gst_detail,'sale_return'=>$sale_return,'einvoice_status'=>$GstSettings->einvoice,'seller_info'=>$seller_info]);
   }
   public function saleReturnWithoutItemInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('sales_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname')
                                 ->where('sales_returns.id',$id)
                                 ->first();   
      $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
                                 ->where('sale_return_id', $id)
                                 ->select('debit','percentage','sale_return_without_gst_entry.hsn_code','accounts.account_name')
                                 ->get();     
      return view('sale_return_without_item_invoice')->with(['company_data' => $company_data,'sale_return'=>$sale_return,'items'=>$items]);
   }
   public function saleReturnWithoutGstInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('sales_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname')
                                 ->where('sales_returns.id',$id)
                                 ->first();   
      $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
                                 ->where('sale_return_id', $id)
                                 ->select('debit','percentage','sale_return_without_gst_entry.hsn_code','accounts.account_name')
                                 ->get();  
      return view('sale_return_without_gst_invoice')->with(['items' => $items,'company_data' => $company_data,'sale_return'=>$sale_return]);
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function edit($id){
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
      return view('editSaleReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('mat_series', $mat_series)->with('sale_return', $sale_return)->with('sale_return_description', $sale_return_description)->with('sale_return_sundry', $sale_return_sundry)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list)->with('without_gst', $without_gst);
   }
   public function update(Request $request){
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
      $sale->date = $request->input('date');
      $sale->invoice_no = $request->input('voucher_no');
      $sale->party = $request->input('party');

      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $sale->taxable_amt = $request->input('taxable_amt');
         $sale->total = $request->input('total');         
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $sale->taxable_amt = $request->input('net_amount');
         $sale->total = $request->input('total_amount');
      }
      $voucher_prefix = $request->input('voucher_prefix');;
      // if(!empty($request->input('voucher_prefix'))){
      //    $voucher_prefix_arr = explode("/",$request->input('voucher_prefix'));
      //    if(count($voucher_prefix_arr)>1){
      //       $voucher_prefix = $voucher_prefix_arr[0]."/".$voucher_prefix_arr[1]."/";
      //    }else if(count($voucher_prefix_arr)==1){
      //       $voucher_prefix = "";
      //    }
      // }
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
      $sale->financial_year = $financial_year;
      $sale->sale_bill_id = $request->input('sale_bill_id');
      $sale->save();
      if($sale->id){
         SaleReturnDescription::where('sale_return_id',$sale->id)->delete();
         ItemLedger::where('source_id',$sale->id)->where('source',4)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',3)->delete();
         SaleReturnSundry::where('sale_return_id',$sale->id)->delete();
         SaleReturnWithoutGstEntry::where('sale_return_id',$sale->id)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',9)->delete();
         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',10)->delete();

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
                  if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
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
               $sale_return_without = new SaleReturnWithoutGstEntry;
               $sale_return_without->sale_return_id = $sale->id;
               $sale_return_without->company_id = Session::get('user_company_id');
               $sale_return_without->type = "Debit";
               $sale_return_without->account_name = $item;
               $sale_return_without->debit = $amount;
               $sale_return_without->percentage = $percentage;  
               $sale_return_without->hsn_code = $hsn;  
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

         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('sale-return-without-gst-invoice/'.$sale->id)->withSuccess('Sale return updated successfully!');
         }     
         
      }else{
         return $this->failedMessage('Something went wrong','purchase/create');
      }
   }
}
