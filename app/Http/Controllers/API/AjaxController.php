<?php

namespace App\Http\Controllers\API;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Units;
use App\Models\ManageItems;
use App\Models\Companies;
use App\Models\Accounts;
use App\Models\BillSundrys;
use Carbon\Carbon;
use DB;

class AjaxController extends Controller
{
    public function getItemDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
           'party_id' => 'required',
           'item_id' => 'required',
           'quantity' => 'required',
           'price' => 'required',
           'amount' => 'required',

        ], 
        [
            'party_id.required' => 'Party id is required.',
            'item_id.required' => 'Item id is required.',
            'quantity.required' => 'Quantity is required.',
            'price.required' => 'Price is required.',
            'amount.required' => 'Amount is required.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }



        $ids = $request->item_id;
        //$units = $request->units;
        $quantities = $request->quantity;
        $prices = $request->price;
        $amounts = $request->amount;

        $total_tax_amount = 0;
        // print_r($ids);
        // print_r($units);
        // print_r($quantity);
        // print_r($price); 
        // print_r($amount); die;

        $party_id = $request->party_id;
        $accountData = Accounts::find($party_id);

        $itemsdata = array();
        $uncommon_SGST_tax_amount = 0;
        $uncommon_CGST_tax_amount = 0;
        $index = 0;
        foreach($ids as $id)
        {
            $items = ManageItems::find($id);
            $companyData = Companies::find($items->company_id);

            $SGST_tax_amount = 0;
            $CGST_tax_amount = 0;
            $IGST_tax_amount = 0;
            $gst_rate = $items->gst_rate;
            
                if(!empty(trim($companyData->gst)) && !empty(trim($accountData->gstin)))
                {
                    
                    $state_code1 = substr(trim($companyData->gst),0,-13);
                    $state_code2 = substr(trim($accountData->gstin),0,-13);
                    

                    if($state_code1==$state_code2)
                    {
                        $gst_rate = number_format($items->gst_rate/2,1);
                        $items->SGST = $gst_rate;
                        $items->CGST = $gst_rate;
                        $items->IGST = 0;
                    }
                    else
                    {
                        $items->SGST = 0;
                        $items->CGST = 0;
                        $items->IGST = $gst_rate;
                    }
   
                }
                else
                {
                    $items->SGST = 0;
                    $items->CGST = 0;
                    $items->IGST = $gst_rate;

                }

                if(!empty($amounts[$index]))
                {
                    $SGST_tax_amount = ($amounts[$index]*$items->SGST)/100;

                    $CGST_tax_amount = ($amounts[$index]*$items->CGST)/100;

                    $IGST_tax_amount = ($amounts[$index]*$items->IGST)/100;
                }
               

                $itemsdata[$id] = array("IGST"=>array("tax_rate"=>$items->IGST,'tax_amount'=>$IGST_tax_amount),"SGST"=>array("tax_rate"=>$items->SGST,'tax_amount'=>$SGST_tax_amount),'CGST'=>array("tax_rate"=>$items->CGST,'tax_amount'=>$CGST_tax_amount));

                $index++;
        }


      //  print_r($itemsdata);die;

        $common_IGST_array = array();
        $common_IGST_tax_amount = array();

        $common_SGST_array = array();
        $common_SGST_tax_amount = array();

        $common_CGST__array = array();
        $common_CGST_tax_amount = array();

        $uncommon_IGST_array = array();
        $uncommon_IGST_tax_amount = array();

        $uncommon_SGST_array = array();
        $uncommon_SGST_tax_amount = array();

        $uncommon_CGST__array = array();
        $uncommon_CGST_tax_amount = array();

        $IGST_amount = 0;
        $SGST_amount = 0;
        $CGST_amount = 0;
         

    

        foreach($itemsdata as $key=>$itemdt)
        {
           
            // $IGST_amount = $itemdt['IGST']['tax_amount'];
            // $SGST_amount = $itemdt['SGST']['tax_amount'];
            // $CGST_amount = $itemdt['CGST']['tax_amount'];

            $flag=0;
            foreach($itemsdata as $index=>$itemdt2)
            {

                if($itemdt['IGST']['tax_rate'] == $itemdt2['IGST']['tax_rate'] && $key!=$index && $itemdt2['IGST']['tax_amount']>0)
                {
                    $flag=1;
                    $IGST_amount = $itemdt2['IGST']['tax_amount'];
                    $common_IGST__array[$key]["IGST_Amount"]['tax_amount'] = $IGST_amount;
                    $common_IGST__array[$key]["IGST_Rate"]['tax_rate'] = $itemdt['IGST']['tax_rate'];
                }

                if($itemdt['SGST']['tax_rate'] == $itemdt2['SGST']['tax_rate'] && $key!=$index && $itemdt2['SGST']['tax_amount']>0)
                {
                    $flag=1;
                    $SGST_amount = $itemdt2['SGST']['tax_amount'];
                    $common_SGST_array[$key]["SGST_Amount"]['tax_amount'] = $SGST_amount;
                    $common_SGST_array[$key]["SGST_Rate"]['tax_rate'] = $itemdt['SGST']['tax_rate'];
                }

                if($itemdt['CGST']['tax_rate'] == $itemdt2['CGST']['tax_rate'] && $key!=$index && $itemdt2['CGST']['tax_amount']>0)
                {
                    $flag=1;
                    $CGST_amount = $itemdt2['CGST']['tax_amount'];
                    $common_CGST__array[$key]["CGST_Amount"]['tax_amount'] = $CGST_amount;
                    $common_CGST__array[$key]["CGST_Rate"]['tax_rate'] = $itemdt['CGST']['tax_rate'];
                }

            }





            $IGST_amount2 = 0;

            $SGST_amount2 = 0;
            $CGST_amount2 = 0;

            if ($flag==0) 
            {
                if($itemdt['IGST']['tax_amount']>0)
                {
                    $IGST_amount2 += $itemdt['IGST']['tax_amount'];

                    $uncommon_IGST_array[$key]["IGST_Amount"]['tax_amount'] = $IGST_amount2;
                    $uncommon_IGST_array[$key]["IGST_Rate"]['tax_rate'] = $itemdt['IGST']['tax_rate'];
                }

                if($itemdt['SGST']['tax_amount']>0)
                {
                    $SGST_amount2 += $itemdt['SGST']['tax_amount'];

                    $uncommon_SGST_array[$key]["SGST_Amount"]['tax_amount'] = $SGST_amount2;
                    $uncommon_SGST_array[$key]["SGST_Rate"]['tax_rate'] = $itemdt['SGST']['tax_rate'];
                }

                if($itemdt['CGST']['tax_amount']>0)
                {
                    $CGST_amount2 += $itemdt['CGST']['tax_amount'];

                    $uncommon_CGST__array[$key]["CGST_Amount"]['tax_amount'] = $CGST_amount2;
                    $uncommon_CGST__array[$key]["CGST_Rate"]['tax_rate'] = $itemdt['CGST']['tax_rate'];
                }
            }
        }





        $all_taxes = array("Common_IGST"=>$common_IGST_array,"Common_SGST"=>$common_SGST_array,'Common_CGST'=>$common_CGST__array,'UnCommon_IGST'=>$uncommon_IGST_array,'UnCommon_SGST'=>$uncommon_SGST_array,'UnCommon_CGST'=>$uncommon_CGST__array);

       // print_r($all_taxes); die;

        $tax__array = array();
        $i = 0;

        $IGST_Amount_final = 0;
        $CGST_Amount_final = 0;
        $SGST_Amount_final = 0;

        $IGST_Rate_final = 0;
        $CGST_Rate_final = 0;
        $SGST_Rate_final = 0;
        $itemsIds = [];

            if(count($common_IGST_array)>0)
            {
                $itemsIds = [];
                foreach($common_IGST_array as $index=>$dt)
                {
                    if(!empty($dt['IGST_Amount']['tax_amount']) && !empty($dt['IGST_Rate']['tax_rate']))
                    {
                        $IGST_Amount_final+=$dt['IGST_Amount']['tax_amount'];
                        $IGST_Rate_final = $dt['IGST_Rate']['tax_rate'];
                        $itemsIds[] = $index;

                    }
                }

                $tax__array[] = array("item_id"=>$itemsIds,"tax_type"=>"IGST","tax_rate"=>$IGST_Rate_final,"tax_Amount"=>$IGST_Amount_final);
            }



            if(count($common_SGST_array)>0)
            {
                $itemsIds = [];
                foreach($common_SGST_array as $index=>$dt)
                {
                    if(!empty($dt['SGST_Amount']['tax_amount']) && !empty($dt['SGST_Rate']['tax_rate']))
                    {
                        $SGST_Amount_final += $dt['SGST_Amount']['tax_amount'];
                        $SGST_Rate_final = $dt['SGST_Rate']['tax_rate'];
                        $itemsIds[] = $index;
                    }
                }
                $tax__array[] = array("item_id"=>$itemsIds,"tax_type"=>"SGST","tax_rate"=>$SGST_Rate_final,"tax_Amount"=>$SGST_Amount_final);
            }

            if(count($common_CGST__array)>0)
            {
                $itemsIds = [];
                foreach($common_CGST__array as $index=>$dt)
                {
                    if(!empty($dt['CGST_Amount']['tax_amount']) && !empty($dt['CGST_Rate']['tax_rate']))
                    {
                        $CGST_Amount_final += $dt['CGST_Amount']['tax_amount'];
                        $CGST_Rate_final = $dt['CGST_Rate']['tax_rate'];
                        $itemsIds[] = $index;
                        
                    }
                }
                $tax__array[] = array("item_id"=>$itemsIds,"tax_type"=>"CGST","tax_rate"=>$CGST_Rate_final,"tax_Amount"=>$CGST_Amount_final);
            }


            if(count($uncommon_IGST_array)>0)
            {
                //$itemsIds = [];
                foreach($uncommon_IGST_array as $index=>$dt)
                {
                    if(!empty($dt['IGST_Amount']['tax_amount']) && !empty($dt['IGST_Rate']['tax_rate']))
                    {
                        //$itemsIds[] = $index;
                        $tax__array[] = array("item_id"=>[$index],"tax_type"=>"IGST","tax_rate"=>$dt['IGST_Rate']['tax_rate'],"tax_Amount"=>$dt['IGST_Amount']['tax_amount']);
                    }
                }
            }

            if(count($uncommon_SGST_array)>0)
            {
                //$itemsIds = [];
                foreach($uncommon_SGST_array as $index=>$dt)
                {
                    if(!empty($dt['SGST_Amount']['tax_amount']) && !empty($dt['SGST_Rate']['tax_rate']))
                    {
                      //  $itemsIds[] = $index;
                        $tax__array[] = array("item_id"=>[$index],"tax_type"=>"SGST","tax_rate"=>$dt['SGST_Rate']['tax_rate'],"tax_Amount"=>$dt['SGST_Amount']['tax_amount']);
                    }
                }
            }

            if(count($uncommon_CGST__array)>0)
            {
               // $itemsIds = [];
                foreach($uncommon_CGST__array as $index=>$dt)
                {
                    if(!empty($dt['CGST_Amount']['tax_amount']) && !empty($dt['CGST_Rate']['tax_rate']))
                    {
                       // $itemsIds[] = $index;
                        $tax__array[] = array("item_id"=>[$index],"tax_type"=>"CGST","tax_rate"=>$dt['CGST_Rate']['tax_rate'],"tax_Amount"=>$dt['CGST_Amount']['tax_amount']);
                    }
                }
            }


            
        $response = array(
                    'code' => 200,
                    'status'=>true,
                    'message'=>'items details fetched successfully.',
                    'data'=>array('tax_data'=>$tax__array)
                );
        return response()->json($response);
    }

    public function getBillSundryDetails(Request $request)
    {
        $billSundryName = $request->billSundryName;
        $BillSundrys = BillSundrys::where('name',$billSundryName)->first();
        $response = array(
                    'code' => 200,
                    'status'=>true,
                    'message'=>'items details fetched successfully.',
                    'data'=>array('bill_sundrys'=>$BillSundrys)
                );
         return response()->json($response);
    }


    public function getAccountsDetails(Request $request)
    {
        $account_id  = $request->account_id;
        $Accountdetails = Accounts::select('address','pin_code','gstin','pan')->where('id', $account_id)->first();
        $response = array(
                'code' => 200,
                    'status'=>true,
                    'message'=>'accounts details fetched successfully.',
                    'data'=>array('accounts'=>$Accountdetails)
                );
         return response()->json($response);
    }
}
