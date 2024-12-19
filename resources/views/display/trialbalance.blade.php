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
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
               <nav>
                  <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                     <li class="breadcrumb-item">Dashboard</li>
                     <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                     <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Balance Sheet</li>
                  </ol>
               </nav>
               <form class="" id="frm" method="GET" action="{{ route('trialbalance.filter') }}">
                  <div class="d-xxl-flex d-block  align-items-center"> 
                     <select class="form-select w-min-230 ms-xxl-2" aria-label="Default select example" name="type">
                        <option value="open">Opening Trial Balance</option>
                        <option value="close">Closing Trial Balance</option>
                     </select>
                     <div class="calender-administrator w-min-150 ms-xxl-2">
                        <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <button class="btn btn-info ms-xxl-2 next_btn">Next</button>
                  </div>
               </form>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Trial Balance</h5>               
            </div>
            <div class="display-sale-month overflow-auto  bg-white table-view shadow-sm">
               <table id="" class="table-striped table m-0 shadow-sm ">
                  <thead>
                     <tr class=" font-14  fw-bold">
                        <th class="w-min-230 border-none py-12 px-3">Account</th>                        
                        <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Debit Balance</th>
                        <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Credit Balance</th>
                     </tr>
                  </thead>
                  <tbody>
                     @php $total_debit_amount = 0;$total_credit_amount = 0; setlocale(LC_MONETARY, 'en_IN');@endphp
                     @foreach($account as $value)
                        @php $balance = $value->debit - $value->credit; @endphp
                        <tr class=" font-14 font-heading bg-white">
                           <td class="w-min-230 fw-500 py-12 px-3">{{$value->account_name}}</td>
                           <td class="w-min-180 fw-500 py-12 px-3 border-left-divider detail_td" style="text-align: right;">
                              @if($balance>=0)
                                 @if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                                    @php echo number_format($balance,2); @endphp
                                 @else
                                    @php echo money_format('%!i', $balance); @endphp
                                 @endif 
                                 @php 
                                    $total_debit_amount = $total_debit_amount + $balance;
                                 @endphp                   
                              @else
                                 @php echo '0.00'; @endphp
                              @endif                              
                           </td>
                           <td class="w-min-180 fw-500 py-12 px-3 border-left-divider detail_td" style="text-align: right;">
                              @if($balance<0)
                                 @if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                                    @php echo number_format(abs($balance),2); @endphp
                                 @else
                                    @php echo money_format('%!i', abs($balance)); @endphp
                                 @endif 
                                 @php 
                                    $total_credit_amount = $total_credit_amount + abs($balance);
                                 @endphp                         
                              @else
                                 @php echo '0.00'; @endphp
                              @endif                              
                           </td>                           
                        </tr>                        
                     @endforeach
                     @php
                     $diff = $total_debit_amount - $total_credit_amount;  
                     $diff = round($diff,2);
                     @endphp
                  
                     @if($diff!=0)
                        <tr>
                           <th>Opening Difference</th>
                           <th style="text-align: right;">@if($diff<0) {{money_format('%!i', $diff)}} @endif</th>
                           <th style="text-align: right;">@if($diff>0) {{money_format('%!i', $diff)}} @endif</th>
                        </tr>
                     @endif
                     <tr>
                        <th>Total</th>
                        <th style="text-align: right;">@if($diff!=0) @if($diff<0) {{money_format('%!i', $total_credit_amount)}} @else  {{money_format('%!i', $total_debit_amount)}}@endif @else {{money_format('%!i', $total_debit_amount)}} @endif</th>
                        <th style="text-align: right;">@if($diff!=0) @if($diff<0) {{money_format('%!i', $total_credit_amount)}} @else  {{money_format('%!i', $total_debit_amount)}}@endif @else {{money_format('%!i', $total_debit_amount)}} @endif</th>
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
   $(document).ready(function() {
      $("#detailed").click(function(){
         if($(this).prop('checked')==true){
            $("#from_date").show();
            $(".detail_td").show();
         }else{
            $("#from_date").hide();
            $(".detail_td").hide();
         }
      });
        
   });
</script>
@endsection