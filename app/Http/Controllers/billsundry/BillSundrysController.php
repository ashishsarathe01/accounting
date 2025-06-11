<?php

namespace App\Http\Controllers\billsundry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillSundrys;
use App\Models\Accounts;
use App\Models\Sales;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Session;
use Gate;
class BillSundrysController extends Controller
{
     /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Gate::authorize('view-module', 9);
        $com_id = Session::get('user_company_id');
        $billsundry = BillSundrys::where('company_id',$com_id)
                                   ->where('delete', '=', '0')
                                   ->where('status', '=', '1')
                                   //->OrwhereIn('id',[1,2,3,8,9])
                                   ->orderBy('name')
                                   ->get();
        return view('billsundry/billSundrys')->with('billsundry', $billsundry);
    }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $com_id = Session::get('user_company_id');
        $account = Accounts::where('delete', '=', '0')
                           ->whereIn('accounts.company_id', [$com_id,0])
                           ->orderBy('account_name')
                           ->get();
         $financial_year = Session::get('default_fy');
         $last_invoice_date = Sales::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('delete','=','0')
                           ->max(\DB::raw("date"));
        return view('billsundry/addbillSundrys')->with('account', $account)->with('last_invoice_date', $last_invoice_date);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ], [
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $check = BillSundrys::select('id')
                          ->where('name',$request->input('name'))
                          ->where('delete', '=', '0')
                          ->whereIn('company_id', [Session::get('user_company_id')])
                          ->get();
        if(count($check)>0){
           return $this->failedMessage('Bill Sundry Already Created.');
           exit();
        }
        $account = new BillSundrys;
        $account->name = $request->input('name');
        $account->company_id =  Session::get('user_company_id');
        $account->bill_sundry_type = $request->input('bill_sundry_type');
        $account->adjust_sale_amt = $request->input('adjust_sale_amt');
        $account->sale_amt_account = $request->input('sale_amt_account');
        $account->adjust_purchase_amt = $request->input('adjust_purchase_amt');
        $account->nature_of_sundry = $request->input('nature_of_sundry');
        $account->sequence = $request->input('sequence');
        $account->purchase_amt_account = $request->input('purchase_amt_account');
        $account->status = $request->input('status');
        $account->save();

        if ($account->id) {
            return redirect('account-bill-sundry')->withSuccess('Bill sundry created successfully!');
        } else {
            return $this->failedMessage('Something went wrong, please try again after some time.');
        }
    }

    public function edit($id)
    {
      $financial_year = Session::get('default_fy');
        $editbill = BillSundrys::find($id);
        $com_id = Session::get('user_company_id');
        $account = Accounts::where('delete', '=', '0')
                           ->whereIn('accounts.company_id', [$com_id,0])
                           ->orderBy('account_name')
                           ->get();
         $last_invoice_date = Sales::select('voucher_no')                     
                           ->where('company_id',Session::get('user_company_id'))
                           ->where('financial_year','=',$financial_year)
                           ->where('delete','=','0')
                           ->max(\DB::raw("date"));
        return view('billsundry/editBillSundrys')->with('editbill', $editbill)->with('account', $account)->with('last_invoice_date', $last_invoice_date);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',

        ], [
            'name.required' => 'Name is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $account =  BillSundrys::find($request->bill_id);
        $account->name = $request->input('name');
        $account->bill_sundry_type = $request->input('bill_sundry_type');
        $account->adjust_sale_amt = $request->input('adjust_sale_amt');
        $account->sale_amt_account = $request->input('sale_amt_account');
        $account->adjust_purchase_amt = $request->input('adjust_purchase_amt');
        $account->purchase_amt_account = $request->input('purchase_amt_account');
        $account->status = $request->input('status');
        $account->sequence = $request->input('sequence');
        $account->nature_of_sundry = $request->input('nature_of_sundry');
        
        $account->updated_at = Carbon::now();
        $account->update();

        return redirect('account-bill-sundry')->withSuccess('Account bill sundry updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $bill =  BillSundrys::find($request->bill_id);
        $bill->delete = '1';
        $bill->deleted_at = Carbon::now();
        $bill->update();
        if ($bill) {
            return redirect('account-bill-sundry')->withSuccess('Account bill sundry deleted successfully!');
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage($msg)
    {
        return response()->json([
            'code' => 422,
            'message' => $msg,
        ]);
    }
}
