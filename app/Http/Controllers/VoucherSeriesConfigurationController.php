<?php

namespace App\Http\Controllers;
use App\Models\VoucherSeriesConfiguration;
use App\Models\Companies;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use DB;
use App\Models\GstBranch;
use App\Models\Sales;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\StockTransfer;
class VoucherSeriesConfigurationController extends Controller
{
    public function index()
    {
        $companyData = Companies::where('id', Session::get('user_company_id'))->first();
        if(!$companyData){
            return redirect('dashboard')->withSuccess('Please Add Company Details First!');
        }
        $series_list = [];
        if($companyData->gst_config_type == "single_gst"){
            $series_list = DB::table('gst_settings')
                              ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])
                              ->get();
            $branch = GstBranch::select('branch_series as series')
                              ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_id'=>$series_list[0]->id])
                              ->get();
            if(count($branch)>0){
               $series_list = $series_list->merge($branch);
            }            
         }else if($companyData->gst_config_type == "multiple_gst"){
            $series_list = DB::table('gst_settings_multiple')
                              ->select('id','series')
                              ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])
                              ->get();
            foreach ($series_list as $key => $value) {
               $branch = GstBranch::select('branch_series as series')
                           ->where(['delete' => '0', 'company_id' => Session::get('user_company_id'),'gst_setting_multiple_id'=>$value->id])
                           ->get();
               if(count($branch)>0){
                  $series_list = $series_list->merge($branch);
               }
            }         
        }
        return view('voucher_series_configuration.index',['series_list'=>$series_list]);
    }
    public function addSeriesConfiguration(Request $request)
    {   
        VoucherSeriesConfiguration::where('company_id',Session::get('user_company_id'))
                                    ->where('series',$request->series)
                                    ->where('configuration_for',$request->configuration_for)
                                    ->delete();
        $configuration = new VoucherSeriesConfiguration();
        $configuration->configuration_for = $request->configuration_for;
        $configuration->series = $request->series;
        $configuration->manual_numbering = $request->manual_numbering;
        if($request->manual_numbering=="YES"){
            $configuration->duplicate_voucher = $request->duplicate_voucher;
            $configuration->blank_voucher = $request->blank_voucher;
        }else if($request->manual_numbering=="NO"){
            $configuration->prefix = $request->prefix;
            $configuration->prefix_value = $request->prefix_value;
            $configuration->year = $request->year;
            $configuration->year_format = $request->year_format;
            $configuration->suffix = $request->suffix;
            $configuration->suffix_value = $request->suffix_value;
            $configuration->number_digit = $request->number_digit;
            $configuration->separator_1 = $request->separator_1;
            $configuration->separator_2 = $request->separator_2;
            $configuration->separator_3 = $request->separator_3;
            $configuration->invoice_start = $request->invoice_start;
            $configuration->max_invoice = $request->max_invoice;
        }
        $configuration->company_id = Session::get('user_company_id');        
        $configuration->created_at = Carbon::now();
        if($configuration->save()){
            return redirect('voucher-series-configuration')->withSuccess('Series Configuration Added Successfully!');
        }else{
            return $this->failedMessage('Something went wrong','voucher-series-configuration');
        }
    }


    
    public function seriesConfigurationBySeries(Request $request)
    {  
        $series = $request->series;
        if($request->configuration_for=="SALE"){
            $sale = Sales::select('id')
                            ->where('series_no',$series)                           
                            ->where('status','1')
                            ->where('delete','0')
                            ->where('company_id',Session::get('user_company_id'))
                            ->first();
        }else if($request->configuration_for=="DEBIT NOTE"){
            $sale = PurchaseReturn::select('id')
                            ->where('series_no',$series)                           
                            ->where('status','1')
                            ->where('delete','0')
                            ->where('company_id',Session::get('user_company_id'))
                            ->first();
        }else if($request->configuration_for=="CREDIT NOTE"){
            $sale = SalesReturn::select('id')
                            ->where('series_no',$series)                           
                            ->where('status','1')
                            ->where('delete','0')
                            ->where('company_id',Session::get('user_company_id'))
                            ->first();
        }else if($request->configuration_for=="STOCK TRANSFER"){
            $sale = StockTransfer::select('id')
                            ->where('series_no',$series)                           
                            ->where('status','1')
                            ->where('delete_status','0')
                            ->where('company_id',Session::get('user_company_id'))
                            ->first();
        }            
        $configuration = VoucherSeriesConfiguration::where('series',$series)
                                                    ->where('configuration_for',$request->configuration_for)
                                                    ->where('company_id',Session::get('user_company_id'))
                                                ->first();
        $update_status = 1;
        if($sale){
            $update_status = 0;
        }
        if($configuration){
            $data = [
                'configuration' => $configuration,
                'update_status'=>$update_status,
                'status' => 'success'
            ];
        }else{
            $data = [
                'configuration' => $configuration,
                'status' => 'failed'
            ];
        }        
        return response()->json($data);
    }
}
