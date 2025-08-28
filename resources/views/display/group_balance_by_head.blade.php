@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">           
            <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="{{url('balancesheet')}}">Balance Sheet</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Group Balances</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">{{$head->name}}</h5><span>At End Of {{date('d-m-Y',strtotime($to_date))}}</span>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th style="text-align:left;">Account/Group</th>
                        <th style="text-align:left;">Type</th>
                        <th style="text-align:right;">Debit</th>
                        <th style="text-align:right;">Credit</th>
                     </tr>                     
                  </thead>
                  <tbody>
                     @php 
                     $undergroup_result = array();
                    
                     foreach ($undergroup->toArray() as $element){ 
                        if(count($element['account_under_group'])>0){
                           $undergroup_result[$element['id']][] = $element['account_under_group'];
                        }
                     } 
                     $debit_total = 0;$credit_total = 0; 
                     $balance = 0;
                     @endphp
                     @foreach($group as $value)
                        @php $debit = 0;$credit = 0; @endphp
                        @if($value->account)
                           @foreach($value->account as $v2)                              
                              @php 
                                 if($v2->account_ledger_sum_debit!=""){
                                    $debit = $debit + $v2->account_ledger_sum_debit;
                                 }
                                 if($v2->account_ledger_sum_credit!=""){
                                    $credit = $credit + $v2->account_ledger_sum_credit; 
                                 }                                    
                              @endphp                              
                           @endforeach
                        @endif
                        @php 
                                                   
                        if(isset($undergroup_result[$value->id])){
                           foreach($undergroup_result[$value->id] as $a){
                              foreach($a as $a1){                                    
                                 foreach($a1['account'] as $a2){
                                    if($a2['account_ledger_sum_debit']!=""){
                                       $debit = $debit + $a2['account_ledger_sum_debit'];
                                    }
                                    if($a2['account_ledger_sum_credit']!=""){
                                       $credit = $credit + $a2['account_ledger_sum_credit'];
                                    }
                                 }
                                 foreach($a1['account_under_group'] as $a3){                                       
                                    foreach($a3['account'] as $a4){
                                       if($a4['account_ledger_sum_debit']!=""){
                                          $debit = $debit + $a4['account_ledger_sum_debit'];
                                       }
                                       if($a4['account_ledger_sum_credit']!=""){
                                          $credit = $credit + $a4['account_ledger_sum_credit'];
                                       }
                                    }
                                    foreach($a3['account_under_group'] as $a5){                                       
                                       foreach($a5['account'] as $a6){
                                          if($a6['account_ledger_sum_debit']!=""){
                                             $debit = $debit + $a6['account_ledger_sum_debit'];
                                          }
                                          if($a6['account_ledger_sum_credit']!=""){
                                             $credit = $credit + $a6['account_ledger_sum_credit'];
                                          }
                                       }
                                       foreach($a5['account_under_group'] as $a7){                                       
                                          foreach($a7['account'] as $a8){
                                             if($a8['account_ledger_sum_debit']!=""){
                                                $debit = $debit + $a8['account_ledger_sum_debit'];
                                             }
                                             if($a8['account_ledger_sum_credit']!=""){
                                                $credit = $credit + $a8['account_ledger_sum_credit'];
                                             }
                                          }
                                          foreach($a7['account_under_group'] as $a9){                                       
                                             foreach($a9['account'] as $a10){
                                                if($a10['account_ledger_sum_debit']!=""){
                                                   $debit = $debit + $a10['account_ledger_sum_debit'];
                                                }
                                                if($a10['account_ledger_sum_credit']!=""){
                                                   $credit = $credit + $a10['account_ledger_sum_credit'];
                                                }
                                             }

                                             
                                          }
                                          
                                       }
                                       
                                    }

                                 }
                              }                                 
                           }
                        }
                        
                        $balance = $debit - $credit;                        
                        @endphp
                        <tr>
                           <td>
                              @if($value->stock_in_hand==1)
                                 <a href="{{url('itemledger-filter')}}?&items_id=all&from_date={{$from_date}}&to_date={{$to_date}}">{{$value->name}}</a>
                              @else 
                                 <a href="{{url('account-balance-by-group/bs')}}/{{$value->id}}/{{$from_date}}/{{$to_date}}/group">{{$value->name}}</a>
                              @endif
                              
                           </td>
                           <td>Group</td>
                           <td style="text-align:right;">
                              <?php 
                              setlocale(LC_MONETARY, 'en_IN'); 
                              if($value->stock_in_hand==1){
                                 $debit_total = $debit_total + $stock_in_hand;                    
                                echo formatIndianNumber($stock_in_hand);
                              }else{
                                 if($balance>=0){                                    
                                    $debit_total = $debit_total + $balance; 
                                    echo formatIndianNumber($balance);                          
                                 }else{
                                    echo '0.00';
                                 }
                              }                              
                              ?>
                           </td>
                           <td style="text-align:right;">
                              <?php 
                              if($value->stock_in_hand==0){
                                 if($balance<0){                                     
                                    $credit_total = $credit_total + abs($balance); 
                                    echo formatIndianNumber(abs($balance));                          
                                 }else{
                                    echo '0.00';
                                 }
                              }else{
                                 echo '0.00';
                              }
                              ?>
                           </td>
                        </tr>
                     @endforeach
                     <?php 
                     
                     foreach($head_account as $value1){
                        $debit = 0;$credit = 0;                        
                        ?>
                        <tr class="get_info" data-id="{{$value1['id']}}" style="cursor: pointer;">
                           <td>{{$value1->account_name}}</td>
                           <td>Account</td>
                           <td style="text-align:right;">
                              <?php 
                              $debit_total = $debit_total + $value1->account_ledger_sum_debit;
                              setlocale(LC_MONETARY, 'en_IN');                              
                              echo formatIndianNumber($value1->account_ledger_sum_debit);
                              ?>
                           </td>
                           <td style="text-align:right;">
                              <?php 
                              $credit_total = $credit_total + $value1->account_ledger_sum_credit;
                              setlocale(LC_MONETARY, 'en_IN');                              
                              echo formatIndianNumber($value1->account_ledger_sum_credit);
                              ?>
                           </td>
                        </tr>
                     <?php } ?>
                     <tr>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">
                           <?php                              
                           echo formatIndianNumber($debit_total);
                           ?>
                        </th>
                        <th style="text-align:right;">
                           <?php                              
                           echo formatIndianNumber($credit_total);
                           ?>
                        </th>
                     </tr> 
                     <tr>
                        <th colspan="4">Balance : 
                           <?php 
                           $bal = $debit_total - $credit_total;
                           if($bal>=0){
                              $balance = $bal;
                              echo formatIndianNumber($bal).' Dr';
                           }else{
                              $balance = abs($bal);
                              echo formatIndianNumber(abs($bal)).' Cr';
                           }
                           
                           ?>
                        </th>
                     </tr>               
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(document).ready(function(){
         $(".get_info").click(function(){
            window.location = "{{url('accountledger-filter')}}/?party="+$(this).attr('data-id')+"&from_date={{$from_date}}&to_date={{$to_date}}";  
         });
      });
   });
</script>
@endsection