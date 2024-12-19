<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use Auth;

class ModuleController extends Controller
{
    public function moduleList(Request $request)
    {
        $modules = DB::table('modules')->get();
        if ($modules) 
        {
            return response()->json([
                'code' => 200,
                'data' => $modules,
                'dataCount' => $modules->count(),
            ]);
        } 
        else 
        {
            $this->failedMessage();
        }
    }


    public function getAssignedModules(Request $request)
    {
        $user_id = Auth::user()->id;
        
        $allModules = DB::table('modules')->where('status',1)->get();
        $modules = DB::table('user_privilege_mapping')->where('user_privilege_mapping.user_id',$user_id)->get();
        if($modules->count()>0)
        {
             $user_modules = DB::select('SELECT module_mapping.*,(select module_name from modules where modules.id=module_mapping.module_id) as module_name  FROM `module_mapping` WHERE id IN('.$modules[0]->module_map_id.')');

             if ($user_modules) 
             {
               return response()->json([
                'code' => 200,
                'data' => array('Modules'=>$allModules,'AssignedModules'=>$user_modules),
                'dataCount' => count($user_modules),
                ]);
             } 
             else 
             {
                 return response()->json(['code' => 422, 'message' => 'Something went wrong, please try after some time!']);
             }
    
        }
         else 
         {
             return response()->json(['code' => 422, 'message' => 'User has not assigned any modules yet.']);
         }
    
    
    }

    public function assignModule(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'module_mapping_id' => 'required'
        ], [
            'module_mapping_id.required' => 'Module mapping id is required.'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $module_mapping = $request->module_mapping_id;
        if(is_array($module_mapping))
        {
            $mapping_data = implode(",", $module_mapping);

            $userDatacount = DB::table('user_privilege_mapping')->where('user_id',Auth::user()->id)->count();
            if($userDatacount>0)
            {
                $update = DB::table('user_privilege_mapping')->where('user_id',Auth::user()->id)->update(['module_map_id'=>$mapping_data]);
            }
            else
            {
                $insert = DB::table('user_privilege_mapping')->insert(['user_id'=>Auth::user()->id,'module_map_id'=>$mapping_data]);
            }

            return response()->json([
                'code' => 200,
                'data' => array('module_mapping_id'=>$mapping_data),
                'message'=>'User privilege updated successfully.'
                ]);    
        }
        else
        {
             return response()->json(['code' => 422, 'message' => 'Data type not accepted, module_mapping_id accept only array values.']);
        }

       


    }


    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return response()->json([
            'code' => 422,
            'message' => 'Something went wrong, please try again after some time.',
        ]);
    }
}
