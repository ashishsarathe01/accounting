<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MerchantModule;
use App\Models\MerchantModuleMapping;
use App\Models\Companies;
use Carbon\Carbon;
class MerchantModulePermissionController extends Controller
{
    public function index($merchant_id=null,$company_id=null){
        $modules = MerchantModule::where('status',1)
                                    ->orderBy('name')
                                    ->get();        
        $company_list = Companies::select('id','company_name','gst')
                                ->where('user_id',$merchant_id)
                                ->get();
        if($company_id==null && count($company_list)>0){
            $company_id = $company_list[0]->id;
        }
        $selected_modules = MerchantModuleMapping::where('merchant_id',$merchant_id)
                                                ->where('company_id',$company_id)
                                                ->pluck('module_id')
                                                ->toArray();
        return view('admin-module.merchant.module_permission',[
            "modules"=>$modules,
            "selected_modules"=>$selected_modules,
            "company_list"=>$company_list,
            "merchant_id"=>$merchant_id,
            "company_id"=>$company_id,
            ]);
    }

    public function storeMerchantModule(Request $request){
        $request->validate([
            'company_id'=>'required',
            'modules'=>'required|array'
        ]);
        // echo "<pre>";
        // print_r($request->all());die;        
        //delete old permissions
        MerchantModuleMapping::where('company_id',$request->company_id)->delete();
        foreach($request->modules as $module_id){
            $module = MerchantModule::where('id',$module_id)->first();
            if($module){
                $newModule = new MerchantModuleMapping();
                $newModule->merchant_id = $request->merchant_id;
                $newModule->company_id = $request->company_id;
                $newModule->module_id = $module->id;
                $newModule->created_at = Carbon::now();
                $newModule->save();
            }
        }
        return redirect()->back()->with('success','Modules permissions updated successfully');
    }
}
