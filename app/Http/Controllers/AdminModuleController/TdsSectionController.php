<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TdsSection;
use App\Models\Accounts;

class TdsSectionController extends Controller
{

public function index()
{
    $tdsSections = TdsSection::orderBy('id', 'desc')->get();
    return view('admin-module.TdsSection.index', compact('tdsSections'));
}
    public function create()
    {
        return view('admin-module.TdsSection.create');
    }

    public function store(Request $request)
{
    $request->validate([
    'section' => 'required|unique:tds_sections,section',
    'account_name' => 'required',
    'rate_individual_huf' => 'required|numeric',
    'rate_others' => 'required|numeric',
    'applicable_on' => 'required',
    'repeated_transaction_applicable' => 'required',
    'applicable_when' => 'required',
    'exemption_applicable' => 'required',
]);

    $tds = TdsSection::create([
        'section' => $request->section,
        'description' => $request->description,
        'rate_individual_huf' => $request->rate_individual_huf,
        'rate_others' => $request->rate_others,
        'single_transaction_limit' => $request->single_transaction_limit,
        'aggregate_transaction_limit' => $request->aggregate_transaction_limit,
        'applicable_on' => $request->applicable_on,
        'repeated_transaction_applicable' => $request->repeated_transaction_applicable,
        'applicable_when' => $request->applicable_when,
        'exemption_applicable' => $request->exemption_applicable,
    ]);
Accounts::create([
    'company_id' => 0,
    'account_name' => $request->account_name,
    'print_name' => $request->account_name,
    'under_group' => 1,
    'under_group_type' => 'group',
    'under_group_s' => 1,
    'tds_section' => $tds->id,
]);
   return redirect()->route('admin.tds.index')
                     ->with('success', 'TDS Section Saved Successfully');
}

public function edit($id)
{
    $tds = TdsSection::findOrFail($id);
    $account = Accounts::where('tds_section', $id)->first();

    return view('admin-module.TdsSection.edit', compact('tds','account'));
}

public function update(Request $request, $id)
{
    $request->validate([
    'section' => 'required|unique:tds_sections,section,' . $id,
    'account_name' => 'required',
    'rate_individual_huf' => 'required|numeric',
    'rate_others' => 'required|numeric',
    'applicable_on' => 'required',
    'repeated_transaction_applicable' => 'required',
    'applicable_when' => 'required',
    'exemption_applicable' => 'required',
]);

    $tds = TdsSection::findOrFail($id);

$tds->update([
    'section' => $request->section,
    'description' => $request->description,
    'rate_individual_huf' => $request->rate_individual_huf,
    'rate_others' => $request->rate_others,
    'single_transaction_limit' => $request->single_transaction_limit,
    'aggregate_transaction_limit' => $request->aggregate_transaction_limit,
    'applicable_on' => $request->applicable_on,
    'repeated_transaction_applicable' => $request->repeated_transaction_applicable,
    'applicable_when' => $request->applicable_when,
    'exemption_applicable' => $request->exemption_applicable,
]);

$account = Accounts::where('tds_section', $id)->first();

if ($account) {
    $account->update([
        'account_name' => $request->account_name,
        'print_name' => $request->account_name
    ]);
}
    return redirect()->route('admin.tds.index')
        ->with('success', 'TDS Section Updated Successfully');
}

public function delete(Request $request)
{
    $tds = TdsSection::find($request->tds_id);

    if (!$tds) {
        return redirect()->route('admin.tds.index')
            ->with('error', 'Record not found');
    }

    $tds->delete();

    return redirect()->route('admin.tds.index')
        ->with('success', 'TDS Section deleted successfully');
}
}