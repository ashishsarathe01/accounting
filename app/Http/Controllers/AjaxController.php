<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\Units;
use App\Models\ManageItems;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\BillSundrys;
use App\Models\Sales;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Models\Bank;
use App\Models\ItemLedger;
use App\Models\ClosingStock;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use Session;

class AjaxController extends Controller
{
    public function getItemDetails(Request $request)
    {
        $ids = $request->item_id;
        $units = $request->units;
        $quantities = $request->quantity;
        $prices = $request->price;
        $amounts = $request->amount;

        $total_tax_amount = 0;
        // print_r($ids);
        // print_r($units);
        // print_r($quantity);
        // print_r($price); 
        // print_r($amount); die;

        $party_id = $request->party;
        $accountData = Accounts::find($party_id);

        $itemsdata = array();
        $uncommon_SGST_tax_amount = 0;
        $uncommon_CGST_tax_amount = 0;
        $index = 0;
        foreach ($ids as $id) {
            $items = ManageItems::find($id);
            $companyData = Companies::find($items->company_id);

            $SGST_tax_amount = 0;
            $CGST_tax_amount = 0;
            $IGST_tax_amount = 0;
            $gst_rate = $items->gst_rate;

            if (!empty(trim($companyData->gst)) && !empty(trim($accountData->gstin))) {

                $state_code1 = substr(trim($companyData->gst), 0, -13);
                $state_code2 = substr(trim($accountData->gstin), 0, -13);


                if ($state_code1 == $state_code2) {
                    $gst_rate = number_format($items->gst_rate / 2, 1);
                    $items->SGST = $gst_rate;
                    $items->CGST = $gst_rate;
                    $items->IGST = 0;
                } else {
                    $items->SGST = 0;
                    $items->CGST = 0;
                    $items->IGST = $gst_rate;
                }
            } else {
                $items->SGST = 0;
                $items->CGST = 0;
                $items->IGST = $gst_rate;
            }

            if (!empty($amounts[$index])) {
                $SGST_tax_amount = ($amounts[$index] * $items->SGST) / 100;

                $CGST_tax_amount = ($amounts[$index] * $items->CGST) / 100;

                $IGST_tax_amount = ($amounts[$index] * $items->IGST) / 100;
            }


            $itemsdata[$id] = array("IGST" => array("tax_rate" => $items->IGST, 'tax_amount' => $IGST_tax_amount), "SGST" => array("tax_rate" => $items->SGST, 'tax_amount' => $SGST_tax_amount), 'CGST' => array("tax_rate" => $items->CGST, 'tax_amount' => $CGST_tax_amount));

            $index++;
        }


        //  print_r($itemsdata);die;

        $common_IGST_array = array();
        $common_IGST_tax_amount = array();

        $common_SGST_array = array();
        $common_SGST_tax_amount = array();

        $common_CGST_array = array();
        $common_CGST_tax_amount = array();

        $uncommon_IGST_array = array();
        $uncommon_IGST_tax_amount = array();

        $uncommon_SGST_array = array();
        $uncommon_SGST_tax_amount = array();

        $uncommon_CGST_array = array();
        $uncommon_CGST_tax_amount = array();

        $IGST_amount = 0;
        $SGST_amount = 0;
        $CGST_amount = 0;




        foreach ($itemsdata as $key => $itemdt) {

            // $IGST_amount = $itemdt['IGST']['tax_amount'];
            // $SGST_amount = $itemdt['SGST']['tax_amount'];
            // $CGST_amount = $itemdt['CGST']['tax_amount'];

            $flag = 0;
            foreach ($itemsdata as $index => $itemdt2) {

                if ($itemdt['IGST']['tax_rate'] == $itemdt2['IGST']['tax_rate'] && $key != $index && $itemdt2['IGST']['tax_amount'] > 0) {
                    $flag = 1;
                    $IGST_amount = $itemdt2['IGST']['tax_amount'];
                    $common_IGST__array[$key]["IGST_Amount"]['tax_amount'] = $IGST_amount;
                    $common_IGST__array[$key]["IGST_Rate"]['tax_rate'] = $itemdt['IGST']['tax_rate'];
                }

                if ($itemdt['SGST']['tax_rate'] == $itemdt2['SGST']['tax_rate'] && $key != $index && $itemdt2['SGST']['tax_amount'] > 0) {
                    $flag = 1;
                    $SGST_amount = $itemdt2['SGST']['tax_amount'];
                    $common_SGST_array[$key]["SGST_Amount"]['tax_amount'] = $SGST_amount;
                    $common_SGST_array[$key]["SGST_Rate"]['tax_rate'] = $itemdt['SGST']['tax_rate'];
                }

                if ($itemdt['CGST']['tax_rate'] == $itemdt2['CGST']['tax_rate'] && $key != $index && $itemdt2['CGST']['tax_amount'] > 0) {
                    $flag = 1;
                    $CGST_amount = $itemdt2['CGST']['tax_amount'];
                    $common_CGST_array[$key]["CGST_Amount"]['tax_amount'] = $CGST_amount;
                    $common_CGST_array[$key]["CGST_Rate"]['tax_rate'] = $itemdt['CGST']['tax_rate'];
                }
            }





            $IGST_amount2 = 0;

            $SGST_amount2 = 0;
            $CGST_amount2 = 0;

            if ($flag == 0) {
                if ($itemdt['IGST']['tax_amount'] > 0) {
                    $IGST_amount2 += $itemdt['IGST']['tax_amount'];

                    $uncommon_IGST_array[$key]["IGST_Amount"]['tax_amount'] = $IGST_amount2;
                    $uncommon_IGST_array[$key]["IGST_Rate"]['tax_rate'] = $itemdt['IGST']['tax_rate'];
                }

                if ($itemdt['SGST']['tax_amount'] > 0) {
                    $SGST_amount2 += $itemdt['SGST']['tax_amount'];

                    $uncommon_SGST_array[$key]["SGST_Amount"]['tax_amount'] = $SGST_amount2;
                    $uncommon_SGST_array[$key]["SGST_Rate"]['tax_rate'] = $itemdt['SGST']['tax_rate'];
                }

                if ($itemdt['CGST']['tax_amount'] > 0) {
                    $CGST_amount2 += $itemdt['CGST']['tax_amount'];

                    $uncommon_CGST_array[$key]["CGST_Amount"]['tax_amount'] = $CGST_amount2;
                    $uncommon_CGST_array[$key]["CGST_Rate"]['tax_rate'] = $itemdt['CGST']['tax_rate'];
                }
            }
        }





        $all_taxes = array("Common_IGST" => $common_IGST_array, "Common_SGST" => $common_SGST_array, 'Common_CGST' => $common_CGST_array, 'UnCommon_IGST' => $uncommon_IGST_array, 'UnCommon_SGST' => $uncommon_SGST_array, 'UnCommon_CGST' => $uncommon_CGST_array);

        // print_r($all_taxes); die;

        $tax__array = array();
        $i = 0;


        $CGST_Amount_final = 0;
        $SGST_Amount_final = 0;

        $IGST_Rate_final = 0;
        $CGST_Rate_final = 0;
        $SGST_Rate_final = 0;
        $itemsIds = [];

        $UniqueArrIGST = array();
        $UniqueArrSGST = array();
        $UniqueArrCGST = array();

        $UniqueArr_IGST = array();
        $UniqueArr_SGST = array();
        $UniqueArr_CGST = array();

        // $UniqueArrIGST=array_unique($common_IGST_array, SORT_REGULAR);
        //$UniqueArrSGST=array_unique($common_SGST_array, SORT_REGULAR);
        //$UniqueArrCGST=array_unique($common_CGST_array, SORT_REGULAR);


        foreach ($common_IGST_array as $values) {
            $UniqueArr_IGST[$values['SGST_Rate']['tax_rate']] = $values;
        }

        $UniqueArrIGST = array_values($UniqueArr_IGST);

        foreach ($common_SGST_array as $values) {
            $UniqueArr_SGST[$values['SGST_Rate']['tax_rate']] = $values;
        }

        $UniqueArrSGST = array_values($UniqueArr_SGST);

        foreach ($common_CGST_array as $values) {
            $UniqueArr_CGST[$values['CGST_Rate']['tax_rate']] = $values;
        }

        $UniqueArrCGST = array_values($UniqueArr_CGST);


        $count = 0;
        if (count($common_IGST_array) > 0) {

            foreach ($UniqueArrIGST as $index => $dt) {
                $IGST_Amount_final = 0;
                $itemsIds = [];
                foreach ($common_IGST_array as $indx => $rw) {
                    if (!empty($dt['IGST_Amount']['tax_amount']) && !empty($dt['IGST_Rate']['tax_rate'])) {
                        if ($dt['IGST_Rate']['tax_rate'] == $rw['IGST_Rate']['tax_rate']) {
                            $IGST_Amount_final += $rw['IGST_Amount']['tax_amount'];
                            $IGST_Rate_final = $rw['IGST_Rate']['tax_rate'];
                            $itemsIds[] = $indx;

                            $tax__array[$count] = array("item_id" => $itemsIds, "tax_type" => "IGST", "tax_rate" => $IGST_Rate_final, "tax_Amount" => number_format($IGST_Amount_final,2));
                        }
                    }
                }

                $count++;
            }

            //$tax__array[] = array("item_id"=>$itemsIds,"tax_type"=>"IGST","tax_rate"=>$IGST_Rate_final,"tax_Amount"=>$IGST_Amount_final);
        }



        if (count($common_SGST_array) > 0) {
            //$itemsIds = [];

            foreach ($UniqueArrSGST as $index => $dt) {
                $SGST_Amount_final = 0;
                $itemsIds = [];
                foreach ($common_SGST_array as $indx => $rw) {
                    if (!empty($dt['SGST_Amount']['tax_amount']) && !empty($dt['SGST_Rate']['tax_rate'])) {
                        if ($dt['SGST_Rate']['tax_rate'] == $rw['SGST_Rate']['tax_rate']) {
                            $SGST_Amount_final += $rw['SGST_Amount']['tax_amount'];
                            $SGST_Rate_final = $rw['SGST_Rate']['tax_rate'];
                            $itemsIds[] = $indx;

                            $tax__array[$count] = array("item_id" => $itemsIds, "tax_type" => "SGST", "tax_rate" => $SGST_Rate_final, "tax_Amount" => number_format($SGST_Amount_final,2));
                        }
                    }
                }

                $count++;
            }
        }

        if (count($common_CGST_array) > 0) {
            foreach ($UniqueArrCGST as $index => $dt) {
                $CGST_Amount_final = 0;
                $itemsIds = [];
                foreach ($common_CGST_array as $indx => $rw) {
                    if (!empty($dt['CGST_Amount']['tax_amount']) && !empty($dt['CGST_Rate']['tax_rate'])) {
                        if ($dt['CGST_Rate']['tax_rate'] == $rw['CGST_Rate']['tax_rate']) {
                            $CGST_Amount_final += $rw['CGST_Amount']['tax_amount'];
                            $CGST_Rate_final = $rw['CGST_Rate']['tax_rate'];
                            $itemsIds[] = $indx;

                            $tax__array[$count] = array("item_id" => $itemsIds, "tax_type" => "CGST", "tax_rate" => $CGST_Rate_final, "tax_Amount" => number_format($CGST_Amount_final,2));
                        }
                    }
                }

                $count++;
            }
        }


        if (count($uncommon_IGST_array) > 0) {
            //$itemsIds = [];
            //$count++;
            foreach ($uncommon_IGST_array as $index => $dt) {
                if (!empty($dt['IGST_Amount']['tax_amount']) && !empty($dt['IGST_Rate']['tax_rate'])) {
                    //$itemsIds[] = $index;
                    $tax__array[] = array("item_id" => [$index], "tax_type" => "IGST", "tax_rate" => $dt['IGST_Rate']['tax_rate'], "tax_Amount" => number_format($dt['IGST_Amount']['tax_amount'],2));
                }
            }
        }

        if (count($uncommon_SGST_array) > 0) {
            //$itemsIds = [];
            //$count++;
            foreach ($uncommon_SGST_array as $index => $dt) {
                if (!empty($dt['SGST_Amount']['tax_amount']) && !empty($dt['SGST_Rate']['tax_rate'])) {
                    //  $itemsIds[] = $index;
                    $tax__array[] = array("item_id" => [$index], "tax_type" => "SGST", "tax_rate" => $dt['SGST_Rate']['tax_rate'], "tax_Amount" => number_format($dt['SGST_Amount']['tax_amount'],2));
                }
            }
        }

        if (count($uncommon_CGST_array) > 0) {
            // $itemsIds = [];
            //$count++;
            foreach ($uncommon_CGST_array as $index => $dt) {
                if (!empty($dt['CGST_Amount']['tax_amount']) && !empty($dt['CGST_Rate']['tax_rate'])) {
                    // $itemsIds[] = $index;
                    $tax__array[] = array("item_id" => [$index], "tax_type" => "CGST", "tax_rate" => $dt['CGST_Rate']['tax_rate'], "tax_Amount" => number_format($dt['CGST_Amount']['tax_amount'],2));
                }
            }
        }
        array_multisort(array_column($tax__array, 'tax_rate'), SORT_ASC, $tax__array);
        $maximum_tax_rate = 0;
        if (count($tax__array) > 0) {
            $maximum_tax_rate = max(array_column($tax__array, 'tax_rate'));
        }


        $response = array(
            'status' => true,
            'message' => 'items details fetched successfully.',
            'data' => array('tax_data' => $tax__array, 'maximum_tax_rate' => $maximum_tax_rate)
        );
        return json_encode($response);
    }

