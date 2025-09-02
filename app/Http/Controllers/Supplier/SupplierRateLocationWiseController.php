<?php

namespace App\Http\Controllers\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use Carbon\Carbon;
use App\Models\SupplierLocation;
use App\Models\SupplierRateLocationWise;
use App\Models\SupplierLocationRates;
use App\Models\SupplierSubHead;
use App\Models\SupplierDifferenceRate;
class SupplierRateLocationWiseController extends Controller
{
    public function manageSupplierRate($date=null)
    {
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        $current_date = date('Y-m-d');
        $supplier_rate = SupplierRateLocationWise::select('location_id','head_id','head_rate','rate_date')
                                                    ->where('company_id',Session::get('user_company_id'))
                                                    ->when($date, function($q)use($date){
                                                        $q->where('rate_date',$date);
                                                    })
                                                    ->when($date==null, function($q)use($current_date){
                                                        $q->where('rate_date','<=',$current_date);
                                                    })
                                                    ->get();
                                                    if($date){
                                                        $current_date = $date;
                                                    }
        $advance_rate = SupplierRateLocationWise::select('rate_date')
                                                    ->where('rate_date','>',$current_date)
                                                     ->distinct()
                                                    ->orderBy('rate_date')
                                                    ->pluck('rate_date');
        $result = [];
        $rate_date = $date;
        foreach ($supplier_rate as $row) {
            $key = $row->location_id . "_" . $row->head_id;
            $result[$key] = $row->head_rate;
            $rate_date = $row->rate_date;
        }
        $all_supplier_rate = SupplierRateLocationWise::join('supplier_locations','supplier_rate_location_wises.location_id','=','supplier_locations.id')
                                                    ->join('supplier_sub_heads','supplier_rate_location_wises.head_id','=','supplier_sub_heads.id')
                                                    ->select('supplier_locations.name','supplier_sub_heads.name as head_name','head_rate','rate_date')
                                                    ->where('supplier_rate_location_wises.company_id',Session::get('user_company_id'))
                                                    
                                                    ->orderBy('rate_date','desc')
                                                    ->orderBy('supplier_sub_heads.sequence')
                                                    ->get();
        $grouped = [];
        foreach ($all_supplier_rate->toArray() as $row) {
            $grouped[$row['rate_date']][] = $row;
        }
        // echo "<pre>";
        // print_r($grouped);die;
        $difference_rate = SupplierDifferenceRate::select('head_id','head_rate','head_action')->where('company_id',Session::get('user_company_id'))->get();
        return view('supplier.manage_supplier_rate',["locations"=>$location,"supplier_rates"=>$result,"heads"=>$heads,"rate_date"=>$rate_date,"all_rate"=>$grouped,"difference_rate"=>$difference_rate,"advance_rate"=>$advance_rate]);
    }
    public function storeSupplierRate(Request $request)
    {
       
        SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))
                                ->where('rate_date',$request->date)
                                ->delete();
        SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))                                
                                ->update(['status'=>0]);
        foreach ($request->location_id as $key => $value) {
            foreach ($request["head_id_".$value] as $k => $v) {
                $supplier_rate = new SupplierRateLocationWise;
                $supplier_rate->location_id = $request->location_id[$key];
                $supplier_rate->head_id = $v;
                $supplier_rate->head_rate = $request["head_value_".$value][$k];
                $supplier_rate->rate_date = $request->date;
                $supplier_rate->company_id = Session::get('user_company_id');
                $supplier_rate->status = 1;
                $supplier_rate->created_by = Session::get('user_id');
                $supplier_rate->created_at = Carbon::now();
                if($supplier_rate->save()){
                    // SupplierLocationRates::where('location',$request->location_id[$key])->update([
                    //     'kraft_i_rate' => $request->kraft_i_rate[$key],
                    //     'kraft_ii_rate' => $request->kraft_ii_rate[$key],
                    //     'duplex_rate' => $request->duplex_rate[$key],
                    //     'poor_rate' => $request->poor_rate[$key],
                    //     'updated_at' => Carbon::now(),
                    // ]);
                }
            }
            
        }
        return redirect()->back()->with('success','Supplier Rate Added Successfully');
    }
    
    
}
