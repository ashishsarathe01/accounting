<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Accounts;
use App\Helpers\CommonHelper;


class PayrollHeadController extends Controller
{

    public function index()
    {
        $company_id = Session::get('user_company_id');

        $payroll_heads = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->orderByRaw("FIELD(type, 'basic', 'da', 'other')")
            ->get();

        $headNames = DB::table('payroll_heads')
        ->pluck('name','id');
    // echo "<pre>";
    // print_r($payroll_heads);print_r($headNames);die;
    return view('payroll.index', compact('payroll_heads','headNames'));
    }
    /**
     * Show create form
     */
    public function create()
    {
        $company_id = Session::get('user_company_id');

        $top_groups = [13, 15];

        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;

            $childGroups = CommonHelper::getAllChildGroupIds($group_id, $company_id);
            $all_groups = array_merge($all_groups, $childGroups);
        }

        $group_ids = array_unique($all_groups);

        $account_list = Accounts::whereIn('company_id', [$company_id, 0])
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('under_group', $group_ids)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();
        $existing_heads = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->get();
        return view('payroll.create', compact('account_list','existing_heads'));
    }

    /**
     * Store payroll head
     */
    public function store(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:basic,da,other,esic',
            'income_type' => 'nullable|in:fixed,variable',
            'affect_gross_salary' => 'nullable|in:0,1',
            'affect_net_salary' => 'nullable|in:0,1',
            'adjustment_type' => 'nullable|in:addictive,subtractive',
            'calculation_type' => 'required|in:user_defined,percentage,custom_formula',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'formula_heads' => 'nullable|array',
            'formula_heads.*' => 'integer'
        ]);

        if (in_array($request->type, ['basic','da'])) {

            $exists = DB::table('payroll_heads')
                ->where('company_id', $company_id)
                ->where('type', $request->type)
                ->exists();

            if ($exists) {
                return back()->with(
                    'error',
                    ucfirst($request->type) . ' already created for this company.'
                );
            }
        }

        if ($request->type == 'other' && !$request->adjustment_type) {
            return back()->with('error', 'Adjustment type required for Other.');
        }

        $nameExists = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->where('name', $request->name)
            ->exists();

        if ($nameExists) {
            return back()->with('error', 'Payroll head name already exists.');
        }


        $income_type = $request->income_type ?? 'fixed';
        $affect_gross_salary = $request->affect_gross_salary ?? 1;

        $affect_net_salary = null;

        if ($affect_gross_salary == 0) {
            $affect_net_salary = $request->affect_net_salary ?? 1;
        }
        $slip_name = $request->slip_name ?: $request->name;

        $use_for_gratuity = null;
        $calculation_type = $request->calculation_type;
        $percentage = null;
        $adjustment_type = null;
        $formula_heads = null;
        if ($request->type == 'basic') {

            if ($calculation_type == 'percentage') {
                return back()->with('error', 'Basic cannot be percentage based.');
            }

            $calculation_type = 'user_defined';
            $percentage = null;
            $use_for_gratuity = $request->use_for_gratuity ?? 1;
        }

        elseif ($request->type == 'da') {

            $use_for_gratuity = $request->use_for_gratuity ?? 1;

            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for DA.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Create Basic first before defining DA percentage.');
                }

                $percentage = $request->percentage;
            }
        }
        elseif ($request->type == 'esic') {

            $adjustment_type = 'subtractive';

            $use_for_gratuity = null;



            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for ESIC.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Create Basic first before defining ESIC.');
                }

                $percentage = $request->percentage;
            }

            elseif ($calculation_type == 'custom_formula') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for ESIC formula.');
                }

                if (!$request->formula_heads || count($request->formula_heads) == 0) {
                    return back()->with('error', 'Select at least one head for ESIC formula.');
                }

                $percentage = $request->percentage;
                $formula_heads = json_encode($request->formula_heads);
            }
        }
        elseif ($request->type == 'other') {

            $use_for_gratuity = null;

            if (!$request->adjustment_type) {
                return back()->with('error', 'Adjustment type required for Other.');
            }

            $adjustment_type = $request->adjustment_type;

            

            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Create Basic first before defining percentage.');
                }

                $percentage = $request->percentage;
            }

            elseif ($calculation_type == 'custom_formula') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for custom formula.');
                }

                if (!$request->formula_heads || count($request->formula_heads) == 0) {
                    return back()->with('error', 'Select at least one head for formula.');
                }

                $percentage = $request->percentage;
                $formula_heads = json_encode($request->formula_heads);
            }
        }

        DB::table('payroll_heads')->insert([
            'company_id' => $company_id,
            'name' => $request->name,
            'type' => $request->type,
            'adjustment_type' => $adjustment_type,
            'income_type' => $income_type,
            'linked_account_id' => $request->linked_account_id,
            'affect_gross_salary' => $affect_gross_salary,
            'affect_net_salary' => $affect_net_salary,
            'slip_name' => $slip_name,
            'use_for_gratuity' => $use_for_gratuity,
            'calculation_type' => $calculation_type,
            'percentage' => $percentage,
            'formula_heads' => $formula_heads,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll head created successfully.');
    }

    public function edit($id)
    {
        $company_id = Session::get('user_company_id');

        $payroll_head = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->where('id', $id)
            ->first();

        if (!$payroll_head) {
            return back()->with('error', 'Payroll head not found.');
        }

        $top_groups = [13, 15];
        $all_groups = [];

        foreach ($top_groups as $group_id) {
            $all_groups[] = $group_id;
            $childGroups = CommonHelper::getAllChildGroupIds($group_id, $company_id);
            $all_groups = array_merge($all_groups, $childGroups);
        }

        $group_ids = array_unique($all_groups);

        $account_list = Accounts::whereIn('company_id', [$company_id, 0])
            ->where('delete', '0')
            ->where('status', '1')
            ->whereIn('under_group', $group_ids)
            ->select('id', 'account_name')
            ->orderBy('account_name')
            ->get();
        // Decode formula heads if exists
        if ($payroll_head->formula_heads) {
            $payroll_head->formula_heads = json_decode($payroll_head->formula_heads, true);
        } else {
            $payroll_head->formula_heads = [];
        }

        $existing_heads = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->get();
       return view('payroll.edit', compact('payroll_head', 'account_list', 'existing_heads'));
    }

    public function update(Request $request, $id)
    {
        $company_id = Session::get('user_company_id');

        $payroll_head = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->where('id', $id)
            ->first();

        if (!$payroll_head) {
            return back()->with('error', 'Payroll head not found.');
        }

        $request->validate([
        'name' => 'required|string|max:255',
        'income_type' => 'nullable|in:fixed,variable',
        'affect_gross_salary' => 'nullable|in:0,1',
        'affect_net_salary' => 'nullable|in:0,1',
        'adjustment_type' => 'nullable|in:addictive,subtractive',
        'calculation_type' => 'required|in:user_defined,percentage,custom_formula',
        'formula_heads' => 'nullable|array',
        'formula_heads.*' => 'integer',
            'percentage' => 'nullable|numeric|min:0|max:100'
        ]);
    $nameExists = DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('name', $request->name)
        ->where('id', '!=', $id)
        ->exists();

    if ($nameExists) {
        return back()->with('error', 'Payroll head name already exists.');
    }
        $income_type = $request->income_type ?? 'fixed';
        $affect_gross_salary = $request->affect_gross_salary ?? 1;
        $affect_net_salary = null;

// universal logic (same as create)
if ($affect_gross_salary == 0) {
    $affect_net_salary = $request->affect_net_salary ?? 1;
}
        $slip_name = $request->slip_name ?: $request->name;

        $use_for_gratuity = null;
        $calculation_type = $request->calculation_type;
        $percentage = null;
        $adjustment_type = null;
        $formula_heads = null;
        if ($payroll_head->type == 'basic') {

            $calculation_type = 'user_defined';
            $percentage = null;

            $use_for_gratuity = $request->use_for_gratuity ?? 1;
        }

        elseif ($payroll_head->type == 'da') {

            $use_for_gratuity = $request->use_for_gratuity ?? 1;

            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for DA.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Basic must exist for percentage calculation.');
                }

                $percentage = $request->percentage;
            }
        }
        elseif ($payroll_head->type == 'esic') {

            $adjustment_type = 'subtractive';

            $use_for_gratuity = null;


            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for ESIC.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Basic must exist for ESIC.');
                }

                $percentage = $request->percentage;
                $formula_heads = null;
            }

            elseif ($calculation_type == 'custom_formula') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for ESIC formula.');
                }

                if (!$request->formula_heads || count($request->formula_heads) == 0) {
                    return back()->with('error', 'Select at least one head for ESIC formula.');
                }

                $percentage = $request->percentage;

                $formula_heads = json_encode(array_values($request->formula_heads));
            }

            else {
                $percentage = null;
                $formula_heads = null;
            }
        }
        elseif ($payroll_head->type == 'other') {

            $use_for_gratuity = null;

            if (!$request->adjustment_type) {
                return back()->with('error', 'Adjustment type required for Other.');
            }

            $adjustment_type = $request->adjustment_type;


            if ($calculation_type == 'percentage') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required.');
                }

                $basicExists = DB::table('payroll_heads')
                    ->where('company_id', $company_id)
                    ->where('type', 'basic')
                    ->exists();

                if (!$basicExists) {
                    return back()->with('error', 'Basic must exist for percentage calculation.');
                }

                $percentage = $request->percentage;
                $formula_heads = null;
            }

            elseif ($calculation_type == 'custom_formula') {

                if (!$request->percentage) {
                    return back()->with('error', 'Percentage required for custom formula.');
                }

                if (!$request->formula_heads || count($request->formula_heads) == 0) {
                    return back()->with('error', 'Select at least one head for formula.');
                }

                $percentage = $request->percentage;
                $formula_heads = json_encode($request->formula_heads);
            }

            else {

                $percentage = null;
                $formula_heads = null;
            }
        }

        DB::table('payroll_heads')
        ->where('company_id', $company_id)
        ->where('id', $id)
        ->update([
            'name' => $request->name,
            'income_type' => $income_type,
            'linked_account_id' => $request->linked_account_id,
            'affect_gross_salary' => $affect_gross_salary,
            'affect_net_salary' => $affect_net_salary,
            'adjustment_type' => $adjustment_type,
            'slip_name' => $slip_name,
            'use_for_gratuity' => $use_for_gratuity,
            'calculation_type' => $calculation_type,
            'percentage' => $percentage,
            'formula_heads' => $formula_heads,
            'updated_at' => now()
        ]);

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll head updated successfully.');
    }

    public function destroy(Request $request)
    {
        $company_id = Session::get('user_company_id');

        $id = $request->id;

        $record = DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->where('id', $id)
            ->first();

        if (!$record) {
            return back()->with('error', 'Record not found.');
        }

        DB::table('payroll_heads')
            ->where('company_id', $company_id)
            ->where('id', $id)
            ->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll head deleted successfully.');
    }
}