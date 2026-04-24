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
               <h5 class="master-table-title m-0 py-2">Trial Balance</h5> 
                             
                           
            </div>
            <div class="display-sale-month overflow-auto  bg-white table-view shadow-sm">
               <?php 
               if(!isset($_GET['trial_balance_by']) || (isset($_GET['trial_balance_by']) && $_GET['trial_balance_by']=="by_account")){?>
                  <table class="table-striped table m-0 shadow-sm" >
                     <thead>
                        <tr class=" font-14  fw-bold">
                           <th class="w-min-230 border-none py-12 px-3">Account</th>                        
                           <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Debit Balance</th>
                           <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Credit Balance</th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $total_debit_amount = 0;$total_credit_amount = 0; $total_debit_amount1 = 0;$total_credit_amount1 = 0;@endphp
                        @foreach($accounts as $value)
                        @php $balance = $value['debit'] - $value['credit']; $balance = round($balance,2);$total_debit_amount1 = $total_debit_amount1 + $value['debit']; $total_credit_amount1 = $total_credit_amount1 + abs($value['credit']); 
                                if($balance==0){
                                    continue;
                                }
                                @endphp
                           <tr class=" font-14 font-heading bg-white view_account_tr" data-id="{{$value['id']}}" data-type="" data-under="" style="cursor: pointer;">
                              <td class="w-min-230 fw-500 py-12 px-3">{{$value['account_name']}}</td>
                              <td class="w-min-180 fw-500 py-12 px-3 border-left-divider detail_td" style="text-align: right;">
                                
                                 @if($balance>=0)
                                    @php echo formatIndianNumber($balance,2); @endphp
                                    @php 
                                       $total_debit_amount = $total_debit_amount + $balance;
                                    @endphp                   
                                 @else
                                    @php echo '0.00'; @endphp
                                 @endif                              
                              </td>
                              <td class="w-min-180 fw-500 py-12 px-3 border-left-divider detail_td" style="text-align: right;">
                                 @if($balance<=0)
                                    @php echo formatIndianNumber(abs($balance),2);
                                       $total_credit_amount = $total_credit_amount + abs($balance);
                                    @endphp                         
                                 @else
                                    @php echo '0.00'; @endphp
                                 @endif                              
                              </td>                           
                           </tr>                        
                        @endforeach
                        
                        <?php 
                            $total_debit_amount = $total_debit_amount + $stock_in_hand;
                            $total_debit_amount = $total_credit_amount - $current_journal_amount;
                            if($profit_loss_amount<0){
                                $total_credit_amount = $total_credit_amount + abs($profit_loss_amount);
                            }else{
                                $total_debit_amount = $total_debit_amount + abs($profit_loss_amount);
                            }
                          if($prev_year_profitloss!=0){
                              if($prev_year_profit_status==1){
                                 $ac_name = "UNADJUSTED PROFIT AMOUNT (".$prevFy.")";
                                 $credit = $prev_year_profitloss;
                                 $total_credit_amount = $total_credit_amount + $prev_year_profitloss;
                                 $debit = 0; 
                              }else{
                                 $ac_name = "UNADJUSTED LOSS AMOUNT (".$prevFy.")";
                                 $debit = $prev_year_profitloss;
                                 $total_debit_amount = $total_debit_amount + $prev_year_profitloss;
                                 $credit = 0; 
                              }  
                              echo '<tr class="font-14 font-heading bg-white"><td class="w-min-230 fw-500 py-12 px-3">'.$ac_name.'</td><td class="w-min-180 fw-500 py-12 px-3 border-left-divider" style="text-align: right;">'.formatIndianNumber($debit).'</td><td class="w-min-180 fw-500 py-12 px-3 border-left-divider detail_td" style="text-align: right;">'.formatIndianNumber($credit).'</td></tr>';
                          }
                        ?>
                        
                        @php
                        echo $total_debit_amount1."***".$total_credit_amount1;
                        $diff = $total_debit_amount - $total_credit_amount;  
                        $diff = round($diff,2);
                        @endphp
                     
                        @if($diff!=0)
                           <tr>
                                 <th>Opening Difference</th>
                                 <th style="text-align: right;">@if($diff<0) {{formatIndianNumber($diff)}} @endif</th>
                                 <th style="text-align: right;">@if($diff>0) {{formatIndianNumber($diff)}} @endif</th>
                              </tr>
                           @endif
                           <tr>
                              <th>Total</th>
                              <th style="text-align: right;">@if($diff!=0) @if($diff<0) {{formatIndianNumber($total_credit_amount)}} @else  {{formatIndianNumber($total_debit_amount)}}@endif @else {{formatIndianNumber($total_debit_amount)}} @endif</th>
                              <th style="text-align: right;">@if($diff!=0) @if($diff<0) {{formatIndianNumber($total_credit_amount)}} @else  {{formatIndianNumber($total_debit_amount)}}@endif @else {{formatIndianNumber($total_debit_amount)}} @endif</th>
                           </tr>
                     </tbody>
                  </table>
                  <?php 
               }
               ?>
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
      $(".view_account_tr").click(function(){
         var id = $(this).data('id');
         var type = $(this).data('type');
         var under = $(this).data('under');
         var to_date = $("#to_date").val();
         var trial_balance_by = $("input[name='trial_balance_by']:checked").val();
         var type1 = $("select[name='type']").val();
         if(id){
            if(type=="inner_group"){
               var url = "trialbalance-filter?trial_balance_by="+trial_balance_by+"&type="+type1+"&to_date="+to_date+"&group_id="+under+"&under=group";
               window.location.href = url; 
            }
            
         }         
      });
   });
</script>
@endsection