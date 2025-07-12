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
        $parameter_value = [];$parameter_header = [];$item_unit = [];
        if(isset($request->item) && !empty($request->item)){
            $item_unit = ManageItems::join('units', 'manage_items.u_name', '=','units.id')
                                ->select('s_name')
                                ->find($request->item);
            $parameter_header = DB::table('item_paremeter_list')
                            ->where('company_id',Session::get('user_company_id'))
                           ->where('status',1)
                           ->get();
            $parameter_value = DB::table('item_parameter_stocks')
                            ->leftjoin('item_paremeter_list as param1','item_parameter_stocks.parameter1_id','=','param1.id')
                            ->leftjoin('item_paremeter_list as param2','item_parameter_stocks.parameter2_id','=','param2.id')
                            ->leftjoin('item_paremeter_list as param3','item_parameter_stocks.parameter3_id','=','param3.id')
                            ->leftjoin('item_paremeter_list as param4','item_parameter_stocks.parameter4_id','=','param4.id')
                            ->leftjoin('item_paremeter_list as param5','item_parameter_stocks.parameter5_id','=','param5.id')
                           ->where('item_parameter_stocks.company_id',Session::get('user_company_id'))
                           ->select('parameter1_id','parameter2_id','parameter3_id','parameter4_id','parameter5_id','parameter1_value','parameter2_value','parameter3_value','parameter4_value','parameter5_value','param1.conversion_factor as conversion_factor1','param1.alternative_unit as alternative_unit1','param1.paremeter_name as paremeter_name1','param2.conversion_factor as conversion_factor2','param2.alternative_unit as alternative_unit2','param2.paremeter_name as paremeter_name2','param3.conversion_factor as conversion_factor3','param3.alternative_unit as alternative_unit3','param3.paremeter_name as paremeter_name3','param4.conversion_factor as conversion_factor4','param4.alternative_unit as alternative_unit4','param4.paremeter_name as paremeter_name4','param5.conversion_factor as conversion_factor5','param5.alternative_unit as alternative_unit5','param5.paremeter_name as paremeter_name5')
                           ->where('item_id',$request->item)
                           ->where('series_no',$request->series)
                           ->where('item_parameter_stocks.status',1)
                           ->get();
            // echo "<pre>";
            // print_r($parameter_value->toArray());
            // die;
        }        
        return view('parameterized_stock',["items"=>$items,"series"=>$series,"parameter_header"=>$parameter_header,"parameter_value"=>$parameter_value,"item_id"=>$request->item,"selected_series"=>$request->series,"to_date"=>$request->to_date,'item_unit'=>$item_unit]);
    }
}
