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
            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Purchase Detail</h5>
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
                        <th class="td_detail" style="text-align:right;display: none;">Purchase</th>
                        @foreach($bill_sundray as $bsundry)
                           <th class="td_detail" style="text-align:right;display: none;">{{$bsundry->name}}</th>
                        @endforeach
                        <th class="td_detail" style="text-align:right;display: none;">Grand Total</th>
                     </tr>                     
                  </thead>
                  <tbody>
                     @php $net_total = 0;$sundry_sum_arr = [];$grand_total = 0;$purchase_total = 0; $merge_arr = [];@endphp
                     @foreach($purchase as $key => $value)
                        @php $adjust_sundry_amount = 0; @endphp
                        @foreach($value['purchaseSundry'] as $v1)
                           @if($v1->adjust_sale_amt=="Yes")
                              @if($v1->bill_sundry_type=="subtractive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount - $v1->amount; @endphp
                              @elseif($v1->bill_sundry_type=="additive")
                                 @php $adjust_sundry_amount = $adjust_sundry_amount + $v1->amount; @endphp
                              @endif
                           @endif
                        @endforeach
                        @php 
                           array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['voucher_no'],"net_total"=>$value['purchase_description_sum_amount'] + $adjust_sundry_amount,'purchase_total'=>$value['purchase_description_sum_amount'],'sundry'=>$value['purchaseSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>'','type'=>'purchase'));
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
                           array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['sr_prefix'],"net_total"=>$value['purchase_return_description_sum_amount'] + $adjust_sundry_amount,'purchase_total'=>$value['purchase_retuen_description_sum_amount'],'sundry'=>$value['purchaseReturnSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>$value['voucher_type'],'type'=>'purchase_return'));
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
                           array_push($merge_arr,array('id'=>$value['id'],'date'=>$value['date'],'account_name'=>$value['account']['account_name'],'voucher_no'=>$value['sr_prefix'],"net_total"=>$value['sale_return_descriptions_sum_amount'] + $adjust_sundry_amount,'purchase_total'=>$value['sale_return_descriptions_sum_amount'],'sundry'=>$value['saleReturnSundry']->toArray(),'grand_total'=>$value['total'],'voucher_type'=>$value['voucher_type'],'type'=>'sale_return'));
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
                                 if($value['type']=="purchase_return"){
                                    $net_total = $net_total - $value['net_total']; 
                                 }else{
                                    $net_total = $net_total + $value['net_total']; 
                                 }                                 
                              @endphp
                           </td>
                           <td class="td_detail" style="text-align:right;display: none;">
                              {{number_format($value['purchase_total'],2)}}
                              @php 
                                 if($value['type']=="purchase_return"){
                                    $purchase_total = $purchase_total - $value['purchase_total'];  
                                 }else{
                                    $purchase_total = $purchase_total + $value['purchase_total'];  
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
                                 if($value['type']=="purchase_return"){
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
                        <th class="td_detail" style="text-align:right;display: none;">{{number_format($purchase_total,2)}}</th>
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
         if($(this).attr('data-type')=="purchase"){
            window.location = "{{route('purchase-invoice')}}/"+$(this).attr('data-id');
         }else if($(this).attr('data-type')=="purchase_return"){
            window.location = "{{route('purchase-return-invoice')}}/"+$(this).attr('data-id');
         }else if($(this).attr('data-type')=="sale_return"){
            window.location = "{{route('sale-return-invoice')}}/"+$(this).attr('data-id');
         }
         
      });
   })
</script>
@endsection