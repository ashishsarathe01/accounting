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
use App\Models\ItemAverage;
use App\Models\ItemAverageDetail;
use App\Models\PurchaseParameterInfo;
use App\Models\ItemParameterStock;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use DB;
use Session;
use DateTime;
use Gate;
class PurchaseController extends Controller{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request)
{
    Gate::authorize('action-module', 11);

    $input = $request->all();

    // Initialize dates
    $from_date = null;
    $to_date = null;

    // If user submitted new dates, update session
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['purchase_from_date' => $from_date, 'purchase_to_date' => $to_date]);
    } elseif (session()->has('purchase_from_date') && session()->has('purchase_to_date')) {
        $from_date = session('purchase_from_date');
        $to_date = session('purchase_to_date');
    }

    Session::put('redirect_url', '');

    // Financial year processing
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');
    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07', $from . '-08', $from . '-09',
        $from . '-10', $from . '-11', $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];

    $query = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name',
                    'units:id,name',
                    'parameterColumnInfo' => function ($q2) {
                        $q2->leftjoin('item_paremeter_list as param1','purchase_parameter_info.parameter1_id','=','param1.id');
                        $q2->leftjoin('item_paremeter_list as param2','purchase_parameter_info.parameter2_id','=','param2.id');
                        $q2->leftjoin('item_paremeter_list as param3','purchase_parameter_info.parameter3_id','=','param3.id');
                        $q2->leftjoin('item_paremeter_list as param4','purchase_parameter_info.parameter4_id','=','param4.id');
                        $q2->leftjoin('item_paremeter_list as param5','purchase_parameter_info.parameter5_id','=','param5.id');
                        $q2->select('purchase_parameter_info.id', 'purchase_desc_row_id','parameter1_id','parameter2_id','parameter3_id','parameter4_id','parameter5_id','parameter1_value','parameter2_value','parameter3_value','parameter4_value','parameter5_value','param1.paremeter_name as paremeter_name1','param2.paremeter_name as paremeter_name2','param3.paremeter_name as paremeter_name3','param4.paremeter_name as paremeter_name4','param5.paremeter_name as paremeter_name5');
                    }
                ]);
                $query->select('id', 'goods_discription', 'qty', 'purchase_id', 'unit');
            },
            'account:id,account_name'
        ])
        ->select(['id', 'date', 'voucher_no', 'total', 'party'])
        ->where('company_id', Session::get('user_company_id'))
        ->where('delete', '0');

    // If date range is provided, filter by date
    if ($from_date && $to_date) {
        $query->whereRaw("
            STR_TO_DATE(purchases.date,'%Y-%m-%d') >= STR_TO_DATE('" . date('Y-m-d', strtotime($from_date)) . "','%Y-%m-%d')
            AND STR_TO_DATE(purchases.date,'%Y-%m-%d') <= STR_TO_DATE('" . date('Y-m-d', strtotime($to_date)) . "','%Y-%m-%d')
        ");
        $query->orderBy('purchases.created_at', 'ASC');
    } else {
        // No date selected — fetch latest 10 records
        $query->orderBy(DB::raw("cast(voucher_no as SIGNED)"), 'desc')
              ->orderBy('date', 'desc')
              ->limit(10);
    }

    $purchase = $query->get()->reverse()->values();
   //  echo "<pre>";
   //  print_r($purchase->toArray());die;
    return view('purchase')
        ->with('purchase', $purchase)
        ->with('month_arr', $month_arr)
        ->with('from_date', $from_date)
        ->with('to_date', $to_date);
}

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function create(){
      Gate::authorize('action-module',83);
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
      // echo "<pre>";
      // $parameter = json_decode($request->input('item_parameters')[0],true);
      // if(count($parameter)>0){
      //    foreach ($parameter as $k1 => $param) {
      //       $parameter1_id = "";$parameter1_value = "";
      //       $parameter2_id = "";$parameter2_value = "";
      //       $parameter3_id = "";$parameter3_value = "";
      //       $parameter4_id = "";$parameter4_value = "";
      //       $parameter5_id = "";$parameter5_value = "";
      //       $alternative_unit_value = 0;
      //       foreach($param as $k11 => $v){
      //          print_r($v);
      //          if($k11==0){
      //             $parameter1_id = $v['id'];
      //             $parameter1_value = $v['value'];
      //             if($v['alternative_unit']==1){
      //                $alternative_unit_value = $v['value'];
      //             }
      //          }else if($k11==1){
      //             $parameter2_id = $v['id'];
      //             $parameter2_value = $v['value'];
      //             if($v['alternative_unit']==1){
      //                $alternative_unit_value = $v['value'];
      //             }
      //          }else if($k11==2){
      //             $parameter3_id = $v['id'];
      //             $parameter3_value = $v['value'];
      //             if($v['alternative_unit']==1){
      //                $alternative_unit_value = $v['value'];
      //             }
      //          }else if($k11==3){
      //             $parameter4_id = $v['id'];
      //             $parameter4_value = $v['value'];
      //             if($v['alternative_unit']==1){
      //                $alternative_unit_value = $v['value'];
      //             }
      //          }else if($k11==4){
      //             $parameter5_id = $v['id'];
      //             $parameter5_value = $v['value'];
      //             if($v['alternative_unit']==1){
      //                $alternative_unit_value = $v['value'];
      //             }
      //          }
      //       }
      //       while($alternative_unit_value>0){

      //          $alternative_unit_value--;
      //       }
      //    }
      // }
      //          die;
      Gate::authorize('action-module',83);
      $validated = $request->validate([
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party_id' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ]);      
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
      $purchase->merchant_gst =  $request->input('merchant_gst');
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
            $desc->company_id = Session::get('user_company_id');
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
            $item_ledger->series_no = $request->input('series_no');
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
            //ADD ITEM AVERAGE



            //Parameter Info
            if($item_parameters[$key]!=""){
               $parameter = json_decode($item_parameters[$key],true);
               if(count($parameter)>0){
                  foreach ($parameter as $k1 => $param) {
                     $parameter1_id = "";$parameter1_value = "";
                     $parameter2_id = "";$parameter2_value = "";
                     $parameter3_id = "";$parameter3_value = "";
                     $parameter4_id = "";$parameter4_value = "";
                     $parameter5_id = "";$parameter5_value = "";
                     $alternative_unit_value = 0;
                     $alternative_unit_key = '';
                     foreach($param as $k11 => $v){
                        if($k11==0){
                           $parameter1_id = $v['id'];
                           $parameter1_value = $v['value'];
                           if($v['alternative_unit']==1){
                              $alternative_unit_key = "parameter1_value";
                           }
                        }else if($k11==1){
                           $parameter2_id = $v['id'];
                           $parameter2_value = $v['value'];
                           if($v['alternative_unit']==1){
                              $alternative_unit_key = "parameter2_value";
                           }
                        }else if($k11==2){
                           $parameter3_id = $v['id'];
                           $parameter3_value = $v['value'];
                           if($v['alternative_unit']==1){
                              $alternative_unit_key = "parameter3_value";
                           }
                        }else if($k11==3){
                           $parameter4_id = $v['id'];
                           $parameter4_value = $v['value'];
                           if($v['alternative_unit']==1){
                              $alternative_unit_key = "parameter4_value";
                           }
                        }else if($k11==4){
                           $parameter5_id = $v['id'];
                           $parameter5_value = $v['value'];
                           if($v['alternative_unit']==1){
                              $alternative_unit_key = "parameter5_value";
                           }
                        }
                        if($v['alternative_unit']==1){
                           $alternative_unit_value = $v['value'];
                        }
                     }
                     $purchase_parameter_info = new PurchaseParameterInfo;
                     $purchase_parameter_info->item_id = $good;
                     $purchase_parameter_info->purchase_id = $purchase->id;
                     $purchase_parameter_info->purchase_desc_row_id = $desc->id;
                     $purchase_parameter_info->parameter1_id = $parameter1_id;
                     $purchase_parameter_info->parameter1_value = $parameter1_value;
                     $purchase_parameter_info->parameter2_id = $parameter2_id;
                     $purchase_parameter_info->parameter2_value = $parameter2_value;
                     $purchase_parameter_info->parameter3_id = $parameter3_id;
                     $purchase_parameter_info->parameter3_value = $parameter3_value;
                     $purchase_parameter_info->parameter4_id = $parameter4_id;
                     $purchase_parameter_info->parameter4_value = $parameter4_value;
                     $purchase_parameter_info->parameter5_id = $parameter5_id;
                     $purchase_parameter_info->parameter5_value = $parameter5_value;
                     $purchase_parameter_info->company_id = Session::get('user_company_id');
                     $purchase_parameter_info->created_by = Session::get('user_id');
                     $purchase_parameter_info->created_at = date('Y-m-d H:i:s');
                     if($purchase_parameter_info->save()){
                        while($alternative_unit_value>0){
                           if($alternative_unit_key=="parameter1_value"){
                              $parameter1_value = 1;
                           }
                           if($alternative_unit_key=="parameter2_value"){
                              $parameter2_value = 1;
                           }
                           if($alternative_unit_key=="parameter3_value"){
                              $parameter3_value = 1;
                           }
                           if($alternative_unit_key=="parameter4_value"){
                              $parameter4_value = 1;
                           }
                           if($alternative_unit_key=="parameter5_value"){
                              $parameter5_value = 1;
                           }
                           $item_parameter_stock = new ItemParameterStock;
                           $item_parameter_stock->item_id = $good;
                           $item_parameter_stock->series_no = $request->input('series_no');
                           $item_parameter_stock->parameter1_id = $parameter1_id;
                           $item_parameter_stock->parameter1_value = $parameter1_value;
                           $item_parameter_stock->parameter2_id = $parameter2_id;
                           $item_parameter_stock->parameter2_value = $parameter2_value;
                           $item_parameter_stock->parameter3_id = $parameter3_id;
                           $item_parameter_stock->parameter3_value = $parameter3_value;
                           $item_parameter_stock->parameter4_id = $parameter4_id;
                           $item_parameter_stock->parameter4_value = $parameter4_value;
                           $item_parameter_stock->parameter5_id = $parameter5_id;
                           $item_parameter_stock->parameter5_value = $parameter5_value;
                           $item_parameter_stock->stock_in_id = $purchase->id;
                           $item_parameter_stock->stock_in_type = 'PURCHASE';
                           $item_parameter_stock->company_id = Session::get('user_company_id');
                           $item_parameter_stock->save();
                           $alternative_unit_value--;
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
            $sundry->company_id = Session::get('user_company_id');
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
               $ledger->series_no = $request->input('series_no');
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
         $ledger->series_no = $request->input('series_no');
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
         //ADD DATA IN Purcahse ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 36;//Purchase
         $ledger->series_no = $request->input('series_no');
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
         //Item Average Calculation Logic Start Here   
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         $item_average_arr = [];$item_average_total = 0;
         foreach ($goods_discriptions as $key => $good) {
            if($good=="" || $qtys[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            array_push($item_average_arr,array("item"=>$good,"quantity"=>$qtys[$key],"price"=>$prices[$key],"amount"=>$amounts[$key]));
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
            if($value['quantity']!="" && $value['quantity']!=0){
               $average_price = $average_amount/$value['quantity'];
               $average_price =  round($average_price,6);
            }else{
               $average_price = 0;
            }            
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->series_no = $request->input('series_no');
            $average_detail->item_id = $value['item'];
            $average_detail->type = 'PURCHASE';
            $average_detail->purchase_id = $purchase->id;
            $average_detail->purchase_weight = $value['quantity'];
            $average_detail->purchase_amount = $value['amount'];
            $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
            $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
            $average_detail->purchase_total_amount = $average_amount;
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            CommonHelper::RewriteItemAverageByItem($request->date,$value['item'],$request->input('series_no'));
            
         }
         //Item Average Calculation Logic End Here
         session(['previous_url_purchase' => URL::previous()]);
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
      $sale_detail = purchase::leftjoin('states','purchases.billing_state','=','states.id')
                           ->leftjoin('accounts','purchases.shipping_name','=','accounts.id')
                           ->where('purchases.id', $id)
                           ->select(['purchases.*','states.name as sname','accounts.account_name as shipp_name'])
                           ->first();
    //   echo "<pre>";
    //   print_r($sale_detail);die;
      $party_detail = Accounts::join('states','accounts.state','=','states.id')
                                 ->where('accounts.id',$sale_detail->party)
                                 ->select(['accounts.*','states.name as sname'])
                                 ->first();
      $sale_sundry = DB::table('purchase_sundries')
                           ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                           ->where('purchase_id', $id)
                           ->select('purchase_sundries.bill_sundry','purchase_sundries.rate','purchase_sundries.amount','bill_sundrys.name','bill_sundrys.bill_sundry_type','bill_sundrys.nature_of_sundry')
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
               $sun = PurchaseSundry::join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                              ->select('amount','bill_sundry_type')
                              ->where('purchase_id', $id)
                              ->where('nature_of_sundry','OTHER')
                              ->get();
               foreach ($sun as $k1 => $v1) {
                  if($v1->bill_sundry_type=="additive"){
                     $taxable_amount = $taxable_amount + $v1->amount;
                  }else if($v1->bill_sundry_type=="subtractive"){
                     $taxable_amount = $taxable_amount - $v1->amount;

                  }
               }
               // $freight = PurchaseSundry::select('amount')
               //             ->where('purchase_id', $id)
               //             ->where('bill_sundry',4)
               //             ->first();
               // $insurance = PurchaseSundry::select('amount')
               //             ->where('purchase_id', $id)
               //             ->where('bill_sundry',7)
               //             ->first();
               // $discount = PurchaseSundry::select('amount')
               //             ->where('purchase_id', $id)
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
      return view('purchaseInvoice')->with(['items_detail' => $items_detail, 'sale_sundry' => $sale_sundry, 'party_detail' => $party_detail,'month_arr' => $month_arr, 'company_data' => $company_data, 'sale_detail' => $sale_detail,'gst_detail'=>$gst_detail]);
   }
   public function delete(Request $request){
      Gate::authorize('action-module',58);
      $check_entry_in_cn_dn = DB::table('purchases')
                  ->select(
                        DB::raw('(select count(*) from sales_returns where sales_returns.sale_bill_id = purchases.id and voucher_type="PURCHASE" and status="1" and sales_returns.delete="0")  as sale_return_count'),
                        DB::raw('(select count(*) from purchase_returns where purchase_returns.purchase_bill_id = purchases.id and voucher_type="PURCHASE" and status="1" and purchase_returns.delete="0")  as purchase_return_count')
                  )
                  ->where('id',$request->purchase_id)
                  ->first();
      if($check_entry_in_cn_dn){
         if($check_entry_in_cn_dn->sale_return_count>0 || $check_entry_in_cn_dn->purchase_return_count>0){
            return back()->with('error', '❌ Action not allowed. Please delete or cancel the related Debit Note or Credit Note first.');
         }
      }
      $stock = ItemParameterStock::where('stock_in_id',$request->purchase_id)
                           ->where('stock_in_type','PURCHASE')
                           ->where('status',0)
                           ->first();
      if($stock){
         return back()->with('error', '❌ Action not allowed. Cannot delete this purchase. Items have already been sold from it.');
      }
      $purchase =  Purchase::find($request->purchase_id);
      $purchase->delete = '1';
      $purchase->deleted_at = Carbon::now();
      $purchase->deleted_by = Session::get('user_id');
      $purchase->update();
      if($purchase) {
         ItemAverageDetail::where('purchase_id',$request->purchase_id)
                           ->where('type','PURCHASE')
                           ->delete();         
         $desc = PurchaseDescription::where('purchase_id',$request->purchase_id)
                              ->get();
         foreach ($desc as $key => $value) {
            CommonHelper::RewriteItemAverageByItem($purchase->date,$value->goods_discription,$purchase->series_no);
         }
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
         //Delete item Stock 
        ItemParameterStock::where('stock_in_id',$request->purchase_id)->where('stock_in_type','PURCHASE')->delete();
         return redirect('purchase')->withSuccess('Sale deleted successfully!');
      }
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function purchaseEdit($id){
      Gate::authorize('action-module',57);
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
      Gate::authorize('action-module',57);
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
      $last_date = $purchase->date;
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
         $desc_item_arr = PurchaseDescription::where('purchase_id',$purchase->id)->pluck('goods_discription')->toArray();
         PurchaseDescription::where('purchase_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',2)->delete();
         ItemAverageDetail::where('purchase_id',$purchase->id)
                           ->where('type','PURCHASE')
                           ->delete(); 
         foreach ($goods_discriptions as $key => $good) {
            if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }

            $desc = new PurchaseDescription;
            $desc->purchase_id = $purchase->id;
            $desc->company_id = Session::get('user_company_id');
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
            $item_ledger->series_no = $request->input('series_no');
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
            $sundry->company_id = Session::get('user_company_id');
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
               $ledger->series_no = $request->input('series_no');
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
         //Item Average Calculation Logic Start Here   
         $goods_discriptions = $request->input('goods_discription');
         $qtys = $request->input('qty');
         $prices = $request->input('price');
         $amounts = $request->input('amount');
         $update_item__arr = [];$item_average_arr = [];$item_average_total = 0;
         foreach ($goods_discriptions as $key => $good) {
            if($good=="" || $qtys[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
               continue;
            }
            array_push($item_average_arr,array("item"=>$good,"quantity"=>$qtys[$key],"price"=>$prices[$key],"amount"=>$amounts[$key]));
            array_push($update_item__arr,$good);
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
            if(!empty($value['quantity']) && $value['quantity']!=0){
               $average_price = $average_amount/$value['quantity'];
               $average_price =  round($average_price,6);
            }else{
               $average_price = 0;
            }
            
            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $request->date;
            $average_detail->item_id = $value['item'];
            $average_detail->series_no = $request->input('series_no');
            $average_detail->type = 'PURCHASE';
            $average_detail->purchase_id = $purchase->id;
            $average_detail->purchase_weight = $value['quantity'];
            $average_detail->purchase_amount = $value['amount'];
            $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
            $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
            $average_detail->purchase_total_amount = $average_amount;
            $average_detail->company_id = Session::get('user_company_id');
            $average_detail->created_at = Carbon::now();
            $average_detail->save();
            $lower_date = (strtotime($last_date) < strtotime($request->date)) ? $last_date : $request->date;
            CommonHelper::RewriteItemAverageByItem($lower_date,$value['item'],$request->input('series_no'));

         }
         foreach ($desc_item_arr as $key => $value) {
            if(!in_array($value, $update_item__arr)){
               CommonHelper::RewriteItemAverageByItem($last_date,$value,$request->input('series_no'));
            }
         }
         
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->credit = $request->input('total');
         $ledger->series_no = $request->input('series_no');
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
         $ledger->series_no = $request->input('series_no');
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
             session(['previous_url_purchaseEdit' => URL::previous()]);
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
       ini_set('max_execution_time', 600);
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
                $data = array_map('trim', $data);
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
             $data = array_map('trim', $data);
            if($data[0]!="" && $data[2]!=""){
               if($series_no!=""){
                  array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"error_arr"=>$error_arr));
               }               
               $item_arr = [];
               $error_arr = [];
               $slicedData = [];
               $series_no = trim($data[0]);
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
               $date = str_replace("/","-",$date);
               $date = date('Y-m-d',strtotime($date));
               if(strtotime($from_date)>strtotime(date('Y-m-d',strtotime($date))) || strtotime($to_date)<strtotime(date('Y-m-d',strtotime($date)))){                  
                  array_push($error_arr, 'Date '.$date.' not in Financial Year - Invoice No. '.$voucher_no);                  
               }
              
               if(!in_array(trim($series_no), $series_arr)){
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
                        // Delete old average details for selected purchases
                            ItemAverageDetail::whereIn('purchase_id', $check_invoices)->delete();

                        // Get item IDs and corresponding purchase dates for those invoices
                        $itemKiId = PurchaseDescription::whereIn('purchase_id', $check_invoices)
                                                        ->join('purchases', 'purchases.id', '=', 'purchase_descriptions.purchase_id')
                                                         ->select('purchase_descriptions.goods_discription as item_id', 'purchases.date')
                                                             ->get();

                                        // Recalculate item averages
                                        foreach ($itemKiId as $k) {
                                            CommonHelper::RewriteItemAverageByItem($k->date, $k->item_id,$series_no);
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
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->series_no = $series_no;
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
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->purchase_amt_account;
                           $ledger->debit = str_replace(",","",$sgst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->series_no = $series_no;
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
                        $sundry->company_id = Session::get('user_company_id');
                        $sundry->status = '1';
                        $sundry->save();
                        //ADD DATA IN CGST ACCOUNT     
                        if($bill_sundrys->adjust_purchase_amt=='No'){
                           $ledger = new AccountLedger();
                           $ledger->account_id = $bill_sundrys->purchase_amt_account;
                           $ledger->debit = str_replace(",","",$igst_rate);                                    
                           $ledger->txn_date = $date;
                           $ledger->series_no = $series_no;
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
                     if (!empty($v1['amount'])) {
                         // Add item amount (after removing comma)
                         $item_taxable_amount += str_replace(",", "", $v1['amount']);
                 
                         // Fetch item with unit info
                         $item = ManageItems::join('units', 'manage_items.u_name', '=', 'units.id')
                             ->select('manage_items.id', 'manage_items.hsn_code', 'manage_items.gst_rate', 'units.s_name as unit', 'units.id as uid')
                             ->where('manage_items.name', trim($v1['item_name']))
                             ->where('manage_items.company_id', Session::get('user_company_id'))
                             ->first();
                 
                         // Save item in purchase description
                         $desc = new PurchaseDescription;
                         $desc->purchase_id = $purchase->id;
                         $desc->company_id = Session::get('user_company_id');
                         $desc->goods_discription = $item->id;
                         $desc->qty = $v1['item_weight'];
                         $desc->unit = $item->uid;
                         $desc->price = $v1['price'];
                         $desc->amount = str_replace(",", "", $v1['amount']);
                         $desc->status = '1';
                         $desc->save();
                 
                         // Save item in item ledger
                         $item_ledger = new ItemLedger();
                         $item_ledger->item_id = $item->id;
                         $item_ledger->series_no = $series_no;
                         $item_ledger->in_weight = $v1['item_weight'];
                         $item_ledger->txn_date = $date;
                         $item_ledger->price = $v1['price'];
                         $item_ledger->total_price = str_replace(",", "", $v1['amount']);
                         $item_ledger->company_id = Session::get('user_company_id');
                         $item_ledger->source = 2;
                         $item_ledger->source_id = $purchase->id;
                         $item_ledger->created_by = Session::get('user_id');
                         $item_ledger->created_at = date('Y-m-d H:i:s');
                         $item_ledger->save();
                     }
                 }
                 
                 // Code for average costing
                 $update_item_arr = [];
                 $item_average_arr = [];
                 $item_average_total = 0;
                 
                 foreach ($item_arr as $k1 => $v1) {
                     if (!empty($v1['amount'])) {
                         $item = ManageItems::join('units', 'manage_items.u_name', '=', 'units.id')
                             ->select('manage_items.id', 'manage_items.hsn_code', 'manage_items.gst_rate', 'units.s_name as unit', 'units.id as uid')
                             ->where('manage_items.name', trim($v1['item_name']))
                             ->where('manage_items.company_id', Session::get('user_company_id'))
                             ->first();
                 
                         if (!$item || $v1['item_weight'] == "" || $v1['price'] == "" || $v1['amount'] == "") {
                             continue;
                         }
                 
                         $amount = str_replace(",", "", $v1['amount']);
                         $item_average_arr[] = [
                             "item" => $item->id,
                             "quantity" => $v1['item_weight'],
                             "price" => $v1['price'],
                             "amount" => $amount
                         ];
                         $update_item_arr[] = $item->id;
                         $item_average_total += $amount;
                     }
                 }
                 
                 // Handle bill sundry (paired as name, value)
                 $additive_sundry_amount_first = 0;
                 $subtractive_sundry_amount_first = 0;
                 $bill_sundry_ids = [];
                 $bill_sundry_amounts = [];
                 
                 foreach ($slicedData as $k2 => $v2) {
                     $v2 = trim($v2);
                     if ($v2 !== "" && $v2 !== '0') {
                         if ($k2 % 2 == 0) {
                             // Even index: Bill Sundry Name
                             $bill = BillSundrys::where('delete', '0')
                                 ->where('status', '1')
                                 ->where('name', $v2)
                                 ->whereIn('company_id', [Session::get('user_company_id'), 0])
                                 ->first();
                             $bill_sundry_ids[] = $bill ? $bill->id : null;
                         } else {
                             // Odd index: Bill Sundry Amount
                             $bill_sundry_amounts[] = str_replace(",", "", $v2);
                         }
                     }else{
                        if ($k2 % 2 != 0) {
                           $bill_sundry_amounts[] = 0;
                        }
                     }
                 }
                 
                 // Match bill sundry amounts with their types
                 
                 foreach ($bill_sundry_ids as $i => $bill_id) {
                     if ($bill_id === null || !isset($bill_sundry_amounts[$i])) continue;
                 
                     $billsundry = BillSundrys::find($bill_id);
                     $amount = $bill_sundry_amounts[$i];                 
                     if ($billsundry && $billsundry->nature_of_sundry == "OTHER") {
                        //print_r($bill_id."**".$amount);
                         if ($billsundry->bill_sundry_type == "additive") {
                             $additive_sundry_amount_first += $amount;
                         } elseif ($billsundry->bill_sundry_type == "subtractive") {
                             $subtractive_sundry_amount_first += $amount;
                         }
                     }
                 }
                 
                 // Distribute sundry amount to items proportionally
                 foreach ($item_average_arr as $value) {
                     $subtractive_sundry_amount = 0;
                     $additive_sundry_amount = 0;
                 
                     if ($additive_sundry_amount_first > 0) {
                         $additive_sundry_amount = ($value['amount'] / $item_average_total) * $additive_sundry_amount_first;
                     }
                 
                     if ($subtractive_sundry_amount_first > 0) {
                         $subtractive_sundry_amount = ($value['amount'] / $item_average_total) * $subtractive_sundry_amount_first;
                     }
                 
                     $additive_sundry_amount = round($additive_sundry_amount, 2);
                     $subtractive_sundry_amount = round($subtractive_sundry_amount, 2);
                     $average_amount = $value['amount'] + $additive_sundry_amount - $subtractive_sundry_amount;
                     $average_amount = round($average_amount, 2);
                     $average_price = round($average_amount / $value['quantity'], 6);
                 
                     // Save to average detail
                     $average_detail = new ItemAverageDetail;
                     $average_detail->entry_date = $date;
                     $average_detail->item_id = $value['item'];
                     $average_detail->series_no = $series_no;
                     $average_detail->type = 'PURCHASE';
                     $average_detail->purchase_id = $purchase->id;
                     $average_detail->purchase_weight = $value['quantity'];
                     $average_detail->purchase_amount = $value['amount'];
                     $average_detail->purchase_bill_sundry_additive_amount = $additive_sundry_amount;
                     $average_detail->purchase_bill_sundry_subtractive_amount = $subtractive_sundry_amount;
                     $average_detail->purchase_total_amount = $average_amount;
                     $average_detail->company_id = Session::get('user_company_id');
                     $average_detail->created_at = Carbon::now();
                     $average_detail->save();
                 
                     // Update average rate
                     CommonHelper::RewriteItemAverageByItem($date, $value['item'],$series_no);
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
                           $sundry->company_id = Session::get('user_company_id');
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
                              $ledger->series_no = $series_no;
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
                  $ledger->series_no = $series_no;
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
                  $ledger->series_no = $series_no;
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
