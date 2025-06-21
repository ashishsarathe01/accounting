<?php

namespace App\Http\Controllers\dashboard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Companies;
use App\Models\State;
use App\Models\Sales;
use App\Models\Purchase;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Journal;
use App\Models\Contra;
use Session;
use DB;
use App\Models\GstSetting;
use App\Models\GstBranch;
use Illuminate\Support\Facades\Validator;

class GstSettingController extends Controller
{
    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
   public function index(){
      $state_list = State::all();
      $id =  Session::get('user_company_id');
      $company_data = Companies::where('id', $id)->where('delete', '0')->get();
      $gstConfigData = DB::table('gst_settings')->where('company_id',$id)->get();
      if(!empty($company_data[0]->gst_config_type)){
         $GstType = $company_data[0]->gst_config_type;
         if($company_data[0]->gst_config_type=="single_gst"){
            $gstConfigData = DB::table('gst_settings')->where('company_id',$id)->get();
         }else if($company_data[0]->gst_config_type=="multiple_gst"){
            $gstConfigData = DB::table('gst_settings_multiple')->where('company_id',$id)->get();
         }
      }      
      foreach ($gstConfigData as $key => $value) {
         if(!empty($value->series)){
            $sale = Sales::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $purchase = Purchase::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $sales_return = SalesReturn::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $purchase_return = PurchaseReturn::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $payment = Payment::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $receipt = Receipt::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            $contra = Contra::select('id')
               ->where('company_id',Session::get('user_company_id'))
               ->where('series_no',$value->series)
               ->where('delete','0')
               ->where('status','1')
               ->first();
            if($sale || $purchase || $sales_return || $purchase_return || $payment || $receipt || $contra){
               $gstConfigData[$key]->readonly_status = 1;
            }else{
               $gstConfigData[$key]->readonly_status = 0;
            }
         }
         $gstConfigData[$key]->readonly_status = 0;
      }
      // echo "<pre>";
      // print_r($gstConfigData);
      // die;
      if($gstConfigData->count()>0){
         return view('dashboard/gstSetting_update')->with('state_list',$state_list)->with('gstConfigData',$gstConfigData)->with('GstType',$GstType)->with('fy',Session::get('default_fy'));
      }else{
         return view('dashboard/gstSetting')->with('state_list', $state_list)->with('company_data', $company_data)->with('fy',Session::get('default_fy'));
      }
   }

