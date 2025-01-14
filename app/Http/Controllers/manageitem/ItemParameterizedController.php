<?php

namespace App\Http\Controllers\manageitem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemParameter;
use App\Models\ItemParameterList;
use App\Models\ItemParameterPredefinedValue;
use Session;
class ItemParameterizedController extends Controller{
   function index(){
      $parameter = ItemParameter::with(['parameters.predefinedValue'])->where('company_id',Session::get('user_company_id'))->first();
      return view('manageitem.item_parameterized_conf',['parameter'=>$parameter]);
   }
   function storeParameterizedConfiguration(Request $request){
      if(count($request['parameter_list'])==0){
         return redirect('parameterized-configuration')->withError('Please Mandatory Fields Required.');
      }      
      // echo "<pre>";
      // print_r($request->all());die;
      $check = ItemParameter::where('company_id',Session::get('user_company_id'))->first();
      if($check){
         $item_parameter = ItemParameter::find($request->id);
         $item_parameter->parameterized_status = $request['parameterized_status'];
         $item_parameter->no_of_parameter = $request['no_of_parameter'];
         $item_parameter->updated_by = Session::get('user_id');
         $item_parameter->updated_at = date('Y-m-d H:i:s');
         if($item_parameter->save()){
            ItemParameterList::where('company_id',Session::get('user_company_id'))->delete();
            ItemParameterPredefinedValue::where('super_parent_id',$request->id)->delete();
            if(count($request['parameter_list'])>0){
               foreach ($request['parameter_list'] as $key => $value){
                  $item_parameter_list = new ItemParameterList;
                  $item_parameter_list->parent_id = $item_parameter->id;
                  $item_parameter_list->paremeter_name = $value;
                  $item_parameter_list->parameter_type = $request['parameter_type_'.$key];
                  $item_parameter_list->company_id = Session::get('user_company_id');
                  if($item_parameter_list->save()){                     
                     if($request['parameter_type_'.$key]=="PREDEFINED"){
                        $defined_value = explode(",", $request['defined_value_'.$key]);
                        $defined_value_alias = explode(",", $request['defined_value_alias_'.$key]);
                        foreach ($defined_value as $k1 => $v1) {
                           $item_parameter_predefined_value = new ItemParameterPredefinedValue;
                           $item_parameter_predefined_value->super_parent_id = $item_parameter->id;
                           $item_parameter_predefined_value->predefined_value = $v1;
                           if(!empty($defined_value_alias[$k1]) && $defined_value_alias[$k1]!='null'){
                              $item_parameter_predefined_value->predefined_value_alias = $defined_value_alias[$k1];
                           }
                           $item_parameter_predefined_value->parent_id = $item_parameter_list->id;
                           $item_parameter_predefined_value->save();
                        }
                     }
                  }
               }
            }
         } 
      }else{
         $item_parameter = new ItemParameter;
         $item_parameter->parameterized_status = $request['parameterized_status'];
         $item_parameter->no_of_parameter = $request['no_of_parameter'];
         $item_parameter->company_id = Session::get('user_company_id');
         $item_parameter->created_by = Session::get('user_id');
         $item_parameter->created_at = date('Y-m-d H:i:s');
         if($item_parameter->save()){
            if(count($request['parameter_list'])>0){
               foreach ($request['parameter_list'] as $key => $value){
                  $item_parameter_list = new ItemParameterList;
                  $item_parameter_list->parent_id = $item_parameter->id;
                  $item_parameter_list->paremeter_name = $value;
                  $item_parameter_list->parameter_type = $request['parameter_type_'.$key];
                  $item_parameter_list->company_id = Session::get('user_company_id');
                  if($item_parameter_list->save()){
                     if($request['parameter_type_'.$key]=="PREDEFINED"){
                        $defined_value = explode(",", $request['defined_value_'.$key]);
                        $defined_value_alias = explode(",", $request['defined_value_alias_'.$key]);
                        foreach ($defined_value as $k1 => $v1) {
                           $item_parameter_predefined_value = new ItemParameterPredefinedValue;
                           $item_parameter_predefined_value->super_parent_id = $item_parameter->id;
                           $item_parameter_predefined_value->predefined_value = $v1;
                           if(!empty($defined_value_alias[$k1]) && $defined_value_alias[$k1]!='null'){
                              $item_parameter_predefined_value->predefined_value_alias = $defined_value_alias[$k1];
                           }
                           $item_parameter_predefined_value->parent_id = $item_parameter_list->id;
                           $item_parameter_predefined_value->save();
                        }
                     }
                  }
               }
            }
         } 
      }
      if($item_parameter->id) {
          return redirect('parameterized-configuration')->withSuccess('Parameter Updated Created successfully!');
      }else{
         $this->failedMessage();
      }    
   }
   public function failedMessage()
   {
       return redirect('parameterized-configuration')->withError('Something went wrong, please try again after some time.');
   }
}
