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
use App\Models\AccountGroups;
use App\Models\PurchaseReturnEntry;
use Carbon\Carbon;
use DB;
use Session;
use DateTime;

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
         $from_date = session('purchaseReturn_from_date', "01-" . date('m-Y'));
         $to_date = session('purchaseReturn_to_date', date('d-m-Y'));

         // Check if user has selected a date range
         if (!empty($input['from_date']) && !empty($input['to_date'])) {
            $from_date = date('d-m-Y', strtotime($input['from_date']));
            $to_date = date('d-m-Y', strtotime($input['to_date']));
            
            // Store in session so it persists after refresh
            session(['purchaseReturn_from_date' => $from_date, 'purchaseReturn_to_date' => $to_date]);
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
      $purchase = DB::table('purchase_returns')
            ->select('purchase_returns.id as purchases_id', 'purchase_returns.date', 'purchase_returns.invoice_no', 'purchase_returns.total','purchase_return_no','purchase_returns.series_no','purchase_returns.financial_year','sr_nature','sr_type', DB::raw('(select account_name from accounts where accounts.id=purchase_returns.party limit 1) as account_name'))
            ->whereRaw("STR_TO_DATE(purchase_returns.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(purchase_returns.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->where('company_id',Session::get('user_company_id'))
            ->where('delete','0')
            ->orderBy(\DB::raw("cast(purchase_return_no as SIGNED)"), 'ASC')
            ->orderBy('purchase_returns.created_at', 'ASC')
            ->get();
      return view('purchaseReturn')->with('purchase', $purchase)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
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
      $purchase = new PurchaseReturn;
      $purchase->date = $request->input('date');
      $purchase->company_id = Session::get('user_company_id');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $purchase->taxable_amt = $request->input('taxable_amt');
         $purchase->total = $request->input('total');         
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $purchase->taxable_amt = $request->input('net_amount');
         $purchase->total = $request->input('total_amount');
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
      $purchase->tax_cgst = $request->input('cgst');
      $purchase->tax_sgst = $request->input('sgst');
      $purchase->tax_igst = $request->input('igst');
      $purchase->remark = $request->input('remark');
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
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
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
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->account_name = $account;
               $purchase_return_without->credit = isset($debits[$key]) ? $debits[$key] : '0';
               $purchase_return_without->narration = $narrations[$key];
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party_id');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
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
            $ledger->credit = $debit_total;                          
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 12;
            $ledger->entry_type_id = $purchase->id;
            //$ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            PurchaseReturn::where('id',$purchase->id)->update(['total'=>$debit_total]);
            return redirect('purchase-return-without-gst-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }                  
         return redirect('purchase-return')->withSuccess('Purchase return added successfully!');
      }else{
         $this->failedMessage('Something went wrong','purchase-return/create');
      }
   }
   public function delete(Request $request){
      $purchase_return =  PurchaseReturn::find($request->purchase_return_id);
      $purchase_return->delete = '1';
      $purchase_return->deleted_at = Carbon::now();
      $purchase_return->deleted_by = Session::get('user_id');
      $purchase_return->update();
      if($purchase_return) {
         if($purchase_return->sr_nature=="WITH GST" && ($purchase_return->sr_type=="WITH ITEM" || $purchase_return->sr_type=="RATE DIFFERENCE")){
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
      $purchase_return = PurchaseReturn::join('purchases','purchase_returns.purchase_bill_id','=','purchases.id')
                                 ->leftjoin('states','purchases.billing_state','=','states.id')
                                 ->where('purchase_returns.id',$id)
                                 ->select(['purchase_returns.date','purchase_returns.invoice_no','purchase_returns.total','purchases.billing_name','purchases.billing_address','purchases.billing_pincode','purchases.billing_gst','states.name as sname','purchase_return_no','purchase_returns.vehicle_no','purchase_returns.gr_pr_no','purchase_returns.transport_name','purchase_returns.station','purchases.voucher_no','purchases.date as purchase_date','purchases.series_no','purchases.financial_year','purchase_returns.series_no as dr_series_no','purchase_returns.financial_year as dr_financial_year'])
                                 ->first();      
      $items_detail = DB::table('purchase_return_descriptions')->where('purchase_return_id', $id)
            ->select('units.s_name as unit', 'units.id as unit_id', 'purchase_return_descriptions.qty', 'purchase_return_descriptions.price', 'purchase_return_descriptions.amount', 'manage_items.name as items_name', 'manage_items.id as item_id','manage_items.hsn_code','manage_items.gst_rate')
            ->join('units', 'purchase_return_descriptions.unit', '=', 'units.id')
            ->join('manage_items', 'purchase_return_descriptions.goods_discription', '=', 'manage_items.id')
            ->get();      
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
      return view('purchaseReturnInvoice')->with(['items_detail' => $items_detail, 'purchase_sundry' => $purchase_sundry,'company_data' => $company_data,'gst_detail'=>$gst_detail,'purchase_return'=>$purchase_return]);
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
      return view('purchase_return_without_item_invoice')->with(['company_data' => $company_data,'purchase_return'=>$purchase_return,'items'=>$items]);
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
      return view('purchase_return_without_gst_invoice')->with(['items' => $items,'company_data' => $company_data,'sale_return'=>$sale_return]);
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function edit($id){
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
      return view('editPurchaseReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('purchase_return', $purchase_return)->with('purchase_return_description', $purchase_return_description)->with('purchase_return_sundry', $purchase_return_sundry)->with('without_gst', $without_gst)->with('vendors', $vendors)->with('items', $items)->with('all_account_list', $all_account_list)->with('merchant_gst',$merchant_gst);
   }
   public function update(Request $request){
      
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
      $financial_year = Session::get('default_fy');      
      $purchase = PurchaseReturn::find($request->input('purchase_return_edit_id'));
      $purchase->date = $request->input('date');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->party = $request->input('party_id');
      if($request->input('nature')=="WITH GST" && ($request->input('type')=="WITH ITEM" || $request->input('type')=="RATE DIFFERENCE")){
         $purchase->taxable_amt = $request->input('taxable_amt');
         $purchase->total = $request->input('total');         
      }else if($request->nature=="WITH GST" && $request->type=="WITHOUT ITEM"){
         $purchase->taxable_amt = $request->input('net_amount');
         $purchase->total = $request->input('total_amount');
      }
      //$purchase->series_no = $request->input('series_no');
      //$purchase->material_center = $request->input('material_center');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->station = $request->input('station');
      //$purchase->purchase_return_no = $request->input('purchase_return_no');      
      $purchase->tax_cgst = $request->input('cgst');
      $purchase->tax_sgst = $request->input('sgst');
      $purchase->tax_igst = $request->input('igst');
      $purchase->financial_year = $financial_year;
      $purchase->purchase_bill_id = $request->input('purchase_bill_id');
      $purchase->save();
      if($purchase->id){
         PurchaseReturnDescription::where('purchase_return_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',5)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',4)->delete();
         PurchaseReturnSundry::where('purchase_return_id',$purchase->id)->delete();
         PurchaseReturnEntry::where('purchase_return_id',$purchase->id)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',12)->delete();
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',13)->delete();
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
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $request->input('party_id');
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
            $i = 0; $debit_total = 0;       
            foreach ($account_names as $key => $account){
               $purchase_return_without = new PurchaseReturnEntry;
               $purchase_return_without->purchase_return_id = $purchase->id;
               $purchase_return_without->company_id = Session::get('user_company_id');
               $purchase_return_without->account_name = $account;
               $purchase_return_without->credit = isset($debits[$key]) ? $debits[$key] : '0';
               $purchase_return_without->narration = $narrations[$key];
               $purchase_return_without->status = '1';
               $purchase_return_without->save();
               //ADD DATA IN Customer ACCOUNT
               $map_account_id = $request->input('party_id');               
               $ledger = new AccountLedger();
               $ledger->account_id = $account_names[$key];
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
            $ledger->credit = $debit_total;                          
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = 12;
            $ledger->entry_type_id = $purchase->id;
            //$ledger->map_account_id = $map_account_id;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            PurchaseReturn::where('id',$purchase->id)->update(['total'=>$debit_total]);
            return redirect('purchase-return-without-gst-invoice/'.$purchase->id)->withSuccess('Sale return added successfully!');
         }
      }else{
         $this->failedMessage('Something went wrong','purchase-return/create');
      }
   }
}