    /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request){      
      $validator = Validator::make($request->all(), [
         'gst_type' => 'required|string',
      ],[
         'gst_type.required' => 'Gst type is required.',
      ]);
      if($validator->fails()) {
         return response()->json($validator->errors(), 422);
      }
      $gst_type = $request->input('gst_type');
      // GST Settings Data
      $gst_no = $request->gst_no;
      $business_type = $request->business_type;
      $validity_from = $request->validity_from;
      //$validity_to = $request->validity_to;
      $address = $request->address;
      $state = $request->state;
      $pincode = $request->pincode;
      $scheme = $request->scheme;
      $return_filing_frequency = $request->return_filing_frequency;
      //$gst_certificate = $request->gst_certificate;
      //$invoice_start_from = $request->invoice_start_from;
      //$invoice_prefix = $request->invoice_prefix;
      $einvoice = $request->e_invoice;
      $einvoice_username = $request->einvoice_username;
      $gst_username = $request->gst_username;
      $einvoice_password = $request->einvoice_password;
      $ewaybill = $request->e_way_bill;
      $ewaybill_username = $request->ewaybill_username;
      $ewaybill_password = $request->ewaybill_password;
      $mat_center = $request->mat_center;
      $series = $request->series;
      // Branchs Data
      $branch_address = $request->branch_address;
      $branch_city = $request->branch_city;
      $branch_pincode = $request->branch_pincode;
      $branch_matcenter = $request->branch_matcenter;
      $branch_series = $request->branch_series;
      //$branch_invoice_start_from = $request->branch_invoice_start_from;
      //$branch_invoice_prefix = $request->branch_invoice_prefix;
      // Update Gst types in company table
      Companies::where('id',Session::get('user_company_id'))->update(['gst_config_type'=>$gst_type]);
      if($gst_type=="multiple_gst"){
         foreach($gst_no as $index=>$val){
            $btype = "";
            if(isset($business_type[$index]) && $business_type[$index][0]){
               $btype = $business_type[$index][0];
            }
            $gst_data= array(
               'company_id'=>Session::get('user_company_id'),
               'gst_type'=>$gst_type, 
               'gst_no'=>$val[0], 
               'business_type'=>$btype, 
               'validity_from'=>$validity_from[$index][0], 
               // 'validity_to'=>$validity_to[$index][0], 
               'address'=>$address[$index][0], 
               'state'=>$state[$index][0], 
               'pincode'=>$pincode[$index][0], 
               'scheme'=> $scheme[$index][0], 
               'return_filing_frequency'=> $return_filing_frequency[$index][0], 
               'gst_certificate'=>"N/A", 
               'mat_center'=>$mat_center[$index][0], 
               'series'=>$series[$index][0], 
               //'invoice_start_from'=>$invoice_start_from[$index][0], 
               //'invoice_prefix'=>$invoice_prefix[$index][0], 
               'einvoice'=>$einvoice[$index][0], 
               'einvoice_username'=>$einvoice_username[$index][0], 
               'gst_username'=>$gst_username[$index][0],
               'einvoice_password'=>encrypt($einvoice_password[$index][0]), 
               'ewaybill'=> $ewaybill[$index][0], 
               'ewaybill_username'=>$ewaybill_username[$index][0], 
               'ewaybill_password'=>encrypt($ewaybill_password[$index][0])
            );
            $gst_settings_id = DB::table('gst_settings_multiple')->insertGetId($gst_data);
            if($branch_address!='' && count($branch_address[$index])>0){
               foreach($branch_address[$index] as $indx=>$rows){
                  $branch = new GstBranch;
                  $branch->company_id = Session::get('user_company_id');
                  $branch->gst_setting_multiple_id = $gst_settings_id;
                  $branch->gst_number = $val[0];
                  $branch->branch_address = $rows;
                  $branch->branch_city = $branch_city[$index][$indx];
                  $branch->branch_pincode = $branch_pincode[$index][$indx];
                  $branch->branch_matcenter = $branch_matcenter[$index][$indx];
                  $branch->branch_series = $branch_series[$index][$indx];
                  //$branch->branch_invoice_start_from = $branch_invoice_start_from[$index][$indx];
                  //$branch->branch_invoice_prefix = $branch_invoice_prefix[$index][$indx];
                  $branch->status = '1';
                  $branch->save();
               }
            }
         }
      }else if($gst_type=="single_gst"){
         foreach($gst_no as $index=>$val){
            $btype = "";
            if(isset($business_type[$index]) && $business_type[$index][0]){
               $btype = $business_type[$index][0];
            }
            $gst_data = array(
               'company_id'=>Session::get('user_company_id'),
               'gst_type'=>$gst_type, 
               'gst_no'=>$val[0], 
               'business_type'=>$btype, 
               'validity_from'=>$validity_from[$index][0], 
               // 'validity_to'=>$validity_to[$index][0], 
               'address'=>$address[$index][0], 
               'state'=>$state[$index][0], 
               'pincode'=>$pincode[$index][0], 
               'scheme'=> $scheme[$index][0], 
               'return_filing_frequency'=> $return_filing_frequency[$index][0], 
               'gst_certificate'=>"N/A", 
               'mat_center'=>$mat_center[$index][0], 
               'series'=>$series[$index][0], 
               // 'invoice_start_from'=>$invoice_start_from[$index][0],
               //'invoice_prefix'=>$invoice_prefix[$index][0],
               'einvoice'=>$einvoice[$index][0], 
               'einvoice_username'=>$einvoice_username[$index][0], 
               'gst_username'=>$gst_username[$index][0],
               'einvoice_password'=>encrypt($einvoice_password[$index][0]), 
               'ewaybill'=> $ewaybill[$index][0], 
               'ewaybill_username'=>$ewaybill_username[$index][0], 
               'ewaybill_password'=>encrypt($ewaybill_password[$index][0])
            );
            $gst_settings_id = DB::table('gst_settings')->insertGetId($gst_data);
            if($branch_address!='' && count($branch_address[$index])>0){
               foreach($branch_address[$index] as $key=>$rows){
                  $branch = new GstBranch;
                  $branch->company_id = Session::get('user_company_id');
                  $branch->gst_setting_id = $gst_settings_id;
                  $branch->gst_number = $val[0];
                  $branch->branch_address = $rows;
                  $branch->branch_city = $branch_city[$index][$key];
                  $branch->branch_pincode = $branch_pincode[$index][$key];
                  $branch->branch_matcenter = $branch_matcenter[$index][$key];
                  $branch->branch_series = $branch_series[$index][$key];
                  // $branch->branch_invoice_start_from = $branch_invoice_start_from[$index][$key];
                  //$branch->branch_invoice_prefix = $branch_invoice_prefix[$index][$key];
                  $branch->status = '1';
                  $branch->save();
               }
            }
         }
      }
      return redirect('gst-setting')->withSuccess('Gst setting added successfully!');
   }

    public function edit($id)
    {

        $editunit = Units::find($id);
        return view('editAccountUnit')->with('editunit', $editunit);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AccountGroups  $fooditem
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request){
      // echo "<pre>";
      // print_r($request->all());die;
      $gst_type = $request->input('gst_type');
      // GST Settings Data
      $gst_no = $request->gst_no;
      $business_type = $request->business_type;
      $validity_from = $request->validity_from;
      // $validity_to = $request->validity_to;
      $address = $request->address;
      $state = $request->state;
      $pincode = $request->pincode;
      $scheme = $request->scheme;
      $return_filing_frequency = $request->return_filing_frequency;
      //$gst_certificate = $request->gst_certificate;
      // $invoice_start_from = $request->invoice_start_from;
      //$invoice_prefix = $request->invoice_prefix;
      $einvoice = $request->e_invoice;
      $einvoice_username = $request->einvoice_username;
      $gst_username = $request->gst_username;
      $einvoice_password = $request->einvoice_password;
      $ewaybill = $request->e_way_bill;
      $ewaybill_username = $request->ewaybill_username;
      $ewaybill_password = $request->ewaybill_password;
      $mat_center = $request->mat_center;
      $series = $request->series;
      // Branchs Data
      $branch_address = $request->branch_address;
      $branch_city = $request->branch_city;
      $branch_pincode = $request->branch_pincode;
      $branch_matcenter = $request->branch_matcenter;
      $branch_series = $request->branch_series;
      // $branch_invoice_start_from = $request->branch_invoice_start_from;
      //$branch_invoice_prefix = $request->branch_invoice_prefix;
      // Update Gst types in company table
      
      GstSetting::where('company_id',Session::get('user_company_id'))->delete();
      DB::table('gst_settings_multiple')->where('company_id',Session::get('user_company_id'))->delete();
      GstBranch::where('company_id',Session::get('user_company_id'))->delete();
      Companies::where('id',Session::get('user_company_id'))->update(['gst_config_type'=>$gst_type]);
      if($gst_type=="multiple_gst"){
         foreach($gst_no as $index=>$val){
            $einvoice_username1 = "";
            $einvoice_password1 = "";
            $ewaybill_username1 = "";
            $ewaybill_password1 = "";
            
            if($einvoice[$index][0]==1 && isset($einvoice_username[$index][0]) && isset($einvoice_password[$index][0])){
               
               $einvoice_username1 = $einvoice_username[$index][0];
               $einvoice_password1 = encrypt($einvoice_password[$index][0]);
            }
            if($ewaybill[$index][0]==1 && isset($ewaybill_username[$index][0]) && isset($ewaybill_password[$index][0])){
               $ewaybill_username1 = $ewaybill_username[$index][0];
               $ewaybill_password1 = encrypt($ewaybill_password[$index][0]);
            }
            if(!empty($val[0]) && $mat_center[$index][0] && $series[$index][0]){
               $btype = "";
               if(isset($business_type[$index]) && $business_type[$index][0]){
                  $btype = $business_type[$index][0];
               }

               $gst_data= array(
                  'company_id'=>Session::get('user_company_id'),
                  'gst_type'=>$gst_type, 
                  'gst_no'=>$val[0], 
                  'business_type'=>$btype, 
                  'validity_from'=>$validity_from[$index][0], 
                  // 'validity_to'=>$validity_to[$index][0], 
                  'address'=>$address[$index][0], 
                  'state'=>$state[$index][0], 
                  'pincode'=>$pincode[$index][0], 
                  'scheme'=> $scheme[$index][0], 
                  'return_filing_frequency'=> $return_filing_frequency[$index][0], 
                  'gst_certificate'=>"N/A", 
                  'mat_center'=>$mat_center[$index][0], 
                  'series'=>$series[$index][0], 
                  // 'invoice_start_from'=>$invoice_start_from[$index][0], 
                  //'invoice_prefix'=>$invoice_prefix[$index][0], 
                  'einvoice'=>$einvoice[$index][0], 
                  'einvoice_username'=>$einvoice_username1, 
                  'gst_username'=>$gst_username[$index][0],
                  'einvoice_password'=>$einvoice_password1, 
                  'ewaybill'=> $ewaybill[$index][0], 
                  'ewaybill_username'=>$ewaybill_username1, 
                  'ewaybill_password'=>$ewaybill_password1
               );
               
               $gst_settings_id = DB::table('gst_settings_multiple')->insertGetId($gst_data);
               if($branch_address!='' && isset($branch_address[$index]) && count($branch_address[$index])>0){
                  foreach($branch_address[$index] as $indx=>$rows){
                     if(!empty($branch_pincode[$index][$indx]) && !empty($branch_matcenter[$index][$indx]) && !empty($branch_series[$index][$indx])){
                        $branch = new GstBranch;
                        $branch->company_id = Session::get('user_company_id');
                        $branch->gst_setting_multiple_id = $gst_settings_id;
                        $branch->gst_number = $val[0];
                        $branch->branch_address = $rows;
                        $branch->branch_city = $branch_city[$index][$indx];
                        $branch->branch_pincode = $branch_pincode[$index][$indx];
                        $branch->branch_matcenter = $branch_matcenter[$index][$indx];
                        $branch->branch_series = $branch_series[$index][$indx];
                        // $branch->branch_invoice_start_from = $branch_invoice_start_from[$index][$indx];
                        //$branch->branch_invoice_prefix = $branch_invoice_prefix[$index][$indx];
                        $branch->status = '1';
                        $branch->save();
                     }
                     
                  }
               }
            }
            
         }
      }else if($gst_type=="single_gst"){
         foreach($gst_no as $index=>$val){
            $einvoice_username1 = "";
            $einvoice_password1 = "";
            $ewaybill_username1 = "";
            $ewaybill_password1 = "";
            if($einvoice[$index][0]==1){
               $einvoice_username1 = $einvoice_username[$index][0];
               $einvoice_password1 = encrypt($einvoice_password[$index][0]);
            }
            if($ewaybill[$index][0]==1){
               $ewaybill_username1 = $ewaybill_username[$index][0];
               $ewaybill_password1 = encrypt($ewaybill_password[$index][0]);
            }
            $btype = "";
            if(isset($business_type[$index]) && $business_type[$index][0]){
               $btype = $business_type[$index][0];
            }
            $gst_data = array(
               'company_id'=>Session::get('user_company_id'),
               'gst_type'=>$gst_type, 
               'gst_no'=>$val[0], 
               'business_type'=>$btype, 
               'validity_from'=>$validity_from[$index][0], 
               // 'validity_to'=>$validity_to[$index][0], 
               'address'=>$address[$index][0], 
               'state'=>$state[$index][0], 
               'pincode'=>$pincode[$index][0], 
               'scheme'=> $scheme[$index][0],
               'return_filing_frequency'=> $return_filing_frequency[$index][0], 
               'gst_certificate'=>"N/A", 
               'mat_center'=>$mat_center[$index][0], 
               'series'=>$series[$index][0], 
               // 'invoice_start_from'=>$invoice_start_from[$index][0],
               //'invoice_prefix'=>$invoice_prefix[$index][0],
               'einvoice'=>$einvoice[$index][0], 
               'einvoice_username'=>$einvoice_username1, 
               'gst_username'=>$gst_username[$index][0],
               'einvoice_password'=>$einvoice_password1, 
               'ewaybill'=> $ewaybill[$index][0], 
               'ewaybill_username'=>$ewaybill_username1, 
               'ewaybill_password'=>$ewaybill_password1
            );
            $gst_settings_id = DB::table('gst_settings')->insertGetId($gst_data);
            if($branch_address!='' && count($branch_address[$index])>0){
               foreach($branch_address[$index] as $key=>$rows){
                  if(!empty($branch_pincode[$index][$key]) && !empty($branch_matcenter[$index][$key]) && !empty($branch_series[$index][$key])){
                     $branch = new GstBranch;
                     $branch->company_id = Session::get('user_company_id');
                     $branch->gst_setting_id = $gst_settings_id;
                     $branch->gst_number = $val[0];
                     $branch->branch_address = $rows;
                     $branch->branch_city = $branch_city[$index][$key];
                     $branch->branch_pincode = $branch_pincode[$index][$key];
                     $branch->branch_matcenter = $branch_matcenter[$index][$key];
                     $branch->branch_series = $branch_series[$index][$key];
                     // $branch->branch_invoice_start_from = $branch_invoice_start_from[$index][$key];
                     //$branch->branch_invoice_prefix = $branch_invoice_prefix[$index][$key];
                     $branch->status = '1';
                     $branch->save();
                  }
               }
            }
         }
      }
      return redirect('gst-setting')->withSuccess('Gst setting added successfully!');
   }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFare $groupfare
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $unit =  Units::find($request->unit_id);
        $unit->delete = '1';
        $unit->deleted_at = Carbon::now();
        $unit->update();
        if ($unit) {
            return redirect('account-unit')->withSuccess('Account unit deleted successfully!');
        }
    }

    /**
     * Generates failed response and message.
     */
    public function failedMessage()
    {
        return redirect('account-unit')->withError('Something went wrong, please try again after some time.');
    }
}
