<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesReturn;
use App\Models\Purchase;
use App\Models\SaleReturnDescription;
use App\Models\SaleReturnSundry;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\Models\Sales;
use App\Models\SaleDescription;
use App\Models\SaleSundry;
use App\Models\Accounts;
use App\Models\ManageItems;
use App\Models\BillSundrys;
use App\Models\Companies;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\AccountGroups;
use App\Models\VoucherSeriesConfiguration;
use App\Models\SaleInvoiceConfiguration;
use App\Models\Bank;
use App\Models\State;
use App\Models\SaleInvoiceTermCondition;
use App\Models\SaleReturnWithoutGstEntry;
use App\Models\ItemAverageDetail;
use App\Models\ItemSizeStock;
use App\Models\SaleReturnParameterInfo;
use App\Models\ItemParameterStock;
use App\Models\MerchantModuleMapping;
use App\Helpers\CommonHelper;
use App\Models\ActivityLog;
use App\Models\EinvoiceToken;
use App\Models\GstBranch;
use Session;
use DateTime;
use Gate;

class CreditNoteController extends Controller
{
    public function index(Request $request)
{
    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;
    $from_date      = $request->from_date;
    $to_date        = $request->to_date;

    if (!$company_id || !$financial_year) {
        return response()->json([
            'status'  => false,
            'message' => 'company_id and financial_year are required'
        ], 400);
    }

    /*
    |--------------------------------------------------------------------------
    | Financial Year Processing
    |--------------------------------------------------------------------------
    */
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04', $from.'-05', $from.'-06', $from.'-07',
        $from.'-08', $from.'-09', $from.'-10', $from.'-11',
        $from.'-12', $to.'-01', $to.'-02', $to.'-03'
    ];

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */
    $query = DB::table('sales_returns')
        ->select(
            'sales_returns.id as sales_return_id',
            'sales_returns.sr_prefix',
            'sales_returns.sr_nature',
            'sales_returns.sr_type',
            'sales_returns.date',
            'sales_returns.series_no',
            'sales_returns.financial_year',
            'sales_returns.invoice_no',
            'sales_returns.sale_return_no',
            'sales_returns.total',
            'sales_returns.e_invoice_status',
            DB::raw('(select account_name from accounts where accounts.id = sales_returns.party limit 1) as account_name'),
            DB::raw('(select manual_numbering 
                      from voucher_series_configurations 
                      where voucher_series_configurations.company_id = '.$company_id.' 
                      and configuration_for="CREDIT NOTE" 
                      and voucher_series_configurations.status=1 
                      and voucher_series_configurations.series = sales_returns.series_no 
                      limit 1) as manual_numbering_status'),
            DB::raw('(select max(sale_return_no) 
                      from sales_returns as s 
                      where s.company_id = '.$company_id.' 
                      and s.delete="0" 
                      and s.series_no = sales_returns.series_no) as max_voucher_no')
        )
        ->where('sales_returns.company_id', $company_id)
        ->where('sales_returns.delete', '0');

