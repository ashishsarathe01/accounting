<?php

namespace App\Http\Controllers\DutiesCompliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Session;
use DB;
use Carbon\Carbon;
use App\Models\Companies;
use App\GstReturnCompliance;

class GstReturnComplianceController extends Controller
{
public function index()
{
    $compliances = DB::table('gst_return_compliances')
        ->where(
            'company_id',
            Session::get('user_company_id')
        )
        ->orderBy('month_year', 'desc')
        ->orderBy('return_type')
        ->get();

    return view(
        'DutiesCompliance.index',
        compact('compliances')
    );
}
    public function create()
    {
        $company = Companies::select('gst_config_type')
            ->where('id', Session::get('user_company_id'))
            ->first();

        if ($company->gst_config_type == "single_gst") {

            $gst = DB::table('gst_settings')
                ->select('gst_no')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_type'   => 'single_gst',
                    'delete'     => '0',
                    'status'     => '1'
                ])
                ->get();

        } elseif ($company->gst_config_type == "multiple_gst") {

            $gst = DB::table('gst_settings_multiple')
                ->select('gst_no')
                ->where([
                    'company_id' => Session::get('user_company_id'),
                    'gst_type'   => 'multiple_gst',
                    'delete'     => '0',
                    'status'     => '1'
                ])
                ->get();

        } else {

            $gst = collect();

        }
        $financial_year = Session::get('default_fy');

        [$startYY, $endYY] = explode('-', $financial_year);

        $fy_start_month = '20' . $startYY . '-04';
        $fy_end_month   = '20' . $endYY . '-03';
        return view(
            'DutiesCompliance.add',
            compact('gst', 'fy_start_month', 'fy_end_month')
        );
    }

public function store(Request $request)
{
    $request->validate([
        'gst_number'  => 'required',
        'month_year'  => 'required',
        'return_type' => 'required',
        'arn_number' => 'required|size:15',
        'is_locked'   => 'required'
    ]);

    $financial_year = Session::get('default_fy');

    [$startYY, $endYY] = explode('-', $financial_year);

    $fy_start_month = '20' . $startYY . '-04';
    $fy_end_month   = '20' . $endYY . '-03';

    if (
        $request->month_year < $fy_start_month ||
        $request->month_year > $fy_end_month
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Selected month does not belong to the current financial year.'
            );
    }

    $exists = DB::table('gst_return_compliances')
        ->where('company_id', Session::get('user_company_id'))
        ->where('gst_number', $request->gst_number)
        ->where('month_year', $request->month_year)
        ->where('return_type', $request->return_type)
        ->exists();

    if ($exists) {
        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Compliance already exists for selected GST Number, Month and Return Type.'
            );
    }
    $arnExists = DB::table('gst_return_compliances')
        ->where(
            'arn_number',
            strtoupper(trim($request->arn_number))
        )
        ->exists();

    if ($arnExists) {

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'This ARN Number already exists.'
            );
    }
    DB::table('gst_return_compliances')->insert([

        'company_id'  => Session::get('user_company_id'),

        'gst_number'  => $request->gst_number,

        'month_year'  => $request->month_year,

        'return_type' => $request->return_type,

        'arn_number'  => strtoupper(trim($request->arn_number)),

        'is_locked'   => $request->is_locked,

        'created_by'  => Session::get('user_id'),

        'created_at'  => now(),

        'updated_at'  => now(),

    ]);

    return redirect()
        ->route('gst-return-compliance.index')
        ->with(
            'success',
            'GST Return Compliance saved successfully.'
        );
}
public function checkArn(Request $request)
{
    $arnNumber = strtoupper(trim($request->arn_number));

    $query = DB::table('gst_return_compliances')
        ->where('arn_number', $arnNumber);

    if (!empty($request->id)) {

        $query->where('id', '!=', $request->id);

    }

    $exists = $query->exists();

    if ($exists) {

        return response()->json([
            'status'  => false,
            'message' => 'ARN Number already exists.'
        ]);
    }
}
public function edit($id)
{
    $compliance = DB::table('gst_return_compliances')
        ->where('company_id', Session::get('user_company_id'))
        ->where('id', $id)
        ->first();

    if (!$compliance) {
        return redirect()
            ->route('gst-return-compliance.index')
            ->with('error', 'Record not found.');
    }

    $company = Companies::select('gst_config_type')
        ->where('id', Session::get('user_company_id'))
        ->first();

    if ($company->gst_config_type == "single_gst") {

        $gst = DB::table('gst_settings')
            ->select('gst_no')
            ->where([
                'company_id' => Session::get('user_company_id'),
                'gst_type'   => 'single_gst',
                'delete'     => '0',
                'status'     => '1'
            ])
            ->get();

    } else {

        $gst = DB::table('gst_settings_multiple')
            ->select('gst_no')
            ->where([
                'company_id' => Session::get('user_company_id'),
                'gst_type'   => 'multiple_gst',
                'delete'     => '0',
                'status'     => '1'
            ])
            ->get();
    }

    $financial_year = Session::get('default_fy');

    [$startYY, $endYY] = explode('-', $financial_year);

    $fy_start_month = '20' . $startYY . '-04';
    $fy_end_month   = '20' . $endYY . '-03';

    return view(
        'DutiesCompliance.edit',
        compact(
            'compliance',
            'gst',
            'fy_start_month',
            'fy_end_month'
        )
    );
}
public function update(Request $request, $id)
{
    $request->validate([
        'gst_number'  => 'required',
        'month_year'  => 'required',
        'return_type' => 'required',
        'arn_number'  => 'required|size:15',
        'is_locked'   => 'required'
    ]);

    $compliance = DB::table('gst_return_compliances')
        ->where('company_id', Session::get('user_company_id'))
        ->where('id', $id)
        ->first();

    if (!$compliance) {

        return redirect()
            ->route('gst-return-compliance.index')
            ->with('error', 'Record not found.');
    }

    $financial_year = Session::get('default_fy');

    [$startYY, $endYY] = explode('-', $financial_year);

    $fy_start_month = '20' . $startYY . '-04';
    $fy_end_month   = '20' . $endYY . '-03';

    if (
        $request->month_year < $fy_start_month ||
        $request->month_year > $fy_end_month
    ) {
        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Selected month does not belong to the current financial year.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Same GST + Month + Return Type already exists?
    |--------------------------------------------------------------------------
    */

    $exists = DB::table('gst_return_compliances')
        ->where('company_id', Session::get('user_company_id'))
        ->where('gst_number', $request->gst_number)
        ->where('month_year', $request->month_year)
        ->where('return_type', $request->return_type)
        ->where('id', '!=', $id)
        ->exists();

    if ($exists) {

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'Compliance already exists for selected GST Number, Month and Return Type.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ARN already exists?
    |--------------------------------------------------------------------------
    */

    $arnExists = DB::table('gst_return_compliances')
        ->where(
            'arn_number',
            strtoupper(trim($request->arn_number))
        )
        ->where('id', '!=', $id)
        ->exists();

    if ($arnExists) {

        return redirect()
            ->back()
            ->withInput()
            ->with(
                'error',
                'This ARN Number already exists.'
            );
    }

    DB::table('gst_return_compliances')
        ->where('id', $id)
        ->update([

            'gst_number'  => $request->gst_number,

            'month_year'  => $request->month_year,

            'return_type' => $request->return_type,

            'arn_number'  => strtoupper(trim($request->arn_number)),

            'is_locked'   => $request->is_locked,

            'updated_at'  => now(),

        ]);

    return redirect()
        ->route('gst-return-compliance.index')
        ->with(
            'success',
            'GST Return Compliance updated successfully.'
        );
}
}