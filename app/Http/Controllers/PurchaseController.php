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
use App\Models\SparePart;
use App\Models\SparePartItem;
use App\Models\ItemAverageDetail;
use App\Models\PurchaseParameterInfo;
use App\Models\ItemParameterStock;
use App\Models\SupplierPurchaseVehicleDetail;
use App\Models\SaleOrderSetting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Cache;
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
    $from_date = null;
    $to_date = null;
    if (!empty($input['from_date']) && !empty($input['to_date'])) {
        $from_date = date('d-m-Y', strtotime($input['from_date']));
        $to_date = date('d-m-Y', strtotime($input['to_date']));
        session(['purchase_from_date' => $from_date, 'purchase_to_date' => $to_date]);
    } elseif (session()->has('purchase_from_date') && session()->has('purchase_to_date')) {
        $from_date = session('purchase_from_date');
        $to_date = session('purchase_to_date');
    }
    Session::put('redirect_url', '');
    // Financial year setup
    $financial_year = Session::get('default_fy');
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to = DateTime::createFromFormat('y', $y[1])->format('Y');
    $month_arr = [
        $from . '-04', $from . '-05', $from . '-06', $from . '-07', $from . '-08', $from . '-09',
        $from . '-10', $from . '-11', $from . '-12', $to . '-01', $to . '-02', $to . '-03'
    ];

    // Base query
    $query = Purchase::with([
        'purchaseDescription' => function ($query) {
            $query->with([
                'item:id,name',
                'units:id,name',
                'parameterColumnInfo' => function ($q2) {
                    $q2->leftJoin('item_paremeter_list as param1', 'purchase_parameter_info.parameter1_id', '=', 'param1.id');
                    $q2->leftJoin('item_paremeter_list as param2', 'purchase_parameter_info.parameter2_id', '=', 'param2.id');
                    $q2->leftJoin('item_paremeter_list as param3', 'purchase_parameter_info.parameter3_id', '=', 'param3.id');
                    $q2->leftJoin('item_paremeter_list as param4', 'purchase_parameter_info.parameter4_id', '=', 'param4.id');
                    $q2->leftJoin('item_paremeter_list as param5', 'purchase_parameter_info.parameter5_id', '=', 'param5.id');
                    $q2->select(
                        'purchase_parameter_info.id',
                        'purchase_desc_row_id',
                        'parameter1_id', 'parameter2_id', 'parameter3_id', 'parameter4_id', 'parameter5_id',
                        'parameter1_value', 'parameter2_value', 'parameter3_value', 'parameter4_value', 'parameter5_value',
                        'param1.paremeter_name as paremeter_name1',
                        'param2.paremeter_name as paremeter_name2',
                        'param3.paremeter_name as paremeter_name3',
                        'param4.paremeter_name as paremeter_name4',
                        'param5.paremeter_name as paremeter_name5'
                    );
                }
            ]);
            $query->select('id', 'goods_discription', 'qty', 'purchase_id', 'unit');
        },
        'account:id,account_name'
    ])
    ->select([
        'id',
        'date',
        'voucher_no',
        'total',
        'party',
        'approved_status',
        'approved_by',
        'approved_at',
        'created_by',
        DB::raw("(SELECT name FROM users WHERE users.id = purchases.approved_by LIMIT 1) as approved_by_name"),
        DB::raw("(SELECT name FROM users WHERE users.id = purchases.created_by LIMIT 1) as created_by_name"),
        DB::raw("(SELECT voucher_no 
                      FROM supplier_purchase_vehicle_details 
                      WHERE map_purchase_id = purchases.id 
                      LIMIT 1) AS vehicle_voucher_no"),
        
    ])
        ->where('company_id', Session::get('user_company_id'))
        ->where('delete', '0');

    if ($from_date && $to_date) {
        $query->whereBetween(DB::raw("STR_TO_DATE(purchases.date, '%Y-%m-%d')"), [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy('purchases.date', 'ASC')
              ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'ASC');
    } else {
        //  Fetch latest 10
        $query->orderBy('purchases.date', 'DESC')
              ->orderBy(DB::raw("CAST(voucher_no AS SIGNED)"), 'DESC')
              ->limit(10);
    }

    // Get data
    $purchase = $query->get();

    // ⬆️ Reverse only when showing the latest 10 (no date filter)
    if (!$from_date && !$to_date) {
        $purchase = $purchase->reverse()->values();
    }

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
    public function create(Request $request){
      
      $rowId     = $request->query('row_id');     // 2
      $accountId = $request->query('account_id'); // 332
      $groupId   = $request->query('group_id');   // 1
      $vehicleEntryDate = null;
      if (!empty($rowId)) {
         $vehicleEntry = SupplierPurchaseVehicleDetail::find($rowId);
         if ($vehicleEntry && !empty($vehicleEntry->entry_date)) {
            $vehicleEntryDate = $vehicleEntry->entry_date; // Y-m-d
         }
      }
      $in_quantity   = $request->query('quantity');   // 1
      $in_price   = $request->query('price');   // 1
      $itemsJson = $request->query('items');
      $spare_part_id = $request->query('spare_part_id');
      $startItems = json_decode($itemsJson, true);
      $invoice_no     = $request->query('invoice_no');
      $invoice_date   = $request->query('invoice_date');
      $bill_sundry_id  = request('bill_sundry_id');
      $freight_amount = $request->query('freight_amount');
      $eway_bill_no   = $request->query('eway_bill_no');
      $vehicle_no = $request->query('vehicle_no', '');
      $transport  = $request->query('transport', '');

      Gate::authorize('action-module',83);
      //Account List
       //Account List
      $top_groups = [3, 11, 7, 8];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }
      // Remove duplicates just in case
      $group_ids = array_unique($all_groups);
      $allowed_group_ids = array_unique($all_groups);
      $no_gst_groups = [7, 8];

      $no_gst_all_groups = [];
      foreach ($no_gst_groups as $gid) {
         $no_gst_all_groups[] = $gid;

         $no_gst_all_groups = array_merge(
            $no_gst_all_groups,
            CommonHelper::getAllChildGroupIds($gid, Session::get('user_company_id'))
         );
      }

      $no_gst_group_ids = array_unique($no_gst_all_groups);
      $allowedAccountGroups = DB::table('account_groups')
         ->select('id', 'name')
         ->whereIn('id', $allowed_group_ids)
         ->orderBy('name')
         ->get();
      $party_list = Accounts::leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$group_ids)
                              ->select(
    'accounts.id',
    'accounts.gstin',
    'accounts.allow_without_gst',
    'accounts.address',
    'accounts.pin_code',
    'accounts.account_name',
    'states.state_code',
    'under_group'
)
                              ->orderBy('account_name')
                              ->get();    

      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $stockEntryEnabled = (int) ($companyData->stock_entry_status ?? 0);
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
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';   
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
      $item = DB::table('manage_items')
    ->join('units', 'manage_items.u_name', '=', 'units.id')
    ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
    ->leftjoin(DB::raw('(SELECT igr.item_id, igr.gst_rate 
                     FROM item_gst_rate igr
                     WHERE igr.effective_from <= "'.$bill_date.'"
                     AND igr.effective_from = (
                         SELECT MAX(effective_from) 
                         FROM item_gst_rate 
                         WHERE item_id = igr.item_id 
                         AND effective_from <= "'.$bill_date.'"
                     )
                    ) as gst'), 'gst.item_id', '=', 'manage_items.id')
    ->where('manage_items.delete', '=', '0')
    ->where('manage_items.status', '=', '1')
    ->where('manage_items.company_id', Session::get('user_company_id'))
    ->when($groupId, function($q) use ($groupId) {
        $q->where('manage_items.g_name', $groupId);
    })
    ->orderBy('manage_items.name')
    ->select([
        'units.s_name as unit',
        'manage_items.id',
        'manage_items.u_name',
        'manage_items.gst_rate',
        'manage_items.name',
        'parameterized_stock_status',
        'config_status',
        'item_groups.id as group_id'
    ])
    ->get();

    $station = "";
      if (empty($vehicle_no) && $rowId != "") {

         $supplier = SupplierPurchaseVehicleDetail::select(
                  'vehicle_no',
                  'supplier_locations.name'
            )
            ->leftjoin(
                  'supplier_locations',
                  'supplier_purchase_vehicle_details.location',
                  '=',
                  'supplier_locations.id'
            )
            ->find($rowId);

         if ($supplier) {
            $vehicle_no = $supplier->vehicle_no;

            if (!in_array(strtolower($supplier->name), ['local'])) {
                  $station = $supplier->name;
            }
         }
      }
      $credit_days = DB::table('manage_credit_days')
    ->where('status','1')
    ->where('company_id', Session::get('user_company_id'))
    ->orderBy('days')
    ->get();
    
$state_list = DB::table('states') ->orderBy('state_code') ->get();
$com_id = Session::get('user_company_id');

// Item Groups
$itemGroups = DB::table('item_groups')
    ->where('delete','0')
    ->where('company_id', $com_id)
    ->orderBy('group_name')
    ->get();

// Units
$accountunit = DB::table('units')
    ->where('delete', '0')
    ->where('company_id', $com_id)
    ->orderBy('name')
    ->get();

// ✅ USE ALREADY MERGED GST SETTINGS
if ($companyData->gst_config_type == "single_gst") {

    $series = DB::table('gst_settings')
        ->where([
            'company_id' => Session::get('user_company_id'),
            'gst_type' => "single_gst"
        ])
        ->select('id', 'series')
        ->get();

    $branch = GstBranch::select('branch_series as series')
        ->where([
            'delete' => '0',
            'company_id' => Session::get('user_company_id'),
            'gst_setting_id' => $series[0]->id ?? null
        ])
        ->get();

    if ($branch->count()) {
        $series = $series->merge($branch);
    }

} else {

    $series = DB::table('gst_settings_multiple')
        ->select('id', 'series')
        ->where([
            'company_id' => Session::get('user_company_id'),
            'gst_type' => "multiple_gst"
        ])
        ->get();

    foreach ($series as $value) {
        $branch = GstBranch::select('branch_series as series')
            ->where([
                'delete' => '0',
                'company_id' => Session::get('user_company_id'),
                'gst_setting_multiple_id' => $value->id
            ])
            ->get();

        if ($branch->count()) {
            $series = $series->merge($branch);
        }
    }
}

      return view('addPurchase')
            ->with('fy_start_date', $fy_start_date)
            ->with('fy_end_date', $fy_end_date)
            ->with('spare_part_id',$spare_part_id)
            ->with('startItems',$startItems)
            ->with('party_list', $party_list)
            ->with('billsundry', $billsundry)
            ->with('GstSettings', $GstSettings)
            ->with('bill_date', $bill_date)
            ->with('items', $item)
            ->with('rowId', $rowId)
            ->with('accountId', $accountId)
            ->with('groupId', $groupId)
            ->with("in_quantity",$in_quantity)
            ->with("vehicle_no",$vehicle_no)
            ->with("no_gst_group_ids",$no_gst_group_ids)
            ->with("station",$station)
            ->with('transport', $transport)
            ->with('invoice_no', $invoice_no)
            ->with('invoice_date', $invoice_date)
            ->with('bill_sundry_id', $bill_sundry_id)
            ->with('freight_amount', $freight_amount)
            ->with('eway_bill_no', $eway_bill_no)
            ->with('itemGroups', $itemGroups)->with('accountunit', $accountunit)->with('series', $series)->with('state_list', $state_list)->with('allowedAccountGroups', $allowedAccountGroups)->with('credit_days', $credit_days)
            ->with('stockEntryEnabled', $stockEntryEnabled)
            ->with('vehicleEntryDate', $vehicleEntryDate)
            ->with("in_price",$in_price);
   }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
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
      // echo "<pre>";
      // print_r($request->all());
      // die;
      // if($request->input('goods_discription')[0]=="" || $request->input('qty')[0]=="" || $request->input('units')[0]=="" || $request->input('price')[0]=="" || $request->input('amount')[0]==""){
      //    return $this->failedMessage('Plases Select Item','purchase/create');
      // }
      $goods_discriptions = $request->input('goods_discription');
      $qtys = $request->input('qty');
      $units = $request->input('units');
      $prices = $request->input('price');
      $amounts = $request->input('amount');
      foreach ($goods_discriptions as $key => $good) {
         if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
            return back()
            ->withInput()
            ->with('error', 'Please fill all item fields before submitting.');
         }
      }
      
      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $stockEntryEnabled = (int) ($companyData->stock_entry_status ?? 0);
      $vehicleEntryId = $request->input('vehicle_entry_id'); 
      $rules = [
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party_id' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ];
      if ($stockEntryEnabled === 1) {
         $rules['stock_entry_date'] = 'required|date';
      }
      $validated = $request->validate($rules);
      
      $financial_year = CommonHelper::getFinancialYear($request->input('date'));
      $account = Accounts::where('id',$request->input('party_id'))->first();
      $purchase = new Purchase;
      $purchase->series_no = $request->input('series_no');
      $purchase->company_id = Session::get('user_company_id');
      $purchase->date = $request->input('date');
      $purchase->stock_entry_date = $stockEntryEnabled === 1
         ? $request->input('stock_entry_date')
         : null;
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
      $purchase->created_by = Session::get('user_id');
      $applyGST = false;
      if ($account->gstin) {
         if (!$account->gst_effective_from) {
            $applyGST = true;
         } 
         elseif ($request->input('date') >= $account->gst_effective_from) {
            $applyGST = true;
         }
      }

      $purchase->billing_gst = $applyGST ? $account->gstin : null;
      $purchase->merchant_gst =  $request->input('merchant_gst');
      $purchase->billing_state = $account->state;
      $purchase->shipping_name = $request->input('shipping_name');
      $purchase->shipping_state = $request->input('shipping_state');
      $purchase->shipping_address = $request->input('shipping_address');
      $purchase->shipping_pincode = $request->input('shipping_pincode');
      $purchase->shipping_gst = $request->input('shipping_gst');
      $purchase->shipping_pan = $request->input('shipping_pan');
      $purchase->financial_year = $financial_year;
      $purchase->narration = $request->input('narration');
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
            
            $finalStockEntryDate = null;

            if ($stockEntryEnabled === 1) {
                $finalStockEntryDate = $request->filled('stock_entry_date')
                    ? $request->input('stock_entry_date')
                    : $request->input('date');
            }

            $item_ledger = new ItemLedger();
            $item_ledger->item_id = $good;
            $item_ledger->series_no = $request->input('series_no');
            $item_ledger->in_weight = $qtys[$key];
            $item_ledger->txn_date = $finalStockEntryDate ?? $request->input('date');
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
               $ledger->financial_year = $financial_year;
               $ledger->entry_type = 2;
               $ledger->entry_type_id = $purchase->id;
               $ledger->map_account_id = $request->input('party_id');
               $ledger->created_by = Session::get('user_id');
               $ledger->created_at = date('d-m-Y H:i:s');
               $ledger->save();
               $roundoff = $roundoff - $bill_sundry_amounts[$key];
            }
         }
         
         $purchasePostingAmount = 0;

            // 1️⃣ Add all item amounts
            foreach ($amounts as $key => $amt) {
                if ($amt != "") {
                    $purchasePostingAmount += (float)$amt;
                }
            }
            
            // 2️⃣ Adjust only bill sundry where adjust_purchase_amt = Yes
            foreach ($bill_sundrys as $key => $bill) {
            
                if ($bill_sundry_amounts[$key] == "" || $bill == "") {
                    continue;
                }
            
                $billsundry = BillSundrys::where('id', $bill)->first();
            
                if ($billsundry && $billsundry->adjust_purchase_amt == 'Yes') {
            
                    if ($billsundry->bill_sundry_type == 'additive') {
                        $purchasePostingAmount += (float)$bill_sundry_amounts[$key];
                    }
            
                    if ($billsundry->bill_sundry_type == 'subtractive') {
                        $purchasePostingAmount -= (float)$bill_sundry_amounts[$key];
                    }
                }
            }
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party_id');
         $ledger->series_no = $request->input('series_no');
         $ledger->credit = $request->input('total');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
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
         $ledger->debit = $purchasePostingAmount;
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
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
            
            $finalStockEntryDate = null;

            if ($stockEntryEnabled === 1) {
               $finalStockEntryDate = $request->filled('stock_entry_date')
                  ? $request->input('stock_entry_date')
                  : $request->input('date');
            }

            //Add Data In Average Details table
            $average_detail = new ItemAverageDetail;
            $average_detail->entry_date = $finalStockEntryDate ?? $request->input('date');
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
            CommonHelper::RewriteItemAverageByItem($finalStockEntryDate ?? $request->input('date'),$value['item'],$request->input('series_no'));
         }
         //Update Vehicle Entry Row
         
            // if(isset($request->spare_part_id)){
            //       $spare = SparePart::find($request->spare_part_id);
            //       $spare->status=3;
            //       $spare->save();
            //       $purchase = Purchase::find($purchase->id);
            //       $purchase->spare_part_id = $request->spare_part_id;
            //       $purchase->save();
            // }
             if ($request->filled('spare_part_id')) {

            DB::transaction(function () use ($request, $purchase) {
        
                $spare = SparePart::with('items')
                    //->lockForUpdate()
                    ->findOrFail($request->spare_part_id);
        
                // ✅ FIX 1: Root ID must ALWAYS point to ORIGINAL
                $rootId = $spare->root_spare_part_id ?: $spare->id;
        
                // Ensure original has correct root + sequence
                if (!$spare->root_spare_part_id) {
                    $spare->root_spare_part_id = $rootId;
                    $spare->order_sequence = 1;
                    $spare->save();
                }
        
                $closePurchase = (int) $request->close_purchase === 1;
                $remainingItems = [];
        
                foreach ($spare->items as $index => $item) {
        
                    $orderedQty = (float) $item->quantity;
                    $gotQty     = isset($request->qty[$index])
                        ? (float) $request->qty[$index]
                        : 0;
        
                    // FULL PURCHASE
                    if ($closePurchase || $gotQty >= $orderedQty) {
                        $item->quantity = $orderedQty;
                        $item->save();
                        continue;
                    }
        
                    // PARTIAL
                    $remainingQty = $orderedQty - $gotQty;
        
                    // Update purchased qty
                    $item->quantity = $gotQty;
                    $item->save();
        
                    $remainingItems[] = [
                        'item_id'    => $item->item_id,
                        'quantity'   => $remainingQty,
                        'price'      => $item->price,
                        'unit'       => $item->unit,
                        'company_id' => $item->company_id,
                    ];
                }
                  if (!empty($vehicleEntryId)) {
                     $spare->map_vehicle_entry_id = $vehicleEntryId;
                  }
                // Mark CURRENT spare part completed
                $spare->status = 3;
                $spare->save();
        
                // 🔴 If closed OR nothing remaining → STOP
                if ($closePurchase || empty($remainingItems)) {
                    $purchase->spare_part_id = $spare->id;
                    $purchase->save();
                    return;
                }
        
                // ✅ FIX 2: Correct order_sequence under SAME ROOT
                $nextSequence = SparePart::where('root_spare_part_id', $rootId)
                    ->max('order_sequence');
        
                $nextSequence = $nextSequence ? $nextSequence + 1 : 2;
        
                // ✅ FIX 3: New spare part MUST use SAME root
                $rootSpare = SparePart::find($rootId);

                $basePo = $rootSpare->po_number;
                
                $basePo = preg_replace('/-\d+$/', '', $basePo);
                
                $newPoNumber = $basePo . '-' . ($nextSequence - 1);
                
                $newSpare = SparePart::create([
                    'root_spare_part_id' => $rootId,
                    'order_sequence'     => $nextSequence,
                
                    'po_number'          => $newPoNumber,
                    'po_date'            => $spare->po_date,
                
                    'bill_to_account_id' => $spare->bill_to_account_id,
                    'bill_to_company_id' => $spare->bill_to_company_id,
                
                    'ship_to_account_id' => $spare->ship_to_account_id,
                    'ship_to_company_id' => $spare->ship_to_company_id,
                
                    'po_narration'       => $spare->po_narration,
                    'freight'            => $spare->freight,
                
                    'account_id'         => $spare->account_id,
                    'source'             => $spare->source,
                    'status'             => 2,
                    'company_id'         => $spare->company_id,
                    'created_by'         => auth()->id(),
                ]);
        
                foreach ($remainingItems as $row) {
                    SparePartItem::create([
                        'spare_part_id' => $newSpare->id,
                        'item_id'       => $row['item_id'],
                        'quantity'      => $row['quantity'],
                        'price'         => $row['price'],
                        'unit'          => $row['unit'],
                        'status'        => 2,
                        'company_id'    => $row['company_id'],
                    ]);
                }
        
                // Link purchase to ORIGINAL spare part
                $purchase->spare_part_id = $spare->id;
                $purchase->save();
            });
        }
         //Item Average Calculation Logic End Here
         session(['previous_url_purchase' => URL::previous()]);
         if(isset($request->rowId) && !empty($request->rowId)){
            $supp = SupplierPurchaseVehicleDetail::find($request->rowId);
            SupplierPurchaseVehicleDetail::where('id',$request->rowId)->update(['map_purchase_id'=>$purchase->id]);
            $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->where('item_id', $supp->group_id)
                            ->select('group_type')
                            ->first();
            if($group_list->group_type=="BOILER FUEL"){
                return redirect('boiler-fuel?status=0&id='.$request->rowId);
            }else if($group_list->group_type=="WASTE KRAFT"){
                return redirect('waste-kraft?status=0&id='.$request->rowId);
            }
         }
         if (!empty($vehicleEntryId)) {
            SupplierPurchaseVehicleDetail::where('id', $vehicleEntryId)
               ->update([
                     'map_purchase_id' => $purchase->id,
                     'status' => 3
               ]);
         }
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
      $party_detail = Accounts::leftjoin('states','accounts.state','=','states.id')
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
      $deleteSnapshot = [
         'purchase' => $purchase ? $purchase->toArray() : null,
     
         'items' => PurchaseDescription::where('purchase_id', $purchase->id)
             ->get()->toArray(),
     
         'sundries' => PurchaseSundry::where('purchase_id', $purchase->id)
             ->get()->toArray(),
     
         'parameters' => PurchaseParameterInfo::where('purchase_id', $purchase->id)
             ->get()->toArray(),
     
         'item_parameter_stock' => ItemParameterStock::where('stock_in_id', $purchase->id)
             ->where('stock_in_type', 'PURCHASE')
             ->get()->toArray(),
     
         'item_ledgers' => ItemLedger::where('source', 2)
             ->where('source_id', $purchase->id)
             ->get()->toArray(),
     
         'account_ledgers' => AccountLedger::where('entry_type', 2)
             ->where('entry_type_id', $purchase->id)
             ->get()->toArray(),
     
         'item_average_details' => ItemAverageDetail::where('purchase_id', $purchase->id)
             ->where('type', 'PURCHASE')
             ->get()->toArray(),
     ];
      $purchase->delete = '1';
      $purchase->deleted_at = Carbon::now();
      $purchase->deleted_by = Session::get('user_id');
      $purchase->update();
      if($purchase) {
         $supplier = SupplierPurchaseVehicleDetail::where('map_purchase_id',$request->purchase_id)->first();
         if($supplier){
            SupplierPurchaseVehicleDetail::where('id',$supplier->id)->update(['status'=>1,"map_purchase_id"=>null,"reapproval"=>0]);
         }
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
        $sparePartId = $purchase->spare_part_id;
        if ($sparePartId) {
            $purchaseItems = DB::table('purchase_descriptions')
               ->where('purchase_id', $purchase->id)
               ->where('delete', '0') 
               ->get();
            foreach ($purchaseItems as $item) {
               DB::table('spare_part_items')
                     ->where('spare_part_id', $sparePartId)
                     ->where('item_id', $item->goods_discription) 
                     ->decrement('quantity', $item->qty);         
            }
            DB::table('spare_parts')
               ->where('id', $sparePartId)
               ->update([
                     'status' => 2
               ]);
            DB::table('purchases')
               ->where('id', $purchase->id)
               ->update([
                     'spare_part_id' => null
               ]);
        }
        ActivityLog::create([
         'module_type' => 'purchase',
         'module_id'   => $purchase->id,
         'action'      => 'delete',
         'old_data'    => $deleteSnapshot,
         'new_data'    => null,
         'action_by'   => Session::get('user_id'),
         'company_id'  => Session::get('user_company_id'),
         'action_at'   => now(),
      ]);
         return redirect('purchase')->withSuccess('Purchase deleted successfully!');
      }
   }
   public function failedMessage($msg,$url){
      return redirect($url)->withError($msg);
   }
   public function purchaseEdit(Request $request,$id){
      $groupId = "";
      Gate::authorize('action-module',57);
      $rowId     = $request->query('row_id'); 
      $purchase = Purchase::where('id',$id)->first();
      $party = Accounts::find($purchase->party);

      $gstApplicable = true;

      if($party && $party->gst_effective_from!='' && $party->gstin==""){
        
         if($purchase->date < $party->gst_effective_from){
            $gstApplicable = false;
         }
      }else if($party->gstin==""){
         $gstApplicable = false;
      }
      
      $PurchaseDescription = PurchaseDescription::with(['parameterColumnInfo'=>function($q){
                              $q->leftjoin('item_paremeter_list as p1', 'purchase_parameter_info.parameter1_id', '=', 'p1.id')
                                 ->leftjoin('item_paremeter_list as p2', 'purchase_parameter_info.parameter2_id', '=', 'p2.id')
                                 ->leftjoin('item_paremeter_list as p3', 'purchase_parameter_info.parameter3_id', '=', 'p3.id')
                                 ->leftjoin('item_paremeter_list as p4', 'purchase_parameter_info.parameter4_id', '=', 'p4.id')
                                 ->leftjoin('item_paremeter_list as p5', 'purchase_parameter_info.parameter5_id', '=', 'p5.id')
                                 ->select([
                                    'purchase_parameter_info.*',
                                    'p1.alternative_unit as parameter1_alternative_unit',  // adjust column alternative_unit
                                    'p2.alternative_unit as parameter2_alternative_unit',  // adjust column alternative_unit
                                    'p3.alternative_unit as parameter3_alternative_unit',  // adjust column alternative_unit
                                    'p4.alternative_unit as parameter4_alternative_unit',  // adjust column alternative_unit
                                    'p5.alternative_unit as parameter5_alternative_unit',  // adjust column alternative_unit
                                 ]);
                           }])
                           ->join('units','purchase_descriptions.unit','=','units.id')
                           ->where('purchase_id',$id)
                           ->select(['purchase_descriptions.*','units.s_name'])
                           ->get();
      // echo "<pre>";
      // print_r($PurchaseDescription->toArray());
      // die;
      $PurchaseSundry = PurchaseSundry::join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                                 ->select(['bill_sundrys.effect_gst_calculation','bill_sundrys.nature_of_sundry','purchase_sundries.*'])
                                 ->where('purchase_id',$id)
                                 ->get();
      $top_groups = [3, 11, 7, 8];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $group_ids = array_unique($all_groups);
      $allowed_group_ids = array_unique($all_groups);
       $no_gst_groups = [7, 8];

      $no_gst_all_groups = [];
      foreach ($no_gst_groups as $gid) {
         $no_gst_all_groups[] = $gid;

         $no_gst_all_groups = array_merge(
            $no_gst_all_groups,
            CommonHelper::getAllChildGroupIds($gid, Session::get('user_company_id'))
         );
      }

      $no_gst_group_ids = array_unique($no_gst_all_groups);
      $allowedAccountGroups = DB::table('account_groups')
         ->select('id', 'name')
         ->whereIn('id', $allowed_group_ids)
         ->orderBy('name')
         ->get();
      $party_list = Accounts::select('accounts.*','states.state_code')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('accounts.delete', '=', '0')
                              ->where('accounts.status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group', $group_ids)
                              ->orderBy('account_name')
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

      
      $manageitems = DB::table('manage_items')
                     ->join('units', 'manage_items.u_name', '=', 'units.id')
                     ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
                     ->leftjoin(DB::raw('(SELECT igr.item_id, igr.gst_rate 
                                       FROM item_gst_rate igr
                                       WHERE igr.effective_from <= "'.$bill_date.'"
                                       AND igr.effective_from = (
                                          SELECT MAX(effective_from) 
                                          FROM item_gst_rate 
                                          WHERE item_id = igr.item_id 
                                          AND effective_from <= "'.$bill_date.'"
                                       )
                                    ) as gst'), 'gst.item_id', '=', 'manage_items.id')
                     ->where('manage_items.delete', '=', '0')
                     ->where('manage_items.status', '=', '1')
                     ->where('manage_items.company_id', Session::get('user_company_id'))
                     ->when($groupId, function($q) use ($groupId) {
                        $q->where('manage_items.g_name', $groupId);
                     })
                     ->orderBy('manage_items.name')
                     ->select([
                        'units.s_name as unit',
                        'manage_items.id',
                        'manage_items.u_name',
                        'manage_items.gst_rate',
                        'manage_items.name',
                        'parameterized_stock_status',
                        'config_status',
                        'item_groups.id as group_id'
                     ])
                     ->get();

            


      $companyData = Companies::where('id', Session::get('user_company_id'))->first();
      $stockEntryEnabled = (int) ($companyData->stock_entry_status ?? 0);
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
      
      $billsundry = BillSundrys::where('delete', '=', '0')
                                 ->where('status', '=', '1')
                                 ->whereIn('company_id',[Session::get('user_company_id'),0])
                                 //->OrwhereIn('id',[1,2,3,8,9])
                                 ->orderBy('name')
                                 ->get();
      //Parameter data
      $stock_status = 1;
      $check_stock = ItemParameterStock::where('stock_in_id',$purchase->id)
                                          ->where('stock_in_type',"PURCHASE")
                                          ->where('status',"0")
                                          ->first();
      if($check_stock){
         $stock_status = 0;
      }
      $financial_year = Session::get('default_fy');
      [$startYY, $endYY] = explode('-', $financial_year);

      $fy_start_date = '20' . $startYY . '-04-01'; 
      $fy_end_date   = '20' . $endYY   . '-03-31';   
      $credit_days = DB::table('manage_credit_days')
            ->where('status','1')
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('days')
            ->get();
    
            $state_list = DB::table('states') ->orderBy('state_code') ->get();
            $com_id = Session::get('user_company_id');

      // Item Groups
      $itemGroups = DB::table('item_groups')
         ->where('delete','0')
         ->where('company_id', $com_id)
         ->orderBy('group_name')
         ->get();

      // Units
      $accountunit = DB::table('units')
         ->where('delete', '0')
         ->where('company_id', $com_id)
         ->orderBy('name')
         ->get();

      // ✅ USE ALREADY MERGED GST SETTINGS
      if ($companyData->gst_config_type == "single_gst") {

         $series = DB::table('gst_settings')
            ->where([
                  'company_id' => Session::get('user_company_id'),
                  'gst_type' => "single_gst"
            ])
            ->select('id', 'series')
            ->get();

         $branch = GstBranch::select('branch_series as series')
            ->where([
                  'delete' => '0',
                  'company_id' => Session::get('user_company_id'),
                  'gst_setting_id' => $series[0]->id ?? null
            ])
            ->get();

         if ($branch->count()) {
            $series = $series->merge($branch);
         }

      } else {

         $series = DB::table('gst_settings_multiple')
            ->select('id', 'series')
            ->where([
                  'company_id' => Session::get('user_company_id'),
                  'gst_type' => "multiple_gst"
            ])
            ->get();

         foreach ($series as $value) {
            $branch = GstBranch::select('branch_series as series')
                  ->where([
                     'delete' => '0',
                     'company_id' => Session::get('user_company_id'),
                     'gst_setting_multiple_id' => $value->id
                  ])
                  ->get();

            if ($branch->count()) {
                  $series = $series->merge($branch);
            }
         }
      }
      
      return view('editPurchase')
               ->with('fy_start_date', $fy_start_date)
               ->with('fy_end_date', $fy_end_date)
               ->with('party_list', $party_list)
               ->with('manageitems', $manageitems)
               ->with('billsundry', $billsundry)
               ->with('mat_center', $mat_center)
               ->with('GstSettings', $GstSettings)
               ->with('mat_series', $mat_series)
               ->with('purchase', $purchase)
               ->with('PurchaseDescription', $PurchaseDescription)
               ->with('PurchaseSundry', $PurchaseSundry)
               ->with("stock_status",$stock_status)
               ->with('rowId',$rowId)
               ->with('groupId',$groupId)
               ->with('itemGroups', $itemGroups)
               ->with('accountunit', $accountunit)
               ->with('series', $series)
               ->with('no_gst_group_ids',$no_gst_group_ids)
               ->with('state_list', $state_list)
               ->with('allowedAccountGroups', $allowedAccountGroups)
               ->with('stock_entry_date', $purchase->stock_entry_date)
               ->with('stockEntryEnabled', $stockEntryEnabled)
               ->with('gstApplicable', $gstApplicable)
               ->with('credit_days', $credit_days);
   }
   public function update(Request $request){
      // echo "<pre>";
      // print_r($request->all());
      // die;
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
      $rules = [
         'series_no' => 'required',
         'date' => 'required',
         'voucher_no' => 'required',
         'party' => 'required',
         'material_center' => 'required',
         'total' => 'required',
         'goods_discription' => 'required|array|min:1',
      ];
      $company = Companies::where('id', Session::get('user_company_id'))->first();
      if (($company->stock_entry_status ?? 0) == 1) {
         $rules['stock_entry_date'] = 'required|date';
      }
        
       $financial_year = CommonHelper::getFinancialYear($request->input('date'));
        // ✅ Decide effective stock date
      $voucherDate = $request->input('date');
      $stockEntryDate = ($company->stock_entry_status ?? 0) == 1
         ? $request->input('stock_entry_date')
         : null;

      // For item ledger → stock entry date preferred
      $itemLedgerDate = $stockEntryDate ?? $voucherDate;
      // For average rewrite → earlier of the two
      $averageRewriteDate = $stockEntryDate
         ? (strtotime($stockEntryDate) < strtotime($voucherDate) ? $stockEntryDate : $voucherDate)
         : $voucherDate;
      $validated = $request->validate($rules);
      $account = Accounts::where('id',$request->input('party'))->first();
      $purchase = Purchase::find($request->input('purchase_edit_id'));
      $oldSnapshot = [
         'purchase' => $purchase->toArray(),

         'items' => PurchaseDescription::where('purchase_id', $purchase->id)->get()->toArray(),

         'sundries' => PurchaseSundry::where('purchase_id', $purchase->id)->get()->toArray(),

         'parameters' => PurchaseParameterInfo::where('purchase_id', $purchase->id)->get()->toArray(),

         'item_parameter_stock' => ItemParameterStock::where('stock_in_id', $purchase->id)
            ->where('stock_in_type', 'PURCHASE')
            ->get()->toArray(),

         'item_ledgers' => ItemLedger::where('source', 2)
            ->where('source_id', $purchase->id)
            ->get()->toArray(),

         'account_ledgers' => AccountLedger::where('entry_type', 2)
            ->where('entry_type_id', $purchase->id)
            ->get()->toArray(),

         'item_average_details' => ItemAverageDetail::where('purchase_id', $purchase->id)
            ->where('type', 'PURCHASE')
            ->get()->toArray(),
      ];
     $last_date = ($company->stock_entry_status ?? 0) == 1 && $purchase->stock_entry_date
    ? $purchase->stock_entry_date
    : $purchase->date;
      $goods_discriptions = $request->input('goods_discription');
      $qtys = $request->input('qty');
      $units = $request->input('units');
      $prices = $request->input('price');
      $amounts = $request->input('amount');
      foreach ($goods_discriptions as $key => $good) {
         if($good=="" || $qtys[$key]=="" || $units[$key]=="" || $prices[$key]=="" || $amounts[$key]==""){
            return back()
            ->withInput()
            ->with('error', 'Please fill all item fields before submitting.');
         }
      }      
    //echo $averageRewriteDate."--".$last_date;die;
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
      if (($company->stock_entry_status ?? 0) == 1) {
         $purchase->stock_entry_date = $request->input('stock_entry_date');
      } 
      $purchase->billing_name = $account->account_name;
      $purchase->billing_address = $account->address;
      $purchase->billing_pincode = $account->pin_code;
      $applyGST = false;
      if ($account->gstin) {

         if (!$account->gst_effective_from) {
            $applyGST = true;
         } 
         elseif ($request->input('date') >= $account->gst_effective_from) {
            $applyGST = true;
         }
      }
      $purchase->billing_gst = $applyGST ? $account->gstin : null;
      $purchase->billing_state = $account->state;
      $purchase->shipping_name = $request->input('shipping_name');
      $purchase->shipping_state = $request->input('shipping_state');
      $purchase->shipping_address = $request->input('shipping_address');
      $purchase->shipping_pincode = $request->input('shipping_pincode');
      $purchase->shipping_gst = $request->input('shipping_gst');
      $purchase->shipping_pan = $request->input('shipping_pan');
      $purchase->narration = $request->input('narration');
      $purchase->updated_by = Session::get('user_id');
      $purchase->save();
      if($purchase->id){         
         $config_status = $request->input('config_status');
         $item_parameters = $request->input('item_parameters');
         $desc_item_arr = PurchaseDescription::where('purchase_id',$purchase->id)->pluck('goods_discription')->toArray();
         PurchaseDescription::where('purchase_id',$purchase->id)->delete();
         ItemLedger::where('source_id',$purchase->id)->where('source',2)->delete();
         ItemAverageDetail::where('purchase_id',$purchase->id)
                           ->where('type','PURCHASE')
                           ->delete(); 
         PurchaseParameterInfo::where('purchase_id',$purchase->id)->delete();
         ItemParameterStock::where('stock_in_id',$purchase->id)->where('stock_in_type',"PURCHASE")->delete();
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
           $item_ledger->txn_date = $itemLedgerDate;
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
               $ledger->financial_year = $financial_year;
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
         $update_item__arr = [];$item_average_arr = [];$item_average_total = 0;$purchase_amt = 0;
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
            $average_detail->entry_date = $itemLedgerDate;
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
            $lower_date = (strtotime($last_date) < strtotime($averageRewriteDate))
    ? $last_date
    : $averageRewriteDate;

CommonHelper::RewriteItemAverageByItem(
    $lower_date,
    $value['item'],
    $request->input('series_no')
);


         }
         foreach ($desc_item_arr as $key => $value) {
            if(!in_array($value, $update_item__arr)){
              $rewriteDate = (strtotime($last_date) < strtotime($averageRewriteDate))
    ? $last_date
    : $averageRewriteDate;

CommonHelper::RewriteItemAverageByItem(
    $rewriteDate,
    $value,
    $request->input('series_no')
);

            }
         }
         
         
         $purchasePostingAmount = 0;

// 1️⃣ Add all item amounts
foreach ($amounts as $key => $amt) {
    if ($amt != "") {
        $purchasePostingAmount += (float)$amt;
    }
}

// 2️⃣ Adjust only bill sundry where adjust_purchase_amt = Yes
foreach ($bill_sundrys as $key => $bill) {

    if ($bill_sundry_amounts[$key] == "" || $bill == "") {
        continue;
    }

    $billsundry = BillSundrys::where('id', $bill)->first();

    if ($billsundry && $billsundry->adjust_purchase_amt == 'Yes') {

        if ($billsundry->bill_sundry_type == 'additive') {
            $purchasePostingAmount += (float)$bill_sundry_amounts[$key];
        }

        if ($billsundry->bill_sundry_type == 'subtractive') {
            $purchasePostingAmount -= (float)$bill_sundry_amounts[$key];
        }
    }
}
         //ADD DATA IN Customer ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = $request->input('party');
         $ledger->credit = $request->input('total');
         $ledger->series_no = $request->input('series_no');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = 36;//Purchase
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         //ADD DATA IN Sale ACCOUNT
         $ledger = new AccountLedger();
         $ledger->account_id = 36;//Purchase
         $ledger->debit = $purchasePostingAmount;
         $ledger->series_no = $request->input('series_no');
         $ledger->txn_date = $request->input('date');
         $ledger->company_id = Session::get('user_company_id');
         $ledger->financial_year = $financial_year;
         $ledger->entry_type = 2;
         $ledger->entry_type_id = $purchase->id;
         $ledger->map_account_id = $request->input('party');
         $ledger->created_by = Session::get('user_id');
         $ledger->created_at = date('d-m-Y H:i:s');
         $ledger->save();
         $purchaseRow = Purchase::where('id', $purchase->id)->first();
         $newSnapshot = [
            'purchase' => $purchaseRow ? $purchaseRow->toArray() : null,

            'items' => PurchaseDescription::where('purchase_id', $purchase->id)
               ->get()->toArray(),

            'sundries' => PurchaseSundry::where('purchase_id', $purchase->id)
               ->get()->toArray(),

            'parameters' => PurchaseParameterInfo::where('purchase_id', $purchase->id)
               ->get()->toArray(),

            'item_parameter_stock' => ItemParameterStock::where('stock_in_id', $purchase->id)
               ->where('stock_in_type', 'PURCHASE')
               ->get()->toArray(),

            'item_ledgers' => ItemLedger::where('source', 2)
               ->where('source_id', $purchase->id)
               ->get()->toArray(),

            'account_ledgers' => AccountLedger::where('entry_type', 2)
               ->where('entry_type_id', $purchase->id)
               ->get()->toArray(),

            'item_average_details' => ItemAverageDetail::where('purchase_id', $purchase->id)
               ->where('type', 'PURCHASE')
               ->get()->toArray(),
         ];

         ActivityLog::create([
            'module_type' => 'purchase',
            'module_id'   => $purchase->id,
            'action'      => 'edit',
            'old_data'    => $oldSnapshot,
            'new_data'    => $newSnapshot,
            'action_by'   => Session::get('user_id'),
            'company_id'  => Session::get('user_company_id'),
            'action_at'   => now(),
         ]);
         $supp_vehicle_check = SupplierPurchaseVehicleDetail::where('map_purchase_id',$purchase->id)->first();
         if(isset($request->rowId) && !empty($request->rowId) || $supp_vehicle_check){
            if(isset($request->rowId) && !empty($request->rowId)){
                $supp_rowId = $request->rowId;
               $supplier = SupplierPurchaseVehicleDetail::find($request->rowId);
               if($supplier->status==2){
                  $reapproval = 0;
               }else if($supplier->status==3){
                  $reapproval = 1;
               }
            }else{
               $supplier = SupplierPurchaseVehicleDetail::where('map_purchase_id',$purchase->id)->first();
               $supp_rowId = $supplier->id;
               if($supplier->status==2){
                  $reapproval = 0;
               }else if($supplier->status==3){
                  $reapproval = 1;
               }else{
                  $reapproval = 1;
               }
            }
            
            SupplierPurchaseVehicleDetail::where('id',$supp_rowId)->update(['status'=>2,'reapproval'=>$reapproval]);
            if(isset($request->rowId) && !empty($request->rowId)){
               $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->where('item_id', $supplier->group_id)
                            ->select('group_type')
                            ->first();
               if($group_list->group_type=="BOILER FUEL"){
                  return redirect('boiler-fuel?status=2');
               }else if($group_list->group_type=="WASTE KRAFT"){
                  return redirect('waste-kraft?status=2');
               }
            }
            //return redirect('manage-purchase-info?id='.$request->rowId);
         }
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
            // if($data[2]==""){
            //    array_push($error_arr, 'Invoice No. cannot be empty - Row No. '.$index); 
            // }            
            if($data[0]!="" && $data[2]!=""){
               if($series_no!=""){
                  $akey = array_search($series_no, $series_arr);
                  $merchant_gst = $gst_no_arr[$akey];
                  array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"merchant_gst"=>$merchant_gst,"error_arr"=>$error_arr));
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
               $akey = array_search($series_no, $series_arr);
               $merchant_gst = $gst_no_arr[$akey];
               array_push($data_arr,array("series_no"=>$series_no,"date"=>$date,"voucher_no"=>$voucher_no,"party"=>$party,"material_center"=>$material_center,"grand_total"=>$grand_total,"self_vehicle"=>$self_vehicle,"vehicle_no"=>$vehicle_no,"transport_name"=>$transport_name,"reverse_charge"=>$reverse_charge,"gr_pr_no"=>$gr_pr_no,"station"=>$station,"ewaybill_no"=>$ewaybill_no,"shipping_name"=>$shipping_name,"item_arr"=>$item_arr,"slicedData"=>$slicedData,"merchant_gst"=>$merchant_gst,"error_arr"=>$error_arr));
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
            $override_average_data_arr = [];$new_average_data_arr = [];$smallestDate = null;
            foreach ($data_arr as $key => $value) {
               if(count($value['error_arr'])>0){
                  array_push($all_error_arr,$value['error_arr']);
                  $failed_invoice_count++;
                  continue;
               }
               $date = date('Y-m-d',strtotime($value['date']));
               if ($smallestDate === null || strtotime($date) < strtotime($smallestDate)) {
                  $smallestDate = $date;
               }
               $series_no = $value['series_no'];
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
               //$sale->merchant_gst = $merchant_gst;
               $ewaybill_no = $value['ewaybill_no'];
               $shipping_name = $value['shipping_name'];
               $item_arr = $value['item_arr'];
               $merchant_gst = $value['merchant_gst'];
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
                        //CommonHelper::RewriteItemAverageByItem($k->date, $k->item_id,$series_no);
                        array_push($override_average_data_arr,array("item_id"=>$k->item_id,"series"=>$series_no,"date"=>$k->date));
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
               $purchase->merchant_gst = $merchant_gst;//
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
                        if(!$item || $v1['item_weight'] == "" || $v1['price'] == "" || $v1['amount'] == "") {
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
                     //CommonHelper::RewriteItemAverageByItem($date, $value['item'],$series_no);
                     array_push($new_average_data_arr,array("item_id"=>$value['item'],"series"=>$series_no,"date"=>$date));
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
            if($duplicate_voucher_status==2){
               if(count($override_average_data_arr)>0){
                  $override_average_data_arr = array_map("unserialize", array_unique(array_map("serialize", $override_average_data_arr)));
                  foreach($override_average_data_arr as $value){
                     CommonHelper::RewriteItemAverageByItem($value['date'],$value['item_id'],$value['series']);
                  }
               }
            }
            if(count($new_average_data_arr)>0){
               $new_average_data_arr = array_map("unserialize", array_unique(array_map("serialize", $new_average_data_arr)));
               foreach($new_average_data_arr as $value){
                  CommonHelper::RewriteItemAverageByItem($value['date'],$value['item_id'],$value['series']);
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


   public function checkDuplicateVoucher(Request $request)
{
   $exists = \DB::table('purchases')
               ->leftjoin('supplier_purchase_vehicle_details','purchases.id','=','supplier_purchase_vehicle_details.map_purchase_id')
               ->select('purchases.id','supplier_purchase_vehicle_details.voucher_no')
               ->where('purchases.voucher_no', $request->voucher_no)
               ->where('party', $request->party_id)
               ->where('financial_year', $request->financial_year)
               ->where('purchases.status','1')
               ->where('purchases.delete','0')
               ->first();
   if($exists){

      return response()->json(['exists' => true,'voucher_no' => $exists->voucher_no]);
   }else{
      return response()->json(['exists' => false]);
   }
   
}

 public function checkDuplicateVoucherEdit(Request $request)
{
    $exists = \DB::table('purchases')
                ->where('voucher_no', $request->voucher_no)
                ->where('party', $request->party_id)
                ->where('id','!=',$request->purchase_id)
                ->where('financial_year', $request->financial_year)
                ->where('delete','0')
                ->exists();

    return response()->json(['exists' => $exists]);
}

public function getItemsByDate(Request $request)
{
    $bill_date = $request->bill_date;
    $groupId   = $request->group_id;

    $items = DB::table('manage_items')
        ->join('units', 'manage_items.u_name', '=', 'units.id')
        ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
        ->join(DB::raw('(SELECT igr.item_id, igr.gst_rate, MAX(igr.effective_from) as eff_date
                         FROM item_gst_rate igr
                         WHERE igr.effective_from <= "'.$bill_date.'"
                         GROUP BY igr.item_id, igr.gst_rate
                        ) as gst'), 'gst.item_id', '=', 'manage_items.id')
        ->where('manage_items.delete', '=', '0')
        ->where('manage_items.status', '=', '1')
        ->where('manage_items.company_id', Session::get('user_company_id'))
        ->when($groupId, function($q) use ($groupId) {
            $q->where('manage_items.g_name', $groupId);
        })
        ->orderBy('manage_items.name')
        ->select([
            'units.s_name as unit',
            'manage_items.id',
            'manage_items.u_name',
            'gst.gst_rate',
            'manage_items.name',
            'parameterized_stock_status',
            'config_status',
            'item_groups.id as group_id'
        ])
        ->get();

    return response()->json($items);
}
public function exportPurchasesView()
   {
      return view('purchase_export');
   }
   public function exportPurchases(Request $request)
   {
      return $this->exportPurchaseChallanCSV($request);
   }
   public function exportPurchaseBillView()
   {
      return view('purchase_bill_export');
   }
    public function exportPurchaseBill(Request $request)
   {
      $request->validate([
         'from_date'     => 'required|date',
         'to_date'       => 'required|date',
         'purchase_type' => 'required|in:LOCAL,CENTER',
      ]);

      $from         = $request->input('from_date');
      $to           = $request->input('to_date');
      $purchaseType = $request->input('purchase_type');

      $company_id = Session::get('user_company_id');

      $purchases = DB::table('purchases')
         ->leftJoin('accounts', 'purchases.party', '=', 'accounts.id')
         ->where('purchases.company_id', $company_id)
         ->whereBetween('purchases.date', [$from, $to])
         ->where(function ($q) {
               $q->where('purchases.delete', '0')
               ->orWhereNull('purchases.delete');
         })
         ->select([
               'purchases.*',
               'purchases.billing_gst',
               'purchases.merchant_gst',
               'accounts.account_name as party_name',
               'accounts.id as party_alias',
               'accounts.gstin as party_gst',
               'accounts.address as party_address',
         ])
         ->orderBy('purchases.date')
         ->get();

      $filename = "purchase_bill_{$from}_to_{$to}_" . strtolower($purchaseType) . ".csv";

      $headers = [
         "Content-Type"        => "text/csv; charset=UTF-8",
         "Content-Disposition" => "attachment; filename=\"$filename\""
      ];

      $callback = function () use ($purchases, $company_id, $purchaseType) {

         $out = fopen('php://output', 'w');
         $billSundries = DB::table('bill_sundrys')
            ->where('company_id', $company_id)
            ->where(function ($q) {
               $q->where('delete', '0')->orWhereNull('delete');
            })
            ->orderBy('sequence')
            ->get();
                  $header = [
         'Series','Date','Voucher No','Purchase Type','GST %','Party Name','Party Alias',
         'GSTIN','Address','Material Center','Narration','Item Name','Qty in KG',
         'Unit','Price','Amount'
         ];

         foreach ($billSundries as $bs) {
            $header[] = $bs->name;
         }

         $header = array_merge($header, [
         'Transport','GR','GR Date','Vehicle No','Station'
         ]);

         fputcsv($out, $header);
         $purchases = $purchases->sortByDesc(function($p){

            $rate = DB::table('purchase_descriptions')
            ->leftJoin('manage_items','purchase_descriptions.goods_discription','=','manage_items.id')
            ->where('purchase_descriptions.purchase_id',$p->id)
            ->max('manage_items.gst_rate');

            return $rate ?? 0;

         });
         foreach ($purchases as $p) {

               $billingGst  = trim((string) $p->billing_gst);
               $merchantGst = trim((string) $p->merchant_gst);

               $billingState  = strlen($billingGst)  >= 2 ? substr($billingGst, 0, 2)  : null;
               $merchantState = strlen($merchantGst) >= 2 ? substr($merchantGst, 0, 2) : null;

               if ($billingState && $merchantState) {
                  $row_type = ($billingState === $merchantState) ? 'LOCAL' : 'CENTER';
               } else {
                  $row_type = 'CENTER';
               }

               if ($row_type !== $purchaseType) {
                  continue;
               }

               $dateFormatted = date('d-m-Y', strtotime($p->date));

               $sundries = DB::table('purchase_sundries')
                  ->leftJoin('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
                  ->where('purchase_sundries.purchase_id', $p->id)
                  ->where('purchase_sundries.company_id', $company_id)
                  ->select('bill_sundrys.id as bs_id', 'bill_sundrys.name', 'purchase_sundries.amount')
                  ->get();

               $sundryValues = [];

               foreach ($billSundries as $bs) {
                  $sundryValues[$bs->id] = 0;
               }

               foreach ($sundries as $s) {
                  $sundryValues[$s->bs_id] = floatval($s->amount);
               }
               $descs = DB::table('purchase_descriptions')
                  ->where('purchase_id', $p->id)
                  ->where('company_id', $company_id)
                  ->where(function ($q) {
                     $q->where('status', '1')->orWhereNull('status');
                  })
                  ->get();

               $firstRow = true;

               if ($descs->isEmpty()) {
                  fputcsv($out, [
                     $p->series_no, $dateFormatted, $p->voucher_no, $row_type,
                     $p->party_name, $p->party_alias, $p->party_gst, $p->party_address,
                     $p->material_center, '', '', '', '', '',
                     $freight, $insurance, $cgst, $sgst, $igst, $tcs,
                     $p->transport_name, $p->gr_pr_no, '', $p->vehicle_no, $p->station
                  ]);
                  continue;
               }

               foreach ($descs as $d) {

                  $item = DB::table('manage_items')
                  ->leftJoin('units', 'manage_items.u_name', '=', 'units.id')
                  ->where('manage_items.id', $d->goods_discription)
                  ->select(
                  'manage_items.name as item_name',
                  'units.s_name as unit_name',
                  'manage_items.gst_rate',
                  'manage_items.item_type'
                  )
                  ->first();
                  if ($item) {

                     if ($item->item_type == 'exempted' || $item->gst_rate == 0) {
                        $gstLabel = 'EXEMPTED';
                        $gstSort  = 0;
                     } else {

                        if ($row_type == 'LOCAL') {
                              $gstLabel = $item->gst_rate . '% (' . ($item->gst_rate/2) . '% CGST + ' . ($item->gst_rate/2) . '% SGST)';
                        } else {
                              $gstLabel = $item->gst_rate . '% IGST';
                        }

                        $gstSort = $item->gst_rate;
                     }

                  } else {
                     $gstLabel = '';
                     $gstSort = 0;
                  }

                  $sundryColumns = [];

                  if ($firstRow) {
                     foreach ($billSundries as $bs) {
                        $sundryColumns[] = $sundryValues[$bs->id] ?? 0;
                     }

                     $vehicle_col   = $p->vehicle_no;
                     $transport_col = $p->transport_name;
                  } else {
                     foreach ($billSundries as $bs) {
                        $sundryColumns[] = '';
                     }

                     $vehicle_col = '';
                     $transport_col = '';
                  }

                  $row = [
                  $p->series_no,
                  $dateFormatted,
                  $p->voucher_no,
                  $row_type,
                  $gstLabel,
                  $p->party_name,
                  $p->party_alias,
                  $p->party_gst,
                  $p->party_address,
                  $p->material_center,
                  '',
                  $item->item_name ?? '',
                  $d->qty,
                  $item->unit_name ?? '',
                  $d->price,
                  $d->amount
                  ];

                  $row = array_merge($row, $sundryColumns);

                  $row = array_merge($row, [
                  $transport_col,
                  $p->gr_pr_no,
                  '',
                  $vehicle_col,
                  $p->station
                  ]);

                  fputcsv($out, $row);

                  $firstRow = false;
               }
         }

         fclose($out);
      };

      return response()->stream($callback, 200, $headers);
   }


public function purchaseTallyExport(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'required|date',
    ]);

    $purchases = DB::table('purchases')
                ->where('company_id',Session::get('user_company_id'))
        ->whereBetween('date', [$request->from_date, $request->to_date])
        ->get();

    if ($purchases->isEmpty()) {
        return back()->with('error', 'No purchase data found.');
    }

    return response()->streamDownload(function () use ($purchases) {

        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'VOUCHER TYPE',
            'DATE',
            'SUPPLIER INVOICE NUMBER',
            'LEDGER NAME',
            'LEDGER VALUE',
            'DR/CR',
            'ITEM NAME',
            'QTY',
            'RATE',
            'UNIT',
            'AMOUNT'
        ]);

        foreach ($purchases as $purchase) {

            /* ========== FIRST ROW (PARTY) ========== */
            fputcsv($file, [
                'Purchase',
                date('d-m-Y', strtotime($purchase->date)),
                $purchase->voucher_no,
                $purchase->billing_name,
                number_format($purchase->total, 2, '.', ''),
                'Cr',
                '', '', '', '', ''
            ]);

            /* ========== ITEMS ========== */
            $items = DB::table('purchase_descriptions')
               ->join('manage_items', 'purchase_descriptions.goods_discription', '=', 'manage_items.id')
               ->leftJoin('units', 'purchase_descriptions.unit', '=', 'units.id')
               ->where('purchase_descriptions.purchase_id', $purchase->id)
               ->select(
                  'purchase_descriptions.amount',
                  'purchase_descriptions.qty',
                  'purchase_descriptions.price',
                  'manage_items.name as item_name',
                  'units.name as unit_name'
               )
               ->get();

            $baseTotal = $items->sum('amount');

            /* ========== ADJUSTABLE SUNDRIES (adjust_purchase_amt = Yes) ========== */
            $adjustableSundryTotal = DB::table('purchase_sundries')
               ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
               ->where('purchase_sundries.purchase_id', $purchase->id)
               ->where('bill_sundrys.adjust_purchase_amt', 'Yes')
               ->sum('purchase_sundries.amount');

            /* ========== ITEM ROWS (LEDGER VALUE ADJUSTED, AMOUNT SAME) ========== */
            foreach ($items as $item) {

               $proportionate = ($baseTotal > 0)
                  ? ($item->amount / $baseTotal) * $adjustableSundryTotal
                  : 0;

               $ledgerValue = round($item->amount + $proportionate, 2);

               fputcsv($file, [
                  '', '', '',
                  'Purchase',
                  number_format($ledgerValue, 2, '.', ''), 
                  'Dr',
                  $item->item_name,
                  $item->qty,
                  $item->price,
                  $item->unit_name,
                  number_format($item->amount, 2, '.', ''), 
               ]);
            }


            /* ========== BILL SUNDRIES (NAME, NOT ID) ========== */
            $sundries = DB::table('purchase_sundries')
               ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
               ->where('purchase_sundries.purchase_id', $purchase->id)
               ->where('bill_sundrys.adjust_purchase_amt', 'No')
               ->select(
                  'bill_sundrys.name as sundry_name',
                  'purchase_sundries.amount'
               )
               ->get();

            foreach ($sundries as $sundry) {
                if ((float)$sundry->amount == 0) continue;

                fputcsv($file, [
                    '', '', '',
                    $sundry->sundry_name,
                    number_format($sundry->amount, 2, '.', ''),
                    'Dr',
                    '', '', '', '', ''
                ]);
            }
        }

        fclose($file);

    }, 'purchase_tally_export.csv', [
        'Content-Type' => 'text/csv',
    ]);
}


public function bulkUpdateRoundOff1(Request $request)
{
    $companyId = Session::get('user_company_id');

    // ✅ Validate dates
    $request->validate([
        'from_date' => 'required|date',
        'to_date' => 'required|date|after_or_equal:from_date',
    ]);

    $fromDate = $request->from_date;
    $toDate   = $request->to_date;

    // ✅ Pre-fetch roundoff IDs
    $roundOffIds = BillSundrys::where('nature_of_sundry', 'like', 'ROUNDED OFF%')
        ->pluck('id')
        ->toArray();

    Purchase::where('company_id', $companyId)
        ->whereBetween('date', [$fromDate, $toDate]) // ✅ FILTER ADDED
        ->chunk(50, function ($purchases) use ($companyId, $roundOffIds) {

            foreach ($purchases as $purchase) {

                DB::beginTransaction();

                try {

                    // 1️⃣ Item total
                    $itemTotal = PurchaseDescription::where('purchase_id', $purchase->id)
                        ->sum('amount');

                    // 2️⃣ Other sundries
                    $otherSundryTotal = PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereNotIn('bill_sundry', $roundOffIds)
                        ->sum('amount');

                    $expectedTotal = $itemTotal + $otherSundryTotal;

                    $finalTotal = round($expectedTotal);
                    $roundOffAmount = round($finalTotal - $expectedTotal, 2);

                    // 3️⃣ Delete old roundoff
                    PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereIn('bill_sundry', $roundOffIds)
                        ->delete();

                    AccountLedger::where('entry_type', 2)
                        ->where('entry_type_id', $purchase->id)
                        ->whereIn('account_id', function ($q) use ($roundOffIds) {
                            $q->select('purchase_amt_account')
                              ->from('bill_sundrys')
                              ->whereIn('id', $roundOffIds);
                        })
                        ->delete();

                    // 4️⃣ Insert new roundoff
                    if ($roundOffAmount != 0) {

                        $roundOffMaster = $roundOffAmount < 0
                            ? BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (-)')->first()
                            : BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (+)')->first();

                        if ($roundOffMaster) {

                            PurchaseSundry::create([
                                'purchase_id' => $purchase->id,
                                'bill_sundry' => $roundOffMaster->id,
                                'rate' => 0,
                                'amount' => abs($roundOffAmount),
                                'company_id' => $companyId,
                                'status' => '1',
                            ]);

                            AccountLedger::create([
                                'account_id' => $roundOffMaster->purchase_amt_account,
                                'debit' => $roundOffAmount > 0 ? abs($roundOffAmount) : null,
                                'credit' => $roundOffAmount < 0 ? abs($roundOffAmount) : null,
                                'txn_date' => $purchase->date,
                                'series_no' => $purchase->series_no,
                                'company_id' => $companyId,
                                'financial_year' => $purchase->financial_year ?? Session::get('default_fy'),
                                'entry_type' => 2,
                                'entry_type_id' => $purchase->id,
                                'map_account_id' => $purchase->party,
                                'created_by' => Session::get('user_id'),
                                'created_at' => now(),
                            ]);
                        }
                    }

                    // 5️⃣ Update total
                    $purchase->update(['total' => $finalTotal]);

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        });

    return back()->with('success', 'Round-off updated from '.$fromDate.' to '.$toDate);
}

public function bulkUpdateRoundOff2()
{
    $companyId = Session::get('user_company_id');
    $fromDate = $request->from_date;
    $toDate   = $request->to_date;

    // ✅ Pre-fetch roundoff IDs
    $roundOffIds = BillSundrys::where('nature_of_sundry', 'like', 'ROUNDED OFF%')
        ->pluck('id')
        ->toArray();

    Purchase::where('company_id', $companyId)
                ->whereBetween('date', [$fromDate, $toDate])
        ->chunk(50, function ($purchases) use ($companyId, $roundOffIds) {

            foreach ($purchases as $purchase) {

                DB::beginTransaction(); // ✅ small transaction

                try {

                    // 1️⃣ Item total
                    $itemTotal = PurchaseDescription::where('purchase_id', $purchase->id)
                        ->sum('amount');

                    // 2️⃣ Other sundries (excluding roundoff)
                    $otherSundryTotal = PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereNotIn('bill_sundry', $roundOffIds)
                        ->sum('amount');

                    $expectedTotal = $itemTotal + $otherSundryTotal;

                    $finalTotal = round($expectedTotal);
                    $roundOffAmount = round($finalTotal - $expectedTotal, 2);

                    // 3️⃣ Delete OLD roundoff (FAST)
                    PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereIn('bill_sundry', $roundOffIds)
                        ->delete();

                    AccountLedger::where('entry_type', 2)
                        ->where('entry_type_id', $purchase->id)
                        ->whereIn('account_id', function ($q) use ($roundOffIds) {
                            $q->select('purchase_amt_account')
                              ->from('bill_sundrys')
                              ->whereIn('id', $roundOffIds);
                        })
                        ->delete();

                    // 4️⃣ Insert new roundoff
                    if ($roundOffAmount != 0) {

                        $roundOffMaster = $roundOffAmount < 0
                            ? BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (-)')->first()
                            : BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (+)')->first();

                        if ($roundOffMaster) {

                            PurchaseSundry::create([
                                'purchase_id' => $purchase->id,
                                'bill_sundry' => $roundOffMaster->id,
                                'rate' => 0,
                                'amount' => abs($roundOffAmount),
                                'company_id' => $companyId,
                                'status' => '1',
                            ]);

                            AccountLedger::create([
                                'account_id' => $roundOffMaster->purchase_amt_account,
                                'debit' => $roundOffAmount > 0 ? abs($roundOffAmount) : null,
                                'credit' => $roundOffAmount < 0 ? abs($roundOffAmount) : null,
                                'txn_date' => $purchase->date,
                                'series_no' => $purchase->series_no,
                                'company_id' => $companyId,
                                'financial_year' => $purchase->financial_year ?? Session::get('default_fy'),
                                'entry_type' => 2,
                                'entry_type_id' => $purchase->id,
                                'map_account_id' => $purchase->party,
                                'created_by' => Session::get('user_id'),
                                'created_at' => now(),
                            ]);
                        }
                    }

                    // 5️⃣ Update total
                    $purchase->update(['total' => $finalTotal]);

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        });

    return back()->with('success', 'Round-off fixed successfully!');
}

public function bulkUpdateRoundOff(Request $request)
{
    $companyId = Session::get('user_company_id');
      $fromDate = $request->from_date;
    $toDate   = $request->to_date;

    // ✅ Pre-fetch roundoff IDs
    $roundOffIds = BillSundrys::where('nature_of_sundry', 'like', 'ROUNDED OFF%')
        ->pluck('id')
        ->toArray();
    // print_r($roundOffIds);
    Purchase::where('company_id', $companyId)
    ->whereBetween('date', [$fromDate, $toDate])
        ->chunk(50, function ($purchases) use ($companyId, $roundOffIds) {

            foreach ($purchases as $purchase) {

                DB::beginTransaction(); // ✅ small transaction

                try {

                    // 1️⃣ Item total
                    $itemTotal = PurchaseDescription::where('purchase_id', $purchase->id)
                        ->sum('amount');

                    // 2️⃣ Other sundries (excluding roundoff)
                    $otherSundryTotal = PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereNotIn('bill_sundry', $roundOffIds)
                        ->sum('amount');

                    $expectedTotal = $itemTotal + $otherSundryTotal;

                    $finalTotal = round($expectedTotal);
                    $roundOffAmount = round($finalTotal - $expectedTotal, 2);

                    // 3️⃣ Delete OLD roundoff (FAST)
                    PurchaseSundry::where('purchase_id', $purchase->id)
                        ->whereIn('bill_sundry', $roundOffIds)
                        ->delete();

                    AccountLedger::where('entry_type', 2)
                        ->where('entry_type_id', $purchase->id)
                        ->whereIn('account_id', function ($q) use ($roundOffIds) {
                            $q->select('purchase_amt_account')
                              ->from('bill_sundrys')
                              ->whereIn('id', $roundOffIds);
                        })
                        ->delete();

                    // 4️⃣ Insert new roundoff
                    if ($roundOffAmount != 0) {

                        $roundOffMaster = $roundOffAmount < 0
                            ? BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (-)')->first()
                            : BillSundrys::where('nature_of_sundry', 'ROUNDED OFF (+)')->first();

                        if ($roundOffMaster) {

                            PurchaseSundry::create([
                                'purchase_id' => $purchase->id,
                                'bill_sundry' => $roundOffMaster->id,
                                'rate' => 0,
                                'amount' => abs($roundOffAmount),
                                'company_id' => $companyId,
                                'status' => '1',
                            ]);

                            AccountLedger::create([
                                'account_id' => $roundOffMaster->purchase_amt_account,
                                'debit' => $roundOffAmount > 0 ? abs($roundOffAmount) : null,
                                'credit' => $roundOffAmount < 0 ? abs($roundOffAmount) : null,
                                'txn_date' => $purchase->date,
                                'series_no' => $purchase->series_no,
                                'company_id' => $companyId,
                                'financial_year' => $purchase->financial_year ?? Session::get('default_fy'),
                                'entry_type' => 2,
                                'entry_type_id' => $purchase->id,
                                'map_account_id' => $purchase->party,
                                'created_by' => Session::get('user_id'),
                                'created_at' => now(),
                            ]);
                        }
                    }

                    // 5️⃣ Update total
                    $purchase->update(['total' => $finalTotal]);

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        });

    return back()->with('success', 'Round-off fixed successfully!');
}


public function roundoffProgress()
{
    try {

        $companyId = Cache::get('roundoff_company');

        if (!$companyId) {
            return response()->json([
                'total' => 0,
                'done' => 0,
                'percent' => 0
            ]);
        }

        $total = Cache::get('roundoff_total_'.$companyId, 0);
        $done  = Cache::get('roundoff_done_'.$companyId, 0);

        $percent = $total > 0 ? round(($done / $total) * 100, 2) : 0;

        return response()->json([
            'total' => $total,
            'done' => $done,
            'percent' => $percent
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

}
