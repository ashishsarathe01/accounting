<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Bank;
use Session;

class BankController extends Controller
{
    public function addBank()
    {
        $inserted_company_id = Session::get('inserted_company_id');
        $inserted_business_type = Session::get('inserted_business_type');
        $bank_data = Bank::where('company_id', $inserted_company_id)->get();

        return view('addBank')->with('company_id', $inserted_company_id)->with('business_type', $inserted_business_type)->with('bank_data', $bank_data);
    }

    public function submitAddBank(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'bank_name' => 'required',
            'account_no' => 'required|string',
        ], [

            'bank_name.required' => 'Bank name is required.',
            'account_no' => 'Account no is required.',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $bank = new Bank;
        $bank->user_id = Auth::id();
        $bank->company_id = $request->company_id;
        $bank->name = $request->name;
        $bank->bank_name = $request->bank_name;
        $bank->account_no = $request->account_no;
        $bank->ifsc = $request->ifsc;
        $bank->branch = $request->branch;
        $bank->save();

        if ($bank) {
            return redirect('add-bank')->withSuccess('bank Added successfully!');
        } else {
            return redirect("add-shareholder")->withError('Something went wrong, please try after some time!');
        }
    }
}
