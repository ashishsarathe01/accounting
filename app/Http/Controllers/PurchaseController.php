<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseSundry;
use App\Models\PurchaseDescription;
use App\Models\Accounts;
use App\Models\GstBranch;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ManageItems;
use App\Models\ParameterInfo;
use App\Models\ParameterInfoValue;
use App\Models\ParameterInfoValueDetail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use Session;
use DateTime;
class PurchaseController extends Controller{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
       $input = $request->all();
      // Default date range (first day of current month to today)
$from_date = session('purchase_from_date', "01-" . date('m-Y'));
$to_date = session('purchase_to_date', date('d-m-Y'));

// Check if user has selected a date range
if (!empty($input['from_date']) && !empty($input['to_date'])) {
    $from_date = date('d-m-Y', strtotime($input['from_date']));
    $to_date = date('d-m-Y', strtotime($input['to_date']));
    
    // Store in session so it persists after refresh
    session(['purchase_from_date' => $from_date, 'purchase_to_date' => $to_date]);
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
      $purchase = Purchase::with(['purchaseDescription'=> function ($query){
                              $query->with(['item'=>function($q){
                                 $q->select('name','id');
                              },'units'=>function($q1){
                                 $q1->select('id','name');
                              },'parameterColumnInfo'=>function($q2){
                                 $q2->with(['parameterColumnName'=>function($q3){
                                    $q3->select('id','paremeter_name');
                                 },'parameterColumnValues'=>function($q4){
                                    $q4->select('parent_id','column_value');
                                 }]);
                                 $q2->select('id','purchase_desc_row_id','parameter_col_id');
                              }]);
                              $query->select('id', 'goods_discription','qty','purchase_id','unit');
                           },'account'=>function($query){                              
                              $query->select('id', 'account_name');
                           }])
                           ->select(['id','date','voucher_no','total','party'])
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('delete','0')
                           ->whereRaw("STR_TO_DATE(purchases.date,'%Y-%m-%d')>=STR_TO_DATE('".date('Y-m-d',strtotime($from_date))."','%Y-%m-%d') and STR_TO_DATE(purchases.date,'%Y-%m-%d')<=STR_TO_DATE('".date('Y-m-d',strtotime($to_date))."','%Y-%m-%d')")
                           ->orderBy('purchases.created_at', 'ASC')
                           ->get();
         // echo "<pre>";
         // print_r($purchase->toArray());die;
      return view('purchase')->with('purchase', $purchase)->with('month_arr', $month_arr)->with("from_date",$from_date)->with("to_date",$to_date);
   }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
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

