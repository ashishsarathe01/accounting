<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use DB;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    // --------------------- View Users ---------------------
    public function index()
    {
        $active_id = Session::get('admin_id');
        $active = DB::table('admins')->where('id', $active_id)->first();

        $users = DB::table('admins')->where('status', '!=', 2)->get();

        // Filter based on role
        if ($active->type == 'ADMIN') {
            $users = $users->filter(fn($u) => $u->type == 'SUBADMIN' && $u->created_by == $active_id)->values();
        } elseif ($active->type == 'SUBADMIN') {
            $users = $users->filter(fn($u) => $u->id == $active_id)->values();
        }

        return view('admin-module.manageUser.user', compact('users'));
    }

    // --------------------- Role-based check ---------------------
    private function canManage($user)
    {
        $active_id = Session::get('admin_id');
        $active = DB::table('admins')->where('id', $active_id)->first();

        if ($active->type == 'SUPERADMIN') return true;
        if ($active->type == 'ADMIN' && $user->type == 'SUBADMIN' && $user->created_by == $active_id) return true;
        return false;
    }

    // --------------------- Create / Store ---------------------
    public function create()
    {
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }
        return view('admin-module.manageUser.create');
    }

    public function store(Request $request)
    {
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $request->validate([
            'name'=>'required',
            'mobile'=>'required',
            'email'=>'required|email',
            'marital_status'=>'required',
            'gender'=>'required',
            'dob'=>'required|date',
            'aadhar_id'=>'required',
            'present_address'=>'required',
            'permanent_address'=>'required',
            'date_of_appointment'=>'required|date',
            'status'=>'required'
        ]);

        $data = $request->only([
            'name','mobile','email','marital_status','gender','dob','aadhar_id',
            'present_address','permanent_address','date_of_appointment','status'
        ]);

        if ($request->hasFile('aadhar_image')) {
            $file = $request->file('aadhar_image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/aadhar'), $filename);
            $data['aadhar_image'] = $filename;
        }

        // Set type based on creator
        $finaltype = $active->type == 'SUPERADMIN' ? 'ADMIN' : 'SUBADMIN';

        $admin = new Admin();
        $admin->fill($data);
        $admin->type = $finaltype;
        $admin->password = Hash::make($request->mobile);
        $admin->created_by = $active->id;
        $admin->save();

        return redirect()->route('admin.manageUser.index')->with('success','User added successfully');
    }

    // --------------------- Edit / Update ---------------------
    public function edit($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        if (!$this->canManage($user)) return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        return view('admin-module.manageUser.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        if (!$this->canManage($user)) return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');

        $request->validate([
            'name'=>'required',
            'mobile'=>'required',
            'email'=>'required|email',
            'marital_status'=>'required',
            'gender'=>'required',
            'dob'=>'required|date',
            'aadhar_id'=>'required',
            'present_address'=>'required',
            'permanent_address'=>'required',
            'date_of_appointment'=>'required|date',
            'status'=>'required'
        ]);

        $data = $request->only([
            'name','mobile','email','marital_status','gender','dob','aadhar_id',
            'present_address','permanent_address','date_of_appointment','status'
        ]);

        if ($request->hasFile('aadhar_image')) {
            $file = $request->file('aadhar_image');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/aadhar'), $filename);
            $data['aadhar_image'] = $filename;
        }

        $data['updated_by'] = Session::get('admin_id');
        $data['updated_at'] = now();

        DB::table('admins')->where('id', $id)->update($data);

        return redirect()->route('admin.manageUser.index')->with('success','User updated successfully');
    }

    // --------------------- Soft Delete ---------------------
    public function destroy($id)
    {
        
        $user = DB::table('admins')->where('id', $id)->first();
        if (!$user || !$this->canManage($user)) {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }
        DB::table('admins')->where('id', $id)->update(['status' => 2]);
        return redirect()->route('admin.manageUser.index')->with('success','User deleted successfully.');
    }

    // --------------------- Assign Companies ---------------------
    public function assignCompanies($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();

        // SUBADMIN cannot assign companies
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        // SUPERADMIN sees all merchants and companies
        if ($active->type == 'SUPERADMIN') {
            $merchants = DB::table('users')->where('status','1')->get()->map(function($merchant) {
                $merchant->company = DB::table('companies')->where('user_id', $merchant->id)->get();
                return $merchant;
            });
        } else { // ADMIN sees only their assigned companies
            $assigned_merchants = DB::table('assign_companies')
                ->where('admin_users_id', $active->id)
                ->pluck('merchant_id')
                ->unique()
                ->toArray();

            $merchants = DB::table('users')
                ->whereIn('id', $assigned_merchants)
                ->where('status', '1')
                ->get()
                ->map(function($merchant) use ($active) {
                    $merchant->company = DB::table('assign_companies')
                        ->join('companies','assign_companies.comp_id','=','companies.id')
                        ->where('assign_companies.admin_users_id', $active->id)
                        ->where('assign_companies.merchant_id', $merchant->id)
                        ->select('companies.*')
                        ->get();
                    return $merchant;
                });
        }

        $assigned = DB::table('assign_companies')->where('admin_users_id', $id)
                    ->pluck('comp_id')->toArray();

        return view('admin-module.manageUser.assign_companies', compact('user','merchants','assigned'));
    }

    public function storeAssignCompanies(Request $request)
    {
        $admin_user_id = $request->admin_user_id;
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();
        $user = DB::table('admins')->where('id', $admin_user_id)->first();

        // SUBADMIN cannot assign
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $merchant_companies = $request->merchant_companies ?? [];

        DB::table('assign_companies')->where('admin_users_id', $admin_user_id)->delete();

        foreach ($merchant_companies as $merchant_id => $company_ids) {
            foreach ($company_ids as $comp_id) {
                DB::table('assign_companies')->insert([
                    'comp_id' => $comp_id,
                    'merchant_id' => $merchant_id,
                    'admin_users_id' => $admin_user_id,
                    'created_at' => now()
                ]);
            }
        }

        return redirect()->route('admin.manageUser.assignCompanies', $admin_user_id)
                         ->with('success','Companies assigned successfully.');
    }

    // --------------------- Privileges ---------------------
    public function privileges($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();

        // SUBADMIN cannot set privileges
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $modules = DB::table('privileges_modules')->where('status',1)->get()->toArray();
        $modules = json_decode(json_encode($modules), true);
        $tree = $this->buildTree($modules);

        $assigned = DB::table('admin_user_privileges_module_mappings')
                      ->where('user_id', $id)
                      ->pluck('module_id')
                      ->toArray();

        return view('admin-module.manageUser.privileges', [
            'user' => $user,
            'privileges' => $tree,
            'assigned' => $assigned
        ]);
    }

    public function setUserPrivileges(Request $request)
    {
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $user_id = $request->user_id;
        $selected = $request->privileges ?? [];

        DB::table('admin_user_privileges_module_mappings')
            ->where('user_id', $user_id)
            ->whereNotIn('module_id', $selected)
            ->delete();

        foreach ($selected as $module_id) {
            DB::table('admin_user_privileges_module_mappings')->updateOrInsert(
                ['user_id' => $user_id, 'module_id' => $module_id],
                ['status' => 1, 'created_at' => Carbon::now()]
            );
        }

        return redirect()->route('admin.manageUser.privileges', $user_id)
                         ->with('success','Privileges updated successfully.');
    }

    private function buildTree(array $elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) { $element['children'] = $children; }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    // --------------------- Admin Panel Privileges ---------------------
    public function adminPrivileges($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();

        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $modules = DB::table('admin_privileges_modules')->where('status',1)->get()->toArray();
        $modules = json_decode(json_encode($modules), true);
        $tree = $this->buildTree($modules);

        $assigned = DB::table('admin_privileges_module_mappings')
                      ->where('employee_id', $id)
                      ->pluck('module_id')
                      ->toArray();

        return view('admin-module.manageUser.admin_privileges', [
            'user' => $user,
            'privileges' => $tree,
            'assigned' => $assigned
        ]);
    }

    public function saveAdminPrivileges(Request $request, $id)
    {
        $active = DB::table('admins')->where('id', Session::get('admin_id'))->first();
        if ($active->type == 'SUBADMIN') {
            return redirect()->route('admin.manageUser.index')->with('error','Unauthorized action.');
        }

        $selected = $request->privileges ?? [];

        DB::table('admin_privileges_module_mappings')
            ->where('employee_id', $id)
            ->whereNotIn('module_id', $selected)
            ->delete();

        foreach ($selected as $module_id) {
            DB::table('admin_privileges_module_mappings')->updateOrInsert(
                ['employee_id' => $id, 'module_id' => $module_id],
                ['status' => 1, 'created_at' => Carbon::now()]
            );
        }

        return redirect()->route('admin.manageUser.adminPrivileges', $id)
                         ->with('success','Admin Panel Privileges updated successfully.');
    }
}
