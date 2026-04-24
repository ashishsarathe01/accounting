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
                    <form class="" id="frm" method="GET" action="{{ route('balancesheet.index') }}">
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
                                <span class="ms-auto">Amt.(₹)</span>
                            </div>
                            
                            @php 
                                $liability_total = 0; $asset_total = 0;
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
                                if($profit_loss_amount<0){
                                   echo "<span>PROFIT FOR THE PERIOD</span>";                           
                                }else if($profit_loss_amount==0){
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
                                   if($profit_loss_amount<0){
                                      echo formatIndianNumber(abs($profit_loss_amount));
                                      if($current_journal_amount>0){
                                         echo "<p style='font-size:10px'>ADJUSTED IN ACCOUNT : ".formatIndianNumber($current_journal_amount)."</p>";
                                      }
                                      $liability_total = $liability_total + abs($profit_loss_amount);
                                   }else{
                                      echo "&nbsp;";
                                   }
                                   if($prev_year_profitloss!=0){
                                      echo "<br>".formatIndianNumber($prev_year_profitloss);
                                   }
                                   
                                   ?>
                                </span>
                             </div>
                            @foreach($heads as $value)
                                @if($value->bs_profile==2)
                                    @continue
                                @endif
                                @if(($value->show_in_balance_sheet==1 || $value->balance!=0) && $value->id!=4)
                                    <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                                        <span class="ms-auto">
                                         <?php 
                                         setlocale(LC_MONETARY, 'en_IN');     
                                         if($value->balance>0){
                                            echo "- ".formatIndianNumber($value->balance);
                                            $liability_total = $liability_total - $value->balance;  
                                         }else{
                                            echo formatIndianNumber(abs($value->balance));
                                            $liability_total = $liability_total + abs($value->balance);  
                                         }                                                                 
                                         ?>
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                            
                        </div>
                    </div>
                    <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">Assets (Rs.)
                                <span class="ms-auto">Amt.(₹)</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider profitloss_redirect" style="cursor:pointer;color: #0000EE">
                                <?php 
                                if($profit_loss_amount>0){
                                   echo "LOSS FOR THE PERIOD";
                                }
                                ?>
                                <span class="ms-auto">
                                   <?php 
                                   setlocale(LC_MONETARY, 'en_IN');                           
                                   if($profit_loss_amount>0){
                                      echo formatIndianNumber(abs($profit_loss_amount));
                                      $asset_total = $asset_total + abs($profit_loss_amount);
                                   }else{
                                      echo "&nbsp;";
                                   }
                                   ?>
                                </span>
                            </div>
                            @foreach($heads as $value)
                                @if($value->bs_profile==1)
                                    @continue
                                @endif
                                @if($value->show_in_balance_sheet==1 || $value->balance!=0)
                                    <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                                        <span class="ms-auto">
                                             <?php 
                                             setlocale(LC_MONETARY, 'en_IN');                                 
                                             echo formatIndianNumber($value->balance);
                                             $asset_total = $asset_total + abs($value->balance);
                                             ?>
                                        </span>
                                    </div>
                                @endif
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