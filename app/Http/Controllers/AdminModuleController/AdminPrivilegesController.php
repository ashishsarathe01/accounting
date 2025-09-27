<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminPrivilegesModule;
use Carbon\Carbon;

class AdminPrivilegesController extends Controller
{
    public function index()
    {
        $privileges = AdminPrivilegesModule::with('parent')->get();
        return view('admin-module.adminPrivileges.index', compact('privileges'));
    }

    public function create()
    {
        $privileges = AdminPrivilegesModule::all();
        return view('admin-module.adminPrivileges.add', compact('privileges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            "module_name" => "required",
        ]);

        AdminPrivilegesModule::create([
            'module_name' => $request->module_name,
            'parent_id' => $request->parent,
            'status' => $request->status,
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.admin-privilege.index')->withSuccess('Added Successfully!');
    }

    public function edit($id)
    {
        $privilege_for_edit = AdminPrivilegesModule::find($id);
        $privileges = AdminPrivilegesModule::where('id', '!=', $id)->get();
        return view('admin-module.adminPrivileges.edit', compact('privileges','privilege_for_edit'));
    }

    public function update(Request $request, $id)
    {
        $privilege = AdminPrivilegesModule::find($id);
        $privilege->module_name = $request->module_name;
        $privilege->parent_id = $request->parent;
        $privilege->status = $request->status;
        $privilege->updated_at = Carbon::now();
        $privilege->save();

        return redirect()->route('admin.admin-privilege.index')->withSuccess('Updated Successfully!');
    }

    public function destroy($id)
    {
        $privilege = AdminPrivilegesModule::find($id);
        if ($privilege) {
            $privilege->delete();
            return redirect()->route('admin.admin-privilege.index')->withSuccess('Deleted Successfully!');
        }
        return redirect()->route('admin.admin-privilege.index')->withError('Privileges Not Found!');
    }
}
