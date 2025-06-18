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
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="{{url('profitloss-filter')}}?financial_year={{$selected_year}}&from_date={{$from_date}}&to_date={{$to_date}}">Profit & Loss</a>
                     </li>
                     <!-- <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="{{url('sale-by-month/')}}/{{$selected_year}}">Sale By Month</a>
                     </li> -->
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12 text-decoration-none" href="#">Sale Detail</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Sale Detail</h5>
                  <div class="d-md-flex d-block">                  
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required id="from_date" value="{{date('Y-m-d',strtotime($from_date))}}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required id="to_date" value="{{date('Y-m-d',strtotime($to_date))}}">
                     </div>
                     <button type="button" class="btn btn-info searchByDate" style="margin-left: 5px;">Search</button>
                  </div>
               <p><input type="checkbox" class="custom-checkbox-input detailed"> Detailed</p>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <table class="table table-bordered">
                  <thead>
                     <tr>
                        <th style="width: 8%">Date</th>
                        <th>Particular</th>
                        <th style="text-align:right;">Invoice No.</th>
                        <th style="text-align:right;">Net Total</th>
                        <th class="td_detail" style="text-align:right;display: none;">Sale</th>
                        @foreach($bill_sundray as $bsundry)
                           <th class="td_detail" style="text-align:right;display: none;">{{$bsundry->name}}</th>
                        @endforeach
                        <th class="td_detail" style="text-align:right;display: none;">Grand Total</th>
                     </tr>                     
                  </thead>
                  <tbody>
                     @php $net_total = 0;$sundry_sum_arr = [];$grand_total = 0; $sale_total = 0;
                     $merge_arr = []; @endphp

                     @foreach($sale as $key => $value)
                           @php $adjust_sundry_amount = 0; @endphp
                           @foreach($value['saleSundry'] as $v1)
                              @if($v1->adjust_sale_amt=="Yes")
                                 @if($v1->bill_sundry_type=="subtractive")
                                    @php $adjust_sundry_amount = $adjust_sundry_amount - $v1->amount; @endphp
                                 @elseif($v1->bill_sundry_type=="additive")
                                    @php $adjust_sundry_amount = $adjust_sundry_amount + $v1->amount; @endphp
                                 @endif
                              @endif
                           @endforeach
                        @php 
                         array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['voucher_no_prefix'],"net_total"=>$value['sale_description_sum_amount'] + $adjust_sundry_amount,'sale_total'=>$value['sale_description_sum_amount'],'sundry'=>$value['saleSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>'','type'=>'sale'));
                         @endphp
                     @endforeach  
                     @foreach($purchase_return as $key => $value)                           
                        @php $adjust_sundry_amount = 0; @endphp
                        @foreach($value['purchaseReturnSundry'] as $v1)
                           @if($v1->adjust_sale_amt=="Yes")
                              @if($v1->bill_sundry_type=="subtractive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount - $v1->amount; @endphp
                              @elseif($v1->bill_sundry_type=="additive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount + $v1->amount; @endphp
                              @endif
                           @endif
                        @endforeach
                        @php 
                           array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['sr_prefix'],"net_total"=>$value['purchase_return_description_sum_amount'] + $adjust_sundry_amount,'sale_total'=>$value['purchase_retuen_description_sum_amount'],'sundry'=>$value['purchaseReturnSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>$value['voucher_type'],'type'=>'purchase_return'));
                        @endphp
                     @endforeach
                     @foreach($sale_return as $key => $value)                           
                        @php $adjust_sundry_amount = 0; @endphp
                        @foreach($value['saleReturnSundry'] as $v1)
                           @if($v1->adjust_sale_amt=="Yes")
                              @if($v1->bill_sundry_type=="subtractive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount - $v1->amount; @endphp
                              @elseif($v1->bill_sundry_type=="additive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount + $v1->amount; @endphp
                              @endif
                           @endif
                        @endforeach
                        @php 
                           array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['sr_prefix'],"net_total"=>$value['sale_return_descriptions_sum_amount'] + $adjust_sundry_amount,'sale_total'=>$value['sale_return_descriptions_sum_amount'],'sundry'=>$value['saleReturnSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>$value['voucher_type'],'type'=>'sale_return'));
                        @endphp
                     @endforeach
                     @php 
                        usort($merge_arr, function ($a, $b) {
                           return strtotime($a['date']) <=> strtotime($b['date']);
                        });
                     @endphp
                     @foreach($merge_arr as $key => $value)
                        <tr class="view_invoice" data-id="{{$value['id']}}" data-type="{{$value['type']}}" style="cursor:pointer;">
                           <td style="text-align:center;">{{date('d-m-Y',strtotime($value['date']))}}</td>
                           <td style="text-align:left;">{{$value['account_name']}}</td>
                           <td style="text-align:right;">{{$value['voucher_no']}}</td>
                          <td style="text-align:right;">
                              {{number_format($value['net_total'],2)}}
                              @php 
                                 if($value['type']=="sale_return"){
                                    $net_total = $net_total - $value['net_total']; 
                                 }else{
                                    $net_total = $net_total + $value['net_total']; 
                                 }                                 
                              @endphp
                           </td>
                           <td class="td_detail" style="text-align:right;display: none;">
                              {{number_format($value['sale_total'],2)}}
                              @php 
                                 if($value['type']=="sale_return"){
                                    $sale_total = $sale_total - $value['sale_total'];  
                                 }else{
                                    $sale_total = $sale_total + $value['sale_total'];  
                                 }
                              @endphp
                           </td>
                           @foreach($bill_sundray as $bsundry)
                              <td class="td_detail" style="text-align:right;display: none;">
                                 @php $freight = '0.00'; @endphp
                                 @foreach($value['sundry'] as $v1)
                                    @if($v1['bill_sundry']==$bsundry->id)
                                       @php 
                                          $freight = $v1['amount'];
                                       @endphp
                                    @endif
                                 @endforeach
                                 {{number_format($freight,2)}}
                                 @php 
                                    if(isset($sundry_sum_arr[$bsundry->id])){
                                       $sundry_sum_arr[$bsundry->id] = $sundry_sum_arr[$bsundry->id] + $freight;
                                    }else{
                                       $sundry_sum_arr[$bsundry->id] = $freight;
                                    }
                                 @endphp
                              </td>
                           @endforeach                           
                           <td class="td_detail" style="text-align:right;display: none;">
                              {{number_format($value['grand_total'],2)}}
                              @php 
                                 if($value['type']=="sale_return"){
                                    $grand_total = $grand_total - $value['grand_total']; 
                                 }else{
                                    $grand_total = $grand_total + $value['grand_total'];  
                                 }
                              @endphp
                           </td>                           
                        </tr>
                     @endforeach
                     <tr>
                        <th style="text-align:center;"></th>
                        <th style="text-align:center;"></th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:right;">{{number_format($net_total,2)}}</th>
                        <th class="td_detail" style="text-align:right;display: none;">{{number_format($sale_total,2)}}</th>
                        @foreach($bill_sundray as $bsundry)
                        
                           <th class="td_detail" style="text-align:right;display: none;">
                              @php 
                              if(isset($sundry_sum_arr[$bsundry->id])){
                                 echo number_format($sundry_sum_arr[$bsundry->id],2);
                              }
                              @endphp
                           </th>
                        @endforeach
                        <th class="td_detail" style="text-align:right;display: none;">{{number_format($grand_total,2)}}</th>
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
      $(".detailed").click(function(){
         if($(this).prop('checked')==true){
            $(".td_detail").show();
         }else{
            $(".td_detail").hide();
         }
      });
      $(".view_invoice").click(function(){
         
         if($(this).attr('data-type')=="sale"){
            window.location = "{{route('sale-invoice')}}/"+$(this).attr('data-id');
         }else if($(this).attr('data-type')=="purchase_return"){
            window.location = "{{route('purchase-return-invoice')}}/"+$(this).attr('data-id');
         }else if($(this).attr('data-type')=="sale_return"){
            window.location = "{{route('sale-return-invoice')}}/"+$(this).attr('data-id');
         }
      });
      $(".searchByDate").click(function(){
         let from_date = $("#from_date").val();
         let to_date = $("#to_date").val();
         if(from_date!="" && to_date!=""){
            window.location = "{{url('sale-by-month-detail')}}/{{$selected_year}}/"+from_date+"/"+to_date;
         }
      });
   })
</script>
@endsection