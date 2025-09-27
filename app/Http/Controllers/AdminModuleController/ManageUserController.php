<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class ManageUserController extends Controller
{
    // List all users
    public function index()
    {
        $users = DB::table('admins')->where('status', '!=', 2)->get();
        return view('admin-module.manageUser.user', compact('users'));
    }

    // Show create form
    public function create()
    {
        return view('admin-module.manageUser.create');
    }

    // Store new user
    public function store(Request $request)
    {
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

        DB::table('admins')->insert($data);
        return redirect()->route('admin.manageUser.index')->with('success','User added successfully');
    }

    // Show edit form
    public function edit($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        return view('admin-module.manageUser.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, $id)
    {
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

        DB::table('admins')->where('id', $id)->update($data);
        return redirect()->route('admin.manageUser.index')->with('success','User updated successfully');
    }

    // Soft delete user
    public function destroy($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
        if (!$user) {
            return redirect()->route('admin.manageUser.index')->with('error', 'Invalid user selected.');
        }
        DB::table('admins')->where('id', $id)->update(['status' => 2]);
        return redirect()->route('admin.manageUser.index')->with('success', 'User deleted successfully.');
    }

    // ----------------------- Privileges -----------------------
    public function privileges($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
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

    // ----------------------- Assign Companies -----------------------
    public function assignCompanies($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();

        $merchants = DB::table('users')->where('status','1')->get()->map(function($merchant) {
            $merchant->company = DB::table('companies')->where('user_id', $merchant->id)->get();
            return $merchant;
        });

        $assigned = DB::table('assign_companies')->where('admin_users_id', $id)
                    ->pluck('comp_id')->toArray();

        return view('admin-module.manageUser.assign_companies', compact('user','merchants','assigned'));
    }

    public function storeAssignCompanies(Request $request)
    {
        $admin_user_id = $request->admin_user_id;
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
                         ->with('success', 'Companies assigned successfully.');
    }

    // ----------------------- Admin Panel Privileges -----------------------
    public function adminPrivileges($id)
    {
        $user = DB::table('admins')->where('id', $id)->first();
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
        $selected = $request->privileges ?? [];

        // Delete unchecked
        DB::table('admin_privileges_module_mappings')
            ->where('employee_id', $id)
            ->whereNotIn('module_id', $selected)
            ->delete();

        // Insert/update checked
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
