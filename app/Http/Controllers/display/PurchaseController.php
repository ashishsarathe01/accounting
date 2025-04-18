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
use App\Models\ParameterInfo;
use App\Models\ParameterInfoValue;
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
      $party_list = Accounts::where('delete', '=', '0')
                                ->where('status', '=', '1')
                                ->whereIn('company_id', [Session::get('user_company_id'),0])
                                ->whereIn('under_group', [3,11])
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
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','sale');
      }
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->where('company_id',Session::get('user_company_id'))
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
      return view('addPurchase')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('GstSettings', $GstSettings)->with('bill_date', $bill_date);
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
      echo "<pre>";
      print_r($request->all());
      // print_r(json_decode($request->input('item_parameters')[0],true));
      // print_r($request->input('item_parameters')[0]);
      // print_r(json_decode($request->input('item_parameters')[0],true));
      die;
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
                              $parameter_info_value->save();
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
            if($billsundry->adjust_sale_amt=='No'){
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
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
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
            if($billsundry->adjust_sale_amt=='No'){
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
}
