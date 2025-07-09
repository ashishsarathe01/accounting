<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParameterInfo;
use App\Models\ParameterInfoValue;
use App\Models\ParameterInfoValueDetail;
use App\Models\ManageItems;
use App\Models\Companies;
use App\Models\GstBranch;
use DB;
use Session;
class ParameterizedStockController extends Controller
{
    public function index(Request $request){
        //Item List
        $items = ManageItems::select('manage_items.*')
                                ->where('company_id', Session::get('user_company_id'))
                                ->where('delete', '0')
                                ->where('status','1')
                                ->get();
        //Branch List
        $companyData = Companies::where('id', Session::get('user_company_id'))->first();      
        if($companyData->gst_config_type == "single_gst"){
            $series = DB::table('gst_settings')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                           ->get();
            $branch = GstBranch::select('id','branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series[0]->id])
                           ->get();
            if(count($branch)>0){
                $series = $series->merge($branch);
            }         
        }else if($companyData->gst_config_type == "multiple_gst"){
            $series = DB::table('gst_settings_multiple')
                           ->select('id','series')
                           ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                           ->get();
            foreach ($series as $key => $value) {
                $branch = GstBranch::select('id','branch_series as series')
                            ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                            ->get();
                if(count($branch)>0){
                $series = $series->merge($branch);
                }
            }         
        }
        //Get Stock
        $parameters = [];
        if(isset($request->item) && !empty($request->item)){
            $parameters = ParameterInfo::with('parameterColumnName:id,parent_id,paremeter_name','parameterColumnValues:id,parent_id,column_value')
                                        ->where('item_id',$request->item)
                                        ->select('id','purchase_desc_row_id','parameter_col_id','item_id')
                                        ->get();
        }
        
        return view('parameterized_stock',["items"=>$items,"series"=>$series,"parameters"=>$parameters]);
    }
}
