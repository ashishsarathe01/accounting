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
                     @php $debit_total = 0;$credit_total = 0; @endphp
                     @foreach($group as $value)
                        @php $debit = 0;$credit = 0; @endphp
                        @if($value->account)
                           @foreach($value->account as $v2)
                              @foreach($v2->accountLedger as $v3)
                                 @php 
                                    if($v3->debit!=""){
                                       $debit = $debit + $v3->debit;
                                    }
                                    if($v3->credit!=""){
                                       $credit = $credit + $v3->credit; 
                                    }                                    
                                 @endphp
                              @endforeach
                           @endforeach
                        @endif
                        @php $balance = $debit - $credit;@endphp
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
                                 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    echo number_format($stock_in_hand,2);
                                 }else{
                                    echo money_format('%!i', $stock_in_hand);
                                 }
                              }else{
                                 if($balance>=0){                                    
                                    $debit_total = $debit_total + $balance; 
                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                       echo number_format($balance,2);
                                    }else{
                                       echo money_format('%!i', $balance);
                                    }                           
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
                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                       echo number_format(abs($balance),2);
                                    }else{
                                       echo money_format('%!i', abs($balance));
                                    }                           
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
                              if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                 echo number_format($value1->account_ledger_sum_debit,2);
                              }else{
                                 echo money_format('%!i', $value1->account_ledger_sum_debit);
                              }
                              ?>
                           </td>
                           <td style="text-align:right;">
                              <?php 
                              $credit_total = $credit_total + $value1->account_ledger_sum_credit;
                              setlocale(LC_MONETARY, 'en_IN');                              
                              if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                 echo number_format($value1->account_ledger_sum_credit,2);
                              }else{
                                 echo money_format('%!i', $value1->account_ledger_sum_credit);
                              }
                              ?>
                           </td>
                        </tr>
                     <?php } ?>
                     <tr>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">
                           <?php                              
                           if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                              echo number_format($debit_total,2);
                           }else{
                              echo money_format('%!i', $debit_total);
                           }
                           ?>
                        </th>
                        <th style="text-align:right;">
                           <?php                              
                           if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                              echo number_format($credit_total,2);
                           }else{
                              echo money_format('%!i', $credit_total);
                           }
                           ?>
                        </th>
                     </tr> 
                     <tr>
                        <th colspan="4">Balance : 
                           <?php 
                           if($debit_total-$credit_total<0){
                              echo str_replace("-","",number_format($debit_total-$credit_total)).' Cr'; 
                           }else{ 
                              echo number_format($debit_total-$credit_total).' Dr'; 
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