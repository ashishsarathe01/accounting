<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use DB;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\FuelSupplier;
use App\Models\FuelSupplierRate;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\FuelItemRates;
class FuelSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'status' => 'required|in:1,0',
        ]);
        $supplier = new FuelSupplier;
        $supplier->account_id = $request->account;
        $supplier->status = $request->status;
        $supplier->company_id = Session::get('user_company_id');
        $supplier->created_by = Session::get('user_id');
        $supplier->created_at = Carbon::now();
        if($supplier->save()){
            if($request->has('item')){
                foreach($request->item as $key => $item){
                    if(isset($request["item_price"][$key]) && !empty($request["item_price"][$key])){
                        $supplier_item_rates = new FuelSupplierRate;
                        $supplier_item_rates->parent_id = $supplier->id;
                        $supplier_item_rates->account_id = $request->account;
                        $supplier_item_rates->item_id = $item;
                        $supplier_item_rates->price = $request["item_price"][$key];
                        $supplier_item_rates->price_date = $request["fule_date"];
                        $supplier_item_rates->company_id = Session::get('user_company_id');
                        $supplier_item_rates->created_at = Carbon::now();
                        $supplier_item_rates->save();
                    }
                }
            }
            return redirect()->route('supplier.index')->with('success','Supplier added successfully');
        }
    }

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
    public function edit($id)
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
                                    )->whereIn('price_date', function ($sub) {
                                $sub->selectRaw('MAX(price_date)')
                                    ->from('fuel_supplier_rates as fsr2')
                                    ->whereColumn('fsr2.parent_id', 'fuel_supplier_rates.parent_id');
                            });
                                }
                            ])
                            ->select('fuel_suppliers.id', 'account_id', 'fuel_suppliers.status')
                            ->where('fuel_suppliers.company_id', Session::get('user_company_id'))
                            ->where('fuel_suppliers.id',$id)
                            ->first();
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
        // echo "<pre>";print_r($supplier->toArray());die;
        return view('supplier.boilerfuel/edit_fuel_supplier',["accounts"=>$accounts,'fuel_supplier'=>$fuel_supplier,'items'=>$items]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'status' => 'required|in:1,0',
        ]);
        $supplier = FuelSupplier::find($id);
        $supplier->account_id = $request->account;
        $supplier->status = $request->status;
        $supplier->updated_at = Carbon::now();
        if($supplier->save()){
            if($request->has('item')){                
                FuelSupplierRate::where('parent_id', $supplier->id)
                    ->where('price_date', $request->fule_date)
                    ->where('company_id', Session::get('user_company_id'))
                    ->delete();
                foreach($request->item as $key => $item){
                    if(isset($request["item_price"][$key]) && !empty($request["item_price"][$key])){
                        $supplier_item_rates = new FuelSupplierRate;
                        $supplier_item_rates->parent_id = $supplier->id;
                        $supplier_item_rates->account_id = $request->account;
                        $supplier_item_rates->item_id = $item;
                        $supplier_item_rates->price = $request["item_price"][$key];
                        $supplier_item_rates->price_date = $request["fule_date"];
                        $supplier_item_rates->company_id = Session::get('user_company_id');
                        $supplier_item_rates->created_at = Carbon::now();
                        $supplier_item_rates->save();
                    }
                }
            }
            return redirect()->route('supplier.index')->with('success','Supplier updated successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = FuelSupplier::find($id);
        if($supplier){
            FuelSupplierRate::where('parent_id', $supplier->id)
                ->where('company_id', Session::get('user_company_id'))
                ->delete(); // Delete existing rates for the supplier
            $supplier->delete();
            return redirect()->route('supplier.index')->with('success','Supplier deleted successfully');
        }else{
            return redirect()->route('supplier.index')->with('error','Supplier not found');
        }
    }
    public function storeFuelItemRate(Request $request)
    {
       
        FuelItemRates::where('company_id',Session::get('user_company_id'))
                                ->where('item_price_date',$request->fuel_date)
                                ->delete();        
        $save_status = 0;
        foreach ($request->item_id as $key => $value) {
            if($request["item_price"][$key]!=""){
                $supplier_rate = new FuelItemRates;
                $supplier_rate->item_id = $value;
                $supplier_rate->item_price = $request["item_price"][$key];
                $supplier_rate->item_price_date = $request->fuel_date;
                $supplier_rate->company_id = Session::get('user_company_id');
                $supplier_rate->status = 1;
                $supplier_rate->created_by = Session::get('user_id');
                $supplier_rate->created_at = Carbon::now();
                if($supplier_rate->save()){
                    $save_status = 1;
                }
            }
            
        }
        if($save_status==1){
            $supplier = FuelSupplier::where('company_id',Session('user_company_id'))->get();
            foreach ($supplier as $k2 => $v2) {
                foreach ($request->item_id as $key => $value) {
                    if($request["item_price"][$key]!=""){
                        $check_supp = FuelSupplierRate::where('account_id',$v2->account_id)
                                         ->where('item_id',$value)
                                        ->where('status',1)
                                        ->count();
                        if($check_supp==0){
                            continue;
                        }
                        FuelSupplierRate::where('price_date',$request->fuel_date)
                                            ->where('account_id',$v2->account_id)
                                            ->where('item_id',$value)
                                            ->where('status',1)
                                            ->delete();
                        $supplier_location_rates = new FuelSupplierRate;
                        $supplier_location_rates->parent_id = $v2->id;
                        $supplier_location_rates->account_id = $v2->account_id;
                        $supplier_location_rates->item_id = $value;
                        $supplier_location_rates->price = $request["item_price"][$key];
                        $supplier_location_rates->price_date = $request->fuel_date;
                        $supplier_location_rates->company_id = Session::get('user_company_id');
                        $supplier_location_rates->created_at = Carbon::now();
                        $supplier_location_rates->save();  

                    }                                      
                }
            }
        }
        return redirect()->back()->with('success','Supplier Rate Added Successfully');
    }
    public function fuelPriceByItem(Request $request){
        $current_date = date('Y-m-d',strtotime($request->date));
        $latestDate = FuelItemRates::where('item_id', $request->item_id)
                                                ->where('item_price_date', '<=', $current_date)
                                                ->max('item_price_date');
        $item_rate = FuelItemRates::select('item_price')
                                ->where('item_id',$request->item_id)
                                ->where('item_price_date',$latestDate)
                                ->first();

        $supplier_rate = FuelSupplierRate::select('price')
                                ->where('item_id',$request->item_id)
                                ->where('price_date',$latestDate)
                                ->where('account_id',$request->account_id)
                                ->first();
        if($supplier_rate && $item_rate->item_price!=$supplier_rate->price){
            $item_rate->item_price = $supplier_rate->price;
        }
        return response()->json([
            'rate' => $item_rate,
            'latestDate'=> $latestDate
        ]);
    }
    public function getSupplierRateByItem(Request $request)
    {
        $supplier_max_date = FuelSupplierRate::where('company_id',Session::get('user_company_id'))
                                        ->where('item_id',$request->item_id)
                                        ->where('account_id',$request->account_id)
                                        ->where('price_date','<=',$request->date)
                                        ->max('price_date');
        $max_date = FuelItemRates::where('company_id',Session::get('user_company_id'))
                                        ->where('item_id',$request->item_id)
                                        ->where('item_price_date','<=',$request->date)
                                        ->max('item_price_date');
        if($max_date>$supplier_max_date){
            $rate = FuelItemRates::select('item_price')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('item_price_date','<=',$request->date)
                                        ->where('item_id',$request->item_id)
                                        ->orderBy('item_price_date','desc')
                                        ->first();
        }else{
            $rate = FuelSupplierRate::select('price as item_price')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('account_id',$request->account_id)
                                        ->where('price_date','<=',$request->date)
                                        ->where('item_id',$request->item_id)
                                        ->orderBy('price_date','desc')
                                        ->first();
        }
        
        return response()->json($rate);
    }
}
