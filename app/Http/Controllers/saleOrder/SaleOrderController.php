<?php
namespace App\Http\Controllers\saleOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SaleOrderSetting;
use App\Models\ItemGroups;
use App\Models\ManageItems;
use App\Models\Units;
use App\Models\SaleOrder;
use App\Models\Accounts;
use App\Models\Companies;
use App\Models\SaleInvoiceConfiguration;
use App\Models\SaleOrderItemGsmSize;
use App\Models\SaleOrderItemGsm;
use App\Models\SaleOrderItemWeight;
use App\Models\BillSundrys;
use App\Models\ItemSizeStock;
use App\Helpers\CommonHelper;
use Gate;
use Session;
use DB;
use Carbon\Carbon;


class SaleOrderController extends Controller
{
    /**
     * Show list of sale orders
     */
    public function index(Request $request)
    {
        Gate::authorize('action-module',114); 

        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $from_date  = $request->input('from_date');
        $to_date    = $request->input('to_date');
        $status     = (int) $request->input('status');

        switch ($status) {
            case 1:
                Gate::authorize('action-module',127); 
                break;

            case 2:
                Gate::authorize('action-module',128); 
                break;

            case 4:
                Gate::authorize('action-module',128); 
                break;

            default:
                Gate::authorize('action-module',126); 
                break;
        }

        $queryBase = \App\Models\SaleOrder::with([
            'billTo:id,account_name',
            'shippTo:id,account_name',
            'sale:id,sale_order_id,e_invoice_status,voucher_no_prefix,date',
            'createdByUser:id,name',
            'updatedByUser:id,name'
        ])
        ->where('company_id', $company_id);

        if ($from_date || $to_date) {
            if ($status === 1) {
                // Filter by sale date (Completed)
                $queryBase->whereHas('sale', function ($q) use ($from_date, $to_date) {
                    if ($from_date) {
                        $q->whereDate('date', '>=', $from_date);
                    }
                    if ($to_date) {
                        $q->whereDate('date', '<=', $to_date);
                    }
                });
            } else {
                // Filter by sale_order created date
                if ($from_date) {
                    $queryBase->whereDate('created_at', '>=', $from_date);
                }
                if ($to_date) {
                    $queryBase->whereDate('created_at', '<=', $to_date);
                }
            }
        }
        $pendingOrders = []; $completedOrders = []; $cancelledOrders = [];$readyToDispatchOrders = [];
        if($status==0){
            $pendingOrders = (clone $queryBase)->where('status', 0)->get();
        }
        
        if($status==1){
            $completedQuery = (clone $queryBase)
                            ->where('status', 1)
                            ->orderBy(
                                \App\Models\Sales::select('voucher_no')
                                    ->whereColumn('sales.sale_order_id', 'sale_orders.id'),
                                'desc'
                            );
            if (!$from_date && !$to_date) {
                $completedQuery->limit(10);
            }
            $completedOrders = $completedQuery->get();
        }
        if($status==2){
            $cancelledOrders = (clone $queryBase)->where('status', 2)->get();
        }
        if($status==4){
            $readyToDispatchOrders = (clone $queryBase)->where('status', 4)->get();
        }
        $summary = DB::table('sale_order_item_gsm_sizes as soigs')
            ->join('sale_order_item_gsms as soig', 'soig.id', '=', 'soigs.sale_order_item_gsm_id')
            ->join('sale_order_items as soi', 'soi.id', '=', 'soigs.sale_order_item_id')
            ->join('sale_orders as so', 'so.id', '=', 'soi.sale_order_id')
            ->join('manage_items as i', 'i.id', '=', 'soi.item_id')
            ->join('units as u', 'u.id', '=', 'soi.unit')

            ->select(
                'soi.item_id',
                'i.name as item_name',

                DB::raw("
                    SUM(
                        CASE 
                            WHEN LOWER(TRIM(u.name)) LIKE '%reel%' THEN
                                (
                                    soigs.quantity *
                                    (
                                        CASE 
                                            WHEN soi.sub_unit = 'CM' THEN (soigs.size / 2.54)
                                            WHEN soi.sub_unit = 'MM' THEN (soigs.size / 25.4)
                                            ELSE soigs.size
                                        END
                                    ) * 15
                                )
                            ELSE 
                                soigs.quantity
                        END
                    ) as total_kg_raw
                "),

                DB::raw("
                    SUM(
                        CASE 
                            WHEN LOWER(TRIM(u.name)) LIKE '%kg%' THEN
                                (
                                    soigs.quantity /
                                    (
                                        (
                                            CASE 
                                                WHEN soi.sub_unit = 'CM' THEN (soigs.size / 2.54)
                                                WHEN soi.sub_unit = 'MM' THEN (soigs.size / 25.4)
                                                ELSE soigs.size
                                            END
                                        ) * 15
                                    )
                                )
                            ELSE 
                                soigs.quantity
                        END
                    ) as total_reel_raw
                ")
            )

            ->where('so.company_id', $company_id)
            ->where('so.status', 0)

            ->groupBy('soi.item_id', 'i.name')
            ->orderBy('i.name')
            ->get();
        return view('saleorder.sale_order', [
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'cancelledOrders' => $cancelledOrders,
            'readyToDispatchOrders' => $readyToDispatchOrders,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'summary' => $summary,
        ]);
    }

    /**
     * Show Add Sale Order page
     */
    public function create()
    {
        Gate::authorize('action-module',122);
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Get only items allowed in sale-order-settings
        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        $groups = ItemGroups::with(['items' => function($query) use ($company_id, $allowedItemIds) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1')
                  ->whereIn('id', $allowedItemIds);
        }])
        ->where('company_id', $company_id)
        ->get();

        
         $top_groups = [3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $groups1 = array_unique($all_groups);
        
        $party_list = Accounts::with('otherAddress')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups1)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get(); 
        // Get only units allowed in sale-order-settings
        $allowedUnitIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();

        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->whereIn('id', $allowedUnitIds)
                      ->get();
        $units = Units::select('units.*', 'sale-order-settings.unit_type')
                    ->join('sale-order-settings', 'sale-order-settings.item_id', '=', 'units.id')
                    ->where('units.company_id', $company_id)
                    ->where('units.delete', '0')
                    ->where('units.status', '1')
                    ->where('sale-order-settings.company_id', $company_id)
                    ->where('sale-order-settings.setting_type', 'UNIT')
                    ->get();

        return view('saleorder/add_sale_order', compact('groups', 'units','party_list'));
    }
    /**
     * Store a new Sale Order
     */
    public function store(Request $request)
    {
        Gate::authorize('action-module',122);
        $validated = $request->validate([
            'bill_to' => 'required',
            'ship_to' => 'required',
            'item.*'  => 'required',
            'price.*' => 'required|numeric',
            'unit.*'  => 'required',
            'gsm.*'   => 'required',
            'size.*'  => 'required',
            'reel.*'  => 'required|numeric',
        ]);
        // echo "<pre>";
        // print_r($request->all()); exit;
        DB::transaction(function() use ($request) {
            // 1. Create Sale Order
            $sale_order = SaleOrder::select('sale_order_no')
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('created_at', 'LIKE', date('Y-m-d').'%')
                    ->where('parent_order_no',null)
                    ->orderBy('id', 'desc')
                    ->first();
            if($sale_order){
                $new_order_no = substr($sale_order->sale_order_no,-4);
                if($new_order_no==""){
                    $new_order_no = '0001';
                }else{
                    $new_order_no = $new_order_no + 1;
                    $new_order_no = sprintf("%'04d", $new_order_no);
                }
            }else{
                $new_order_no = '0001';
            }
           
            $sale_order_no = "SO".date("dmY").$new_order_no;
            $saleOrder = SaleOrder::create([
                'bill_to' => $request->bill_to,
                'shipp_to' => $request->ship_to,
                'bill_to_address_id' => $request->bill_to_other_address,
                'shipp_to_address_id' => $request->shipp_to_other_address,
                'deal_id'    => $request->deal,
                'purchase_order_no'    => $request->purchase_order_no,
                'sale_order_no'    => $sale_order_no,
                'purchase_order_date'    => $request->purchase_order_date,
                'freight' => $request->freight,
                'created_by' => auth()->id(),
                'company_id' => Session::get('user_company_id'),
            ]);
            // 2. Loop Items
            foreach ($request->items as $item) {
                if(empty($item['item_id'])) {
                    continue; // Skip if item_id is missing
                }
                $validGsms = collect($item['gsms'] ?? [])->filter(function ($gsm) {
                    if (empty($gsm['gsm']) || empty($gsm['details'])) {
                        return false;
                    }
                    // at least one detail with size + reel
                    return collect($gsm['details'])->contains(function ($d) {
                        return !empty($d['size']) && !empty($d['reel']);
                    });
                });
                if ($validGsms->isEmpty()) {
                    continue; // skip item if no valid gsm
                }
                $orderItem = $saleOrder->items()->create([
                    'item_id'    => $item['item_id'],
                    'price'      => $item['price'],
                    'bill_price' => $item['bill_price'] ?? null,
                    'unit'    => $item['unit'],
                    'sub_unit'   => $item['sub_unit'],
                    'company_id' => Session::get('user_company_id'),
                ]);
                // 3. Loop GSMs
                foreach ($item['gsms'] as $gsm) {
                    if(empty($gsm['gsm'])) {
                        continue; // Skip if item_id is missing
                    }
                    if (empty($gsm['details']) || collect($gsm['details'])->filter(function ($d) {
                            return !empty($d['size']) && !empty($d['reel']);
                        })->count() < 1 ) {
                        continue;
                    }
                    $gsmRow = $orderItem->gsms()->create([
                        'sale_orders_id' => $saleOrder->id,
                        'gsm' => $gsm['gsm'],
                        'company_id' => Session::get('user_company_id'),
                    ]);
                    // 4. Loop Sizes/Reels
                    foreach ($gsm['details'] as $detail) {
                        if(empty($detail['size']) || empty($detail['reel'])) {
                            continue; // Skip if item_id is missing
                        }
                        $gsmRow->details()->create([
                            'sale_orders_id' => $saleOrder->id,
                            'sale_order_item_id' => $orderItem->id,
                            'size' => $detail['size'],
                            'quantity' => $detail['reel'],
                            'company_id' => Session::get('user_company_id'),
                        ]);
                    }
                }
            }
        });
        return redirect()->route('sale-order.index')->with('success', 'Sale order added successfully!');
    }
    /**
     * Show Sale Order Settings page
     */
    public function show($id)
    {
        Gate::authorize('action-module',114);
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        $saleOrder = SaleOrder::with([
                            'billTo:id,account_name,gstin,address,pin_code,state,pan',
                            'shippTo:id,account_name,gstin,address,pin_code,state,pan',
                            'billToOtherAddress:id,address,pincode,location',
                            'shippToOtherAddress:id,address,pincode,location',
                            'orderCreatedBy:id,name',
                            'items.item:id,name,hsn_code',
                            'items.unitMaster:id,s_name',
                            'items.gsms.details',
                            
                            ])->where('id', $id)
                            ->first();
        $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
        // echo "<pre>";
        // print_r($saleOrder->toArray()); exit;
        return view('saleorder/view_sale_order',["saleOrder"=>$saleOrder, 'company_data'=>$company_data,'configuration'=>$configuration]);
    }
    public function saleOrderSetting()
    {
        Gate::authorize('action-module',116);
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $groups = ItemGroups::with(['items' => function($query) use ($company_id) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1');
        }])
         ->where('delete', '0')
        ->where('status', '1')
        ->where('company_id', $company_id)->get();

        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->get();

        $selectedItems = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        $selectedUnits = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();
        $selectedUnitsType = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('unit_type','item_id')
                            ->toArray();
                            
        return view('saleorder/saleOrderSetting', compact('groups', 'units', 'selectedItems', 'selectedUnits','selectedUnitsType'));
    }
    public function edit(Request $request,$id)
    {
        Gate::authorize('action-module',123);
        $sale_id = $request->sale_id;
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Get only items allowed in sale-order-settings
        $allowedItemIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        $groups = ItemGroups::with(['items' => function($query) use ($company_id, $allowedItemIds) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1')
                  ->whereIn('id', $allowedItemIds);
        }])
        ->where('company_id', $company_id)
        ->get();

