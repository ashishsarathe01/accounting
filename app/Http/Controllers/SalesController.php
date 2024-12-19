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
use DB;
use Session;
use DateTime;

class SalesController extends Controller
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
      $sale = DB::table('sales')
            ->select('sales.id as sales_id', 'sales.date', 'sales.voucher_no', 'sales.total','financial_year','series_no', DB::raw('(select account_name from accounts where accounts.id=sales.party limit 1) as account_name'))
            ->where('company_id',Session::get('user_company_id'))
            ->whereRaw("STR_TO_DATE(sales.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(sales.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
            ->where('delete','0')            
            ->orderBy(\DB::raw("cast(voucher_no as SIGNED)"), 'ASC')
            ->orderBy('sales.date', 'asc')
            ->get();
      return view('sale')->with('sale', $sale)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */

   public function create(){
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', [3,11])
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }

      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }
      if(!empty($GstSettings->invoice_start_from)) {
         $GstSettings->invoice_start_from = $GstSettings->invoice_start_from;
      }else{
         $GstSettings->invoice_start_from = 1;
      }
      $financial_year = Session::get('default_fy');
      $voucher_no = Sales::select('voucher_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
      if(!$voucher_no){
         $GstSettings->invoice_start_from =  1;
      }else{
         $GstSettings->invoice_start_from =  $voucher_no + 1;
      }
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->mat_center)) {
         $mat_center[] = array("branch_matcenter" => $GstSettings->mat_center);
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)) {
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->where('company_id',Session::get('user_company_id'))
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
      return view('addSale')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('bill_date', $bill_date);
   }

   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function store(Request $request){
      $validated = $request->validate([
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party_id' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ]); 

      // echo "<pre>";
      // print_r($request->all());die;
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
      
      $voucher_no = Sales::select('voucher_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
      if(!$voucher_no){
         $voucher_no = 1;
      }else{
         $voucher_no++;
      }
      $sale = new Sales;
      $sale->series_no = $request->input('series_no');
      $sale->company_id = Session::get('user_company_id');
      $sale->date = $request->input('date');
      $sale->voucher_no = $voucher_no;
      $sale->party = $request->input('party_id');
      $sale->material_center = $request->input('material_center');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->self_vehicle = $request->input('self_vehicle');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->reverse_charge = $request->input('reverse_charge');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->station = $request->input('station');
      $sale->ewaybill_no = $request->input('ewaybill_no');
      $sale->billing_name = $account->account_name;
      $sale->billing_address = $account->address;
      $sale->billing_pincode = $account->pin_code;
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
               if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                  $ledger->debit = $bill_sundry_amounts[$key];
               }else{
                  $ledger->credit = $bill_sundry_amounts[$key];
               }               
               $ledger->txn_date = $request->input('date');
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
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party_id');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
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
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 1;
         $ledger->entry_type_id = $sale->id;
         $ledger->map_account_id = $request->input('party_id');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();     
         return redirect('sale-invoice/'.$sale->id)->withSuccess('Sale voucher added successfully!');
      }else{
         return $this->failedMessage('Something went wrong','sale/create');
         exit();
      }
   }
   public function edit($id){
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
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }

      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }      
      $financial_year = Session::get('default_fy');      
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->mat_center)) {
         $mat_center[] = array("branch_matcenter" => $GstSettings->mat_center);
      }
      $mat_series = array();
      $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->series)){
         $mat_series[] = array("branch_series" => $GstSettings->series);
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->where('company_id',Session::get('user_company_id'))
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
                           ->select(['sales.*','states.name as sname','accounts.account_name as shipp_name'])
                           ->first();
      $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
                     ->where('accounts.id', $sale_detail->party)
                     ->select(['accounts.*','states.name as sname'])            
                     ->first();
      $sale_sundry = DB::table('sale_sundries')
                        ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('sale_id', $id)
                        ->select('sale_sundries.bill_sundry','sale_sundries.rate','sale_sundries.amount','bill_sundrys.name')
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
            if(substr($company_data->gst,0,2)==substr($sale_detail->billing_gst,0,2)){
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
               $freight = SaleSundry::select('amount')
                           ->where('sale_id', $id)
                           ->where('bill_sundry',4)
                           ->first();
               $insurance = SaleSundry::select('amount')
                           ->where('sale_id', $id)
                           ->where('bill_sundry',7)
                           ->first();
               $discount = SaleSundry::select('amount')
                           ->where('sale_id', $id)
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
      $bank_detail = DB::table('banks')->where('company_id', Session::get('user_company_id'))
            ->select('banks.*')
            ->first();
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }else if($company_data->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      if(substr($company_data->gst,0,2)==substr($sale_detail->billing_gst,0,2)){
         if($sale_detail->total<100000){
            $GstSettings->ewaybill = 0;
         }
      }else{
         if($sale_detail->total<50000){
            $GstSettings->ewaybill = 0;
         }
      }
      return view('saleInvoice')->with(['items_detail' => $items_detail, 'sale_sundry' => $sale_sundry, 'party_detail' => $party_detail, 'company_data' => $company_data, 'sale_detail' => $sale_detail,'bank_detail' => $bank_detail,'gst_detail'=>$gst_detail,'einvoice_status'=>$GstSettings->einvoice,'ewaybill_status'=>$GstSettings->ewaybill]);
   }
   public function delete(Request $request){
      $sale =  Sales::find($request->sale_id);
      $sale->delete = '1';
      $sale->deleted_at = Carbon::now();
      $sale->deleted_by = Session::get('user_id');
      $sale->update();
      if($sale) {
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
      $sale->series_no = $request->input('series_no');
      $sale->date = $request->input('date');
      $sale->voucher_no = $request->input('voucher_no');
      $sale->party = $request->input('party');
      $sale->material_center = $request->input('material_center');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->self_vehicle = $request->input('self_vehicle');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->ewaybill_no = $request->input('ewaybill_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->reverse_charge = $request->input('reverse_charge');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->station = $request->input('station');
      $sale->billing_name = $account->account_name;
      $sale->billing_address = $account->address;
      $sale->billing_pincode = $account->pin_code;
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
         SaleDescription::where('sale_id',$request->input('sale_edit_id'))->delete();
         ItemLedger::where('source_id',$request->input('sale_edit_id'))->where('source',1)->delete();
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
               if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                  $ledger->debit = $bill_sundry_amounts[$key];
               }else{
                  $ledger->credit = $bill_sundry_amounts[$key];
               } 
               $ledger->txn_date = $request->input('date');
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
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->debit = $request->input('total');
         $ledger->txn_date = $request->input('date');
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
            return redirect('sale-invoice/'.$sale->id)->withSuccess('Sale voucher updated successfully!');
         }
         
      }else{
         return $this->failedMessage('Something went wrong','sale/create');
         exit();
      }
   }
}
