<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Companies;
use Illuminate\Support\Facades\DB;
use Auth;
use Session;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        // Get admin type from session (SUPERADMIN / EMPLOYEE)
        $admin_type = Session::get('type');

        if($admin_type == 'SUPERADMIN'){
            // SUPERADMIN: Fetch all merchants and all their companies
            $merchants = User::with(['company' => function($q){
                $q->select('business_type','company_name','gst','user_id');
            }])
            ->select('id','name','email','mobile_no','status')
            ->where('type','OWNER')
            ->where('delete_status','0')
            ->get();
        } else {
            // EMPLOYEE: Fetch only assigned merchants and their assigned companies
            $admin_id = Session::get('admin_id');

            // Get assigned companies for this admin
            $assigned = DB::table('assign_companies')
                        ->where('admin_users_id', $admin_id)
                        ->get();

            $merchant_ids = $assigned->pluck('merchant_id')->unique()->toArray();
            $company_ids  = $assigned->pluck('comp_id')->unique()->toArray();

            // Fetch merchants with only assigned companies
            $merchants = User::with(['company' => function($q) use($company_ids){
                $q->select('business_type','company_name','gst','user_id')
                  ->whereIn('id', $company_ids);
            }])
            ->select('id','name','email','mobile_no','status')
            ->where('type','OWNER')
            ->whereIn('id', $merchant_ids)
            ->where('delete_status','0')
            ->get();
        }

        return view('admin-module.merchant.view_merchant', ['merchants' => $merchants]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $merchant = User::find($id);
        return view("admin-module.merchant.edit_merchant");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Login as merchant
     */
    public function loginMerchant(Request $request)
    {
        // Find user by ID
        Auth::logout();
        $user = User::where('id', $request->id)->first();

        if ($user) {
            Auth::login($user);
            $user_data  = auth()->user();              
            $from_date = "";$to_date = "";            

            $company = Companies::where('user_id', $user_data->id)
                        ->where('default_company', '1')
                        ->first();

            if($company){
                $y = explode("-",$company->default_fy);
                $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
                $to_date   = date('Y-m-d',strtotime($y[1]."-03-31"));
            }            

            Session::put([
                'user_id' => $user_data->id,
                'user_name' => $user_data->name,
                'user_email' => $user_data->email,
                'user_mobile_no' => $user_data->mobile_no,
                'business_type' => isset($company) ? $company->business_type : '',
                'default_fy' => isset($company) ? $company->default_fy : '',
                'from_date' => $from_date,
                'user_type' => $user_data->type,
                'to_date' => $to_date,
                'user_company_id' => isset($company) ? $company->id : '',
            ]);

            $response = [
                'status' => true,
                'message' => 'Login Successfully.'
            ];
            return json_encode($response);
        }

        $response = [
            'status' => false,
            'message' => 'User Not Found.'
        ];
        return json_encode($response);
    }

}