        $top_groups = [3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $groups1 = array_unique($all_groups);
        $party_list = Accounts::with('otherAddress')
                              ->leftjoin('states','accounts.state','=','states.id')
                              ->where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$groups1)
                              ->select('accounts.id','accounts.gstin','accounts.address','accounts.pin_code','accounts.account_name','states.state_code')
                              ->orderBy('account_name')
                              ->get(); 
        // Get only units allowed in sale-order-settings
        $allowedUnitIds = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->pluck('item_id')
                            ->toArray();

        $units = Units::where('company_id', $company_id)
                      ->where('delete', '0')
                      ->where('status', '1')
                      ->whereIn('id', $allowedUnitIds)
                      ->get();
        $units = Units::select('units.*', 'sale-order-settings.unit_type')
                    ->join('sale-order-settings', 'sale-order-settings.item_id', '=', 'units.id')
                    ->where('units.company_id', $company_id)
                    ->where('units.delete', '0')
                    ->where('units.status', '1')
                    ->where('sale-order-settings.company_id', $company_id)
                    ->where('sale-order-settings.setting_type', 'UNIT')
                    ->get();
        $saleOrder = SaleOrder::with([
                            'billTo:id,account_name,gstin,address,pin_code,state,pan',
                            'shippTo:id,account_name,gstin,address,pin_code,state,pan',
                            'orderCreatedBy:id,name',
                            'items.item:id,name,hsn_code',
                            'items.unitMaster:id,s_name',
                            'items.gsms.details',
                            
                            ])->where('id', $id)
                            ->first();
                            
            $deals = DB::table('manage_deal')
                        ->where('comp_id', $company_id)
                        ->where('party_id',$saleOrder->billTo->id)
                        ->where('status',0)
                        ->get();
                        
