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
use App\Models\SparePart;
use App\Models\ManageItems;
use App\Models\FuelItemRates;
use App\Models\PrivilegesModuleMapping;
use App\Helpers\CommonHelper;
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
        $purchase_info->item_id = $request->item;
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
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->where('supplier_purchase_vehicle_details.status', 0)
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
                    'supplier_purchase_vehicle_details.location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->orderBy('entry_date', 'ASC')
                ->get();
        }
        if($request->type=="All" || $request->type=="in_process_report"){
            $in_process_report = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->where('supplier_purchase_vehicle_details.status', 1)
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
                    'supplier_purchase_vehicle_details.location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->orderBy('entry_date', 'ASC')
                ->get();
                $in_process_report = $in_process_report->map(function ($row) {

                    $row->image_1 = $row->image_1 
                        ? url('public/' . $row->image_1) 
                        : null;
                
                    $row->image_2 = $row->image_2 
                        ? url('public/' . $row->image_2) 
                        : null;
                
                    $row->image_3 = $row->image_3 
                        ? url('public/' . $row->image_3) 
                        : null;
                
                    return $row;
                });
                $in_process_report->transform(function ($report) {
                    $heads = DB::table('supplier_purchase_reports')
                        ->where('purchase_id', $report->id)
                        ->leftJoin('supplier_sub_heads','supplier_purchase_reports.head_id','supplier_sub_heads.id')
                        ->select(
                            'supplier_sub_heads.name as subHeadName',
                            'head_id',
                            'head_contract_rate',
                            'head_bill_rate',
                            'head_qty',
                            'head_difference_amount',
                            DB::raw('(head_contract_rate * head_qty) as report_amt')
                        )
                    ->get();
                    $report->head_data = $heads;
                    $report->total_report_amt = round(
                        $heads->sum(function($h){
                            return (float) $h->report_amt;
                        }),
                        2
                    );
                    return $report;
                });
        }
        if($request->type=="All" || $request->type=="pending_for_approval_report"){
            $pending_for_approval_report =  SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                ->where('supplier_purchase_vehicle_details.status', 2)
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
                    'supplier_purchase_vehicle_details.location',
                    DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                    DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                )
                ->orderBy('entry_date', 'ASC')
                ->get();
                
                $pending_for_approval_report = $pending_for_approval_report->map(function ($row) {

                    $row->image_1 = $row->image_1 
                        ? url('public/' . $row->image_1) 
                        : null;
                
                    $row->image_2 = $row->image_2 
                        ? url('public/' . $row->image_2) 
                        : null;
                
                    $row->image_3 = $row->image_3 
                        ? url('public/' . $row->image_3) 
                        : null;
                
                    return $row;
                });

                
                 $pending_for_approval_report->transform(function ($report) {

         $heads = DB::table('supplier_purchase_reports')
            ->where('purchase_id', $report->id)
            ->leftJoin('supplier_sub_heads','supplier_purchase_reports.head_id','supplier_sub_heads.id')
            ->select(
                'supplier_sub_heads.name as subHeadName',
                'head_id',
                'head_contract_rate',
                'head_bill_rate',
                'head_qty',
                'head_difference_amount',
                DB::raw('(head_contract_rate * head_qty) as report_amt')
            )
            ->get();

        $report->head_data = $heads;
        
        $report->total_report_amt = round(
            $heads->sum(function($h){
                return (float) $h->report_amt;
            }),
            2
        );

        return $report;
    });
        }
        if($request->type=="All" || $request->type=="approved_report"){
           $approved_query = SupplierPurchaseVehicleDetail::join('accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id')
                        ->leftJoin('purchases','supplier_purchase_vehicle_details.map_purchase_id','=','purchases.id')
                        ->join('item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id')
                        ->where('supplier_purchase_vehicle_details.company_id', $request->company_id)
                        ->where('supplier_purchase_vehicle_details.group_id', $request->group_id)
                        ->where('supplier_purchase_vehicle_details.status', 3)
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
                            'supplier_purchase_vehicle_details.location',
                            DB::raw('(SELECT sum(qty) FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id) as purchase_qty'),
                            DB::raw('(SELECT price FROM purchase_descriptions WHERE purchase_descriptions.purchase_id = purchases.id LIMIT 1) as price')
                        );
                    
                    if ($request->from_date && $request->to_date) {
                    
                        $approved_query->whereBetween(
                            DB::raw("STR_TO_DATE(entry_date, '%Y-%m-%d')"), 
                            [
                                date('Y-m-d', strtotime($request->from_date)),
                                date('Y-m-d', strtotime($request->to_date))
                            ]
                        );
                    
                        $approved_query->orderBy('entry_date', 'ASC');
                    } 
                    else {
                        $approved_query->orderBy('entry_date', 'DESC')
                                       ->limit(10);
                    }
                    
                    $approved_report = $approved_query->get();

                    $approved_report = $approved_report->map(function ($row) {
    
                        $row->image_1 = $row->image_1 
                            ? url('public/' . $row->image_1) 
                            : null;
                    
                        $row->image_2 = $row->image_2 
                            ? url('public/' . $row->image_2) 
                            : null;
                    
                        $row->image_3 = $row->image_3 
                            ? url('public/' . $row->image_3) 
                            : null;
                    
                        return $row;
                    });
                    $approved_report->transform(function ($report) {
                        $heads = DB::table('supplier_purchase_reports')
                            ->where('purchase_id', $report->id)
                            ->leftJoin('supplier_sub_heads','supplier_purchase_reports.head_id','supplier_sub_heads.id')
                            ->select(
                                'supplier_sub_heads.name as subHeadName',
                                'head_id',
                                'head_contract_rate',
                                'head_bill_rate',
                                'head_qty',
                                'head_difference_amount',
                                DB::raw('(head_contract_rate * head_qty) as report_amt')
                            )
                            ->get();
                        $report->head_data = $heads;
                        $report->total_report_amt = round( $heads->sum(function($h){ return (float) $h->report_amt; }),2);
                        return $report;
                    });

        }
        //Privileges
        $privileges_arr = [];
        if(isset($request->user_id) && $request->user_id!=""){
            //190-PENDING REPORT START
            $privileges = PrivilegesModuleMapping::where('module_id',190)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'PENDING REPORT START');
            }
            //192-IN PROCESS REPORT ADD IMAGE
            $privileges = PrivilegesModuleMapping::where('module_id',192)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'IN PROCESS REPORT ADD IMAGE');
            }
            //197 - PENDING IN APPROVAL APPROVE
            $privileges = PrivilegesModuleMapping::where('module_id',197)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'PENDING IN APPROVAL APPROVE');
            }
        }
        $data = [];
        if($request->type=="All"){
            $data = array("pending_report"=>$pending_report,"in_process_report"=>$in_process_report,"pending_for_approval_report"=>$pending_for_approval_report,"approved_report"=>$approved_report,'privileges'=>$privileges_arr);
        }elseif($request->type=="pending_report"){
            $data = array("pending_report"=>$pending_report,'privileges'=>$privileges_arr);
        }elseif($request->type=="in_process_report"){
            $data = array("in_process_report"=>$in_process_report,'privileges'=>$privileges_arr);
        }elseif($request->type=="pending_for_approval_report"){
            $data = array("pending_for_approval_report"=>$pending_for_approval_report,'privileges'=>$privileges_arr);
        }elseif($request->type=="approved_report"){
            $data = array("approved_report"=>$approved_report,'privileges'=>$privileges_arr);
        }
        return response()->json(['code' => 200, 'message' => "Report List",'data'=> $data]);
    }
    public function supplierHeadList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'group_id' => 'required',
            
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
                                ->where('group_id',$request->group_id)
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
            'head_data.required' => 'Head Data required.',
            'id.required' => 'Id required.',
            'user_id' => 'User Id required',
        ]);
                    $headData = $request->head_data;
            
            // If sent as JSON string
            if (is_string($headData)) {
                $headData = json_decode($headData, true);
            }
            
            if (!is_array($headData)) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Invalid head_data format'
                ], 422);
            }
            
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
        $purchase_vehicle->tare_weight = $request->tare_weight;
        $purchase_vehicle->location = $request->location_id;
        $purchase_vehicle->voucher_no = $request->slip_number;
        $purchase_vehicle->processed_by = $request->user_id;
      if($purchase_vehicle->save()){ foreach ($headData as $key => $value) {
          $rate = SupplierLocationRates::where('head_id',$value['head_id']) 
                                        ->where('company_id',$request->company_id)
                                        ->where('account_id',$request->account_id)
                                        ->where('r_date','<=',date('Y-m-d',strtotime($request->entry_date)))
                                        ->where('location',$request->location_id) ->value('head_rate');
         $report = new SupplierPurchaseReport; 
         $report->purchase_id = $request->id;
         $report->head_id = $value['head_id'];
         $report->head_qty = $value['head_qty'];
         $report->head_contract_rate = $rate;
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
        $company_id = $request->company_id;
        $today = date('Y-m-d');
    
        // 1) Get settings (no change)
        $setting = SaleOrderSetting::join('item_groups','sale-order-settings.item_id','=','item_groups.id')
            ->where('sale-order-settings.company_id',$company_id)
            ->where('setting_type','PURCHASE GROUP')
            ->where('setting_for','PURCHASE ORDER')
            ->where('sale-order-settings.status','1')
            ->select('group_name','group_type','item_id')
            ->get();
    
        // 2) Prepare Base Query (JOIN Only Once)
        $base = SupplierPurchaseVehicleDetail::join('sale-order-settings','sale-order-settings.item_id','=','supplier_purchase_vehicle_details.group_id')
            ->join('item_groups','item_groups.id','=','supplier_purchase_vehicle_details.group_id')
            ->where('supplier_purchase_vehicle_details.company_id', $company_id)
            ->where('sale-order-settings.setting_type','PURCHASE GROUP')
            ->where('sale-order-settings.setting_for','PURCHASE ORDER');
    
        // 3) Net Weights in one query using conditional SUM
        $netWeights = (clone $base)
            ->whereDate('entry_date', $today)
            ->selectRaw("
                SUM(CASE WHEN `sale-order-settings`.group_type = 'WASTE KRAFT' THEN (gross_weight - tare_weight) ELSE 0 END) AS net_wk,
                SUM(CASE WHEN `sale-order-settings`.group_type = 'BOILER FUEL' THEN (gross_weight - tare_weight) ELSE 0 END) AS net_bf
            ")
            ->first();
    
        $Net_weight_wk = (float) $netWeights->net_wk;
        $Net_weight_bf = (float) $netWeights->net_bf;
    
        // 4) Pending counts in one query
        $pending = (clone $base)
            ->selectRaw("
                SUM(CASE WHEN `sale-order-settings`.group_type = 'WASTE KRAFT' AND supplier_purchase_vehicle_details.status = 0 THEN 1 ELSE 0 END) AS pending_wk,
                SUM(CASE WHEN `sale-order-settings`.group_type = 'BOILER FUEL' AND supplier_purchase_vehicle_details.status = 0 THEN 1 ELSE 0 END) AS pending_bf
            ")
            ->first();
    
        // Response (extend if needed)
        //Privileges
        $privileges_arr = [];
        if(isset($request->user_id) && $request->user_id!=""){
            //160-Waste kraft
            $privileges = PrivilegesModuleMapping::where('module_id',160)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'WASTE KRAFT');
            }
            //161-Boiler Fuel
            $privileges = PrivilegesModuleMapping::where('module_id',161)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'BOILER FUEL');
            }
            //162 - Spare parts
            $privileges = PrivilegesModuleMapping::where('module_id',162)
                                                ->where('employee_id',$request->user_id)
                                                ->where('company_id',$request->company_id)
                                                ->first();
            if($privileges){
                array_push($privileges_arr,'SPARE PARTS');
            }
        }
        
        return response()->json([
            'code' => 200,
            'message' => 'Purchase Item Type',
            'data' => [
                'data' => $setting,
                'privileges'=>$privileges_arr,
                'net_weight' => [
                    'WASTEKRAFT' => $Net_weight_wk,
                    'BOILER_FUEL' => $Net_weight_bf,
                ],
                'pending' => [
                    'WASTEKRAFT' => (int) $pending->pending_wk,
                    'BOILER_FUEL' => (int) $pending->pending_bf,
                    'TOTAL' => (int) $pending->pending_total,
                ],
            ]
        ]);
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
    public function getAccountsByGroup(Request $request)
    {
        /* -------------------------------
         | 1. Validate Request
         |--------------------------------*/
        $request->validate([
            'group_id' => 'required|integer'
        ]);

        $group_id = $request->group_id;
        $company_id = $request->company_id;

        $accountIds = [];

        /* -------------------------------
         | 2. Fetch Purchase Group Settings
         |--------------------------------*/
        $items = SaleOrderSetting::where('setting_type', 'PURCHASE GROUP')
                    ->where('item_id', $group_id)
                    ->select('group_type')
                    ->get();

        /* -------------------------------
         | 3. Collect Account IDs
         |--------------------------------*/
        foreach ($items as $item) {

            switch ($item->group_type) {

                case 'BOILER FUEL':
                    $accountIds = array_merge(
                        $accountIds,
                        FuelSupplier::where('status', 1)
                            ->pluck('account_id')
                            ->toArray()
                    );
                    break;

                case 'WASTE KRAFT':
                    $accountIds = array_merge(
                        $accountIds,
                        Supplier::where('status', 1)
                            ->pluck('account_id')
                            ->toArray()
                    );
                    break;

                case 'SPARE PART':
                    $accountIds = array_merge(
                        $accountIds,
                        SparePart::where('status', 2)
                            ->pluck('account_id')
                            ->toArray()
                    );
                    break;
            }
        }

        /* -------------------------------
         | 4. Remove Duplicates
         |--------------------------------*/
        $accountIds = array_unique($accountIds);

        /* -------------------------------
         | 5. Fetch Accounts
         |--------------------------------*/
        $accounts = Accounts::whereIn('id', $accountIds)
                    ->where('company_id', $company_id)
                    ->select('id', 'account_name')
                    ->orderBy('account_name')
                    ->get();

        /* -------------------------------
         | 6. Return JSON Response
         |--------------------------------*/
        return response()->json([
            'status' => true,
            'count'  => $accounts->count(),
            'data'   => $accounts
        ]);
    }
    
    public function approvePurchaseReport(Request $request)
{
    $request->validate([
        'purchase_id' => 'required|exists:supplier_purchase_vehicle_details,id',
        'user_id'     => 'required|exists:users,id'
    ]);

    $purchase = SupplierPurchaseVehicleDetail::find($request->purchase_id);

    $purchase->status = 3;
    $purchase->reapproval = 0;
    $purchase->approved_by = $request->user_id;

    if ($purchase->save()) {
        return response()->json([
            'status'  => 1,
            'message' => 'Purchase Report Approved Successfully.'
        ], 200);
    }

    return response()->json([
        'status'  => 0,
        'message' => 'Failed to approve purchase report.'
    ], 400);
}

