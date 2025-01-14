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
      $sale = DB::table('sales_returns')
            ->select('sales_returns.id as sales_returns_id', 'sales_returns.date','sales_returns.series_no','sales_returns.financial_year', 'sales_returns.invoice_no','sale_return_no', 'sales_returns.total', DB::raw('(select account_name from accounts where accounts.id=sales_returns.party limit 1) as account_name'))
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
      $party_list = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', [3,11])
                              ->orderBy('account_name')
                              ->get();
      $manageitems = DB::table('manage_items')->where('manage_items.company_id', Session::get('user_company_id'))
            ->select('units.s_name as unit', 'manage_items.*')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->orderBy('manage_items.name')
            ->get();
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();

      $GstSettings = (object)NULL;
      $GstSettings->mat_center = array();

      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale-return');
      }
      
      $saleData = Sales::where('company_id', Session::get('user_company_id'))->orderBy('id', 'desc')->limit(1)->get();
      if($saleData->count() > 0) {
         $GstSettings->invoice_start_from = $saleData[0]->voucher_no + 1;
      }
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if (!empty($GstSettings->mat_center)) {
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
                                 ->get();
      $financial_year = Session::get('default_fy');
      $sale_return_no = SalesReturn::select('sale_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
      if(!$sale_return_no){
         $sale_return_no = 1;
      }else{
         $sale_return_no++;
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
      return view('addSaleReturn')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('sale_return_no', $sale_return_no)->with('bill_date', $bill_date);
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
         'voucher_no' => 'required',
         'party_id' => 'required',         
         'goods_discription' => 'required|array|min:1',
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      //Check Item Empty or not
      if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','sale-return/create');
      }
      $financial_year = Session::get('default_fy');
      $sale_return_no = SalesReturn::select('sale_return_no')                   
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->where('series_no',$request->input('series_no'))
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
      if(!$sale_return_no){
         $sale_return_no = 1;
      }else{
         $sale_return_no++;
      }
      $sale = new SalesReturn;
      $sale->date = $request->input('date');
      $sale->company_id = Session::get('user_company_id');
      $sale->invoice_no = $request->input('voucher_no');
      $sale->party = $request->input('party_id');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->series_no = $request->input('series_no');
      $sale->material_center = $request->input('material_center');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->station = $request->input('station');
      $sale->sale_return_no = $sale_return_no;
      $sale->financial_year = $financial_year;
      $sale->sale_bill_id = $request->input('sale_bill_id');
      $sale->save();
      //$roundoff = $request->input('total')-$request->input('taxable_amt');
      if($sale->id){
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
         return redirect('sale-return-invoice/'.$sale->id)->withSuccess('Sale return added successfully!');
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
         SaleReturnDescription::where('sale_return_id',$request->sale_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',3)
                        ->where('entry_type_id',$request->sale_return_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         SaleReturnSundry::where('sale_return_id',$request->sale_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         return redirect('sale-return')->withSuccess('Sale Return deleted successfully!');
      }
   }
   public function saleReturnInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $sale_return = SalesReturn::join('sales','sales_returns.sale_bill_id','=','sales.id')
                                 ->join('states','sales.billing_state','=','states.id')
                                 ->where('sales_returns.id',$id)
                                 ->select(['sales_returns.date','sales_returns.invoice_no','sales_returns.total','sales.billing_name','sales.billing_address','sales.billing_pincode','sales.billing_gst','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.gr_pr_no','sales_returns.transport_name','sales_returns.station','sales.voucher_no','sales.date as sale_date','sales.series_no','sales.financial_year','sales_returns.series_no as sr_series_no','sales_returns.financial_year as sr_financial_year'])
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
      }else if($company_data->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      return view('saleReturnInvoice')->with(['items_detail' => $items_detail, 'sale_sundry' => $sale_sundry,'company_data' => $company_data,'gst_detail'=>$gst_detail,'sale_return'=>$sale_return,'einvoice_status'=>$GstSettings->einvoice]);
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale-return');
      }
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if (!empty($GstSettings->mat_center)) {
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
                                 ->get();
      $financial_year = Session::get('default_fy');                   
      return view('editSaleReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('sale_return', $sale_return)->with('sale_return_description', $sale_return_description)->with('sale_return_sundry', $sale_return_sundry);
   }
   public function update(Request $request){
      // echo "<pre>";
      // print_r($request->all());die;
      $validated = $request->validate([
         'date' => 'required',
         'voucher_no' => 'required',
         'party' => 'required',         
         'goods_discription' => 'required|array|min:1',
         'series_no' => 'required',
         'material_center' => 'required',
      ]);
      //Check Item Empty or not      
      if($request->input('goods_discription')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','sale-return/create');
      }      
      $account = Accounts::where('id',$request->input('party'))->first();
      $financial_year = Session::get('default_fy');
      $sale = SalesReturn::find($request->input('sale_return_edit_id'));
      $sale->date = $request->input('date');
      $sale->invoice_no = $request->input('voucher_no');
      $sale->party = $request->input('party');
      $sale->taxable_amt = $request->input('taxable_amt');
      $sale->total = $request->input('total');
      $sale->series_no = $request->input('series_no');
      $sale->material_center = $request->input('material_center');
      $sale->vehicle_no = $request->input('vehicle_no');
      $sale->gr_pr_no = $request->input('gr_pr_no');
      $sale->transport_name = $request->input('transport_name');
      $sale->station = $request->input('station');
      $sale->sale_return_no = $request->input('sale_return_no');
      $sale->financial_year = $financial_year;
      $sale->sale_bill_id = $request->input('sale_bill_id');
      $sale->save();
      if($sale->id){

         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         SaleReturnDescription::where('sale_return_id',$sale->id)->delete();
         ItemLedger::where('source_id',$sale->id)->where('source',4)->delete();
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
         $bill_sundrys = $request->input('bill_sundry');
         $tax_rate = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');

         AccountLedger::where('entry_type_id',$sale->id)->where('entry_type',3)->delete();
         SaleReturnSundry::where('sale_return_id',$sale->id)->delete();
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
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('sale-return-invoice/'.$sale->id)->withSuccess('Sale return updated successfully!');
         }     
         
      }else{
         return $this->failedMessage('Something went wrong','purchase/create');
      }
   }
}
