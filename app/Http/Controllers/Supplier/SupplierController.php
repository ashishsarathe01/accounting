<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use DB;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Supplier;
use App\Models\SupplierLocation;
use App\Models\SupplierLocationRates;
use App\Models\Accounts;
use App\Models\AccountGroups;
use App\Models\SupplierSubHead;
use App\Models\SupplierDifferenceRate;
use App\Models\SupplierBonus;
use App\Models\ManageItems;
use App\Models\FuelSupplier;
use App\Models\FuelSupplierRate;
use App\Models\SaleOrderSetting;
use App\Models\BusinessActivityLog;
use Gate;
use Yajra\DataTables\Facades\DataTables;
class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $location = SupplierLocation::select('id','name')
                                    ->where('status',1)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->orderBy('name')
                                    ->get();
        $supplier = Supplier::with([
                                'account:id,account_name',
                                'latestLocationRate' => function ($q) {
                                    $q->join('supplier_sub_heads', 'supplier_location_rates.head_id', '=', 'supplier_sub_heads.id')
                                    ->select(
                                        'supplier_location_rates.id',
                                        'supplier_location_rates.parent_id',
                                        'location',
                                        'head_rate',
                                        'supplier_sub_heads.name',
                                        'supplier_location_rates.head_id',
                                        'r_date',
                                        'supplier_sub_heads.id',
                                        'supplier_location_rates.r_date'
                                    )->orderBy('r_date','desc');
                                }
                            ])
                            ->select('suppliers.id', 'account_id', 'suppliers.status')
                            ->where('suppliers.company_id', Session::get('user_company_id'))
                            ->join('accounts', 'suppliers.account_id', '=', 'accounts.id')
                            ->orderBy('accounts.account_name', 'asc')
                            ->get();
        $fuel_supplier = FuelSupplier::with([
                    'account:id,account_name',
                    'itemRates' => function ($q) {
                        $q->join('manage_items', 'fuel_supplier_rates.item_id', '=', 'manage_items.id')
                            ->select(
                                'price_date',
                                'price',
                                'item_id',
                                'parent_id',
                                'name'
                            )
                            ->whereIn('price_date', function ($sub) {
                                $sub->selectRaw('MAX(price_date)')
                                    ->from('fuel_supplier_rates as fsr2')
                                    ->whereColumn('fsr2.parent_id', 'fuel_supplier_rates.parent_id');
                            });
                    }
                ])
                ->select('fuel_suppliers.id', 'account_id', 'fuel_suppliers.status')
                ->where('fuel_suppliers.company_id', Session::get('user_company_id'))
                ->join('accounts', 'fuel_suppliers.account_id', '=', 'accounts.id')
                ->orderBy('accounts.account_name', 'asc')
                ->get();

        $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->select('item_id','group_type')
                            ->get();
                // echo "<pre>";print_r($fuel_supplier->toArray());die;
        
        return view('supplier.index',["locations"=>$location,'suppliers'=>$supplier,"fuel_supplier"=>$fuel_supplier,"group_list"=>$group_list]);
    }