    /*
    |--------------------------------------------------------------------------
    | Date Filtering
    |--------------------------------------------------------------------------
    */
    if (!empty($from_date) && !empty($to_date)) {

        $query->whereBetween('sales_returns.date', [
            date('Y-m-d', strtotime($from_date)),
            date('Y-m-d', strtotime($to_date))
        ]);

        $query->orderBy(DB::raw("CAST(sale_return_no AS SIGNED)"), 'ASC')
              ->orderBy('sales_returns.created_at', 'ASC');

    } else {

        $query->orderBy('financial_year', 'DESC')
              ->orderBy(DB::raw("CAST(sale_return_no AS SIGNED)"), 'DESC')
              ->orderBy('sales_returns.created_at', 'DESC')
              ->orderBy('sales_returns.id', 'DESC')
              ->limit(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Execute Query
    |--------------------------------------------------------------------------
    */
    $salesReturn = $query->get();

    if (empty($from_date) && empty($to_date)) {
        $salesReturn = $salesReturn->reverse()->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Return JSON Response
    |--------------------------------------------------------------------------
    */
    return response()->json([
        'code'        => 200,
        'message'       => 'Sales Return list fetched successfully',
        'month_arr'     => $month_arr,
        'from_date'     => $from_date,
        'to_date'       => $to_date,
        'total_records' => $salesReturn->count(),
        'data'          => $salesReturn
    ]);
}


public function saleReturnInvoicePdf(Request $request)
{
    $sale_return_id = $request->sale_return_id;
    $company_id     = $request->company_id;
    $financial_year = $request->financial_year;

    if (!$sale_return_id || !$company_id) {
        return response()->json([
            'code' => 400,
            'message' => 'sale_return_id and company_id required'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Company Data
    |--------------------------------------------------------------------------
    */
    $company_data = Companies::join('states','companies.state','=','states.id')
        ->where('companies.id', $company_id)
        ->select(['companies.*','states.name as sname'])
        ->first();
        
        $sale_return_nature = SalesReturn::where('id', $sale_return_id)
                                ->select('sr_nature','sr_type')
                                ->first();


    /*
    |--------------------------------------------------------------------------
    | Sale Return Main
    |--------------------------------------------------------------------------
    */
    if($sale_return_nature->sr_nature == "WITH GST" && ($sale_return_nature->sr_type == "WITH ITEM" || $sale_return_nature->sr_type == "RATE DIFFERENCE")){
    $sale_return = SalesReturn::leftjoin('accounts','sales_returns.party','=','accounts.id')
                                 ->leftjoin('states','sales_returns.billing_state','=','states.id')
                                 ->where('sales_returns.id',$sale_return_id)
                                 ->select(['sales_returns.e_invoice_status','sales_returns.einvoice_response','sales_returns.date','sales_returns.id','sales_returns.invoice_no','sales_returns.remark as narration','sales_returns.merchant_gst','sales_returns.total','sales_returns.remark as narration','states.name as sname','sale_return_no','sales_returns.vehicle_no','sales_returns.billing_gst','sales_returns.gr_pr_no','sales_returns.transport_name','sales_returns.station','sales_returns.series_no','sales_returns.financial_year as dr_financial_year','sr_nature','sr_type','sr_prefix','accounts.address as party_address','accounts.print_name as billing_name','original_invoice_date'])
                                 ->first();
      $items_detail = DB::table('sale_return_descriptions')
                           ->where('sale_return_descriptions.sale_return_id', $sale_return_id)
                           ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
                           ->where('sales_returns.sr_type', 'WITH ITEM')
                           ->select(
                              'units.s_name as unit',
                              'units.id as unit_id',
                              'sale_return_descriptions.qty',
                              'sale_return_descriptions.price',
                              'sale_return_descriptions.amount',
                              'manage_items.name as items_name',
                              'manage_items.id as item_id',
                              'manage_items.hsn_code',
                              'manage_items.gst_rate'
                           )
                           ->leftjoin('units', 'sale_return_descriptions.unit', '=', 'units.id')
                           ->leftjoin('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
                           ->get();
      $items_detail1 = DB::table('sale_return_descriptions')
                              ->where('sale_return_descriptions.sale_return_id', $sale_return_id)
                              ->join('sales_returns', 'sale_return_descriptions.sale_return_id', '=', 'sales_returns.id')
                              ->where('sales_returns.sr_type', 'RATE DIFFERENCE')
                              ->select(
                                 DB::raw("''  as unit"),
                                 'units.id as unit_id',
                                 DB::raw("'' as qty"),
                                 DB::raw("'' as price"),
                                 'sale_return_descriptions.amount',
                                 'manage_items.name as items_name',
                                 'manage_items.id as item_id',
                                 'manage_items.hsn_code',
                                 'manage_items.gst_rate'
                              )
                              ->leftjoin('units', 'sale_return_descriptions.unit', '=', 'units.id')
                              ->join('manage_items', 'sale_return_descriptions.goods_discription', '=', 'manage_items.id')
                              ->get();    
      // Merge both collections
      $items_detail = $items_detail->merge($items_detail1);     
      $sale_sundry = DB::table('sale_return_sundries')
                        ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
                        ->where('sale_return_id', $sale_return_id)
                        ->select('sale_return_sundries.bill_sundry','sale_return_sundries.rate','sale_return_sundries.amount','bill_sundrys.name')
                        ->orderBy('sequence')
                        ->get();
      $gst_detail = DB::table('sale_return_sundries')
                        ->select('rate','amount')                     
                        ->where('sale_return_id', $sale_return_id)
                        ->where('rate','!=','0')
                        ->distinct('rate')                       
                        ->get(); 
      $max_gst = DB::table('sale_return_sundries')
                        ->select('rate')                     
                        ->where('sale_return_id', $sale_return_id)
                        ->where('rate','!=','0')
                        ->max(\DB::raw("cast(rate as SIGNED)"));
      if(count($gst_detail)>0){
         foreach ($gst_detail as $key => $value){
            $rate = $value->rate;      
            if(substr($company_data->gst,0,2)==substr($sale_return->billing_gst,0,2)){
               $rate = $rate*2;
               $max_gst = $max_gst*2;
            }
            $taxable_amount = 0;
            foreach($items_detail as $k1 => $item) {
               if($item->gst_rate==$rate){
                  $taxable_amount = $taxable_amount + $item->amount;
               }
            }
            $gst_detail[$key]->rate = $rate;
            if($max_gst==$rate){

               $freight = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $sale_return_id)
                           ->where('bill_sundry',4)
                           ->first();
               $insurance = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $sale_return_id)
                           ->where('bill_sundry',7)
                           ->first();
               $discount = SaleReturnSundry::select('amount')
                           ->where('sale_return_id', $sale_return_id)
                           ->where('bill_sundry',5)
                           ->first();
               if($freight && !empty($freight->amount)){
                  $taxable_amount = $taxable_amount + $freight->amount;
               }
               if($insurance && !empty($insurance->amount)){
                  $taxable_amount = $taxable_amount + $insurance->amount;
               }
               if($discount && !empty($discount->amount)){
                  $taxable_amount = $taxable_amount - $discount->amount;
               }
            }
            $gst_detail[$key]->taxable_amount = $taxable_amount;
         }
      }
      if($company_data->gst_config_type == "single_gst") {
         $GstSettings = DB::table('gst_settings')->where(['company_id' => $company_id, 'gst_type' => "single_gst"])->first();
         //Seller Info
         // echo "<pre>";
         // print_r($GstSettings);die;
         $seller_info = DB::table('gst_settings')
                           ->join('states','gst_settings.state','=','states.id')
                           ->where(['company_id' => $company_id, 'gst_type' => "single_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
         if(!$seller_info){
            $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => $company_id,'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
            $state_info = DB::table('states')
                           ->where('id',$GstSettings->state)
                           ->first();
            $seller_info->sname = $state_info->name;
         }
      }else if($company_data->gst_config_type == "multiple_gst") {    
         if($sale_return->voucher_type=="PURCHASE"){
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $company_id, 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])
                           ->first();
                     //Seller Info         
            $seller_info = DB::table('gst_settings_multiple')
                           ->join('states','gst_settings_multiple.state','=','states.id')
                           ->where(['company_id' => $company_id, 'gst_type' => "multiple_gst",'series'=>$sale_return->series_no])
                           ->select(['gst_no','address','pincode','states.name as sname'])
                           ->first();
                        
            if(!$seller_info){
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => $company_id,'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
            } 
         }else{
            $GstSettings = DB::table('gst_settings_multiple')
                           ->where(['company_id' => $company_id, 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst])
                           ->first();
            //Seller Info         
                  $seller_info = DB::table('gst_settings_multiple')
                  ->join('states','gst_settings_multiple.state','=','states.id')
                  ->where(['company_id' => $company_id, 'gst_type' => "multiple_gst",'gst_no' => $sale_return->merchant_gst,'series'=>$sale_return->series_no])
                  ->select(['gst_no','address','pincode','states.name as sname'])
                  ->first();
               
               if(!$seller_info){                  
                  $seller_info = GstBranch::select('gst_number as gst_no','branch_address as address','branch_pincode as pincode')
                           ->where(['delete' => '0', 'company_id' => $company_id,'gst_number'=>$sale_return->merchant_gst,'branch_series'=>$sale_return->series_no])
                           ->first();
                  $state_info = DB::table('states')
                                 ->where('id',$GstSettings->state)
                                 ->first();
                  $seller_info->sname = $state_info->name;                          
               } 
         }      
      }
      Session::put('redirect_url','');
      $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04', $from.'-05', $from.'-06', $from.'-07',
        $from.'-08', $from.'-09', $from.'-10', $from.'-11',
        $from.'-12', $to.'-01', $to.'-02', $to.'-03'
    ];
      if($GstSettings){
         // if(substr($sale_detail->merchant_gst,0,2)==substr($sale_detail->billing_gst,0,2)){
         //    if($sale_detail->total<100000){
         //       $GstSettings->ewaybill = 0;
         //    }
         // }else{
         //    if($sale_detail->total<50000){
         //       $GstSettings->ewaybill = 0;
         //    }
         // }
      }else{
         $GstSettings = (object)NULL;
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
      }
      if($sale_return->voucher_type!="SALE"){
         $GstSettings->ewaybill = 0;
         $GstSettings->einvoice = 0;
      }
      $configuration = SaleInvoiceConfiguration::with(['terms','banks'])->where('company_id',$company_id)->first();
    /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */
    $pdf = \PDF::loadView('saleReturnInvoicePdf', [
        'items_detail' => $items_detail,
        'sale_sundry'  => $sale_sundry,
        'company_data' => $company_data,
        'gst_detail'   => $gst_detail,
        'sale_return'  => $sale_return,
        'configuration'=> $configuration,
        'seller_info'  => $seller_info,
        'month_arr'    => $month_arr,
    ])->setPaper('A4');

    return $pdf->stream('CreditNote-'.$sale_return->sr_prefix.'.pdf');
    
    }elseif($sale_return_nature->sr_nature == "WITH GST" 
        && $sale_return_nature->sr_type == "WITHOUT ITEM"){

    
    
    
    /*
    |--------------------------------------------------------------------------
    | Sale Return
    |--------------------------------------------------------------------------
    */
    $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
        ->join('states','accounts.state','=','states.id')
        ->select(
            'sales_returns.*',
            'accounts.account_name',
            'accounts.gstin',
            'accounts.address',
            'accounts.pin_code',
            'states.name as sname'
        )
        ->where('sales_returns.id',$sale_return_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Items (Without GST Entry)
    |--------------------------------------------------------------------------
    */
    $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
        ->where('sale_return_id', $sale_return_id)
        ->select(
            'debit',
            'percentage',
            'sale_return_without_gst_entry.hsn_code',
            'accounts.account_name'
        )
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Seller Info
    |--------------------------------------------------------------------------
    */
    $seller_info = DB::table('gst_settings')
        ->join('states','gst_settings.state','=','states.id')
        ->where([
            'company_id' => $company_id,
            'gst_no'     => $sale_return->merchant_gst
        ])
        ->select(['gst_no','address','pincode','states.name as sname'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */
    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
        ->where('company_id',$company_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Financial Year Array
    |--------------------------------------------------------------------------
    */
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04',$from.'-05',$from.'-06',$from.'-07',
        $from.'-08',$from.'-09',$from.'-10',$from.'-11',
        $from.'-12',$to.'-01',$to.'-02',$to.'-03'
    ];

    /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */
    $pdf = \PDF::loadView('saleReturnWithoutItemPdf', [
        'company_data' => $company_data,
        'configuration'=> $configuration,
        'month_arr'    => $month_arr,
        'seller_info'  => $seller_info,
        'sale_return'  => $sale_return,
        'items'        => $items,
    ])->setPaper('A4');

    return $pdf->stream('CreditNote-'.$sale_return->sr_prefix.'.pdf');

}elseif($sale_return_nature->sr_nature == "WITHOUT GST"){
   
    $sale_return = SalesReturn::join('accounts','sales_returns.party','=','accounts.id')
        ->join('states','accounts.state','=','states.id')
        ->select(
            'sales_returns.*',
            'accounts.account_name',
            'accounts.gstin',
            'accounts.address',
            'accounts.pin_code',
            'states.name as sname'
        )
        ->where('sales_returns.id',$sale_return_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Items
    |--------------------------------------------------------------------------
    */
    $items = SaleReturnWithoutGstEntry::join('accounts','sale_return_without_gst_entry.account_name','=','accounts.id')
        ->where('sale_return_id', $sale_return_id)
        ->select('debit','percentage','accounts.account_name')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Seller Info
    |--------------------------------------------------------------------------
    */
    $seller_info = DB::table('gst_settings')
        ->join('states','gst_settings.state','=','states.id')
        ->where([
            'company_id' => $company_id,
            'gst_no'     => $sale_return->merchant_gst
        ])
        ->select(['gst_no','address','pincode','states.name as sname'])
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */
    $configuration = SaleInvoiceConfiguration::with(['terms','banks'])
        ->where('company_id',$company_id)
        ->first();

    /*
    |--------------------------------------------------------------------------
    | Financial Year Month Array
    |--------------------------------------------------------------------------
    */
    $y = explode("-", $financial_year);
    $from = DateTime::createFromFormat('y', $y[0])->format('Y');
    $to   = DateTime::createFromFormat('y', $y[1])->format('Y');

    $month_arr = [
        $from.'-04',$from.'-05',$from.'-06',$from.'-07',
        $from.'-08',$from.'-09',$from.'-10',$from.'-11',
        $from.'-12',$to.'-01',$to.'-02',$to.'-03'
    ];

    /*
    |--------------------------------------------------------------------------
    | Generate PDF
    |--------------------------------------------------------------------------
    */
    $pdf = \PDF::loadView('saleReturnWithoutGstPdf', [
        'items'        => $items,
        'company_data' => $company_data,
        'month_arr'    => $month_arr,
        'seller_info'  => $seller_info,
        'configuration'=> $configuration,
        'sale_return'  => $sale_return,
    ])->setPaper('A4');

    return $pdf->stream('CreditNote-'.$sale_return->sr_prefix.'.pdf');


}
    
    
}


}
