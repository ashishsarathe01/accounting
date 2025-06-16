<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDescription;
use App\Models\PurchaseReturnSundry;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchase;
use App\Models\PurchaseSundry;
use App\Models\PurchaseDescription;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\AccountGroups;
use App\Models\PurchaseReturnEntry;
use App\Models\ItemAverageDetail;
use App\Helpers\CommonHelper;
use App\Models\State;
use App\Models\Sales;
use Carbon\Carbon;
use DB;
use Session;
use DateTime;
use Gate;
class PurchaseReturnController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
  public function index(Request $request){
    $input = $request->all();

    // Default date range (first day of current month to today)
    $from_date = null;
    $to_date = null;

    // Check if user has selected a date range
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));

        // Store in session so it persists after refresh
        session([
            'purchaseReturn_from_date' => $from_date,
            'purchaseReturn_to_date' => $to_date
        ]);
    }

    Session::put('redirect_url','');

    // Financial year parsing
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04',$from.'-05',$from.'-06',$from.'-07',$from.'-08',$from.'-09',
        $from.'-10',$from.'-11',$from.'-12',$to.'-01',$to.'-02',$to.'-03'
    ];

    // Base query
    $query = DB::table('purchase_returns')
        ->select(
            'purchase_returns.id as purchases_id',
            'purchase_returns.date',
            'purchase_returns.sr_prefix',
            'purchase_returns.total',
            'purchase_return_no',
            'purchase_returns.series_no',
            'purchase_returns.financial_year',
            'sr_nature',
            'sr_type',
            DB::raw('(select account_name from accounts where accounts.id = purchase_returns.party limit 1) as account_name')
        )
        ->where('company_id', Session::get('user_company_id'))
        ->where('delete', '0');

    // Apply date filter only if user selected a range
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $query->whereRaw("
            STR_TO_DATE(purchase_returns.date, '%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "', '%Y-%m-%d')
            AND STR_TO_DATE(purchase_returns.date, '%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "', '%Y-%m-%d')
        ")
        ->orderBy(DB::raw("cast(purchase_return_no as SIGNED)"), 'ASC')
        ->orderBy('purchase_returns.created_at', 'ASC');
    } else {
        // No date selected, fetch last 10 entries
        $query->orderBy('financial_year', 'desc')
            ->orderBy(DB::raw("cast(purchase_return_no as SIGNED)"), 'desc')
            ->limit(10);
    }

    $purchase = $query->get()->reverse()->values(); // Optional: reverse for ascending display

    return view('purchaseReturn')
        ->with('purchase', $purchase)
        ->with('month_arr', $month_arr)
        ->with("from_date", $from_date)
        ->with("to_date", $to_date);
}


    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',77);
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
        $manageitems = DB::table('manage_items')
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '0')
                        ->where('manage_items.status', '1')
                        ->where('manage_items.u_name', '!=', '')
                        ->select('units.s_name as unit', 'manage_items.*')
                        ->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->get();

        $companyData = Companies::where('id', Session::get('user_company_id'))->first();

        $GstSettings = (object)NULL;
        $GstSettings->mat_center = array();
        if ($companyData->gst_config_type == "single_gst") {
            $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
        } elseif ($companyData->gst_config_type == "multiple_gst") {
            $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
        }
        if(!$GstSettings || !isset($companyData->gst_config_type)){
           return $this->failedMessage('Please Enter GST Configuration!','purchase-return');
        }
        $purchaseData = Purchase::where('company_id', Session::get('user_company_id'))->orderBy('id', 'desc')->limit(1)->get();
         $mat_series = array();
         if($companyData->gst_config_type == "single_gst"){
            $mat_series = DB::table('gst_settings')
                              ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                              ->get();
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                              ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                              ->get();
            if(count($branch)>0){
               $mat_series = $mat_series->merge($branch);
            }            
         }else if($companyData->gst_config_type == "multiple_gst"){
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
         $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->get();
         $financial_year = Session::get('default_fy');
         foreach ($mat_series as $key => $value) {
            $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
                  ->where('series',$value->series)
                  ->where('configuration_for','DEBIT NOTE')
                  ->where('status','1')
                  ->first();
            //With GST
            $purchase_return_no = PurchaseReturn::select('purchase_return_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','!=',"WITHOUT GST")
                           ->where('series_no','=',$value->series)
                           ->where('delete','=','0')
                           ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
            if(!$purchase_return_no){
               if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  $mat_series[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
               }else{
                  $mat_series[$key]->invoice_start_from =  "001";
               }
            }else{
               $invc = $purchase_return_no + 1;
               $invc = sprintf("%'03d", $invc);
               $mat_series[$key]->invoice_start_from =  $invc;
            }
            //Without GST
            $purchase_return_no_without = PurchaseReturn::select('purchase_return_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','=',"WITHOUT GST")
                           ->where('series_no','=',$value->series)
                           ->where('delete','=','0')
                           ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
            if(!$purchase_return_no_without){
               $mat_series[$key]->without_invoice_start_from =  "001";
            }else{
               $invc = $purchase_return_no_without + 1;
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
                     $invoice_prefix_wt .= '20' . $fy_parts[0] . '-20' . $fy_parts[1];
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
        return view('addPurchaseReturn')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_series', $mat_series)->with('bill_date', $bill_date)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list);
   }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      Gate::authorize('action-module',77);
      $validated = $request->validate([         
         'date' => 'required',
         'nature' => 'required',  
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      // echo "<pre>";
      // print_r($request->all());die;
      //Check Item Empty or not
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
            return $this->failedMessage('Plases Select Item','sale-return/create');
         }
      }
      $financial_year = Session::get('default_fy');
      if($request->input('manual_enter_invoice_no')=='0'){
         if($request->nature!="WITHOUT GST"){
            $purchase_return_no = PurchaseReturn::select('purchase_return_no')                   
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','!=',"WITHOUT GST")
                           ->where('delete','=','0')
                           ->where('series_no',$request->input('series_no'))
                           ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
            if(!$purchase_return_no){
               $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$request->input('series_no'))
               ->where('configuration_for','DEBIT NOTE')
               ->where('status','1')
               ->first();
               if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  $purchase_return_no =  sprintf("%'03d",$series_configuration->invoice_start);
               }else{
                  $purchase_return_no = "001";
               }
            }else{
               $purchase_return_no++;
               $purchase_return_no = sprintf("%'03d", $purchase_return_no);
            }
         }else{
            $purchase_return_no = PurchaseReturn::select('purchase_return_no')                   
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('sr_nature','=',"WITHOUT GST")
                           ->where('delete','=','0')
                           ->where('series_no',$request->input('series_no'))
                           ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
            if(!$purchase_return_no){
               $purchase_return_no = "001";
            }else{
               $purchase_return_no++;
               $purchase_return_no = sprintf("%'03d", $purchase_return_no);
            }
         }
      }else{
         $purchase_return_no = $request->input('voucher_no');
      }
      $account = Accounts::where('id',$request->input('party_id'))->first();
      $purchase = new PurchaseReturn;
      $purchase->date = $request->input('date');
      $purchase->company_id = Session::get('user_company_id');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->voucher_type = $request->input('voucher_type');
      $purchase->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $purchase->taxable_amt = $request->input('taxable_amt');
         $purchase->total = $request->input('total');   
         $purchase->remark = $request->input('narration_withgst');      
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $purchase->taxable_amt = $request->input('net_amount');
         $purchase->total = $request->input('total_amount');
         $purchase->remark = $request->input('remark');
      }
      $purchase->sr_nature = $request->input('nature');
      $purchase->sr_type = $request->input('type');
      $voucher_prefix = $request->input('voucher_prefix');
      $purchase->sr_prefix = $voucher_prefix;

      $purchase->series_no = $request->input('series_no');
      $purchase->material_center = $request->input('material_center');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->station = $request->input('station');
      $purchase->merchant_gst = $request->input('merchant_gst');
      $purchase->billing_gst = $account->gstin;
      $purchase->billing_state = $account->state;
      $purchase->tax_cgst = $request->input('cgst');
      $purchase->tax_sgst = $request->input('sgst');
      $purchase->tax_igst = $request->input('igst');
      $purchase->other_invoice_no = $request->input('other_invoice_no');
      $purchase->other_invoice_date = $request->input('other_invoice_date');
      $purchase->other_invoice_against = $request->input('other_invoice_against');
      
      $purchase->purchase_return_no = $purchase_return_no;
      $purchase->financial_year = $financial_year;
      $purchase->purchase_bill_id = $request->input('purchase_bill_id');
      $purchase->save();
     // $roundoff = $request->input('total')-$request->input('taxable_amt');
      if($purchase->id){
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
               $desc = new PurchaseReturnDescription;
               $desc->purchase_return_id = $purchase->id;
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
                     $item_ledger->out_weight = $qtys[$key];
                     $item_ledger->txn_date = $request->input('date');
                     $item_ledger->price = $prices[$key];
                     $item_ledger->total_price = $amounts[$key];
                     $item_ledger->company_id = Session::get('user_company_id');
                     $item_ledger->source = 5;
                     $item_ledger->source_id = $purchase->id;
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
               $sundry = new PurchaseReturnSundry;
               $sundry->purchase_return_id = $purchase->id;
               $sundry->bill_sundry = $bill;
               $sundry->rate = $tax_rate[$key];
               $sundry->amount = $bill_sundry_amounts[$key];
               $sundry->status = '1';
               $sundry->save();
               //ADD DATA IN CGST ACCOUNT
               $billsundry = BillSundrys::where('id', $bill)->first();
               if($billsundry->adjust_sale_amt=='No'){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $billsundry->purchase_amt_account;
                  if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                     $ledger->debit = $bill_sundry_amounts[$key];
                  }else{
                     $ledger->credit = $bill_sundry_amounts[$key];
                  }
                  $ledger->series_no = $request->input('series_no');
                  $ledger->txn_date = $request->input('date');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 4;
                  $ledger->entry_type_id = $purchase->id;
                  $ledger->map_account_id = $request->input('party_id');
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //$roundoff = $roundoff - $bill_sundry_amounts[$key];
               }
            }
            //Average Calculation
            if($request->type=="WITH ITEM" || $request->type=="RATE DIFFERENCE"){               
               $goods_discriptions = $request->input('goods_discription');
               $qtys = $request->input('qty');
               $amounts = $request->input('amount');
               $purchase_return_item_array = [];$item_average_total = 0;
               foreach($goods_discriptions as $key => $good){
                  if($good=="" || $qtys[$key]==""){
                     continue;
                  }
                  array_push($purchase_return_item_array,array("item"=>$good,"quantity"=>$qtys[$key],"amount"=>$amounts[$key]));
                  $item_average_total = $item_average_total + $amounts[$key];
               }
               //Sundry
               $additive_sundry_amount_first = 0;$subtractive_sundry_amount_first = 0;
               $bill_sundrys = $request->input('bill_sundry');
               $bill_sundry_amounts = $request->input('bill_sundry_amount');
               foreach($bill_sundrys as $key => $bill){
                  if($bill_sundry_amounts[$key]=="" || $bill==""){
                     continue;
                  }
                  $billsundry = BillSundrys::where('id', $bill)->first();  
                  if($billsundry->nature_of_sundry=="OTHER"){
                     if($billsundry->bill_sundry_type=="additive"){
                        $additive_sundry_amount_first = $additive_sundry_amount_first + $bill_sundry_amounts[$key];
                     }else if($billsundry->bill_sundry_type=="subtractive"){
                        $subtractive_sundry_amount_first = $subtractive_sundry_amount_first + $bill_sundry_amounts[$key];
                     }
                  }
               }
               foreach ($purchase_return_item_array as $key => $value) {
                  $subtractive_sundry_amount = 0;$additive_sundry_amount = 0;
                  if($additive_sundry_amount_first>0){
                     $additive_sundry_amount = ($value['amount']/$item_average_total)*$additive_sundry_amount_first;
                  }
                  if($subtractive_sundry_amount_first>0){
                     $subtractive_sundry_amount = ($value['amount']/$item_average_total)*$subtractive_sundry_amount_first;
                  }
                  $additive_sundry_amount = round($additive_sundry_amount,2);
                  $subtractive_sundry_amount = round($subtractive_sundry_amount,2);
                  $average_amount = $value['amount'] + $additive_sundry_amount - $subtractive_sundry_amount;
                  $average_amount =  round($average_amount,2);
                  $average_price = $average_amount/$value['quantity'];
                  $average_price =  round($average_price,6);
                  //Add Data In Average Details table
                  
                  $average_detail = new ItemAverageDetail;
                  $average_detail->entry_date = $request->date;
                  $average_detail->series_no = $request->input('series_no');
                  $average_detail->item_id = $value['item'];
                  $average_detail->type = 'PURCHASE RETURN';
                  $average_detail->purchase_return_id = $purchase->id;
                  if($request->type=="WITH ITEM"){
                     $average_detail->purchase_return_weight = $value['quantity'];
                  }                  
                  $average_detail->purchase_return_amount = $value['amount'];
                  $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
                  $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
                  $average_detail->purchase_total_amount = $average_amount;
                  $average_detail->company_id = Session::get('user_company_id');
                  $average_detail->created_at = Carbon::now();
                  $average_detail->save();
                  CommonHelper::RewriteItemAverageByItem($request->date,$value['item'],$request->input('series_no'));                  
               }
            }
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->series_no = $request->input('series_no');
            $ledger->debit = $request->input('total');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 4;
            $ledger->entry_type_id = $purchase->id;
            $ledger->map_account_id = 36;//Purchase
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            //ADD DATA IN Sale ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = 36;//Purchase
            $ledger->series_no = $request->input('series_no');
            $ledger->credit = $request->input('taxable_amt');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 4;
            $ledger->entry_type_id = $purchase->id;
            $ledger->map_account_id = $request->input('party_id');
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();   
            return redirect('purchase-return-invoice/'.$purchase->id)->withSuccess('Purchase return added successfully!');
         }else if($request->input('nature')=="WITH GST" && $request->input('type')=="WITHOUT ITEM"){
            //Ledger Entry
            $account_info = Accounts::select('under_group')->where('id',$request->input('party_id'))->first();
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->series_no = $request->input('series_no');
            $ledger->debit = $request->input('total_amount');                       
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 13;
            $ledger->map_account_id = $request->input('item')[0];
            $ledger->entry_type_id = $purchase->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($request->input('item') as $key => $item){
               $percentage = $request->input('percentage')[$key];
               $amount = $request->input('without_item_amount')[$key];
               $hsn = $request->input('hsn')[$key];
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->type = "Credit";
               $purchase_return_without->account_name = $item;
               $purchase_return_without->debit = $amount;
               $purchase_return_without->percentage = $percentage;  
               $purchase_return_without->hsn_code = $hsn;  
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $item;
               $ledger->series_no = $request->input('series_no');
               $ledger->credit = $amount;                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $request->input('party_id');
               $ledger->entry_type_id = $purchase->id;
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
               $ledger->series_no = $request->input('series_no');
               $ledger->credit = $request->input('igst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $purchase->id;
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
               $ledger->credit = $request->input('cgst');  
               $ledger->series_no = $request->input('series_no');                     
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $purchase->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->credit = $request->input('sgst');
               $ledger->series_no = $request->input('series_no');
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $purchase->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            return redirect('purchase-return-without-item-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }else if($request->input('nature')=="WITHOUT GST"){
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');        
            $narrations = $request->input('narration');
            $narration_without_gst = $request->input('long_narration');
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->account_name = $account;
               $purchase_return_without->credit = isset($debits[$key]) ? $debits[$key] : '0';
              // $purchase_return_without->narration = $narrations[$key];
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party_id');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
               $ledger->credit = $debits[$key];
               $ledger->series_no = $request->input('series_no');
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 12;
               $ledger->entry_type_id = $purchase->id;
               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $debit_total = $debit_total + $debits[$key];
               $i++;
            }
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->debit = $debit_total;
            $ledger->series_no = $request->input('series_no');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 12;
            $ledger->entry_type_id = $purchase->id;
            //$ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            PurchaseReturn::where('id',$purchase->id)->update(['total'=>$debit_total,'taxable_amt'=>$debit_total,'remark'=>$narration_without_gst]);
            return redirect('purchase-return-without-gst-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }                  
         return redirect('purchase-return')->withSuccess('Purchase return added successfully!');
      }else{
         $this->failedMessage('Something went wrong','purchase-return/create');
      }
   }
   public function delete(Request $request){
      Gate::authorize('action-module',48);   
      $purchase_return =  PurchaseReturn::find($request->purchase_return_id);
      $purchase_return->delete = '1';
      $purchase_return->deleted_at = Carbon::now();
      $purchase_return->deleted_by = Session::get('user_id');
      $purchase_return->update();
      if($purchase_return) {
         if($purchase_return->sr_nature=="WITH GST" && ($purchase_return->sr_type=="WITH ITEM" || $purchase_return->sr_type=="RATE DIFFERENCE")){
            if($purchase_return->sr_type=="WITH ITEM" || $purchase_return->sr_type=="RATE DIFFERENCE"){
               ItemAverageDetail::where('purchase_return_id',$request->purchase_return_id)
                           ->where('type','PURCHASE RETURN')
                           ->delete();         
               $desc = PurchaseReturnDescription::where('purchase_return_id',$request->purchase_return_id)
                              ->get();
               foreach ($desc as $key => $value) {
                  CommonHelper::RewriteItemAverageByItem($purchase_return->date,$value->goods_discription,$purchase_return->series_no);
               }
            }
            PurchaseReturnDescription::where('purchase_return_id',$request->purchase_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',4)
                           ->where('entry_type_id',$request->purchase_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            PurchaseReturnSundry::where('purchase_return_id',$request->purchase_return_id)
                           ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            ItemLedger::where('source',5)
                           ->where('source_id',$request->purchase_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }else if($purchase_return->sr_nature=="WITH GST" && $purchase_return->sr_type=="WITHOUT ITEM"){
            PurchaseReturnEntry::where('purchase_return_id',$request->purchase_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',13)
                           ->where('entry_type_id',$request->purchase_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }else if($purchase_return->sr_nature=="WITHOUT GST"){
            PurchaseReturnEntry::where('purchase_return_id',$request->purchase_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
            AccountLedger::where('entry_type',12)
                           ->where('entry_type_id',$request->purchase_return_id)
                           ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         }  
         
         return redirect('purchase-return')->withSuccess('Purchase Return deleted successfully!');
      }
   }
   public function purchaseReturnInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $purchase_ret = PurchaseReturn::where('id',$id)->first();
      if($purchase_ret->voucher_type=="SALE"){
         $purchase_return = PurchaseReturn::join('sales','purchase_returns.purchase_bill_id','=','sales.id')
                                 ->leftjoin('states','sales.billing_state','=','states.id')
                                 ->where('purchase_returns.id',$id)
                                 ->select(['purchase_returns.date','purchase_returns.invoice_no','purchase_returns.total','sales.billing_name','sales.billing_address','sales.billing_pincode','sales.billing_gst','states.name as sname','purchase_return_no','purchase_returns.vehicle_no','purchase_returns.gr_pr_no','purchase_returns.transport_name','purchase_returns.station','sales.voucher_no','sales.date as purchase_date','sales.series_no','sales.financial_year','purchase_returns.series_no as dr_series_no','purchase_returns.financial_year as dr_financial_year','sr_prefix','purchase_returns.id','purchase_returns.voucher_type'])
                                 ->first();      
      }else if($purchase_ret->voucher_type=="PURCHASE"){     
         $purchase_return = PurchaseReturn::join('purchases','purchase_returns.purchase_bill_id','=','purchases.id')
                                 ->leftjoin('states','purchases.billing_state','=','states.id')
                                 ->where('purchase_returns.id',$id)
                                 ->select(['purchase_returns.date','purchase_returns.invoice_no','purchase_returns.total','purchases.billing_name','purchases.billing_address','purchases.billing_pincode','purchases.billing_gst','states.name as sname','purchase_return_no','purchase_returns.vehicle_no','purchase_returns.gr_pr_no','purchase_returns.transport_name','purchase_returns.station','purchases.voucher_no','purchases.date as purchase_date','purchases.series_no','purchases.financial_year','purchase_returns.series_no as dr_series_no','purchase_returns.financial_year as dr_financial_year','sr_prefix','purchase_returns.id','purchase_returns.voucher_type'])
                                 ->first();  
      }else if($purchase_ret->voucher_type=="OTHER"){
           $purchase_return = PurchaseReturn::leftjoin('states','purchase_returns.billing_state','=','states.id')
                                 ->join('accounts','purchase_returns.party','=','accounts.id')
                                 ->where('purchase_returns.id',$id)
                                 ->select(['purchase_returns.date','purchase_returns.invoice_no','purchase_returns.total','accounts.account_name as billing_name','accounts.address as billing_address','accounts.pin_code as billing_pincode','purchase_returns.billing_gst','states.name as sname','purchase_return_no','purchase_returns.vehicle_no','purchase_returns.gr_pr_no','purchase_returns.transport_name','purchase_returns.station','purchase_returns.series_no as dr_series_no','purchase_returns.financial_year as dr_financial_year','sr_prefix','purchase_returns.id','purchase_returns.voucher_type','other_invoice_no','other_invoice_date'])
                                 ->first(); 
      }
    $items_detail = DB::table('purchase_return_descriptions')
    ->where('purchase_return_id', $id)
    ->join('units', 'purchase_return_descriptions.unit', '=', 'units.id')
    ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
    ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
    ->where('purchase_returns.sr_type', 'WITH ITEM')
    ->select(
        'units.s_name as unit',
        'units.id as unit_id',
        'purchase_return_descriptions.qty',
        'purchase_return_descriptions.price',
        'purchase_return_descriptions.amount',
        'manage_items.name as items_name',
        'manage_items.id as item_id',
        'manage_items.hsn_code',
        'manage_items.gst_rate'
    )
    ->get();
    
    $items_detail1 = DB::table('purchase_return_descriptions')
    ->where('purchase_return_id', $id)
    ->join('units', 'purchase_return_descriptions.unit', '=', 'units.id')
    ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
    ->join('purchase_returns', 'purchase_return_descriptions.purchase_return_id', '=', 'purchase_returns.id')
    ->where('purchase_returns.sr_type', 'RATE DIFFERENCE')
    ->select(
        DB::raw("''  as unit"),
        'units.id as unit_id',
         DB::raw("'' as qty"),
        DB::raw("'' as price"),
        'purchase_return_descriptions.amount',
        'manage_items.name as items_name',
        'manage_items.id as item_id',
        'manage_items.hsn_code',
        'manage_items.gst_rate'
    )
    ->get();
    
    $items_detail = $items_detail->merge($items_detail1);     
         $purchase_sundry = DB::table('purchase_return_sundries')
                           ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
                           ->where('purchase_return_id', $id)
                           ->select('purchase_return_sundries.bill_sundry','purchase_return_sundries.rate','purchase_return_sundries.amount','bill_sundrys.name')
                           ->orderBy('sequence')
                           ->get();
         $gst_detail = DB::table('purchase_return_sundries')
                           ->select('rate','amount')                     
                           ->where('purchase_return_id', $id)
                           ->where('rate','!=','0')
                           ->distinct('rate')                       
                           ->get(); 
         $max_gst = DB::table('purchase_return_sundries')
                           ->select('rate')                     
                           ->where('purchase_return_id', $id)
                           ->where('rate','!=','0')
                           ->max(\DB::raw("cast(rate as SIGNED)"));

         if(count($gst_detail)>0){
            foreach ($gst_detail as $key => $value){
               $rate = $value->rate;      
               if(substr($company_data->gst,0,2)==substr($purchase_return->billing_gst,0,2)){
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
                  $freight = PurchaseReturnSundry::select('amount')
                              ->where('purchase_return_id', $id)
                              ->where('bill_sundry',4)
                              ->first();
                  $insurance = PurchaseReturnSundry::select('amount')
                              ->where('purchase_return_id', $id)
                              ->where('bill_sundry',7)
                              ->first();
                  $discount = PurchaseReturnSundry::select('amount')
                              ->where('purchase_return_id', $id)
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
         }else if($company_data->gst_config_type == "multiple_gst") {         
            $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series' => $purchase_return->series_no])->first();
         }
         if(!$GstSettings){
            $GstSettings = (object)NULL;
            $GstSettings->ewaybill = 0;
            $GstSettings->einvoice = 0;
         }
         if($purchase_ret->voucher_type!="SALE"){
            $GstSettings->ewaybill = 0;
            $GstSettings->einvoice = 0;
         }

          if($company_data->gst_config_type == "single_gst") {
         $GstSettings1 = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $purchase_return->merchant_gst,'series'=>$purchase_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$purchase_return->merchant_gst,'branch_series'=>$purchase_return->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings1->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
        
            $GstSettings1 = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $purchase_return->merchant_gst])
                           ->first();
                         
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $purchase_return->merchant_gst,'series'=>$purchase_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$purchase_return->merchant_gst,'branch_series'=>$purchase_return->series_no])
                           ->first();
                           
                          
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings1->state)
                                 ->first();
                                 
                  $seller_info->sname = $state_info->name;                          
              
         }
         }
               $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
               $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();

               
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

      return view('purchaseReturnInvoice')->with(['items_detail' => $items_detail,'month_arr' => $month_arr,'configuration'=>$configuration,'seller_info'=>$seller_info, 'purchase_sundry' => $purchase_sundry,'company_data' => $company_data,'gst_detail'=>$gst_detail,'purchase_return'=>$purchase_return,'einvoice_status'=>$GstSettings->einvoice,'ewaybill_status'=>$GstSettings->ewaybill]);
   }
   public function purchaseReturnWithoutItemInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $purchase_return = PurchaseReturn::join('accounts','purchase_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('purchase_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname')
                                 ->where('purchase_returns.id',$id)
                                 ->first();   
      $items = PurchaseReturnEntry::join('accounts','purchase_return_entries.account_name','=','accounts.id')
                                 ->where('purchase_return_id', $id)
                                 ->select('debit','percentage','purchase_return_entries.hsn_code','accounts.account_name')
                                 ->get();     

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
         $GstSettings1 = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'gst_no' => $purchase_return->merchant_gst,'series'=>$purchase_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$purchase_return->merchant_gst,'branch_series'=>$purchase_return->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings1->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
        
            $GstSettings1 = DB::table('gst_settings_multiple')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $purchase_return->merchant_gst])
                           ->first();
                         
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'gst_no' => $purchase_return->merchant_gst,'series'=>$purchase_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_number'=>$purchase_return->merchant_gst,'branch_series'=>$purchase_return->series_no])
                           ->first();
                           
                          
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings1->state)
                                 ->first();
                                 
                  $seller_info->sname = $state_info->name;                          
              
         }
         }
               $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();

      return view('purchase_return_without_item_invoice')->with(['company_data' => $company_data,'month_arr' => $month_arr,'configuration'=>$configuration,'seller_info'=>$seller_info,'purchase_return'=>$purchase_return,'items'=>$items]);
   }
   public function purchaseReturnWithoutGstInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = PurchaseReturn::join('accounts','purchase_returns.party','=','accounts.id')
                                 ->join('states','accounts.state','=','states.id')
                                 ->select('purchase_returns.*','accounts.account_name','accounts.gstin','address','pin_code','states.name as sname')
                                 ->where('purchase_returns.id',$id)
                                 ->first();   
      $items = PurchaseReturnEntry::join('accounts','purchase_return_entries.account_name','=','accounts.id')
                                 ->where('purchase_return_id', $id)
                                 ->select('credit','percentage','purchase_return_entries.hsn_code','accounts.account_name')
                                 ->get();
                                 
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
         $GstSettings1 = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
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
               $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
               $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
      return view('purchase_return_without_gst_invoice')->with(['items' => $items,'month_arr' => $month_arr,'configuration'=>$configuration,'seller_info'=>$seller_info,'company_data' => $company_data,'sale_return'=>$sale_return]);
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function edit($id){
      Gate::authorize('action-module',47);  
      $purchase_return =  PurchaseReturn::find($id);
      $purchase_return_description =  PurchaseReturnDescription::join('units','purchase_return_descriptions.unit','=','units.id')
                                    ->where('purchase_return_id',$id)
                                    ->select(['purchase_return_descriptions.*','units.s_name'])
                                    ->get();
      $purchase_return_sundry =  PurchaseReturnSundry::join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','purchase_return_sundries.*'])
                                             ->where('purchase_return_id',$id)
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
      $merchant_gst = "";
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'series' => $purchase_return->series_no])->first();
         if($GstSettings){
            $merchant_gst = $GstSettings->gst_no;
         }else{            
            $branch = GstBranch::select('gst_number as gst_no')
                              ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'branch_series'=>$purchase_return->series_no])
                              ->first();
            if($branch){
               $merchant_gst = $branch->gst_no;
            }
         }         
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'series' => $purchase_return->series_no])->first();
         if($GstSettings){
            $merchant_gst = $GstSettings->gst_no;
         }else{            
            $branch = GstBranch::select('gst_number as gst_no')
                              ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'branch_series'=>$purchase_return->series_no])
                              ->first();
            if($branch){
               $merchant_gst = $branch->gst_no;
            }
         }         
      }
      if(!isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','purchase-return');
      }    
      $financial_year = Session::get('default_fy');
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
      $without_gst = PurchaseReturnEntry::where('purchase_return_id',$id)->get(); 
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
      $manageitems = DB::table('manage_items')
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '0')
                        ->where('manage_items.status', '1')
                        ->where('manage_items.u_name', '!=', '')
                        ->select('units.s_name as unit', 'manage_items.*')
                        ->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->get();
      return view('editPurchaseReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('purchase_return', $purchase_return)->with('purchase_return_description', $purchase_return_description)->with('purchase_return_sundry', $purchase_return_sundry)->with('without_gst', $without_gst)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list)->with('merchant_gst',$merchant_gst)->with('manageitems',$manageitems);
   }
   public function update(Request $request){
      Gate::authorize('action-module',47); 
      $validated = $request->validate([         
         'date' => 'required',
         'party_id' => 'required', 
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      // echo "<pre>";
      // print_r($request->all());die;
      //Check Item Empty or not
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
            return $this->failedMessage('Plases Select Item','sale-return/create');
         }
      }
      $account = Accounts::where('id',$request->input('party_id'))->first();
      $financial_year = Session::get('default_fy');      
      $purchase = PurchaseReturn::find($request->input('purchase_return_edit_id'));
      $last_date = $purchase->date;
      $purchase->date = $request->input('date');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $purchase->taxable_amt = $request->input('taxable_amt');
         $purchase->total = $request->input('total');
          $purchase->remark = $request->input('narration_withgst');         
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $purchase->taxable_amt = $request->input('net_amount');
         $purchase->total = $request->input('total_amount');
         $purchase->remark = $request->input('remark');
      }
      //$purchase->series_no = $request->input('series_no');
      //$purchase->material_center = $request->input('material_center');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->voucher_type = $request->input('voucher_type');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->station = $request->input('station');
      //$purchase->purchase_return_no = $request->input('purchase_return_no');      
      $purchase->tax_cgst = $request->input('cgst');
      $purchase->tax_sgst = $request->input('sgst');
      $purchase->tax_igst = $request->input('igst');
      $purchase->billing_gst = $account->gstin;
      $purchase->billing_state = $account->state;
      $purchase->financial_year = $financial_year;
      $purchase->other_invoice_no = $request->input('other_invoice_no');
      $purchase->other_invoice_date = $request->input('other_invoice_date');
      $purchase->other_invoice_against = $request->input('other_invoice_against');
      $purchase->purchase_bill_id = $request->input('purchase_bill_id');
      $purchase->save();
      if($purchase->id){
         $desc_item_arr = PurchaseReturnDescription::where('purchase_return_id',$purchase->id)
                                                   ->pluck('goods_discription')
                                                   ->toArray();
         PurchaseReturnDescription::where('purchase_return_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',5)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',4)->delete();
         PurchaseReturnSundry::where('purchase_return_id',$purchase->id)->delete();
         PurchaseReturnEntry::where('purchase_return_id',$purchase->id)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',12)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',13)->delete();
         ItemAverageDetail::where('purchase_return_id',$purchase->id)
                           ->where('type','PURCHASE RETURN')
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
               $desc = new PurchaseReturnDescription;
               $desc->purchase_return_id = $purchase->id;
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
                     $item_ledger->out_weight = $qtys[$key];
                     $item_ledger->series_no = $request->input('series_no');
                     $item_ledger->txn_date = $request->input('date');
                     $item_ledger->price = $prices[$key];
                     $item_ledger->total_price = $amounts[$key];
                     $item_ledger->company_id = Session::get('user_company_id');
                     $item_ledger->source = 5;
                     $item_ledger->source_id = $purchase->id;
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
               $sundry = new PurchaseReturnSundry;
               $sundry->purchase_return_id = $purchase->id;
               $sundry->bill_sundry = $bill;
               $sundry->rate = $tax_rate[$key];
               $sundry->amount = $bill_sundry_amounts[$key];
               $sundry->status = '1';
               $sundry->save();
               //ADD DATA IN CGST ACCOUNT
               $billsundry = BillSundrys::where('id', $bill)->first();
               if($billsundry->adjust_sale_amt=='No'){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $billsundry->purchase_amt_account;
                  if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                     $ledger->debit = $bill_sundry_amounts[$key];
                  }else{
                     $ledger->credit = $bill_sundry_amounts[$key];
                  }
                  $ledger->txn_date = $request->input('date');
                  $ledger->series_no = $request->input('series_no');
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 4;
                  $ledger->entry_type_id = $purchase->id;
                  $ledger->map_account_id = $request->input('party_id');
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //$roundoff = $roundoff - $bill_sundry_amounts[$key];
               }
            }
            //Avearge Calculation
            if($request->type=="WITH ITEM" || $request->type=="RATE DIFFERENCE"){
               //Average Calculation
               $goods_discriptions = $request->input('goods_discription');
               $qtys = $request->input('qty');
               $purchase_return_item_array = [];
               $amounts = $request->input('amount');
               $item_average_arr = [];$item_average_total = 0;
               foreach ($goods_discriptions as $key => $good) {
                  if($good=="" || $qtys[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
                     continue;
                  }
                  array_push($item_average_arr,array("item"=>$good,"quantity"=>$qtys[$key],"amount"=>$amounts[$key]));
                  array_push($update_item_arr,$good);
                  $item_average_total = $item_average_total + $amounts[$key];
               }
               //Sundry
               $additive_sundry_amount_first = 0;$subtractive_sundry_amount_first = 0;
               $bill_sundrys = $request->input('bill_sundry');
               $bill_sundry_amounts = $request->input('bill_sundry_amount');
               foreach($bill_sundrys as $key => $bill){
                  if($bill_sundry_amounts[$key]=="" || $bill==""){
                     continue;
                  }
                  $billsundry = BillSundrys::where('id', $bill)->first();  
                  if($billsundry->nature_of_sundry=="OTHER"){
                     if($billsundry->bill_sundry_type=="additive"){
                        $additive_sundry_amount_first = $additive_sundry_amount_first + $bill_sundry_amounts[$key];
                     }else if($billsundry->bill_sundry_type=="subtractive"){
                        $subtractive_sundry_amount_first = $subtractive_sundry_amount_first + $bill_sundry_amounts[$key];
                     }
                  }
               }
               foreach ($item_average_arr as $key => $value) {
                  $subtractive_sundry_amount = 0;$additive_sundry_amount = 0;
                  if($additive_sundry_amount_first>0){
                     $additive_sundry_amount = ($value['amount']/$item_average_total)*$additive_sundry_amount_first;
                  }
                  if($subtractive_sundry_amount_first>0){
                     $subtractive_sundry_amount = ($value['amount']/$item_average_total)*$subtractive_sundry_amount_first;
                  }
                  $additive_sundry_amount = round($additive_sundry_amount,2);
                  $subtractive_sundry_amount = round($subtractive_sundry_amount,2);
                  $average_amount = $value['amount'] + $additive_sundry_amount - $subtractive_sundry_amount;
                  $average_amount =  round($average_amount,2);
                  $average_price = $average_amount/$value['quantity'];
                  $average_price =  round($average_price,6);
                  //Add Data In Average Details table
                  $average_detail = new ItemAverageDetail;
                  $average_detail->entry_date = $request->date;
                  $average_detail->series_no = $request->input('series_no');
                  $average_detail->item_id = $value['item'];
                  $average_detail->type = 'PURCHASE RETURN';
                  $average_detail->purchase_return_id = $purchase->id;
                  if($request->type=="WITH ITEM"){
                     $average_detail->purchase_return_weight = $value['quantity'];
                  }                  
                  $average_detail->purchase_return_amount = $value['amount'];
                  $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
                  $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
                  $average_detail->purchase_total_amount = $average_amount;
                  $average_detail->company_id = Session::get('user_company_id');
                  $average_detail->created_at = Carbon::now();
                  $average_detail->save();
                  CommonHelper::RewriteItemAverageByItem($request->date,$value['item'],$request->input('series_no'));                  
               }
            }            
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->debit = $request->input('total');
            $ledger->series_no = $request->input('series_no');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 4;
            $ledger->entry_type_id = $purchase->id;
            $ledger->map_account_id = 36;//Purchase
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            //ADD DATA IN Sale ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = 36;//Purchase
            $ledger->credit = $request->input('taxable_amt');
            $ledger->series_no = $request->input('series_no');
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 4;
            $ledger->entry_type_id = $purchase->id;
            $ledger->map_account_id = $request->input('party_id');
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($desc_item_arr as $key => $value) {
               if(!in_array($value, $update_item_arr)){
                  CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
               }
            }
            return redirect('purchase-return-invoice/'.$purchase->id)->withSuccess('Purchase return added successfully!');
         }else if($request->input('nature')=="WITH GST" && $request->input('type')=="WITHOUT ITEM"){
            //Ledger Entry
            $account_info = Accounts::select('under_group')->where('id',$request->input('party_id'))->first();
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->debit = $request->input('total_amount'); 
            $ledger->series_no = $request->input('series_no');                      
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 13;
            $ledger->map_account_id = $request->input('item')[0];
            $ledger->entry_type_id = $purchase->id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            foreach ($request->input('item') as $key => $item){
               $percentage = $request->input('percentage')[$key];
               $amount = $request->input('without_item_amount')[$key];
               $hsn = $request->input('hsn')[$key];
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->type = "Credit";
               $purchase_return_without->account_name = $item;
               $purchase_return_without->debit = $amount;
               $purchase_return_without->percentage = $percentage;  
               $purchase_return_without->hsn_code = $hsn;  
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $item;
               $ledger->credit = $amount;                       
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $request->input('party_id');
               $ledger->entry_type_id = $purchase->id;
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
               $ledger->credit = $request->input('igst');  
               $ledger->series_no = $request->input('series_no');                     
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $account_name;
               $ledger->entry_type_id = $purchase->id;
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
               $ledger->credit = $request->input('cgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $cgst_account_name;
               $ledger->entry_type_id = $purchase->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               
               //Ledger Entry
               $ledger = new AccountLedger();
               $ledger->account_id = $sgst_account_name;
               $ledger->credit = $request->input('sgst');                       
               $ledger->txn_date = $request->input('date');
               $ledger->series_no = $request->input('series_no');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 13;
               $ledger->map_account_id = $sgst_account_name;
               $ledger->entry_type_id = $purchase->id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
            foreach ($desc_item_arr as $key => $value) {
               if(!in_array($value, $update_item_arr)){
                  CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
               }
            }
            return redirect('purchase-return-without-item-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }else if($request->input('nature')=="WITHOUT GST"){
            $account_names = $request->input('account_name');
            $debits = $request->input('debit');        
        //    $narrations = $request->input('narration');
             $narration_without_gst = $request->input('long_narration');
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->account_name = $account;
               $purchase_return_without->credit = isset($debits[$key]) ? $debits[$key] : '0';
              // $purchase_return_without->narration = $narrations[$key];
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party_id');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
               $ledger->series_no = $request->input('series_no');
               $ledger->credit = $debits[$key];                          
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 12;
               $ledger->entry_type_id = $purchase->id;
               $ledger->map_account_id = $map_account_id;
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $debit_total = $debit_total + $debits[$key];
               $i++;
            }
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
            $ledger->debit = $debit_total;                          
            $ledger->txn_date = $request->input('date');
            $ledger->series_no = $request->input('series_no');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 12;
            $ledger->entry_type_id = $purchase->id;
            $ledger->map_account_id =  $account_names[0];
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
           PurchaseReturn::where('id',$purchase->id)->update(['total'=>$debit_total,'taxable_amt'=>$debit_total,'remark'=>$narration_without_gst]);
            foreach ($desc_item_arr as $key => $value) {
               if(!in_array($value, $update_item_arr)){
                  CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
               }
            }
            return redirect('purchase-return-without-gst-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }

      }else{
         $this->failedMessage('Something went wrong','purchase-return/create');
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
   public function generateEinvoice(Request $request){      
      $einvoice_username = ""; $einvoice_password = "";
      $einvoice_gst = ""; $einvoice_company = "";
      ini_set('serialize_precision','-1');
      $validated = $request->validate([
        'id' => 'required',
      ]);
      $sale = PurchaseReturn::join('accounts','purchase_returns.party','=','accounts.id')
                    ->join('companies','purchase_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('purchase_returns.id',$request->id)
                    ->first(['purchase_returns.*','accounts.print_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);
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
      if($sale->purchase_bill_id && !empty($sale->purchase_bill_id)){
         $sale_info = Sales::find($sale->purchase_bill_id);
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
      $sundry = PurchaseReturnSundry::join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
                  ->select(['purchase_return_sundries.rate','purchase_return_sundries.amount','bill_sundry_type','adjust_sale_amt','nature_of_sundry','effect_gst_calculation'])
                  ->where('purchase_return_id',$request->id)
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
      $total_item_price = PurchaseReturnDescription::where('purchase_return_id',$request->id)->sum('amount');
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
      $item_data = PurchaseReturnDescription::join('manage_items','purchase_return_descriptions.goods_discription','=','manage_items.id')
      ->join('units','manage_items.u_name','=','units.id')
                              ->where('purchase_return_id',$request->id)
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
            $invoice_update = PurchaseReturn::find($request->id);
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
      $sale = PurchaseReturn::join('accounts','purchase_returns.party','=','accounts.id')
                    ->join('companies','purchase_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('purchase_returns.id',$request->id)
                    ->first(['purchase_returns.*','accounts.print_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);
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
      if($sale->purchase_bill_id && !empty($sale->purchase_bill_id)){
         $sale_info = Sales::find($sale->purchase_bill_id);
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
      
      $total_item_price = PurchaseReturnEntry::where('purchase_return_id',$request->id)->sum('debit');
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
      $item_data = PurchaseReturnEntry::join('accounts','purchase_return_entries.account_name','=','accounts.id')
                              ->where('purchase_return_id',$request->id)
                              ->groupBy('hsn_code')
                              ->get( array(
                                DB::raw('SUM(debit) as tprice'),
                                DB::raw('purchase_return_entries.hsn_code'),
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
            $invoice_update = PurchaseReturn::find($request->id);
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
   public function generateEwaybillPurchaseReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = PurchaseReturn::join('accounts','purchase_returns.party','=','accounts.id')
                    ->join('companies','purchase_returns.company_id','=','companies.id')
                    ->join('states','accounts.state','=','states.id')
                    ->where('purchase_returns.id',$request->id)
                    ->first(['purchase_returns.*','accounts.account_name','accounts.gstin','accounts.address','accounts.state','states.name','accounts.pin_code','companies.company_name','companies.gst_config_type']);          
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
            $invoice_update = PurchaseReturn::find($request->id);
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
   public function cancelEwaybillPurchaseReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = PurchaseReturn::join('companies','purchase_returns.company_id','=','companies.id')
                    ->where('purchase_returns.id',$request->id)
                    ->first(['purchase_returns.*','companies.gst_config_type']);          
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
            PurchaseReturn::where('id',$request->id)->update(['e_waybill_status'=>0,'eway_bill_response'=>'']);
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
   public function cancelEinvoicePurchaseReturn(Request $request){
      $einvoice_username = "";
      $einvoice_password = "";
      $einvoice_gst = ""; 
      $einvoice_company = "";    
      $sale = PurchaseReturn::join('companies','purchase_returns.company_id','=','companies.id')
                    ->where('purchase_returns.id',$request->id)
                    ->first(['purchase_returns.*','companies.gst_config_type']);          
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
            PurchaseReturn::where('id',$request->id)->update(['e_invoice_status'=>0,'status'=>'2','einvoice_response'=>'','total'=>'0']);
            if($sale->sr_nature=="WITH GST" && ($sale->sr_type=="WITH ITEM" || $sale->sr_type=="Rate Difference")){
               PurchaseReturnDescription::where('purchase_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);            
               PurchaseReturnSundry::where('purchase_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
               AccountLedger::where('entry_type',4)
                        ->where('entry_type_id',$request->id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
               if($sale->sr_type=="WITH ITEM"){
                  ItemLedger::where('source',5)
                     ->where('source_id',$request->id)
                     ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                  ItemAverageDetail::where('purchase_return_id',$request->id)
                     ->where('type','PURCHASE RETURN')
                     ->delete();         
                  $desc = PurchaseReturnDescription::where('purchase_return_id',$request->id)
                                 ->get();
                  foreach ($desc as $key => $value) {
                     CommonHelper::RewriteItemAverageByItem($sale->date,$value->goods_discription,$sale->series_no);
                  }
               }               
            }else if($sale->sr_nature=="WITH GST" && $sale->sr_type=="WITHOUT ITEM"){
               PurchaseReturnEntry::where('purchase_return_id',$request->id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);  
               AccountLedger::where('entry_type',13)
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