    public function getBillSundryDetails(Request $request)
    {
        $billSundryName = $request->billSundryName;
        $BillSundrys = BillSundrys::where('name', $billSundryName)->first();
        $response = array(
            'status' => true,
            'message' => 'items details fetched successfully.',
            'data' => array('bill_sundrys' => $BillSundrys)
        );
        return json_encode($response);
    }


    public function getAccountsDetails(Request $request)
    {
        $account_id  = $request->account_id;
        $Accountdetails = Accounts::join('states','accounts.state','=','states.id')
                                    ->where('accounts.id', $account_id)
                                    ->select('address', 'pin_code', 'gstin', 'pan','states.name as sname')
                                    ->first();
        $response = array(
            'status' => true,
            'message' => 'accounts details fetched successfully.',
            'data' => array('accounts' => $Accountdetails)
        );
        return json_encode($response);
    }

   public function getInvoiceDetails(Request $request){
      $financial_year = Session::get('default_fy');
      $account_id  = $request->account_id;
      $assign = SalesReturn::where('party',$account_id)
                              ->where('delete','0')
                              ->where('sale_bill_id','!=',$request->sale_bill_id)
                              ->where('financial_year',$financial_year)
                              ->pluck('invoice_no');
                              
      $Accountdetails = Sales::select('voucher_no','id','series_no','financial_year','material_center')
                              ->where('party', $account_id)
                              ->where('delete','0')
                              ->where('financial_year',$financial_year)
                              ->whereNotIn('voucher_no', $assign)
                              ->orderBy('voucher_no','desc')
                              ->get();
      return json_encode($Accountdetails);
   }

