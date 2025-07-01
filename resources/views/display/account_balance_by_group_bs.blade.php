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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="{{url('profitloss-filter')}}?financial_year={{$financial_year}}&from_date={{$from_date}}&to_date={{$to_date}}">Profit & Loss</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Account Balance By Group</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">{{$group->name}}</h5>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th style="text-align:left;">Account</th>
                        <th style="text-align:right;">Debit(Rs.)</th>
                        <th style="text-align:right;">Credit(Rs.)</th>
                     </tr>                     
                  </thead>
                  <tbody>
                     @php $debit_balance = 0;$credit_balance = 0; @endphp
                     @foreach($data as $key => $value)
                        @php $balance = $value['account_ledger_sum_debit'] - $value['account_ledger_sum_credit'];@endphp
                        <tr class="get_info" data-id="{{$value['id']}}" data-financial_year="{{$financial_year}}" data-type="{{$value['type']}}" style="cursor: pointer;">
                           <td style="text-align:left;">{{$value['account_name']}}</td>
                           <td style="text-align:right;">
                              @if($balance>=0)
                                 {{number_format($balance,2)}}
                              @endif  
                           </td>
                           <td style="text-align:right;">                              
                              @if($balance<0)
                                 {{number_format(abs($balance),2)}}
                              @endif                              
                           </td>
                        </tr>
                        @php 
                           if($balance>=0){
                              $debit_balance = $debit_balance + $balance;
                           }
                           
                           if($balance<0){
                              $credit_balance = $credit_balance + abs($balance);
                           } 
                        @endphp
                     @endforeach  
                     <tr>
                        <th style="text-align:left;">Total</th>
                        <th style="text-align:right;">{{number_format($debit_balance,2)}}</th>
                        <th style="text-align:right;">{{number_format($credit_balance,2)}}</th>
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
      $(".get_info").click(function(){
         if($(this).attr('data-type')==1){
            window.location = "{{url('account-balance-by-group')}}/bs/"+$(this).attr('data-id')+"/{{$from_date}}/{{$to_date}}/group ";
         }else{
            window.location = "{{url('accountledger-filter')}}/?party="+$(this).attr('data-id')+"&from_date={{$from_date}}&to_date={{$to_date}}";            
         }         
      });
   });
</script>
@endsection