        return view('saleorder/edit_sale_order', compact('groups', 'units','party_list','saleOrder','deals','sale_id'));
    }
    public function update(Request $request,$id){
        Gate::authorize('action-module',123);
        $validated = $request->validate([
            'bill_to' => 'required',
            'ship_to' => 'required',
            'item.*'  => 'required',
            'price.*' => 'required|numeric',
            'unit.*'  => 'required',
            'gsm.*'   => 'required',
            'size.*'  => 'required',
            'reel.*'  => 'required|numeric',
        ]);
        // echo "<pre>";
        // print_r($request->all()); exit;
        DB::transaction(function() use ($request,$id) {
            // 1. Create Sale Order
            $saleOrder = SaleOrder::find($id);
            $saleOrder->bill_to = $request->bill_to;
            $saleOrder->shipp_to = $request->ship_to;
            $saleOrder->bill_to_address_id = $request->bill_to_other_address;
            $saleOrder->shipp_to_address_id = $request->shipp_to_other_address;
            $saleOrder->deal_id   = $request->deal;
            $saleOrder->purchase_order_no    = $request->purchase_order_no;
            $saleOrder->purchase_order_date    = $request->purchase_order_date;
            $saleOrder->freight = $request->freight ?? null;
            $saleOrder->updated_at    = Carbon::now();
            $saleOrder->updated_by = auth()->id();
            if($saleOrder->save()){
                $saleOrder->items()->delete();
                SaleOrderItemGsm::where('sale_orders_id',$id)->delete();
                SaleOrderItemGsmSize::where('sale_orders_id',$id)->delete();
                // 2. Loop Items
                foreach ($request->items as $item) {
                    if(empty($item['item_id'])) {
                        continue; // Skip if item_id is missing
                    }
                    $validGsms = collect($item['gsms'] ?? [])->filter(function ($gsm) {
                        if (empty($gsm['gsm']) || empty($gsm['details'])) {
                            return false;
                        }
                        // at least one detail with size + reel
                        return collect($gsm['details'])->contains(function ($d) {
                            return !empty($d['size']) && !empty($d['reel']);
                        });
                    });
                    if ($validGsms->isEmpty()) {
                        continue; // skip item if no valid gsm
                    }
                    $orderItem = $saleOrder->items()->create([
                        'item_id'    => $item['item_id'],
                        'price'      => $item['price'],
                        'bill_price' => $item['bill_price'] ?? null,
                        'unit'    => $item['unit'],
                        'sub_unit'   => $item['sub_unit'],
                        'company_id' => Session::get('user_company_id'),
                    ]);
                    // 3. Loop GSMs
                    foreach ($item['gsms'] as $gsm) {
                        if(empty($gsm['gsm'])) {
                            continue; // Skip if item_id is missing
                        }
                        if (empty($gsm['details']) || collect($gsm['details'])->filter(function ($d) {
                                return !empty($d['size']) && !empty($d['reel']);
                            })->count() < 1 ) {
                            continue;
                        }
                        $gsmRow = $orderItem->gsms()->create([
                            'sale_orders_id' => $saleOrder->id,
                            'gsm' => $gsm['gsm'],
                            'company_id' => Session::get('user_company_id'),
                        ]);
                        // 4. Loop Sizes/Reels
                        foreach ($gsm['details'] as $detail) {
                            if(empty($detail['size']) || empty($detail['reel'])) {
                                continue; // Skip if item_id is missing
                            }
                            $gsmRow->details()->create([
                                'sale_orders_id' => $saleOrder->id,
                                'sale_order_item_id' => $orderItem->id,
                                'size' => $detail['size'],
                                'quantity' => $detail['reel'],
                                'company_id' => Session::get('user_company_id'),
                            ]);
                        }
                    }
                }
            }
            
        });
        if(isset($request->sale_id) && !empty($request->sale_id)){
            return redirect()->route('sale-order-start', [
                'id' => $id,
                'sale_id' => $request->sale_id
            ]);
        }
        return redirect()->route('sale-order.index')->with('success', 'Sale order updated successfully!');
    }
    /**
     * Update Sale Order Settings (items + units)
     */
    public function updateSaleOrderSettings(Request $request)
    {
        
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // --- Handle Items ---
        $newItems = $request->input('items', []);
        $existingItems = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->pluck('item_id')
                            ->toArray();

        // Insert newly checked items
        $toAdd = array_diff($newItems, $existingItems);
        foreach ($toAdd as $item_id) {
            SaleOrderSetting::create([
                'company_id'   => $company_id,
                'item_id'      => $item_id,
                'setting_type' => 'ITEM',
                'setting_for'  => 'SALE ORDER',
                'status'       => 1,
            ]);
        }

        // Remove unchecked items
        $toRemove = array_diff($existingItems, $newItems);
        if (!empty($toRemove)) {
            SaleOrderSetting::where('company_id', $company_id)
                ->where('setting_type', 'ITEM')
                ->whereIn('item_id', $toRemove)
                ->delete();
        }

        // --- Handle Units ---
        $newUnits = $request->input('units', []);
        $existingUnits = SaleOrderSetting::where('company_id', $company_id)
                            ->where('setting_type', 'UNIT')
                            ->delete('item_id');

        //$toAddUnits = array_diff($newUnits, $existingUnits);
        foreach ($newUnits as $unit_id) {
            SaleOrderSetting::create([
                'company_id'   => $company_id,
                'item_id'      => $unit_id,
                'unit_type' => $request->input('unit_type_'.$unit_id) ?? null,
                'setting_type' => 'UNIT',
                'setting_for'  => 'SALE ORDER',
                'status'       => 1,
            ]);
        }

        // $toRemoveUnits = array_diff($existingUnits, $newUnits);
        // if (!empty($toRemoveUnits)) {
        //     SaleOrderSetting::where('company_id', $company_id)
        //         ->where('setting_type', 'UNIT')
        //         ->whereIn('item_id', $toRemoveUnits)
        //         ->delete();
        // }

        return redirect()->route('sale-order.settings')
            ->with('success', 'Settings updated successfully.');
    }
    public function saleOrderStart(Request $request)
    {
        $sale_id = $request->sale_id;
        $saleOrder = SaleOrder::with([
                            'billTo:id,account_name,gstin,address,pin_code,state,pan',
                            'shippTo:id,account_name,gstin,address,pin_code,state,pan,location',
                            'orderCreatedBy:id,name',
                            'items.item:id,name,hsn_code',
                            'items.unitMaster:id,s_name',
                            'items.SaleOrderSettingUnitMaster:item_id,unit_type',
                            'items.itemSize:item_id,size,weight,reel_no,quality_row_id,id',
                            'items.gsms.details',
                            
                            ])->where('id', $request->id)
                            ->first();
        $selected_weight = SaleOrderItemWeight::join('item_size_stocks','sale_order_item_weights.weight_id','=','item_size_stocks.id')
                            ->select('size','item_id','sale_order_item_weights.weight','item_size_stocks.reel_no','item_size_stocks.id')
                            ->where('sale_order_item_weights.sale_order_id',$request->id)
                            ->get();
        $grouped = [];
        foreach ($selected_weight as $row) {
            $key = $row['item_id'] . 'X' . $row['size'];
            $grouped[$key][] = $row->toArray();
        }

        // echo "<pre>";
        // print_r($grouped);die;
        $company_id = Session::get('user_company_id');
        
        $vehicles = DB::table('sale_order_vehicles')
                ->where('company_id', $company_id)
                ->where('status', 1)
                ->orderBy('id')
                ->get();
        $selectedTransporters = DB::table('sale_order_transporters')
                                ->join('accounts','accounts.id','=','sale_order_transporters.account_id')
                                ->select('sale_order_transporters.*','accounts.account_name')
                                ->where('sale_order_transporters.company_id', $company_id)
                                ->get();
        $saleOrder->location_price = "";
        if($saleOrder->shippTo->location){
            $locationPrice = DB::table('location_prices')
                                ->select('price')
                                ->where('location',$saleOrder->shippTo->location)
                                ->first();
            if($locationPrice){
                $saleOrder->location_price = $locationPrice->price;
            }
        }
        $expense = DB::table('sale-order-settings')
                                 ->where('setting_type','EXPENSE_ACCOUNT')
                                 ->where('setting_for','SALE ORDER')
                                 ->where('company_id',Session::get('user_company_id'))
                                 ->first();
        // echo "<pre>";
        // print_r($saleOrder->toArray());
        // die;
        return view('saleorder/processOrder/start_sale_order',["saleOrder"=>$saleOrder,"selected_weight"=>$grouped,"sale_id"=>$sale_id,"vehicles"=>$vehicles,"selectedTransporters"=>$selectedTransporters,"expense"=>$expense]);
    }
    public function saleOrderDelete(Request $request){
        Gate::authorize('action-module',124);
        SaleOrder::where('id',$request->sale_id)->update(['status'=>2,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        SaleOrderItemGsmSize::where('sale_orders_id',$request->sale_id)->update(['status'=>0,'updated_at'=>Carbon::now(),"estimate_quantity"=>0]);
        return redirect('sale-order')->withSuccess('Deleted successfully!');
    }
    public function saleOrderConvertInPending(Request $request){
        Gate::authorize('action-module',129);
        $update = SaleOrder::where('id',$request->id)->update(['status'=>0,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        
        if($update){
           SaleOrderItemGsmSize::where('sale_orders_id',$request->id)->update(['status'=>0,'updated_at'=>Carbon::now(),"estimate_quantity"=>0]);

            return json_encode(array('status'=>true));
        }else{
            return json_encode(array('status'=>false));
        }
    }
    public function setSaleOrder()
    {
        $companyId = auth()->user()->company_id ?? Session::get('user_company_id');

        $queryBase = SaleOrder::with([
            'billTo:id,account_name',
            'items.unitMaster:id,s_name',
            'items.item:id,name',
            'items.gsms.details',
        ])->where('company_id', $companyId);

        $pendingOrders   = (clone $queryBase)->where('status', 3)->get();
        $completedOrders = (clone $queryBase)->where('status', 1)->get();
        $cancelledOrders = (clone $queryBase)->where('status', 2)->get();

        $deckleRows = DB::table('sale_order_deckle_manual_sets as ds')
            ->join('manage_items as mi', 'mi.id', '=', 'ds.item_id')
            ->where('ds.company_id', $companyId)
            ->orderBy('ds.created_at', 'asc')
            ->select('ds.*', 'mi.name as item_name')
            ->get();

        $savedDeckleGroups = [];

        if ($deckleRows->isNotEmpty()) {

            // Collect all sale order IDs involved
            $allSaleOrderIds = [];
            foreach ($deckleRows as $row) {
                $allSaleOrderIds = array_merge(
                    $allSaleOrderIds,
                    json_decode($row->sale_order_ids, true)
                );
            }
            $allSaleOrderIds = array_unique($allSaleOrderIds);

            // Fetch sale order info
            $saleOrders = SaleOrder::with('billTo:id,account_name')
                ->whereIn('id', $allSaleOrderIds)
                ->get()
                ->keyBy('id');

            foreach ($deckleRows as $row) {

                $saleOrderIds = json_decode($row->sale_order_ids, true);
                sort($saleOrderIds); 

                $groupKey = implode(',', $saleOrderIds)
                    . '|' . $row->item_id
                    . '|' . $row->gsm;

                if (!isset($savedDeckleGroups[$groupKey])) {

                    $soNos = [];
                    $billTos = [];
                    $date = null;

                    foreach ($saleOrderIds as $sid) {
                        if (isset($saleOrders[$sid])) {
                            $soNos[]   = $saleOrders[$sid]->sale_order_no;
                            $billTos[] = $saleOrders[$sid]->billTo->account_name ?? '-';
                            $date = date('d-m-Y', strtotime($row->created_at));
                        }
                    }

                    $savedDeckleGroups[$groupKey] = [
                        'date'           => $date,
                        'sale_order_ids' => $saleOrderIds,
                        'sale_orders'    => implode(', ', array_unique($soNos)),
                        'bill_tos'       => implode(', ', array_unique($billTos)),
                        'item_id'        => $row->item_id,
                        'item_name'      => $row->item_name,
                        'gsm'            => $row->gsm,
                        'combinations'   => [],
                        'filler'   => [],
                    ];
                }

                $combo = json_decode($row->combination, true);

                $parts = array_merge(
                    $combo['fixed'] ?? [],
                    $combo['filler'] ?? []
                );

                $savedDeckleGroups[$groupKey]['combinations'][] =
                    implode(' + ', $parts) . ' = ' . $combo['total'];
                $savedDeckleGroups[$groupKey]['filler'][] = $combo['filler'] ?? [];
            }
        }

        return view('saleorder.set_sale_order', [
            'pendingOrders'     => $pendingOrders,
            'completedOrders'   => $completedOrders,
            'cancelledOrders'   => $cancelledOrders,
            'savedDeckleGroups' => array_values($savedDeckleGroups),
        ]);
    }

    public function setSaleOrderQuantity(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // Common query for all orders
        $queryBase = \App\Models\SaleOrder::with([
            'billTo:id,account_name',
            'items.unitMaster:id,s_name',
            'items.item:id,name',
            'items.itemSize',
            'items.saleOrderItem',
            'items.gsms.details', // includes size + quantity
            
        ])->where('company_id', $company_id)
        ->where('id',$request->id);

        // Separate orders by status
        $sale_order   = (clone $queryBase)->first();
        // echo "<pre>";
        // print_r($sale_order->toArray());die;
        return view('saleorder.set_sale_order_quantity', [
            'sale_order'   => $sale_order
        ]);
    }
    public function saveSaleOrderQuantity(Request $request)
    {
        foreach($request->size_id as $key => $value){
            //if(!empty($request->size_quantity[$key])){
                $size = SaleOrderItemGsmSize::find($value);
                if($size){
                    $size->estimate_quantity = $request->size_quantity[$key];
                    $size->update();
                }
            //}
        }
        return redirect()->route('set-sale-order')->with('success', 'Sale Order Quantity Set Successfully!');
    }
    public function setSaleOrderDeckle(Request $request)
    {
        $queryBase = \App\Models\SaleOrder::with([
            'billTo:id,account_name',
            'items.unitMaster:id,s_name',
            'items.item:id,name',
            'items.gsms.details', // includes size + quantity
            
        ])->where('company_id', Session::get('user_company_id'))
        ->whereIn('id',json_decode($request->sale_order));
        // Separate orders by status
        $sale_order   = (clone $queryBase)->get();        
        $arr = [];
        foreach ($sale_order as $key => $value) {
            foreach ($value->items as $k1 => $v1) {
                foreach ($v1->gsms as $k2 => $v2) {
                    foreach ($v2->details as $k3 => $v3) {
                        array_push($arr,array(
                            "sale_order_id"=>$value->id,
                            "sale_order_no"=>$value->sale_order_no,
                            "bill_to"=>$value->billTo->account_name,
                            "unit"=>$v1->unitMaster->s_name,
                            "sub_unit"=>$v1->sub_unit,
                            "item_id"=>$v1->item->id,
                            "item_name"=>$v1->item->name,
                            "size"=>$v3->size."X".$v2->gsm,
                            "order_quantity"=>$v3->quantity,
                            "estimate_quantity"=>$v3->estimate_quantity
                        ));
                    }
                }
            }
        }
        $grouped = [];
        foreach ($arr as $row) {
            $key = $row['item_id'] . 'X' . $row['size'];
            $grouped[$key][] = $row;
        }
        if ($request->item_id) {
            $itemId = $request->item_id;

            $filtered = [];
            foreach ($grouped as $key => $rows) {
                if ($rows[0]['item_id'] == $itemId) {
                    $filtered[$key] = $rows;
                }
            }

            if (count($filtered) > 0) {
                $grouped = $filtered;
            }
        }

        //Other Pending Orders for Deckle
        $queryBase = \App\Models\SaleOrder::with([
            'billTo:id,account_name',
            'items.unitMaster:id,s_name',
            'items.item:id,name',
            'items.gsms.details', // includes size + quantity
            
        ])->where('company_id', Session::get('user_company_id'))
        ->whereIn('status',[0])
        ->whereNotIn('id',json_decode($request->sale_order));
        // Separate orders by status
        $other_sale_order   = (clone $queryBase)->get();        
        $other_arr = [];
        foreach ($other_sale_order as $key => $value) {
            foreach ($value->items as $k1 => $v1) {
                foreach ($v1->gsms as $k2 => $v2) {
                    foreach ($v2->details as $k3 => $v3) {
                        array_push($other_arr,array(
                            "sale_order_id"=>$value->id,
                            "sale_order_no"=>$value->sale_order_no,
                            "bill_to"=>$value->billTo->account_name,
                            "unit"=>$v1->unitMaster->s_name,
                            "sub_unit"=>$v1->sub_unit,
                            "item_id"=>$v1->item->id,
                            "item_name"=>$v1->item->name,
                            "size"=>$v3->size."X".$v2->gsm,
                            "order_quantity"=>$v3->quantity,
                            "estimate_quantity"=>$v3->estimate_quantity
                        ));
                    }
                }
            }
        }
        $other_grouped = [];
        foreach ($other_arr as $row) {
            $key = $row['item_id'] . 'X' . $row['size'];
            $other_grouped[$key][] = $row;
        }
        //Deckle Size Range
        $deckle_range = DB::table('sale_order_deckle_size_range')
                            ->where('company_id',Session::get('user_company_id'))
                            ->first();
        $remaining_sizes = [];

        foreach ($grouped as $rows) {
            $order_qty = 0;
            $estimate_qty = 0;

            foreach ($rows as $r) {
                $order_qty += $r['order_quantity'];
                $estimate_qty += $r['estimate_quantity'];
            }

            $remaining = $order_qty - $estimate_qty;

            if ($remaining > 0) {
                $remaining_sizes[] = [
                    'item_id'   => $rows[0]['item_id'],
                    'item_name' => $rows[0]['item_name'],
                    'size'      => $rows[0]['size'],
                    'qty'       => $remaining,
                ];
            }
        }
        $pending_pool = [];

        foreach ($other_grouped as $rows) {
            foreach ($rows as $r) {
                $pending_pool[] = [
                    'item_id'       => $r['item_id'],
                    'item_name'     => $r['item_name'],
                    'size'          => $r['size'],
                    'qty'           => $r['order_quantity'] - $r['estimate_quantity'],
                    'sale_order_no' => $r['sale_order_no'],
                ];
            }
        }
        $pending_deckle_matches = [];

        foreach ($remaining_sizes as $rem) {

            [$remWidth, $remGsm] = explode('X', $rem['size']);

            foreach ($pending_pool as $pen) {

                if ($pen['item_id'] != $rem['item_id']) continue;

                [$penWidth, $penGsm] = explode('X', $pen['size']);

                if ($remGsm != $penGsm) continue;

                $sum = $remWidth + $penWidth;

                if ($deckle_range) {
                    if ($sum < $deckle_range->from_size || $sum > $deckle_range->to_size) {
                        continue;
                    }
                }

                // ✅ VALID COMBINATION FOUND
                $pending_deckle_matches[] = [
                    'item_name'     => $rem['item_name'],
                    'gsm'           => $remGsm,
                    'combination'   => $remWidth . ' + ' . $penWidth . ' = ' . $sum,
                    'from_sale_order' => $pen['sale_order_no'],
                ];
            }
        }
        $saleOrderIds = json_decode($request->sale_order, true);

        $savedManualSets = DB::table('sale_order_deckle_manual_sets')
            ->where('company_id', Session::get('user_company_id'))
            ->where('item_id', $request->item_id)
            ->where(function ($q) use ($saleOrderIds) {
                foreach ($saleOrderIds as $sid) {
                    $q->orWhereJsonContains('sale_order_ids', (string)$sid);
                }
            })
            ->get();


        return view('saleorder.set_sale_order_deckle', [
            'sale_order'   => $grouped,
            'other_sale_order'   => $other_grouped,
            'deckle_range' => $deckle_range,
            'pending_deckle_matches'=> $pending_deckle_matches,
            'saved_manual_sets' => $savedManualSets
        ]);
    }   
    public function creditDays()
    {
        Gate::authorize('action-module',172);
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $creditDays = DB::table('manage_credit_days')
                        ->where('company_id', $company_id)
                        ->orderBy('days')
                        ->get();

        return view('saleorder.credit-days-list', compact('creditDays'));
    }
    /**
     * Show create day form (you already have this file; kept for completeness)
     */
    public function createCreditDay()
    {
        return view('saleorder.credit-days-create');
    }
    /**
     * Store new credit day
     */
    public function storeCreditDay(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $created_by = auth()->id() ?? Session::get('user_id');

        DB::table('manage_credit_days')->insert([
            'days' => $request->days,
            'status' => $request->status,
            'company_id' => $company_id,
            'created_by' => $created_by,
            'created_at' => now(),
        ]);

        return redirect()->route('sale-order.credit-days')->with('success', 'Credit Day added successfully.');
    }
    /**
     * Show edit day form
     */
    public function editDay($id)
    {
        $day = DB::table('manage_credit_days')->where('id', $id)->first();

        if (!$day) {
            return redirect()->route('sale-order.credit-days')->with('error', 'Credit Day not found.');
        }

        return view('saleorder.credit-days-edit', compact('day'));
    }
    /**
     * Update a credit day
     */
    public function updateDay(Request $request, $id)
    {
        $request->validate([
            'days' => 'required|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        DB::table('manage_credit_days')->where('id', $id)->update([
            'days' => $request->days,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        return redirect()->route('sale-order.credit-days')->with('success', 'Credit Day updated successfully.');
    }
    /**
     * Show credit rates matrix (readonly)
     */
    public function creditRates()
    {
        Gate::authorize('action-module',173);
        return $this->loadCreditRateData(false);
    }

    /**
     * Show credit rates matrix in edit mode
     */
    public function editCreditRates()
    {
        return $this->loadCreditRateData(true);
    }
    /**
     * Helper: load data for credit rates views
     */
    private function loadCreditRateData($editMode = false)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        // only enabled days for rates (if you want all days, remove where('status','1'))
        $creditDays = DB::table('manage_credit_days')
                        ->where('company_id', $company_id)
                        ->where('status', '1')
                        ->orderBy('days')
                        ->get();

        // allowed items from sale-order-settings table (you used `sale-order-settings` name previously)
        $allowedItemIds = DB::table('sale-order-settings')
                            ->where('company_id', $company_id)
                            ->where('setting_type', 'ITEM')
                            ->where('setting_for', 'SALE ORDER')
                            ->pluck('item_id')
                            ->toArray();

        // fetch items from manage_items (as you confirmed)
        $items = DB::table('manage_items')
                    ->whereIn('id', $allowedItemIds)
                    ->get();

        // existing rates
        $existingRatesRaw = DB::table('credit_days_rates')
                            ->where('company_id', $company_id)
                            ->get();

        $existingRates = [];
        foreach ($existingRatesRaw as $r) {
            $existingRates[$r->item_id . '_' . $r->credit_days] = $r->rates;
        }

        return view('saleorder.credit-rates', compact('creditDays', 'items', 'existingRates', 'editMode'));
    }
    /**
     * Store / Update the entire matrix rates
     */
    public function storeCreditRates(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $user_id = auth()->id() ?? Session::get('user_id');

        // input name in the blade: rate[item_id][day_id]
        $rates = $request->input('rate', []); // default to empty array to avoid foreach null error

        foreach ($rates as $itemId => $daysRates) {
            // $daysRates can be null or array
            if (!is_array($daysRates)) continue;
            foreach ($daysRates as $dayId => $rate) {
                // normalize rate: empty string => null
                $rateValue = $rate === '' ? null : (string)$rate;

                DB::table('credit_days_rates')->updateOrInsert(
                    [
                        'item_id' => $itemId,
                        'credit_days' => $dayId,
                        'company_id' => $company_id,
                    ],
                    [
                        'rates' => $rateValue,
                        'created_by' => $user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return redirect()->route('sale-order.credit-days.rates')->with('success', 'Rates saved successfully.');
    }
    public function getItemPriceSO(Request $req)
    {
        if (!$req->bill_to || !$req->item_id) {
            return response()->json(['price' => null]);
        }

        $creditDays = Accounts::where('id', $req->bill_to)->value('credit_days');

        if (!$creditDays) {
            return response()->json(['price' => null]);
        }

        $price = DB::table('credit_days_rates')
            ->where('item_id', $req->item_id)
            ->where('credit_days', $creditDays)
            ->value('rates');

        return response()->json(['price' => $price]);
    }
    public function readyToDispatch(Request $req)
    {
        Gate::authorize('action-module',125);
        // echo "<pre>";
        // print_r($req->all()); die;
        $sale_order_id = $req->sale_order_id;
        $update = SaleOrder::where('id',$sale_order_id)->update(['status'=>4,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        if($update){
            // if(count($req->sale_enter_data)>0){
            //     foreach($req->sale_enter_data as $key => $value){
            //         if($value['unit_type']=="REEL"){
            //             $enter_qty = $value['enter_qty'];
            //          }else if($value['unit_type']=="KG"){
            //             $enter_qty = array_sum($value['reel_weight_arr']);
            //          }
            //         SaleOrderItemGsmSize::where('id',$value['detail_row_id'])
            //                             ->update([
            //                                 'sale_order_qty'=>$enter_qty,
            //                                 "status"=>3
            //                             ]);
            //         foreach($value['reel_weight_arr'] as $k1=>$v1){
            //             $sale_order_item_weight = new SaleOrderItemWeight;
            //             $sale_order_item_weight->sale_order_id = $req->sale_order_id;
            //             $sale_order_item_weight->sale_order_item_row_id = $value['detail_row_id'];
            //             $sale_order_item_weight->weight = $v1;
            //             $sale_order_item_weight->status = 0;
            //             $sale_order_item_weight->weight_id = $value['reel_weight_id'][$k1];
            //             $sale_order_item_weight->company_id = Session::get('user_company_id');
            //             $sale_order_item_weight->created_at = Carbon::now();
            //             $sale_order_item_weight->save();
            //             ItemSizeStock::where('id',$value['reel_weight_id'][$k1])
            //                             ->update([
            //                                 'status'=>0,
            //                                 'sale_order_id'=>$req->sale_order_id,
            //                                 //'sale_id'=>$sale->id
            //                             ]);
            //         }
            //     }
            // }
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    }
    public function startOrder(Request $req)
    {
        Gate::authorize('action-module',125);
        $sale_order_id = $req->id;
        $update = SaleOrder::where('id',$sale_order_id)->update(['status'=>3,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        if($update){
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    }
    public function saveDeckleRange(Request $req)
    {
        
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $deckle_range = DB::table('sale_order_deckle_size_range')
                            ->where('company_id',$company_id)
                            ->first();
        if($deckle_range){
            $update = DB::table('sale_order_deckle_size_range')
                        ->where('company_id',$company_id)
                        ->update([
                            'from_size'=>$req->from_size,
                            'to_size'=>$req->to_size,
                            'updated_at'=>Carbon::now(),
                            'updated_by'=>Session::get('user_id'),
                        ]);
            if($update){
                return redirect()->back()->with('success', 'Deckle Size Range Updated Successfully!');
            }else{
                return redirect()->back()->with('error', 'Something went wrong!');
            }
        }else{
            $insert = DB::table('sale_order_deckle_size_range')
                        ->insert([
                            'from_size'=>$req->from_size,
                            'to_size'=>$req->to_size,
                            'company_id'=>$company_id,
                            'created_at'=>Carbon::now(),
                            'created_by'=>Session::get('user_id'),
                        ]);
            if($insert){
                return redirect()->back()->with('success', 'Deckle Size Range Added Successfully!');
            }else{
                return redirect()->back()->with('error', 'Something went wrong!');
            }
        }
    }
    public function backToPending(Request $req)
    {
        $sale_order_id = $req->id;
        $update = SaleOrder::where('id',$sale_order_id)->update(['status'=>0,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        if($update){
            SaleOrderItemGsmSize::where('sale_orders_id',$sale_order_id)->update(['status'=>0,'updated_at'=>Carbon::now(),"estimated_quantity"=>0]);
            
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    }
    public function backToSetQuantity(Request $req)
    {
        // echo "<pre>";
        // print_r($req->all()); die;
        $sale_order_id = $req->id;
        $update = SaleOrder::where('id',$sale_order_id)->update(['status'=>3,'updated_at'=>Carbon::now(),"updated_by"=>Session::get('user_id')]);
        if($update){
            return response()->json(['status' => true]);
        }else{
            return response()->json(['status' => false]);
        }
    }
    public function getSaleOrderItems(Request $request)
    {
        $saleOrderIds = $request->sale_orders;

        if (!$saleOrderIds || !is_array($saleOrderIds)) {
            return response()->json([]);
        }

        // 🔹 Fetch sale orders
        $saleOrders = SaleOrder::with([
            'billTo:id,account_name',
            'items.item:id,name'
        ])
        ->where('company_id', Session::get('user_company_id'))
        ->whereIn('id', $saleOrderIds)
        ->get();

        // 🔹 Maintain PRIORITY ORDER (checkbox click order)
        $saleOrders = $saleOrders
            ->sortBy(function ($so) use ($saleOrderIds) {
                return array_search($so->id, $saleOrderIds);
            })
            ->values();

        // 🔹 Prepare Sale Order list for modal
        $saleOrderList = $saleOrders->map(function ($so) {
            return [
                'id'            => $so->id,
                'date'          => date('d-m-Y', strtotime($so->created_at)),
                'sale_order_no' => $so->sale_order_no,
                'bill_to'       => $so->billTo->account_name ?? '-',
            ];
        });

        // 🔹 Collect UNIQUE items only
        $uniqueItems = [];

        foreach ($saleOrders as $so) {
            foreach ($so->items as $item) {

                // ✅ Check if item has ANY pending size
                $hasPending = false;

                foreach ($item->gsms as $gsm) {
                    foreach ($gsm->details as $detail) {
                        if (!isset($detail->set_sale_status) || $detail->set_sale_status == 0) {
                            $hasPending = true;
                            break 2;
                        }
                    }
                }

                // ❌ Skip completed items
                if (!$hasPending) {
                    continue;
                }

                $key = $item->item->id;

                    if (!isset($uniqueItems[$key])) {
                        $uniqueItems[$key] = [
                            'item_id'   => $item->item->id,
                            'item_name' => $item->item->name,
                        ];
                    }
            }
        }
        return response()->json([
            'sale_orders' => $saleOrderList,          
            'items'       => array_values($uniqueItems),
        ]);
    }


    public function usePendingDeckleAndGenerateFallback(Request $request)
    {
        $item = $request->item;
        $gsm = $request->gsm;
        $combination = $request->combination; // e.g., "13 + 12 = 25"
        $remainingSizes = $request->remaining_sizes ?? []; // Current remaining sizes from frontend
        
        if (!$item || !$gsm || !$combination) {
            return response()->json(['status' => false, 'msg' => 'Invalid data']);
        }
        
        // Extract sizes from combination (e.g., "13 + 12 = 25" -> [13, 12])
        $left = explode('=', $combination)[0];
        $sizeParts = array_map('trim', explode('+', $left));
        $sizeParts = array_map('intval', $sizeParts);
        
        // Get deckle range
        $deckle_range = DB::table('sale_order_deckle_size_range')
            ->where('company_id', Session::get('user_company_id'))
            ->first();
        $targetMin = $deckle_range ? $deckle_range->from_size : 40;
        $targetMax = $deckle_range ? $deckle_range->to_size : 40;
        
        // Calculate remaining after using the pending combo
        // Reduce quantities for sizes used in the combination
        $remaining = [];
        foreach ($remainingSizes as $remSize) {
            if ($remSize['item'] !== $item) {
                $remaining[] = $remSize; // Keep other items as-is
                continue;
            }
            
            $size = $remSize['size'];
            [$w, $h] = explode('X', $size);
            $w = intval($w);
            
            // Count how many times this size was used in the combination
            $usedQty = 0;
            foreach ($sizeParts as $part) {
                if ($part == $w) {
                    $usedQty++;
                }
            }
            
            $newQty = $remSize['qty'] - $usedQty;
            if ($newQty > 0) {
                $remaining[] = [
                    'item' => $item,
                    'size' => $size,
                    'qty' => $newQty,
                    'unit' => $remSize['unit'] ?? 'INCH',
                    'actual_size' => $remSize['actual_size'] ?? $size,
                ];
            }
        }
        
        // Filter remaining to only this item/gsm for manual generation
        $remainingForItem = [];
        foreach ($remaining as $rem) {
            if ($rem['item'] === $item) {
                [$w, $h] = explode('X', $rem['size']);
                if ($h == $gsm) {
                    $remainingForItem[$rem['size']] = $rem;
                }
            }
        }
        
        // Get pending combinations for duplicate check
        $pendingCombinationsNormalized = [];
        if ($request->pending_combos) {
            foreach ($request->pending_combos as $pending) {
                $left = explode('=', $pending['combination'])[0];
                $parts = array_map('trim', explode('+', $left));
                $parts = array_map('intval', $parts);
                sort($parts);
                $normalized = implode('+', $parts);
                $pendingCombinationsNormalized[$normalized] = true;
            }
        }
        
        // Generate manual combinations for remaining sizes
        $newManualCombos = [];
        foreach ($remainingForItem as $size => $info) {
            $qty = intval($info['qty']);
            if ($qty <= 0) continue;
            
            [$w, $h] = explode('X', $size);
            $w = intval($w);
            
            for ($i = 0; $i < $qty; $i++) {
                $target = rand($targetMin, $targetMax);
                $sum = $w;
                $parts = [$w];
                
                while ($sum < $target && count($parts) < 5) {
                    $needed = $target - $sum;
                    $parts[] = $needed;
                    $sum += $needed;
                    break;
                }
                
                if ($sum < $targetMin || $sum > $targetMax) {
                    continue;
                }
                
                // Check for duplicates
                $normalizedParts = $parts;
                sort($normalizedParts);
                $normalizedStr = implode('+', $normalizedParts);
                
                if (isset($pendingCombinationsNormalized[$normalizedStr])) {
                    continue;
                }
                
                $newManualCombos[] = [
                    'selected' => $parts,
                    'details' => [[
                        'size' => $size,
                        'qty' => 1,
                        'actual_size' => $info['actual_size'],
                        'unit' => $info['unit'],
                    ]],
                    'manual' => true,
                    'display' => implode(' + ', $parts) . ' = ' . $sum,
                ];
                
                $remainingForItem[$size]['qty']--;
                if ($remainingForItem[$size]['qty'] <= 0) {
                    unset($remainingForItem[$size]);
                    break;
                }
            }
        }
        
        // Update remaining array with new quantities
        $remainingFormatted = [];
        foreach ($remaining as $rem) {
            if ($rem['item'] === $item && isset($remainingForItem[$rem['size']])) {
                $rem['qty'] = $remainingForItem[$rem['size']]['qty'];
            }
            if ($rem['qty'] > 0) {
                $remainingFormatted[] = $rem;
            }
        }
        
        return response()->json([
            'status' => true,
            'new_combos' => $newManualCombos,
            'remaining' => $remainingFormatted,
        ]);
    }

   public function saveDeckleStatus(Request $req)
{
    $saleOrderIds = $req->sale_order_ids;
    $itemId       = $req->item_id;

    if (!$saleOrderIds || !$itemId) {
        return response()->json(['status' => false, 'msg' => 'Invalid data']);
    }

    $companyId = Session::get('user_company_id');
    $userId    = Session::get('user_id');

    DB::table('sale_order_items')
        ->whereIn('sale_order_id', $saleOrderIds)
        ->where('item_id', $itemId)
        ->update([
            'set_sale_status' => 1,
            'updated_at' => now()
        ]);

    $saleOrderItemIds = DB::table('sale_order_items')
        ->whereIn('sale_order_id', $saleOrderIds)
        ->where('item_id', $itemId)
        ->pluck('id')
        ->toArray();

    if (!empty($saleOrderItemIds)) {
        DB::table('sale_order_item_gsm_sizes')
            ->whereIn('sale_order_item_id', $saleOrderItemIds)
            ->update([
                'set_sale_status' => 1,
                'updated_at' => now()
            ]);
    }

    DB::table('sale_order_deckle_manual_sets')
        ->where('company_id', $companyId)
        ->where('item_id', $itemId)
        ->where('type', 'SYSTEM')
        ->where(function ($q) use ($saleOrderIds) {
            foreach ($saleOrderIds as $sid) {
                $q->orWhereJsonContains('sale_order_ids', (string)$sid);
            }
        })
        ->delete();

    if (!empty($req->system_manual_deckles)) {

    foreach ($req->system_manual_deckles as $row) {

        $fullSizes = array_merge(
            $row['fixed'] ?? [],
            $row['filler'] ?? []
        );

        sort($fullSizes);
        $sizesJson = json_encode($fullSizes);

        $existsAsUser = DB::table('sale_order_deckle_manual_sets')
            ->where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('gsm', $row['gsm'])
            ->where('type', 'USER')
            ->where('sizes', $sizesJson)
            ->exists();

        if ($existsAsUser) {
            continue;
        }

        DB::table('sale_order_deckle_manual_sets')->insert([
            'company_id'     => $companyId,
            'item_id'        => $itemId,
            'gsm'            => $row['gsm'],
            'sale_order_ids' => json_encode($saleOrderIds),
            'sizes'          => $sizesJson,
            'combination'    => json_encode([
                'fixed'  => $row['fixed'] ?? [],
                'filler' => $row['filler'] ?? [],
                'total'  => $row['total']
            ]),
            'type'       => 'SYSTEM',
            'created_by' => $userId,
            'created_at' => now()
        ]);
    }
}


if (!empty($req->user_manual_deckles)) {

    foreach ($req->user_manual_deckles as $row) {

        DB::table('sale_order_deckle_manual_sets')->insert([
            'company_id'     => $companyId,
            'item_id'        => $itemId,
            'gsm'            => $row['gsm'],
            'sale_order_ids' => json_encode($saleOrderIds),
            'sizes'          => json_encode($row['filler']), 
            'combination'    => json_encode([
                'fixed'  => [],
                'filler' => $row['filler'],
                'total'  => $row['total']
            ]),
            'type'       => 'USER',
            'created_by' => $userId,
            'created_at' => now()
        ]);
    }
}


    return response()->json(['status' => true]);
}

    public function getSavedDeckleSizes(Request $req)
    {
        $saleOrderIds = $req->sale_order_ids;
        $itemId       = $req->item_id;

        if (is_string($saleOrderIds)) {
            $saleOrderIds = json_decode($saleOrderIds, true);
        }

        if (empty($saleOrderIds) || !$itemId) {
            return response()->json([]);
        }

        $companyId = Session::get('user_company_id');

        $rows = DB::table('sale_order_deckle_manual_sets as ds')
            ->join('manage_items as mi', 'mi.id', '=', 'ds.item_id')
            ->where('ds.company_id', $companyId)
            ->where('ds.item_id', $itemId)
            ->where(function ($q) use ($saleOrderIds) {
                foreach ($saleOrderIds as $sid) {
                    $q->orWhereJsonContains('ds.sale_order_ids', (string)$sid);
                }
            })
            ->orderBy('ds.gsm')
            ->orderBy('ds.created_at', 'asc')
            ->orderBy('ds.id', 'asc')
            ->select('ds.*', 'mi.name as item_name')
            ->get();

        if ($rows->isEmpty()) {
            return response()->json([]);
        }

        $saleOrders = DB::table('sale_orders')
            ->whereIn('id', $saleOrderIds)
            ->pluck('sale_order_no', 'id')
            ->toArray();

        $result = [];

        foreach ($rows as $row) {

            $combo = json_decode($row->combination, true);
            if (!$combo) continue;

            $parts = array_merge(
                $combo['fixed'] ?? [],
                $combo['filler'] ?? []
            );

            $soNumbers = [];
            foreach (json_decode($row->sale_order_ids, true) as $sid) {
                if (isset($saleOrders[$sid])) {
                    $soNumbers[] = $saleOrders[$sid];
                }
            }

            if (!isset($result[$row->gsm])) {
                $result[$row->gsm] = [
                    'sale_orders' => implode(', ', $soNumbers),
                    'item_id'     => $row->item_id,
                    'item_name'   => $row->item_name,
                    'gsm'         => $row->gsm,
                    'sets'        => []
                ];
            }

            $result[$row->gsm]['sets'][] = [
                'id'      => $row->id,
                'type'    => $row->type, 
                'display' => implode(' + ', $parts) . ' = ' . $combo['total']
            ];
        }

        return response()->json(array_values($result));
    }

    public function cancelDeckleSize(Request $req)
    {
        $sizeId = $req->size_id;

        DB::table('sale_order_item_gsm_sizes')
            ->where('id', $sizeId)
            ->update(['set_sale_status' => 0]);

        $row = DB::table('sale_order_item_gsm_sizes')
            ->where('id', $sizeId)
            ->first();

        if ($row) {

            $saleOrderItemId = $row->sale_order_item_id;

            $stillSaved = DB::table('sale_order_item_gsm_sizes')
                ->where('sale_order_item_id', $saleOrderItemId)
                ->where('set_sale_status', 1)
                ->exists();

            if (!$stillSaved) {
                DB::table('sale_order_items')
                    ->where('id', $saleOrderItemId)
                    ->update(['set_sale_status' => 0]);
            }
        }

        return response()->json(['status' => true]);
    }

    public function cancelDeckleItem(Request $req)
    {
        $saleOrderId = $req->sale_order_id;
        $itemId      = $req->item_id;

        if (!$saleOrderId || !$itemId) {
            return response()->json(['status' => false, 'msg' => 'Invalid data']);
        }

        $saleOrderItemIds = DB::table('sale_order_items')
            ->where('sale_order_id', $saleOrderId)
            ->where('item_id', $itemId)
            ->pluck('id');

        if ($saleOrderItemIds->isEmpty()) {
            return response()->json(['status' => false, 'msg' => 'Item not found']);
        }

        DB::table('sale_order_item_gsm_sizes')
            ->whereIn('sale_order_item_id', $saleOrderItemIds)
            ->update(['set_sale_status' => 0]);

        DB::table('sale_order_items')
            ->whereIn('id', $saleOrderItemIds)
            ->update(['set_sale_status' => 0]);

        return response()->json(['status' => true]);
    }
    public function removeSingleDeckleCombination(Request $req)
    {
        $id = $req->id;

        if (!$id) {
            return response()->json(['status' => false, 'msg' => 'Invalid ID']);
        }

        DB::table('sale_order_deckle_manual_sets')
            ->where('id', $id)
            ->where('company_id', Session::get('user_company_id'))
            ->delete();

        return response()->json(['status' => true]);
    }

    public function removeCompleteDeckleGsm(Request $req)
    {
        $saleOrderIds = $req->sale_order_ids;
        $itemId       = $req->item_id;
        $gsm          = $req->gsm;

        if (is_string($saleOrderIds)) {
            $saleOrderIds = json_decode($saleOrderIds, true);
        }

        if (empty($saleOrderIds) || !$itemId || !$gsm) {
            return response()->json([
                'status' => false,
                'msg'    => 'Invalid data'
            ]);
        }

        $companyId = Session::get('user_company_id');

        DB::beginTransaction();

        try {

            DB::table('sale_order_deckle_manual_sets')
        ->where('company_id', $companyId)
        ->where('item_id', $itemId)
        ->where('gsm', $gsm)
        ->where(function ($q) use ($saleOrderIds) {
            foreach ($saleOrderIds as $sid) {
                $q->orWhereJsonContains('sale_order_ids', (string)$sid);
            }
        })
        ->delete();

            $gsmSizeIds = DB::table('sale_order_item_gsm_sizes as sgs')
                ->join('sale_order_items as soi', 'soi.id', '=', 'sgs.sale_order_item_id')
                ->join('sale_order_item_gsms as sog', 'sog.id', '=', 'sgs.sale_order_item_gsm_id')
                ->whereIn('soi.sale_order_id', $saleOrderIds)
                ->where('soi.item_id', $itemId)
                ->where('sog.gsm', $gsm)
                ->pluck('sgs.id')
                ->toArray();

            if (!empty($gsmSizeIds)) {
                DB::table('sale_order_item_gsm_sizes')
                    ->whereIn('id', $gsmSizeIds)
                    ->update([
                        'set_sale_status' => 0,
                        'updated_at'      => now()
                    ]);
            }

            $saleOrderItemIds = DB::table('sale_order_items')
                ->whereIn('sale_order_id', $saleOrderIds)
                ->where('item_id', $itemId)
                ->pluck('id')
                ->toArray();

            foreach ($saleOrderItemIds as $soItemId) {

                $hasAnySet = DB::table('sale_order_item_gsm_sizes')
                    ->where('sale_order_item_id', $soItemId)
                    ->where('set_sale_status', 1)
                    ->exists();

                if (!$hasAnySet) {
                    DB::table('sale_order_items')
                        ->where('id', $soItemId)
                        ->update([
                            'set_sale_status' => 0,
                            'updated_at'      => now()
                        ]);
                }
            }

            DB::commit();

            return response()->json(['status' => true]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'msg'    => 'Failed to remove deckle',
                'error'  => $e->getMessage()
            ]);
        }
    }


    public function saleOrderInfo()
    {
        return $this->loadSaleOrderInfo(false);
    }

    public function editSaleOrderInfo()
    {
        return $this->loadSaleOrderInfo(true);
    }

    private function loadSaleOrderInfo($editMode = false)
    {
        $company_id = auth()->user()->company_id ?? session('user_company_id');

        $vehicles = DB::table('sale_order_vehicles')
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $formCompanyId = Session::get('user_company_id');

        $top_groups = [3, 11]; 

        $all_groups = [];
        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $all_groups = array_merge(
                $all_groups,
                CommonHelper::getAllChildGroupIds($group_id, $formCompanyId)
            );
        }
        $all_groups = array_unique($all_groups);

        $transporterAccounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [$formCompanyId, 0])
            ->whereIn('under_group', $all_groups)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        $selectedTransporters = DB::table('sale_order_transporters')
            ->where('company_id', $formCompanyId)
            ->pluck('account_id')
            ->toArray();

        $billsundry = BillSundrys::where('delete', '0')
            ->where('status', '1')
            ->whereIn('company_id', [$formCompanyId, 0])
            ->orderBy('name')
            ->get();

        $selectedBillSundry = DB::table('sale-order-settings')
            ->where('company_id', $formCompanyId)
            ->where('setting_type', 'BILL_SUNDRY')
            ->where('setting_for', 'SALE ORDER')
            ->value('bill_sundry_id');
        // ================= EXPENSE ACCOUNT GROUPS =================
        $expense_root_groups = [12, 15]; // DIRECT EXPENSE, INDIRECT EXPENSE

        $expense_group_ids = [];

        foreach ($expense_root_groups as $gid) {
            $expense_group_ids[] = $gid;

            $expense_group_ids = array_merge(
                $expense_group_ids,
                CommonHelper::getAllChildGroupIds($gid, $formCompanyId)
            );
        }

        $expense_group_ids = array_unique($expense_group_ids);

        // ===== Fetch Accounts under Expense Groups =====
        $expenseAccounts = Accounts::whereIn('company_id', [$formCompanyId, 0])
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('under_group', $expense_group_ids)
            ->where('under_group_type', 'group')
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        // ===== Selected Expense Account =====
        $selectedExpenseAccount = DB::table('sale-order-settings')
            ->where('company_id', $formCompanyId)
            ->where('setting_type', 'EXPENSE_ACCOUNT')
            ->where('setting_for', 'SALE ORDER')
            ->value('expense_account_id');
        return view('saleorder.sale-order-info', compact(
            'editMode',
            'vehicles',
            'transporterAccounts',
            'selectedTransporters',
            'billsundry',
            'selectedBillSundry',
            'expenseAccounts',
            'selectedExpenseAccount'
        ));

    }

    public function storeSaleOrderInfo(Request $request)
    {
        $company_id = auth()->user()->company_id ?? session('user_company_id');

        $vehicles = $request->input('vehicles', []);

        $existingVehicleIds = DB::table('sale_order_vehicles')
            ->where('company_id', $company_id)
            ->pluck('id')
            ->toArray();

        $submittedVehicleIds = [];

        foreach ($vehicles as $key => $value) {
            if ($key === 'new') continue;

            $submittedVehicleIds[] = (int)$key;

            DB::table('sale_order_vehicles')
                ->where('id', $key)
                ->where('company_id', $company_id)
                ->update([
                    'vehicle_no' => $value,
                    'updated_at' => now()
                ]);
        }

        $vehiclesToDelete = array_diff($existingVehicleIds, $submittedVehicleIds);

        if (!empty($vehiclesToDelete)) {
            DB::table('sale_order_vehicles')
                ->where('company_id', $company_id)
                ->whereIn('id', $vehiclesToDelete)
                ->update([
                    'status'     => 0,
                    'updated_at' => now(),
                ]);
        }

        if (isset($vehicles['new'])) {
            foreach ($vehicles['new'] as $vehicleNo) {
                if (trim($vehicleNo) === '') continue;

                DB::table('sale_order_vehicles')->insert([
                    'company_id' => $company_id,
                    'vehicle_no' => $vehicleNo,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

            DB::table('sale_order_transporters')
                ->where('company_id', $company_id)
                ->delete();

            $transporters = array_unique($request->input('transporters', []));

            foreach ($transporters as $accountId) {
                if (!$accountId) continue;

                DB::table('sale_order_transporters')->insert([
                    'company_id' => $company_id,
                    'account_id' => $accountId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('sale-order-settings')->updateOrInsert(
            [
                'company_id'   => $company_id,
                'setting_type' => 'BILL_SUNDRY',
                'setting_for'  => 'SALE ORDER',
            ],
            [
                'bill_sundry_id' => $request->bill_sundry_id,
                'status'         => 1,
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );
        // ===== Save Expense Account =====
        DB::table('sale-order-settings')->updateOrInsert(
        [
            'company_id'   => $company_id,
            'setting_type' => 'EXPENSE_ACCOUNT',
            'setting_for'  => 'SALE ORDER',
        ],
        [
            'expense_account_id' => $request->expense_account_id,
            'status'       => 1,
            'updated_at'   => now(),
            'created_at'   => now(),
        ]
        );
            return redirect()->route('sale-order.info')
                ->with('success', 'Sale order info saved successfully.');
    }

    public function saveFillerRange(Request $req)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        DB::table('sale_order_deckle_size_range')
            ->updateOrInsert(
                ['company_id' => $company_id],
                [
                    'filler_from_size' => $req->filler_from_size,
                    'filler_to_size'   => $req->filler_to_size,
                    'updated_at'       => now(),
                    'updated_by'       => Session::get('user_id')
                ]
            );

        return redirect()->back()->with('success', 'Filler Size Range Saved Successfully!');
    }

    public function addLocationPrice(Request $req){
        $locations = Accounts::select(
            'accounts.location',
            'location_prices.price'
        )
        ->leftJoin(
            'location_prices',
            'accounts.location',
            '=',
            'location_prices.location'
        )
        ->where('accounts.company_id', Session::get('user_company_id'))
        ->whereNotNull('accounts.location')
        ->where('accounts.location','!=','')
        ->orderBy('accounts.location')
        ->distinct()
        ->get();
        return view('saleorder.add-location-price',compact('locations'));
    }
    public function storeLocationPrice(Request $req){
        foreach($req->location as $key=>$location){
            $price = $req->price[$key];
                DB::table('location_prices')
                    ->updateOrInsert(
                    [
                        'company_id' => Session::get('user_company_id'),
                        'location' => $location
                    ],
                    [
                        'price' => $price,
                        'updated_at' => Carbon::now(),
                        'updated_by' => Session::get('user_id'),
                        'created_at' => Carbon::now(),
                        'created_by' => Session::get('user_id')
                    ]
                );
        }
        return redirect()->back()->with('success', 'Location Price Saved Successfully!');
        
    }
    public function saleOrderPreview(Request $req){
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',Session::get('user_company_id'))->first();
        $bill_to_id = $req->bill_to_id;
        $bill_to = Accounts::where('id',$bill_to_id)->first();
        $shipp_to_id = $req->shipp_to_id;
        $shipp_to = Accounts::where('id',$shipp_to_id)->first();
        $item_arr = json_decode($req->item_arr, true);
        foreach($item_arr as $key=>$item){
            $items = ManageItems::join('units', 'manage_items.u_name', '=', 'units.id')->where('manage_items.id',$item['item_id'])->get(['manage_items.name','hsn_code','s_name','p_name'])->first();
            $item_arr[$key]['item_name'] = $items->name ?? '';
            $item_arr[$key]['hsn_code'] = $items->hsn_code ?? '';
            $item_arr[$key]['unit_name'] = $items->s_name ?? '';
            $item_arr[$key]['item_print_name'] = $items->p_name ?? '';
        }
        $vehicle_info = $req->vehicle_info;
        $vehicle_info_type = $req->vehicle_info_type;
        $to_pay_freight = $req->to_pay_freight;
        $to_pay_other_charges = $req->to_pay_other_charges;
        $vehicle_freight = $req->vehicle_freight;
        $transporter_freight = $req->transporter_freight;
        $transporter_other_charges = $req->transporter_other_charges;
        //     echo "<pre>";
        //     print_r($item_arr);

        // die;
        $bank_detail = DB::table('banks')->where('company_id', Session::get('user_company_id'))
            ->select('banks.*')
            ->first();
        return view('saleorder.sale_order_preview',compact('bill_to','shipp_to','item_arr','vehicle_info','vehicle_info_type','to_pay_freight','to_pay_other_charges','vehicle_freight','transporter_freight','transporter_other_charges','company_data','configuration','bank_detail'));
        
    }
    public function vehicleReport(Request $request)
    {
        $company_id = Session::get('user_company_id');
    
        // vehicle dropdown list
        $vehicle_list = DB::table('sale_order_vehicles')
            ->where('company_id',$company_id)
            ->where('status',1)
            ->orderBy('vehicle_no')
            ->get();
    
        $query = DB::table('sale_vehicle_txns')
            ->join('sale_orders','sale_vehicle_txns.sale_order_id','=','sale_orders.id')
            ->leftJoin('sales','sale_vehicle_txns.sale_id','=','sales.id')
            ->leftJoin('sale_order_vehicles','sale_vehicle_txns.vehicle_id','=','sale_order_vehicles.id')
            ->leftJoin('accounts','sales.party','=','accounts.id')
            ->where('sale_vehicle_txns.company_id',$company_id);
    
        // vehicle filter
        if($request->vehicle_id){
            $query->where('sale_vehicle_txns.vehicle_id',$request->vehicle_id);
        }
    
        // date filter
        if($request->from_date && $request->to_date){
            $query->whereBetween('sales.date', [$request->from_date, $request->to_date]);
        }
    
        $vehicles = $query->select(
            'sale_order_vehicles.vehicle_no',
            'sales.date as bill_date',
            'sales.voucher_no_prefix',
            'accounts.account_name as party_name',
            'sale_vehicle_txns.vehicle_freight_amount'
        )->get();
    
        return view('saleorder.vehicle_report', compact('vehicles','vehicle_list'));
    }
    public function summaryItemDetails(Request $request)
{
    $company_id = Session::get('user_company_id');

    $data = DB::table('sale_order_item_gsm_sizes as soigs')
        ->join('sale_order_item_gsms as soig', 'soig.id', '=', 'soigs.sale_order_item_gsm_id')
        ->join('sale_order_items as soi', 'soi.id', '=', 'soigs.sale_order_item_id')
        ->join('sale_orders as so', 'so.id', '=', 'soi.sale_order_id')
        ->join('accounts as acc', 'acc.id', '=', 'so.bill_to')
        ->join('units as u', 'u.id', '=', 'soi.unit')

        ->select(
            DB::raw("DATE_FORMAT(so.created_at, '%d-%m-%Y') as date"),
            'so.sale_order_no as so_no',
            'so.purchase_order_no as po_no',
            'acc.account_name as bill_to',

            DB::raw("
                SUM(
                    CASE 
                        WHEN LOWER(TRIM(u.name)) LIKE '%reel%' THEN
                            (
                                soigs.quantity *
                                (
                                    CASE 
                                        WHEN soi.sub_unit = 'CM' THEN (soigs.size / 2.54)
                                        WHEN soi.sub_unit = 'MM' THEN (soigs.size / 25.4)
                                        ELSE soigs.size
                                    END
                                ) * 15
                            )
                        ELSE 
                            soigs.quantity
                    END
                ) as kg
            "),

            DB::raw("
                SUM(
                    CASE 
                        WHEN LOWER(TRIM(u.name)) LIKE '%kg%' THEN
                            (
                                soigs.quantity /
                                (
                                    (
                                        CASE 
                                            WHEN soi.sub_unit = 'CM' THEN (soigs.size / 2.54)
                                            WHEN soi.sub_unit = 'MM' THEN (soigs.size / 25.4)
                                            ELSE soigs.size
                                        END
                                    ) * 15
                                )
                            )
                        ELSE 
                            soigs.quantity
                    END
                ) as reel
            ")
        )

        ->where('so.company_id', $company_id)
        ->where('so.status', 0)
        ->where('soi.item_id', $request->item_id)

        ->groupBy(
            'so.id',
            'so.sale_order_no',
            'so.purchase_order_no',
            'acc.account_name',
            'so.created_at'
        )

        ->orderBy('so.created_at')
        ->get();

    return response()->json($data);
}
}