public function wasteKraftSupplier()
    {
       
        Gate::authorize('action-module',98);
        $locations = SupplierLocation::select('id','name')
            ->where('status',1)
            ->where('company_id', Session::get('user_company_id'))
            ->orderBy('name')
            ->get();
        $globalItems = DB::table('supplier_location_rates as slr')
            ->join('supplier_sub_heads as ssh', 'ssh.id', '=', 'slr.head_id')
            ->where('slr.company_id', Session::get('user_company_id'))
            ->where('ssh.status', 1)
            ->groupBy('ssh.id', 'ssh.name', 'ssh.sequence', 'ssh.group_id')
            ->orderBy('ssh.group_id')
            ->orderBy('ssh.sequence')
            ->pluck('ssh.name')
            ->toArray();
            //die;
        // $suppliers = Supplier::with([
        //         'account:id,account_name',
        //         'latestLocationRate' => function ($q) {
        //             $q->join('supplier_sub_heads', 'supplier_location_rates.head_id', '=', 'supplier_sub_heads.id')
        //             ->select(
        //                 'supplier_location_rates.id',
        //                 'supplier_location_rates.parent_id',
        //                 'location',
        //                 'head_rate',
        //                 'supplier_sub_heads.name',
        //                 'supplier_location_rates.head_id',
        //                 'r_date',
        //                 'supplier_sub_heads.id',
        //                 'supplier_location_rates.r_date'
        //             )->orderBy('r_date','desc');
        //         }
        //     ])
        //     ->select('suppliers.id', 'account_id', 'suppliers.status')
        //     ->where('suppliers.company_id', Session::get('user_company_id'))
        //     ->join('accounts', 'suppliers.account_id', '=', 'accounts.id')
        //     ->orderBy('accounts.account_name', 'asc')
        //     ->get();

        return view('supplier.WasteKraft.index', [
            'locations' => $locations,
            'globalItems' => $globalItems
        ]);
    }
    public function wasteKraftSupplierDatatable(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $locations = SupplierLocation::pluck('name','id');
        $heads = DB::table('supplier_location_rates as slr')
            ->join('supplier_sub_heads as ssh', 'ssh.id', '=', 'slr.head_id')
            ->where('slr.company_id', $company_id)
            ->where('ssh.status', 1)
            ->groupBy('ssh.id', 'ssh.name', 'ssh.sequence', 'ssh.group_id')
            ->orderBy('ssh.group_id')
            ->orderBy('ssh.sequence')
            ->pluck('ssh.name', 'ssh.id');
           
        $suppliers = Supplier::with([
            'account:id,account_name',
            'latestLocationRate' => function ($q) {
                $q->join('supplier_sub_heads', 'supplier_location_rates.head_id', '=', 'supplier_sub_heads.id')
                ->select(
                    'supplier_location_rates.parent_id',
                    'supplier_location_rates.head_id',
                    'location',
                    'head_rate',
                    'supplier_sub_heads.name',
                    'supplier_location_rates.r_date'
                );
            }
        ])
        ->where('suppliers.company_id',$company_id)
        ->get();
    // die;
        $rows = [];

        foreach ($suppliers as $supplier) {

            $grouped = [];

            foreach ($supplier->latestLocationRate as $rate) {
                $grouped[$rate->location][] = $rate;
            }

            foreach ($grouped as $location_id => $rates) {

                $row = [];
                // initialize all columns (VERY IMPORTANT for correct order)
                foreach ($heads as $headName) {
                    $row[$headName] = '';
                }
                $row['supplier_name'] = '(' . ($supplier->account->account_name ?? '') . ')';
                $row['date'] = date('d-m-Y',strtotime($rates[0]->r_date));
                $row['location'] = strtoupper($locations[$location_id] ?? '');

                foreach ($rates as $r) {
                    if (isset($heads[$r->head_id])) {
                        $row[$heads[$r->head_id]] = $r->head_rate;
                    }
                }

                $edit = '';
                $delete = '';

                if (Gate::allows('view-module',105)) {
                    $edit = '<a href="'.url('supplier/waste-kraft/'.$supplier->id.'/edit').'">
                                <img src="'.asset('public/assets/imgs/edit-icon.svg').'" class="px-1">
                            </a>';
                }

                if (Gate::allows('view-module',106)) {
                    $delete = '<button class="border-0 bg-transparent delete"
                                    data-id="'.$supplier->id.'"
                                    data-type="Waste">
                                    <img src="'.asset('public/assets/imgs/delete-icon.svg').'" class="px-1">
                            </button>';
                }

                $row['action'] = $edit.$delete;

                            $rows[] = $row;
                        }
                    }

        return DataTables::of($rows)
            ->rawColumns(['action'])
            ->make(true);
    }
    public function boilerFuelSupplier()
    {
        $fuel_suppliers = FuelSupplier::with([
                'account:id,account_name',
                'itemRates' => function ($q) {
                    $q->join('manage_items', 'fuel_supplier_rates.item_id', '=', 'manage_items.id')
                    ->select(
                        'price_date',
                        'price',
                        'item_id',
                        'parent_id',
                        'name'
                    )
                    ->whereIn('price_date', function ($sub) {
                        $sub->selectRaw('MAX(price_date)')
                            ->from('fuel_supplier_rates as fsr2')
                            ->whereColumn('fsr2.parent_id', 'fuel_supplier_rates.parent_id');
                    });
                }
            ])
            ->select('fuel_suppliers.id', 'account_id', 'fuel_suppliers.status')
            ->where('fuel_suppliers.company_id', Session::get('user_company_id'))
            ->join('accounts', 'fuel_suppliers.account_id', '=', 'accounts.id')
            ->orderBy('accounts.account_name', 'asc')
            ->get();

        return view('supplier.BoilerFuel.index', [
            'fuel_supplier' => $fuel_suppliers
        ]);
    }

    public function boilerFuelSupplierDatatable(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $suppliers = FuelSupplier::with([
            'account:id,account_name',
            'itemRates' => function ($q) {
                $q->join('manage_items', 'fuel_supplier_rates.item_id', '=', 'manage_items.id')
                ->select(
                    'price_date',
                    'price',
                    'item_id',
                    'parent_id',
                    'name'
                )
                ->whereIn('price_date', function ($sub) {
                    $sub->selectRaw('MAX(price_date)')
                        ->from('fuel_supplier_rates as fsr2')
                        ->whereColumn('fsr2.parent_id', 'fuel_supplier_rates.parent_id');
                });
            }
        ])
        ->select('fuel_suppliers.id','account_id','fuel_suppliers.status')
        ->where('fuel_suppliers.company_id',$company_id)
        ->join('accounts','fuel_suppliers.account_id','=','accounts.id')
        ->orderBy('accounts.account_name','asc')
        ->get();

        $rows = [];

        foreach ($suppliers as $supplier) {

            $row = [];

            $row['supplier_name'] = '(' . ($supplier->account->account_name ?? '') . ')';

            $row['date'] = isset($supplier->itemRates[0])
                ? date('d-m-Y', strtotime($supplier->itemRates[0]->price_date))
                : '';

            /*
            Build items table
            */

            $items = '<table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($supplier->itemRates as $item) {
                $items .= '<tr>
                            <td>'.$item->name.'</td>
                            <td>'.$item->price.'</td>
                        </tr>';
            }

            $items .= '</tbody></table>';

            $row['items'] = $items;



            $edit = '';
            $delete = '';

            if (Gate::allows('view-module',206)) {

                $edit = '<a href="'.url('/supplier/boiler-fuel/'.$supplier->id.'/edit').'">
                            <img src="'.asset('public/assets/imgs/edit-icon.svg').'" class="px-1">
                        </a>';
            }

            if (Gate::allows('view-module',205)) {

                $delete = '<button class="border-0 bg-transparent delete"
                            data-id="'.$supplier->id.'"
                            data-type="Fuel">
                            <img src="'.asset('public/assets/imgs/delete-icon.svg').'" class="px-1">
                        </button>';
            }

            $row['action'] = $edit . $delete;

            $rows[] = $row;
        }

        return DataTables::of($rows)
            ->rawColumns(['items','action'])
            ->make(true);
    }
