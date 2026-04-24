<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PrivilegesModule; 
use App\Models\MerchantPrivilegeMapping;
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
                $q->where('delete','0');
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
                  ->whereIn('id', $company_ids)->where('delete','0');
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
        $admin_id = Session::get('admin_id');
        $admin_type = Session::get('type');
        Auth::logout();
        $user = User::where('id', $request->id)->first();
        if ($user) {
            Auth::login($user);
            $user_data  = auth()->user();
            $from_date = "";$to_date = "";
            $assign_company = DB::table('assign_companies')
                        ->where('admin_users_id', $admin_id)
                        ->where('merchant_id', $user_data->id)
                        ->first();
            if($assign_company){
                $company = Companies::where('user_id', $user_data->id)
                        ->where('id',$assign_company->comp_id)
                        ->where('delete','0')
                        ->first();
            }else{
                $company = Companies::where('user_id', $user_data->id)
                        //->where('default_company', '1')
                        ->where('delete','0')
                        ->first();
            }
            
            if($company){
                $y = explode("-",$company->default_fy);
                $from_date = date('Y-m-d',strtotime($y[0]."-04-01"));
                $to_date   = date('Y-m-d',strtotime($y[1]."-03-31"));
            }
            // echo "<pre>";
            // print_r($company);die;
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
                'admin_id' => $admin_id,
                'admin_type' => $admin_type,
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
    public function activateMerchant()
    {
        $merchants = User::select('id', 'name', 'mobile_no', 'status')
        ->where('type', 'OWNER')
        ->where('delete_status', '0')
        ->orderBy('created_at', 'desc')
        ->get();
        return view('admin-module.merchant.activate_merchant', compact('merchants'));
    }

    public function updateMerchantStatus(Request $request)
    {
        $request->validate([
        'merchant_id' => 'required|exists:users,id',
        'status' => 'required|in:0,1',
        ]);
        User::where('id', $request->merchant_id)->update([
        'status' => $request->status,
        ]);
        return redirect()->back()->with('success', 'Merchant status updated successfully.');
    }

public function merchantPrivileges($id)
{
    // Get assigned privileges
    $assign_privilege = MerchantPrivilegeMapping::where('merchant_id', $id)
        ->pluck('module_id')
        ->toArray();

    // Get all modules
    $privileges = PrivilegesModule::select('id', 'module_name', 'parent_id')
        ->where('status', 1)
        ->get()
        ->toArray();

    $tree = $this->buildTree($privileges);

    // Get all companies of this merchant
    $company = Companies::select('id', 'company_name')
        ->where('user_id', $id)
        ->where('status', '1')
        ->where('delete', '0')
        ->get();

    return view('admin-module.merchant.merchant_privileges', [
        "privileges" => $tree,
        "merchant_id" => $id,
        "company" => $company,
        "assign_privilege" => $assign_privilege
    ]);
}
private function buildTree(array $elements, $parentId = null)
{
    $branch = [];
    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = $this->buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            }
            $branch[] = $element;
        }
    }
    return $branch;
}
public function setMerchantPrivileges(Request $request)
{
    $selected_modules = $request->privileges ?? [];

    // 🔥 STEP 1: Get parent relationships
    $all_modules = PrivilegesModule::select('id', 'parent_id')
        ->where('status', 1)
        ->get();

    // 🔥 STEP 2: Add parents automatically
    foreach ($selected_modules as $module_id) {

        $parent_id = PrivilegesModule::where('id', $module_id)
            ->value('parent_id');

        while ($parent_id) {

            if (!in_array($parent_id, $selected_modules)) {
                $selected_modules[] = $parent_id;
            }

            $parent_id = PrivilegesModule::where('id', $parent_id)
                ->value('parent_id');
        }
    }

    $selected_modules = array_unique($selected_modules);

    // 🔥 SAVE LOGIC
    if ($request->apply_all) {

        $companies = Companies::select('id')
            ->where('user_id', $request->merchant_id)
            ->where('status', '1')
            ->where('delete', '0')
            ->get();

        foreach ($companies as $company) {

            MerchantPrivilegeMapping::where('merchant_id', $request->merchant_id)
                ->where('company_id', $company->id)
                ->delete();

            foreach ($selected_modules as $module_id) {
                MerchantPrivilegeMapping::create([
                    'module_id' => $module_id,
                    'merchant_id' => $request->merchant_id,
                    'company_id' => $company->id,
                    'created_at' => now(),
                ]);
            }
        }

    } else {

        MerchantPrivilegeMapping::where('merchant_id', $request->merchant_id)
            ->where('company_id', $request->company_id)
            ->delete();

        foreach ($selected_modules as $module_id) {
            MerchantPrivilegeMapping::create([
                'module_id' => $module_id,
                'merchant_id' => $request->merchant_id,
                'company_id' => $request->company_id,
                'created_at' => now(),
            ]);
        }
    }

    return redirect()
        ->route('admin.merchant-module-privileges', $request->merchant_id)
        ->withSuccess('Merchant privileges updated successfully.');
}



}
