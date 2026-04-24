<?php

namespace App\Http\Controllers\PurchaseConfiguration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use Session;

class PurchaseConfigurationController extends Controller
{
    /**
     * Show Purchase Configuration page
     */
   public function index()
{
    $formCompanyId = Session::get('user_company_id');

    if (!$formCompanyId) {
        abort(403, 'Company information missing');
    }

    $company = Companies::where('id', $formCompanyId)
                        ->where('status', '1')
                        ->where('delete', '0')
                        ->first();

    return view('PurchaseConfiguration.index', compact('company', 'formCompanyId'));
}
public function store(Request $request)
{
    $request->validate([
        'form_company_id' => 'required',
        'stock_entry_status' => 'required|in:0,1',
    ]);

    $formCompanyId = $request->input('form_company_id');

    if (!$formCompanyId) {
        abort(403, 'Company information missing');
    }

    $company = Companies::where('id', $formCompanyId)
                        ->where('status', '1')
                        ->where('delete', '0')
                        ->first();

    if (!$company) {
        abort(403, 'Company not found');
    }

    $company->stock_entry_status = $request->stock_entry_status;
    $company->save();

    return redirect()->back()->with('success', 'Purchase configuration updated successfully.');
}

}