public function revertInProcessPurchaseReport(Request $request)
{
    $request->validate([
        'row_id'  => 'required|exists:supplier_purchase_vehicle_details,id',
        'user_id' => 'required|exists:users,id',
        'company_id' => 'required|exists:companies,id'
    ]);

    $company_id = $request->company_id;

    $update = SupplierPurchaseVehicleDetail::where('company_id', $company_id)
                ->where('id', $request->row_id)
                ->update([
                    'status' => 1
                ]);

    if ($update) {
        return response()->json([
            'status'  => 1,
            'message' => 'Purchase report reverted to In Process successfully.'
        ], 200);
    }

    return response()->json([
        'status'  => 0,
        'message' => 'Something went wrong.'
    ], 400);
}


  public function report(Request $request)
{
    $companyId = $request->company_id;
    $from_date = $request->from_date ?? date('Y-m-01');
    $to_date   = $request->to_date ?? date('Y-m-t');
    $view_by   = $request->view_by ?? 'party';
    $accountId = $request->account_id ?? 'all';

    /** ================= GROUP ================= */
    $group_id = SaleOrderSetting::where('company_id', $companyId)
        ->where('setting_type', 'PURCHASE GROUP')
        ->where('setting_for', 'PURCHASE ORDER')
        ->where('group_type', 'WASTE KRAFT')
        ->value('item_id');

    /** ================= PARTY SUMMARY ================= */
    $partySummary = SupplierPurchaseVehicleDetail::leftJoin(
            'purchases',
            'supplier_purchase_vehicle_details.map_purchase_id',
            '=',
            'purchases.id'
        )
        ->where('supplier_purchase_vehicle_details.company_id', $companyId)
        ->where('supplier_purchase_vehicle_details.group_id', $group_id)
        ->when($view_by === 'party', fn ($q) => $q->where('supplier_purchase_vehicle_details.status', 3))
        ->when($accountId !== 'all', fn ($q) => $q->where('account_id', $accountId))
        ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date])
        ->select(
            'account_id',
            DB::raw('SUM(purchases.total) as invoice_amount'),
            DB::raw('SUM(purchases.taxable_amt) as taxable_amount'),
            DB::raw('SUM(difference_total_amount) as difference_amount')
        )
        ->groupBy('account_id')
        ->with('accountInfo:id,account_name')
        ->get();

    /** ================= GST MAP ================= */
    $purchase_gst = DB::table('purchase_sundries')
        ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
        ->whereIn('nature_of_sundry', ['CGST', 'SGST', 'IGST'])
        ->groupBy('purchase_id')
        ->pluck(DB::raw('SUM(amount)'), 'purchase_id');

    /** ================= DETAILED ROWS ================= */
    $details = SupplierPurchaseVehicleDetail::with([
            'purchaseReport',
            'accountInfo:id,account_name',
            'locationInfo:id,name',
            'purchaseInfo:id,voucher_no,total,taxable_amt,date'
        ])
        ->where('company_id', $companyId)
        ->where('group_id', $group_id)
        ->when($view_by === 'party', fn ($q) => $q->where('status', 3))
        ->when($accountId !== 'all', fn ($q) => $q->where('account_id', $accountId))
        ->whereBetween('entry_date', [$from_date, $to_date])
        ->orderBy('entry_date')
        ->get();

    /** ================= PRE-CALCULATE REPORT AMOUNT (ONCE) ================= */
    foreach ($details as $row) {
        $row->total_report_amt = 0;

        foreach ($row->purchaseReport as $rp) {
            $rp->report_amt = round(($rp->head_contract_rate * $rp->head_qty), 2);
            $row->total_report_amt += $rp->report_amt;
        }
    }

    /** ================= DATE WISE + DAILY SUMMARY ================= */
    $dateWise = [];

    $grouped = $details->groupBy('entry_date');

    foreach ($grouped as $date => $rows) {

        $date_inv = $date_gst = $date_tax = $date_act = $date_diff = 0;
        $date_total_netwt  = 0;

        foreach ($rows as $row) {

            $purchase = $row->purchaseInfo ?? new \stdClass();

            $invoice = $purchase->total ?? 0;
            $gst = $purchase_gst[$row->map_purchase_id] ?? 0;
            $taxable = $invoice - $gst;

            $purchase->gst_amt = round($gst, 2);
            $purchase->actual_amt = round($row->total_report_amt, 2);

            $real_net_weight = ($row->gross_weight - $row->tare_weight);

            $date_inv += $invoice;
            $date_gst += $gst;
            $date_tax += $taxable;
            $date_act += $row->total_report_amt;
            $date_diff += ($row->difference_total_amount ?? 0);

            $date_total_netwt += $real_net_weight;
        }

        $dateWise[] = [
            'date' => $date,
            'rows' => $rows,
            'totals' => [
                'invoice_total'    => round($date_inv, 2),
                'gst_total'        => round($date_gst, 2),
                'taxable_total'    => round($date_tax, 2),
                'actual_total'     => round($date_act, 2),
                'difference_total' => round($date_diff, 2),
            ],
            'daily_summary' => [
                'total_report_amount' => round($date_act, 2),
                'total_net_weight'    => round($date_total_netwt, 2),
                'total_average_rate'  => $date_total_netwt > 0
                    ? round($date_act / $date_total_netwt, 2)
                    : 0,
            ]
        ];
    }

    /** ================= HEAD SUMMARY (NO NESTED LOOPS) ================= */
    $headSummary = [];

    foreach ($details as $row) {
        foreach ($row->purchaseReport as $r) {

            if ($r->head_qty >= 0) {

                $key = $r->headInfo->name ?? $r->head_id;

                if (!isset($headSummary[$key])) {
                    $headSummary[$key] = [
                        'qty' => 0,
                        'amount' => 0,
                        'report_amt' => 0
                    ];
                }

                $headSummary[$key]['qty'] += $r->head_qty;
                $headSummary[$key]['amount'] += $r->head_difference_amount;
                $headSummary[$key]['report_amt'] += $r->report_amt;
            }
        }
    }

    /** ================= PARTY DETAIL ATTACH ================= */
    if ($view_by === 'party') {
        $partyWiseDetails = $details->groupBy('account_id');
        $partySummary->each(function($p) use ($partyWiseDetails) {
            $p->detail = $partyWiseDetails[$p->account_id] ?? collect();
        });
    }

    /** ================= FINAL RESPONSE ================= */
    $response = [
        'status' => true,
        'filters' => [
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'view_by'   => $view_by,
            'account'   => $accountId,
        ],
        'head_summary' => array_values($headSummary),
        'meta' => [
            'group_id'   => $group_id,
            'company_id' => $companyId,
        ]
    ];

    if ($view_by === 'party') {
        $response['summary_party'] = $partySummary;
    }

    if ($view_by === 'date') {
        $response['date_wise_details'] = $dateWise;
    }

    return response()->json($response);
}

