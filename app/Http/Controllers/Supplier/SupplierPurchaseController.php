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
use App\Models\FuelSupplier;
use App\Models\SupplierLocation;
use App\Models\SupplierLocationRates;
use App\Models\SupplierPurchaseReport;
use App\Models\Accounts;
use App\Models\SparePart;
use App\Models\SupplierSubHead;
use App\Models\ItemGroups;
use App\Models\SupplierPurchaseVehicleDetail;
use App\Models\SupplierRateLocationWise;
use App\Models\ManageItems;
use App\Models\SaleOrderSetting;
use App\Models\FuelSupplierRate;
use App\Models\SparePartSupplier;
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
        $check_voucher = SupplierPurchaseVehicleDetail::where('company_id', Session::get('user_company_id'))
                    ->where('voucher_no', $request->voucher_no)
                    ->where('id', '!=', $request->purchase_id)
                    ->where('group_id', $request->group_id)
                    ->first();

        if ($check_voucher) {

            // Status text mapping
            switch ((int) $check_voucher->status) {
                case 0:
                    $statusText = 'Pending';
                    break;
                case 1:
                    $statusText = 'In Process';
                    break;
                case 2:
                    $statusText = 'Pending For Approval';
                    break;
                case 3:
                    $statusText = 'Approved';
                    break;
                default:
                    $statusText = 'Unknown';
            }

            // Format entry date
            $usedDate = $check_voucher->entry_date
                ? date('d F Y', strtotime($check_voucher->entry_date))
                : 'N/A';

            $response = array(
                'status'  => false,
                'message' => "Voucher No already exists.\n\nUsed on: {$usedDate}\nStatus: {$statusText}"
            );

            return json_encode($response);
        }
        $purchase_vehicle = SupplierPurchaseVehicleDetail::find($request->purchase_id);

        $oldStatus = $purchase_vehicle->status;

        $oldHeads = SupplierPurchaseReport::where('purchase_id', $request->purchase_id)
            ->get()
            ->keyBy('head_id')
            ->map(function ($row) {
                return [
                    'net_weight'    => (float) $row->head_qty,
                    'bill_rate'     => (float) $row->head_bill_rate,
                    'contract_rate' => (float) $row->head_contract_rate,
                ];
            })
            ->toArray();
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
        }else if($request->save_approve_status==1){
            $purchase_vehicle->status = 3;
            $purchase_vehicle->reapproval = 0;
        }
        $purchase_vehicle->account_id = $request->account_id;
        $purchase_vehicle->entry_date = $request->entry_date;
        $purchase_vehicle->group_id = $request->group_id;
        $purchase_vehicle->vehicle_no = $request->vehicle_no;

        $purchase_vehicle->difference_total_amount = $request->difference_total_amount;
        $purchase_vehicle->tare_weight = $request->tare_weight;
        $purchase_vehicle->gross_weight = $request->gross_weight;
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
                if($value['qty']!='cut'){
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
        $newHeads = SupplierPurchaseReport::where('purchase_id', $request->purchase_id)
            ->get()
            ->keyBy('head_id')
            ->map(function ($row) {
                return [
                    'net_weight'   => (float) $row->head_qty,
                    'bill_rate'    => (float) $row->head_bill_rate,
                    'contract_rate'=> (float) $row->head_contract_rate,
                ];
            })->toArray();
            $changedHeads = [];

        foreach ($newHeads as $headId => $newValues) {

            $oldValues = $oldHeads[$headId] ?? null;

            if (!$oldValues || $oldValues != $newValues) {
                $changedHeads[$headId] = [
                    'old' => $oldValues,
                    'new' => $newValues,
                ];
            }
        }

        if (
            $oldStatus == 2 &&
            $purchase_vehicle->status == 3 &&
            !empty($changedHeads)
        ) {
            DB::table('business_activity_logs')->insert([
                'module_type' => 'purchase_report',
                'module_id'   => $purchase_vehicle->id, // supplier_purchase_vehicle_details.id
                'action'      => 1, // EDIT
                'old_data'    => json_encode(['heads' => array_map(fn($h) => $h['old'], $changedHeads)]),
                'new_data'    => json_encode(['heads' => array_map(fn($h) => $h['new'], $changedHeads)]),
                'action_by'   => Session::get('user_id'),
                'company_id'  => Session::get('user_company_id'),
                'status'      => 1, // APPROVED CHANGE
                'action_at'   => now(),
            ]);
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
    public function manageSupplierPurchaseReport($id = null, $from_date = null, $to_date = null)
    {
        if (request()->is('wastekraft-purchase-report*')) {
            $group_id = 1; // Waste Kraft
        } elseif (request()->is('boilerfuel-purchase-report*')) {
            $group_id = 4; // Boiler Fuel
        } else {
            $group_id = null; // fallback
        }

        $purchases = SupplierPurchaseVehicleDetail::with([
            'purchaseReport' => function ($q) {
                $q->select('purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
            },
            'locationInfo:id,name',
            'accountInfo:id,account_name'
        ])
            ->join('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.status','3')
            ->when($group_id, function($q) use ($group_id) {
                $q->where('supplier_purchase_vehicle_details.group_id', $group_id);
            })
            ->when($id, function ($q) use ($id, $from_date, $to_date) {
                if ($id != 'all') {
                    $q->where('account_id', $id);
                }
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
                DB::raw('SUM(purchases.total) as total_sum')
            )
            ->groupBy('account_id')
            ->get();

        $purchases_details = SupplierPurchaseVehicleDetail::with([
            'purchaseReport' => function ($q) {
                $q->select('purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
            },
            'locationInfo:id,name',
            'headInfo:id,name',
            'accountInfo:id,account_name',
            'purchaseInfo:id,total,voucher_no,date'
        ])
            ->join('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
            ->select('supplier_purchase_vehicle_details.id','difference_total_amount')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.status','3')
            ->when($group_id, function($q) use ($group_id) {
                $q->where('supplier_purchase_vehicle_details.group_id', $group_id);
            })
            ->when($id, function ($q) use ($id, $from_date, $to_date) {
                if ($id != 'all') {
                    $q->where('account_id', $id);
                }
                $q->whereDate('purchases.date', '>=', date('Y-m-d', strtotime($from_date)));
                $q->whereDate('purchases.date', '<=', date('Y-m-d', strtotime($to_date)));
            })
            ->get();

        $group_ids = CommonHelper::getAllChildGroupIds(3, Session::get('user_company_id'));
        $group_ids[] = 3;
        $group_ids = array_merge(
            $group_ids,
            CommonHelper::getAllChildGroupIds(11, Session::get('user_company_id'))
        );
        $group_ids = array_unique($group_ids);
        $group_ids[] = 11;

        $accounts = Accounts::where('delete', 0)
            ->where('status', 1)
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->whereIn('under_group', $group_ids)
            ->orderBy('account_name')
            ->get();

        return view('supplier.supplier_purchase_report', [
            "purchases" => $purchases,
            "accounts" => $accounts,
            "id" => $id,
            "from_date" => $from_date,
            "to_date" => $to_date,
            "purchases_details" => $purchases_details,
            "group_id" => $group_id // IMPORTANT
        ]);
    }

    public function viewCompletePurchaseInfo($id = null)
    {           //updated by khushi
        $report = SupplierPurchaseReport::select('head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount')
            ->where('purchase_id', $id)
            ->get();

        $purchase = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->leftJoin('manage_items','supplier_purchase_vehicle_details.contract_item_id','=','manage_items.id')
                ->leftJoin('supplier_locations','supplier_purchase_vehicle_details.location','=','supplier_locations.id')
                ->where('supplier_purchase_vehicle_details.id', $id)
                ->select(
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'supplier_purchase_vehicle_details.difference_total_amount',
                    'supplier_purchase_vehicle_details.tare_weight',
                    'supplier_purchase_vehicle_details.contract_item_id',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'manage_items.id as item_id',
                    'manage_items.name as item_name',
                    'supplier_locations.name as location_name',
                    'supplier_purchase_vehicle_details.location'
                )
                ->first();

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
                $filename = time() . '' . str_replace(" ", "", $image->getClientOriginalName());
                
                $destination = public_path('purchase_images/' . $filename);
                $this->compressImage($image->getPathname(), $destination);
                
                //$path = 'purchase_images/'.$filename;
                //$image->move(public_path('purchase_images'), $filename);
                // Save path in DB 
                $purchase = SupplierPurchaseVehicleDetail::find($request->image_purchase_id);
                $dbPath = 'purchase_images/' . $filename;
                if($key==0){
                    $purchase->image_1 = $dbPath;
                }else if($key==1){
                    $purchase->image_2 = $dbPath;
                }else if($key==2){
                    $purchase->image_3 = $dbPath;
                }                
                $purchase->save();
                $paths[] = $dbPath;
            }
            if($purchase_vehicle->status==1 && !empty($purchase_vehicle->map_purchase_id)){
                $purchase_vehicle->status = 2;
                $purchase_vehicle->completed_by = Session::get('user_id');
                $purchase_vehicle->save();
            }
        //}
        return redirect()->back()->with('success', 'Image Uploaded Successfully');
        //return redirect()->route('manage-purchase-info')->with('success','Image Uploaded Successfully');
    }
    private function compressImage($sourcePath, $destinationPath)
    {
        $info = getimagesize($sourcePath);

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                $tmp = imagecreatetruecolor(imagesx($image), imagesy($image));
                imagecopy($tmp, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                $image = $tmp;
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                copy($sourcePath, $destinationPath);
                return;
        }

        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3: $image = imagerotate($image, 180, 0); break;
                    case 6: $image = imagerotate($image, -90, 0); break;
                    case 8: $image = imagerotate($image, 90, 0); break;
                }
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $maxWidth = 1600; 

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = intval($height * ($newWidth / $width));

            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $tmp;
        }

        imagejpeg($image, $destinationPath, 88);

        return $destinationPath;
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
    public function viewApprovedPurchaseDetail(Request $request, $id, $from = null, $to = null, $group_id)
    {
        if ($from === "all") $from = null;
        if ($to === "all")   $to = null;

        $account = Accounts::select('account_name')
            ->where('id', $id)
            ->first();
        //updated by khushi
        $purchases = SupplierPurchaseVehicleDetail::with([
                'purchaseReport',
                'locationInfo',
                'accountInfo'
            ])
            ->join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
            ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
            ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.account_id', $id)
            ->where('supplier_purchase_vehicle_details.group_id', $group_id)
            ->where('supplier_purchase_vehicle_details.status', 3)
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from, $to]);
            })
            ->select(
                'supplier_purchase_vehicle_details.*',
                'accounts.account_name',
                'item_groups.group_name',

                'purchases.voucher_no as purchase_voucher_no',
                'purchases.date as purchase_date',
                'purchases.total as purchase_amount',
                'purchases.taxable_amt as purchase_taxable_amount',
                'stock_entry_date',
                'purchases.voucher_no as invoice_no',
                'purchases.taxable_amt',
                'purchases.total',
                'purchases.date',

                DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price'),
                DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]") 
                    FROM purchase_descriptions 
                    WHERE purchase_descriptions.purchase_id = purchases.id) as prices'),
                DB::raw('(SELECT group_type FROM `sale-order-settings` 
                    WHERE `sale-order-settings`.item_id = supplier_purchase_vehicle_details.group_id 
                    AND setting_for="PURCHASE ORDER" 
                    AND setting_type="PURCHASE GROUP" 
                    LIMIT 1) as group_type')
            )
            ->orderBy('purchases.date', 'asc')
            ->get();


        foreach ($purchases as $purchase) {

            $gst_amount = DB::table('purchase_sundries')
                ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
                ->where('purchase_id', $purchase->map_purchase_id)
                ->whereIn('nature_of_sundry', ['CGST','SGST','IGST'])
                ->sum('amount');

            $purchase->taxable_amount = $purchase->taxable_amt;
            $purchase->gst_rate = $gst_amount; 
        }
        $payments = DB::table('payments as p')
            ->join('payment_details as pd', 'pd.payment_id', '=', 'p.id')
            ->where('p.delete', '0')
            ->where('pd.delete', '0')
            ->where('p.company_id', Session::get('user_company_id'))
            ->where('pd.account_name', $id)
            ->where('pd.type', 'Debit')
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('p.date', [$from, $to]);
            })
            ->select(
                DB::raw('DATE(p.date) as pay_date'),
                DB::raw('SUM(pd.debit) as amount') 
            )
            ->groupBy('pay_date')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    \Carbon\Carbon::parse($item->pay_date)->format('Y-m-d') => $item
                ];
            });
            $finalRows = [];

        $purchasesGrouped = $purchases->groupBy(function ($item) {
            $date = $item->purchase_date ?? $item->date ?? $item->entry_date;
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        });

        $allDates = collect($purchasesGrouped->keys())
            ->merge($payments->keys())
            ->filter()
            ->unique()
            ->sort();

        foreach ($allDates as $date) {

            $purchaseRows = $purchasesGrouped[$date] ?? collect();
            $paymentAmount = isset($payments[$date]) ? $payments[$date]->amount : 0;

            if ($purchaseRows->count()) {

                foreach ($purchaseRows as $index => $row) {

                    $isLast = $index == ($purchaseRows->count() - 1);

                    $row->payment_amount = $isLast ? $paymentAmount : 0;
                    $row->is_payment_only = false;

                    $finalRows[] = $row;
                }
            }

            else {

                $finalRows[] = (object)[
                    'date' => $date,
                    'is_payment_only' => true,
                    'payment_amount' => $paymentAmount
                ];
            }
        }
        $locations = SupplierLocation::where('company_id', Session::get('user_company_id'))
            ->where('status', 1)
            ->get();

        $heads = SupplierSubHead::leftjoin('sale-order-settings','supplier_sub_heads.group_id',"=","sale-order-settings.item_id")
            ->where('supplier_sub_heads.company_id',Session::get('user_company_id'))
            ->where('supplier_sub_heads.status',1)
            ->where('sale-order-settings.setting_type', '=', 'PURCHASE GROUP')
            ->where('sale-order-settings.setting_for', '=', 'PURCHASE ORDER')
            ->select('supplier_sub_heads.*','group_type')
            ->orderBy('sequence')
            ->get();

        $accounts = Accounts::where('accounts.delete', '=', '0')
            ->where('accounts.status', '=', '1')
            ->whereIn('accounts.company_id', [Session::get('user_company_id'),0])
            ->select('accounts.id','accounts.account_name')
            ->orderBy('account_name')
            ->get();

        $item_groups = ItemGroups::where('company_id', Session::get('user_company_id'))
            ->where('delete', '=', '0')
            ->where('status', '=', '1')
            ->orderBy('group_name')
            ->get();
        $items = ManageItems::where('company_id', Session::get('user_company_id'))
                ->where('status', 1)
                ->orderBy('name')
                ->get();
        $waste_group_id = SaleOrderSetting::where('group_type','WASTE KRAFT')
                ->where('company_id', Session::get('user_company_id'))
                ->value('item_id');

        $boiler_group_id = SaleOrderSetting::where('group_type','BOILER FUEL')
            ->where('company_id', Session::get('user_company_id'))
            ->value('item_id');

        $selected = $purchases->first();
        $purchase_prices = $selected->prices ?? "[]";


        return view('supplier.supplier_purchase_report_detail', [
            'account_name'   => $account->account_name,
            'from_date'      => $from,
            'to_date'        => $to,
            'purchases' => collect($finalRows),
            'id'             => $id,
            'items'         =>$items,
            'waste_group_id'=>$waste_group_id,
            'boiler_group_id'=>$boiler_group_id,
            'group_id'       => $group_id,
            'accounts'       => $accounts,
            'locations'      => $locations,
            'heads'          => $heads,
            'item_groups'    => $item_groups,
            'purchase_prices'=> $purchase_prices
        ]);
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
        if (isset($request->type) && $request->type == "BOILERFUEL") {
            $supplier = FuelSupplier::select('account_id')
                ->where('company_id', Session::get('user_company_id'))
                ->pluck('account_id');
        } else if (isset($request->type) && $request->type == "WASTEKRAFT") {
            $supplier = Supplier::select('account_id')
                ->where('company_id', Session::get('user_company_id'))
                ->pluck('account_id');
        } else {
            $supplier = Supplier::select('account_id')
                ->where('company_id', Session::get('user_company_id'))
                ->pluck('account_id');
        }

        $accounts = Accounts::whereIn('id', $supplier)
            ->select('accounts.id', 'accounts.account_name')
            ->orderBy('account_name')
            ->get();

        $item_groups = ItemGroups::join('sale-order-settings', 'item_groups.id', '=', 'sale-order-settings.item_id')
            ->select('item_groups.id', 'group_name')
            ->where('item_groups.company_id', Session::get('user_company_id'))
            ->where('item_groups.delete', '=', '0')
            ->where('item_groups.status', '=', '1')
            ->where('setting_type', '=', 'PURCHASE GROUP')
            ->where('setting_for', '=', 'PURCHASE ORDER')
            ->orderBy('group_name')
            ->get();

        $groupIds = SaleOrderSetting::join('item_groups', 'sale-order-settings.item_id', '=', 'item_groups.id')
            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
            ->where('sale-order-settings.setting_type', 'PURCHASE GROUP')
            ->where('sale-order-settings.setting_for', 'PURCHASE ORDER')
            ->whereIn('sale-order-settings.group_type', ['WASTE KRAFT', 'BOILER FUEL'])
            ->pluck('item_groups.id')
            ->toArray();

        $pending_report = SupplierPurchaseVehicleDetail::join('accounts', 'supplier_purchase_vehicle_details.account_id', '=', 'accounts.id')
            ->join('item_groups', 'supplier_purchase_vehicle_details.group_id', '=', 'item_groups.id')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.status', 0)
            ->whereIn('supplier_purchase_vehicle_details.group_id', $groupIds)
            ->select(
                'supplier_purchase_vehicle_details.id',
                'supplier_purchase_vehicle_details.entry_date',
                'supplier_purchase_vehicle_details.vehicle_no',
                'supplier_purchase_vehicle_details.gross_weight',
                'accounts.account_name',
                'item_groups.group_name'
            )
            ->orderBy('supplier_purchase_vehicle_details.entry_date', 'desc')
            ->get();

        return view('supplier.add_purchase_vehicle_detail', [
            "accounts" => $accounts,
            "item_groups" => $item_groups,
            "pending_report" => $pending_report
        ]);
    }
    public function storePurchaseInfo(Request $request){
        $validated = $request->validate([
            'vehicle_no' => 'required',
            'group' => 'required',
            'gross_weight' => 'required',
            'account' => 'required',
            'bill_no' => 'nullable',
            'amount' => 'nullable|numeric'
        ]);
        $purchase_info = new SupplierPurchaseVehicleDetail;
        $purchase_info->vehicle_no = $request->vehicle_no;
        $purchase_info->entry_date = $request->date;
        $purchase_info->group_id = $request->group;
        $purchase_info->item_id = $request->item;
        $purchase_info->account_id = $request->account;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->bill_no = $request->bill_no;
        $purchase_info->amount = $request->amount;
        $purchase_info->company_id = Session::get('user_company_id');
        $purchase_info->created_at =  Carbon::now();
         if($purchase_info->save()){
            $group_type = SaleOrderSetting::where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->where('item_id', $request->group)
                            ->select('group_type')
                            ->first();
            if($group_type && $group_type->group_type=="WASTE KRAFT"){
                //die;
                return redirect()->route('supplier.waste_kraft')->with('success','Vehicle Entry Saved Successfully.');
            }else if($group_type && $group_type->group_type=="BOILER FUEL"){
                return redirect()->route('supplier.boiler_fuel')->with('success','Vehicle Entry Saved Successfully.');
            }else if($group_type && $group_type->group_type=="SPARE PART"){
                return redirect()->route('spare-part.vehicle.index')->with('success','Vehicle Entry Saved Successfully.');
            }else{
                return redirect()->route('add-purchase-info')->with('success','Vehicle Entry Saved Successfully.');
            }
        }else{
            return redirect()->route('add-purchase-info')->with('error','Something Went Wrong');
        }
    }
    public function editPurchaseInfo(Request $request,$id){
        $purchase_info = SupplierPurchaseVehicleDetail::find($id);
        $group_type = SaleOrderSetting::where('sale-order-settings.company_id', Session::get('user_company_id'))
                        ->where('setting_type', 'PURCHASE GROUP')
                        ->where('setting_for', 'PURCHASE ORDER')
                        ->where('item_id', $purchase_info->group_id)
                        ->select('group_type')
                        ->first();
        if($group_type && $group_type->group_type == "BOILER FUEL"){
                $supplier = FuelSupplier::select('account_id')
                                ->where('status', 1)
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        }else if($group_type && $group_type->group_type == "WASTE KRAFT"){
                $supplier = Supplier::select('account_id')
                                ->where('status', 1)
                                ->where('company_id',Session::get('user_company_id'))
                                ->pluck('account_id');
        }else if($group_type && $group_type->group_type == "SPARE PART"){
            
                $supplier = SparePartSupplier::where('status', 1)
                                        ->Where('company_id',Session::get('user_company_id'))
                                        ->pluck('account_id');
        }
        
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
        $group_type = SaleOrderSetting::where('sale-order-settings.company_id', Session::get('user_company_id'))
                        ->where('setting_type', 'PURCHASE GROUP')
                        ->where('setting_for', 'PURCHASE ORDER')
                        ->where('item_id', $request->group)
                        ->select('group_type')
                        ->first();

        if($group_type && $group_type->group_type == "SPARE PART"){
            $purchase_info->bill_no = $request->bill_no;
            $purchase_info->amount = $request->amount;
        }else{
            // clear old spare part values
            $purchase_info->bill_no = null;
            $purchase_info->amount = null;
        }
        $purchase_info->account_id = $request->account;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->updated_at =  Carbon::now();
        if($purchase_info->save()){
            if($group_type && $group_type->group_type=="WASTE KRAFT"){
                //die;
                return redirect()->route('supplier.waste_kraft')->with('success','Vehicle Entry Saved Successfully.');
            }else if($group_type && $group_type->group_type=="BOILER FUEL"){
                return redirect()->route('supplier.boiler_fuel')->with('success','Vehicle Entry Saved Successfully.');
            }else if($group_type && $group_type->group_type=="SPARE PART"){
                return redirect()->route('spare-part.vehicle.index')->with('success','Vehicle Entry Saved Successfully.');
            }else{
                return redirect()->route('add-purchase-info')->with('success','Vehicle Entry Saved Successfully.');
            }
            //return redirect()->route('add-purchase-info')->with('success','Info Updated Successfully');
        }else{
            return redirect()->route('add-purchase-info')->with('error','Something Went Wrong');
        }
    }
    public function deletePurchaseInfo(Request $request){
        $validated = $request->validate([
            'delete_id' => 'required',
        ]);
        $purchase_info = SupplierPurchaseVehicleDetail::where('id',$request->delete_id)->delete();
        if($purchase_info){
            return redirect()->back()->with('success', 'Info Deleted Successfully.');
            //return redirect()->route('manage-purchase-info')->with('success','Info Deleted Successfully');
        }else{
            return redirect()->back()->with('success', 'Something Went Wrong');
            //return redirect()->route('manage-purchase-info')->with('success','Something Went Wrong');
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



    public function getAccountsByGroup(Request $request)
    {
        $group_id = $request->group_id;
        $accounts = [];

        // Get items of the selected group
        $items = SaleOrderSetting::where('setting_type', 'PURCHASE GROUP')
                    ->where('item_id', $group_id)
                    ->get();

        foreach ($items as $item) {
            if ($item->group_type == 'BOILER FUEL') {
                // Fuel supplier accounts
                $fuelAccounts = FuelSupplier::where('status', 1)
                    ->where('company_id',Session::get('user_company_id'))
                    ->pluck('account_id')
                    ->toArray();

                $accounts = array_merge($accounts, $fuelAccounts);
            } elseif ($item->group_type == 'WASTE KRAFT') {
                // Waste kraft supplier accounts
                $kraftAccounts = Supplier::where('status', 1)
                    ->where('company_id',Session::get('user_company_id'))
                    ->pluck('account_id')
                    ->toArray();

                $accounts = array_merge($accounts, $kraftAccounts);
            } elseif ($item->group_type == 'SPARE PART') {
                // Waste kraft supplier accounts
                $spareAccounts = SparePartSupplier::where('status', 1)
                    ->where('company_id',Session::get('user_company_id'))
                    ->pluck('account_id')
                    ->toArray();

                $accounts = array_merge($accounts, $spareAccounts);
        }
        }

        // Remove duplicates and get account names
        $accounts = Accounts::whereIn('id', array_unique($accounts))
                    ->where('company_id',Session::get('user_company_id'))
                    ->select('id', 'account_name')
                    ->get();

        return response()->json($accounts);
    }
    public function wasteKraft(Request $request){
        $status = $request->get('status', 0);
        $approve_from_date = date('Y-m-d');
        $approve_to_date   = date('Y-m-d');
        $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->where('group_type', 'WASTE KRAFT')
                            ->select('item_id','group_type')
                            ->first();
        if(!$group_list){            
            return redirect()->route('supplier-purchase-setting')->with('error', 'No WASTE KRAFT group found in settings.');
        }
        $pending_report = collect();
        $in_process_report = collect();
        $pending_for_approval_report = collect();
        $approved_report = collect();
        if ($status == 0) {
            $pending_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 0)
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
        }
        if ($status == 0) {  
            $in_process_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 1)
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price'),
                    DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]") 
          FROM purchase_descriptions 
          WHERE purchase_descriptions.purchase_id = purchases.id) as prices') )
                ->orderBy('entry_date')
                 ->orderBy('voucher_no')
                ->get();
                
        }
        if ($status == 2) {
            $pending_for_approval_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                    ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                    ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                    ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                    ->where('supplier_purchase_vehicle_details.status', 2)
                    ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                        DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price'),
                    DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]") 
            FROM purchase_descriptions 
            WHERE purchase_descriptions.purchase_id = purchases.id) as prices') )
            ->orderBy('supplier_purchase_vehicle_details.entry_date', 'asc')
            ->orderBy('supplier_purchase_vehicle_details.voucher_no', 'asc')
                    ->get();  
        }  
             
        
        if ($status == 3) {
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
                    ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                        DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price'),
                    DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]") 
            FROM purchase_descriptions 
            WHERE purchase_descriptions.purchase_id = purchases.id) as prices')

                    )
                    ->get();
        }
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

        // $items = ManageItems::join('sale-order-settings','manage_items.g_name','=','sale-order-settings.item_id')
        //                         ->select('manage_items.id','name')
        //                         ->where('manage_items.company_id',Session::get('user_company_id'))
        //                         ->where('setting_type', 'PURCHASE GROUP')
        //                         ->where('setting_for', 'PURCHASE ORDER')
        //                          ->where('group_type', 'BOILER FUEL')
        //                         ->where('manage_items.status','1')
        //                         ->where('manage_items.delete','0')
        //                         ->orderBy('name')
        //                         ->get();
        
        
                                // $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                                // ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                                // ->where('setting_type', 'PURCHASE GROUP')
                                // ->where('setting_for', 'PURCHASE ORDER')
                                // ->select('item_id','group_type')
                                // ->get();
            
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
                                
                                $waste_group = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
                                            ->where('group_type', 'WASTE KRAFT')
                                            ->where('setting_type', "PURCHASE GROUP")
                                            ->where('setting_for', "PURCHASE ORDER")
                                            ->select('item_id')
                                            ->first();
                                            
                                            $fromDate = '2026-01-06 15:52:17';
                        
                               $vouchers = DB::table('supplier_purchase_vehicle_details')
                                ->where('company_id', Session::get('user_company_id'))
                                ->whereNotNull('voucher_no')
                                ->where('voucher_no', '!=', '')
                                ->where('group_id', $waste_group->item_id)
                                ->where('entry_date', '>=', $fromDate)
                                ->pluck('voucher_no')
                                ->map(fn($v) => (int)$v)
                                ->sort()
                                ->values();
                            
                            $fromDate1 = '2026-01-05 15:52:17';
                            $vouchers1 = DB::table('supplier_purchase_vehicle_details')
                                ->where('company_id', Session::get('user_company_id'))
                                ->whereNotNull('voucher_no')
                                ->where('voucher_no', '!=', '')
                                ->where('group_id', $waste_group->item_id)
                                ->where('entry_date', '<', $fromDate1)
                                ->pluck('voucher_no')
                                ->map(fn($v) => (int)$v)
                                ->sort()
                                ->values();
                            
                            // missing after $fromDate
                            $min1 = $vouchers->min();
                            $max1 = $vouchers->max();
                            $fullRange1 = collect(range($min1, $max1));
                            $missing = $fullRange1->diff($vouchers);
                            
                            // missing before $fromDate
                            $min2 = $vouchers1->min();
                            $max2 = $vouchers1->max();
                            $fullRange2 = collect(range($min2, $max2));
                            ini_set('memory_limit', '512M');
                            $missing1 = $fullRange2->diff($vouchers1);
                            
                            // merge missing sets
                            $missing = $missing1->merge($missing)->sort()->values();
                            
                            $count = $missing->count();

                            //    die($missing);
                                 return view('supplier.waste_kraft',["pending_report"=>$pending_report_grouped,"in_process_report"=>$in_process_report_grouped,"pending_for_approval_report"=>$pending_for_approval_report_grouped,"approved_report"=>$approved_report_grouped,"locations"=>$location,"heads"=>$heads,"accounts"=>$accounts,"item_groups"=>$item_groups,"approve_from_date"=>$approve_from_date,"approve_to_date"=>$approve_to_date,"count"=>$count,"missing"=>$missing]);
    }
    public function boilerFuel(Request $request){
        $status = $request->get('status', 0);
        $pending_report = collect();
        $in_process_report = collect();
        $pending_for_approval_report = collect();
        $approved_report = collect();
        $group_list = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
                            ->where('sale-order-settings.company_id', Session::get('user_company_id'))
                            ->where('setting_type', 'PURCHASE GROUP')
                            ->where('setting_for', 'PURCHASE ORDER')
                            ->where('group_type', 'BOILER FUEL')
                            ->select('item_id','group_type')
                            ->first();
        if(!$group_list){
            
            return redirect()->route('supplier-purchase-setting')->with('error', 'No BOILER FUEL group found in settings.');
        }
        if($status==0){
            $pending_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 0)
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                ->orderBy('supplier_purchase_vehicle_details.entry_date', 'asc')
                ->orderBy('supplier_purchase_vehicle_details.voucher_no', 'asc')
                ->get();
        }
        if($status==2){
            $pending_for_approval_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 2)
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                ->orderBy('supplier_purchase_vehicle_details.entry_date', 'asc')
                ->orderBy('supplier_purchase_vehicle_details.voucher_no', 'asc')
                ->get();
        }
        $approve_from_date = $request->approve_from_date;
        $approve_to_date = $request->approve_to_date;
        if($approve_from_date=="" && $approve_to_date==""){
            $approve_from_date = date('Y-m-d');
            $approve_to_date = date('Y-m-d');
        }
        if($status==3){
            $approved_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', 3)
                ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$approve_from_date,$approve_to_date])
                ->where('supplier_purchase_vehicle_details.group_id', $group_list->item_id)
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
                ->orderBy('entry_date')
                 ->orderBy('voucher_no')
                ->get();
        }
        
        
                
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
       $group_ids = CommonHelper::getAllChildGroupIds(3, Session::get('user_company_id'));
        array_push($group_ids, 3);

        $group_ids = array_merge(
            $group_ids,
            CommonHelper::getAllChildGroupIds(11, Session::get('user_company_id'))
        );

        array_push($group_ids, 11);
        $group_ids = array_unique($group_ids);

        $accounts = Accounts::join('fuel_suppliers', 'fuel_suppliers.account_id', '=', 'accounts.id')
            ->where('accounts.delete', '0')
            ->where('accounts.status', '1')
            ->where('fuel_suppliers.status', '1')
            ->where('fuel_suppliers.company_id', Session::get('user_company_id'))
            ->whereIn('accounts.company_id', [Session::get('user_company_id'), 0])
            ->whereIn('accounts.under_group', $group_ids)
            ->select('accounts.id', 'accounts.account_name')
            ->orderBy('accounts.account_name')
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
        //Missing Count
        $boiler_group = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
                    ->where('group_type', 'BOILER FUEL')
                    ->where('setting_type', "PURCHASE GROUP")
                    ->where('setting_for', "PURCHASE ORDER")
                    ->select('item_id')
                    ->first();

        $vouchers = DB::table('supplier_purchase_vehicle_details')
                        ->where('company_id', Session::get('user_company_id'))
                        ->whereNotNull('voucher_no')
                        ->where('voucher_no', '!=', '')
                        ->where('group_id',$boiler_group->item_id)
                        ->pluck('voucher_no')
                        ->map(fn($v) => (int)$v)
                        ->sort()
                        ->values();
        $min = $vouchers->min();
        $max = $vouchers->max();
        $fullRange = collect(range($min, $max));
        $missing = $fullRange->diff($vouchers);        
        $count = $missing->count();
        if(isset($missing[0]) && $missing[0]==0){
            $count = 0;
        }
        return view('supplier.boiler_fuel',["pending_report"=>$pending_report_grouped,"in_process_report"=>$in_process_report_grouped,"pending_for_approval_report"=>$pending_for_approval_report_grouped,"approved_report"=>$approved_report_grouped,"locations"=>$location,"heads"=>$heads,"accounts"=>$accounts,"item_groups"=>$item_groups,"approve_from_date"=>$approve_from_date,"approve_to_date"=>$approve_to_date,"items"=>$items,"group_list"=>$group_list,"count"=>$count,"missing"=>$missing]);
    }
    
    public function wasteKraftPurchaseReport($id = null, $from_date = null, $to_date = null)
    {
            //updated by khushi
        $from_date = request()->from_date ?? $from_date;
        $to_date   = request()->to_date ?? $to_date;

        if (empty($from_date) || empty($to_date)) {
            $from_date = date('Y-m-01');
            $to_date   = date('Y-m-t');
        }

        $view_by = request()->view_by ?? 'party';

        $group_list = SaleOrderSetting::where('sale-order-settings.company_id', Session::get('user_company_id'))
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'WASTE KRAFT')
            ->select('item_id')
            ->first();

        $group_id = $group_list->item_id;

        $purchases = SupplierPurchaseVehicleDetail::with([
                'purchaseReport',
                'locationInfo:id,name',
                'accountInfo:id,account_name'
            ])
            ->leftjoin('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->when($view_by === 'party',function($q){                
                $q->where('supplier_purchase_vehicle_details.status', '3');                
            })
            
            ->when($id && $id != 'all', function ($q) use ($id) {
                $q->where('account_id', $id);
            })
            ->where('supplier_purchase_vehicle_details.group_id', $group_id) // keep same group filter
            ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
            ->select(
                DB::raw('SUM(difference_total_amount) AS difference_sum'),
                'account_id',
                DB::raw('SUM(purchases.total) AS total_sum'),
                DB::raw('SUM(purchases.taxable_amt) AS taxable_amt')
            )
            ->groupBy('account_id')
            ->get();
        $paymentsByDate = DB::table('payments as p')
            ->join('payment_details as pd', 'pd.payment_id', '=', 'p.id')
            ->where('p.delete', '0')
            ->where('pd.delete', '0')
            ->where('p.company_id', Session::get('user_company_id'))
            ->where('pd.type', 'Debit')
            ->whereBetween('p.date', [$from_date, $to_date])
            ->select(
                DB::raw('DATE(p.date) as pay_date'),
                'pd.account_name as account_id',
                DB::raw('SUM(pd.debit) as amount')
            )
            ->groupBy('pay_date','pd.account_name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->pay_date.'_'.$item->account_id => $item
                ];
            });
            $payments = DB::table('payments as p')
                        ->join('payment_details as pd', 'pd.payment_id', '=', 'p.id')
                        ->select('account_name', DB::raw('SUM(debit) as total_debit'))
                        ->whereBetween('date', [$from_date, $to_date])
                        ->where('p.delete', '0')
                        ->where('pd.delete', '0')
                        ->where('pd.type', 'Debit')
                        ->where('p.company_id', Session::get('user_company_id'))
                        ->groupBy('account_name')
                        ->pluck('total_debit', 'account_name')
                        ->toArray();
           
        foreach ($purchases as $purchase) {
            $purchase->payment = $payments[$purchase->account_id] ?? 0;
            $purchase_ids = SupplierPurchaseVehicleDetail::where('company_id', Session::get('user_company_id'))
                ->where('account_id', $purchase->account_id)
                ->when($view_by === 'party',function($q){                
                    $q->where('status', '3');                
                })
                //->where('status', '3')
                ->where('group_id', $group_id) // ensure same group
                ->pluck('map_purchase_id');

            $gst_amount = DB::table('purchase_sundries')
                ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
                ->join('purchases', 'purchase_sundries.purchase_id', '=', 'purchases.id')
                ->whereIn('purchase_id', $purchase_ids)
                ->whereIn('nature_of_sundry', ['CGST', 'SGST', 'IGST'])
                ->whereBetween('purchases.date', [$from_date, $to_date])
                ->sum('amount');

            $purchase->gst_amt = $gst_amount;
        }


        $purchases_details = SupplierPurchaseVehicleDetail::with([
                'purchaseReport'=>function($q){
                    $q->leftjoin('supplier_sub_heads','supplier_purchase_reports.head_id','=','supplier_sub_heads.id');
                    $q->select('supplier_purchase_reports.*','supplier_sub_heads.sequence');
                },
                'locationInfo:id,name',
                'headInfo:id,name',
                'accountInfo:id,account_name',
                'purchaseInfo:id,total,voucher_no,date'
            ])
            ->leftjoin('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
            ->select(
                'supplier_purchase_vehicle_details.*',
                'purchases.voucher_no as purchase_voucher_no',
                'purchases.date as purchase_date',
                'purchases.total as purchase_amount',
                'purchases.taxable_amt as purchase_taxable_amount',

                DB::raw('(SELECT SUM(qty) FROM purchase_descriptions 
                        WHERE purchase_descriptions.purchase_id = purchases.id) AS purchase_qty'),

                DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]")
                        FROM purchase_descriptions 
                        WHERE purchase_descriptions.purchase_id = purchases.id) AS prices')
            )

            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.group_id', $group_id)
            ->when($view_by === 'party',function($q){                
                $q->where('supplier_purchase_vehicle_details.status', '3');                
            })
            //->where('supplier_purchase_vehicle_details.status', '3')
            ->when($id && $id != 'all', function ($q) use ($id) {
                $q->where('account_id', $id);
            })
            ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
            ->get();
            $purchasesGrouped = $purchases_details->groupBy(function ($item) {
                $date = $item->purchase_date ?? $item->entry_date;
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            });

            $finalRows = [];
            // echo "<pre>";
            // print_r($purchasesGrouped);die;
            $allDates = collect($purchasesGrouped->keys())
                ->merge($paymentsByDate->keys())
                ->filter()
                ->unique()
                ->sort();

            foreach ($allDates as $date) {
                $purchaseRows = $purchasesGrouped[$date] ?? collect();
                if ($purchaseRows->count()) {
                    $rowsByAccount = $purchaseRows->groupBy('account_id');
                    foreach ($rowsByAccount as $accountId => $rows) {
                        $key = $date . '_' . $accountId;
                        $paymentAmount = $paymentsByDate[$key]->amount ?? 0;
                        $rows = $rows->sortByDesc(function ($r) {
                            return !empty($r->purchase_voucher_no);
                        })->values();

                        foreach ($rows as $index => $row) {
                            if ($index == 0) {
                                $row->payment_amount = $paymentAmount;
                            } else {
                                $row->payment_amount = 0;
                            }
                            if($key=="2026-04-02_47803"){
                                //echo "-------".$row->payment_amount;
                            }
                            $finalRows[] = $row;
                        }
                    }
                }
            }

        // $grouped_by_date_raw = SupplierPurchaseVehicleDetail::with([
        //         'purchaseReport',
        //         'accountInfo:id,account_name'
        //     ])
        //     ->leftjoin('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
        //     ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
        //     ->when($view_by === 'party',function($q){                
        //         $q->where('supplier_purchase_vehicle_details.status', '3');                
        //     })
        //     //->where('supplier_purchase_vehicle_details.status', '3')
        //     ->where('supplier_purchase_vehicle_details.group_id', $group_id) // ensure same filter
        //     ->when($id && $id != 'all', function ($q) use ($id) {
        //         $q->where('account_id', $id);
        //     })
        //     ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
        //     ->orderBy('purchases.date', 'asc')
        //     ->select(
        //         'supplier_purchase_vehicle_details.*',
        //         'purchases.id as purchases_id',
        //         'purchases.date as purchase_date',
        //         'purchases.total as purchase_total',
        //         'purchases.taxable_amt as purchase_taxable_amt'
        //     )
        //     ->get();

        // $grouped_by_date = $grouped_by_date_raw->groupBy(function ($item) {
        //     return $item->entry_date ?? null;
        // });


        
        $supplier = Supplier::select('account_id')
                ->where('company_id', Session::get('user_company_id'))
                ->pluck('account_id');
        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('id', $supplier)
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->orderBy('account_name')
            ->get();
        $locations = SupplierLocation::where('company_id', Session::get('user_company_id'))
                    ->where('status', 1)
                    ->get();

        $heads = SupplierSubHead::leftjoin('sale-order-settings','supplier_sub_heads.group_id',"=","sale-order-settings.item_id")
            ->where('supplier_sub_heads.company_id',Session::get('user_company_id'))
            ->where('supplier_sub_heads.status',1)
            ->where('sale-order-settings.setting_type', '=', 'PURCHASE GROUP')
            ->where('sale-order-settings.setting_for', '=', 'PURCHASE ORDER')
            ->select('supplier_sub_heads.*','group_type')
            ->orderBy('sequence')
            ->get();

        
        $items = ManageItems::where('company_id', Session::get('user_company_id'))
            ->where('status', 1)
            ->orderBy('name')
            ->get();
        $waste_group_id = SaleOrderSetting::where('group_type','WASTE KRAFT')
            ->where('company_id', Session::get('user_company_id'))
            ->value('item_id');

        $boiler_group_id = SaleOrderSetting::where('group_type','BOILER FUEL')
            ->where('company_id', Session::get('user_company_id'))
            ->value('item_id');

        $item_groups = ItemGroups::where('company_id', Session::get('user_company_id'))
            ->where('delete', '=', '0')
            ->where('status', '=', '1')
            ->orderBy('group_name')
            ->get();

        $selected = $purchases->first();
        $purchase_prices = $selected->prices ?? "[]";

       
        // echo "<pre>";
        // print_r(collect($finalRows)->toArray());"</pre>";
        return view('supplier.supplier_purchase_report', [
            'purchases'         => $purchases,
            'accounts'          => $accounts,
            'items'             => $items,
            'id'                => $id,
            'waste_group_id'  => $waste_group_id,
            'boiler_group_id' => $boiler_group_id,
            'current_group_type' => 'WASTE KRAFT',
            'from_date'         => $from_date,
            'to_date'           => $to_date,
            'purchases_details' => collect($finalRows),
            'group_id'          => $group_id,
            'view_by'           => $view_by,
            // 'grouped_by_date'   => $grouped_by_date,
            'locations'      => $locations,
            'heads'          => $heads,
            'item_groups'    => $item_groups,
            'purchase_prices'=> $purchase_prices
        ]);
    }
    
    public function boilerFuelPurchaseReport($id = null, $from_date = null, $to_date = null)
    {        //updated by khushi
        $from_date = request()->from_date ?? $from_date;
        $to_date   = request()->to_date ?? $to_date;

        if (empty($from_date) || empty($to_date)) {
            $from_date = date('Y-m-01');
            $to_date   = date('Y-m-t');
        }

        $view_by = request()->view_by ?? 'party';

        $group_list = SaleOrderSetting::where('company_id', Session::get('user_company_id'))
            ->where('setting_type', 'PURCHASE GROUP')
            ->where('setting_for', 'PURCHASE ORDER')
            ->where('group_type', 'BOILER FUEL')
            ->select('item_id')
            ->first();

        if (!$group_list) {
            return redirect()->back()->with('error', 'BOILER FUEL group not found');
        }

        $group_id = $group_list->item_id;

        $purchases = SupplierPurchaseVehicleDetail::with([
                    'purchaseReport',
                    'locationInfo:id,name',
                    'accountInfo:id,account_name'
                ])
                ->join('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
                ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
                ->where('supplier_purchase_vehicle_details.status', '3')
                ->when($id && $id != 'all', function ($q) use ($id) {
                    $q->where('account_id', $id);
                })
                ->where('supplier_purchase_vehicle_details.group_id', $group_id) 
                ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
                ->select(
                    DB::raw('SUM(difference_total_amount) AS difference_sum'),
                    'account_id',
                    DB::raw('SUM(purchases.total) AS total_sum'),
                    DB::raw('SUM(purchases.taxable_amt) AS taxable_amt')
                )
                ->groupBy('account_id')
                ->get();
        $paymentsByDate = DB::table('payments as p')
            ->join('payment_details as pd', 'pd.payment_id', '=', 'p.id')
            ->where('p.delete', '0')
            ->where('pd.delete', '0')
            ->where('p.company_id', Session::get('user_company_id'))
            ->where('pd.type', 'Debit')
            ->whereBetween('p.date', [$from_date, $to_date])
            ->select(
                DB::raw('DATE(p.date) as pay_date'),
                'pd.account_name as account_id',
                DB::raw('SUM(pd.debit) as amount')
            )
            ->groupBy('pay_date','pd.account_name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->pay_date.'_'.$item->account_id => $item
                ];
            });
            foreach ($purchases as $purchase) {
                 $purchase->payment = $payments[$purchase->account_id] ?? 0;

                $purchase_ids = SupplierPurchaseVehicleDetail::where('company_id', Session::get('user_company_id'))
                    ->where('account_id', $purchase->account_id)
                    ->where('status', '3')
                    ->where('group_id', $group_id) 
                    ->pluck('map_purchase_id');
            }

        $purchases_details = SupplierPurchaseVehicleDetail::with([
                'purchaseReport' => function ($q) {
                    $q->leftJoin('supplier_sub_heads', 'supplier_purchase_reports.head_id', '=', 'supplier_sub_heads.id');
                    $q->select(
                        'supplier_purchase_reports.*',
                        'supplier_sub_heads.sequence'
                    );
                },
                'locationInfo:id,name',
                'accountInfo:id,account_name',
                'purchaseInfo:id,total,voucher_no,date,taxable_amt'
            ])
            ->join('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
            ->where('supplier_purchase_vehicle_details.company_id', Session::get('user_company_id'))
            ->where('supplier_purchase_vehicle_details.group_id', $group_id)
            ->where('supplier_purchase_vehicle_details.status', 3)
            ->when($id && $id !== 'all', function ($q) use ($id) {
                $q->where('supplier_purchase_vehicle_details.account_id', $id);
            })
            ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
            ->select(
                'supplier_purchase_vehicle_details.*',
                'purchases.voucher_no as purchase_voucher_no',
                'purchases.date as purchase_date',
                'purchases.total as purchase_amount',
                'purchases.taxable_amt as purchase_taxable_amount',

                DB::raw('(SELECT SUM(qty)
                        FROM purchase_descriptions
                        WHERE purchase_descriptions.purchase_id = purchases.id) AS purchase_qty'),

                DB::raw('(SELECT CONCAT("[", GROUP_CONCAT(CAST(price AS DECIMAL(10,2)) SEPARATOR ","), "]")
                        FROM purchase_descriptions
                        WHERE purchase_descriptions.purchase_id = purchases.id) AS prices')
            )
            ->orderBy('supplier_purchase_vehicle_details.entry_date')
            ->orderBy('supplier_purchase_vehicle_details.voucher_no')
            ->get();
            $purchasesGrouped = $purchases_details->groupBy(function ($item) {
                $date = $item->purchase_date ?? $item->entry_date;
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            });

            $finalRows = [];

            $allDates = collect($purchasesGrouped->keys())
                ->merge($paymentsByDate->keys())
                ->filter()
                ->unique()
                ->sort();

            foreach ($allDates as $date) {

                $purchaseRows = $purchasesGrouped[$date] ?? collect();

                if ($purchaseRows->count()) {

                    $rowsByAccount = $purchaseRows->groupBy('account_id');

                    foreach ($rowsByAccount as $accountId => $rows) {

                        $key = $date . '_' . $accountId;
                        $paymentAmount = $paymentsByDate[$key]->amount ?? 0;

                        // ✅ sort invoice rows first
                        $rows = $rows->sortByDesc(function ($r) {
                            return !empty($r->purchase_voucher_no);
                        })->values();

                        foreach ($rows as $index => $row) {

                            if ($index == 0) {
                                $row->payment_amount = $paymentAmount;
                            } else {
                                $row->payment_amount = 0;
                            }

                            $finalRows[] = $row;
                        }
                    }
                }
            }
        $heads = SupplierSubHead::leftJoin(
                'sale-order-settings',
                'supplier_sub_heads.group_id',
                '=',
                'sale-order-settings.item_id'
            )
            ->where('supplier_sub_heads.company_id', Session::get('user_company_id'))
            ->where('supplier_sub_heads.status', 1)
            ->where('sale-order-settings.setting_type', 'PURCHASE GROUP')
            ->where('sale-order-settings.setting_for', 'PURCHASE ORDER')
            ->select('supplier_sub_heads.*', 'sale-order-settings.group_type')
            ->orderBy('supplier_sub_heads.sequence')
            ->get();

        $supplier = FuelSupplier::select('account_id')
                ->where('company_id', Session::get('user_company_id'))
                ->pluck('account_id');
        $accounts = Accounts::where('delete', '0')
            ->where('status', '1')
            ->whereIn('id', $supplier)
            ->whereIn('company_id', [Session::get('user_company_id'), 0])
            ->orderBy('account_name')
            ->get();

        $locations = SupplierLocation::where('company_id', Session::get('user_company_id'))
            ->where('status', 1)
            ->get();
        $items = ManageItems::join('sale-order-settings','manage_items.g_name','=','sale-order-settings.item_id')
            ->where('sale-order-settings.setting_type', 'PURCHASE GROUP')
            ->where('sale-order-settings.setting_for', 'PURCHASE ORDER')
            ->where('sale-order-settings.group_type', 'BOILER FUEL')
            ->where('manage_items.company_id', Session::get('user_company_id'))
            ->where('manage_items.status', 1)
            ->where('manage_items.delete', 0)
            ->select('manage_items.id', 'manage_items.name')
            ->orderBy('manage_items.name')
            ->get();


        $waste_group_id = SaleOrderSetting::where('group_type','WASTE KRAFT')
            ->where('company_id', Session::get('user_company_id'))
            ->value('item_id');

        $boiler_group_id = SaleOrderSetting::where('group_type','BOILER FUEL')
            ->where('company_id', Session::get('user_company_id'))
            ->value('item_id');

        $item_groups = ItemGroups::where('company_id', Session::get('user_company_id'))
            ->where('delete', 0)
            ->where('status', 1)
            ->orderBy('group_name')
            ->get();

        return view('supplier.supplier_purchase_report', [
            'purchases'         => $purchases,
            'accounts'          => $accounts,
            'items'             => $items,
            'waste_group_id'  => $waste_group_id,
            'boiler_group_id' => $boiler_group_id,
            'current_group_type' => 'BOILER FUEL',
            'id'                => $id,
            'from_date'         => $from_date,
            'to_date'           => $to_date,
            'purchases_details' => collect($finalRows),
            'group_id'          => $group_id,
            'view_by'           => $view_by,
            'locations'         => $locations,
            'heads'             => $heads,
            'item_groups'       => $item_groups
        ]);
    }

    public function purchaseGroupReport($group_id, $id = null, $from_date = null, $to_date = null)
    {
        $company_id = Session::get('user_company_id');

        $from = date('Y-m-d', strtotime($from_date));
        $to   = date('Y-m-d', strtotime($to_date));

        $purchases = SupplierPurchaseVehicleDetail::join('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
            ->where('supplier_purchase_vehicle_details.company_id', $company_id)
            ->where('supplier_purchase_vehicle_details.status', 3)
            ->where('supplier_purchase_vehicle_details.group_id', $group_id)
            ->when($id, function ($q) use ($id) {
                if ($id != 'all') {
                    $q->where('supplier_purchase_vehicle_details.account_id', $id);
                }
            })
            ->whereBetween('purchases.date', [$from, $to])
            ->select(
                'supplier_purchase_vehicle_details.account_id',
                DB::raw('SUM(purchases.total) as actual_sum'),
                DB::raw('SUM(supplier_purchase_vehicle_details.difference_total_amount) as diff_sum')
            )
            ->groupBy('supplier_purchase_vehicle_details.account_id')
            ->with(['accountInfo:id,account_name'])
            ->get();

        $purchases_details = SupplierPurchaseVehicleDetail::join('purchases', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'purchases.id')
            ->leftJoin('supplier_purchase_reports', 'supplier_purchase_vehicle_details.map_purchase_id', '=', 'supplier_purchase_reports.purchase_id')
            ->where('supplier_purchase_vehicle_details.company_id', $company_id)
            ->where('supplier_purchase_vehicle_details.status', 3)
            ->where('supplier_purchase_vehicle_details.group_id', $group_id)
            ->when($id, function ($q) use ($id) {
                if ($id != 'all') {
                    $q->where('supplier_purchase_vehicle_details.account_id', $id);
                }
            })
            ->whereBetween('purchases.date', [$from, $to])
            ->select(
                'supplier_purchase_reports.head_id',
                'supplier_purchase_reports.head_qty',
                'supplier_purchase_reports.head_difference_amount'
            )
            ->with(['headInfo:id,name'])
            ->get();

        $group_ids = CommonHelper::getAllChildGroupIds($group_id, $company_id);
        $group_ids[] = $group_id;

        $accounts = Accounts::where('delete', 0)
            ->where('status', 1)
            ->whereIn('company_id', [$company_id, 0])
            ->whereIn('under_group', $group_ids)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();

        return view('supplier.supplier_purchase_report', compact(
            'purchases',
            'purchases_details',
            'accounts',
            'id',
            'from_date',
            'to_date'
        ));
    }
    public function revertInProcessPurchaseReport(Request $request){

        $row_id = $request->row_id;
        $company_id = auth()->user()->company_id ?? Session::get('user_company_id');
        $update = SupplierPurchaseVehicleDetail::where('company_id', $company_id)
                            ->where('id', $row_id)
                            ->update(['status'=> 1]);
        if ($update) {
            echo json_encode(['status' => 1, 'message' => 'Purchase report reverted to In Process successfully.']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
    public function wasteKraftMerge(Request $request){
        $row_id = $request->id;
        $update = SupplierPurchaseVehicleDetail::where('id', $row_id)
                            ->update(['status'=> 5,"merge_slip_no"=>$request->merge_slip_no]);
        if ($update) {
            echo json_encode(['status' => 1, 'message' => 'Merge successfully.']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Something went wrong.']);
        }
    }
    public function resizeImages(Request $request){
        $vehicle = SupplierPurchaseVehicleDetail::orderBy('id','asc')
                                                    ->skip(1701)
                                                    ->take(100)
                                                ->get();
        if(count($vehicle)>0){
            foreach ($vehicle as $key => $value) {
                // echo "<pre>";
            // print_r($vehicle->toArray());
                if($value->image_1!=""){
                    $destination = public_path($value->image_1);
                    $destination1 = public_path($value->image_1);
                    $this->compressImage($destination1,$destination);
                }
                if($value->image_2!=""){
                    $destination = public_path($value->image_2);
                    $destination1 = public_path($value->image_2);
                    $this->compressImage($destination1,$destination);
                }
                if($value->image_3!=""){
                    $destination = public_path($value->image_3);
                    $destination1 = public_path($value->image_3);
                    $this->compressImage($destination1,$destination);
                }
                echo $value->voucher_no;
                echo "------";
            }
            
            
        }
        
    }
    public function reportsDashboard(Request $request)
    {
        $from_date  = $request->from_date ?? date('Y-m-d');
        $to_date    = $request->to_date ?? date('Y-m-d');
        $item_group = $request->item_group ?? 'all';

        $baseQuery = SupplierPurchaseVehicleDetail::join(
                        'accounts',
                        'supplier_purchase_vehicle_details.account_id',
                        '=',
                        'accounts.id'
                    )
                    ->join(
                        'item_groups',
                        'supplier_purchase_vehicle_details.group_id',
                        '=',
                        'item_groups.id'
                    )
                    ->join(
                        'sale-order-settings',
                        'sale-order-settings.item_id',
                        '=',
                        'supplier_purchase_vehicle_details.group_id'
                    )
                    ->where('supplier_purchase_vehicle_details.company_id', session('user_company_id'))
                    ->where('sale-order-settings.setting_type', 'PURCHASE GROUP')
                    ->where('sale-order-settings.setting_for', 'PURCHASE ORDER')
                    ->whereBetween(
                        'supplier_purchase_vehicle_details.entry_date',
                        [$from_date, $to_date]
                    );

        $itemGroupOptions = DB::table('item_groups')
                ->join('sale-order-settings','item_groups.id',"=","sale-order-settings.item_id")
                ->where('item_groups.company_id', Session::get('user_company_id'))
                ->whereRaw("item_groups.delete = '0'")
                ->whereRaw("item_groups.status = '1'")
                ->where('setting_type','PURCHASE GROUP')
                ->where('setting_for','PURCHASE ORDER')
                ->orderBy('group_name')
                ->pluck('group_name');

        if ($item_group !== 'all') {
            $baseQuery->where('item_groups.group_name', $item_group);
        }

        $records = $baseQuery
            ->select(
                'supplier_purchase_vehicle_details.entry_date',
                'accounts.account_name',
                'item_groups.group_name',
                'supplier_purchase_vehicle_details.vehicle_no',
                'supplier_purchase_vehicle_details.tare_weight',
                'supplier_purchase_vehicle_details.voucher_no',
                'supplier_purchase_vehicle_details.gross_weight',
                'sale-order-settings.group_type'
            )
            ->orderBy('supplier_purchase_vehicle_details.entry_date')
            ->orderBy('item_groups.group_name')
            ->get();

        $grouped = $records->groupBy([
            fn ($row) => $row->entry_date,
            fn ($row) => $row->group_type,
            fn ($row) => $row->group_name,
        ]);

        return view(
            'supplier.supplier_purchase_reports_dashboard',
            compact(
                'grouped',
                'from_date',
                'to_date',
                'item_group',
                'itemGroupOptions'
            )
        );
    }
    
    public function getUserDefaultStatus()
{
    $userId = session('user_id');

    $modules = DB::table('privileges_module_mappings')
        ->where('employee_id', $userId)
        ->whereIn('module_id', [184,185,186])
        ->whereNull('deleted_at')
        ->pluck('module_id')
        ->toArray();

    if (in_array(184, $modules)) {
        $status = 0;
    } elseif (in_array(185, $modules)) {
        $status = 2;
    } elseif (in_array(186, $modules)) {
        $status = 3;
    } else {
        $status = 0;
    }

    return response()->json(['status' => $status]);
}

public function getUserDefaultStatusBoilerFuel()
{
    $userId = session('user_id');

    $modules = DB::table('privileges_module_mappings')
        ->where('employee_id', $userId)
        ->whereIn('module_id', [111,112,113])
        ->whereNull('deleted_at')
        ->pluck('module_id')
        ->toArray();

    if (in_array(111, $modules)) {
        $status = 0;
    } elseif (in_array(112, $modules)) {
        $status = 2;
    } elseif (in_array(113, $modules)) {
        $status = 3;
    } else {
        $status = 0;
    }

    return response()->json(['status' => $status]);
}

    public function sparePartVehicleEntry()
{
    $companyId = Session::get('user_company_id');

    // Get SPARE PART group IDs
    $sparePartGroupIds = SaleOrderSetting::where('company_id', $companyId)
        ->where('setting_type', 'PURCHASE GROUP')
        ->where('setting_for', 'PURCHASE ORDER')
        ->where('group_type', 'SPARE PART')
        ->pluck('item_id')
        ->toArray();

    // Vehicle entries ONLY for SPARE PART
    $vehicle_entries = SupplierPurchaseVehicleDetail::join(
            'accounts',
            'supplier_purchase_vehicle_details.account_id',
            '=',
            'accounts.id'
        )
        ->join(
            'item_groups',
            'supplier_purchase_vehicle_details.group_id',
            '=',
            'item_groups.id'
        )
        ->where('supplier_purchase_vehicle_details.company_id', $companyId)
        ->whereIn('supplier_purchase_vehicle_details.group_id', $sparePartGroupIds)
        ->where('supplier_purchase_vehicle_details.status', 0) 
        ->select(
            'supplier_purchase_vehicle_details.*',
            'accounts.account_name',
            'item_groups.group_name'
        )
        ->orderBy('supplier_purchase_vehicle_details.entry_date', 'desc')
        ->get();

    return view(
        'supplier.spare_part_vehicle_entry',
        compact('vehicle_entries')
    );
}

public function getGroupType($groupId)
{
    $companyId = Session::get('user_company_id');

    $group = SaleOrderSetting::where('company_id', $companyId)
        ->where('item_id', $groupId)
        ->select('group_type')
        ->first();

    return response()->json([
        'group_type' => $group ? $group->group_type : null
    ]);
}


}
