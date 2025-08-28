<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use session;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use App\Models\Purchase;
use App\Models\SupplierLocation;
use App\Models\SupplierLocationRates;
use App\Models\SupplierPurchaseReport;
use App\Models\Accounts;

class SupplierPurchaseController extends Controller
{
    public function manageSupplierPurchase()
    {
        $purchases = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name',
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
        ])->where('company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('status','1')
                                ->where('supplier_action_status','0')
                                ->orderBy('date','asc')
                                ->get();
        $location = SupplierLocation::where('company_id',Session::get('user_company_id'))
                                        ->where('status',1)
                                        ->get();
        return view('supplier.manage_supplier_purchase', ["purchases" => $purchases,"locations"=>$location]);
    }
    public function getSupplierRateByLocation(Request $request)
    {
        $rate = SupplierLocationRates::where('company_id',Session::get('user_company_id'))
                                        ->where('account_id',$request->account_id)
                                        ->where('location',$request->location)
                                        ->first();
        return response()->json($rate);
    }
    public function storeSupplierPurchaseReport(Request $request)
    {
        SupplierPurchaseReport::where('company_id',Session::get('user_company_id'))
                                ->where('purchase_id',$request->purchase_id)
                                ->delete();
        $report = new SupplierPurchaseReport;
        $report->purchase_id = $request->purchase_id;
        $report->voucher_no = $request->voucher_no;
        $report->location = $request->location;

        $report->kraft_i_qty = $request->kraft_i_qty;
        $report->kraft_i_bill_rate = $request->kraft_i_bill_rate;
        $report->kraft_i_contract_rate = $request->kraft_i_contract_rate;
        $report->kraft_i_difference_amount = $request->kraft_i_difference_amount;

        $report->kraft_ii_qty = $request->kraft_ii_qty;
        $report->kraft_ii_bill_rate = $request->kraft_ii_bill_rate;
        $report->kraft_ii_contract_rate = $request->kraft_ii_contract_rate;
        $report->kraft_ii_difference_amount = $request->kraft_ii_difference_amount;

        $report->duplex_qty = $request->duplex_qty;
        $report->duplex_bill_rate = $request->duplex_bill_rate;
        $report->duplex_contract_rate = $request->duplex_contract_rate;
        $report->duplex_difference_amount = $request->duplex_difference_amount;

        $report->poor_qty = $request->poor_qty;
        $report->poor_bill_rate = $request->poor_bill_rate;
        $report->poor_contract_rate = $request->poor_contract_rate;
        $report->poor_difference_amount = $request->poor_difference_amount;

        $report->cut_qty = $request->cut_qty ? $request->cut_qty : 0;
        $report->cut_bill_rate = $request->cut_bill_rate ? $request->cut_bill_rate : 0;
        $report->cut_contract_rate = $request->cut_contract_rate ? $request->cut_contract_rate : 0;
        $report->cut_difference_amount = $request->cut_difference_amount ? $request->cut_difference_amount : 0;

        $report->other_qty = $request->other_qty ? $request->other_qty : 0;
        $report->other_bill_rate = $request->other_bill_rate ? $request->other_bill_rate : 0;
        $report->other_contract_rate = $request->other_contract_rate ? $request->other_contract_rate : 0;
        $report->other_difference_amount = $request->other_difference_amount ? $request->other_difference_amount : 0;

        $report->other_check = $request->other_check;
        $report->difference_total_amount = $request->difference_total_amount;

        $report->company_id = Session::get('user_company_id');
        $report->status = 1;
        $report->created_by = Session::get('user_id');
        $report->created_at = Carbon::now();
        if($report->save()){
            Purchase::where('company_id',Session::get('user_company_id'))
                    ->where('id',$request->purchase_id)
                    ->update(['supplier_action_status' => 1]);
            $response = array(
                'status' => true,
                'message' => 'Supplier Purchase Report Added Successfully.'
            );
            return json_encode($response);
        }else{
            return redirect()->back()->with('error','Something Went Wrong');
            $response = array(
                'status' => false,
                'message' => 'Something went wrong.'
            );
            return json_encode($response);
        }
    }
    public function completeSupplierPurchase($id=null)
    {
        $purchases = Purchase::with([
            'purchaseDescription' => function ($query) {
                $query->with([
                    'item:id,name',
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
                                ->where('supplier_action_status','1')
                                ->when($id!=null, function ($q) use ($id) {
                                    // ✅ Example: add condition only if dates are passed
                                    $q->where('purchases.party', $id);
                                })
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
        return view('supplier.complete_supplier_purchase', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id]);
    }
    public function manageSupplierPurchaseReport($id=null, $from_date=null, $to_date=null)
    {
        $purchases = Purchase::join('supplier_purchase_reports','purchases.id','=','supplier_purchase_reports.purchase_id')
        ->select('purchases.*','supplier_purchase_reports.difference_total_amount')
        ->where('purchases.company_id',Session::get('user_company_id'))
                                ->where('delete','0')
                                ->where('purchases.status','1')
                                ->where('supplier_action_status','1')
                                ->where('purchases.party', $id)
                                ->whereDate('purchases.date', '>=', date('Y-m-d', strtotime($from_date)))
                                ->whereDate('purchases.date', '<=', date('Y-m-d', strtotime($to_date)))
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
        return view('supplier.supplier_purchase_report', ["purchases" => $purchases,"accounts"=>$accounts,"id"=>$id,"from_date"=>$from_date,"to_date"=>$to_date]);
    }
    public function viewCompletePurchaseInfo($id=null)
    {
        $report = SupplierPurchaseReport::join('supplier_locations','supplier_purchase_reports.location','=','supplier_locations.id')
                                        ->select('supplier_purchase_reports.*','supplier_locations.name as location_name')
        ->where('purchase_id',$id)->first();
        $response = array(
            'reports' => $report
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
    
}