public function reportsDashboardApi(Request $request)
{   
     $companyId = $request->company_id;
    $from_date  = $request->from_date ?? date('Y-m-d');
    $to_date    = $request->to_date ?? date('Y-m-d');
    $item_group = $request->item_group ?? 'all';

    $baseQuery = SupplierPurchaseVehicleDetail::join(
                        'accounts','supplier_purchase_vehicle_details.account_id','=','accounts.id'
                   )
                   ->join(
                        'item_groups','supplier_purchase_vehicle_details.group_id','=','item_groups.id'
                   )
                   ->join(
                        'sale-order-settings','sale-order-settings.item_id','=','supplier_purchase_vehicle_details.group_id'
                   )
                   ->where('supplier_purchase_vehicle_details.company_id', $companyId)
                   ->where('sale-order-settings.setting_type', 'PURCHASE GROUP')
                   ->where('sale-order-settings.setting_for', 'PURCHASE ORDER')
                   ->whereBetween('supplier_purchase_vehicle_details.entry_date', [$from_date, $to_date]);

    if ($item_group !== 'all') {
        $baseQuery->where('sale-order-settings.group_type', $item_group);
    }

    $records = $baseQuery->select(
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

    // ======= GROUPING =======
    $grouped = $records->groupBy([
        fn ($row) => $row->entry_date,
        fn ($row) => $row->group_type,
        fn ($row) => $row->group_name,
    ]);

    // Prepare final API response
    $response = [];
    $totalGross = 0; $totalTare = 0; $totalNet = 0;

    foreach ($grouped as $date => $typeGroups) {
        foreach ($typeGroups as $groupType => $groupNames) {
            foreach ($groupNames as $groupName => $rows) {

                $items = [];
                foreach ($rows as $row) {

                    $gross = $row->gross_weight ?? 0;
                    $tare  = $row->tare_weight ?? 0;
                    $net = ($tare == 0) ? 0 : ($gross - $tare);

                    $totalGross += $gross;
                    $totalTare  += $tare;
                    $totalNet   += $net;

                    $items[] = [
                        'date' => date('d-m-Y', strtotime($row->entry_date)),
                        'account_name' => $row->account_name,
                        'item_group' => $row->group_name,
                        'vehicle_no' => $row->vehicle_no,
                        'gross_weight' => (float)$gross,
                        'tare_weight' => (float)$tare,
                        'slip_no' => $row->voucher_no,
                        'net_weight' => (float)$net,
                    ];
                }

                $response[] = [
                    'entry_date' => $date,
                    'group_type' => $groupType,
                    'item_group' => $groupName,
                    'items' => $items
                ];
            }
        }
    }

    return response()->json([
        'status' => true,
        'filters' => [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'item_group' => $item_group
        ],
        'summary' => [
            'total_gross' => (float)$totalGross,
            'total_tare'  => (float)$totalTare,
            'total_net'   => (float)$totalNet,
        ],
        'data' => $response
    ]);
}


}
