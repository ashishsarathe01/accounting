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
      $purchase = DB::table('purchase_returns')
            ->select('purchase_returns.id as purchases_id', 'purchase_returns.date', 'purchase_returns.invoice_no', 'purchase_returns.total','purchase_return_no','purchase_returns.series_no','purchase_returns.financial_year', DB::raw('(select account_name from accounts where accounts.id=purchase_returns.party limit 1) as account_name'))
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
        $party_list = Accounts::where('delete', '=', '0')
                                ->where('status', '=', '1')
                                ->whereIn('company_id', [Session::get('user_company_id'),0])
                                ->whereIn('under_group', [3,11])
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

        // if (!empty($GstSettings->invoice_start_from)) {
        //     $GstSettings->invoice_start_from = $GstSettings->invoice_start_from + 1;
        // } else {
        //     $GstSettings->invoice_start_from = 1;
        // }

        $purchaseData = Purchase::where('company_id', Session::get('user_company_id'))->orderBy('id', 'desc')->limit(1)->get();


        // if ($purchaseData->count() > 0) {
        //     $GstSettings->invoice_start_from = $purchaseData[0]->voucher_no + 1;
        // }

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
         $purchase_return_no = PurchaseReturn::select('purchase_return_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('delete','=','0')
                           ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
         if(!$purchase_return_no){
            $purchase_return_no = 1;
         }else{
            $purchase_return_no++;
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
        return view('addPurchaseReturn')->with('party_list', $party_list)->with('manageitems', $manageitems)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('purchase_return_no', $purchase_return_no)->with('bill_date', $bill_date);
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
         return $this->failedMessage('Plases Select Item','purchase-return/create');
      }
      $financial_year = Session::get('default_fy');
      $purchase_return_no = PurchaseReturn::select('purchase_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',$financial_year)
                        ->where('delete','=','0')
                        ->where('series_no',$request->input('series_no'))
                        ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
      if(!$purchase_return_no){
         $purchase_return_no = 1;
      }else{
         $purchase_return_no++;
      }
      $purchase = new PurchaseReturn;
      $purchase->date = $request->input('date');
      $purchase->company_id = Session::get('user_company_id');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->party = $request->input('party_id');
      $purchase->taxable_amt = $request->input('taxable_amt');
      $purchase->total = $request->input('total');
      $purchase->series_no = $request->input('series_no');
      $purchase->material_center = $request->input('material_center');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->station = $request->input('station');
      $purchase->purchase_return_no = $purchase_return_no;
      $purchase->financial_year = $financial_year;
      $purchase->purchase_bill_id = $request->input('purchase_bill_id');
      $purchase->save();
      $roundoff = $request->input('total')-$request->input('taxable_amt');
      if($purchase->id){
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         foreach($goods_discriptions as $key => $good){
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
         PurchaseReturnDescription::where('purchase_return_id',$request->purchase_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         AccountLedger::where('entry_type',4)
                        ->where('entry_type_id',$request->purchase_return_id)
                        ->update(['delete_status'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
         PurchaseReturnSundry::where('purchase_return_id',$request->purchase_return_id)
                        ->update(['delete'=>'1','deleted_at'=>Carbon::now(),'deleted_by'=>Session::get('user_id')]);
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
      $GstSettings = (object)NULL;
      $GstSettings->mat_center = array();
      if($companyData->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
      }elseif ($companyData->gst_config_type == "multiple_gst") {
         $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
      }
      if(!$GstSettings || !isset($companyData->gst_config_type)){
         return $this->failedMessage('Please Enter GST Configuration!','purchase-return');
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
                                ->get();
      $financial_year = Session::get('default_fy');
      return view('editPurchaseReturn')->with('party_list', $party_list)->with('billsundry', $billsundry)->with('mat_center', $mat_center)->with('GstSettings', $GstSettings)->with('mat_series', $mat_series)->with('purchase_return', $purchase_return)->with('purchase_return_description', $purchase_return_description)->with('purchase_return_sundry', $purchase_return_sundry);
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
         return $this->failedMessage('Plases Select Item','purchase-return/create');
      }
      $financial_year = Session::get('default_fy');
      
      $purchase = PurchaseReturn::find($request->input('purchase_return_edit_id'));
      $purchase->date = $request->input('date');
      $purchase->invoice_no = $request->input('voucher_no');
      $purchase->party = $request->input('party');
      $purchase->taxable_amt = $request->input('taxable_amt');
      $purchase->total = $request->input('total');
      $purchase->series_no = $request->input('series_no');
      $purchase->material_center = $request->input('material_center');
      $purchase->vehicle_no = $request->input('vehicle_no');
      $purchase->gr_pr_no = $request->input('gr_pr_no');
      $purchase->transport_name = $request->input('transport_name');
      $purchase->station = $request->input('station');
      $purchase->purchase_return_no = $request->input('purchase_return_no');
      $purchase->financial_year = $financial_year;
      $purchase->purchase_bill_id = $request->input('purchase_bill_id');
      $purchase->save();
      $roundoff = $request->input('total')-$request->input('taxable_amt');
      if($purchase->id){
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $units = $request->input('units');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         PurchaseReturnDescription::where('purchase_return_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',5)->delete();
         foreach($goods_discriptions as $key => $good){
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
         $bill_sundrys = $request->input('bill_sundry');
         $tax_rate = $request->input('tax_rate');
         $bill_sundry_amounts = $request->input('bill_sundry_amount');
         AccountLedger::where('entry_type_id',$purchase->id)->where('entry_type',4)->delete();
         PurchaseReturnSundry::where('purchase_return_id',$purchase->id)->delete();
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
               $ledger->map_account_id = $request->input('party');
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $roundoff = $roundoff - $bill_sundry_amounts[$key];
            }
         }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
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
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();     
         if(!empty(Session::get('redirect_url'))){
            return redirect(Session::get('redirect_url'));
         }else{
            return redirect('purchase-return')->withSuccess('Purchase return updated successfully!');
         }    
         
      }else{
         $this->failedMessage('Something went wrong','purchase-return/create');
      }
   }
}