      $party_list = Accounts::leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get();    

      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      if($companyData->gst_config_type == "single_gst"){
         $GstSettings = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$GstSettings[0]->id])
                           ->get();
         if(count($branch)>0){
            $GstSettings = $GstSettings->merge($branch);
         }
         
      }else if($companyData->gst_config_type == "multiple_gst"){
         $GstSettings = DB::table('gst_settings_multiple')
                           ->select('id','gst_no','mat_center','series','invoice_start_from')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
         foreach ($GstSettings as $key => $value) {
            $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                        ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                        ->get();
            if(count($branch)>0){
               $GstSettings = $GstSettings->merge($branch);
            }
         }         
      }
      if(!isset($companyData->gst_config_type) || !$GstSettings){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->orderBy('name')
                                 ->get();
      $financial_year = Session::get('default_fy');
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
      //Item List
      $item = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.id','manage_items.u_name','manage_items.gst_rate','manage_items.name','parameterized_stock_status','config_status','item_groups.id as group_id'])
            ->get(); 
      return view('addPurchase')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('GstSettings', $GstSettings)->with('bill_date', $bill_date)->with('items', $item);
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
      //Check Item Empty or not
      // echo "<pre>";
      // print_r($request->all());
      // print_r(json_decode($request->input('item_parameters')[0],true));
      // print_r($request->input('item_parameters')[0]);
      // print_r(json_decode($request->input('item_parameters')[0],true));
      //die;
      if($request->input('goods_discription')[0]=="" || $request->input('qty')[0]=="" || $request->input('price')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','purchase/create');
      }
      $financial_year = Session::get('default_fy'); 
      $account = Accounts::where('id',$request->input('party_id'))->first();
      $purchase = new Purchase;
      $purchase->series_no = $request->input('series_no');
      $purchase->company_id = Session::get('user_company_id');
      $purchase->date = $request->input('date');
      $purchase->voucher_no = $request->input('voucher_no');
      $purchase->party = $request->input('party_id');
      $purchase->material_center = $request->input('material_center');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->reverse_charge = $request->input('reverse_charge');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->ewaybill_no = $request->input('ewaybill_no');
      $purchase->station = $request->input('station');
      $purchase->taxable_amt = $request->input('taxable_amt');
      $purchase->total = $request->input('total');
      $purchase->self_vehicle = $request->input('self_vehicle');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->invoice_date = $request->input('invoice_date');
      $roundoff = $request->input('total')-$request->input('taxable_amt');
      $purchase->billing_name = $account->account_name;
      $purchase->billing_address = $account->address;
      $purchase->billing_pincode = $account->pin_code;
      $purchase->billing_gst = $account->gstin;
      $purchase->billing_state = $account->state;
      $purchase->shipping_name = $request->input('shipping_name');
      $purchase->shipping_state = $request->input('shipping_state');
      $purchase->shipping_address = $request->input('shipping_address');
      $purchase->shipping_pincode = $request->input('shipping_pincode');
      $purchase->shipping_gst = $request->input('shipping_gst');
      $purchase->shipping_pan = $request->input('shipping_pan');
      $purchase->financial_year = $financial_year;
      $purchase->save();
      if($purchase->id){
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         $config_status = $request->input('config_status');
         $item_parameters = $request->input('item_parameters');
         foreach ($goods_discriptions as $key => $good) {
            if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            $desc = new PurchaseDescription;
            $desc->purchase_id = $purchase->id;
            $desc->goods_discription = $good;
            $desc->qty = $qtys[$key];
            $desc->unit = $units[$key];
            $desc->price = $prices[$key];
            $desc->amount = $amounts[$key];
            $desc->parameter_source = $config_status[$key];
            $desc->status = '1';
            $desc->save();
            //ADD ITEM LEDGER
            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $good;
            $item_ledger->in_weight = $qtys[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $prices[$key];
            $item_ledger->total_price = $amounts[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 2;
            $item_ledger->source_id = $purchase->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
            //Parameter Info
            if($item_parameters[$key]!=""){
               $parameter = json_decode($item_parameters[$key],true);
               if(count($parameter)>0){
                  foreach ($parameter as $k1 => $param) {
                     $column_id = $param['column_id'];
                     $parameter_info = new ParameterInfo();
                     $parameter_info->item_id = $good;
                     $parameter_info->purchase_id = $purchase->id;
                     $parameter_info->purchase_desc_row_id = $desc->id;
                     $parameter_info->parameter_col_id = $column_id;
                     //$parameter_info->purchase_type = "PURCHASE";
                     $parameter_info->company_id = Session::get('user_company_id');
                     $parameter_info->created_by = Session::get('user_id');
                     $parameter_info->created_at = date('Y-m-d H:i:s');
                     if($parameter_info->save()){
                        if(count($param['column_value'])>0){
                           foreach ($param['column_value'] as $k2 => $param_val) {
                              $parameter_info_value = new ParameterInfoValue();
                              $parameter_info_value->parent_id = $parameter_info->id;
                              $parameter_info_value->item_id = $good;
                              $parameter_info_value->column_value = $param_val;
                              $parameter_info_value->in_source_id = $purchase->id;
                              $parameter_info_value->in_source_row_id = $desc->id;
                              $parameter_info_value->company_id = Session::get('user_company_id');
                              $parameter_info_value->created_at = date('Y-m-d H:i:s');
                              if($parameter_info_value->save()){
                                 $parameter_info_value_detail = new ParameterInfoValueDetail();
                                 $parameter_info_value_detail->parent_id = $parameter_info->id;
                                 $parameter_info_value_detail->item_id = $good;
                                 $parameter_info_value_detail->column_value = $param_val;
                                 $parameter_info_value_detail->in_source_id = $purchase->id;
                                 $parameter_info_value_detail->in_source_row_id = $desc->id;
                                 $parameter_info_value_detail->company_id = Session::get('user_company_id');
                                 $parameter_info_value_detail->created_at = date('Y-m-d H:i:s');
                              }

                              
                           }
                        }
                     }
                  }
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
            $sundry = new PurchaseSundry;
            $sundry->purchase_id = $purchase->id;
            $sundry->bill_sundry = $bill;
            $sundry->rate = $tax_amts[$key];
            $sundry->amount = $bill_sundry_amounts[$key];
            $sundry->status = '1';
            $sundry->save();
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();
            if($billsundry->adjust_purchase_amt=='No'){
               $ledger = new AccountLedger();
               $ledger->account_id = $billsundry->purchase_amt_account;
               if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                  $ledger->credit = $bill_sundry_amounts[$key];
               }else{
                  $ledger->debit = $bill_sundry_amounts[$key];
               }
               //debit
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 2;
               $ledger->entry_type_id = $purchase->id;
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
         $ledger->credit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = 36;//Purchase
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 36;//Purchase
         $ledger->debit = $request->input('taxable_amt');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = $request->input('party_id');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
          
         return redirect('purchase')->withSuccess('Purchase voucher added successfully!');
      }else{
         return $this->failedMessage('Something went wrong','purchase/create');
      }
   }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function purchaseInvoice($id){
      $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
      $items_detail = DB::table('purchase_descriptions')->where('purchase_id', $id)
            ->select('units.s_name as unit', 'units.id as unit_id', 'purchase_descriptions.qty', 'purchase_descriptions.price', 'purchase_descriptions.amount', 'manage_items.name as items_name', 'manage_items.id as item_id', 'purchases.*', 'accounts.*','manage_items.hsn_code','manage_items.gst_rate')
            ->join('units', 'purchase_descriptions.unit', '=', 'units.id')
            ->join('purchases', 'purchases.id', '=', 'purchase_descriptions.purchase_id')
            ->join('manage_items', 'purchase_descriptions.goods_discription', '=', 'manage_items.id')
            ->join('accounts', 'accounts.id', '=', 'purchases.party')
            ->get();
      $sale_detail = purchase::join('states','purchases.billing_state','=','states.id')
                           ->leftjoin('accounts','purchases.shipping_name','=','accounts.id')
                           ->where('purchases.id', $id)
                           ->select(['purchases.*','states.name as sname','accounts.account_name as shipp_name'])
                           ->first();
      // echo "<pre>";
      // print_r($sale_detail);die;
      $party_detail = Accounts::join('states','accounts.state','=','states.id')
                                 ->where('accounts.id',$sale_detail->party)
                                 ->select(['accounts.*','states.name as sname'])
                                 ->first();
      $sale_sundry = DB::table('purchase_sundries')
                           ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                           ->where('purchase_id', $id)
                           ->select('purchase_sundries.bill_sundry','purchase_sundries.rate','purchase_sundries.amount','bill_sundrys.name')
                           ->orderBy('sequence')
                           ->get();
      $gst_detail = DB::table('purchase_sundries')
                           ->select('rate','amount')                     
                           ->where('purchase_id', $id)
                           ->where('rate','!=','0')
                           ->distinct('rate')                       
                           ->get(); 
      $max_gst = DB::table('purchase_sundries')
                           ->select('rate')                     
                           ->where('purchase_id', $id)
                           ->where('rate','!=','0')
                           ->max(\DB::raw("cast(rate as SIGNED)"));
      if(count($gst_detail)>0){
         foreach ($gst_detail as $key => $value){
            $rate = $value->rate;      
            if(substr($company_data->gst,0,2)==substr($party_detail->gstin,0,2)){
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
               $freight = PurchaseSundry::select('amount')
                           ->where('purchase_id', $id)
                           ->where('bill_sundry',4)
                           ->first();
               $insurance = PurchaseSundry::select('amount')
                           ->where('purchase_id', $id)
                           ->where('bill_sundry',7)
                           ->first();
               $discount = PurchaseSundry::select('amount')
                           ->where('purchase_id', $id)
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
      return view('purchaseInvoice')->with(['items_detail' => $items_detail, 'sale_sundry' => $sale_sundry, 'party_detail' => $party_detail, 'company_data' => $company_data, 'sale_detail' => $sale_detail,'gst_detail'=>$gst_detail]);
   }
   public function delete(Request $request){
      $purchase =  Purchase::find($request->purchase_id);
      $purchase->delete = '1';
      $purchase->deleted_at = Carbon::now();
      $purchase->deleted_by = Session::get('user_id');
      $purchase->update();
      if($purchase) {
         PurchaseDescription::where('purchase_id',$request->purchase_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',2)
                        ->where('entry_type_id',$request->purchase_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         PurchaseSundry::where('purchase_id',$request->purchase_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         ItemLedger::where('source',2)
                     ->where('source_id',$request->purchase_id)
                     ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         return redirect('purchase')->withSuccess('Sale deleted successfully!');
      }
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function purchaseEdit($id){
      $purchase = Purchase::where('id',$id)->first();
      $PurchaseDescription = PurchaseDescription::join('units','purchase_descriptions.unit','=','units.id')
                           ->where('purchase_id',$id)
                           ->select(['purchase_descriptions.*','units.s_name'])
                           ->get();
      $PurchaseSundry = PurchaseSundry::join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','purchase_sundries.*'])
                                 ->where('purchase_id',$id)
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
      $manageitems = DB::table('manage_items')->where('manage_items.company_id', Session::get('user_company_id'))
            ->select('units.s_name as unit', 'manage_items.*')
            ->where('manage_items.delete', '=', '0')
            ->join('units', 'manage_items.u_name', '=', 'units.id')
            ->orderBy('manage_items.name')
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
         $branch = GstBranch::select('id','gst_number as gst_no','branch_matcenter as mat_center','branch_series as series','branch_invoice_start_from as invoice_start_from')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$mat_series[0]->id])
                           ->get();
         if(count($branch)>0){
            $mat_series = $mat_series->merge($branch);
         }
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
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
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','purchase');
      }
      $purchaseData = Purchase::where('company_id', Session::get('user_company_id'))->orderBy('id', 'desc')->limit(1)->get();
      $mat_center = array();
      $mat_center = GstBranch::select('branch_matcenter')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      if(!empty($GstSettings->mat_center)) {
         $mat_center[] = array("branch_matcenter" => $GstSettings->mat_center);
      }
      // $mat_series = array();
      // $mat_series = GstBranch::select('branch_series')->where(['delete' => '0', 'company_id' => Session::get('user_company_id')])->get()->toArray();
      // if(!empty($GstSettings->series)) {
      //    $mat_series[] = array("branch_series" => $GstSettings->series);
      // }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->orderBy('name')
                                 ->get();

      return view('editPurchase')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('purchase', $purchase)->with('PurchaseDescription', $PurchaseDescription)->with('PurchaseSundry', $PurchaseSundry);
   }
   public function update(Request $request){
      // echo "<pre>";
      // print_r($request->all());
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
      
      if($request->input('goods_discription')[0]=="" || $request->input('qty')[0]=="" || $request->input('price')[0]=="" || $request->input('amount')[0]==""){
         return $this->failedMessage('Plases Select Item','purchase/create');
      }
      $account = Accounts::where('id',$request->input('party'))->first();
      $purchase = Purchase::find($request->input('purchase_edit_id'));
      $purchase->series_no = $request->input('series_no');
      $purchase->date = $request->input('date');
      $purchase->voucher_no = $request->input('voucher_no');
      $purchase->party = $request->input('party');
      $purchase->material_center = $request->input('material_center');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->reverse_charge = $request->input('reverse_charge');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->ewaybill_no = $request->input('ewaybill_no');
      $purchase->station = $request->input('station');
      $purchase->taxable_amt = $request->input('taxable_amt');
      $purchase->total = $request->input('total');
      $purchase->self_vehicle = $request->input('self_vehicle');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->invoice_date = $request->input('invoice_date');      
      $purchase->billing_name = $account->account_name;
      $purchase->billing_address = $account->address;
      $purchase->billing_pincode = $account->pin_code;
      $purchase->billing_gst = $account->gstin;
      $purchase->billing_state = $account->state;
      $purchase->shipping_name = $request->input('shipping_name');
      $purchase->shipping_state = $request->input('shipping_state');
      $purchase->shipping_address = $request->input('shipping_address');
      $purchase->shipping_pincode = $request->input('shipping_pincode');
      $purchase->shipping_gst = $request->input('shipping_gst');
      $purchase->shipping_pan = $request->input('shipping_pan');
      $purchase->save();
      if($purchase->id){
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         PurchaseDescription::where('purchase_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',2)->delete();
         foreach ($goods_discriptions as $key => $good) {
            if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            $desc = new PurchaseDescription;
            $desc->purchase_id = $purchase->id;
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
            $item_ledger->in_weight = $qtys[$key];
            $item_ledger->txn_date = $request->input('date');
            $item_ledger->price = $prices[$key];
            $item_ledger->total_price = $amounts[$key];
            $item_ledger->company_id = Session::get('user_company_id');
            $item_ledger->source = 2;
            $item_ledger->source_id = $purchase->id;
            $item_ledger->created_by = Session::get('user_id');
            $item_ledger->created_at = date('d-m-Y H:i:s');
            $item_ledger->save();
         }
         $bill_sundrys = $request->input('bill_sundry');
         $tax_amts = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');

         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',2)->delete();
         PurchaseSundry::where('purchase_id',$purchase->id)->delete();
         foreach($bill_sundrys as $key => $bill){
            if($bill_sundry_amounts[$key]=="" || $bill==""){
               continue;
            }
            $sundry = new PurchaseSundry;
            $sundry->purchase_id = $purchase->id;
            $sundry->bill_sundry = $bill;
            $sundry->rate = $tax_amts[$key];
            $sundry->amount = $bill_sundry_amounts[$key];
            $sundry->status = '1';
            $sundry->save();
            //ADD DATA IN CGST ACCOUNT
            $billsundry = BillSundrys::where('id', $bill)->first();
            if($billsundry->adjust_purchase_amt=='No'){
               $ledger = new AccountLedger();
               $ledger->account_id = $billsundry->purchase_amt_account;
               if($billsundry->nature_of_sundry=='ROUNDED OFF (-)'){
                  $ledger->credit = $bill_sundry_amounts[$key];
               }else{
                  $ledger->debit = $bill_sundry_amounts[$key];
               }
               $ledger->txn_date = $request->input('date');
               $ledger->company_id = Session::get('user_company_id');
               $ledger->financial_year = Session::get('default_fy');
               $ledger->entry_type = 2;
               $ledger->entry_type_id = $purchase->id;
               $ledger->map_account_id = $request->input('party');
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
            }
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->credit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = 36;//Purchase
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 36;//Purchase
         $ledger->debit = $request->input('taxable_amt');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = Session::get('default_fy');
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();    
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('purchase')->withSuccess('Purchase voucher updated successfully!');
         }      
         
      }else{
         return $this->failedMessage('Something went wrong','purchase/create');
      }
   }
   public function purchaseImportView(Request $request){      
      return view('purchase_import');
   }
   public function purchaseImportProcess(Request $request) {       
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $duplicate_voucher_status = $request->duplicate_voucher_status;
      $financial_year = Session::get('default_fy');
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
               if($data[0]!="" && $data[2]!=""){
                  $series_no = $data[0];
                  $voucher_no = $data[2]; 
                  $party = $data[3];
                  $account = Accounts::select('id')
                                       ->where('account_name',trim($party))
                                       ->where('company_id',trim(Session::get('user_company_id')))
                                       ->first();
                  if($account){
                     $check_invoice = Purchase::select('id')
                                 ->where('party',$account->id)
                                 ->where('voucher_no',$voucher_no)
                                 ->where('financial_year','=',$financial_year)
                                 ->where('delete','0')
                                 ->first();
                     if($check_invoice){
                        array_push($already_exists_error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                     }
                     if(in_array($account->id."_".$voucher_no, $already_exists_voucher_arr)){
                        array_push($already_exists_error_arr, 'Voucher '.$voucher_no.' already exists - Invoice No. '.$voucher_no);
                     }
                     array_push($already_exists_voucher_arr,$account->id."_".$voucher_no);
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
            if($data[0]!="" && $data[2]!=""){
               if($series_no!=""){
                  array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"error_arr"=>$error_arr));
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
                     if($key%2==0){
                        if($value!="" && $value!='0'){
                           $bill_sundry = BillSundrys::where('delete', '=', '0')
                                    ->where('status', '=', '1')
                                    ->where('name',$value)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->first(); 
                           //$bill_sundry = $bill_sundrys->where('name',$value)->first();
                           if(!$bill_sundry){
                              array_push($error_arr, 'Bill Sundry '.$value.' not found - Invoice No. '.$voucher_no);
                           }
                        }
                        
                     }                     
                  }
               }
               if($duplicate_voucher_status!=2 && $account){
                  $check_invoice = Purchase::select('id')
                              ->where('company_id',Session::get('user_company_id'))
                              ->where('voucher_no',$voucher_no)
                              ->where('party',$account->id)
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
            $itemc = ManageItems::select('id','hsn_code')
                        ->where('name',trim($item_name))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
                        // echo "<pre>";
                        // print_r($itemc);
            //echo $itemc->id;
            if(!$itemc){
               
               array_push($error_arr, 'Item Name '.$item_name.' not found - Invoice No. '.$voucher_no);
            }
            $item_weight = $data[15];
            $item_weight = str_replace(",","",$item_weight);
            $price = $data[16];
            $price = str_replace(",","",$price);
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
               array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"error_arr"=>$error_arr));
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
               $account = Accounts::where('account_name',trim($party))
                        ->where('company_id',trim(Session::get('user_company_id')))
                        ->first();
               $slicedData = $value['slicedData'];
               if($duplicate_voucher_status==2){
                  $check_invoices = Purchase::select('id')
                              ->where('company_id',Session::get('user_company_id'))
                              ->where('party',$account->id)
                              ->where('series_no',$series_no)
                              ->where('voucher_no',$voucher_no)
                              ->where('financial_year','=',$financial_year)
                              ->where('delete','0')
                              ->pluck('id');
                  if($check_invoices){
                        Purchase::whereIn('id',$check_invoices)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        PurchaseDescription::whereIn('purchase_id',$check_invoices)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        AccountLedger::where('entry_type',2)
                                       ->whereIn('entry_type_id',$check_invoices)
                                       ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        PurchaseSundry::whereIn('purchase_id',$check_invoices)
                                       ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                        ItemLedger::where('source',2)
                                    ->whereIn('source_id',$check_invoices)
                                    ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
                     
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
               $purchase = new Purchase;
               $purchase->series_no = $series_no;
               $purchase->company_id = Session::get('user_company_id');
               $purchase->date = $date;
               $purchase->voucher_no = $voucher_no;
               $purchase->party = $account->id;
               $purchase->material_center = $material_center;
               //$sale->taxable_amt = $request->input('taxable_amt');//
               $purchase->total = $grand_total;
               $purchase->self_vehicle = $self_vehicle;
               $purchase->vehicle_no = $vehicle_no;
               $purchase->transport_name = $transport_name;
               $purchase->reverse_charge = $reverse_charge;
               $purchase->gr_pr_no = $gr_pr_no;
               $purchase->station = $station;
               $purchase->ewaybill_no = $ewaybill_no;
               $purchase->billing_name = $account->account_name;
               $purchase->billing_address = $account->address;
               $purchase->billing_pincode = $account->pin_code;
               $purchase->billing_gst = $account->gstin;
               $purchase->billing_state = $account->state; 
               if($shipp){
                  $purchase->shipping_name = $shipp->account_name;;
                  $purchase->shipping_state = $shipp->state;
                  $purchase->shipping_address = $shipp->address;
                  $purchase->shipping_pincode = $shipp->pin_code;
                  $purchase->shipping_gst = $shipp->gstin;
                  $purchase->shipping_pan = $shipp->pan;
               }
               $purchase->financial_year = $financial_year;
               $purchase->save();
               if($purchase->id){  
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
                        $sundry = new PurchaseSundry;
                        $sundry->purchase_id = $purchase->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate/2;
                        $sundry->amount = str_replace(",","",$cgst_rate);
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->purchase_amt_account;
                           $ledger->debit = str_replace(",","",$cgst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 2;
                           $ledger->entry_type_id = $purchase->id;
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
                        $sundry = new PurchaseSundry;
                        $sundry->purchase_id = $purchase->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate/2;
                        $sundry->amount = str_replace(",","",$sgst_rate);
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->purchase_amt_account;
                           $ledger->debit = str_replace(",","",$sgst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 2;
                           $ledger->entry_type_id = $purchase->id;
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
                        $sundry = new PurchaseSundry;
                        $sundry->purchase_id = $purchase->id;
                        $sundry->bill_sundry = $bill_sundrys->id;
                        $sundry->rate = $tx_rate;
                        $sundry->amount = str_replace(",","",$igst_rate);
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->purchase_amt_account;
                           $ledger->debit = str_replace(",","",$igst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->company_id = Session::get('user_company_id');
                           $ledger->financial_year = Session::get('default_fy');
                           $ledger->entry_type = 2;
                           $ledger->entry_type_id = $purchase->id;
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
                        $desc = new PurchaseDescription;
                        $desc->purchase_id = $purchase->id;
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
                        $item_ledger->in_weight = $v1['item_weight'];
                        $item_ledger->txn_date = $date;
                        $item_ledger->price = $v1['price'];
                        $item_ledger->total_price = str_replace(",","",$v1['amount']);
                        $item_ledger->company_id = Session::get('user_company_id');
                        $item_ledger->source = 2;
                        $item_ledger->source_id = $purchase->id;
                        $item_ledger->created_by = Session::get('user_id');
                        $item_ledger->created_at = date('d-m-Y H:i:s');
                        $item_ledger->save(); 
                     }
                                          
                  }
                  //Other Bill Sundry
                  $sundry_id = "";
                  $adjust_purchase_amt = "";
                  $purchase_amt_account = "";
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
                           $adjust_purchase_amt = $bill_sundrys->adjust_purchase_amt;
                           $nature_of_sundry = $bill_sundrys->nature_of_sundry;
                           $purchase_amt_account = $bill_sundrys->purchase_amt_account;
                           $bill_sundry_type = $bill_sundrys->bill_sundry_type;
                        }else if($k2%2!=0){
                           $v2 = str_replace(",","",$v2);                           
                           $sundry = new PurchaseSundry;
                           $sundry->purchase_id = $purchase->id;
                           $sundry->bill_sundry = $sundry_id   ;
                           $sundry->rate = 0;
                           $sundry->amount = str_replace(",","",$v2);
                           $sundry->status = '1';
                           $sundry->save();
                           //ADD DATA IN CGST ACCOUNT     
                           if($adjust_purchase_amt=='No'){
                              $ledger = new AccountLedger();
                              $ledger->account_id = $purchase_amt_account;
                              if($nature_of_sundry=='ROUNDED OFF (-)'){
                                 $ledger->credit= $v2;
                              }else{
                                 $ledger->debit  = $v2;
                              }               
                              $ledger->txn_date = $date;
                              $ledger->company_id = Session::get('user_company_id');
                              $ledger->financial_year = Session::get('default_fy');
                              $ledger->entry_type = 2;
                              $ledger->entry_type_id = $purchase->id;
                              $ledger->map_account_id = $account->id;
                              $ledger->created_by = Session::get('user_id');
                              $ledger->created_at = date('d-m-Y H:i:s');
                              $ledger->save();
                           }else if($adjust_purchase_amt=='Yes'){
                              if($bill_sundry_type=='additive'){
                                 $item_taxable_amount = $item_taxable_amount + str_replace(",","",$v2);
                              }else if($bill_sundry_type=='subtractive'){
                                 $item_taxable_amount = $item_taxable_amount - str_replace(",","",$v2);
                              }
                           }
                        }
                     }                     
                  }
                  //ADD DATA IN Customer ACCOUNT
                  $ledger = new AccountLedger();
                  $ledger->account_id = $account->id;
                  $ledger->credit = $grand_total;
                  $ledger->txn_date = $date;
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 2;
                  $ledger->entry_type_id = $purchase->id;
                  $ledger->map_account_id = 36;//Sales Account
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
                  //ADD DATA IN Sale ACCOUNT
                  $ledger = new AccountLedger();
                  $ledger->account_id = 36;//Sales Account
                  $ledger->debit = $item_taxable_amount;
                  $ledger->txn_date = $date;
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = 2;
                  $ledger->entry_type_id = $purchase->id;
                  $ledger->map_account_id = $account->id;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();

                  $update_sale = Purchase::find($purchase->id);
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
}
