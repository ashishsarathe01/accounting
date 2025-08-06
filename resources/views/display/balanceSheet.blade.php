@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
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
               <form class="" id="frm" method="GET" action="{{ route('balancesheet.filter') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">FY</p>
                     <select class="form-select w-min-120 ms-xxl-2" aria-label="Default select example" id="financial_year" name="financial_year" required>
                        <option value="{{Session::get('default_fy')}}">{{Session::get('default_fy')}}</option>
                     </select>
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-150" style="display:none;">
                        <input type="date" id="from_date" name="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required value="{{$from_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator w-min-150 ms-xxl-2">
                        <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <button class="btn btn-info ms-xxl-2 next_btn">Next</button>
                  </div>
               </form>
               
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Balance Sheet</h5>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <div class="col-md-6  font-14 p-0 border-bottom-divider">
                  <div class="row p-0 m-0">
                     <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">Liabilities (Rs.)
                        <span class="ms-auto">0.00</span>
                     </div>
                      @php 
                        $liability_total = 0;
                        $asset_total = 0;
                        if($prev_year_profitloss!=0){
                           if($prev_year_profit_status==1){
                              $liability_total = $liability_total + $prev_year_profitloss;
                           }else{
                              $liability_total = $liability_total - $prev_year_profitloss;
                           }
                        }
                        $liability_total = $liability_total - $current_journal_amount;
                     @endphp
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider profitloss_redirect" style="cursor:pointer;color: #0000EE">
                        <?php 
                        if($profitloss<0){
                           echo "<span>PROFIT FOR THE PERIOD</span>";                           
                        }else if($profitloss==0){
                           echo "PROFIT/LOSS ADJUSTED";
                        }
                        if($prev_year_profitloss!=0){                           
                           if($prev_year_profit_status==1){
                              echo "<br>UNADJUSTED PROFIT AMOUNT (".$prevFy.")";
                           }else{
                              echo "<br>UNADJUSTED LOSS AMOUNT (".$prevFy.")";
                           }                    
                        }
                        ?>
                        <span class="ms-auto" style="text-align: right;">
                           <?php 
                           setlocale(LC_MONETARY, 'en_IN');                           
                           if($profitloss<0){
                              echo formatIndianNumber(abs($profitloss));
                              if($current_journal_amount>0){
                                 echo "<p style='font-size:10px'>ADJUSTED IN ACCOUNT : ".formatIndianNumber($current_journal_amount)."</p>";
                              }
                              //echo formatIndianNumber(abs($profitloss)- $current_journal_amount);
                              $liability_total = $liability_total + abs($profitloss);
                           }else{
                              echo "&nbsp;";
                           }
                           if($prev_year_profitloss!=0){
                              echo "<br>".formatIndianNumber($prev_year_profitloss);
                           }
                           ?>
                        </span>
                     </div>
                    
                     @foreach($liability as $value)
                        @php $debit = 0;$credit = 0; @endphp
                        
                        @foreach($value->accountGroup as $v1)
                           @php
                              $d = 0;
                              $c = 0;
                           // if($value->name=="CURRENT LIABILITIES"){                              
                           //    print_r($v1->name);
                           //    echo $d." - ".$c;
                           //    echo "<br>";
                           // }
                           @endphp
                           @if(count($v1->accountUnderGroup)>0)
                              @foreach($v1->accountUnderGroup as $val1)
                                
                                 @foreach($val1->account as $val2)
                                 
                                    @foreach($val2->accountLedger as $val3)
                                       @php 
                                          if($val3->debit!=""){
                                             $debit = $debit + str_replace(",","",$val3->debit);
                                              $d = $d + str_replace(",","",$val3->debit);
                                          }
                                          if($val3->credit!=""){
                                             $credit = $credit + str_replace(",","",$val3->credit);
                                             $c = $c + str_replace(",","",$val3->credit);
                                          }                                    
                                       @endphp
                                    @endforeach
                                 @endforeach
                                 
                                 @foreach($val1->accountUnderGroup as $a3)
                                 {{-- @php 
                                if($v1->name=="PROVISIONS/EXPENSES PAYABLE"){
                                 echo $a3->account;
                                }
                                @endphp --}}
                                    @foreach($a3->account as $a4)
                                       @foreach($a4->accountLedger as $val3)
                                       @php 
                                             if($val3->debit!=""){
                                                $debit = $debit + str_replace(",","",$val3->debit);
                                                $d = $d + str_replace(",","",$val3->debit);
                                             }
                                             if($val3->credit!=""){
                                                $credit = $credit + str_replace(",","",$val3->credit); 
                                                $c = $c + str_replace(",","",$val3->credit);
                                             }                                    
                                          @endphp
                                       @endforeach
                                    @endforeach
                                 @endforeach

                              @endforeach
                           @endif
                           @foreach($v1->account as $v2)
                          
                              @foreach($v2->accountLedger as $v3)
                                 @php 
                                    if($v3->debit!="" && $v3->debit!="Nan"){                                       
                                       $debit = (float)$debit + (float)str_replace(",","",$v3->debit);
                                       $d = (float)$d + (float)str_replace(",","",$v3->debit);
                                    }
                                    if($v3->credit!="" && $v3->credit!='null'){
                                       $credit = $credit + str_replace(",","",$v3->credit); 
                                       $c = $c + str_replace(",","",$v3->credit);
                                    }                                    
                                 @endphp
                              @endforeach
                           @endforeach
                           @php 
                           // if($value->name=="CURRENT LIABILITIES"){
                              
                           //    print_r($v1->name);
                           //    echo $d." - ".$c;
                           //    echo "<br>";
                           // }
                           @endphp
                        @endforeach
                        @foreach($value->accountWithHead as $v1)                           
                           @foreach($v1->accountLedger as $v3)
                              @php 
                                 if($v3->debit!="" && $v3->debit!='null'){
                                    $debit = $debit + str_replace(",","",$v3->debit);
                                 }
                                 if($v3->credit!="" && $v3->credit!='null'){
                                    $credit = $credit + str_replace(",","",$v3->credit); 
                                 }                                    
                              @endphp
                           @endforeach                           
                        @endforeach
                        <?php
                        $amount = $debit - $credit;
                        if(($value->show_in_balance_sheet==1 || $amount!=0) && $value->id!=4){?>
                           <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                              <span class="ms-auto">
                                 <?php 
                                 setlocale(LC_MONETARY, 'en_IN');     
                                 if($amount>0){
                                    echo "- ".formatIndianNumber($amount);
                                    $liability_total = $liability_total - $amount;  
                                 }else{
                                    echo formatIndianNumber(abs($amount));
                                    $liability_total = $liability_total + abs($amount);  
                                 }                                                                 
                                 ?>
                              </span>
                           </div>
                        <?php   
                        } ?>
                     @endforeach
                  </div>
               </div>
               <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                  <div class="row p-0 m-0">
                     <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">Assets (Rs.)
                        <span class="ms-auto">0.00</span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider profitloss_redirect" style="cursor:pointer;color: #0000EE">
                        <?php 
                        if($profitloss>0){
                           echo "LOSS FOR THE PERIOD";
                        }
                        ?>
                        <span class="ms-auto">
                           <?php 
                           setlocale(LC_MONETARY, 'en_IN');                           
                           if($profitloss>0){
                              echo formatIndianNumber(abs($profitloss));
                              $asset_total = $asset_total + abs($profitloss);
                           }else{
                              echo "&nbsp;";
                           }
                           ?>
                        </span>
                     </div>
                     @foreach($assets as $value)
                        @php $debit = 0;$credit = 0; @endphp
                        @foreach($value->accountGroup as $v1)
                           @if(count($v1->accountUnderGroup)>0)
                              @foreach($v1->accountUnderGroup as $val1)
                                 @foreach($val1->account as $val2)
                                    @foreach($val2->accountLedger as $val3)
                                       @php
                                          if($val3->debit!="" && $val3->debit!="Nan"){
                                             $debit = $debit + trim(str_replace(",","",$val3->debit));
                                          }
                                          if($val3->credit!=""){
                                             $credit = $credit + str_replace(",","",$val3->credit);
                                          }                                    
                                       @endphp
                                    @endforeach
                                 @endforeach
                                  {{-- dd --}}
                                 @if(count($val1->accountUnderGroup)>0)
                                    @foreach($val1->accountUnderGroup as $val1a)
                                       @foreach($val1a->account as $val2)
                                          @foreach($val2->accountLedger as $val3)
                                             @php
                                                if($val3->debit!="" && $val3->debit!="Nan"){
                                                   $debit = $debit + trim(str_replace(",","",$val3->debit));
                                                }
                                                if($val3->credit!=""){
                                                   $credit = $credit + str_replace(",","",$val3->credit);
                                                }                                    
                                             @endphp
                                          @endforeach
                                       @endforeach

                                       @if(count($val1a->accountUnderGroup)>0)
                                          @foreach($val1a->accountUnderGroup as $val1b)
                                             @foreach($val1b->account as $val2)
                                                @foreach($val2->accountLedger as $val3)
                                                   @php
                                                      if($val3->debit!="" && $val3->debit!="Nan"){
                                                         $debit = $debit + trim(str_replace(",","",$val3->debit));
                                                      }
                                                      if($val3->credit!=""){
                                                         $credit = $credit + str_replace(",","",$val3->credit);
                                                      }                                    
                                                   @endphp
                                                @endforeach
                                             @endforeach
                                             @if(count($val1b->accountUnderGroup)>0)
                                                @foreach($val1b->accountUnderGroup as $val1c)
                                                   @foreach($val1c->account as $val2)
                                                      @foreach($val2->accountLedger as $val3)
                                                         @php
                                                            if($val3->debit!="" && $val3->debit!="Nan"){
                                                               $debit = $debit + trim(str_replace(",","",$val3->debit));
                                                            }
                                                            if($val3->credit!=""){
                                                               $credit = $credit + str_replace(",","",$val3->credit);
                                                            }                                    
                                                         @endphp
                                                      @endforeach
                                                   @endforeach
                                                   @if(count($val1c->accountUnderGroup)>0)
                                                      @foreach($val1c->accountUnderGroup as $val1d)
                                                         @foreach($val1d->account as $val2)
                                                            @foreach($val2->accountLedger as $val3)
                                                               @php
                                                                  if($val3->debit!="" && $val3->debit!="Nan"){
                                                                     $debit = $debit + trim(str_replace(",","",$val3->debit));
                                                                  }
                                                                  if($val3->credit!=""){
                                                                     $credit = $credit + str_replace(",","",$val3->credit);
                                                                  }                                    
                                                               @endphp
                                                            @endforeach
                                                         @endforeach

                                                         
                                                         
                                                      @endforeach
                                                   @endif
                                                   
                                                   
                                                @endforeach
                                             @endif
                                             
                                             
                                          @endforeach
                                       @endif
                                       
                                    @endforeach
                                 @endif

                                  {{-- dd --}}
                              @endforeach
                           @endif
                           @php 
                           if($v1->stock_in_hand==0){ @endphp
                              @foreach($v1->account as $v2)
                                 @foreach($v2->accountLedger as $v3)
                                    @php 
                                       if($v3->debit!="" && $v3->debit!='null'){
                                          $debit += floatval($v3->debit);
                                       }
                                       if($v3->credit!="" && $v3->credit!='null'){
                                          $credit += floatval($v3->credit); 
                                       }                                    
                                    @endphp
                                 @endforeach
                              @endforeach
                              @php 
                           }else{                              
                              $debit = $debit + $stock_in_hand;
                           }
                           @endphp
                          
                        @endforeach
                        @foreach($value->accountWithHead as $v1)
                           @foreach($v1->accountLedger as $v3)
                              @php 
                                 if($v3->debit!="" && $v3->debit!='null'){
                                    $debit += floatval($v3->debit);
                                 }
                                 if($v3->credit!="" && $v3->credit!='null'){
                                    $credit += floatval($v3->credit); 
                                 }                                    
                              @endphp
                           @endforeach                           
                        @endforeach
                        <?php
                        $amount = $debit - $credit;                        
                        if($value->show_in_balance_sheet==1 || $amount!=0){?>
                           <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                              <span class="ms-auto">
                                 <?php 
                                 setlocale(LC_MONETARY, 'en_IN');                                 
                                 echo formatIndianNumber($debit - $credit);
                                 $asset_total = $asset_total + abs($debit-$credit);
                                 
                                 ?>
                              </span>
                           </div>
                           @php  @endphp
                           <?php 
                        } ?>
                     @endforeach                     
                  </div>
               </div>
               @php 
               $total = $liability_total - $asset_total;  
               $total = round($total,2);       
               @endphp               
               <div class="col-md-6  font-14 p-0 border-bottom-divider">
                  <div class="row p-0 m-0">
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                        <?php
                           if($total<0){ 
                              echo "OPENING DIFFERENCE";
                           }else{
                              echo '&nbsp';
                           }
                        ?>
                        <span class="ms-auto">
                           <?php 
                          if($total<0){ 
                                 echo formatIndianNumber(abs($total));
                              }                             
                           ?>
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                        TOTAL
                        <span class="ms-auto">
                           <?php                            
                           if($total>0){ 
                              echo formatIndianNumber(abs($liability_total));
                           }else{
                              echo formatIndianNumber(abs($asset_total));                           
                           }                              
                           ?>
                        </span>
                     </div>
                  </div>
               </div>

               <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                  <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                     <?php  
                        if($total>0){ 
                           echo "OPENING DIFFERENCE";
                        }else{
                           echo '&nbsp';
                        }
                     ?>
                     <span class="ms-auto">
                        <?php                       
                        if($total>0){ 
                              echo formatIndianNumber(abs($total));
                           }                         
                        ?>
                     </span>
                  </div>
                  <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">TOTAL
                     <span class="ms-auto">
                        <?php                            
                        if($total>0){ 
                           echo formatIndianNumber(abs($liability_total));
                        }else{
                          echo formatIndianNumber(abs($asset_total));                           
                        }                              
                        ?>
                     </span>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(".get_group_detail").click(function(){
         let id = $(this).attr('data-id');
         if(id==4){
            window.location = "{{url('profitloss-filter')}}?financial_year=24-25&from_date={{$from_date}}&to_date={{$to_date}}";
         }else{
            window.location = "{{url('group-balance-by-head')}}/"+id+"/{{$from_date}}/{{$to_date}}";
         }
         
      });
   });
   $(".profitloss_redirect").click(function(){
      window.location = "{{url('profitloss-filter')}}?financial_year={{ Session::get('default_fy') }}&from_date={{$from_date}}&to_date={{$to_date}}";
   });
</script>
@endsection