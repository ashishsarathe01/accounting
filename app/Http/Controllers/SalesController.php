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
use App\Helpers\CommonHelper;
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
   public function index(Request $request){
      Gate::authorize('action-module',10);
      $input = $request->all();
      // Initialize dates
      $from_date = null;
      $to_date = null;
      // If user submitted new dates, update session
      if (!empty($input['from_date']) && !empty($input['to_date'])) {
         $from_date = date('d-m-Y', strtotime($input['from_date']));
         $to_date = date('d-m-Y', strtotime($input['to_date']));
         session(['sales_from_date' => $from_date, 'sales_to_date' => $to_date]);
      }elseif (session()->has('sales_from_date') && session()->has('sales_to_date')) {
         // Use previously stored session dates
         $from_date = session('sales_from_date');
         $to_date = session('sales_to_date');
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
                     DB::raw('(select account_name from accounts where accounts.id = sales.party limit 1) as account_name'),
                     DB::raw('(select manual_numbering from voucher_series_configurations where voucher_series_configurations.company_id = '.Session::get('user_company_id').' and configuration_for="SALE" and voucher_series_configurations.status=1 and voucher_series_configurations.series = sales.series_no limit 1) as manual_numbering_status'),
                     DB::raw('(select max(voucher_no) from sales as s where s.company_id = '.Session::get('user_company_id').' and s.delete="0" and s.series_no = sales.series_no and entry_source=1) as max_voucher_no')
                  )
                  ->where('sales.company_id', Session::get('user_company_id'))
                  ->where('sales.delete', '0');   
      // Filter if dates selected
      if($from_date && $to_date) {
         $query->whereRaw("
            STR_TO_DATE(sales.date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "','%Y-%m-%d')
            AND STR_TO_DATE(sales.date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "','%Y-%m-%d')
         ");
         $query->orderBy('sales.date','asc');
         $query->orderBy(DB::raw("cast(voucher_no as SIGNED)"), 'ASC');
      }else{
         // No date filter: show last 10 transactions
         $query->orderBy('financial_year','desc')->orderBy(DB::raw("cast(date as SIGNED)"), 'desc')->limit(10);
      }
      $sale = $query->get()->reverse()->values();

      

      return view('sale')
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

   public function create(){
      Gate::authorize('action-module',85);
      $financial_year = Session::get('default_fy');    
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      //Ashish Code Start Here
      // echo "<pre>";
      //invoice_prefix
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
                              $GstSettings[$key]->invoice_start_from =  sprintf("%'03d",$series_configuration->invoice_start);
                           }else{
                              $GstSettings[$key]->invoice_start_from =  "001";
                           }            
                        }else{
                           $invc = $voucher_no + 1;
                           $invc = sprintf("%'03d", $invc);
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
      $groups = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
      $groups->push(3);
      $groups->push(11);
      $party_list = Accounts::with('otherAddress')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get(); 
                                 
      //Item List
      $item = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.id','manage_items.u_name','manage_items.gst_rate','manage_items.name','parameterized_stock_status','config_status','item_groups.id as group_id'])
            ->get(); 
      foreach($item as $key=>$row){
         $item_in_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('in_weight');

         $item_out_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('out_weight');

         $available_item = $item_in_weight-$item_out_weight;
         $item[$key]->available_item = $available_item;
      }
      
      return view('addSale')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('bill_date', $bill_date)->with('GstSettings', $GstSettings)->with('item', $item);
   }   
   public function store(Request $request){
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

      //echo "<pre>";
      //print_r($request->all());
      
      //die;
      //Check Item Empty or not
      if($request->input('goods_discription')[0]=="" || $request->input('qty')[0]=="" || $request->input('price')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','sale/create');
      }
      $financial_year = Session::get('default_fy');
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
         if(!$voucher_no){
            $series_configuration = VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
               ->where('series',$request->input('series_no'))
               ->where('configuration_for','SALE')
               ->where('status','1')
               ->first();
               if($series_configuration && $series_configuration->manual_numbering=="NO" && $series_configuration->invoice_start!=""){
                  $voucher_no =  sprintf("%'03d",$series_configuration->invoice_start);
               }else{
                  $voucher_no = "001";
               }
         }else{
            $voucher_no++;
            $voucher_no = sprintf("%'03d", $voucher_no);
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
      $roundoff = $request->input('total')-$request->input('taxable_amt');
      $sale->save();
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
            $desc = new SaleDescription;
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $good;
            $desc->qty = $qtys[$key];
            $desc->unit = $units[$key];
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->status = '1';
            $desc->save();
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
            //Parameter Info
            if($item_parameters[$key]!=""){
               $parameter = json_decode($item_parameters[$key],true);
               if(count($parameter)>0){                  
                  ItemParameterStock::whereIn('id',$parameter)->update(['status'=>0,'stock_out_id'=>$sale->id]);
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
               $ledger->financial_year = Session::get('default_fy');
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
            // $stock_average = ItemAverage::where('item_id',$key)
            //                ->orderBy('stock_date','desc')
            //                ->orderBy('id','desc')
            //                ->first();
            // if($stock_average){
            //    if(strtotime($stock_average->stock_date)==strtotime($request->date)){
            //       $purchase_weight = $stock_average->average_weight;
            //       $sale_weight = $value;
            //       $price = $stock_average->price;
            //       if(!empty($stock_average->sale_weight)){
            //          $sale_weight = $sale_weight + $stock_average->sale_weight;
            //       }
            //       $average_weight = $purchase_weight - $value;  
                                  
            //       $average = ItemAverage::find($stock_average->id);
            //       $average->sale_weight = $sale_weight;
            //       $average->average_weight = $average_weight;
            //       $average->amount = round($average_weight*$price,2);
            //       $average->updated_at = Carbon::now();
            //       $average->save();
            //    }else if(strtotime($stock_average->stock_date)<strtotime($request->date)){
            //       $stock_average_weight = $stock_average->average_weight - $value;
            //       $stock_average_price = $stock_average->price;
            //       $stock_average_amount = round($stock_average_weight*$stock_average_price,2);

            //       $average = new ItemAverage;
            //       $average->item_id = $key;
            //       $average->sale_weight = $value;
            //       $average->average_weight = $stock_average_weight;
            //       $average->price = $stock_average_price;
            //       $average->amount = $stock_average_amount;
            //       $average->stock_date = $request->date;
            //       $average->company_id = Session::get('user_company_id');
            //       $average->created_at = Carbon::now();
            //       $average->save();               
            //    }else if(strtotime($stock_average->stock_date)>strtotime($request->date)){
            //       CommonHelper::RewriteItemAverageByItem($request->date,$key);
            //    }
            // }else{
            //    $opening = ItemLedger::where('item_id',$key)
            //                            ->where('source','-1')
            //                            ->first();
            //    if($opening){
            //       $stock_average_weight = $opening->in_weight - $value;
            //       $stock_average_price = $opening->total_price/$opening->in_weight;
            //       $stock_average_price = round($stock_average_price,6);
            //       $stock_average_amount = round($stock_average_weight*$stock_average_price,2);
            //       $average = new ItemAverage;
            //       $average->item_id = $key;
            //       $average->sale_weight = $value;
            //       $average->purchase_weight = 0;
            //       $average->average_weight = $stock_average_weight;
            //       $average->price = $stock_average_price;
            //       $average->amount = $stock_average_amount;
            //       $average->stock_date = $request->date;
            //       $average->company_id = Session::get('user_company_id');
            //       $average->created_at = Carbon::now();
            //       $average->save();
            //    }else{
            //       $average = new ItemAverage;
            //       $average->item_id = $key;
            //       $average->sale_weight = $value;
            //       $average->purchase_weight = 0;
            //       $average->average_weight = -$value;
            //       $average->price = 0;
            //       $average->amount = 0;
            //       $average->stock_date = $request->date;
            //       $average->company_id = Session::get('user_company_id');
            //       $average->created_at = Carbon::now();
            //       $average->save();
            //    }
            // }
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party_id');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
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
                  
               }}
               
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
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party_id');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         session(['previous_url' => URL::previous()]);
         return redirect('sale-invoice/'.$sale->id)->withSuccess('Sale voucher added successfully!');
      }else{
         return $this->failedMessage('Something went wrong','sale/create');
         exit();
      }
   }
   public function edit($id){
      Gate::authorize('action-module',61);
      $sale = Sales::find($id);
      $SaleDescription = SaleDescription::join('units','sale_descriptions.unit','=','units.id')
                                          ->where('sale_id', $id)
                                          ->select(['sale_descriptions.*','units.s_name'])
                                          ->get();
      $SaleSundry = SaleSundry::join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','sale_sundries.*'])
                                 ->where('sale_sundries.sale_id', $id)
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
      $party_list = Accounts::with(['otherAddress'])->select('accounts.*','states.state_code')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('accounts.delete', '=', '0')
                              ->where('accounts.status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->orderBy('account_name')
                              ->get();
      $manageitems = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
                        ->select(['units.s_name as unit', 'manage_items.*'])
                        ->where('manage_items.company_id', Session::get('user_company_id'))
                        ->where('manage_items.delete', '=', '0')
                        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                        ->orderBy('manage_items.name')
                        ->get();
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

      return view('editSale')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('sale', $sale)->with('SaleDescription', $SaleDescription)->with('SaleSundry', $SaleSundry);
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
            ->select('units.s_name as unit', 'units.id as unit_id', 'sale_descriptions.qty', 'sale_descriptions.price', 'sale_descriptions.amount', 'manage_items.name as items_name', 'manage_items.id as item_id', 'sales.*', 'accounts.*','manage_items.hsn_code','manage_items.gst_rate')
            ->join('units', 'sale_descriptions.unit', '=', 'units.id')
            ->join('sales', 'sales.id', '=', 'sale_descriptions.sale_id')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('accounts', 'accounts.id', '=', 'sales.party')
            ->get();
      $sale_detail = Sales::leftjoin('states','sales.billing_state','=','states.id')
                           ->leftjoin('accounts','sales.shipping_name','=','accounts.id')
                           ->where('sales.id', $id)
                           ->select(['sales.*','states.name as sname','accounts.print_name as shipp_name'])
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
            if($sale_detail->total<100000){
               $GstSettings->ewaybill = 0;
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
      return view('saleInvoice')->with(['items_detail' => $items_detail, 'sale_sundry' => $sale_sundry, 'party_detail' => $party_detail,'month_arr' => $month_arr, 'company_data' => $company_data, 'sale_detail' => $sale_detail,'bank_detail' => $bank_detail,'gst_detail'=>$gst_detail,'einvoice_status'=>$GstSettings->einvoice,'ewaybill_status'=>$GstSettings->ewaybill,'configuration'=>$configuration,'seller_info'=>$seller_info]);
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
      // echo "<pre>";
      // print_r($request->all());die;
      $account = Accounts::where('id',$request->input('party'))->first();
      $financial_year = Session::get('default_fy');      
      $sale = Sales::find($request->input('sale_edit_id'));
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
      $sale->save();
      if($sale->id){
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         $desc_item_arr = SaleDescription::where('sale_id',$sale->id)->pluck('goods_discription')->toArray();
         SaleDescription::where('sale_id',$request->input('sale_edit_id'))->delete();
         ItemLedger::where('source_id',$request->input('sale_edit_id'))->where('source',1)->delete();
         ItemAverageDetail::where('sale_id',$sale->id)
                        ->where('type','SALE')
                        ->delete(); 
         foreach($goods_discriptions as $key => $good){
            if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            $desc = new SaleDescription;
            $desc->sale_id = $sale->id;
            $desc->goods_discription = $good;
            $desc->qty = $qtys[$key];
            $desc->unit = $units[$key];
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->status = '1';
            $desc->save();
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
               $ledger->financial_year = Session::get('default_fy');
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
         $ledger->credit = $request->input('taxable_amt');
         $ledger->txn_date = $request->input('date');
         $ledger->series_no = $request->input('series_no');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();     
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            session(['previous_url_saleEdit' => URL::previous()]);
            return redirect('sale-invoice/'.$sale->id)->withSuccess('Sale voucher updated successfully!');
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
      ini_set('max_execution_time', 600);      
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
            foreach ($data_arr as $key => $value) {               
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }            
               
               $series_no = $value['series_no'];
               $date = date('Y-m-d',strtotime($value['date']));
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
               
                  $check_invoices = Sales::select('id')
                              ->where('company_id',Session::get('user_company_id'))
                              ->where('voucher_no',$voucher_no)
                              ->where('series_no',$series_no)
                              ->where('financial_year','=',$financial_year)
                              ->where('delete','0')
                              ->first();
               
               if($check_invoices){
                  if($duplicate_voucher_status==2){
                     $updated_sale = Sales::find($check_invoices->id);
                     $updated_sale->delete = '1';
                     $updated_sale->deleted_at = Carbon::now();
                     $updated_sale->deleted_by = Session::get('user_id');
                     $updated_sale->update();
                     if($updated_sale){
                        SaleDescription::where('sale_id',$check_invoices->id)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',1)
                                       ->where('entry_type_id',$check_invoices->id)
                                       ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        SaleSundry::where('sale_id',$check_invoices->id)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        ItemLedger::where('source',1)
                                    ->where('source_id',$check_invoices->id)
                                    ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        ItemAverageDetail::where('sale_id',$check_invoices->id)
                                          ->delete();
                        $itemKiId =  SaleDescription::where('sale_id',$check_invoices->id)
                                    ->select('sale_descriptions.goods_description as item_id');
                                    foreach( $itemKiId as $k){
                        CommonHelper::RewriteItemAverageByItem($check_invoices->date,$k->item_id,$series_no);       
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
                              }else if($adjust_sale_amt=='Yes'){                                
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
                        $item_taxable_amount = $item_taxable_amount + str_replace(",","",$v1['amount']);
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
               CommonHelper::RewriteItemAverageByItem($sale->date,$item->id,$series_no);               
            
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
         }
      }
      $res = array("total_count"=>$total_invoice_count,"success_count"=>$success_invoice_count,"failed_count"=>$failed_invoice_count,"error_message"=>$all_error_arr);
      $res = array(
         'status' => true,
         'data' => $res,
         "message"=>"Uploaded Successfully."
      );
      return json_encode($res);
      return view('sale_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$all_error_arr);
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
      //echo "<pre>";
      // $res = array(
      //    'status' => true,
      //    'data' => $einvoice_requset
      // );
      // echo json_encode($einvoice_requset);
      // die;
      
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
      die;
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
                                DB::raw('name'),
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
               "productName"=>$value->name,
               "productDesc"=>$value->name,
               "hsnCode"=>(int)$value->hsn_code,
               "quantity"=>(float)$value->tweight,
               "qtyUnit"=>$qtyUnit->s_name,
               "taxableAmount"=>(float)$item_total,
               "sgstRate"=>$item_sgst,
               "cgstRate"=>$item_igst,
               "igstRate"=>$item_cgst,
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
            "transDistance"=>$request->distance,
            "transporterName"=>"",         
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
            "transDistance"=>$request->distance,
            "transporterName"=>"",         
            "transDocNo"=>"",
            "transDocDate"=>"",
            "vehicleNo"=>$request->vehicle_number,
            "vehicleType"=>"R",
            "itemList"=>$ItemList
         );
      }
         echo "<pre>";
         print_r(json_encode($eway_bill_request));
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
      print_r(json_encode($eway_bill_request));die;
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
      $bank = Bank::where('company_id',Session::get('user_company_id'))->where('status','1')->get();
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
         if(!empty($request->signature)){
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
                     ItemAverageDetail::where('sale_id',$request->sale_id)
                     ->where('type','SALE')
                     ->delete();         
            $desc = SaleDescription::where('sale_id',$request->id)
                                 ->get();
            foreach ($desc as $key => $value) {
               CommonHelper::RewriteItemAverageByItem($sale->date,$value->goods_discription,$sale->series_no);
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
