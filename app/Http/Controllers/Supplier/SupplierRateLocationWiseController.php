<?php

namespace App\Http\Controllers\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use Carbon\Carbon;
use App\Models\SupplierLocation;
use App\Models\SupplierRateLocationWise;
use App\Models\SupplierLocationRates;
class SupplierRateLocationWiseController extends Controller
{
    public function manageSupplierRate()
    {
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        $supplier_rate = SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))->get();
        return view('supplier.manage_supplier_rate',["locations"=>$location,"supplier_rates"=>$supplier_rate]);
    }
    public function storeSupplierRate(Request $request)
    {
        SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))->delete();
        foreach ($request->location_id as $key => $value) {
            $supplier_rate = new SupplierRateLocationWise;
            $supplier_rate->location_id = $request->location_id[$key];
            $supplier_rate->kraft_i = $request->kraft_i_rate[$key];
            $supplier_rate->kraft_ii = $request->kraft_ii_rate[$key];
            $supplier_rate->duplex = $request->duplex_rate[$key];
            $supplier_rate->poor = $request->poor_rate[$key];
            $supplier_rate->company_id = Session::get('user_company_id');
            $supplier_rate->status = 1;
            $supplier_rate->created_by = Session::get('user_id');
            $supplier_rate->created_at = Carbon::now();
            if($supplier_rate->save()){
                SupplierLocationRates::where('location',$request->location_id[$key])->update([
                    'kraft_i_rate' => $request->kraft_i_rate[$key],
                    'kraft_ii_rate' => $request->kraft_ii_rate[$key],
                    'duplex_rate' => $request->duplex_rate[$key],
                    'poor_rate' => $request->poor_rate[$key],
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        return redirect()->back()->with('success','Supplier Rate Added Successfully');
    }
    
    
}
