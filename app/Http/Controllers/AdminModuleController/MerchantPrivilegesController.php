<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivilegesModule;
use Carbon\Carbon;
class MerchantPrivilegesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $privileges = PrivilegesModule::with('parent')
                                        ->where('status',1)
                                        ->get();
        
        return view('admin-module.merchantPrivileges.index',["privileges"=>$privileges]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $privileges = PrivilegesModule::where('status',1)->orderBy('module_name')->get();
        return view('admin-module.merchantPrivileges.add',["privileges"=>$privileges]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "module_name"=>"required",
        ]);
        
        $privilege = new PrivilegesModule;
        $privilege->module_name = $request->module_name;
        $privilege->parent_id = $request->parent;
        $privilege->status = $request->status;
        $privilege->created_at = Carbon::now();
        if($privilege->save()){
            return redirect('admin/merchant-privilege')->withSuccess('Added Successfully!');
        }
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
    public function edit($id)
    {
        $privilege_for_edit = PrivilegesModule::find($id);
        $privileges = PrivilegesModule::where('status',1)->orderBy('module_name')->get();
        return view('admin-module.merchantPrivileges.edit',["privileges"=>$privileges,"privilege_for_edit"=>$privilege_for_edit]);
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
        $privilege = PrivilegesModule::find($id);
        $privilege->module_name = $request->module_name;
        $privilege->parent_id = $request->parent;
        $privilege->status = $request->status;
        $privilege->updated_at = Carbon::now();
        if($privilege->update()){
            return redirect('admin/merchant-privilege')->withSuccess('Updated Successfully!');
        }
        
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
}
