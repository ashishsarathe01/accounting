<?php

namespace App\Http\Controllers\display;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountHeading;
use App\Models\AccountGroups;
use App\Models\Accounts;
use App\Models\AccountLedger;
use App\Models\ItemLedger;
use App\Models\ClosingStock;
use Illuminate\Support\Facades\Validator;
use Session;
use DB;
class BalanceSheetController extends Controller{
   /**
     * Show the specified resources in storage.
     *
     * @return \Illuminate\Http\Response
   */
   public function index(){
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      $from_date = $y['0']."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = date('Y-m-t');      
      if(date('m')<=3){
         $current_year = (date('y')-1) . '-' . date('y');
      }else{
         $current_year = date('y') . '-' . (date('y') + 1);
      }
      if($financial_year!=$current_year){
         $from_date = $y[0]."-04-01";
         $from_date = date('Y-m-d',strtotime($from_date));
         $to_date = $y[1]."-03-31";
         $to_date = date('Y-m-d',strtotime($to_date));
      }

      $liability = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){                                       
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                 }
                     ])
                     ->where('bs_profile','1')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();


      $assets = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                 }
                     ])
                     ->where('bs_profile','2')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();
      
      //Closing Stock      
      $open_date = $y[0]."-04-01";
      $open_date = date('Y-m-d',strtotime($open_date));
      $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));
      $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2) || (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=-1) GROUP BY item_id"));
      $result = array();
      foreach ($item_in_data as $element){
         $result[$element->item_id][] = round($element->total_price/$element->in_weight,2);
      }
      $stock_in_hand = 0;$total_weight = 0;
      foreach ($item as $key => $value){
         $remaining_weight = $value->in_weight - $value->out_weight;
         if (array_key_exists($value->item_id,$result)){
            $stock_in_hand = $stock_in_hand + $remaining_weight*$result[$value->item_id][0];
            $total_weight = $total_weight + $remaining_weight;
         }
      }
      $stock_in_hand = round($stock_in_hand,2);
      //Profit & Loss
      $profitloss = 0;
      //Opening Stock      
      $opening_stock = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where(function($query) use ($from_date){
                     $query->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<STR_TO_DATE('".$from_date."','%Y-%m-%d')");
                     $query->orWhere('source','=','-1');
                  })->sum('total_price');
      $item_account = ItemLedger::where('status', '1')  
                  ->where('company_id',Session::get('user_company_id'))
                  ->where('delete_status','0')
                  ->where('source','!=','-1')
                  ->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<STR_TO_DATE('".$from_date."','%Y-%m-%d')")
                  ->orderBy('txn_date')
                  ->get();
      $sale = $item_account->sum('out_weight');
      $purchase = $item_account->sum('in_weight');
      $item_balance = $purchase - $sale;
      if($item_balance>0){
         $weight = 0;$price_arr = [];
         foreach ($item_account as $key => $value) {
            if($item_balance>$weight){
               array_push($price_arr,$value['price']);
            }else{
               break;
            }
            $weight = $weight + $value['in_weight'];
         }         
         $price_arr = array_filter($price_arr);
         $average = array_sum($price_arr)/count($price_arr);
         $average = round($average,2);
         $opening_stock = $opening_stock + ($item_balance*$average);
      }

      //Purchase
      $tot_purchase_amt = DB::table('purchases')
         ->join('purchase_descriptions','purchases.id','=','purchase_descriptions.purchase_id')
         ->where(['purchases.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $purchase_sundry = DB::table('purchases')
         ->join('purchase_sundries','purchases.id','=','purchase_sundries.purchase_id')
         ->join('bill_sundrys','purchase_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchases.delete' => '0', 'purchases.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_sundry)>0){
         foreach ($purchase_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_amt = $tot_purchase_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_amt = $tot_purchase_amt - $value->amount;
            }
         }
      }
      //Sale
      $tot_sale_amt = DB::table('sales')
         ->join('sale_descriptions','sales.id','=','sale_descriptions.sale_id')
         ->where(['sales.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereRaw("STR_TO_DATE(sales.date,'%Y-%m-%d')>=STR_TO_DATE('".$from_date."','%Y-%m-%d')")
         ->whereRaw("STR_TO_DATE(sales.date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')")
         //->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $sale_sundry = DB::table('sales')
         ->join('sale_sundries','sales.id','=','sale_sundries.sale_id')
         ->join('bill_sundrys','sale_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales.delete' => '0', 'sales.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_sundry)>0){
         foreach ($sale_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_amt = $tot_sale_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_amt = $tot_sale_amt - $value->amount;
            }
         }
      }
      //Purchase Return
      $tot_purchase_return_amt = DB::table('purchase_returns')
         ->join('purchase_return_descriptions','purchase_returns.id','=','purchase_return_descriptions.purchase_return_id')
         ->where(['purchase_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $purchase_return_sundry = DB::table('purchase_returns')
         ->join('purchase_return_sundries','purchase_returns.id','=','purchase_return_sundries.purchase_return_id')
         ->join('bill_sundrys','purchase_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['purchase_returns.delete' => '0', 'purchase_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($purchase_return_sundry)>0){
         foreach ($purchase_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_purchase_return_amt = $tot_purchase_return_amt - $value->amount;
            }
         }
      }
      //Sale Return
      $tot_sale_return_amt = DB::table('sales_returns')
         ->join('sale_return_descriptions','sales_returns.id','=','sale_return_descriptions.sale_return_id')
         ->where(['sales_returns.delete' => '0', 'company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year])
         ->whereBetween('date', [$from_date, $to_date])
         ->get()
         ->sum("amount");
      $sale_return_sundry = DB::table('sales_returns')
         ->join('sale_return_sundries','sales_returns.id','=','sale_return_sundries.sale_return_id')
         ->join('bill_sundrys','sale_return_sundries.bill_sundry','=','bill_sundrys.id')
         ->where(['sales_returns.delete' => '0', 'sales_returns.company_id' => Session::get('user_company_id'),'financial_year'=>$financial_year,'adjust_purchase_amt'=>'Yes'])
         ->whereBetween('date', [$from_date, $to_date])
         ->select('bill_sundry_type','amount')
         ->get();
      if(count($sale_return_sundry)>0){
         foreach ($sale_return_sundry as $key => $value) {
            if($value->bill_sundry_type=="additive"){
               $tot_sale_return_amt = $tot_sale_return_amt + $value->amount;
            }else if($value->bill_sundry_type=="subtractive"){
               $tot_sale_return_amt = $tot_sale_return_amt - $value->amount;
            }
         }
      }
      //Direct Expensess
      $direct_expenses_account_id = Accounts::where('under_group','12')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','12')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $direct_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $direct_expenses_account_id = $direct_expenses_account_id->merge($direct_expenses_account_id1);      
      $direct_expenses = AccountLedger::whereIn('account_id',$direct_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      //InDirect Expensess
      $indirect_expenses_account_id = Accounts::where('under_group','15')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','15')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $indirect_expenses_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $indirect_expenses_account_id = $indirect_expenses_account_id->merge($indirect_expenses_account_id1);   
      $indirect_expenses = AccountLedger::whereIn('account_id',$indirect_expenses_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('debit');
      //Direct Income
      $direct_income_account_id = Accounts::where('under_group','13')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','13')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $direct_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $direct_income_account_id = $direct_income_account_id->merge($direct_income_account_id1);  
      $direct_income = AccountLedger::whereIn('account_id',$direct_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      //InDirect Income
      $indirect_income_account_id = Accounts::where('under_group','14')
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
      $account_group = AccountGroups::where('heading','14')
                     ->whereIn('company_id',[Session::get('user_company_id'),0])
                     ->where('heading_type','group')
                     ->pluck('id');
      $indirect_income_account_id1 = Accounts::whereIn('under_group',$account_group)
                                    ->whereIn('company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');      
      $indirect_income_account_id = $indirect_income_account_id->merge($indirect_income_account_id1);  
      $indirect_income = AccountLedger::whereIn('account_id',$indirect_income_account_id)
                  ->where('delete_status','0')
                  ->whereIn('company_id',[Session::get('user_company_id'),0])
                  ->whereBetween('txn_date', [$from_date, $to_date])
                  ->where('status','1')
                  ->where('financial_year',$financial_year)
                  ->sum('credit');
      //echo $stock_in_hand ."+". $tot_sale_amt ."+". $direct_income;die;
      $total_net_sale = $stock_in_hand + $tot_sale_amt + $direct_income + $indirect_income;
      $total_net_purchase = $opening_stock + $tot_purchase_amt + $direct_expenses + $indirect_expenses;
      $balance = $total_net_purchase - $total_net_sale;
      $profitloss = $balance;
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss]);
   }
   public function filter(Request $request){
      $financial_year = $request->financial_year;
      $y = explode("-",$financial_year);
      $from_date = $y[0]."-04-01";
      $from_date = date('Y-m-d',strtotime($from_date));
      $to_date = $y[1]."-03-31";
      $to_date = date('Y-m-d',strtotime($to_date));
      if(isset($request->from_date) && !empty($request->from_date) && isset($request->to_date) && !empty($request->to_date)){
         $from_date = $request->from_date;
         $to_date = $request->to_date;
      }
      $liability = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                 }
                     ])
                     ->where('bs_profile','1')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();
                     
      $assets = AccountHeading::with(['accountGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup.accountUnderGroup.account.accountLedger' => function($q)use($to_date){
                                    $q->where(function($q1) use ($to_date){
                                       $q1->where('company_id', '=', Session::get('user_company_id'));
                                       $q1->where('status', '1');
                                       $q1->where('delete_status', '0');
                                    })->where(function($q2) use ($to_date){
                                       $q2->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q2->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountWithHead.accountLedger' => function($q4)use($to_date){
                                    $q4->where(function($q5) use ($to_date){
                                       $q5->where('company_id', '=', Session::get('user_company_id'));
                                       $q5->where('status', '1');
                                       $q5->where('delete_status', '0');
                                    })->where(function($q6) use ($to_date){
                                       $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                       $q6->orWhere('entry_type','-1');
                                    });
                                 }
                                 ])->with(['accountGroup'=> function($q3){
                                    $q3->where('heading_type','head');
                                 }
                     ])
                     ->where('bs_profile','2')
                     ->where('status','1')
                     ->where('delete','0')
                     ->where('company_id','0')
                     ->get();
                     // echo "<pre>";
                     // print_r($assets->toArray());die;
      //Closing Stock      
      $open_date = $y[0]."-04-01";
      $open_date = date('Y-m-d',strtotime($open_date));
      $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));
      $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2) || (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=-1) GROUP BY item_id"));
      $result = array();
      foreach ($item_in_data as $element){
         $result[$element->item_id][] = round($element->total_price/$element->in_weight,2);
      }
      $stock_in_hand = 0;$total_weight = 0;
      foreach ($item as $key => $value){
         $remaining_weight = $value->in_weight - $value->out_weight;
         if (array_key_exists($value->item_id,$result)){
            $stock_in_hand = $stock_in_hand + $remaining_weight*$result[$value->item_id][0];
            $total_weight = $total_weight + $remaining_weight;
         }
      }
      $stock_in_hand = round($stock_in_hand,2);
      $profitloss = 0;
      
      return view('display/balanceSheet',['liability'=>$liability,'assets'=>$assets,'from_date'=>$from_date,'to_date'=>$to_date,"stock_in_hand"=>$stock_in_hand,"profitloss"=>$profitloss]);
   }
   public function groupBalanceByHead($id,$from_date,$to_date){
      $financial_year = Session::get('default_fy');
      $y = explode("-",$financial_year);
      $head = AccountHeading::select('name')
                              ->where('id',$id)
                              ->first();                              
      $group = AccountGroups::with(['account.accountLedger'=>function($q)use($to_date){
                                 $q->where(function($q1) use ($to_date){
                                    $q1->where('company_id', '=', Session::get('user_company_id'));
                                    $q1->where('status','1');
                                    $q1->where('delete_status','0'); 
                                 })->where(function($q6) use ($to_date){
                                    $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                    $q6->orWhere('entry_type','-1');
                                 });
                              }
                              ])->with(['account'=> function($q2){
                                 // $q2->whereIn('company_id1',[Session::get('user_company_id',0)]);
                                 // $q2->where('status','1');
                                 // $q2->where('delete','0');
                                 //$q2->orWhere('company_id',0);
                              }
                           ])
                           ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                           ->where('heading',$id)
                           ->where('status','1')
                           ->where('delete','0')
                           ->where('heading_type','head')
                           ->orWhere('heading_type','=','')
                           ->orderBy('name')
                           ->get();  
      $undergroup = AccountGroups::with(['accountUnderGroup.account.accountLedger'=>function($q)use($to_date){
                                 $q->where(function($q1) use ($to_date){
                                    $q1->where('company_id', '=', Session::get('user_company_id'));
                                    $q1->where('status','1');
                                    $q1->where('delete_status','0'); 
                                 })->where(function($q6) use ($to_date){
                                    $q6->whereRaw("STR_TO_DATE(txn_date,'%Y-%m-%d')<=STR_TO_DATE('".$to_date."','%Y-%m-%d')");
                                    $q6->orWhere('entry_type','-1');
                                 });
                              }
                           ])
                           ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                           ->where('heading',$id)
                           ->where('status','1')
                           ->where('delete','0')
                           ->where('heading_type','head')
                           //->orWhere('heading_type','=','')
                           ->orderBy('name')
                           ->get();
                           // echo "<pre>";
                           // print_r($undergroup->toArray());
                           // die;   
             //$group = $group->merge($undergroup);        
      $head_account = Accounts::withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where(function($q1) use ($to_date,$financial_year){
                                 $q1->where('financial_year', $financial_year);
                                 $q1->where('delete_status','0');
                                 $q1->where('company_id',Session::get('user_company_id'));
                              })->where(function($q2) use ($to_date){
                                 $q2->where('txn_date', '<=', $to_date);
                                 $q2->orWhere('entry_type','-1');
                              });                                 
                            }], 'debit')
                           ->withSum([
                            'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                              $query->where(function($q1) use ($to_date,$financial_year){
                                 $q1->where('financial_year', $financial_year);
                                 $q1->where('delete_status','0');
                                 $q1->where('company_id',Session::get('user_company_id'));
                              })->where(function($q2) use ($to_date){
                                 $q2->where('txn_date', '<=', $to_date);
                                 $q2->orWhere('entry_type','-1');
                              });                                 
                            }], 'credit')
                           ->where('under_group',$id)
                           ->where('under_group_type','head')
                           ->where('accounts.delete','0')
                           ->where('accounts.status','1')                           
                           ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                           ->orderBy('account_name')
                           ->get(); 
                                              
      // $heading = AccountHeading::with(['accountWithHead.accountLedger' => function($q4)use($to_date){
      //                               $q4->where(function($q5) use ($to_date){
      //                                  $q5->where('company_id', '=', Session::get('user_company_id'));
      //                               })->where(function($q6) use ($to_date){
      //                                  $q6->where('txn_date', '<=', $to_date);
      //                                  $q6->orWhere('entry_type','-1');
      //                               });
      //                            }
      //                            ])->with(['accountWithHead'=> function($q1){
      //                            $q1->whereIn('company_id',[Session::get('user_company_id',0)]);
      //                         }
      //                      ])
      //                      ->whereIn('company_id',[Session::get('user_company_id'),0])
      //                      ->where('id',$id) 
      //                      ->where('status','1')
      //                      ->where('delete','0')                             
      //                      ->orderBy('name')
      //                      ->get();
      //Closing Stock      
      $open_date = $y[0]."-04-01";
      $open_date = date('Y-m-d',strtotime($open_date));
      $item = DB::select(DB::raw("SELECT item_id,SUM(total_price) as total_price,SUM(in_weight) as in_weight,SUM(out_weight) as out_weight,manage_items.name,units.name as uname FROM item_ledger inner join manage_items on item_ledger.item_id=manage_items.id inner join units on manage_items.u_name=units.id WHERE item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and item_ledger.status='1' and g_name!='' and item_ledger.delete_status='0' GROUP BY item_id order by manage_items.name"));
      $item_in_data = DB::select(DB::raw("SELECT SUM(total_price) as total_price,SUM(in_weight) as in_weight,item_id FROM item_ledger WHERE (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=2) || (item_ledger.company_id='".Session::get('user_company_id')."' and STR_TO_DATE(txn_date, '%Y-%m-%d')>=STR_TO_DATE('".$open_date."', '%Y-%m-%d') and STR_TO_DATE(txn_date, '%Y-%m-%d')<=STR_TO_DATE('".$to_date."', '%Y-%m-%d') and status='1' and delete_status='0' and in_weight!='' and source=-1) GROUP BY item_id"));
      $result = array();
      foreach ($item_in_data as $element){
         $result[$element->item_id][] = round($element->total_price/$element->in_weight,2);
      }
      $stock_in_hand = 0;$total_weight = 0;
      foreach ($item as $key => $value){
         $remaining_weight = $value->in_weight - $value->out_weight;
         if (array_key_exists($value->item_id,$result)){
            $stock_in_hand = $stock_in_hand + $remaining_weight*$result[$value->item_id][0];
            $total_weight = $total_weight + $remaining_weight;
         }
      }
      $stock_in_hand = round($stock_in_hand,2);
      return view('display/group_balance_by_head',["from_date"=>$from_date,"to_date"=>$to_date,"head"=>$head,"group"=>$group,"head_account"=>$head_account,"stock_in_hand"=>$stock_in_hand,"undergroup"=>$undergroup]);
   }
   public function accountBalanceByGroup(Request $request,$id,$from_date,$to_date,$type){
      $financial_year = Session::get('default_fy');
      
      if($type=="head"){
         $group = AccountHeading::select('name')
                              ->where('id',$id)
                               ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                              ->first();
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'debit')
                              ->withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'credit')
                              ->where('under_group',$id)
                              ->where('under_group_type',$type)
                              ->where('accounts.delete','0')
                              ->where('accounts.status','1')                           
                              ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
      }else if($type=="group"){
         $group = AccountGroups::select('name')
                                 ->where('id',$id)
                                 ->whereIn('company_id',[Session::get('user_company_id'),'0'])
                                 ->first();
         $account = Accounts::withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'debit')
                              ->withSum([
                               'accountLedger' => function ($query) use ($financial_year,$from_date,$to_date) { 
                                 $query->where(function($q1) use ($to_date,$financial_year){
                                    $q1->where('financial_year', $financial_year);
                                    $q1->where('delete_status','0');
                                    $q1->where('company_id',Session::get('user_company_id'));
                                 })->where(function($q2) use ($to_date){
                                    $q2->where('txn_date', '<=', $to_date);
                                    $q2->orWhere('entry_type','-1');
                                 });                                 
                               }], 'credit')
                              ->where('under_group',$id)
                              ->where('under_group_type',$type)
                              ->where('accounts.delete','0')
                              ->where('accounts.status','1')                           
                              ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                              ->orderBy('account_name')
                              ->get();
         //Inner Group
         $inner_group = AccountGroups::select('id','name as account_name')
                                       ->where('heading',$id)
                                       ->whereIn('company_id',[Session::get('user_company_id'),0])
                                       ->where('heading_type','group')
                                      ->get();
         foreach ($inner_group as $key => $value) {
            $account_id = Accounts::where('under_group',$value->id)
                                    ->where('accounts.delete','0')
                                    ->where('accounts.status','1')
                                    ->whereIn('accounts.company_id',[Session::get('user_company_id'),0])
                                    ->pluck('id');
                                    
                                    
            $leger = AccountLedger::whereIn('account_id',$account_id)
                           ->where('financial_year',$financial_year)
                           ->where('delete_status','0')
                           ->where('txn_date', '<=', $to_date)
                           ->where('delete_status','0')
                           ->whereIn('company_id',[Session::get('user_company_id'),0])
                           ->get();                       
            $inner_group[$key]->account_ledger_sum_debit = $leger->sum('debit');         
            $inner_group[$key]->account_ledger_sum_credit = $leger->sum('credit');                     
            $inner_group[$key]->type = 1;
         }
         $account = $account->merge($inner_group);
      }
      return view('display/account_balance_by_group')->with('data',$account)->with('group',$group)->with('financial_year',$financial_year)->with('type',$type)->with('from_date',$from_date)->with('to_date',$to_date);
   }
}