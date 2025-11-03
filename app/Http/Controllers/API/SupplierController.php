<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\Models\Supplier;
use App\Models\Accounts;
use App\Models\SupplierPurchaseVehicleDetail;
use App\Models\SupplierSubHead;
use App\Models\SupplierLocationRates;
use App\Models\SupplierLocation;
use App\Models\SupplierPurchaseReport;
use App\Models\SaleOrderSetting;
use App\Models\FuelSupplierRate;
use App\Models\ManageItems;
use App\Models\FuelItemRates;
class SupplierController extends Controller
{
    public function accountList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            
        ], [
            'company_id.required' => 'Company id is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $supplier = Supplier::select('account_id')
                                ->where('company_id',$request->company_id)
                                ->pluck('account_id');
        $accounts = Accounts:: whereIn('id',$supplier)
                              ->select('accounts.id','accounts.account_name')
                              ->orderBy('account_name')
                              ->get();         
         return response()->json(['code' => 200, 'message' => "Accounts List",'data'=> $accounts]);
    }
    public function addPurchaseVehicleEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'vehicle_no' => 'required',
            'group_id' => 'required',
            'gross_weight' => 'required',
            'account_id' => 'required',
        ], [
            'company_id.required' => 'Company id is required.',
            'vehicle_no.required' => 'Company id is required.',
            'group_id.required' => 'Company id is required.',
            'gross_weight.required' => 'Company id is required.',
            'account_id.required' => 'Company id is required.',            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $purchase_info = new SupplierPurchaseVehicleDetail;
        $purchase_info->vehicle_no = $request->vehicle_no;
        $purchase_info->entry_date = date('Y-m-d',strtotime($request->date));
        $purchase_info->group_id = $request->group_id;
        $purchase_info->account_id = $request->account_id;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->company_id = $request->company_id;
        $purchase_info->created_at =  Carbon::now();
        if($purchase_info->save()){
            return response()->json(['code' => 200, 'message' => "Vehicle entry saved successfully.",'data'=> ""]);
        }else{
            $this->failedMessage();
        }        
    }
    public function editPurchaseVehicleEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'vehicle_no' => 'required',
            'group_id' => 'required',
            'gross_weight' => 'required',
            'account_id' => 'required',
            'id' => 'required',
        ], [
            'company_id.required' => 'Company id is required.',
            'vehicle_no.required' => 'Company id is required.',
            'group_id.required' => 'Company id is required.',
            'gross_weight.required' => 'Company id is required.',
            'account_id.required' => 'Company id is required.',
            'id.required' => 'Id is required.', 
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $purchase_info = SupplierPurchaseVehicleDetail::find($request->id);
        $purchase_info->vehicle_no = $request->vehicle_no;
        $purchase_info->entry_date = date('Y-m-d',strtotime($request->date));
        $purchase_info->group_id = $request->group_id;
        $purchase_info->account_id = $request->account_id;
        $purchase_info->gross_weight = $request->gross_weight;
        $purchase_info->company_id = $request->company_id;
        $purchase_info->updated_at =  Carbon::now();
        if($purchase_info->save()){
            return response()->json(['code' => 200, 'message' => "Vehicle entry updated successfully.",'data'=> ""]);
        }else{
            $this->failedMessage();
        }        
    }
    public function deletePurchaseVehicleEntry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ], [
            'id.required' => 'Id is required.', 
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $purchase_info = SupplierPurchaseVehicleDetail::find($request->id);
        if($purchase_info==null){
            return response()->json(['code' => 422, 'message' => "No data found.",'data'=> ""]);
        }
        if($purchase_info->status==0){
            return response()->json(['code' => 422, 'message' => "You do not have permission to delete.",'data'=> ""]);
        }
        if($purchase_info->delete()){
            return response()->json(['code' => 200, 'message' => "Vehicle entry deleted successfully.",'data'=> ""]);
        }else{
            $this->failedMessage();
        }        
    }
    
    public function purchaseVehicleEntryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'type' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'type.required' => 'Report type is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if($request->type=="All" || $request->type=="pending_report"){
            $pending_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.status', 0)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'entry_date',
                    'reapproval',
                    'supplier_purchase_vehicle_details.status',
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'difference_total_amount',
                    'tare_weight',
                    'location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        }
        if($request->type=="All" || $request->type=="in_process_report"){
            $in_process_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.status', 1)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'entry_date',
                    'reapproval',
                    'supplier_purchase_vehicle_details.status',
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'difference_total_amount',
                    'tare_weight',
                    'location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        }
        if($request->type=="All" || $request->type=="pending_for_approval_report"){
            $pending_for_approval_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.status', 2)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'entry_date',
                    'reapproval',
                    'supplier_purchase_vehicle_details.status',
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'difference_total_amount',
                    'tare_weight',
                    'location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        }
        if($request->type=="All" || $request->type=="approved_report"){
            $approved_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.status', 3)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->select(
                    'supplier_purchase_vehicle_details.id',
                    'gross_weight',
                    'supplier_purchase_vehicle_details.vehicle_no',
                    'accounts.account_name',
                    'item_groups.group_name',
                    'supplier_purchase_vehicle_details.account_id',
                    'supplier_purchase_vehicle_details.group_id',
                    'map_purchase_id',
                    'purchases.voucher_no as purchase_voucher_no',
                    'purchases.date as purchase_date',
                    'purchases.total as purchase_amount',
                    'entry_date',
                    'reapproval',
                    'supplier_purchase_vehicle_details.status',
                    'supplier_purchase_vehicle_details.image_1',
                    'supplier_purchase_vehicle_details.image_2',
                    'supplier_purchase_vehicle_details.image_3',
                    'supplier_purchase_vehicle_details.voucher_no',
                    'difference_total_amount',
                    'tare_weight',
                    'location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->get();
        }        
        $data = [];
        if($request->type=="All"){
            $data = array("pending_report"=>$pending_report,"in_process_report"=>$in_process_report,"pending_for_approval_report"=>$pending_for_approval_report,"approved_report"=>$approved_report);
        }elseif($request->type=="pending_report"){
            $data = array("pending_report"=>$pending_report);
        }elseif($request->type=="in_process_report"){
            $data = array("in_process_report"=>$in_process_report);
        }elseif($request->type=="pending_for_approval_report"){
            $data = array("pending_for_approval_report"=>$pending_for_approval_report);
        }elseif($request->type=="approved_report"){
            $data = array("approved_report"=>$approved_report);
        }
        return response()->json(['code' => 200, 'message' => "Report List",'data'=> $data]);
    }
    public function supplierHeadList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'id.required' => 'Id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $heads = SupplierSubHead::with(['report' => function($q) use ($request) {
                                        $q->where('purchase_id', $request->id);
                                        $q->select('id','purchase_id','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
                                    }])
                                ->where('company_id',$request->company_id)
                                ->where('status',1)
                                ->select('id','name')
                                ->orderBy('sequence')
                                ->get();
        return response()->json(['code' => 200, 'message' => "Head List",'data'=> $heads]);
    }
    public function locationByAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'account_id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'account_id.required' => 'Account id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $location_id = SupplierLocationRates::where('company_id', $request->company_id)
                                            ->where('account_id', $request->account_id)
                                            ->pluck('location')
                                            ->unique()
                                            ->values();
        
        $location = SupplierLocation::select('id','name')
                                    ->where('status',1)
                                    ->whereIn('id',$location_id)
                                    ->where('company_id',$request->company_id)
                                    ->orderBy('name')
                                    ->get();
        return response()->json(['code' => 200, 'message' => "Report List",'data'=> $location]);
    }
    public function headContractRateByLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'account_id' => 'required',
            'location_id' => 'required',
            'date' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'account_id.required' => 'Account id is required.',
            'location_id.required' => 'Location id is required.',
            'date.required' => 'Date is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $rate = SupplierLocationRates::select('head_id','head_rate')
                                        ->where('company_id',$request->company_id)
                                        ->where('account_id',$request->account_id)
                                        ->where('r_date','<=',date('Y-m-d',strtotime($request->date)))
                                        ->where('location',$request->location_id)
                                        ->get();
        return response()->json(['code' => 200, 'message' => "Report List",'data'=> $rate]);
    }
    public function storeSupplierPurchaseReport(Request $request)
    {       
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'account_id' => 'required',
            'location_id' => 'required',
            'entry_date' => 'required',
            'group_id' => 'required',
            'vehicle_no' => 'required',
            'tare_weight' => 'required',
            'slip_number' => 'required',
            'difference_total_amount' => 'required',
            'head_data' => 'required',
            'id' => 'required',
            'user_id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',
            'account_id.required' => 'Account id is required.',
            'location_id.required' => 'Location id is required.',
            'entry_date.required' => 'Entry date required.',
            'group_id.required' => 'Group id required.',
            'vehicle_no.required' => 'Vehicle no. required.',
            'tare_weight.required' => 'Tare weight required.',
            'slip_number.required' => 'Slip number required.',
            'difference_total_amount.required' => 'Difference total amount required.',
            'head_data.required' => 'Head Data required.',
            'id.required' => 'Id required.',
            'user_id' => 'User Id required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        SupplierPurchaseReport::where('company_id',$request->company_id)
                                ->where('purchase_id',$request->id)
                                ->delete();
        $purchase_vehicle = SupplierPurchaseVehicleDetail::find($request->id);
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
        $purchase_vehicle->location = $request->location_id;
        $purchase_vehicle->voucher_no = $request->slip_number;
        $purchase_vehicle->processed_by = $request->user_id;
        if($purchase_vehicle->save()){            
            foreach ($request->head_data as $key => $value) {
                $report = new SupplierPurchaseReport;
                $report->purchase_id = $request->id;
                $report->head_id = $value['head_id'];
                $report->head_qty = $value['head_qty'];
                $report->head_bill_rate = $value['head_bill_rate'];
                $report->head_contract_rate = $value['head_contract_rate'];
                $report->head_difference_amount = $value['head_difference_amount'];;
                $report->company_id = $request->company_id;
                $report->status = 1;
                $report->created_by = $request->user_id;
                $report->created_at = Carbon::now();
                $report->save();
            }
            return response()->json(['code' => 200, 'message' => "Report Saved Successfully.",'data'=>'']);
        }else{
            $this->failedMessage();
        }
    }
    public function viewSupplierPurchaseReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'id' => 'required',
            
        ], [
            'company_id.required' => 'Company id is required.',            
            'id.required' => 'Id required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $report = SupplierPurchaseReport::select('head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount')
                    ->where('purchase_id',$request->id)
                    ->get();
        $purchase = SupplierPurchaseVehicleDetail::join('supplier_locations','supplier_purchase_vehicle_details.location','=','supplier_locations.id')
                                        ->select('image_1','image_2','image_3','voucher_no','difference_total_amount','supplier_locations.name as location_name','location','tare_weight')
                                        ->find($request->id);
        return response()->json(['code' => 200, 'message' => "Report Data",'data'=>array('report'=>$report,'purchase'=>$purchase)]);
    }
    public function uploadReportImage(Request $request)
    {
        // validate
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'id' => 'required',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            
        ], [
            'company_id.required' => 'Company id is required.',            
            'id.required' => 'Id required.',
            'images.*' => 'Valid images required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $paths = [];
        $purchase_vehicle = SupplierPurchaseVehicleDetail::find($request->id);
       
       // if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key=>$image) {
                 // Save file
                $filename = time().'_'.$image->getClientOriginalName();
                $filename = str_replace(" ","_",$filename);
                $path = 'purchase_images/'.$filename;
                $image->move(public_path('purchase_images'), $filename);
                // Save path in DB 
                $purchase = SupplierPurchaseVehicleDetail::find($request->id);
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
                $purchase_vehicle->completed_by = $request->company_id;
                $purchase_vehicle->save();
            }
        //}
        return response()->json(['code' => 200, 'message' => "Image Uploaded Successfully.",'data'=>'']);
    }
    public function approveReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'id' => 'required',
            
        ], [
            'user_id.required' => 'User id is required.',            
            'id.required' => 'Id required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $purchase = SupplierPurchaseVehicleDetail::find($request->id);
        $purchase->status = 3;
        $purchase->reapproval = 0;
        $purchase->approved_by = $request->user_id;
        if($purchase->save()){
            return response()->json(['code' => 200, 'message' => "Report Approved Successfully.",'data'=>'']);
        }else{
            $this->failedMessage();
        }
    }
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
    public function purchaseItemType(Request $request)
    {
        $setting  = SaleOrderSetting::join("item_groups","sale-order-settings.item_id","=","item_groups.id")
                        ->where('sale-order-settings.company_id',$request->company_id)
                        ->where('setting_type','PURCHASE GROUP')
                        ->where('setting_for','PURCHASE ORDER')
                        ->where('sale-order-settings.status','1')
                        ->select('group_name','group_type','item_id')
                        ->get();
        return response()->json(['code' => 200, 'message' => 'Purchase Item Type','data'=> $setting]);
    }
    public function areaByAccount(Request $request)
    {
        $item_id = FuelSupplierRate::where('company_id', $request->company_id)
                                            ->where('account_id', $request->account_id)
                                            ->pluck('item_id')
                                            ->unique()
                                            ->values();
        
        $items = ManageItems::select('id','name')
                                    ->where('status','1')
                                    ->whereIn('id',$item_id)
                                    ->where('company_id',$request->company_id)
                                    ->orderBy('name')
                                    ->get();
        return response()->json(['code' => 200, 'message' => 'Purchase Item Type','data'=> $items]);
    }
    public function contractRateByArea(Request $request)
    {
        $supplier_max_date = FuelSupplierRate::where('company_id',$request->company_id)
                                        ->where('item_id',$request->area_id)
                                        ->where('account_id',$request->account_id)
                                        ->where('price_date','<=',$request->date)
                                        ->max('price_date');
        $max_date = FuelItemRates::where('company_id',$request->company_id)
                                        ->where('item_id',$request->area_id)
                                        ->where('item_price_date','<=',$request->date)
                                        ->max('item_price_date');
        if($max_date>$supplier_max_date){
            $rate = FuelItemRates::select('item_price')
                                        ->where('company_id',$request->company_id)
                                        ->where('item_price_date','<=',$request->date)
                                        ->where('item_id',$request->area_id)
                                        ->orderBy('item_price_date','desc')
                                        ->first();
        }else{
            $rate = FuelSupplierRate::select('price as item_price')
                                        ->where('company_id',$request->company_id)
                                        ->where('account_id',$request->account_id)
                                        ->where('price_date','<=',$request->date)
                                        ->where('item_id',$request->area_id)
                                        ->orderBy('price_date','desc')
                                        ->first();
        }
        return response()->json(['code' => 200, 'message' => 'Purchase Item Type','data'=> $rate]);
    }
    
}
