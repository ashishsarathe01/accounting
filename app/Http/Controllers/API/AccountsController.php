<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\State;
use App\Models\Accounts;
use App\Models\AccountGroups;
use Carbon\Carbon;
use DB;


class AccountsController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function accountList(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'company_id' => 'required',

        ], 
        [
            'company_id.required' => 'Company id is required.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $accounts = Accounts::select('accounts.*',DB::raw('(select company_name from companies where companies.id = accounts.company_id limit 1) as company_name'))->where(['delete'=> '0','company_id'=>$request->company_id])->get();

        if ($accounts) {
            return response()->json([
                'code' => 200,
                'data' => $accounts,
                'dataCount' => $accounts->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string',
            'company_id' => 'required',
        ], [
            'account_name.required' => 'Name is required.',
            'company_id.required' => 'Company id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account = new Accounts;
        $account->company_id = $request->company_id;
        $account->account_name = $request->account_name;
        $account->print_name = $request->print_name;
        $account->under_group = $request->under_group;
        $account->under_group_s = $request->under_group_s;
        $account->opening_balance = $request->opening_balance;
        $account->dr_cr = $request->dr_cr;
        $account->address = $request->address;
        $account->gstin = $request->gstin;
        $account->country = $request->country;
        $account->state = $request->state;
        $account->pin_code = $request->pin_code;
        $account->pan = $request->pan;
        $account->email = $request->email;
        $account->mobile = $request->mobile;
        $account->contact_person = $request->contact_person;
        $account->whatsup_number = $request->whatsup_number;
        $account->maintain_bill_by_details = $request->maintain_bill_by_details;
        $account->credit_days = $request->credit_days;
        $account->limit = $request->limit;
        $account->price_change_sms = $request->price_change_sms;
        $account->bank_name = $request->bank_name;
        $account->bank_account_no = $request->bank_account_no;
        $account->ifsc_code = $request->ifsc_code;
        $account->depreciation_rate = $request->depreciation_rate;
        $account->yearly = $request->yearly;
        $account->per_tax = $request->per_tax;
        $account->company_act = $request->company_act;
        $account->gst_rate = $request->gst_rate;
        $account->hsn_code = $request->hsn_code;
        $account->status = $request->status;
        $account->save();

        if ($account->id) {
            return response()->json(['code' => 200, 'message' => 'Account unit Created successfully!','AccountgData'=> $account,'AccountUnitId'=> $account->id]);
         } 
         else 
         {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
         }
    }

    public function GetAccountbyId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'Account id is required.',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account = Accounts::select('accounts.*',DB::raw('(select company_name from companies where companies.id = accounts.company_id limit 1) as company_name'))->where(['id'=>$request->id,'delete'=>"0"])->first();

        if ($account) {
            return response()->json([
                'code' => 200,
                'data' => $account,
                'dataCount' => $account->count(),
            ]);
        } else {
            $this->failedMessage();
        }
    }


    public function updateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'company_id' => 'required|exists:companies,id',
            'account_name' => 'required|string'
            
        ], [
            'account_id.required' => 'Account id is required.',
            'company_id.required' => 'Company id is required.',
            'account_name.required' => 'Name is required.',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account = Accounts::find($request->account_id);
        $account->company_id = $request->company_id;
        $account->account_name = $request->account_name;
        $account->print_name = $request->print_name;
        $account->under_group = $request->under_group;
        $account->under_group_s = $request->under_group_s;
        $account->opening_balance = $request->opening_balance;
        $account->dr_cr = $request->dr_cr;
        $account->address = $request->address;
        $account->gstin = $request->gstin;
        $account->country = $request->country;
        $account->state = $request->state;
        $account->pin_code = $request->pin_code;
        $account->pan = $request->pan;
        $account->email = $request->email;
        $account->mobile = $request->mobile;
        $account->contact_person = $request->contact_person;
        $account->whatsup_number = $request->whatsup_number;
        $account->maintain_bill_by_details = $request->maintain_bill_by_details;
        $account->credit_days = $request->credit_days;
        $account->limit = $request->limit;
        $account->price_change_sms = $request->price_change_sms;
        $account->bank_name = $request->bank_name;
        $account->bank_account_no = $request->bank_account_no;
        $account->ifsc_code = $request->ifsc_code;
        $account->depreciation_rate = $request->depreciation_rate;
        $account->yearly = $request->yearly;
        $account->per_tax = $request->per_tax;
        $account->company_act = $request->company_act;
        $account->gst_rate = $request->gst_rate;
        $account->hsn_code = $request->hsn_code;
        $account->status = $request->status;
        $account->updated_at = Carbon::now();
        $account->update();

        if (!$account) {
             return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             } else {
                return response()->json(['code' => 200, 'message' => "Accounts details updated successfully",'AccountsData'=> $account,'account_id'=> $account->id]);
             }

    }
    
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id'
            
        ], [
            'account_id.required' => 'Account id is required.'
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $account =  Accounts::find($request->account_id);
        $account->delete = '1';
        $account->deleted_at = Carbon::now();
        $account->update();
         if ($account) 
        {

            return response()->json(['code' => 200, 'message' => 'Account has been deleted successfully!']);
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
