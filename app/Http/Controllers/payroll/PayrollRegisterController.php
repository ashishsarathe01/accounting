<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\User;

class PayrollRegisterController extends Controller
{

    public function index()
    {
        return view('payroll.payroll_register', [
            'employees'        => session('employees', collect()),
            'grossAddHeads'    => session('grossAddHeads', collect()),
            'grossSubHeads'    => session('grossSubHeads', collect()),
            'netAddHeads'      => session('netAddHeads', collect()),
            'netSubHeads'      => session('netSubHeads', collect()),
            'salaryStructures' => session('salaryStructures', collect()),
            'totalDays'        => session('totalDays'),
            'month'            => session('month'),
            'year'             => session('year'),
            'existingSlips' => session('existingSlips', collect()),
'existingSlipDetails' => session('existingSlipDetails', collect()),
        ]);
    }


public function generate(Request $request)
{
    $request->validate([
        'month' => 'required',
        'year'  => 'required'
    ]);

    $month = $request->month;
    $year  = $request->year;

    $totalDays = Carbon::create($year, $month, 1)->daysInMonth;

    $company_id = Session::get('user_company_id');

    $employees = User::where('type','EMPLOYEE')
        ->where('company_id',$company_id)
        ->where('delete_status','0')
        ->get();

    // 🔍 CHECK IF PAYROLL ALREADY EXISTS
    $existingRun = DB::table('payroll_runs')
    ->where('company_id', $company_id)
    ->where('month', $month)
    ->where('year', $year)
    ->first();


// ✅ ALWAYS LOAD BASE SALARY STRUCTURE
$salaryStructures = DB::table('user_salary_structures')
    ->where('company_id', $company_id)
    ->get()
    ->groupBy('user_id');


// ✅ LOAD EXISTING PAYROLL DATA SEPARATELY (DO NOT REPLACE BASE)
$existingSlips = collect();
$existingSlipDetails = collect();

if($existingRun){

    // Load slips (keyed by user_id for easy access in blade)
    $existingSlips = DB::table('payroll_slips')
        ->where('payroll_run_id', $existingRun->id)
        ->get()
        ->keyBy('user_id');

    // Load slip head details grouped by slip_id
    $existingSlipDetails = DB::table('payroll_slip_details')
        ->whereIn('payroll_slip_id', $existingSlips->pluck('id'))
        ->get()
        ->groupBy('payroll_slip_id');
}

    // HEADS (same as before)
    $grossAddHeads = DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('affect_gross_salary', 1)
        ->where(function($q){
            $q->where('adjustment_type', 'addictive')
              ->orWhereNull('adjustment_type');
        })
        ->orderBy('id')
        ->get();

    $grossSubHeads = DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('affect_gross_salary', 1)
        ->where('adjustment_type', 'subtractive')
        ->orderBy('id')
        ->get();

    $netAddHeads = DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('affect_net_salary', 1)
        ->where('adjustment_type', 'addictive')
        ->orderBy('id')
        ->get();

    $netSubHeads = DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('affect_net_salary', 1)
        ->where('adjustment_type', 'subtractive')
        ->orderBy('id')
        ->get();

    return redirect()->route('payroll.register')->with([
        'employees'        => $employees,
        'month'            => $month,
        'year'             => $year,
        'totalDays'        => $totalDays,
        'grossAddHeads'    => $grossAddHeads,
        'grossSubHeads'    => $grossSubHeads,
        'netAddHeads'      => $netAddHeads,
        'netSubHeads'      => $netSubHeads,
        'salaryStructures' => $salaryStructures,
        'existingSlips'        => $existingSlips,
    'existingSlipDetails'  => $existingSlipDetails
    ]);
}

    public function store(Request $request)
{
    $company_id = Session::get('user_company_id');

    $month     = $request->month;
    $year      = $request->year;
    $totalDays = $request->total_days;

    $existingRun = DB::table('payroll_runs')
        ->where('company_id', $company_id)
        ->where('month', $month)
        ->where('year', $year)
        ->first();

    DB::transaction(function() use ($request, $company_id, $month, $year, $totalDays, $existingRun) {

        // ==============================
        // 1️⃣ CREATE OR USE EXISTING RUN
        // ==============================
        if($existingRun){
            $runId = $existingRun->id;
        } else {
            $runId = DB::table('payroll_runs')->insertGetId([
                'company_id' => $company_id,
                'month' => $month,
                'year' => $year,
                'total_days' => $totalDays,
                'status' => 'finalized',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==============================
        // 2️⃣ LOOP EMPLOYEES
        // ==============================
        foreach($request->employees as $userId => $data){

            $absent  = $data['absent'] ?? 0;
            $present = $totalDays - $absent;
            $gross   = $data['gross'] ?? 0;
            $net     = $data['net'] ?? 0;

            // 🔍 Check if slip already exists
            $existingSlip = DB::table('payroll_slips')
                ->where('payroll_run_id', $runId)
                ->where('user_id', $userId)
                ->first();

            if($existingSlip){

                // ✅ UPDATE SLIP
                DB::table('payroll_slips')
                    ->where('id', $existingSlip->id)
                    ->update([
                        'absent_days' => $absent,
                        'present_days' => $present,
                        'gross_salary' => $gross,
                        'net_salary' => $net,
                        'updated_at' => now(),
                    ]);

                $slipId = $existingSlip->id;

                // Delete old head details
                DB::table('payroll_slip_details')
                    ->where('payroll_slip_id', $slipId)
                    ->delete();

            } else {

                // ✅ CREATE SLIP
                $slipId = DB::table('payroll_slips')->insertGetId([
                    'payroll_run_id' => $runId,
                    'user_id' => $userId,
                    'absent_days' => $absent,
                    'present_days' => $present,
                    'gross_salary' => $gross,
                    'net_salary' => $net,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==============================
            // 3️⃣ INSERT HEAD DETAILS
            // ==============================
            if(isset($data['heads'])){

                foreach($data['heads'] as $headId => $amount){

                    $head = DB::table('payroll_heads')
                        ->where('id', $headId)
                        ->where('company_id', $company_id)
                        ->first();

                    if($head){
                        DB::table('payroll_slip_details')->insert([
                            'payroll_slip_id' => $slipId,
                            'payroll_head_id' => $head->id,
                            'head_name' => $head->name,
                            'affect_gross_salary' => $head->affect_gross_salary,
                            'affect_net_salary' => $head->affect_net_salary,
                            'adjustment_type' => $head->adjustment_type,
                            'calculation_type' => $head->calculation_type,
                            'percentage' => $head->percentage,
                            'amount' => $amount,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

        }

    });

    return redirect()->route('payroll.register')
        ->with('success','Payroll saved/updated successfully.');
}

}