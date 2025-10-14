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
use App\Models\SupplierBonus;
use App\Models\Supplier;
use App\Models\ManageItems;
use App\Models\FuelItemRates;
use App\Models\SaleOrderSetting;
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
        $latestDate = SupplierRateLocationWise::where('rate_date','<=',$current_date)
                                                ->where('company_id',Session::get('user_company_id'))
                                                ->max('rate_date');
                                                $latestDate1 = "";
        if($date!="" && $date!=null){
            $latestDate1 = SupplierRateLocationWise::where('rate_date','<=',$date)
                                                ->where('company_id',Session::get('user_company_id'))
                                                ->max('rate_date');
        }
                                        
        $supplier_rate = SupplierRateLocationWise::select('location_id','head_id','head_rate','rate_date')
                                                    ->where('company_id',Session::get('user_company_id'))
                                                    ->when($date, function($q)use($latestDate1){
                                                        $q->where('rate_date',$latestDate1);
                                                    })
                                                    ->when($date==null, function($q)use($latestDate){
                                                        $q->where('rate_date','=',$latestDate);
                                                    })
                                                    ->orderBy('rate_date','desc')
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
        if($date!="" && $date!=null){
            if($latestDate1!=$date){
                $rate_date = $date;
            }
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
        //Fuel Code....
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
        $current_date = date('Y-m-d');
        $latestDate = FuelItemRates::where('item_price_date','<=',$current_date)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->max('item_price_date');
        $latestDate1 = "";
        if($date!="" && $date!=null){
            $latestDate1 = FuelItemRates::where('item_price_date','<=',$date)
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->max('item_price_date');
        }                                        
        $fuel_item_rate = FuelItemRates::select('item_id','item_price','item_price_date')
                                                    ->where('company_id',Session::get('user_company_id'))
                                                    ->when($date, function($q)use($latestDate1){
                                                        $q->where('item_price_date',$latestDate1);
                                                    })
                                                    ->when($date==null, function($q)use($latestDate){
                                                        $q->where('item_price_date','=',$latestDate);
                                                    })
                                                    ->orderBy('item_price_date','desc')
                                                    ->get();
        $all_fuel_item_rate = FuelItemRates::join('manage_items','fuel_item_rates.item_id','=','manage_items.id')
                                                    ->select('manage_items.name as item_name','item_price','item_price_date')
                                                    ->where('fuel_item_rates.company_id',Session::get('user_company_id'))                                                    
                                                    ->orderBy('item_price_date','desc')
                                                    ->get();
        $fuel_grouped = [];
        foreach ($all_fuel_item_rate->toArray() as $row) {
            $fuel_grouped[$row['item_price_date']][] = $row;
        }
        $fuelresult = [];
        $fuel_date = $date;
        foreach ($fuel_item_rate as $row){
            $key = $row->item_id;
            $fuelresult[$key] = $row->item_price;
            $fuel_date = $row->item_price_date;
        }
        if($date!="" && $date!=null){
            if($latestDate1!=$date){
                $fuel_date = $date;
            }
        }
        //echo "<pre>";print_r($fuelresult);die;
        $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->select('item_id','group_type')
                            ->get();
        return view('supplier.manage_supplier_rate',["locations"=>$location,"supplier_rates"=>$result,"heads"=>$heads,"rate_date"=>$rate_date,"all_rate"=>$grouped,"difference_rate"=>$difference_rate,"advance_rate"=>$advance_rate,"items"=>$items,"all_fuel_item_rate"=>$fuel_grouped,"fuel_date"=>$fuel_date,"fuelresult"=>$fuelresult,"group_list"=>$group_list]);
    }
    public function storeSupplierRate(Request $request)
    {
        SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))
                                ->where('rate_date',$request->date)
                                ->delete();
        SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))                                
                                ->update(['status'=>0]);
        $save_status = 0;
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
                    $save_status = 1;
                }
            }            
        }
        if($save_status==1){
            $supplier = Supplier::where('company_id',Session('user_company_id'))->get();
            foreach ($supplier as $k2 => $v2) {                
                foreach ($request->location_id as $key => $value) {
                    $check_supp = SupplierLocationRates::where('account_id',$v2->account_id)
                                         ->where('location',$request->location_id[$key])
                                        ->where('status',1)
                                        ->count();
                    if($check_supp==0){
                        continue;
                    }
                    SupplierLocationRates::where('r_date',$request->date)
                                        ->where('account_id',$v2->account_id)
                                        ->where('location',$request->location_id[$key])
                                        ->where('status',1)
                                        ->delete();
                    foreach ($request["head_id_".$value] as $k => $v) {
                        $bonus = 0;
                        $head_rate = $request["head_value_".$value][$k];
                        $supp_bonus = SupplierBonus::where('account_id',$v2->account_id)->where('location_id',$request->location_id[$key])->first();
                        if($supp_bonus){
                            $bonus = $supp_bonus->bonus;
                            $head_rate = $head_rate + $bonus;
                        }
                        $supplier_location_rates = new SupplierLocationRates;
                        $supplier_location_rates->parent_id = $v2->id;
                        $supplier_location_rates->account_id = $v2->account_id;
                        $supplier_location_rates->location = $request->location_id[$key];
                        $supplier_location_rates->head_id = $v;
                        $supplier_location_rates->head_rate = $head_rate;
                        $supplier_location_rates->bonus = $bonus;
                        $supplier_location_rates->r_date = $request->date;
                        $supplier_location_rates->company_id = Session::get('user_company_id');
                        $supplier_location_rates->created_at = Carbon::now();
                        $supplier_location_rates->save();
                    }
                }
            }
        }
        return redirect()->back()->with('success','Supplier Rate Added Successfully');
    }
    public function rateByLocation(Request $request){
        $current_date = date('Y-m-d',strtotime($request->date));
        $latestDate = SupplierRateLocationWise::where('location_id', $request->location_id)
                                                ->where('rate_date', '<=', $current_date)
                                                ->max('rate_date');
        $rate = SupplierRateLocationWise::select('head_id','head_rate','rate_date')
                                ->where('location_id',$request->location_id)
                                ->where('rate_date',$latestDate)
                                ->get();
        return response()->json([
            'rate' => $rate,
            'latestDate'=> $latestDate
        ]);
    }
    
}
