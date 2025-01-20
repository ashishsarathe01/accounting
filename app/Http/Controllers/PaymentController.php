<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Accounts;
use App\Models\Payment;
use App\Models\PaymentDetails;
use App\Models\AccountLedger;
use App\Models\ItemParameter;
use App\Models\ItemParameterList;
use App\Models\ItemParameterPredefinedValue;
use DB;

class PaymentController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payment = DB::table('payment_details')
            ->select('payments.id', 'payments.date','accounts.account_name as acc_name','payment_details.*')
            ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
            ->join('accounts', 'payment_details.account_name', '=', 'accounts.id')
            ->get();

        return view('payment')->with('payment', $payment);
    }
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $party_list = Accounts::where('delete', '=', '0')->get();
        return view('addPayment')->with('party_list', $party_list);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){
      $payment = new Payment;
      $payment->date = $request->input('date');
      $payment->company_id = '2';
      $payment->save();
      if($payment->id){
         $types = $request->input('type');
         $account_names = $request->input('account_name');
         $debits = $request->input('debit');
         $credits = $request->input('credit');
         $modes = $request->input('mode');
         $narrations = $request->input('narration');
         foreach ($types as $key => $type){
            $paytype = new PaymentDetails;
            $paytype->payment_id = $payment->id;
            $paytype->company_id = '2';
            $paytype->type = $type;
            $paytype->account_name = $account_names[$key];
            $paytype->debit = isset($debits[$key]) ? $debits[$key] : '0';
            $paytype->credit = isset($credits[$key]) ? $credits[$key] : '0';
            $paytype->mode = $modes[$key];
            $paytype->narration = $narrations[$key];
            $paytype->status = '1';
            $paytype->save();
            //ADD DATA IN Customer ACCOUNT
            $ledger = new AccountLedger();
            $ledger->account_id = $account_names[$key];
            if(isset($debits[$key]) && !empty($debits[$key])){
               $ledger->debit = $debits[$key];
            }else{
               $ledger->credit = $credits[$key];
            }            
            $ledger->txn_date = $request->input('date');
            $ledger->company_id = Session::get('user_company_id');
            $ledger->entry_type = 6;
            $ledger->entry_type_id = $payment->id;
            $ledger->map_account_id = 2;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
         return redirect('payment')->withSuccess('Payment voucher added successfully!');
      }else{
         $this->failedMessage();
      }
   }
}
