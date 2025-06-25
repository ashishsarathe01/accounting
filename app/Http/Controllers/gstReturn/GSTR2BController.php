<?php

namespace App\Http\Controllers\gstReturn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GSTR2B;
use App\Models\Companies;
use App\Models\gstToken;
use App\Models\Accounts;
use Session;
use DB;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
class GSTR2BController extends Controller
{
    /**
     * Display the GSTR2B view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }else if($company->gst_config_type == "multiple_gst"){
            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_no')
                            ->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst",'delete'=>'0','status'=>'1'])
                            ->get();
        }
        return view('gstReturn.gstr2b',['gst'=>$gst]);
    }
    
    public function gstr2bDetail(Request $request){
        $month = date('mY',strtotime($request->month));
        $state_code = substr(trim($request->gstin),0,2);
        $txn = "";
        //Get GST Username
        $company = Companies::select('gst_config_type')
                                ->where('id', Session::get('user_company_id'))
                                ->first();
        if($company->gst_config_type == "single_gst"){
            $gst = DB::table('gst_settings')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->gstin
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }else if($company->gst_config_type == "multiple_gst"){            
            $gst = DB::table('gst_settings_multiple')
                            ->select('gst_username')
                            ->where([
                                'company_id' => Session::get('user_company_id'),
                                'gst_no' => $request->gstin
                            ])
                            ->first();
            $gst_user_name = $gst->gst_username;
        }
        if($gst_user_name==""){
            $response = array(
                    'status' => false,
                    'message' => 'Please Enter GST User Name In GST Configuration.'
                );
            return json_encode($response);
        }
        $GSTR2B = GSTR2B::select('id')
                    ->where('company_gstin',$request->gstin)
                    ->where('company_id',Session::get('user_company_id'))
                    ->where('res_month',$request->month)
                    ->first();
        if(!$GSTR2B){
            //Check and generate token
            $gst_token = gstToken::select('txn','created_at')
                                ->where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('status',1)
                                ->orderBy('id','desc')
                                ->first();
            if($gst_token){
                $token_expiry = date('d-m-Y H:i:s',strtotime('+6 hour',strtotime($gst_token->created_at)));
                $current_time = date('d-m-Y H:i:s');
                if(strtotime($token_expiry)<strtotime($current_time)){
                    $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                    if($token_res==0){
                        $response = array(
                            'status' => false,
                            'message' => 'Something Went Wrong In Token Generation'
                        );
                        return json_encode($response);
                    }
                    $response = array(
                        'status' => true,
                        'message' => 'TOKEN-OTP'
                    );
                    return json_encode($response);
                }
                $txn = $gst_token->txn;
            }else{
                $token_res = CommonHelper::gstTokenOtpRequest($state_code,$gst_user_name,$request->gstin);
                if($token_res==0){
                        $response = array(
                            'status' => false,
                            'message' => 'Something Went Wrong In Token Generation'
                        );
                        return json_encode($response);
                    }
                $response = array(
                        'status' => true,
                        'message' => 'TOKEN-OTP'
                );
                return json_encode($response);
            }       
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.mastergst.com/gstr2b/all?email=pram92500@gmail.com&gstin='.$request->gstin.'&rtnprd='.$month,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                'gst_username:'.$gst_user_name,
                'state_cd: '.$state_code,  
                'ip_address: 162.215.254.201',
                'txn: '.$txn,
                'client_id: GSPdea8d6fb-aed1-431a-b589-f1c541424580',
                'client_secret: GSP4c44b790-ef11-4725-81d9-5f8504279d67'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response);
            // echo "<pre>";
            // print_r($result);die;
            if(isset($result->status_cd) && $result->status_cd==0){
                echo json_encode(array("status"=>0,"message"=>$result->error->message));
                exit();
            }
            
            if(isset($result->data)){
                $GSTR2B = new GSTR2B;
                $GSTR2B->res_month = $request->month;
                $GSTR2B->res_data = json_encode($result->data);
                $GSTR2B->company_gstin = $request->gstin;
                $GSTR2B->company_id = Session::get('user_company_id');
                $GSTR2B->created_at = Carbon::now(); 
                $GSTR2B->save();                                       
            }                  
            $response = array(
                'status' => true,
                'message' => 'SUCCESS',
                'data' => ""
            );
            return json_encode($response);                
        }else{
            $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                                ->where('company_id',Session::get('user_company_id'))
                                ->where('res_month',$request->month)
                                ->first(); 
            
            $gstr2b = json_decode($gstr2b->res_data);               
            $response = array(
                'status' => true,
                'message' => 'GSTR2B',
                'data' => $gstr2b->data->docdata->b2b
            );
            return json_encode($response);
        }
    }
    public function gstr2bAllInfo(Request $request){
        $account = Accounts::select('account_name')
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('gstin',$request->ctin)
                            ->first();
        $account_name = "";
        if($account){
            $account_name = $account->account_name;
        }
        $gstr2b = GSTR2B::where('company_gstin',$request->gstin)
                            ->where('company_id',Session::get('user_company_id'))
                            ->where('res_month',$request->month)
                            ->first();        
        $gstr2b = json_decode($gstr2b->res_data);
        // echo "<pre>";
        // print_r($gstr2b->data->docdata->b2b);
        $b2b_invoices = "";$total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;
        foreach ($gstr2b->data->docdata->b2b as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->inv as $invoice) {
                    $total_val += $invoice->val;
                    $total_txval += $invoice->txval;
                    $total_igst += $invoice->igst;
                    $total_cgst += $invoice->cgst;
                    $total_sgst += $invoice->sgst;
                    $total_cess += $invoice->cess;
                    $b2b_invoices.="<tr>
                        <td>".$invoice->inum."</td>
                        <td>".$invoice->dt."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->val)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                        <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                    </tr>";
                }
                $b2b_invoices .= "<tr>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                </tr>";
                break; // Stop after first match
            }
        }
        $b2b_debit_note = "";$b2b_credit_note = "";
        $total_val = 0; $total_txval = 0; $total_igst = 0; $total_cgst = 0; $total_sgst = 0; $total_cess = 0;
        $debit_total_val = 0; $debit_total_txval = 0; $debit_total_igst = 0; $debit_total_cgst = 0; $debit_total_sgst = 0; $debit_total_cess = 0;
        foreach ($gstr2b->data->docdata->cdnr as $record) {
            if($record->ctin === $request->ctin) {
                foreach ($record->nt as $invoice) {
                    if($invoice->typ = "C"){
                        $total_val += $invoice->val;
                        $total_txval += $invoice->txval;
                        $total_igst += $invoice->igst;
                        $total_cgst += $invoice->cgst;
                        $total_sgst += $invoice->sgst;
                        $total_cess += $invoice->cess;
                        $b2b_credit_note.="<tr>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                        </tr>";
                    }else if($invoice->typ = "D"){
                        $debit_total_val += $invoice->val;
                        $debit_total_txval += $invoice->txval;
                        $debit_total_igst += $invoice->igst;
                        $debit_total_cgst += $invoice->cgst;
                        $debit_total_sgst += $invoice->sgst;
                        $debit_total_cess += $invoice->cess;
                        $b2b_debit_note.="<tr>
                            <td>".$invoice->ntnum."</td>
                            <td>".$invoice->dt."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->val)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->txval)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->igst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->sgst)."</td>
                            <td style='text-align: right'>".formatIndianNumber($invoice->cess)."</td>
                        </tr>";
                    }                    
                }
                $b2b_credit_note .= "<tr>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($total_cess)."</th>
                </tr>";
                $b2b_debit_note .= "<tr>
                    <th colspan='2' style='text-align: right'><strong>Total</strong></th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_val)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_txval)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_igst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_sgst)."</th>
                    <th style='text-align: right'>".formatIndianNumber($debit_total_cess)."</th>
                </tr>";
                break; // Stop after first match
            }
        }
        return view('gstReturn.gstr2b_all_info',[
            'account_name' => $account_name,
            'ctin' => $request->ctin,
            'month' => $request->month,
            'gstin' => $request->gstin,
            'b2b_invoices' => $b2b_invoices,
            'b2b_credit_note' => $b2b_credit_note,
            'b2b_debit_note' => $b2b_debit_note
        ]);
        
    }
    
}
