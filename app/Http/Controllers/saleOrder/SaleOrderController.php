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
use Session;
use DB;


class SaleOrderController extends Controller
{
    /**
     * Show list of sale orders
     */
    public function index(Request $request)
    {
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $from_date = $request->input('from_date');
        $to_date   = $request->input('to_date');
        $saleOrder = SaleOrder::with([
                            'billTo:id,account_name','shippTo:id,account_name'
                            ])->where('company_id', $company_id)
                            ->get();
        // if ($from_date) {
        //     $saleOrder->whereDate('date', '>=', $from_date);
        // }
        // if ($to_date) {
        //     $saleOrder->whereDate('date', '<=', $to_date);
        // }

        //$saleOrder->orderBy('id', 'desc')->get();
        // echo "<pre>";
        // print_r($saleOrder->toArray()); exit;
        return view('saleorder/sale_order',["saleOrder"=>$saleOrder, 'from_date' => $from_date, 'to_date' => $to_date]);
    }

    /**
     * Show Add Sale Order page
     */
    public function create()
    {
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

         $groups1 = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
      $groups1->push(3);
      $groups1->push(11);
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
        //$sale_order_no = "SO".str_pad((SaleOrder::where('company_id', Session::get('user_company_id'))->max('id') + 1), 6, '0', STR_PAD_LEFT);
        $saleOrder = SaleOrder::create([
            'bill_to' => $request->bill_to,
            'shipp_to' => $request->ship_to,
            'deal'    => $request->deal,
            'purchase_order_no'    => $request->purchase_order_no,
            'sale_order_no'    => $sale_order_no,
            'purchase_order_date'    => $request->purchase_order_date,
            'freight' => $request->freight ?? null,
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
                if (
    empty($gsm['details']) ||
    collect($gsm['details'])->filter(function ($d) {
        return !empty($d['size']) && !empty($d['reel']);
    })->count() < 1
) {
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
                        'sale_orders_item_id' => $orderItem->id,
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
        $company_data = Companies::join('states','companies.state','=','states.id')
                        ->where('companies.id', Session::get('user_company_id'))
                        ->select(['companies.*','states.name as sname'])
                        ->first();
        $saleOrder = SaleOrder::with([
                            'billTo:id,account_name,gstin,address,pin_code,state,pan',
                            'shippTo:id,account_name,gstin,address,pin_code,state,pan',
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
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');

        $groups = ItemGroups::with(['items' => function($query) use ($company_id) {
            $query->where('company_id', $company_id)
                  ->where('delete', '0')
                  ->where('status', '1');
        }])->where('company_id', $company_id)->get();

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

        return view('saleorder/saleOrderSetting', compact('groups', 'units', 'selectedItems', 'selectedUnits'));
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
}
