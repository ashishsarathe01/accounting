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
                            ->select('id', 'account_id', 'status')
                            ->where('company_id', Session::get('user_company_id'))
                            ->get();

        // echo "<pre>";print_r($supplier->toArray());die;
        
        return view('supplier.index',["locations"=>$location,'suppliers'=>$supplier]);
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
        return view('supplier.add_supplier',["accounts"=>$accounts,"heads"=>$heads]);
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
            'account' => 'required|exists:suppliers,account_id',
            'status' => 'required|in:1,0',
        ]);
        $supplier = new Supplier;
        $supplier->account_id = $request->account;
        $supplier->status = $request->status;
        $supplier->company_id = Session::get('user_company_id');
        $supplier->created_at = Carbon::now();
        if($supplier->save()){
            if($request->has('location')){
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
                    if(isset($request["bonus_".$key]) && isset($request["bonus_".$key][0]) && !empty($request["bonus_".$key][0])){
                        $bonus = $request["bonus_".$key][0];
                    } 
                    foreach ($request["head_id_".$key] as $k => $v) {
                        if(!empty($request["head_rate_".$key][$k])){
                            $supplier_location_rates = new SupplierLocationRates;
                            $supplier_location_rates->parent_id = $supplier->id;
                            $supplier_location_rates->account_id = $request->account;
                            $supplier_location_rates->location = $locationId;
                            $supplier_location_rates->head_id = $v;
                            $supplier_location_rates->head_rate = $request["head_rate_".$key][$k];
                            $supplier_location_rates->bonus = $bonus;
                            $supplier_location_rates->r_date = $request["rate_date"];
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
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        // echo "<pre>";print_r($supplier->toArray());die;
        return view('supplier.edit_supplier',["accounts"=>$accounts,'supplier'=>$supplier,'heads'=>$heads]);
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
        $validator = Validator::make($request->all(), [
            'account' => 'required|exists:suppliers,account_id',
            'status' => 'required|in:1,0',
        ]);
        $supplier = Supplier::find($id);
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
                    if(isset($request["bonus_".$key]) && isset($request["bonus_".$key][0]) && !empty($request["bonus_".$key][0])){
                        $bonus = $request["bonus_".$key][0];
                    }
                    foreach ($request["head_id_".$key] as $k => $v) {
                        if(!empty($request["head_rate_".$key][$k])){
                            $supplier_location_rates = new SupplierLocationRates;
                            $supplier_location_rates->parent_id = $supplier->id;
                            $supplier_location_rates->account_id = $request->account;
                            $supplier_location_rates->location = $locationId;
                            $supplier_location_rates->head_id = $v;
                            $supplier_location_rates->r_date = $request->rate_date;
                            $supplier_location_rates->head_rate = $request["head_rate_".$key][$k];
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
            return redirect()->route('supplier.index')->with('success','Supplier update successfully');
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
        $supplier = Supplier::find($id);
        if($supplier){
            SupplierLocationRates::where('parent_id', $supplier->id)
                ->where('company_id', Session::get('user_company_id'))
                ->delete(); // Delete existing rates for the supplier
            $supplier->delete();
            return redirect()->route('supplier.index')->with('success','Supplier deleted successfully');
        }else{
            return redirect()->route('supplier.index')->with('error','Supplier not found');
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
    public function storeRateDifference(Request $request){
        $data = json_decode($request->data,true);
        SupplierDifferenceRate::where('company_id',Session::get('user_company_id'))->delete();
        foreach ($data as $key => $value) {
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
