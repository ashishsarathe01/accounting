<?php

namespace App\Http\Controllers\BusinessActivityLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessActivityLog;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\SupplierSubHead;
use App\Models\SupplierLocation;
use App\Models\SupplierPurchaseVehicleDetail;
use DB;
use Session;
class BusinessActivityLogController extends Controller
{
    public function index()
    {
        $companyId = session('user_company_id');

        $logs = BusinessActivityLog::where('company_id', $companyId)
            ->where('status', 1)
            ->orderBy('action_at', 'desc')
            ->get();

        $accountMap = Accounts::pluck('account_name', 'id');
        $itemMap     = ManageItems::pluck('name', 'id');
        $headMap     = SupplierSubHead::pluck('name', 'id');
        $locationMap = SupplierLocation::pluck('name', 'id');
        $groupMap = \DB::table('item_groups')->pluck('group_name', 'id');
        $purchaseContext = SupplierPurchaseVehicleDetail::whereIn(
                    'id',
                    $logs->where('module_type', 'purchase_report')->pluck('module_id')
                )
                ->get()
                ->keyBy('id')
                ->map(function ($row) {
                    return [
                        'group_id'   => $row->group_id,
                        'voucher_no'=> $row->voucher_no,
                        'account_id'=> $row->account_id,
                    ];
                });
        return view('BusinessActivityLogs.index', compact(
            'logs',
            'accountMap',
            'itemMap',
            'headMap',
            'locationMap',
            'groupMap',
            'purchaseContext'
        ));

    }

    public function approve($id)
    {
        $companyId = session('user_company_id');

        $log = BusinessActivityLog::where('id', $id)
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->firstOrFail();

        $log->status = 2; 
        $log->save();

        return response()->json([
            'status' => true
        ]);
    }
}
