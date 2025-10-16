<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierLocation;
use App\Models\SupplierLocationRates;
use App\Models\SupplierPurchaseReport;
use App\Models\Accounts;
use App\Models\SupplierSubHead;
use App\Models\ItemGroups;
use App\Models\SupplierPurchaseVehicleDetail;
use App\Models\SupplierRateLocationWise;
use App\Models\ManageItems;
use App\Models\SaleOrderSetting;
use App\Models\FuelSupplierRate;
use App\Models\FuelSupplier;
class SupplierPurchaseController extends Controller
{
    public function manageSupplierPurchase()
    {
        $purchases = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name,g_name as group_id',
                    'units:id,name',
                    'parameterColumnInfo' => function ($q2) {
                        $q2->leftjoin('item_paremeter_list as param1','purchase_parameter_info.parameter1_id','=','param1.id');
                        $q2->leftjoin('item_paremeter_list as param2','purchase_parameter_info.parameter2_id','=','param2.id');
                        $q2->leftjoin('item_paremeter_list as param3','purchase_parameter_info.parameter3_id','=','param3.id');
                        $q2->leftjoin('item_paremeter_list as param4','purchase_parameter_info.parameter4_id','=','param4.id');
                        $q2->leftjoin('item_paremeter_list as param5','purchase_parameter_info.parameter5_id','=','param5.id');
                        $q2->select('purchase_parameter_info.id', 'purchase_desc_row_id','parameter1_id','parameter2_id','parameter3_id','parameter4_id','parameter5_id','parameter1_value','parameter2_value','parameter3_value','parameter4_value','parameter5_value','param1.paremeter_name as paremeter_name1','param2.paremeter_name as paremeter_name2','param3.paremeter_name as paremeter_name3','param4.paremeter_name as paremeter_name4','param5.paremeter_name as paremeter_name5');
                    }
                ]);
                $query->select('id', 'goods_discription', 'qty', 'purchase_id', 'unit','price');
            },
            'account:id,account_name'
        ])->where('company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('status','1')
                                ->where('supplier_action_status','0')
                                ->orderBy('date','asc')
                                ->get();
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        //echo "<pre>"; print_r($purchases->toArray()); die;
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        $compete_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',1)
                ->count();
        $approval_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',2)
                ->count();
        return view('supplier.manage_supplier_purchase', ["purchases" => $purchases,"locations"=>$location,'heads'=>$heads,"compete_purchase"=>$compete_purchase,"approval_purchase"=>$approval_purchase]);
    }
    public function getSupplierRateByLocation(Request $request)
    {
        $supplier_max_date = SupplierLocationRates::where('company_id',Session::get('user_company_id'))
                                        ->where('location',$request->location)
                                        ->where('account_id',$request->account_id)
                                        ->where('r_date','<=',$request->date)
                                        ->max('r_date');
        $max_date = SupplierRateLocationWise::where('company_id',Session::get('user_company_id'))
                                        ->where('location_id',$request->location)
                                        ->where('rate_date','<=',$request->date)
                                        ->max('rate_date');
        if($max_date>$supplier_max_date){
            $rate = SupplierRateLocationWise::select('head_id','head_rate')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('rate_date','<=',$request->date)
                                        ->where('location_id',$request->location)
                                        ->get();
        }else{
            $rate = SupplierLocationRates::select('head_id','head_rate')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('account_id',$request->account_id)
                                        ->where('r_date','<=',$request->date)
                                        ->where('location',$request->location)
                                        ->get();
        }
        
        return response()->json($rate);
    }
    public function storeSupplierPurchaseReport(Request $request)
    {
       
        SupplierPurchaseReport::where('company_id',Session::get('user_company_id'))
                                ->where('purchase_id',$request->purchase_id)
                                ->delete();
        $purchase_vehicle = SupplierPurchaseVehicleDetail::find($request->purchase_id);
        if($purchase_vehicle->status==0){
            $purchase_vehicle->status = 1;
        }else if($purchase_vehicle->status==1 && !empty($purchase_vehicle->map_purchase_id) && ($purchase_vehicle->image_1!='' || $purchase_vehicle->image_2!='' || $purchase_vehicle->image_2!='')){
            $purchase_vehicle->status = 2;
        }else if($purchase_vehicle->status==3){
            $purchase_vehicle->status = 2;
            $purchase_vehicle->reapproval = 1;
        }
        $purchase_vehicle->account_id = $request->account_id;
        $purchase_vehicle->entry_date = $request->entry_date;
        $purchase_vehicle->group_id = $request->group_id;
        $purchase_vehicle->vehicle_no = $request->vehicle_no;

        $purchase_vehicle->difference_total_amount = $request->difference_total_amount;
        $purchase_vehicle->tare_weight = $request->tare_weight;
        if($request->group_type=="waste_craft"){
            $purchase_vehicle->location = $request->location;
        }else{
            $purchase_vehicle->contract_item_id = $request->item_id;
        }        
        $purchase_vehicle->voucher_no = $request->voucher_no;
        $purchase_vehicle->processed_by = Session::get('user_id');
        $purchase_vehicle->save();
        $data = json_decode($request->data,true);        
        foreach ($data as $key => $value) {
            if($request->group_type=="waste_craft"){
                $report = new SupplierPurchaseReport;
                $report->purchase_id = $request->purchase_id;
                $report->head_id = $value['id'];
                $report->head_qty = $value['qty'];
                $report->head_bill_rate = $value['bill_rate'];
                $report->head_contract_rate = $value['contract_rate'];
                $report->head_difference_amount = $value['difference_amount'];;
                $report->company_id = Session::get('user_company_id');
                $report->status = 1;
                $report->created_by = Session::get('user_id');
                $report->created_at = Carbon::now();
                $report->save();
            }else{
                if($value['id']=='cut' || $value['id']=='short_weight'){
                    $report = new SupplierPurchaseReport;
                    $report->purchase_id = $request->purchase_id;
                    $report->head_id = $value['id'];
                    $report->head_qty = $value['qty'];
                    $report->head_bill_rate = $value['bill_rate'];
                    $report->head_contract_rate = $value['contract_rate'];
                    $report->head_difference_amount = $value['difference_amount'];;
                    $report->company_id = Session::get('user_company_id');
                    $report->status = 1;
                    $report->created_by = Session::get('user_id');
                    $report->created_at = Carbon::now();
                    $report->save();
                }
                
            }
            
        }
        $response = array(
            'status' => true,
            'message' => 'Supplier Purchase Report Added Successfully.'
        );
        return json_encode($response);
    }
    public function completeSupplierPurchase($id=null)
    {
        $purchases = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name,g_name as group_id',
                    'units:id,name',
                    'parameterColumnInfo' => function ($q2) {
                        $q2->leftjoin('item_paremeter_list as param1','purchase_parameter_info.parameter1_id','=','param1.id');
                        $q2->leftjoin('item_paremeter_list as param2','purchase_parameter_info.parameter2_id','=','param2.id');
                        $q2->leftjoin('item_paremeter_list as param3','purchase_parameter_info.parameter3_id','=','param3.id');
                        $q2->leftjoin('item_paremeter_list as param4','purchase_parameter_info.parameter4_id','=','param4.id');
                        $q2->leftjoin('item_paremeter_list as param5','purchase_parameter_info.parameter5_id','=','param5.id');
                        $q2->select('purchase_parameter_info.id', 'purchase_desc_row_id','parameter1_id','parameter2_id','parameter3_id','parameter4_id','parameter5_id','parameter1_value','parameter2_value','parameter3_value','parameter4_value','parameter5_value','param1.paremeter_name as paremeter_name1','param2.paremeter_name as paremeter_name2','param3.paremeter_name as paremeter_name3','param4.paremeter_name as paremeter_name4','param5.paremeter_name as paremeter_name5');
                    }
                ]);
                $query->select('id', 'goods_discription', 'qty', 'purchase_id', 'unit');
            },
            'account:id,account_name'
        ])
        ->join('supplier_purchase_reports','purchases.id','=','supplier_purchase_reports.purchase_id')
        ->select('purchases.*','supplier_purchase_reports.difference_total_amount')
        ->where('purchases.company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('purchases.status','1')
                               
                                // ->where(function($q1){
                                //     $q1->where('purchases.image_1',"");
                                //     $q1->where('purchases.image_2',"");
                                //     $q1->where('purchases.image_3',"");
                                //     $q1->orWhere(function($q2){
                                //         $q2->where('purchases.image_1',null);
                                //         $q2->where('purchases.image_2',null);
                                //         $q2->where('purchases.image_3',null);
                                //     });
                                // })
                                ->where('supplier_action_status','1')
                                ->when($id!=null, function ($q) use ($id) {
                                    // ✅ Example: add condition only if dates are passed
                                    $q->where('purchases.party', $id);
                                })
                                ->groupBy('purchase_id')
                                ->orderBy('date','asc')
                                ->get();
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
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        $pending_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',0)
                ->count();
        $approval_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',2)
                ->count();
        return view('supplier.complete_supplier_purchase', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id,"heads"=>$heads,'locations'=>$location,"approval_purchase"=>$approval_purchase,"pending_purchase"=>$pending_purchase]);
    }
    public function manageSupplierPurchaseReport($id=null, $from_date=null, $to_date=null)
    {
        $purchases = SupplierPurchaseVehicleDetail::with(['purchaseReport'=>function($q){
                                    $q->select('purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
                                    },'locationInfo'=>function($q1){
                                        $q1->select('id','name');
                                    },'accountInfo'=>function($q3){
                                        $q3->select('id','account_name');
                                    }
                                ])
                                ->join('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                                ->whereIn('supplier_purchase_vehicle_details.status',['3'])
                                ->when($id,function($q)use($id,$from_date,$to_date){
                                    $q->when($id!='all',function($q)use($id,$from_date,$to_date){
                                        $q->where('account_id', $id);
                                    });
                                    $q->whereDate('purchases.date', '>=', date('Y-m-d', strtotime($from_date)));
                                    $q->whereDate('purchases.date', '<=', date('Y-m-d', strtotime($to_date)));
                                })
                                ->select(
                                    DB::raw('SUM(difference_total_amount) as difference_sum'),
                                    'account_id',
                                    'map_purchase_id',
                                    'supplier_purchase_vehicle_details.voucher_no',
                                    'supplier_purchase_vehicle_details.location',
                                    'supplier_purchase_vehicle_details.id',
                                    DB::raw('SUM(purchases.total) as total_sum'),
                                )
                                ->groupBy('account_id')
                                ->get();
        // echo "<pre>";
        // print_r($purchases->toArray());die;
        $purchases_details = SupplierPurchaseVehicleDetail::with(['purchaseReport'=>function($q){
                                    $q->select('purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
                                    },'locationInfo'=>function($q1){
                                        $q1->select('id','name');
                                    },'headInfo'=>function($q2){
                                        $q2->select('id','name');
                                        $q2->orderBy('sequence');
                                    },'accountInfo'=>function($q3){
                                        $q3->select('id','account_name');
                                    },'purchaseInfo'=>function($q4){
                                        $q4->select('id','total','voucher_no','date');
                                        $q4->orderBy('date','asc');
                                    }
                                ])
                                ->join('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                                ->select('supplier_purchase_vehicle_details.id','difference_total_amount')
                                ->where('supplier_purchase_vehicle_details.company_id',Session::get('user_company_id'))                  
                                ->where('supplier_purchase_vehicle_details.status','3')
                                ->when($id,function($q)use($id,$from_date,$to_date){
                                    $q->when($id!='all',function($q)use($id,$from_date,$to_date){
                                        $q->where('account_id', $id);
                                    });
                                    $q->whereDate('purchases.date', '>=', date('Y-m-d', strtotime($from_date)));
                                    $q->whereDate('purchases.date', '<=', date('Y-m-d', strtotime($to_date)));
                                })                              
                                
                                ->get();
        // if($id=="42108" && $from_date=="2025-08-01" && $to_date=="2025-09-30"){
        //     echo "<pre>";
        //     print_r($purchases_details->toArray());die;
        // }
        
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
        return view('supplier.supplier_purchase_report', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id,"from_date"=>$from_date,"to_date"=>$to_date,"purchases_details"=>$purchases_details]);
    }
    public function viewCompletePurchaseInfo($id=null)
    {
        $report = SupplierPurchaseReport::select('head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount')
                    ->where('purchase_id',$id)
                    ->get();
        $purchase = SupplierPurchaseVehicleDetail::leftjoin('supplier_locations','supplier_purchase_vehicle_details.location','=','supplier_locations.id')
                                        ->leftjoin('manage_items','supplier_purchase_vehicle_details.contract_item_id','=','manage_items.id')
                                        ->select('image_1','image_2','image_3','voucher_no','difference_total_amount','supplier_locations.name as location_name','location','tare_weight','contract_item_id')
                                        ->find($id);
        $response = array(
            'reports' => $report,
            'purchase'=>$purchase
        );
        return json_encode($response);
    }
    public function getLocationBySupplier(Request $request){
        //Location
        $location_id = SupplierLocationRates::where('company_id', Session::get('user_company_id'))
                                            ->where('account_id', $request->account_id)
                                            ->pluck('location')
                                            ->unique()
                                            ->values();
        
        $location = SupplierLocation::select('id','name')
                                    ->where('status',1)
                                    ->whereIn('id',$location_id)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->orderBy('name')
                                    ->get();
        //Items
        $item_id = FuelSupplierRate::where('company_id', Session::get('user_company_id'))
                                            ->where('account_id', $request->account_id)
                                            ->pluck('item_id')
                                            ->unique()
                                            ->values();
        
        $items = ManageItems::select('id','name')
                                    ->where('status',1)
                                    ->whereIn('id',$item_id)
                                    ->where('company_id',Session::get('user_company_id'))
                                    ->orderBy('name')
                                    ->get();
        return response()->json([
            'location' => $location,
            'item' => $items,
        ]);
    }
    public function updateSupplierPurchaseReport(Request $request)
    {
        SupplierPurchaseReport::where('company_id',Session::get('user_company_id'))
                                ->where('purchase_id',$request->purchase_id)
                                ->delete();
        $data = json_decode($request->data,true);
        foreach ($data as $key => $value) {
            $report = new SupplierPurchaseReport;
            $report->purchase_id = $request->purchase_id;
            $report->voucher_no = $request->voucher_no;
            $report->location = $request->location;

            $report->head_id = $value['id'];
            $report->head_qty = $value['qty'];
            $report->head_bill_rate = $value['bill_rate'];
            $report->head_contract_rate = $value['contract_rate'];
            $report->head_difference_amount = $value['difference_amount'];

            $report->difference_total_amount = $request->difference_total_amount;

            $report->company_id = Session::get('user_company_id');
            $report->status = 1;
            $report->created_by = Session::get('user_id');
            $report->created_at = Carbon::now();
            if($report->save()){
                Purchase::where('company_id',Session::get('user_company_id'))
                        ->where('id',$request->purchase_id)
                        ->update(['supplier_action_status' => 1,'supplier_difference_total_amount'=>$request->difference_total_amount]);
                
            }
        }
        $response = array(
            'status' => true,
            'message' => 'Supplier Purchase Report Updated Successfully.'
        );
        return json_encode($response);
    }
    public function uploadPurchaseImage(Request $request)
    {
        // validate
        // $request->validate([
        //     'images.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        // ]);
        
        $paths = [];
        $purchase_vehicle = SupplierPurchaseVehicleDetail::find($request->image_purchase_id);
        //if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key=>$image) {
                 // Save file
                $filename = time().'_'.$image->getClientOriginalName();
                $filename = str_replace(" ","_",$filename);
                $path = 'purchase_images/'.$filename;
                $image->move(public_path('purchase_images'), $filename);
                // Save path in DB 
                $purchase = SupplierPurchaseVehicleDetail::find($request->image_purchase_id);
                if($key==0){
                    $purchase->image_1 = $path;
                }else if($key==1){
                    $purchase->image_2 = $path;
                }else if($key==2){
                    $purchase->image_3 = $path;
                }                
                $purchase->save();
                $paths[] = $path;
            }
            if($purchase_vehicle->status==1 && !empty($purchase_vehicle->map_purchase_id)){
                $purchase_vehicle->status = 2;
                $purchase_vehicle->completed_by = Session::get('user_id');
                $purchase_vehicle->save();
            }
        //}
        
        return redirect()->route('manage-purchase-info')->with('success','Image Uploaded Successfully');
    }
    public function pendingForApproval($id=null)
    {
        $purchases = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name,g_name as group_id',
                    'units:id,name',
                    'parameterColumnInfo' => function ($q2) {
                        $q2->leftjoin('item_paremeter_list as param1','purchase_parameter_info.parameter1_id','=','param1.id');
                        $q2->leftjoin('item_paremeter_list as param2','purchase_parameter_info.parameter2_id','=','param2.id');
                        $q2->leftjoin('item_paremeter_list as param3','purchase_parameter_info.parameter3_id','=','param3.id');
                        $q2->leftjoin('item_paremeter_list as param4','purchase_parameter_info.parameter4_id','=','param4.id');
                        $q2->leftjoin('item_paremeter_list as param5','purchase_parameter_info.parameter5_id','=','param5.id');
                        $q2->select('purchase_parameter_info.id', 'purchase_desc_row_id','parameter1_id','parameter2_id','parameter3_id','parameter4_id','parameter5_id','parameter1_value','parameter2_value','parameter3_value','parameter4_value','parameter5_value','param1.paremeter_name as paremeter_name1','param2.paremeter_name as paremeter_name2','param3.paremeter_name as paremeter_name3','param4.paremeter_name as paremeter_name4','param5.paremeter_name as paremeter_name5');
                    }
                ]);
                $query->select('id', 'goods_discription', 'qty', 'purchase_id', 'unit');
            },
            'account:id,account_name'
        ])
        ->join('supplier_purchase_reports','purchases.id','=','supplier_purchase_reports.purchase_id')
        ->select('purchases.*','supplier_purchase_reports.difference_total_amount')
        ->where('purchases.company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('purchases.status','1')
                                // ->where(function($q1){
                                //     $q1->orWhere('purchases.image_1','!=','');
                                //     $q1->orWhere('purchases.image_2','!=','');
                                //     $q1->orWhere('purchases.image_3','!=','');
                                // })
                                
                                ->where('supplier_action_status','2')
                                ->when($id!=null, function ($q) use ($id) {
                                    // ✅ Example: add condition only if dates are passed
                                    $q->where('purchases.party', $id);
                                })
                                ->groupBy('purchase_id')
                                ->orderBy('date','asc')
                                ->get();
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
        $heads = SupplierSubHead::with('group')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('sequence')
                                ->get();
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        $pending_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',0)
                ->count();
        $complete_purchase = Purchase::where('company_id',Session::get('user_company_id'))
                ->where('delete','0')
                ->where('status','1')
                ->where('supplier_action_status',1)
                ->count();
        return view('supplier.aproval_supplier_purchase', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id,"heads"=>$heads,'locations'=>$location,'pending_purchase'=>$pending_purchase,"complete_purchase"=>$complete_purchase]);
    }
    public function rejectPurchaseReport(Request $request)
    {
        $purchase = Purchase::find($request->purchase_id);
        $purchase->image_1 = "";
        $purchase->image_2 = "";
        $purchase->image_3 = "";
        $purchase->supplier_difference_total_amount = "";
        $purchase->supplier_action_status = 0;
        
        if($purchase->save()){
            SupplierPurchaseReport::where('company_id',Session::get('user_company_id'))
                                ->where('purchase_id',$request->purchase_id)
                                ->delete();
            $response = array(
                'status' => 1,
                'message' => 'Purchase Report Rejected Successfully.'
            );
            return json_encode($response);
        }
    }
    public function approvePurchaseReport(Request $request)
    {
        $purchase = SupplierPurchaseVehicleDetail::find($request->purchase_id);
        $purchase->status = 3;
        $purchase->reapproval = 0;
        $purchase->approved_by = Session::get('user_id');
        if($purchase->save()){
            $response = array(
                'status' => 1,
                'message' => 'Purchase Report Approved Successfully.'
            );
            return json_encode($response);
        }
    }
    public function viewApprovedPurchaseDetail(Request $request,$id=null,$from_date=null,$to_date=null)
    {
        $account = Accounts::select('account_name')->where('id',$id)->first();
        $purchases = SupplierPurchaseVehicleDetail::with(['purchaseReport'=>function($q){
                                    $q->select('purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
                                    },'locationInfo'=>function($q1){
                                        $q1->select('id','name');
                                    },'accountInfo'=>function($q3){
                                        $q3->select('id','account_name');
                                    }
                                ])
                                ->join('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                                ->select('supplier_purchase_vehicle_details.id','purchases.voucher_no as invoice_no','purchases.total','difference_total_amount','date','supplier_purchase_vehicle_details.voucher_no','location')
                                ->where('supplier_purchase_vehicle_details.company_id',Session::get('user_company_id'))
                                ->where('supplier_purchase_vehicle_details.status','3')
                                ->where('supplier_purchase_vehicle_details.account_id', $id)
                                ->when($from_date,function($query)use ($from_date,$to_date){
                                    $query->whereBetween('purchases.date', [$from_date, $to_date]);
                                })
                                ->orderBy('date','asc')
                                ->get();
        // echo "<pre>";
        // print_r($purchases->toArray());
        // echo "</pre>";
        return view('supplier/supplier_purchase_report_detail',['account_name'=>$account->account_name,'from_date'=>$from_date,'to_date'=>$to_date,"purchases"=>$purchases,'id'=>$id]);
    }
    public function performActionOnPurchase(Request $request)
    {
        $action_data = json_decode($request->action_data,true);
        $amount = array_sum(array_column($action_data, 'amount')); 
        $ids = array_column($action_data, 'id');
    }
    //
    public function managePurchaseInfo(Request $request){
        
        $pending_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 0)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'tare_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'purchases.taxable_amt as purchase_taxable_amount',
                    'entry_date',
                    'reapproval',
                    DB::raw('(SELECT group_type FROM `sale-order-settings` WHERE `sale-order-settings`.item_id = supplier_purchase_vehicle_details.group_id and setting_for="PURCHASE ORDER" and setting_type="PURCHASE GROUP" LIMIT 1) as group_type'),
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        $in_process_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 1)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'tare_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'purchases.taxable_amt as purchase_taxable_amount',
                    'entry_date',
                    'reapproval',
                    DB::raw('(SELECT group_type FROM `sale-order-settings` WHERE `sale-order-settings`.item_id = supplier_purchase_vehicle_details.group_id and setting_for="PURCHASE ORDER" and setting_type="PURCHASE GROUP" LIMIT 1) as group_type'),
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        $pending_for_approval_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 2)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'tare_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'purchases.taxable_amt as purchase_taxable_amount',
                    'entry_date',
                    'reapproval',
                    DB::raw('(SELECT group_type FROM `sale-order-settings` WHERE `sale-order-settings`.item_id = supplier_purchase_vehicle_details.group_id and setting_for="PURCHASE ORDER" and setting_type="PURCHASE GROUP" LIMIT 1) as group_type'),
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        $approve_from_date = $request->approve_from_date;
        $approve_to_date = $request->approve_to_date;
        if($approve_from_date=="" && $approve_to_date==""){
            $approve_from_date = date('Y-m-d');
            $approve_to_date = date('Y-m-d');
        }
        $approved_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 3)
                ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$approve_from_date,$approve_to_date])
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'tare_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'purchases.taxable_amt as purchase_taxable_amount',
                    'entry_date',
                    'reapproval',
                    DB::raw('(SELECT group_type FROM `sale-order-settings` WHERE `sale-order-settings`.item_id = supplier_purchase_vehicle_details.group_id and setting_for="PURCHASE ORDER" and setting_type="PURCHASE GROUP" LIMIT 1) as group_type'),
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
                
        // echo "<pre>";
        // print_r($purchase_info->toArray());
        // die;
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        $heads = SupplierSubHead::leftjoin('sale-order-settings','supplier_sub_heads.group_id',"=","sale-order-settings.item_id")
                                ->where('supplier_sub_heads.company_id',Session::get('user_company_id'))
                                ->where('supplier_sub_heads.status',1)
                                ->where('sale-order-settings.setting_type', '=', 'PURCHASE GROUP')
                                ->where('sale-order-settings.setting_for', '=', 'PURCHASE ORDER')
                                ->select('supplier_sub_heads.*','group_type')
                                ->orderBy('sequence')
                                ->get();
        // echo "<pre>";print_r($heads->toArray());die;
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
        $item_groups = ItemGroups::where('company_id', Session::get('user_company_id'))
                            ->where('delete', '=', '0')
                            ->where('status', '=', '1')
                            ->orderBy('group_name')
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
        
        $pending_report_grouped = [];
        foreach ($pending_report->toArray() as $row) {
            $pending_report_grouped[$row['group_type']][] = $row;
        }
        $in_process_report_grouped = [];
        foreach ($in_process_report->toArray() as $row) {
            $in_process_report_grouped[$row['group_type']][] = $row;
        }
        $pending_for_approval_report_grouped = [];
        foreach ($pending_for_approval_report->toArray() as $row) {
            $pending_for_approval_report_grouped[$row['group_type']][] = $row;
        }
        $approved_report_grouped = [];
        foreach ($approved_report->toArray() as $row) {
            $approved_report_grouped[$row['group_type']][] = $row;
        }
        return view('supplier/view_purchase_vehicle_detail',["pending_report"=>$pending_report_grouped,"in_process_report"=>$in_process_report_grouped,"pending_for_approval_report"=>$pending_for_approval_report_grouped,"approved_report"=>$approved_report_grouped,"locations"=>$location,"heads"=>$heads,"accounts"=>$accounts,"item_groups"=>$item_groups,"approve_from_date"=>$approve_from_date,"approve_to_date"=>$approve_to_date,"items"=>$items,"group_list"=>$group_list]);
    }
    public function addPurchaseInfo(Request $request){
        if(isset($request->type) && $request->type=="BOILERFUEL"){
            $supplier = FuelSupplier::select('account_id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        }else if(isset($request->type) && $request->type=="WASTEKRAFT"){
            $supplier = Supplier::select('account_id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        }else{
            $supplier = Supplier::select('account_id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        }
        
        $accounts = Accounts:: whereIn('id',$supplier)
                              ->select('accounts.id','accounts.account_name')
                              ->orderBy('account_name')
                              ->get(); 

                              
        $item_groups = ItemGroups::join('sale-order-settings','item_groups.id','=','sale-order-settings.item_id')
                            ->select('item_groups.id','group_name')
                            ->where('item_groups.company_id', Session::get('user_company_id'))
                            ->where('item_groups.delete', '=', '0')
                            ->where('item_groups.status', '=', '1')
                            ->where('setting_type', '=', 'PURCHASE GROUP')
                            ->where('setting_for', '=', 'PURCHASE ORDER')
                            ->orderBy('group_name')
                            ->get();
       
        return view('supplier/add_purchase_vehicle_detail',["accounts"=>$accounts,"item_groups"=>$item_groups]);
    }
    public function storePurchaseInfo(Request $request){
        $validated = $request->validate([
            'vehicle_no' => 'required',
            'group' => 'required',
            'gross_weight' => 'required',
            'account' => 'required'
        ]);
        $purchase_info = new SupplierPurchaseVehicleDetail;
        $purchase_info->vehicle_no = $request->vehicle_no;
        $purchase_info->entry_date = $request->date;
        $purchase_info->group_id = $request->group;
        $purchase_info->item_id = $request->item;
        $purchase_info->account_id = $request->account;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->company_id = Session::get('user_company_id');
        $purchase_info->created_at =  Carbon::now();
        if($purchase_info->save()){
            return redirect()->route('manage-purchase-info')->with('success','Info Saved Successfully');
        }else{
            return redirect()->route('manage-purchase-info')->with('success','Something Went Wrong');
        }
    }
    public function editPurchaseInfo(Request $request,$id){
        $supplier = Supplier::select('account_id')
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        $accounts = Accounts::whereIn('id',$supplier)
                              ->select('accounts.id','accounts.account_name')
                              ->orderBy('account_name')
                              ->get(); 
        $item_groups = ItemGroups::join('sale-order-settings','item_groups.id','=','sale-order-settings.item_id')
                            ->select('item_groups.id','group_name')
                            ->where('item_groups.company_id', Session::get('user_company_id'))
                            ->where('item_groups.delete', '=', '0')
                            ->where('item_groups.status', '=', '1')
                            ->where('setting_type', '=', 'PURCHASE GROUP')
                            ->where('setting_for', '=', 'PURCHASE ORDER')
                            ->orderBy('group_name')
                            ->get();
        $purchase_info = SupplierPurchaseVehicleDetail::find($id);
        return view('supplier/edit_purchase_vehicle_detail',["accounts"=>$accounts,"item_groups"=>$item_groups,"purchase_info"=>$purchase_info]);
    }
    public function updatePurchaseInfo(Request $request,$id){
        $validated = $request->validate([
            'vehicle_no' => 'required',
            'group' => 'required',
            'gross_weight' => 'required',
            'account' => 'required'
        ]);
        $purchase_info = SupplierPurchaseVehicleDetail::find($id);
        $purchase_info->vehicle_no = $request->vehicle_no;
        $purchase_info->group_id = $request->group;
        $purchase_info->item_id = $request->item;
        $purchase_info->account_id = $request->account;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->updated_at =  Carbon::now();
        if($purchase_info->save()){
            return redirect()->route('manage-purchase-info')->with('success','Info Updated Successfully');
        }else{
            return redirect()->route('manage-purchase-info')->with('success','Something Went Wrong');
        }
    }
    public function deletePurchaseInfo(Request $request){
        $validated = $request->validate([
            'delete_id' => 'required',
        ]);
        $purchase_info = SupplierPurchaseVehicleDetail::where('id',$request->delete_id)->delete();
        if($purchase_info){
            return redirect()->route('manage-purchase-info')->with('success','Info Deleted Successfully');
        }else{
            return redirect()->route('manage-purchase-info')->with('success','Something Went Wrong');
        }
    }
    public function itemByGroup(Request $request){
        $items = ManageItems::select('id','name')
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('g_name',$request->group_id)
                                ->where('status','1')
                                ->where('delete','0')
                                ->orderBy('name')
                                ->get();
        return json_encode(array("status"=>true,"data"=>$items));
    }
    public function supplierPurchaseSetting(Request $request){        
        $item_groups = ItemGroups::where('company_id', Session::get('user_company_id'))
                            ->where('delete', '=', '0')
                            ->where('status', '=', '1')
                            ->orderBy('group_name')
                            ->get();
        $selectedGroups = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->pluck('item_id')
                            ->toArray();
        $selectedGroupType = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->pluck('group_type','item_id')
                            ->toArray();
        return view('supplier/setting',["item_groups"=>$item_groups,"selectedGroups"=>$selectedGroups,"selectedGroupType"=>$selectedGroupType]);
    }
    public function storeSupplierPurchaseSetting(Request $request){
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $newItems = $request->input('group', []);
        SaleOrderSetting::where('company_id', $company_id)
                ->where('setting_type', 'PURCHASE GROUP')
                ->where('setting_for', 'PURCHASE ORDER')
                ->delete();
        foreach ($newItems as $item_id) {
            SaleOrderSetting::create([
                'company_id'   => $company_id,
                'item_id'      => $item_id,
                'setting_type' => 'PURCHASE GROUP',
                'setting_for'  => 'PURCHASE ORDER',
                'status'       => 1,
                'group_type' => $request->input('group_type_'.$item_id) ?? null,
            ]);
        }
        return redirect()->route('supplier-purchase-setting')
            ->with('success', 'Settings updated successfully.');
    }
    
}