public function wasteKraftSupplier1()
{
    $locations = SupplierLocation::select('id','name')
        ->where('status',1)
        ->where('company_id', Session::get('user_company_id'))
        ->orderBy('name')
        ->get();

    $suppliers = Supplier::with([
            'account:id,account_name',
            'latestLocationRate' => function ($q) {
                $q->join('supplier_sub_heads', 'supplier_location_rates.head_id', '=', 'supplier_sub_heads.id')
                  ->select(
                      'supplier_location_rates.id',
                      'supplier_location_rates.parent_id',
                      'location',
                      'head_rate',
                      'supplier_sub_heads.name',
                      'supplier_location_rates.head_id',
                      'r_date',
                      'supplier_sub_heads.id',
                      'supplier_location_rates.r_date'
                  )->orderBy('r_date','desc');
            }
        ])
        ->select('suppliers.id', 'account_id', 'suppliers.status')
        ->where('suppliers.company_id', Session::get('user_company_id'))
        ->join('accounts', 'suppliers.account_id', '=', 'accounts.id')
        ->orderBy('accounts.account_name', 'asc')
        ->get();

    return view('supplier.WasteKraft.index', [
        'locations' => $locations,
        'suppliers' => $suppliers
    ]);
}

public function boilerFuelSupplier1()
{
    $fuel_suppliers = FuelSupplier::with([
            'account:id,account_name',
            'itemRates' => function ($q) {
                $q->join('manage_items', 'fuel_supplier_rates.item_id', '=', 'manage_items.id')
                  ->select(
                      'price_date',
                      'price',
                      'item_id',
                      'parent_id',
                      'name'
                  )
                  ->whereIn('price_date', function ($sub) {
                      $sub->selectRaw('MAX(price_date)')
                          ->from('fuel_supplier_rates as fsr2')
                          ->whereColumn('fsr2.parent_id', 'fuel_supplier_rates.parent_id');
                  });
            }
        ])
        ->select('fuel_suppliers.id', 'account_id', 'fuel_suppliers.status')
        ->where('fuel_suppliers.company_id', Session::get('user_company_id'))
        ->join('accounts', 'fuel_suppliers.account_id', '=', 'accounts.id')
        ->orderBy('accounts.account_name', 'asc')
        ->get();

    return view('supplier.BoilerFuel.index', [
        'fuel_supplier' => $fuel_suppliers
    ]);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $supplier = Supplier::select('account_id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        $group_ids = CommonHelper::getAllChildGroupIds(3,Session::get('user_company_id'));
        array_push($group_ids, 3);
        $group_ids = array_merge($group_ids, CommonHelper::getAllChildGroupIds(11,Session::get('user_company_id'))); // Include group 11 as well
        $group_ids = array_unique($group_ids); // Ensure unique group IDs       
        array_push($group_ids, 11);
        $accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereNotIn('id',$supplier)
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$group_ids)
                              ->select('accounts.id','accounts.account_name')
                              ->orderBy('account_name')
                              ->get(); 
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
                                
        $items = ManageItems::join('sale-order-settings','manage_items.g_name','=','sale-order-settings.item_id')
                                ->select('manage_items.id','name')
                                ->where('manage_items.company_id',Session::get('user_company_id'))
                                ->where('setting_type', 'PURCHASE GROUP')
                                ->where('setting_for', 'PURCHASE ORDER')
                                 ->where('group_type', 'BOILER FUEL')
                                ->where('manage_items.status','1')
                                ->where('manage_items.delete','0')
                                ->orderBy('name')
                                ->get();
        $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->select('item_id','group_type')
                            ->get();
        return view('supplier.add_supplier',["accounts"=>$accounts,"heads"=>$heads,"items"=>$items,"group_list"=>$group_list]);
    }

    public function createWasteKraft()
    {
        
        $supplier = Supplier::select('account_id')
            ->where('company_id', Session::get('user_company_id'))
            ->pluck('account_id');

        // $group_ids = CommonHelper::getAllChildGroupIds(3, Session::get('user_company_id'));
        // array_push($group_ids, 3);
        // $group_ids = array_merge($group_ids, CommonHelper::getAllChildGroupIds(11, Session::get('user_company_id')));
        // $group_ids = array_unique($group_ids);
        // array_push($group_ids, 11);
        
         $top_groups = [3, 11];

      // Step 2: Get all child group IDs recursively
      $all_groups = [];
      foreach ($top_groups as $group_id) {
         $all_groups[] = $group_id; // include the top group itself
         $all_groups = array_merge($all_groups, CommonHelper::getAllChildGroupIds($group_id, Session::get('user_company_id')));
      }

      // Remove duplicates just in case
      $groups = array_unique($all_groups);

        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
           ->whereNotIn('id', $supplier)
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->whereIn('under_group', $groups)
            ->select('accounts.id', 'accounts.account_name')
            ->orderBy('account_name')
            ->get();

        $heads = SupplierSubHead::with('group')
            ->where('company_id', Session::get('user_company_id'))
            ->where('status', 1)
            ->orderBy('sequence')
            ->get();

        $items = []; 

        $group_list = SaleOrderSetting::join('item_groups', 'sale-order-settings.item_id', '=', 'item_groups.id')
            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'WASTE KRAFT') 
            ->select('item_id', 'group_type')
            ->get();

        return view('supplier.WasteKraft.create', [
            "accounts" => $accounts,
            "heads" => $heads,
            "items" => $items, 
            "group_list" => $group_list
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'status'  => 'required|in:1,0',
            'date'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $supplier = new Supplier;
        $supplier->account_id = $request->account;
        $supplier->status     = $request->status;
        $supplier->company_id = Session::get('user_company_id');
        $supplier->created_at = Carbon::now();
        $supplier->save();

        if ($request->has('location')) {

            foreach ($request->location as $key => $location) {

                $loc = SupplierLocation::firstOrCreate(
                    [
                        'id'         => $location,
                        'company_id' => Session::get('user_company_id'),
                    ],
                    [
                        'name'       => $location,
                        'company_id' => Session::get('user_company_id'),
                    ]
                );

                $locationId = $loc->id;

                $bonus = isset($request["bonus_".$key][0])
                        ? $request["bonus_".$key][0]
                        : 0;

                $headIds  = $request->input("head_id_".$key, []);
                $headRates = $request->input("head_rate_".$key, []);

                if (!is_array($headIds) || !is_array($headRates)) {
                    continue;
                }

                foreach ($headIds as $i => $headId) {

                    if (!empty($headRates[$i])) {

                        $rate = new SupplierLocationRates;
                        $rate->parent_id = $supplier->id;
                        $rate->account_id = $request->account;
                        $rate->location   = $locationId;
                        $rate->head_id    = $headId;
                        $rate->head_rate  = $headRates[$i];
                        $rate->bonus      = $bonus;
                        $rate->r_date     = $request->rate_date;
                        $rate->company_id = Session::get('user_company_id');
                        $rate->created_at = Carbon::now();
                        $rate->save();
                    }
                }

                $bonusRec = new SupplierBonus;
                $bonusRec->supplier_id = $supplier->id;
                $bonusRec->account_id  = $request->account;
                $bonusRec->location_id = $locationId;
                $bonusRec->bonus       = $bonus;
                $bonusRec->company_id  = Session::get('user_company_id');
                $bonusRec->created_at  = Carbon::now();
                $bonusRec->save();
            }
        }

        return redirect()->route('supplier.waste_kraft_supplier')
                        ->with('success', 'Waste Kraft Supplier added successfully');
    }


    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editWasteKraft($id)
    {
        
        $group_ids = CommonHelper::getAllChildGroupIds(3,Session::get('user_company_id'));
        array_push($group_ids, 3);
        $group_ids = array_merge($group_ids, CommonHelper::getAllChildGroupIds(11,Session::get('user_company_id'))); // Include group 11 as well
        $group_ids = array_unique($group_ids); // Ensure unique group IDs       
        array_push($group_ids, 11);
        $accounts = Accounts::where('delete', '=', '0')
                              ->where('status', '=', '1')
                              ->whereIn('company_id', [Session::get('user_company_id'),0])
                              ->whereIn('under_group',$group_ids)
                              ->select('accounts.id','accounts.account_name')
                              ->orderBy('account_name')
                              ->get();
        $supplier = Supplier::with([
                                'account:id,account_name',
                                'latestLocationRate' => function ($q) {
                                    $q->join('supplier_sub_heads', 'supplier_location_rates.head_id', '=', 'supplier_sub_heads.id')
                                    ->select(
                                        'supplier_location_rates.id',
                                        'supplier_location_rates.parent_id',
                                        'location',
                                        'head_id',
                                        'r_date',
                                        'head_rate',
                                        'supplier_sub_heads.name',
                                        'supplier_location_rates.head_id',
                                        'r_date',
                                        'supplier_sub_heads.id',
                                        'supplier_location_rates.r_date',
                                        'bonus'
                                    );
                                }
                            ])
                                ->select('id','account_id','status')
                                ->where('id',$id)
                                ->first();
                                
        $waste_group = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
                        ->where('group_type', 'WASTE KRAFT')
                        ->where('setting_type', "PURCHASE GROUP")
                        ->where('setting_for', "PURCHASE ORDER")
                        ->select('item_id')
                        ->first();
                        
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                 ->where('group_id',$waste_group->item_id)
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        // echo "<pre>";print_r($supplier->toArray());die;
        return view('supplier.WasteKraft.edit_supplier',["accounts"=>$accounts,'supplier'=>$supplier,'heads'=>$heads]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateWasteKraft(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required|exists:suppliers,account_id',
            'status' => 'required|in:1,0',
        ]);
        $companyId = Session::get('user_company_id');
        $userId    = Session::get('user_id');
        // echo "<pre>";
        // print_r($request->all());die;
        //$supplier = Supplier::find($id);
        $supplier = Supplier::with([
            'account:id,account_name',
            'locationRates',
            'bonuses'
        ])->findOrFail($id);
        $oldSnapshot = [
            'supplier' => [
                'account_id' => $supplier->account_id,
                'status'     => $supplier->status,
            ],
            'rates'   => $supplier->locationRates->toArray(),
            'bonuses' => $supplier->bonuses->toArray(),
        ];
        $supplier->account_id = $request->account;
        $supplier->status = $request->status;
        $supplier->updated_at = Carbon::now();
        if($supplier->save()){
            if($request->has('location')){
                SupplierLocationRates::where('parent_id', $supplier->id)
                    ->where('r_date', $request->rate_date)
                    ->where('company_id', Session::get('user_company_id'))
                    ->delete(); // Delete existing rates for the supplier
                    SupplierBonus::where('supplier_id', $supplier->id)->delete();
                foreach($request->location as $key => $location){
                    $loc = SupplierLocation::firstOrCreate([
                        'id' => $location,
                        'company_id' => Session::get('user_company_id')
                    ],
                    [
                        'name' => $location, // values to set if new row is created
                        'company_id' => Session::get('user_company_id')
                    ]);
                    $locationId = $loc->id;
                    $bonus = 0;
                    if(isset($request["bonus_".$location]) && isset($request["bonus_".$location][0]) && !empty($request["bonus_".$location][0])){
                        $bonus = $request["bonus_".$location][0];
                    }
                    foreach ($request["head_id_".$location] as $k => $v) {
                        if(!empty($request["head_rate_".$location][$k])){
                            $supplier_location_rates = new SupplierLocationRates;
                            $supplier_location_rates->parent_id = $supplier->id;
                            $supplier_location_rates->account_id = $request->account;
                            $supplier_location_rates->location = $locationId;
                            $supplier_location_rates->head_id = $v;
                            $supplier_location_rates->r_date = $request->rate_date;
                            $supplier_location_rates->head_rate = $request["head_rate_".$location][$k];
                            $supplier_location_rates->bonus = $bonus;
                            $supplier_location_rates->company_id = Session::get('user_company_id');
                            $supplier_location_rates->created_at = Carbon::now();
                            $supplier_location_rates->save();
                        }
                    }
                    
                    $supp_bonus = new SupplierBonus;
                    $supp_bonus->supplier_id = $supplier->id;
                    $supp_bonus->account_id = $request->account;
                    $supp_bonus->location_id = $locationId;
                    $supp_bonus->bonus = $bonus;
                    $supp_bonus->company_id = Session::get('user_company_id');
                    $supp_bonus->created_at = Carbon::now();
                    $supp_bonus->save();
                }
            }
            $supplier->load(['locationRates', 'bonuses']);
            $newSnapshot = [
                'supplier' => [
                    'account_id' => $supplier->account_id,
                    'status'     => $supplier->status,
                ],
                'rates'   => $supplier->locationRates->toArray(),
                'bonuses' => $supplier->bonuses->toArray(),
            ];

            // ðŸ”¹ LOG ACTIVITY (ONLY IF CHANGED)
            if ($oldSnapshot != $newSnapshot) {
                BusinessActivityLog::logRateChange([
                    'module_type' => 'manage_supplier',
                    'module_id'   => $supplier->id,
                    'action'      => 1, // UPDATE
                    'old_data'    => $oldSnapshot,
                    'new_data'    => $newSnapshot,
                    'action_by'   => $userId,
                    'company_id'  => $companyId,
                    'status'      => 1,
                ]);
            }
            return redirect()->route('supplier.waste_kraft_supplier')->with('success','Supplier update successfully');
        }
    }
    public function getSupplierLocation(){
        $location = SupplierLocation::select('id','name')
                                    ->where('status',1)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->orderBy('name')
                                    ->get();
        return response()->json([
            'location' => $location
        ]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyWasteKraft($id)
    {
        $supplier = Supplier::with([
            'account:id,account_name',
            'latestLocationRate',
            'bonuses'
        ])->find($id);

        if (!$supplier) {
            return redirect()->route('supplier.waste_kraft_supplier')
                            ->with('error', 'Supplier not found');
        }
            /*
        |--------------------------------------------------------------------------
        | STEP 1: Capture FULL supplier snapshot BEFORE delete
        |--------------------------------------------------------------------------
        */
        $snapshot = [
            'supplier_id' => $supplier->id,
            'account_id'  => $supplier->account_id,
            'status'      => $supplier->status,
            'rates'       => $supplier->latestLocationRate->toArray(),
            'bonuses'     => $supplier->bonuses->toArray(),
        ];

        /*
        |--------------------------------------------------------------------------
        | STEP 2: Log supplier delete
        |--------------------------------------------------------------------------
        */
        BusinessActivityLog::logRateChange([
            'module_type' => 'manage_supplier',
            'module_id'   => $supplier->id,
            'action'      => 2, // DELETE
            'old_data'    => $snapshot,
            'new_data'    => null,
            'action_by'   => $userId,
            'company_id'  => $companyId,
            'status'      => 1,
        ]);
        SupplierLocationRates::where('parent_id', $supplier->id)
            ->where('company_id', Session::get('user_company_id'))
            ->delete();

        SupplierBonus::where('supplier_id', $supplier->id)
            ->where('company_id', Session::get('user_company_id'))
            ->delete();

        $supplier->delete();

        return redirect()->route('supplier.waste_kraft_supplier')
                        ->with('success', 'Waste Kraft Supplier deleted successfully');
    }

    public function storeRateDifference(Request $request){
        $data = json_decode($request->data,true);
        $companyId = Session::get('user_company_id');
        // ðŸ”¹ STEP 1: Get OLD data before delete
        $oldData = SupplierDifferenceRate::where('company_id', $companyId)
                ->get()
                ->keyBy('head_id');
        SupplierDifferenceRate::where('company_id',Session::get('user_company_id'))->delete();
        foreach ($data as $key => $value) {
            $headId    = $value['head_id'];
            $newRate   = $value['head_rate'];
            $newAction = $value['head_action'];
            $oldRate   = $oldData[$headId]->head_rate   ?? null;
            $oldAction = $oldData[$headId]->head_action ?? null;
            // âœ… LOG ONLY IF ACTUALLY CHANGED
            if (
                $oldRate !== null &&
                (
                    (float)$oldRate !== (float)$newRate ||
                    (int)$oldAction !== (int)$newAction
                )
            ) {
                BusinessActivityLog::logRateChange([
                    'module_type' => 'rate_difference',
                    'module_id'   => $headId,
                    'action'      => 1,
                    'old_data'    => [
                        'head_id'    => $headId,
                        'old_rate'   => $oldRate,
                        'old_action' => $oldAction,
                    ],
                    'new_data'    => [
                        'new_rate'   => $newRate,
                        'new_action' => $newAction,
                    ],
                    'action_by'   => $userId,
                    'company_id'  => $companyId,
                    'status'      => 1,
                ]);
            }
            $diff_rate = new SupplierDifferenceRate;
            $diff_rate->head_id = $value['head_id'];
            $diff_rate->head_rate = $value['head_rate'];
            $diff_rate->head_action = $value['head_action'];
            $diff_rate->company_id = Session::get('user_company_id');
            $diff_rate->created_at = Carbon::now();
            $diff_rate->save();
        }
        return response()->json([
            'status' => 1
        ]);
    }
    public function storeSupplierLocation(Request $request){
        $location = SupplierLocation::updateOrCreate([
                            'id' => $request->location_edit_id,
                            'company_id' => Session::get('user_company_id')
                    ],
                    [
                        'name' => $request->location_name,// values to set if new row is created
                        'company_id' => Session::get('user_company_id'),
                        'status' => $request->status
                    ]);
        return response()->json([
            'status' => 1
        ]);
    }
    public function getSupplierBonus(Request $request){
        $bonus = SupplierBonus::where('supplier_bonuses.company_id',Session::get('user_company_id'))
                                ->join('accounts','supplier_bonuses.account_id','=','accounts.id')
                                ->join('supplier_locations','supplier_bonuses.location_id','=','supplier_locations.id')
                                ->select('supplier_locations.name','account_name','bonus','account_id')
                                ->get();
        return response()->json([
            'bonus' => $bonus
        ]);
    }
    public function resetSupplierBonus(Request $request){
        $accountIds = json_decode($request->supplier, true);
        // ðŸ”¹ STEP 1: Fetch OLD bonuses before delete
        $oldBonuses = SupplierBonus::where('company_id', $companyId)
                        ->whereIn('account_id', $accountIds)
                        ->get();

        // ðŸ”¹ STEP 2: Log each bonus reset
        foreach ($oldBonuses as $bonus) {
            BusinessActivityLog::logRateChange([
                'module_type' => 'supplier_bonus',
                'module_id'   => $bonus->account_id,
                'action'      => 1,
                'old_data'    => [
                    'supplier_id' => $bonus->supplier_id,
                    'account_id'  => $bonus->account_id,
                    'location_id' => $bonus->location_id,
                    'old_bonus'   => $bonus->bonus,
                ],
                'new_data'    => [
                    'new_bonus' => 0,
                ],
                'action_by'   => $userId,
                'company_id'  => $companyId,
                'status'      => 1,
            ]);
        }
        $delete = SupplierBonus::where('company_id',Session::get('user_company_id'))
                                ->whereIn('account_id',json_decode($request->supplier,true))
                                ->delete();
        if($delete){
            return response()->json([
                'status' => 1
            ]);
        }        
    }
    
}
