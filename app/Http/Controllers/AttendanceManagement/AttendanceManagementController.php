<?php

namespace App\Http\Controllers\AttendanceManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class AttendanceManagementController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | WEEKLY OFF - INDEX PAGE
    |--------------------------------------------------------------------------
    */

    public function weeklyOffIndex()
    {
        $company_id = Session::get('user_company_id');

        $settings = DB::table('weekly_off_settings')
            ->where('company_id', $company_id)
            ->orderBy('effective_from', 'desc')
            ->get();

        return view('AttendanceManagement.weeklyOffSetting', compact('settings'));
    }


    /*
    |--------------------------------------------------------------------------
    | WEEKLY OFF - STORE DATA
    |--------------------------------------------------------------------------
    */

    public function weeklyOffStore(Request $request)
    {
        $request->validate([
            'off_days' => 'required|array',
            'effective_from' => 'required|date'
        ]);

        $company_id = Session::get('user_company_id');
        $user_id    = Session::get('user_id');

        // Prevent duplicate effective date for same company
        $exists = DB::table('weekly_off_settings')
            ->where('company_id', $company_id)
            ->where('effective_from', $request->effective_from)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors('Weekly off already set for this date.');
        }

        DB::table('weekly_off_settings')->insert([
            'company_id'     => $company_id,
            'off_days'       => json_encode($request->off_days),
            'effective_from' => $request->effective_from,
            'created_by'     => $user_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('attendance.weeklyoff.index')
            ->with('success', 'Weekly Off Setting Saved Successfully');
    }
public function monthlyOffCalendar(Request $request)
{
    $company_id = Session::get('user_company_id');

    $month = $request->month ?? date('m');
    $year  = $request->year ?? date('Y');

    $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $firstDayOfMonth = date('Y-m-01', strtotime($year.'-'.$month.'-01'));
    $startDayOfWeek = date('w', strtotime($firstDayOfMonth));

    $dates = [];
    $weeklyOffDates = [];
    $holidayDates = [];

    // Get holidays of month
    $holidayDates = DB::table('holidays')
        ->where('company_id', $company_id)
        ->whereMonth('holiday_date', $month)
        ->whereYear('holiday_date', $year)
        ->pluck('holiday_date')
        ->toArray();

    for ($day = 1; $day <= $totalDays; $day++) {

        $currentDate = date('Y-m-d', strtotime($year.'-'.$month.'-'.$day));
        $dayName = date('l', strtotime($currentDate));

        $dates[] = $currentDate;

        // Weekly Off Logic
        $weeklyOff = DB::table('weekly_off_settings')
            ->where('company_id', $company_id)
            ->where('effective_from', '<=', $currentDate)
            ->orderBy('effective_from', 'desc')
            ->first();

        if ($weeklyOff) {
            $offDaysArray = json_decode($weeklyOff->off_days, true);

            if (in_array($dayName, $offDaysArray)) {
                $weeklyOffDates[] = $currentDate;
            }
        }
    }

    return view('AttendanceManagement.monthlyOffCalendar', compact(
        'month',
        'year',
        'dates',
        'weeklyOffDates',
        'holidayDates',
        'startDayOfWeek'
    ));
}

public function saveMonthlyHoliday(Request $request)
{
    $company_id = Session::get('user_company_id');

    $month = $request->month;
    $year  = $request->year;

    // Delete existing holidays of that month
    DB::table('holidays')
        ->where('company_id', $company_id)
        ->whereMonth('holiday_date', $month)
        ->whereYear('holiday_date', $year)
        ->delete();

    if ($request->holiday_dates) {

        foreach ($request->holiday_dates as $date) {

            DB::table('holidays')->insert([
                'company_id' => $company_id,
                'holiday_date' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    return redirect()->back()->with('success','Holidays Updated Successfully');
}
}