   public function getPurchaseInvoiceDetails(Request $request){
      $financial_year = Session::get('default_fy');
      $account_id  = $request->account_id;
      $assign = PurchaseReturn::where('party',$account_id)
                              ->where('purchase_bill_id','!=',$request->purchase_bill_id)
                              ->where('financial_year',$financial_year)
                              ->where('delete','0')
                              ->pluck('invoice_no');
      $Accountdetails = Purchase::select('voucher_no','id','series_no','financial_year','material_center')
                                 ->where('delete','0')
                                 ->where('party', $account_id)
                                 ->where('financial_year',$financial_year)
                                 ->whereNotIn('voucher_no', $assign)
                                 ->orderBy('voucher_no','desc')
                                 ->get();
      return json_encode($Accountdetails);
   }

    public function getSaleItemsDetails(Request $request)
    {
        $voucher_no  = $request->voucher_no;
        $sale_id = Sales::select('id')->where('voucher_no', $voucher_no)->where('company_id',Session::get('user_company_id'))->first();
        $id = $sale_id->id;
        $manageitems = DB::table('sale_descriptions')->where('sale_id', $id)
            ->select('units.s_name as unit','manage_items.gst_rate', 'units.id as unit_id', 'sale_descriptions.goods_discription', 'manage_items.name as items_name', 'manage_items.id as item_id')
            ->join('units', 'sale_descriptions.unit', '=', 'units.id')
            ->join('manage_items', 'sale_descriptions.goods_discription', '=', 'manage_items.id')
            ->get();
        return json_encode($manageitems);
    }
    public function getPurchaseItemsDetails(Request $request)
    {
        $voucher_no  = $request->voucher_no;
        $purchase_id = Purchase::select('id')->where('voucher_no', $voucher_no)->where('company_id',Session::get('user_company_id'))->first();
        $id = $purchase_id->id;
        $manageitems = DB::table('purchase_descriptions')->where('purchase_id', $id)
            ->select('units.s_name as unit','manage_items.gst_rate', 'units.id as unit_id', 'purchase_descriptions.goods_discription', 'manage_items.name as items_name', 'manage_items.id as item_id')
            ->join('units', 'purchase_descriptions.unit', '=', 'units.id')
            ->join('manage_items', 'purchase_descriptions.goods_discription', '=', 'manage_items.id')
            ->get();
        return json_encode($manageitems);
   }
   public function setRedircetUrl(Request $request){
      $url  = $request->url;  
      Session::put('redirect_url',$url);     
      return true;
   }
   public function setPrimaryBank(Request $request){
      $id  = $request->id;  
      $bank = Bank::find($id);     
      $bank->primary_bank = 1;
      if($bank->save()){
         return 1;
      }else{
         return 0;
      }
   }
   public function getPartyList(Request $request){
      if($request->get('query')){
         $query = $request->get('query');
         $groups = DB::table('account_groups')
                        ->whereIn('heading', [3,11])
                        ->where('heading_type','group')
                        ->where('status','1')
                        ->where('delete','0')
                        ->where('company_id',Session::get('user_company_id'))
                        ->pluck('id');
                        $groups->push(3);
                        $groups->push(11);
         $data = DB::table('accounts')
            ->select('accounts.*','states.state_code')
            ->leftjoin('states','accounts.state','=','states.id')
            ->where('account_name', 'LIKE', "%{$query}%")
            ->where('delete', '=', '0')
            ->where('status', '=', '1')
            ->whereIn('company_id', [Session::get('user_company_id'),0])
            ->whereIn('under_group', $groups)
            ->orderBy('account_name')
            ->get();
         $output = '<ul class="dropdown-menu" style="display:block; position:relative">';
         foreach($data as $row){
            $output .='<li class="party_li"  data-state_code="'.$row->state_code.'" data-gstin="'.$row->gstin.'" data-id="'.$row->id.'" data-address="'.$row->address.',".'.$row->pin_code.'"><a href="javascript:void(0)">'.$row->account_name.'</a></li>';
         }
         $output .= '</ul>';
         echo $output;
      }
   }
   public function getItemList(Request $request){
      if($request->get('query')){
         $query = $request->get('query');
         $data = DB::table('manage_items')->join('units', 'manage_items.u_name', '=', 'units.id')
            ->join('item_groups', 'item_groups.id', '=', 'manage_items.g_name')
            ->where('manage_items.name', 'LIKE', "%{$query}%")
            ->where('manage_items.delete', '=', '0')
            ->where('manage_items.status', '=', '1')
            ->where('manage_items.company_id',Session::get('user_company_id'))
            ->orderBy('manage_items.name')
            ->select(['units.s_name as unit', 'manage_items.*'])
            ->get();
         $output = '<ul class="dropdown-menu" style="display:block; position:relative">';
         foreach($data as $row){
            $item_in_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('in_weight');
            $item_out_weight = DB::table('item_ledger')->where('status','1')->where('delete_status','0')->where('company_id',Session::get('user_company_id'))->where('item_id',$row->id)->sum('out_weight');
            $available_item = $item_in_weight-$item_out_weight;
            $output .='<li class="item_li" data-unit_id="'.$row->u_name.'" data-val="'.$row->unit.'" data-percent="'.$row->gst_rate.'" data-id="'.$request->get('id').'" data-itemid="'.$row->id.'" data-available_item="'.$available_item.'"><a href="javascript:void(0)">'.$row->name.'</a></li>';
         }
         $output .= '</ul>';
         echo $output;
      }
   }
   public function checkAccountName(Request $request){
      if($request->get('account_name')){
         $account_name = $request->get('account_name');
         $account = Accounts::select('id')
                  ->where('account_name',$account_name)
                  ->where('delete', '=', '0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->first();
         if($account){
            return 1;
         }else{
            return 0;
         }
      }
   }
   public function checkGstin(Request $request){
      if($request->get('gstin')){
         $curl = curl_init();
         curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://kraftpaperz.com/stage/api/public/index.php/api/v1/gst-info',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
               "gst_no":"'.$request->get('gstin').'"
            }',
            CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json'
            ),
         ));
         $response = curl_exec($curl);
         curl_close($curl);
         echo $response;
      }
   }
   public function updateItemStock(Request $request){
      $input = $request->all();
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      $tdate = date('Y-m-d',strtotime($input['to_date']));
      $open_date = $y[0]."-04-01";
      $open_date = date('Y-m-d',strtotime($open_date));
      $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));
      $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$request->to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' GROUP BY item_id"));
      $result = array();
      foreach ($item_in_data as $element){
         $result[$element->item_id][] = round($element->total_price/$element->in_weight,2);
      }
      $total_balance = 0;$total_weight = 0;
      foreach ($item as $key => $value){
         $remaining_weight = $value->in_weight - $value->out_weight;
         if (array_key_exists($value->item_id,$result)){
            $total_balance = $total_balance + $remaining_weight*$result[$value->item_id][0];
            $total_weight = $total_weight + $remaining_weight;
         }
      }
      echo $total_balance = round($total_balance,2);
      die;
      $stock = new ClosingStock();
      $stock->closing_quantity = round($total_weight);
      $stock->closing_price = round($total_balance/$total_weight,2);
      $stock->closing_amount = $total_balance;
      $stock->from_date = $open_date;
      $stock->to_date = $to_date;
      $stock->company_id = Session::get('user_company_id');
      $stock->created_by = Session::get('user_id');
      $stock->created_at = date("Y-m-d H:i:s");
      if($stock->save()){
         $response = array(
            'status' => true,
            'message' => 'Stock Updated Successfully.'
         );
         return json_encode($response);
      }else{
         $response = array(
            'status' => false,
            'message' => 'Something went wrong.'
         );
         return json_encode($response);
      }

      
      $from_date = $input['from_date'];
      $to_date = $input['to_date'];
      if(date('m')<=3){
         $current_year = (date('Y')-1);
      }else{
         $current_year = date('Y');
      }
      $financial_start_date = $current_year."-04-01";
      $financial_to_date = $input['to_date'];

      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $financial_start_date = $y[0]."-04-01";
         $financial_start_date = date('Y-m-d',strtotime($financial_start_date));
         $financial_to_date = date('Y-m-d',strtotime($input['to_date']));
      }      
      $closing_quantity = 0;$closing_amount = 0;$closing_price = 0;


      // totalpurchasequantity-totalsalequantity = avialablequantity
      // avreageprice = purchasetotalamount/totalpurchasequantity;
      // avreageamount = avialablequantity*avreageprice;
      $closing_amount = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','=','-1')
                  ->sum('total_price');
      $closing_quantity = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','=','-1')
                  ->sum('in_weight');
      $closing_price = $closing_amount/$closing_quantity;
      $closing_price = round($closing_price,2);
      
      //Get Purchase/Purchase
      $item_account = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','!=','-1')
                  ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')>=STR_TO_DATE('".$financial_date."','%Y-%m-%d') and STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                  ->orderBy('txn_date')
                  ->get();
      $out_weight = $item_account->sum('out_weight');
      $in_weight = $item_account->sum('in_weight');
      if($in_weight>0){
         $closing_quantity = $closing_quantity + $in_weight;
         $item_account1 = ItemLedger::where('status', '1')  
                     ->where('company_id',Session::get('user_company_id'))
                     ->where('delete_status','0')
                     ->where('source','=','2')
                     ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')>=STR_TO_DATE('".$financial_date."','%Y-%m-%d') and STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
                     ->orderBy('txn_date')
                     ->sum('total_price');
         $closing_amount = $closing_amount + $item_account1;
         $closing_price = $closing_amount/$closing_quantity;
         $closing_price = round($closing_price,2);
      }
      if($out_weight>0){
         $closing_quantity = $closing_quantity - $out_weight;
         $closing_amount = $closing_amount - ($closing_quantity*$closing_price);
      }
      $stock = new ClosingStock();
      $stock->closing_quantity = round($closing_quantity);
      $stock->closing_price = $closing_price;
      $stock->closing_amount = round($closing_amount);
      $stock->from_date = $from_date;
      $stock->to_date = $to_date;
      $stock->company_id = Session::get('user_company_id');
      $stock->created_by = Session::get('user_id');
      $stock->created_at = date("Y-m-d H:i:s");
      if($stock->save()){
         $response = array(
            'status' => true,
            'message' => 'Stock Updated Successfully.'
         );
         return json_encode($response);
      }else{
         $response = array(
            'status' => false,
            'message' => 'Something went wrong.'
         );
         return json_encode($response);
      }
   }
   public function getNextInvoiceno(Request $request){
      if($request->get('series_no')){
         $series_no = $request->get('series_no');
         $voucher_no = Sales::select('voucher_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',Session::get('default_fy'))
                        ->where('delete','=','0')
                        ->where('series_no',$series_no)
                        ->max(\DB::raw("cast(voucher_no as SIGNED)"));
         if($voucher_no){
            return $voucher_no+1;
         }else{
            $companyData = Companies::select('gst_config_type')->where('id', Session::get('user_company_id'))->first();
            if($companyData->gst_config_type == "single_gst") {
               $GstSettings = DB::table('gst_settings')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "single_gst"])->first();
               if($GstSettings){
                  if($GstSettings->series==$series_no){
                     if(!empty($GstSettings->invoice_start_from)) {
                        return $GstSettings->invoice_start_from;
                     }else{
                        return 1;
                     }
                  }else{
                     $GstBranch = DB::table('gst_branches ')->where(['company_id' => Session::get('user_company_id'),"gst_setting_id"=>$GstSettings->id,"branch_series"=>$series_no])->first();
                     if(!empty($GstBranch->invoice_start_from)) {
                        return $GstBranch->invoice_start_from;
                     }else{
                        return 1;
                     }
                  }                  
               }
            }else if($companyData->gst_config_type == "multiple_gst") {
               $GstSettings = DB::table('gst_settings_multiple')->where(['company_id' => Session::get('user_company_id'), 'gst_type' => "multiple_gst"])->first();
               if($GstSettings){
                  if($GstSettings->series==$series_no){
                     if(!empty($GstSettings->invoice_start_from)) {
                        return $GstSettings->invoice_start_from;
                     }else{
                        return  1;
                     }
                  }else{
                     $GstBranch = DB::table('gst_branches ')->where(['company_id' => Session::get('user_company_id'),"gst_setting_multiple_id"=>$GstSettings->id,"branch_series"=>$series_no])->first();
                     if(!empty($GstBranch->invoice_start_from)) {
                        return $GstBranch->invoice_start_from;
                     }else{
                        return 1;
                     }
                  }                  
               }
            }
         }
      }
   }
   public function getNextSaleReturnno(Request $request){
      if($request->get('series_no')){
         $series_no = $request->get('series_no');
         $voucher_no = SalesReturn::select('sale_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',Session::get('default_fy'))
                        ->where('delete','=','0')
                        ->where('series_no',$series_no)
                        ->max(\DB::raw("cast(sale_return_no as SIGNED)"));
         if($voucher_no){
            return $voucher_no+1;
         }else{
            return 1;
         }
      }
   }
   public function getNextPurchaseReturnno(Request $request){
      if($request->get('series_no')){
         $series_no = $request->get('series_no');
         $voucher_no = PurchaseReturn::select('purchase_return_no')                     
                        ->where('company_id',Session::get('user_company_id'))
                        ->where('financial_year','=',Session::get('default_fy'))
                        ->where('delete','=','0')
                        ->where('series_no',$series_no)
                        ->max(\DB::raw("cast(purchase_return_no as SIGNED)"));
         if($voucher_no){
            return $voucher_no+1;
         }else{
            return 1;
         }
      }
   }
}