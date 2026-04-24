<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounts;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\State;
use App\Models\AccountGroups;
use App\Models\AccountLedger;
use App\Models\AccountHeading;
use App\Models\Bank;
use App\Models\Sales;
use App\Models\AccountOtherAddress;
use Session;
use DB;
use Gate;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
class AccountsController extends Controller{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(Request $request){
      Gate::authorize('view-module', 5);
      
      
               
      return view('account/account');
   }


public function datatable(Request $request)
{
    $com_id = Session::get('user_company_id');

    $status = ['1','0'];
    $incomplete_status = ['1','0'];

    if ($request->filter === 'Enable') {
        $status = ['1'];
    } elseif ($request->filter === 'Disable') {
        $status = ['0'];
    } elseif ($request->filter === 'InComplete') {
        $incomplete_status = ['1'];
    }

    $query = DB::table('accounts')
        ->select(
            'accounts.id',
            'accounts.account_name',
            'accounts.print_name',
            'accounts.gstin',
            'accounts.status',
            'accounts.company_id',
             DB::raw("EXISTS (
                  SELECT 1 FROM account_ledger al
                  WHERE al.account_id = accounts.id
                  AND al.delete_status = '0'
               ) as has_ledger"),
               DB::raw("EXISTS (
                  SELECT 1 FROM sales s
                  WHERE s.shipping_name = accounts.id
                  AND s.status = '0'
               ) as has_sales"),
            DB::raw("COALESCE(account_groups.name, account_headings.name) as group_name")
        )
        ->leftJoin('account_groups', function ($join) {
            $join->on('account_groups.id', 'accounts.under_group')
                 ->where('accounts.under_group_type', 'group');
        })
        ->leftJoin('account_headings', function ($join) {
            $join->on('account_headings.id', 'accounts.under_group')
                 ->where('accounts.under_group_type', 'head');
        })
        
        ->whereIn('accounts.company_id', [$com_id, 0])
        ->whereIn('accounts.status', $status)
        ->whereIn('accounts.incomplete_status', $incomplete_status)
        ->where('accounts.delete', '0');

    return DataTables::of($query)
        ->filterColumn('group_name', function ($query, $keyword) {
        $query->where(function ($q) use ($keyword) {
            $q->where('account_groups.name', 'LIKE', "%{$keyword}%")
              ->orWhere('account_headings.name', 'LIKE', "%{$keyword}%");
        });
    })
        ->addColumn('status_label', fn ($row) =>
            $row->status == 1
                ? '<span class="text-success">Enable</span>'
                : '<span class="text-danger">Disable</span>'
        )
        ->addColumn('action', function ($row) {
            $html = '';

            if (Gate::allows('action-module', 41)) {
                $html .= '<a href="'.route('account.edit', $row->id).'">
                            <img src="'.asset('public/assets/imgs/edit-icon.svg').'">
                          </a>';
            }

            if (!$row->has_ledger && !$row->has_sales && $row->company_id != 0 && Gate::allows('action-module', 42)) {
                $html .= '<button class="border-0 bg-transparent delete" data-id="'.$row->id.'">
                            <img src="'.asset('public/assets/imgs/delete-icon.svg').'">
                          </button>';
            }

            return $html;
        })
        ->rawColumns(['status_label', 'action'])
        ->make(true);
}

   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function create(){
      Gate::authorize('view-module', 73);
      $formCompanyId   = Session::get('user_company_id');
      $formCompanyName = \App\Models\Companies::where('id', $formCompanyId)->value('company_name');
      $accountgroup = AccountGroups::where('delete', '=', '0')
                     ->whereIn('company_id', [$formCompanyId, 0])
                     ->orderBy('name')
                     ->get();

      $state_list = State::all();
      $tds_sections = DB::table('tds_sections')->orderBy('section')->get();
      return view('account/addAccount')->with('state_list', $state_list)->with('tds_sections', $tds_sections)->with('accountgroup', $accountgroup)->with('formCompanyId', $formCompanyId)->with('formCompanyName', $formCompanyName);
   }
   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
   */
   public function storebkp(Request $request){
      Gate::authorize('view-module', 73);
      $request->validate([
         'gstin' => ['nullable',Rule::unique('accounts')
            ->where(fn($query) => $query->where('company_id', Session::get('user_company_id')))],
         'account_name' => 'required|string',
      ]);
      $com_id = Session::get('user_company_id');
      $check = Accounts::select('id')
                        ->where('account_name',$request->input('account_name'))
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [$com_id,0])
                        ->get();
      if(count($check)>0){
         return $this->failedMessage('Account Already Created.');
         exit();
      }
      DB::beginTransaction();
      try {
      $account = new Accounts;
      $account->account_name = $request->input('account_name');
      $account->company_id =  Session::get('user_company_id');
      $account->print_name = $request->input('print_name');
      $account->under_group = $request->input('under_group');
      $account->opening_balance = $request->input('opening_balance');
      $account->dr_cr = $request->input('opening_balance_type');
      $account->tax_type = $request->input('tax_type');      
      $account->gstin = $request->input('gstin');
      $account->state = $request->input('state');
      $account->address = $request->input('address');
      $account->address2 = $request->input('address2');
      $account->address3 = $request->input('address3');
      $account->pan = $request->input('pan');
      $account->pin_code = $request->input('pincode');
      $account->due_day = $request->input('due_day');  
      $account->rcm = $request->input('rcm');
      $account->rcm_rate = $request->input('rcm_rate');  
      $account->credit_limit = $request->input('credit_limit');
      $account->contact_person = $request->input('contact_person');
      $account->mobile = $request->input('mobile_no');
      $account->whatsup_number = $request->input('whatsapp_no');
      $account->email = $request->input('email');
      $account->under_group_type = $request->input('under_group_type');
      $account->bank_account_no = $request->input('account_no');
      $account->ifsc_code = $request->input('ifsc_code');      
      $account->bank_name = $request->input('bank_name');       
      $account->nature_of_account = $request->input('nature_of_account');
      $account->branch = $request->input('branch');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->sms_status = ($request->sms_status == "Yes") ? '1' : '0';
      $account->credit_days = $request->credit_days ?? null;
      $account->tcs_applicable = $request->tcs_applicable ?? '0';
      $account->tds_applicable = $request->tds_applicable ?? '0';
      $account->save();
      if (!$account->id) {
         throw new \Exception('ACCOUNT SAVE FAILED');
      }
         //Account Ledger Update
         if(!empty($request->input('opening_balance')) && !empty($request->input('opening_balance_type'))){
            $ledger = new AccountLedger();
            $ledger->account_id = $account->id;
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
            }
            $ledger->company_id = Session::get('user_company_id');
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
            if (!$ledger->id) {
               throw new \Exception('OPENING LEDGER SAVE FAILED');
            }
         }
         if($request->input('under_group')==7){
            $bank = new Bank;
            $bank->user_id = Session::get('user_id');
            $bank->company_id =  Session::get('user_company_id');
            $bank->name = $request->input('account_name');
            $bank->bank_name = $request->input('bank_name');
            $bank->account_no = $request->input('account_no');
            $bank->ifsc = $request->input('ifsc_code');
            $bank->branch = $request->input('branch');
            $bank->save();
            if (!$bank->id) {
               throw new \Exception('BANK SAVE FAILED');
            }
               $upaccount = Accounts::find($account->id);
               $upaccount->bank_map_id = $bank->id;
               $upaccount->save();
            if (!$upaccount->bank_map_id) {
               throw new \Exception('BANK MAP UPDATE FAILED');
            }            
         }
         if($request->input('form_type') && $request->input('form_type')=="bank"){
            DB::commit();
            return response()->json([
               'status' => true,
               'data' => "",
               'message' => 'Account added successfully!'
            ]);
         }
         //Other Address
          
