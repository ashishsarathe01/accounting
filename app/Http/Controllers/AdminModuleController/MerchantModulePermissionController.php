<?php

namespace App\Http\Controllers\AdminModuleController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MerchantModule;
use App\Models\MerchantModuleMapping;
use Carbon\Carbon;
class MerchantModulePermissionController extends Controller
{
    public function index($id=null){
        $merchants = User::where('type','OWNER')->where('delete_status','0')->where('status','1')->orderBy('name')->get();
        $modules = MerchantModule::where('status',1)->orderBy('name')->get();
        $selected_modules = MerchantModuleMapping::where('merchant_id',$id)->pluck('module_id')->toArray();
        return view('admin-module.merchant.module_permission',['merchants'=>$merchants,'id'=>$id,"modules"=>$modules,"selected_modules"=>$selected_modules]);
    }

    public function storeMerchantModule(Request $request){
        $request->validate([
            'merchant_id'=>'required',
            'modules'=>'required|array'
        ]);
        $merchant = User::where('id',$request->merchant_id)->where('type','OWNER')->where('delete_status','0')->first();
        if(!$merchant){
            return redirect()->back()->with('error','Merchant not found');
        }
        
        //delete old permissions
        MerchantModuleMapping::where('merchant_id',$request->merchant_id)->delete();
        foreach($request->modules as $module_id){
            $module = MerchantModule::where('id',$module_id)->first();
            if($module){
                $newModule = new MerchantModuleMapping();
                $newModule->merchant_id = $request->merchant_id;
                $newModule->module_id = $module->id;
                $newModule->created_at = Carbon::now();
                $newModule->save();
            }
        }
        return redirect()->back()->with('success','Modules permissions updated successfully');
    }
}
