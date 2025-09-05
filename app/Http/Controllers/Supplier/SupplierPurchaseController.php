<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use DB;
use App\Models\Purchase;
use App\Models\SupplierLocation;
use App\Models\SupplierLocationRates;
use App\Models\SupplierPurchaseReport;
use App\Models\Accounts;
use App\Models\SupplierSubHead;

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
        $rate = SupplierLocationRates::select('head_id','head_rate')
                                        ->where('company_id',Session::get('user_company_id'))
                                        ->where('account_id',$request->account_id)
                                        ->where('location',$request->location)
                                        ->get();
        return response()->json($rate);
    }
    public function storeSupplierPurchaseReport(Request $request)
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
                        ->update(['supplier_action_status' => 1,'supplier_difference_total_amount',$request->difference_total_amount]);
                
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
        $purchases = Purchase::where('purchases.company_id', Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('purchases.status','1')
                                ->where('supplier_action_status','3')
                                ->where('purchases.party', $id)
                                ->whereDate('purchases.date', '>=', date('Y-m-d', strtotime($from_date)))
                                ->whereDate('purchases.date', '<=', date('Y-m-d', strtotime($to_date)))
                                ->select(
                                    DB::raw('SUM(purchases.total) as total_sum'),
                                    DB::raw('SUM(supplier_difference_total_amount) as difference_sum')
                                )
                                ->first();
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
        return view('supplier.supplier_purchase_report', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id,"from_date"=>$from_date,"to_date"=>$to_date]);
    }
    public function viewCompletePurchaseInfo($id=null)
    {
        $report = SupplierPurchaseReport::join('supplier_locations','supplier_purchase_reports.location','=','supplier_locations.id')
                                        ->select('difference_total_amount','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount','voucher_no','supplier_locations.name as location_name','location')
        ->where('purchase_id',$id)->get();
        $purchase = Purchase::select('image_1','image_2','image_3')->find($id);
        $response = array(
            'reports' => $report,
            'purchase'=>$purchase
        );
        return json_encode($response);
    }
    public function getLocationBySupplier(Request $request){
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
        return response()->json([
            'location' => $location
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
                        ->update(['supplier_action_status' => 1,'supplier_difference_total_amount',$request->difference_total_amount]);
                
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
        $request->validate([
            'images.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key=>$image) {
                 // Save file
                $filename = time().'_'.$image->getClientOriginalName();
                $path = 'purchase_images/'.$filename;
                $image->move(public_path('purchase_images'), $filename);
                // Save path in DB 
                $purchase = Purchase::find($request->image_purchase_id);
                if($key==0){
                    $purchase->image_1 = $path;
                }else if($key==1){
                    $purchase->image_2 = $path;
                }else if($key==2){
                    $purchase->image_3 = $path;
                }
                $purchase->supplier_action_status = 2;
                $purchase->save();
                $paths[] = $path;
            }
        }
        return redirect()->route('complete-supplier-purchase')->with('success','Image Uploaded Successfully');
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
        $purchase = Purchase::find($request->purchase_id);
        $purchase->supplier_action_status = 3;
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
        $purchases = Purchase::with(['purchaseReport'=>function($q){
                                    $q->select('purchase_id','voucher_no','location','head_id','head_qty','head_bill_rate','head_contract_rate','head_difference_amount');
                                    $q->with(['locationInfo'=>function($q1){
                                        $q1->select('id','name');
                                    },'headInfo'=>function($q2){
                                        $q2->select('id','name');
                                        $q2->orderBy('sequence');
                                    }]);
                                }])
                                ->select('purchases.id','purchases.voucher_no as invoice_no','purchases.total','supplier_difference_total_amount','date')
                                ->where('purchases.company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('purchases.status','1')                                
                                ->where('supplier_action_status','3')
                                ->where('purchases.party', $id)
                                ->whereBetween('purchases.date', [$from_date, $to_date])
                                ->orderBy('date','asc')
                                ->get();
        // echo "<pre>";
        // print_r($purchases->toArray());
        // echo "</pre>";
        return view('supplier/supplier_purchase_report_detail',['account_name'=>$account->account_name,'from_date'=>$from_date,'to_date'=>$to_date,"purchases"=>$purchases]);
    }
    public function performActionOnPurchase(Request $request)
    {
        $action_data = json_decode($request->action_data,true);
        $amount = array_sum(array_column($action_data, 'amount')); 
        $ids = array_column($action_data, 'id');
    }
    //
}