         if(!empty($request->input('other_address'))  && !empty($request->input('other_pincode'))){
            $other_address = $request->input('other_address');
            $other_pincode = $request->input('other_pincode');
            if(count($other_address)>0 && count($other_pincode)>0){
               foreach($other_address as $key => $val){
                  if(!empty($val) && !empty($other_pincode[$key])){
                     $inserted = DB::table('account_other_addresses')->insert([
                        'account_id' => $account->id,
                        'address' => $val,
                        'pincode' => $other_pincode[$key],
                        'created_at' => Carbon::now(),
                        'company_id' => Session::get('user_company_id'),
                     ]);
                     if (!$inserted) {
                        throw new \Exception('OTHER ADDRESS INSERT FAILED');
                     }
                  }
               }
            }            
         }
         DB::commit();
         if ($request->ajax()) {

            $state = DB::table('states')
               ->where('id', $account->state)
               ->first();

            return response()->json([
               'status' => true,
               'account' => [
                     'id'           => $account->id,
                     'account_name' => $account->account_name,
                     'gstin'        => $account->gstin ?? '',
                     'address'      => $account->address ?? '',
                     'state'        => $account->state ?? '',
                     'state_code'   => $state->state_code ?? '',
               ],
               'message' => 'Account added successfully!'
            ]);
         }
         return redirect('account')->withSuccess('Account added successfully!');

         } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('ACCOUNT CREATE FAILED', [
               'error' => $e->getMessage(),
               'request' => $request->all(),
            ]);

            if ($request->ajax()) {
               return response()->json([
                  'status' => false,
                  'message' => $e->getMessage()
               ], 422);
            }

         return back()->withError($e->getMessage());

         }      
   }
   public function store(Request $request){
      // echo "<pre>";
      // print_r($request->all());die;
      //dd($request->sms_status, $request->credit_days);
      Gate::authorize('view-module', 73);
      $formCompanyId = Session::get('user_company_id');
      if (!$formCompanyId) {
         return back()->withError('Invalid company context. Please reload the page.');
      }
       
      $request->validate([
         'gstin' => ['nullable',Rule::unique('accounts')
            ->where(fn($query) => $query->where('company_id', $formCompanyId))],
         'account_name' => 'required|string',
      ]);
     
      $request->validate([
         'account_name' => 'required',
         'print_name'   => 'required',
      ]);

      $check = Accounts::select('id')
                        ->where('account_name',$request->input('account_name'))
                        ->where('delete', '=', '0')
                        ->whereIn('company_id', [$formCompanyId, 0])
                        ->get();
      if(count($check)>0){
         return $this->failedMessage('Account Already Created.');
         exit();
      }
      $account = new Accounts;
      $account->account_name = $request->input('account_name');
      $account->company_id =  $formCompanyId;
      $account->print_name = $request->input('print_name');
      $account->under_group = $request->input('under_group');
      $account->opening_balance = $request->input('opening_balance');
      $account->dr_cr = $request->input('opening_balance_type');
      $account->tax_type = $request->input('tax_type');

      $account->tds_tcs = $request->input('tds_tcs');
      $account->tds_type = $request->input('tds_type');
      $account->tds_section = $request->input('tds_section');

      $account->rcm = $request->input('rcm');
      $account->rcm_rate = $request->input('rcm_rate');

      $account->gstin = $request->input('gstin');
      $account->state = $request->input('state');
      $account->address = $request->input('address');
      $account->address2 = $request->input('address2');
      $account->address3 = $request->input('address3');
      $account->pan = $request->input('pan');
      $account->pin_code = $request->input('pincode');
      $location = $request->input('location');
      $location = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $location);
      $location = preg_replace('/\s+/', ' ', trim($location));
      $account->location = strtoupper($location);
      $account->due_day = $request->input('due_day');
      $account->credit_limit = $request->input('credit_limit');
      $account->contact_person = $request->input('contact_person');
      $account->mobile = $request->input('mobile_no');
      $account->whatsup_number = $request->input('whatsapp_no');
      $account->email = $request->input('email');
      $account->under_group_type = $request->input('under_group_type');
      $account->bank_account_no = $request->input('account_no');
      $account->ifsc_code = $request->input('ifsc_code');      
      $account->bank_name = $request->input('bank_name');       
      $account->nature_of_account = $request->input('nature_of_account');
      $account->branch = $request->input('branch');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->sms_status = ($request->sms_status == "Yes") ? '1' : '0';
      $account->credit_days = $request->credit_days ?? null;
      $account->tcs_applicable = $request->tcs_applicable ?? '0';
      $account->save();
      if($account->id) {
         //Account Ledger Update
         if(!empty($request->input('opening_balance')) && !empty($request->input('opening_balance_type'))){
            $ledger = new AccountLedger();
            $ledger->account_id = $account->id;
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
            }
            $ledger->company_id = $formCompanyId;
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }
         if($request->input('under_group')==7){
            $bank = new Bank;
            $bank->user_id = Session::get('user_id');
            $bank->company_id =  $formCompanyId;
            $bank->name = $request->input('account_name');
            $bank->bank_name = $request->input('bank_name');
            $bank->account_no = $request->input('account_no');
            $bank->ifsc = $request->input('ifsc_code');
            $bank->branch = $request->input('branch');
            if($bank->save()){
               $upaccount = Accounts::find($account->id);
               $upaccount->bank_map_id = $bank->id;
               $upaccount->save();
            }            
         }
         if($request->input('form_type') && $request->input('form_type')=="bank"){
            $res = array(
               'status' => true,
               'data' => "",
               "message"=>"Account added successfully!"
            );
            return json_encode($res);
         }
         //Other Address
          
         if(!empty($request->input('other_address'))  && !empty($request->input('other_pincode'))){
            $other_address = $request->input('other_address');
            $other_pincode = $request->input('other_pincode');
            $other_pincode = $request->input('other_location');
            if(count($other_address)>0 && count($other_pincode)>0){
               foreach($other_address as $key => $val){
                  if(!empty($val) && !empty($other_pincode[$key])){
                     DB::table('account_other_addresses')->insert([
                        'account_id' => $account->id,
                        'address' => $val,
                        'pincode' => $other_pincode[$key],
                        'location' => $other_location[$key],
                        'created_at' => Carbon::now(),
                        'company_id' => $formCompanyId,
                     ]);
                  }
               }
            }            
         }
         
         if ($request->ajax()) {
            $state = DB::table('states')
               ->where('id', $account->state)
               ->first();
            return response()->json([
               'status' => true,
               'id'     => $account->id,
               'name'   => $account->account_name,
               'account' => [
                     'id'           => $account->id,
                     'account_name' => $account->account_name,
                     'gstin'        => $account->gstin ?? '',
                     'address'      => $account->address ?? '',
                     'state'        => $account->state ?? '',
                     'state_code'   => $state->state_code ?? '',
               ],
               'message' => 'Account added successfully!'
            ]);
         }
         return redirect('account')->withSuccess('Account added successfully!');
      }else{
         return $this->failedMessage('Something went wrong, please try again after some time.');
      }
   }
   public function edit($id){
      Gate::authorize('view-module', 41);
      $account = Accounts::find($id);
      $formCompanyId   = $account->company_id;
      $formCompanyName = \App\Models\Companies::where('id', $formCompanyId)->value('company_name');

      if ($formCompanyId != Session::get('user_company_id') && $formCompanyId!=0) {
         abort(403, 'You are trying to edit an Account of another company');
      }
      $sectionExists = DB::table('tds_sections')
                        ->where('id', $account->tds_section)
                        ->exists();
      if (!$sectionExists) {
         $account->tds_section = null;
      }
      $accountgroup = AccountGroups::where('delete', '=', '0')
                     ->whereIn('company_id', [$formCompanyId, 0])
                     ->orderBy('name')
                     ->get();
      $accountheading = AccountHeading::where('delete', '=', '0')
                     ->whereIn('company_id', [$formCompanyId, 0])
                     ->orderBy('name')
                     ->get();
      foreach($accountgroup as $key => $val){
         if($val->primary=='No' && $val->heading_type=='group'){
            $super_parent_id = $this->getSuperParentGroupId($val->heading);
            $accountgroup[$key]->super_parent_id = $super_parent_id;
         }
      }
      $credit_days = DB::table('manage_credit_days')
                  ->where('status','1')
                  ->where('company_id', $formCompanyId)
                  ->orderBy('days')
                  ->get();
      $state_list = State::all();  
      $other_address = AccountOtherAddress::where('account_id',$id)
         ->where('status','1')
         ->where('company_id',$formCompanyId)
         ->get();    
      $tds_sections = DB::table('tds_sections')->orderBy('section')->get();
      return view('account/add_account')->with('state_list', $state_list)->with('tds_sections', $tds_sections)->with('credit_days', $credit_days)->with('formCompanyId', $formCompanyId)->with('formCompanyName', $formCompanyName)->with('accountheading', $accountheading)->with('accountgroup', $accountgroup)->with('account', $account)->with('id', $id)->with('other_address', $other_address);
   }
   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
   */
   public function update(Request $request){
      Gate::authorize('view-module', 41);
      $validator = Validator::make($request->all(), [
         'account_name' => 'required|string',
      ],[
         'account_name.required' => 'Name is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $formCompanyId = Session::get('user_company_id');
      $account =  Accounts::find($request->account_id);
      $oldGstin = $account->gstin;
     //   if (!$formCompanyId || $account->company_id != $formCompanyId) {
     //      abort(403, 'Company mismatch detected. Update blocked.');
     //   }
      $incomplete_status = $account->incomplete_status;
      $account->account_name = $request->input('account_name');
      $account->print_name = $request->input('print_name');
      $account->under_group = $request->input('under_group');
      $account->opening_balance = $request->input('opening_balance');
      $account->dr_cr = $request->input('opening_balance_type');
      $account->tax_type = $request->input('tax_type');

      $account->tds_tcs = $request->input('tds_tcs');
      $account->tds_type = $request->input('tds_type');
      $account->tds_section = $request->input('tds_section');

      $account->rcm = $request->input('rcm');
      $account->rcm_rate = $request->input('rcm_rate');

      $account->gstin = $request->input('gstin');
      if (empty($oldGstin) && !empty($request->input('gstin'))) {
      $account->gst_effective_from = date('Y-m-d');
}
      $account->state = $request->input('state');
      $account->address = $request->input('address');
      $account->address2 = $request->input('address2');
      $account->address3 = $request->input('address3');
      $account->pan = $request->input('pan');
      $account->pin_code = $request->input('pincode');
      $location = $request->input('location');
      $location = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $location);
      $location = preg_replace('/\s+/', ' ', trim($location));
      $account->location = strtoupper($location);
      $account->due_day = $request->input('due_day'); 
      $account->under_group_type = $request->input('under_group_type');     
      $account->credit_limit = $request->input('credit_limit');
      $account->contact_person = $request->input('contact_person');
      $account->mobile = $request->input('mobile_no');
      $account->whatsup_number = $request->input('whatsapp_no');
      $account->email = $request->input('email');
      $account->bank_account_no = $request->input('account_no');
      $account->ifsc_code = $request->input('ifsc_code');      
      $account->bank_name = $request->input('bank_name');       
      $account->nature_of_account = $request->input('nature_of_account');
      $account->branch = $request->input('branch');
      $account->income_tax_class = $request->input('income_tax_class');
      $account->income_tax_dep_method = $request->input('income_tax_dep_method');
      $account->income_tax_dep_rate = $request->input('income_tax_dep_rate');      
      $account->status = $request->input('status');
      $account->updated_at = Carbon::now();
      $account->incomplete_status = 0;
      $account->sms_status = ($request->sms_status == "Yes") ? '1' : '0';
      $account->credit_days = $request->credit_days ?? null;
      $account->tcs_applicable = $request->tcs_applicable ?? '0';

      $account->update();
      //Account Ledger Update
      if(!empty($request->input('opening_balance')) && !empty($request->input('opening_balance_type'))){
         $check = AccountLedger::where('account_id',$request->account_id)
                                 ->where('entry_type','-1')
                                 ->first();
         if($check){
            $ledger = AccountLedger::find($check->id);
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
               $ledger->credit = "";
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
               $ledger->debit = "";
            }
            $ledger->updated_by = Session::get('user_id');
            $ledger->updated_at = date('d-m-Y H:i:s');
            $ledger->save();
         }else{
            $ledger = new AccountLedger();
            $ledger->account_id = $request->account_id;
            if($request->input('opening_balance_type')=='debit'){
               $ledger->debit = $request->input('opening_balance');
            }else if($request->input('opening_balance_type')=='credit'){
               $ledger->credit = $request->input('opening_balance');
            }
            $ledger->company_id = $formCompanyId;
            $ledger->financial_year = Session::get('default_fy');
            $ledger->entry_type = -1;
            $ledger->created_by = Session::get('user_id');
            $ledger->created_at = date('d-m-Y H:i:s');
            $ledger->save();
         }         
      }else{
         $check = AccountLedger::where('account_id',$request->account_id)
                                 ->where('entry_type','-1')
                                 ->first();
         if($check){
            AccountLedger::where('entry_type','-1')
                           ->where('account_id',$request->account_id)
                           ->delete();
         }
      }
      if($request->input('under_group')==7){
         if(!empty($account->bank_map_id)){
            $bank = Bank::find($account->bank_map_id);
         }else{
            $bank = new Bank;
         }         
         $bank->user_id = Session::get('user_id');
         $bank->company_id =  $formCompanyId;
         $bank->name = $request->input('account_name');
         $bank->bank_name = $request->input('bank_name');
         $bank->account_no = $request->input('account_no');
         $bank->ifsc = $request->input('ifsc_code');
         $bank->branch = $request->input('branch');
         $bank->save();
      }else{
         if(!empty($account->bank_map_id)){
            Bank::where('id',$account->bank_map_id)->delete();
         }
      }
      //Other Address
      AccountOtherAddress::where('account_id',$request->account_id)
      ->where('company_id',$formCompanyId)
      ->update(['status'=>'0','updated_at'=>Carbon::now()]); 
      if(!empty($request->input('other_address'))  && !empty($request->input('other_pincode'))){
         $other_address = $request->input('other_address');
         $other_pincode = $request->input('other_pincode');
         $other_location = $request->input('other_location');
         if(count($other_address)>0 && count($other_pincode)>0){
            foreach($other_address as $key => $val){
               if(!empty($val) && !empty($other_pincode[$key])){
                  DB::table('account_other_addresses')->insert([
                     'account_id' => $account->id,
                     'address' => $val,
                     'pincode' => $other_pincode[$key],
                     'location' => $other_location[$key],
                     'created_at' => Carbon::now(),
                     'company_id' => $formCompanyId,
                  ]);
               }
            }
         }            
      }
      if($incomplete_status==1){
         return redirect('account?filter=InComplete')->withSuccess('Account updated successfully!');
      }else{
         return redirect('account')->withSuccess('Account updated successfully!');
      }
      
   }
   /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
   */
   public function delete(Request $request)
   {
      Gate::authorize('view-module', 42);

      // Check if any normal transactions exist (not opening)
      $exist = AccountLedger::where('account_id', $request->account_id)
         ->where('entry_type', '!=', -1)
         ->where('delete_status', "0")
         ->first();

      if ($exist) {
      
         return redirect('account')->with('error', 'Account cannot be deleted. Transactions exist.');
      }

      // Find opening ledger entry
      $exist_opening = AccountLedger::where('account_id', $request->account_id)
         ->where('entry_type', -1)
         ->where('delete_status', "0")
         ->first();

      // ❌ If opening exists AND debit or credit is NOT zero → block deletion
      if ($exist_opening &&
         (($exist_opening->debit ?? 0) != 0 || ($exist_opening->credit ?? 0) != 0 || ($exist_opening->credit ?? 0) != null || ($exist_opening->debit ?? 0) != null)
      ) {
         return redirect('account')->with('error', 'Account cannot be deleted. Opening balance existed. Made it zero to Delete.');
      }
      $exist_shipp_to = Sales::where('shipping_name', $request->account_id)
         ->where('delete', '0')
         ->first();
      if ($exist_shipp_to) {      
         return redirect('account')->with('error', 'Account cannot be deleted. Used In Shipping Transactions.');
      }
      // If no opening exists OR opening is zero → allow deletion
      $account = Accounts::find($request->account_id);

      if ($account) {

         // Soft delete account
         $account->delete = '1';
         $account->deleted_at = Carbon::now();
         $account->save();

         // Soft delete other addresses
         AccountOtherAddress::where('account_id', $request->account_id)
               ->where('company_id', Session::get('user_company_id'))
               ->update([
                  'status' => 0,
                  'updated_at' => Carbon::now()
               ]);

         // Soft delete bank mapping
         if (!empty($account->bank_map_id)) {
               Bank::where('id', $account->bank_map_id)
                  ->update([
                     'delete_status' => 1,
                     'deleted_at' => Carbon::now()
                  ]);
         }

         return redirect('account')->withSuccess('Account deleted successfully!');
      }

      return redirect('account')->with('error', 'Account not found.');
   }
   public function failedMessage($msg){
      return redirect('account')->withError($msg);
   }
   public function addAccount(){
      Gate::authorize('view-module', 73);
      $formCompanyId   = Session::get('user_company_id');
      $formCompanyName = \App\Models\Companies::where('id', $formCompanyId)
                            ->value('company_name');
      $accountgroup = AccountGroups::select('id','name','primary','heading','heading_type')->where('delete', '=', '0')
                     ->whereIn('company_id', [$formCompanyId,0])
                     ->orderBy('name')
                     ->get();
      $accountheading = AccountHeading::where('delete', '=', '0')
                     ->whereIn('company_id', [$formCompanyId,0])
                     ->orderBy('name')
                     ->get();

       // Fetch credit days list
      $credit_days = DB::table('manage_credit_days')
                  ->where('status', '1')
                  ->where('company_id', $formCompanyId)
                  ->orderBy('days')
                  ->get();
         foreach($accountgroup as $key => $val){
            if($val->primary=='No' && $val->heading_type=='group'){
              $super_parent_id = $this->getSuperParentGroupId($val->heading);
              $accountgroup[$key]->super_parent_id = $super_parent_id;
           }
         }
                     
      
      // echo "<pre>";
      // print_r($accountgroup->toArray());
      // echo "</pre>";
      $state_list = State::orderBy('state_code')->get();
      $tds_sections = DB::table('tds_sections')->orderBy('section')->get();
      return view('account/add_account')->with('credit_days', $credit_days)->with('tds_sections', $tds_sections)->with('state_list', $state_list)->with('accountgroup', $accountgroup)->with('formCompanyId', $formCompanyId)->with('formCompanyName', $formCompanyName)->with('accountheading', $accountheading);
   }
   public function importAccountView(Request $request){
      return view('account/account_import')->with('upload_log',0);
   }
   public function importAccountProcessbkp(Request $request) { 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $financial_year = Session::get('default_fy');    
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_invoice_count = $total_row - 1;
         $success_row = 0;
         $index = 1;
         $error_arr = [];$all_error_arr = [];
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         $incomplete_status_count = 0;
         
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map('trim', $data);       
            $data = array_map(function ($value) {
               return trim(rtrim(stripslashes($value), '\\'));
            }, $data);     
            $name = $data[0];
            $under_group = $data[1];
            $debit = $data[2];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[3];
            $credit = trim(str_replace(",","",$credit));
            $gstin = $data[4];
            $address = rtrim($data[5], "\\");
            $state = $data[6];
            $pincode = $data[7];
            if($name=="" || $under_group==""){
               array_push($all_error_arr,array("error_title"=>"Required","mesaage"=>'Name,under group empty - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $check_group = AccountGroups::select('id')
                                       ->where('name',$under_group)
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            $check_head = AccountHeading::select('id')
                                       ->where('delete', '=', '0')
                                       ->where('status','1')
                                       ->where('name',$under_group)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            if(!$check_group && !$check_head){
               array_push($all_error_arr,array("error_title"=>"Group Not Found","mesaage"=>'Under Group '.$under_group.' not found - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $under_group_id = "";
            $under_group_type = "";
            if($check_group){
               $under_group_id = $check_group->id;
               $under_group_type = 'group';
            }else if($check_head){
               $under_group_id = $check_head->id;
               $under_group_type = 'head';
            }            
            $check_account = Accounts::select('id')
                                       ->where('account_name',$name)
                                       ->where('company_id',Session::get('user_company_id'))
                                       ->first();
            if($check_account){
               array_push($all_error_arr,array("error_title"=>"Already Exists","mesaage"=>'Account Name '.$name.' already exists - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $opening_balance = 0;$opening_balance_type = "debit";
            if($debit!="" && $debit!="0" && $debit!="0.00"){
               $opening_balance = $debit;
               $opening_balance_type = "debit";
            }else if($credit!="" && $credit!="0" && $credit!="0.00"){
               $opening_balance = $credit;
               $opening_balance_type = "credit";
            }
            if(!empty($gstin)){
               $state_id = State::select('id')->where('state_code',substr($gstin, 0, 2))->first();
               if(!$state_id){
                  $state = "";
               }else{
                  $state = $state_id->id;
               }
            }else{
               $state = "";
            }            
            $incomplete_status = 0;
            if($check_group){
               if($check_group->id==1 || $check_group->id==7 || $under_group=='Fixed Assets'){
                  $incomplete_status = 1;
                  $incomplete_status_count++;
               }
               if($check_group->id==3 || $check_group->id==11){
                  if($address=="" || $pincode=="" || $state==""){
                     $incomplete_status = 1;
                     $incomplete_status_count++;
                  }
               }
            }
                 
            //3,11    
            $account = new Accounts;
            $account->account_name = $name;
            $account->company_id =  Session::get('user_company_id');
            $account->print_name = $name;
            $account->under_group = $under_group_id;
            $account->under_group_type = $under_group_type;
            $account->opening_balance = $opening_balance;
            $account->dr_cr = $opening_balance_type;
            $account->gstin = $gstin;
            $account->state = $state;
            $account->address = $address;
            $account->pin_code = $pincode;
            $account->status = '1';
            $account->incomplete_status = $incomplete_status;
            $account->save();
            if($account->id) {
               //Account Ledger Update
               if(!empty($opening_balance) && $opening_balance!=0){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $account->id;
                  if($opening_balance_type=='debit'){
                     $ledger->debit = $opening_balance;
                  }else if($opening_balance_type=='credit'){
                     $ledger->credit = $opening_balance;
                  }
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = -1;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
               }
               $success_invoice_count++;
            }            
            $index++;
         } 
         fclose($handle);
      }
      $return = array();
      foreach($all_error_arr as $val) {
         $return[$val['error_title']][] = $val;
      }
      return view('account/account_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$return)->with('incomplete_status_count',$incomplete_status_count);
   }
   
   public function importAccountProcess(Request $request) { 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $overwrite = $request->overwrite ?? 0;
      $financial_year = Session::get('default_fy');    
      $file = $request->file('csv_file');  
      $filePath = $file->getRealPath();      
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_invoice_count = $total_row - 1;
         $success_row = 0;
         $index = 1;
         $error_arr = [];$all_error_arr = [];
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         $incomplete_status_count = 0;
         
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map('trim', $data);       
            $data = array_map(function ($value) {
               return trim(rtrim(stripslashes($value), '\\'));
            }, $data);     
            $name = $data[0];
            $under_group = $data[1];
            $debit = $data[2];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[3];
            $credit = trim(str_replace(",","",$credit));
            /* ===== ADD THIS BLOCK HERE ===== */
            $opening_balance = 0;
            $opening_balance_type = "debit";

            if($debit!="" && $debit!="0" && $debit!="0.00"){
               $opening_balance = $debit;
               $opening_balance_type = "debit";
            }else if($credit!="" && $credit!="0" && $credit!="0.00"){
               $opening_balance = $credit;
               $opening_balance_type = "credit";
            }
            $gstin = $data[4];
            $address = rtrim($data[5], "\\");
            $state = $data[6];
            $state = strtoupper($state);
            $pincode = $data[7];
            if($name=="" || $under_group==""){
               array_push($all_error_arr,array("error_title"=>"Required","mesaage"=>'Name,under group empty - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $check_group = AccountGroups::select('id')
                                       ->where('name',$under_group)
                                       ->where('delete','0')
                                       ->where('status','1')
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            $check_head = AccountHeading::select('id')
                                       ->where('delete', '=', '0')
                                       ->where('status','1')
                                       ->where('name',$under_group)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->first();
            if(!$check_group && !$check_head){
               array_push($all_error_arr,array("error_title"=>"Group Not Found","mesaage"=>'Under Group '.$under_group.' not found - Row No. '.$index));
               $failed_invoice_count++;
               $index++;
               continue;
            }
            $under_group_id = "";
            $under_group_type = "";
            if($check_group){
               $under_group_id = $check_group->id;
               $under_group_type = 'group';
            }else if($check_head){
               $under_group_id = $check_head->id;
               $under_group_type = 'head';
            }            
            $check_account = Accounts::where('account_name', $name)
               ->where('company_id', Session::get('user_company_id'))
               ->where('delete', '0')
               ->first();

            if ($check_account) {
               if ($overwrite == 1) {
                  // ===== UPDATE EXISTING ACCOUNT =====
                  $check_account->under_group = $under_group_id;
                  $check_account->under_group_type = $under_group_type;
                  $check_account->opening_balance = $opening_balance;
                  $check_account->dr_cr = $opening_balance_type;
                  $check_account->gstin = $gstin;
                  // if(!empty($gstin)){
                  //    $state_id = State::select('id')->where('state_code',substr($gstin, 0, 2))->first();
                  //    if(!$state_id){
                  //       $state = "";
                  //    }else{
                  //       $state = $state_id->id;
                  //    }
                  // }else{
                  //    $state = "";
                  // } 
                  if($state!=""){
                     $state_id = State::select('id')
                                 ->where('name',$state)
                                 ->first();
                     if(!$state_id){
                        $state = "";
                     }else{
                        $state = $state_id->id;
                     }
                  }
                  $check_account->state = $state;
                  $check_account->address = $address;
                  $check_account->pin_code = $pincode;
                  $check_account->status = '1';
                  $check_account->updated_at = now();
                  $check_account->save();

                  // ===== UPDATE OPENING LEDGER =====
                  $ledger = AccountLedger::where('account_id', $check_account->id)
                              ->where('entry_type', -1)
                              ->first();

                  if (!$ledger) {
                        $ledger = new AccountLedger();
                        $ledger->account_id = $check_account->id;
                        $ledger->company_id = Session::get('user_company_id');
                        $ledger->financial_year = Session::get('default_fy');
                        $ledger->entry_type = -1;
                        $ledger->created_by = Session::get('user_id');
                  }

                  if ($opening_balance_type == 'debit') {
                        $ledger->debit = $opening_balance;
                        $ledger->credit = null;
                  } else {
                        $ledger->credit = $opening_balance;
                        $ledger->debit = null;
                  }

                  $ledger->save();

                  $success_invoice_count++;
                  $index++;
                  continue;

               } else {

                  array_push($all_error_arr,array(
                        "error_title"=>"Already Exists",
                        "mesaage"=>'Account Name '.$name.' already exists - Row No. '.$index
                  ));

                  $failed_invoice_count++;
                  $index++;
                  continue;
               }
            }       
            if($state!=""){
               $state_id = State::select('id')
                              ->where('name',$state)
                              ->first();
               if(!$state_id){
                  $state = "";
               }else{
                  $state = $state_id->id;
               } 
            }                                  
            $incomplete_status = 0;
            if($check_group){
               if($check_group->id==1 || $check_group->id==7 || $under_group=='Fixed Assets'){
                  $incomplete_status = 1;
                  $incomplete_status_count++;
               }
               if($check_group->id==3 || $check_group->id==11){
                  if($address=="" || $pincode=="" || $state==""){
                     $incomplete_status = 1;
                     $incomplete_status_count++;
                  }
               }
            }
                 
            //3,11    
            $account = new Accounts;
            $account->account_name = $name;
            $account->company_id =  Session::get('user_company_id');
            $account->print_name = $name;
            $account->under_group = $under_group_id;
            $account->under_group_type = $under_group_type;
            $account->opening_balance = $opening_balance;
            $account->dr_cr = $opening_balance_type;
            $account->gstin = $gstin;
            $account->state = $state;
            $account->address = $address;
            $account->pin_code = $pincode;
            $account->status = '1';
            $account->incomplete_status = $incomplete_status;
            $account->save();
            if($account->id) {
               //Account Ledger Update
               if(!empty($opening_balance) && $opening_balance!=0){
                  $ledger = new AccountLedger();
                  $ledger->account_id = $account->id;
                  if($opening_balance_type=='debit'){
                     $ledger->debit = $opening_balance;
                  }else if($opening_balance_type=='credit'){
                     $ledger->credit = $opening_balance;
                  }
                  $ledger->company_id = Session::get('user_company_id');
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = -1;
                  $ledger->created_by = Session::get('user_id');
                  $ledger->created_at = date('d-m-Y H:i:s');
                  $ledger->save();
               }
               $success_invoice_count++;
            }            
            $index++;
         } 
         fclose($handle);
      }
      $return = array();
      foreach($all_error_arr as $val) {
         $return[$val['error_title']][] = $val;
      }
      return view('account/account_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$return)->with('incomplete_status_count',$incomplete_status_count);
   }
   function getSuperParentGroupId($group_id) {      
      $group = AccountGroups::where('id', $group_id)->where('delete', '0')->first();
      
      if (!$group) {
         return null; // group not found
      }
      if (($group->primary == 'No' && $group->heading_type == 'head') || $group->primary == 'Yes') {
         return $group->id; // reached top-level group
      }
      // Stop if heading is null or same as current
      if (!$group->heading || $group->heading == $group_id) {
         return null;
      }
      return $this->getSuperParentGroupId($group->heading);
   }
     public function updateGst(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'gstin' => ['required', 'string', 'size:15',
                Rule::unique('accounts')
                    ->where(fn ($q) =>
                        $q->where('company_id', Session::get('user_company_id'))
                    )
                    ->ignore($request->account_id)
            ],
        ]);
    
        $account = Accounts::find($request->account_id);
    
        $oldGstin = $account->gstin;

$account->gstin = $request->gstin;
$account->state = $request->state;
$account->address = $request->address;
$account->pin_code = $request->pincode;
$account->pan = $request->pan;

// 👉 Only set effective date if GST was empty before
if(empty($oldGstin) && !empty($request->gstin)){
    $account->gst_effective_from = date('Y-m-d');
}

$account->save();

    
        return response()->json([
            'status' => true,
            'message' => 'GST updated successfully'
        ]);
    }
    public function checkGstinExists(Request $request)
   {
      $companyId = Session::get('user_company_id');

      $query = Accounts::where('gstin', $request->gstin)
         ->where('company_id', $companyId)
         ->where('delete', '0');

      if ($request->filled('account_id')) {
         $query->where('id', '!=', $request->account_id);
      }

      return response()->json([
         'exists' => $query->exists()
      ]);
   }
   public function bulkUpdatePage(Request $request)
{
    Gate::authorize('view-module', 5);

    $companyId = Session::get('user_company_id');

      $rootGroups = [3, 11];

      $childGroupIds = $this->getAllChildGroups($rootGroups, $companyId);

      $finalGroupIds = array_merge($rootGroups, $childGroupIds);

      $groups = AccountGroups::whereIn('id', $finalGroupIds)
         ->where('delete', '0')
         ->whereIn('company_id', [$companyId, 0])
         ->orderBy('name')
         ->get();

    $selectedGroup = $request->group_id ?? null;

    $accounts = [];

    if ($selectedGroup) {
        $accounts = Accounts::where('under_group', $selectedGroup)
            ->where('delete', '0')
            ->whereIn('company_id', [$companyId, 0])
            ->get();
    }

    $state_list = State::orderBy('state_code')->get();

    $credit_days = DB::table('manage_credit_days')
        ->where('status', '1')
        ->where('company_id', $companyId)
        ->orderBy('days')
        ->get();

    return view('account.bulk_account_update', compact(
        'groups',
        'accounts',
        'selectedGroup',
        'state_list',
        'credit_days'
    ));
}
public function bulkUpdateSave(Request $request)
{
    $companyId = Session::get('user_company_id');

    if (!$request->has('accounts')) {
        return back()->withError('No accounts submitted.');
    }

    DB::beginTransaction();

    try {

        foreach ($request->accounts as $id => $data) {

            $account = Accounts::where('id', $id)
                ->where('company_id', $companyId)
                ->first();

            if (!$account) continue;


            if (array_key_exists('gstin', $data))
                $account->gstin = $data['gstin'];

            if (array_key_exists('state', $data))
                $account->state = $data['state'];

            if (array_key_exists('address', $data))
                $account->address = $data['address'];

            if (array_key_exists('pin_code', $data))
                $account->pin_code = $data['pin_code'];

            if (array_key_exists('location', $data))
                $account->location = $data['location'];

            if (array_key_exists('pan', $data))
                $account->pan = $data['pan'];

            if (array_key_exists('credit_days', $data))
                $account->credit_days = $data['credit_days'];

            if (array_key_exists('due_day', $data))
                $account->due_day = $data['due_day'];

            if (array_key_exists('contact_person', $data))
                $account->contact_person = $data['contact_person'];

            if (array_key_exists('mobile', $data))
                $account->mobile = $data['mobile'];

            if (array_key_exists('whatsapp', $data))
                $account->whatsup_number = $data['whatsapp'];

            if (array_key_exists('email', $data))
                $account->email = $data['email'];

            if (array_key_exists('bank_account_no', $data))
                $account->bank_account_no = $data['bank_account_no'];

            if (array_key_exists('ifsc_code', $data))
                $account->ifsc_code = $data['ifsc_code'];

            $account->sms_status = isset($data['sms_status']) ? 1 : 0;

            $account->save();


            if (array_key_exists('opening_balance', $data)) {

               // Use existing dr_cr if not sent from form
               $drCr = $data['dr_cr'] ?? $account->dr_cr ?? 'debit';

               $account->opening_balance = $data['opening_balance'];
               $account->dr_cr = $drCr;
               $account->save();

               $ledger = AccountLedger::where('account_id', $id)
                  ->where('entry_type', -1)
                  ->first();

               if (!$ledger) {
                  $ledger = new AccountLedger();
                  $ledger->account_id = $id;
                  $ledger->company_id = $companyId;
                  $ledger->financial_year = Session::get('default_fy');
                  $ledger->entry_type = -1;
                  $ledger->created_by = Session::get('user_id');
               }

               if ($drCr == 'debit') {
                  $ledger->debit = $data['opening_balance'];
                  $ledger->credit = null;
               } else {
                  $ledger->credit = $data['opening_balance'];
                  $ledger->debit = null;
               }

               $ledger->save();
            }
        }

        DB::commit();

        return back()->withSuccess('Bulk account update completed successfully!');

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('BULK ACCOUNT UPDATE FAILED', [
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);

        return back()->withError('Something went wrong. Please try again.');
    }
}


public function bulkUpdateFetch(Request $request)
{
    $companyId = Session::get('user_company_id');
    $rootGroups = [$request->group_id];
    $childGroupIds = $this->getAllChildGroups($rootGroups, $companyId);
    $finalGroupIds = array_merge($rootGroups, $childGroupIds);
    $accounts = Accounts::leftJoin('manage_credit_days', function($join) use ($companyId){
            $join->on('manage_credit_days.id', '=', 'accounts.credit_days')
                 ->where('manage_credit_days.company_id', $companyId)
                 ->where('manage_credit_days.status', '1');
        })
        ->whereIn('accounts.under_group', $finalGroupIds)
        ->where('accounts.delete', '0')
        ->whereIn('accounts.company_id', [$companyId, 0])
        ->select(
            'accounts.*',
            'manage_credit_days.days as credit_days_label'
        )
        ->get();

    return response()->json([
        'accounts' => $accounts
    ]);
}

private function getAllChildGroups($parentIds, $companyId)
{
    $allIds = [];

    $children = AccountGroups::whereIn('heading', $parentIds)
        ->where('heading_type', 'group')
        ->where('delete', '0')
        ->whereIn('company_id', [$companyId, 0])
        ->pluck('id')
        ->toArray();

    if (!empty($children)) {

        $allIds = array_merge($children, 
            $this->getAllChildGroups($children, $companyId)
        );
    }

    return $allIds;
}
public function allowWithoutGst(Request $request)
{
    $request->validate([
        'account_id' => 'required|exists:accounts,id'
    ]);

    $companyId = Session::get('user_company_id');

    $account = Accounts::where('id', $request->account_id)
        ->where('company_id', $companyId)
        ->first();

    if (!$account) {
        return response()->json([
            'status' => false,
            'message' => 'Account not found'
        ], 404);
    }

    $account->allow_without_gst = 1;
    $account->save();

    return response()->json([
        'status' => true,
        'message' => 'Updated successfully'
    ]);
}


public function exportAccountMaster()
{

    $companyId = Session::get('user_company_id');

    $accounts = DB::table('accounts')
        ->leftJoin('account_groups', function ($join) {
            $join->on('account_groups.id', '=', 'accounts.under_group')
                 ->where('accounts.under_group_type', 'group');
        })
        ->leftJoin('account_headings', function ($join) {
            $join->on('account_headings.id', '=', 'accounts.under_group')
                 ->where('accounts.under_group_type', 'head');
        })
        ->leftJoin('states', 'states.id', '=', 'accounts.state')
        ->where('accounts.company_id', $companyId)  
        ->where('accounts.status', '1')              
        ->where('accounts.delete', '0')             
        ->select(
            'accounts.account_name',
            DB::raw("COALESCE(account_groups.name, account_headings.name) as parent_group"),
            'accounts.opening_balance',
            'accounts.dr_cr',
            'accounts.gstin',
            'accounts.address',
            'states.name as state_name',
            'accounts.pin_code'
        )
        ->orderBy('accounts.account_name')
        ->get();

    $filename = "Account_Master_" . date('YmdHis') . ".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate",
        "Expires" => "0"
    ];

    $columns = [
        'Name',
        'Parent Group',
        'Op. Bal. (Dr)',
        'Op. Bal. (Cr)',
        'GSTIN',
        'Address',
        'State',
        'Pincode'
    ];

    $callback = function () use ($accounts, $columns) {

        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($accounts as $acc) {

            $debit = '';
            $credit = '';

            if ($acc->dr_cr == 'debit') {
                $debit = $acc->opening_balance;
            } elseif ($acc->dr_cr == 'credit') {
                $credit = $acc->opening_balance;
            }

            fputcsv($file, [
                $acc->account_name,
                $acc->parent_group,
                $debit,
                $credit,
                $acc->gstin,
                $acc->address,
                $acc->state_name,
                $acc->pin_code
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function importAccountUpdateProcess(Request $request) { 
      $validator = Validator::make($request->all(), [
         'csv_file' => 'required|file|mimes:csv,txt|max:2048', // Max 2MB, CSV or TXT file
      ]); 
      if ($validator->fails()) {
         return redirect()->back()->withErrors($validator)->withInput();
      }
      $financial_year = Session::get('default_fy');
      $file = $request->file('csv_file');
      $filePath = $file->getRealPath();
      $final_result = array();
      if(($handle = fopen($filePath, 'r')) !== false) {
         $header = fgetcsv($handle, 10000, ",");
         $fp = file($filePath, FILE_SKIP_EMPTY_LINES);
         $total_row = count($fp);
         $total_invoice_count = $total_row - 1;
         $success_row = 0;
         $index = 1;
         $error_arr = [];$all_error_arr = [];
         $success_invoice_count = 0;
         $failed_invoice_count = 0;
         $incomplete_status_count = 0;         
         while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_map('trim', $data);       
            $data = array_map(function ($value) {
               return trim(rtrim(stripslashes($value), '\\'));
            }, $data);     
            $name = $data[0];
            $under_group = $data[1];
            $debit = $data[2];
            $debit = trim(str_replace(",","",$debit));
            $credit = $data[3];
            $credit = trim(str_replace(",","",$credit));
            $gstin = $data[4];
            $address = rtrim($data[5], "\\");
            $state = $data[6];
            $pincode = $data[7];
            if(!empty($gstin)){
               $state_id = State::select('id')->where('state_code',substr($gstin, 0, 2))->first();
               if(!$state_id){
                  $state = "";
               }else{
                  $state = $state_id->id;
               }
            }else{
               $state = "";
            }
            $check_account = Accounts::where('account_name', $name)
                    ->where('company_id', Session::get('user_company_id'))
                    ->first();
            if($check_account){
               Accounts::where('id', $check_account->id)
                    ->where('company_id', Session::get('user_company_id'))
                    ->where('delete', '0')
                    ->update(['gstin' => $gstin, 'address' => $address, 'state' => $state, 'pin_code' => $pincode]);
               $success_invoice_count++;
            }
            $index++;
         } 
         fclose($handle);
      }
      $return = array();
      foreach($all_error_arr as $val) {
         $return[$val['error_title']][] = $val;
      }
      return view('account/account_import')->with('upload_log',1)->with('total_count',$total_invoice_count)->with('success_count',$success_invoice_count)->with('failed_count',$failed_invoice_count)->with('error_message',$return)->with('incomplete_status_count',$incomplete_status_count);
   }
    public function updateOpeningBalance(Request $request)
   {     

      $companyId = Session::get('user_company_id');
      $ledgers = AccountLedger::where('delete_status','0')
         ->where('status','1')
         ->where('company_id', $companyId)
         ->where('entry_type', -1)
         //->limit(1)
         ->get();
        // echo count($ledgers);die;
      foreach($ledgers as $ledger){
         $account = Accounts::where('id', $ledger->account_id)
            ->where('company_id', $companyId)
            ->first();
         if($account){
            $account->opening_balance = ($ledger->debit) ? $ledger->debit : $ledger->credit;
            $account->dr_cr = ($ledger->debit) ? 'debit' : 'credit';
            $account->save();
         }
      }

   }

}
