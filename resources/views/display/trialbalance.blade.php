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
               @php 
                  $trial_balance_by_account = "checked";
                  $trial_balance_by_group = "";
                  if(isset($_GET['trial_balance_by'])){
                     if($_GET['trial_balance_by']=="by_group"){
                        $trial_balance_by_account = "";
                        $trial_balance_by_group = "checked";
                     }else if($_GET['trial_balance_by']=="by_account"){
                        $trial_balance_by_account = "checked";
                        $trial_balance_by_group = "";
                     }
                  }
               @endphp               
               <form class="" id="frm" method="GET" action="{{ route('trialbalance.filter') }}">
                  <div class="d-xxl-flex d-block  align-items-center"> 
                     <p style="min-width: 97px;"><input type="radio"  name="trial_balance_by" {{$trial_balance_by_account}} value="by_account"> By Account</p> 
                     <p style="min-width: 97px;"><input type="radio" name="trial_balance_by" {{$trial_balance_by_group}} value="by_group"> By Group</p>
                     <select class="form-select w-min-230 ms-xxl-2" aria-label="Default select example" name="type">
                        <option value="open" @if($type && $type=='open') selected @endif>Opening Trial Balance</option>
                        <option value="close" @if($type && $type=='close') selected @endif>Closing Trial Balance</option>
                     </select>
                     <div class="calender-administrator w-min-150 ms-xxl-2">
                        <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <button class="btn btn-info ms-xxl-2 next_btn">Next</button>
                  </div>
               </form>            
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
                        @php $total_debit_amount = 0;$total_credit_amount = 0; setlocale(LC_MONETARY, 'en_IN'); 
                        // echo "<pre>";
                        //    print_r($account->toArray());
                           @endphp
                        @foreach($account as $value)
                           @php 
                              $balance = $value['debit'] - $value['credit']; 
                              if($balance==0){
                                 continue;
                              }
                           @endphp
                           <tr class=" font-14 font-heading bg-white view_account_tr" data-id="{{$value['id']}}" data-type="{{$value['type']}}" data-under="{{$value['under_group']}}" style="cursor: pointer;">
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
                                 @if($balance<0)
                                    @php echo formatIndianNumber(abs($balance),2); @endphp
                                    @php 
                                       $total_credit_amount = $total_credit_amount + abs($balance);
                                    @endphp                         
                                 @else
                                    @php echo '0.00'; @endphp
                                 @endif                              
                              </td>                           
                           </tr>                        
                        @endforeach
                        <?php 
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
               if(isset($_GET['trial_balance_by'])){
                  if($_GET['trial_balance_by']=="by_group"){ ?>
                     <table class="table-striped table-bordered table m-0 shadow-sm" >
                        <thead>
                           <tr class=" font-14  fw-bold">
                              <th class="w-min-230 border-none py-12 px-3">Account/Group</th>                        
                              <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Debit Balance</th>
                              <th class="w-min-180 border-none py-12 px-3 detail_td" style="text-align: right;">Credit Balance</th>
                           </tr>
                        </thead>
                        <tbody>
                        @php $total_debit_amount = 0;$total_credit_amount = 0;@endphp
                           @foreach($account as $head)
                              @php 
                              $head_total = 0; 
                                 $head_total = $head_total + $head->debit; 
                                 $head_total = $head_total - $head->credit; 
                              @endphp
                              @foreach($head->accountGroup as $group)
                                 @php 
                                    if($group->debit=='0' && $group->credit=='0'){
                                       continue;
                                    }   
                                    $head_total = $head_total + $group->debit; 
                                    $head_total = $head_total - $group->credit;  
                                 @endphp                     
                              @endforeach
                              <tr>
                                 <td>
                                 <a href="trialbalance-filter?trial_balance_by=by_account&type=<?php echo $_GET['type'];?>&to_date=<?php echo $_GET['to_date'];?>&group_id=<?php echo $head->id;?>&under=head">{{$head->name}}</a>
                                    @foreach($head->accountGroup as $group)
                                       @php 
                                          if($group->debit=='0' && $group->credit=='0'){
                                             continue;
                                          }                                 
                                       @endphp
                                       <a href="trialbalance-filter?trial_balance_by=by_account&type=<?php echo $_GET['type'];?>&to_date=<?php echo $_GET['to_date'];?>&group_id=<?php echo $group->id;?>&under=group"><p style="    margin-left: 55px;" >{{$group->name}}</p></a>
                                    @endforeach
                                 </td>
                                 <td style="text-align: right;">                                    
                                    @php 
                                       if($head_total>=0){
                                          $total_debit_amount = $total_debit_amount + $head_total; 
                                          echo "<span style='color:blue'>" . formatIndianNumber(abs($head_total)) . "</span>";
                                       }else{
                                          echo "&nbsp;";
                                       }
                                       
                                    @endphp
                                    @foreach($head->accountGroup as $group)
                                       @php 
                                          if($group->debit=='0' && $group->credit=='0'){
                                             continue;
                                          }                                 
                                       @endphp
                                       <p style="text-align: right;">{{formatIndianNumber($group->debit)}}</p>
                                    @endforeach
                                 </td>
                                 <td style="text-align: right;">                                    
                                    @php 
                                       if($head_total<0){
                                          $total_credit_amount = $total_credit_amount + abs($head_total); 
                                          echo "<span style='color:blue'>" . formatIndianNumber(abs($head_total)) . "</span>";
                                       }else{
                                          echo "&nbsp;";
                                       }
                                       
                                    @endphp
                                    @foreach($head->accountGroup as $group)
                                       @php 
                                          if($group->debit=='0' && $group->credit=='0'){
                                             continue;
                                          }                              
                                       @endphp
                                       <p >{{formatIndianNumber($group->credit)}}</p>
                                       
                                    @endforeach
                                 </td>
                              </tr>                              
                           @endforeach
                           @foreach($group_primary_yes as $value)                           
                              @php 
                                 if($value->debit=='0' && $value->credit=='0'){
                                    continue;
                                 }                                 
                              @endphp
                              <tr>
                                 <td>
                                    <a href="trialbalance-filter?trial_balance_by=by_account&type=<?php echo $_GET['type'];?>&to_date=<?php echo $_GET['to_date'];?>&group_id=<?php echo $value->id;?>&under=group">{{$value->name}}</a>
                                 </td>
                                 <td style="text-align: right;">
                                    {{formatIndianNumber($value->debit)}}
                                    @php $total_debit_amount = $total_debit_amount + $value->debit; @endphp
                                 </td>
                                 <td style="text-align: right;">
                                    {{formatIndianNumber($value->credit)}}  
                                    @php $total_credit_amount = $total_credit_amount + $value->credit; @endphp                                    
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
               }?>
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