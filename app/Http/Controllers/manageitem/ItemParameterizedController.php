<?php

namespace App\Http\Controllers\manageitem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemParameter;
use App\Models\ItemParameterList;
use Session;
class ItemParameterizedController extends Controller{
   function index(){
      return view('manageitem.item_parameterized_conf');
   }
   function storeParameterizedConfiguration(Request $request){
      if(count($request['parameter_list'])==0){
         return;
      }

      DB::table('users')
                  ->updateOrInsert(
                      ['email' => 'john@example.com', 'name' => 'John'],
                      ['votes' => '2']
                  );
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
               $item_parameter_list->company_id = Session::get('user_company_id');
               $item_parameter_list->save();
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
       return redirect('account-group')->withError('Something went wrong, please try again after some time.');
   }
}
