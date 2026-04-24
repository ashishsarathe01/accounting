<?php

namespace App\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Session;
use App\Models\User;
use App\Models\Accounts;
use App\Models\BillSundrys;
use App\Models\ManageItems;
use App\Models\State;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::where('status', 1)
                            ->where('company_id',Session::get('user_company_id'))
            ->orderBy('action_at', 'desc')
            ->get();
        $states = \DB::table('states')->pluck('name', 'id');
        $itemsMap = ManageItems::pluck('name', 'id'); 
        $sundryMap = BillSundrys::pluck('name', 'id');
        $accountMap = Accounts::pluck('account_name', 'id');

        return view('activity_logs.index', compact(
            'logs', 'states', 'itemsMap', 'sundryMap', 'accountMap'
        ));
    }

    public function approve($id)
    {
        $log = ActivityLog::where('id', $id)
            ->where('status', 1)
            ->firstOrFail();
        $log->status = 2;
        $log->save();

        return response()->json([
            'status' => true,
            'message' => 'Approved successfully'
        ]);
    }
}
