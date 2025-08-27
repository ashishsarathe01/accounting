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
        $supplier = Supplier::with(['account','locationRates'])
                                ->select('id','account_id','status')
                                ->where('company_id',Session::get('user_company_id'))
                                //->orderBy('name')
                                ->get();
        
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
        return view('supplier.add_supplier',["accounts"=>$accounts]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
                    if(isset($request->kraft_i_rate[$key]) && isset($request->kraft_ii_rate[$key]) && isset($request->duplex_rate[$key]) && isset($request->poor_rate[$key])){
                        $location = SupplierLocation::firstOrCreate([
                            'id' => $location,
                            'company_id' => Session::get('user_company_id')
                        ],
                        [
                            'name' => $location, // values to set if new row is created
                            'company_id' => Session::get('user_company_id')
                        ]);

                        $locationId = $location->id;
                        $supplier_location_rates = new SupplierLocationRates;
                        $supplier_location_rates->parent_id = $supplier->id;
                        $supplier_location_rates->account_id = $request->account;
                        $supplier_location_rates->location = $locationId;
                        $supplier_location_rates->kraft_i_rate = $request->kraft_i_rate[$key];
                        $supplier_location_rates->kraft_ii_rate = $request->kraft_ii_rate[$key];
                        $supplier_location_rates->duplex_rate = $request->duplex_rate[$key];
                        $supplier_location_rates->poor_rate = $request->poor_rate[$key];
                        $supplier_location_rates->company_id = Session::get('user_company_id');
                        $supplier_location_rates->created_at = Carbon::now();
                        $supplier_location_rates->save();
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
        $supplier = Supplier::with(['locationRates'])
                                ->select('id','account_id','status')
                                ->where('id',$id)
                                //->orderBy('name')
                                ->first();
        return view('supplier.edit_supplier',["accounts"=>$accounts,'supplier'=>$supplier]);
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
                    ->where('company_id', Session::get('user_company_id'))
                    ->delete(); // Delete existing rates for the supplier
                foreach($request->location as $key => $location){
                    if(isset($request->kraft_i_rate[$key]) && isset($request->kraft_ii_rate[$key]) && isset($request->duplex_rate[$key]) && isset($request->poor_rate[$key])){
                        $location = SupplierLocation::firstOrCreate([
                            'id' => $location,
                            'company_id' => Session::get('user_company_id')
                        ],
                        [
                            'name' => $location, // values to set if new row is created
                            'company_id' => Session::get('user_company_id')
                        ]);
                        $locationId = $location->id;
                        $supplier_location_rates = new SupplierLocationRates;
                        $supplier_location_rates->parent_id = $supplier->id;
                        $supplier_location_rates->account_id = $request->account;
                        $supplier_location_rates->location = $locationId;
                        $supplier_location_rates->kraft_i_rate = $request->kraft_i_rate[$key];
                        $supplier_location_rates->kraft_ii_rate = $request->kraft_ii_rate[$key];
                        $supplier_location_rates->duplex_rate = $request->duplex_rate[$key];
                        $supplier_location_rates->poor_rate = $request->poor_rate[$key];
                        $supplier_location_rates->company_id = Session::get('user_company_id');
                        $supplier_location_rates->created_at = Carbon::now();
                        $supplier_location_rates->save();
                    }
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
}
