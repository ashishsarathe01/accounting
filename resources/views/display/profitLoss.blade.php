@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
   .form-control {
      height: 52px;
   }
   input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
}
.vertical-pl-table td,
.vertical-pl-table th{
    vertical-align:middle;
}

.vertical-pl-table .level-0 td{
    font-weight:700;
    font-size:16px;
    background:#f8f9fa;
}

.vertical-pl-table .level-1 td{
    padding-left:35px !important;
}

.vertical-pl-table .total-row td{
    font-weight:700;
    border-top:2px solid #000 !important;
}
.vertical-pl-table .level-2 td{
    padding-left:70px !important;
}
</style>
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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Profit Loss</a>
                     </li>
                  </ol>
               </nav>
               <form class="" id="frm" method="GET" action="{{ route('profitloss.filter') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <div class="ms-xxl-2">
                       <select class="form-select" id="report_design" style="width: 250px;">
                           <option value="horizontal" selected>Horizontal</option>
                           <option value="vertical">Vertical</option>
                       </select>
                     </div>
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">Series</p>
                     <select class="form-select w-min-120 ms-xxl-2" aria-label="Default select example" id="series" name="series" style="margin-right: 5px;">
                        <option value="">ALL</option>
                        @foreach ($mat_series as $series)
                           <option value="{{$series->series}}"  @if($series->series==$data['series']) selected @endif>{{$series->series}}</option>                           
                        @endforeach
                     </select>
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">FY</p>
                     <select class="form-select w-min-120 ms-xxl-2" aria-label="Default select example" id="financial_year" name="financial_year" required>
                        <option value="{{$data['financial_year']}}">{{$data['financial_year']}}</option>
                     </select>
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-150">
                        <input type="date" id="from_date" name="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required value="{{$from_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator w-min-150 ms-xxl-2">
                        <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <button class="btn btn-info ms-xxl-2 next_btn">Next</button>
                  </div>
               </form>
            </div>
            
            @if(!empty($from_date) && !empty($to_date))
            <div id="horizontal_profit_loss">
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Profit & Loss Account</h5>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <!-- for debit -->
               <div class="col-md-6  font-14 p-0 border-bottom-divider">
                  <div class="px-3 py-12 fw-bold border-bottom-divider">Debit (Rs.)</div>
                  <div class="row p-0 m-0">
                     
                     <div class="col-md-12 px-3 py-12 border-bottom-divider">

    @php
        $opening = $data['opening_stock'] ?? 0;
        $transit_opening = $data['stock_in_transit_opening_value'] ?? 0;
        $total_opening_stock = $opening + $transit_opening;

        $previous_date = Carbon\Carbon::parse($from_date)->subDay()->format('Y-m-d');
    @endphp

    <!-- Opening Stock Row -->
    <div class="row fw-500 font-14 align-items-center">

        <div class="col-4">
            Opening Stock
        </div>

        <div class="col-4 text-end">
            <span class="ms-auto">
                <a href="{{ url('itemledger-filter') }}?items_id=all&from_date={{ $from_date }}&to_date={{ $previous_date }}">
                    {{ formatIndianNumber(round($opening,2)) }}
                </a>
            </span>
        </div>

        <div class="col-4 text-end fw-bold">
            {{ formatIndianNumber(round($total_opening_stock,2)) }}
        </div>

    </div>

    @if($transit_opening > 0)
    <!-- Opening Stock in Transit Row -->
    <div class="row font-14 align-items-center mt-1">

        <div class="col-4 ps-4 text-muted">
            <small>Stock in Transit</small>
        </div>

        <div class="col-4 text-end text-muted">
            <span class="ms-auto">
                <a href="{{ url('purchase-by-month-detail-transit-opening/'.$data['financial_year'].'/'.$from_date.'/'.$to_date) }}">
                <small>{{ formatIndianNumber(round($transit_opening,2)) }}</small>
                </a>
            </span>
        </div>

        <div class="col-4"></div>

    </div>
    @endif

</div>

                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        <a class="text-decoration-none text-primary fw-500" href="{{url('account-balance-by-group/23')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">Purchase
                              <span class="ms-auto">
                                 @php
                                    
                                    echo formatIndianNumber(round($data['tot_purchase_amt'],2));
                                 @endphp
                              </span>
                           </p>
                           {{-- <p class="d-flex m-0 py-12">
                              Purchase Return
                              <span class="ms-auto">
                                 @php
                                    echo formatIndianNumber(round($data['tot_purchase_return_amt'],2));
                                 @endphp
                              </span>
                           </p>
                           <p class="d-flex m-0 py-12 align-items-center">
                              Net Purchase
                              <span class="ms-auto h-1-divider">----------</span>
                           </p> --}}
                           <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 @php
                                    //$total_net_purchase = $data['tot_purchase_amt'] - $data['tot_purchase_return_amt']+$data['tot_sale_return_amt_purchase'];
                                    //echo formatIndianNumber(round($total_net_purchase,2));
                                 @endphp
                              </span>
                           </p>
                        </a>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/12')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           Expenses (Direct/Mfg.)</a>
                        <span class="ms-auto">
                           <a href="{{url('account-balance-by-group/12')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                              @php
                                 echo formatIndianNumber(round($data['direct_expenses']-$direct_expenses_credit,2));
                              @endphp
                           </a>
                        </span>
                     </div>
                     @if($data['gross_profit'] > 0)
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                           Gross Profit
                           <span class="ms-auto">
                              {{ formatIndianNumber(round($data['gross_profit'], 2)) }}
                           </span>
                        </div>
                     @else
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="height: 46px;"></div>
                     @endif
                     @php
                        $gross_profit = $data['gross_profit'];
                        $gross_loss   = $data['gross_loss'];
                        $first_total  = $data['trading_total'];
                     @endphp
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <span class="ms-auto">--------------</span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Total
                        <span class="ms-auto">
                           {{ formatIndianNumber(round($data['trading_total'], 2)) }}
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        @if($data['gross_loss'] > 0)
                           Gross Loss B/D
                           <span class="ms-auto">{{ formatIndianNumber($data['gross_loss']) }}</span>
                        @else
                           <span class="ms-auto">&nbsp;</span>
                        @endif
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/15')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                        Expenses (Indirect/Admn.)</a>
                        <span class="ms-auto">                           
                           <a href="{{url('account-balance-by-group/15')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                              @php
                                 echo formatIndianNumber(round($data['indirect_expenses']- $indirect_expenses_credit,2));
                              @endphp
                           </a>
                        </span>
                     </div>
                     @php
                        $nett_profit = $data['net_profit'];
                        $nett_loss   = $data['net_loss'];
                        $nett_final_amount = $data['pnl_total'];
                     @endphp
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <?php 
                        if($nett_profit>0){?>
                           Nett Profit                           
                        <span class="ms-auto">
                           @php
                             echo formatIndianNumber(round($nett_profit, 2));
                           @endphp
                        </span>
                           <?php
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>
                     </div>                    
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Total
                        <span class="ms-auto">
                           @php
                              echo formatIndianNumber(round($nett_final_amount, 2));
                           @endphp
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     @if($current_year!=$data['financial_year'])
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                          
                           <?php 
                           $journal_amount = 0;
                           if($journal){
                              if($journal && count($journal)>0){
                              foreach ($journal as $k => $v) {
                                 if($v->journal_details && count($v->journal_details)>0){
                                    foreach ($v->journal_details as $key => $value) {
                                       if($value['account_name']==13319){
                                          $journal_amount = $journal_amount + $value['debit'] + $value['credit'];
                                          break;
                                       }
                                    }
                                 } 
                              }
                           }
                           }                                                 
                           if($nett_loss>0){
                              $ns = round($nett_loss, 2);
                              $ns = $ns - $journal_amount;
                              ?>
                              Net Loss b/d
                              <?php 
                              if($ns!=0){
                                 echo "<br>Unadjusted";
                              }
                              ?>
                              <span class="ms-auto">                           
                                 @php
                                 echo formatIndianNumber(round($nett_loss, 2));
                                 @endphp
                                 <br>
                                 @php
                                    if($ns!=0){
                                          echo formatIndianNumber(round($ns, 2));
                                       @endphp
                                       <button type="button" class="btn btn-sm btn-info transfer_amount" data-type="loss" data-amount="@php echo round($nett_loss-$journal_amount, 2);@endphp">Transfer</button>
                                       @php
                                    }
                                 @endphp
                              </span>
                              <?php 
                           }else{
                              if(count($journal)>0){
                                 foreach ($journal as $k => $v) {
                                    if(count($v->journal_details)>0){
                                       foreach ($v->journal_details as $key => $value) {
                                          if($value['account_name']!=13319 && $value->account_details){
                                             $amount = $value->debit + $value->credit;
                                             echo   "Account Name : ".$value->account_details->account_name." | Amount : ".formatIndianNumber($amount).'<br>';                                   
                                          }
                                       }
                                    } 
                                 }                                 
                              }                         
                              echo '<span class="ms-auto">&nbsp;</span>';
                           }
                           ?>                        
                        </div>
                     @endif
                  </div>
               </div>
               <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                  <div class="px-3 fw-bold py-12 border-bottom-divider">Credit (Rs.)</div>
                  <div class="row p-0 m-0">
                     
      <div class="col-md-12 px-3 py-12 border-bottom-divider">

    @php
        $closing = $data['closing_stock'] ?? 0;
        $transit = $data['stock_in_transit_value'] ?? 0;
        $total_closing_stock = $closing + $transit;
    @endphp

    <!-- Closing Stock Row -->
    <div class="row fw-500 font-14 align-items-center">

        <div class="col-4">
            Closing Stock
        </div>

        <div class="col-4 text-end">
            <span class="ms-auto">
             <a href="{{url('itemledger-filter')}}?&items_id=all&from_date={{$from_date}}&to_date={{$to_date}}">
            {{ formatIndianNumber(round($closing,2)) }}</a>
                           </span>
        </div>

        <div class="col-4 text-end fw-bold">
            {{ formatIndianNumber(round($total_closing_stock,2)) }}
        </div>

    </div>

    @if($transit > 0)
    <!-- Stock in Transit Row -->
    <div class="row font-14 align-items-center mt-1">

        <div class="col-4 ps-4 text-muted">
            <small>Stock in Transits</small>
        </div>

        <div class="col-4 text-end text-muted">
                        <span class="ms-auto">
            <a href="{{ url('purchase-by-month-detail-transit/'.$data['financial_year'].'/'.$from_date.'/'.$to_date) }}">
                <small>{{ formatIndianNumber(round($transit,2)) }}</small>
            </a>
            
                                       </span>
                    </div>
            
                    <div class="col-4"></div>
            
                </div>
                @endif
            
            </div>


                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        {{-- <a class="text-decoration-none text-primary fw-500" href="{{url('sale-by-month-detail')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}"> --}}
                        <a class="text-decoration-none text-primary fw-500" href="{{url('account-balance-by-group/24')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">
                           Sale
                           <span class="ms-auto">
                           @php
                              echo formatIndianNumber(round($data['tot_sale_amt'], 2));  
                           @endphp
                           </span>
                           </p>                        
                           {{-- <p class="d-flex m-0 py-12">Sale Return
                              <span class="ms-auto">
                                 @php
                                    echo formatIndianNumber(round($data['tot_sale_return_amt'], 2));
                                 @endphp
                              </span>
                           </p>
                           <p class="d-flex m-0 py-12 align-items-center">
                              Net Sale
                              <span class="ms-auto h-1-divider">----------</span>
                           </p> --}}
                           {{-- <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 @php
                                    $total_net_sale = $data['tot_sale_amt'] - $data['tot_sale_return_amt'];
                                    echo formatIndianNumber(round($total_net_sale, 2));
                                 @endphp
                                 
                              </span>
                           </p> --}}
                        </a>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/13')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                        Income (Direct/Opr.)</a>
                        <span class="ms-auto"><a href="{{url('account-balance-by-group/13')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           @php
                              echo formatIndianNumber(round($data['direct_income'] - $debit_direct_income, 2));
                           @endphp</a></span>
                     </div>
                     <?php                     
                     if($gross_loss > 0) {?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                           Gross Loss
                           <span class="ms-auto">
                              @php
                                 echo formatIndianNumber(round($gross_loss,2));
                              @endphp
                           </span>
                        </div>
                        <?php 
                     }else{
                        ?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="height: 46px;"></div>
                        <?php 
                     } ?>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46">
                        <span class="ms-auto">--------------</span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Total
                        <span class="ms-auto">
                           @php
                              echo formatIndianNumber(round($first_total,2));
                           @endphp
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <?php 
                        if($gross_profit>0){ ?>
                           Gross Profit b/d
                           <span class="ms-auto">
                              @php
                                 echo formatIndianNumber(round($gross_profit,2));
                              @endphp
                           </span>
                           <?php
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>
                        

                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        
                        <a href="{{url('account-balance-by-group/14')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                        Income (Indirect)</a>
                        <span class="ms-auto">
                           <a href="{{url('account-balance-by-group/14')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           @php
                              echo formatIndianNumber(round($data['indirect_income'] - $debit_indirect_income, 2));
                           @endphp
                           </a>
                        </span>
                     </div>
                     
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">                        
                        <?php 
                        if($nett_loss>0){ ?>
                           Net Loss
                        <span class="ms-auto">
                           @php
                             echo formatIndianNumber(round($nett_loss, 2));
                           @endphp
                        </span>
                        <?php 
                        }else{
                           
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>                        
                     </div>
                     
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        Total
                        <span class="ms-auto">
                           @php
                              echo formatIndianNumber(round($nett_final_amount, 2));
                           @endphp
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     @if($current_year!=$data['financial_year'])
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">                        
                           <?php 
                           $journal_amount = 0;
                           if(count($journal)>0){
                              foreach ($journal as $k => $v) {
                                 if(count($v->journal_details)>0){
                                    foreach ($v->journal_details as $key => $value) {
                                       if($value['account_name']==13319){
                                          $journal_amount = $journal_amount + $value['debit'] + $value['credit'];
                                          break;
                                       }
                                    }
                                 } 
                              }                              
                           }
                           if($nett_profit>0){
                              $ns = round($nett_profit, 2);
                              $ns = $ns - $journal_amount;
                              ?>
                              Net Profit b/d
                              <?php 
                              if($ns!=0){
                                 echo "<br>Unadjusted";
                              }
                              ?>
                              <span class="ms-auto">                              
                                 @php
                                 echo formatIndianNumber(round($nett_profit, 2));
                                 @endphp
                                 <br>
                                 @php
                                    if($ns!=0){
                                          echo formatIndianNumber(round($ns, 2));
                                       @endphp
                                       <button type="button" class="btn btn-sm btn-info transfer_amount" data-type="profit" data-amount="@php echo round($ns, 2);@endphp">Transfer</button>
                                       @php
                                    }
                                 @endphp
                              </span>
                              <?php 
                           }else{
                              if(count($journal)>0){
                                 foreach ($journal as $k => $v) {
                                    if(count($v->journal_details)>0){
                                       foreach ($v->journal_details as $key => $value) {
                                          if($value['account_name']!=13319 && $value->account_details){
                                             $amount = $value->debit + $value->credit;
                                             echo   "Account Name : ".$value->account_details->account_name." | Amount : ".formatIndianNumber($amount)."<br>";                                   
                                          }
                                       }
                                    } 
                                 }
                              }
                              echo '<span class="ms-auto">&nbsp;</span>';
                           }
                           ?>                        
                        </div>
                     @endif
                  </div>
               </div>
            </div>
            </div> <!-- horizontal_profit_loss -->

            <div id="vertical_profit_loss" style="display:none;">

               <div class="card border-0 shadow-sm">
                  <div class="card-body p-0">
                        <table class="table table-bordered mb-0 vertical-pl-table">
                           @php
   [$startYear, $endYear] = explode('-', $data['financial_year']);
   $currentFyEndYear = '20'.$endYear;
   $previousFyEndYear = $currentFyEndYear - 1;

   if (!function_exists('vplAmt')) {
       function vplAmt($val)
       {
           if (round($val,2) == 0) {
               return '—';
           }
           $formatted = function_exists('formatIndianNumber')
               ? formatIndianNumber(abs($val))
               : number_format(abs($val), 2);

           return $val < 0 ? '-' . $formatted : $formatted;
       }
   }

   $vpl  = $verticalPLBalances ?? [];
   $vpl2 = $verticalPLBalancesPrevious ?? [];
   $plDrillUrl = url('vertical-pl-drilldown');
@endphp
                           <thead>
                              <tr>
                                    @php
                                       [$startYear, $endYear] = explode('-', $data['financial_year']);
                                       $currentFyEndYear = '20'.$endYear;
                                       $previousFyEndYear = $currentFyEndYear - 1;
                                    @endphp
                                    <th width="60%">Particulars</th>
                                    <th width="20%" class="text-end">
                                       As at 31st March {{$currentFyEndYear}}
                                    </th>
                                    <th width="20%" class="text-end">
                                       As at 31st March {{$previousFyEndYear}}
                                    </th>

                              </tr>
                           </thead>

                           <tbody>

                              <tr class="level-0">
                                 <td><strong>Revenue</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Revenue+from+operations" class="text-primary text-decoration-none vpl-drill">
         Revenue from operations
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Revenue from operations'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Revenue from operations'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Other+income" class="text-primary text-decoration-none vpl-drill">
         Other income
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Other income'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Other income'] ?? 0) }}</td>
</tr>

                              <tr class="total-row">
                                 <td>Total Income</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-0">
                                 <td><strong>Expenses</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
                                 <td>Cost of material Consumed</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Purchase+of+stock-in-trade" class="text-primary text-decoration-none vpl-drill">
         Purchase of stock-in-trade
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Purchase of stock-in-trade'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Purchase of stock-in-trade'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
                                 <td>Changes in inventories</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Employee+benefit+expenses" class="text-primary text-decoration-none vpl-drill">
         Employee benefit expenses
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Employee benefit expenses'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Employee benefit expenses'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Finance+costs" class="text-primary text-decoration-none vpl-drill">
         Finance costs
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Finance costs'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Finance costs'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Depreciation+and+amortization+expenses" class="text-primary text-decoration-none vpl-drill">
         Depreciation and amortization expenses
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Depreciation and amortization expenses'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Depreciation and amortization expenses'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Other+expenses" class="text-primary text-decoration-none vpl-drill">
         Other expenses
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Other expenses'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Other expenses'] ?? 0) }}</td>
</tr>

                              <tr class="total-row">
                                 <td>Total expenses</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-0">
                                 <td><strong>Profit before exceptional, extraordinary and prior period items and tax</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Exceptional+items" class="text-primary text-decoration-none vpl-drill">
         Exceptional items
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Exceptional items'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Exceptional items'] ?? 0) }}</td>
</tr>

                              <tr class="level-0">
                                 <td><strong>Profit before extraordinary and prior period items and tax</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Extraordinary+items" class="text-primary text-decoration-none vpl-drill">
         Extraordinary items
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Extraordinary items'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Extraordinary items'] ?? 0) }}</td>
</tr>
                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Prior+period+item" class="text-primary text-decoration-none vpl-drill">
         Prior period item
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Prior period item'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Prior period item'] ?? 0) }}</td>
</tr>

                              <tr class="level-0">
                                 <td><strong>Profit before tax</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-0">
                                 <td><strong>Tax expenses</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Current+tax" class="text-primary text-decoration-none vpl-drill">
         Current tax
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Current tax'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Current tax'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Deferred+tax" class="text-primary text-decoration-none vpl-drill">
         Deferred tax
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Deferred tax'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Deferred tax'] ?? 0) }}</td>
</tr>

                              <tr class="level-1">
   <td>
      <a href="{{ $plDrillUrl }}?mapping_name=Excess/short+provision+relating+earlier+year+tax" class="text-primary text-decoration-none vpl-drill">
         Excess/short provision relating earlier year tax
      </a>
   </td>
   <td class="text-end">{{ vplAmt($vpl['Excess/short provision relating earlier year tax'] ?? 0) }}</td>
   <td class="text-end">{{ vplAmt($vpl2['Excess/short provision relating earlier year tax'] ?? 0) }}</td>
</tr>

                              <tr class="total-row">
                                 <td>Profit(Loss) for the period</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-0">
                                 <td><strong>Earning per share</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
                                 <td><strong>Basic</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-2">
                                 <td>Before extraordinary Items</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-2">
                                 <td>After extraordinary Adjustment</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-1">
                                 <td><strong>Diluted</strong></td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-2">
                                 <td>Before extraordinary Items</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                              <tr class="level-2">
                                 <td>After extraordinary Adjustment</td>
                                 <td></td>
                                 <td></td>
                              </tr>

                           </tbody>

                        </table>

                  </div>
               </div>

            </div>            
@else

<div class="alert alert-info mx-3">
   Please select From Date and To Date to view Profit & Loss Report.
</div>

@endif
         </div>
      </div>
   </section>
</div>
<?php 
   $account_html = "<option value=''>Select</option>";            
   foreach ($party_list as $value) {
      $account_html.="<option value='".$value->id."'>$value->account_name</option>";
   }?>
<div class="modal fade" id="transferAmountModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <h4 class="modal-title"><span id="modal_parameter_name"></span>Journal Voucher</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center p-0">
            <div class="row">
               <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('journal.store') }}">
                  @csrf 
                  <input type="hidden" name="form_source" value="profitloss">
                  <input type="hidden" name="flexRadioDefault"  value="NO">
                  <div class="row">
                     <div class="mb-2 col-md-2">
                        <label for="name" class="form-label font-14 font-heading">Date</label>
                        <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required="">
                     </div>
                     <div class="mb-2 col-md-2">
                        <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                        <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No.">
                     </div>
                     <div class="mb-2 col-md-2">
                        <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                        <select id="series_no" class="form-control" name="series_no" required="">
                           <option value="">Select Series</option>
                           <?php
                           if(count($mat_series) > 0) {
                              foreach ($mat_series as $value) { ?>
                                 <option value="<?php echo $value->series; ?>" <?php if(count($mat_series)==1) { echo "selected";} ?>><?php echo $value->series; ?></option>
                                 <?php 
                              }
                           } ?>
                        </select>
                     </div>
                     
                     <div class="mb-2 col-md-2 with_gst_section" style="display:none;">
                        <label for="series_no" class="form-label font-14 font-heading">Invoice No.</label>
                        <input type="text" class="form-control" name="invoice_no" placeholder="Enter Invoice No.">
                     </div>
                  </div>
                  <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                     <div class="plus-icon">
                           </div><div class="total">
                           </div><table id="without_gst_section" class="table-striped table m-0 shadow-sm table-bordered">
                        <thead>
                           <tr class=" font-12 text-body bg-light-pink ">
                              <th class="w-min-120 border-none bg-light-pink text-body ">Debit/Credit</th>
                              <th class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                              <th class="w-min-120 border-none bg-light-pink text-body ">Debit</th>
                              <th class="w-min-120 border-none bg-light-pink text-body ">Credit</th>
                              <th class="w-min-120 border-none bg-light-pink text-body ">Narration</th>
                           </tr>
                        </thead>
                        <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type" name="type[]" data-id="1" id="type_1" onchange="onTypeChange(1)">
                                 <option value="">Type</option>
                              </select>
                           </td>
                           <td class="">
                              <select class="form-select select2-singlee" id="account_1" name="account_name[]" required>
                                 <option value="">Select</option>
                                 
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" readonly onkeyup="debitTotal();" step="any">
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="1" id="credit_1" placeholder="Credit Amount" onkeyup="creditTotal();" step="any">
                           </td>
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="1" id="narration_1" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type" name="type[]" data-id="2" id="type_2" onchange="onTypeChange(2)">
                                 <option value="">Type</option>
                                 
                              </select>
                           </td>
                           <td class="">
                              <select class="form-select select2-singlee" id="account_2" name="account_name[]" required>
                                 <option value="">Select</option>
                                 <?php
                                 foreach ($party_list as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                    <?php 
                                 } ?>
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="2" id="debit_2" placeholder="Debit Amount" onkeyup="debitTotal();" step="any">
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="2" id="credit_2" placeholder="Credit Amount" readonly onkeyup="creditTotal();" step="any">
                           </td>
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="2" id="narration_2" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td class="w-min-120 fw-bold">Total</td>
                           <td class="w-min-120 fw-bold" id="total_debit"></td>
                           <td class="w-min-120 fw-bold" id="total_credit"></td>
                           <td></td>
                           <td></td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration"></td>
                        </tr>
                     </div>
                  </table>
                     
                  </div>
                  <div class=" d-flex">
                     <div class="ms-auto">
                        <a href="{{ route('journal.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="submit" value="SUBMIT" class="btn btn-xs-primary submit_data">
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      var transfer_type = "";
      var transfer_amount = "";
      //$('.select2-single').select2();
      $("#financial_year").change(function() {
         $("#from_date").val('');
         $("#to_date").val('');
         
      });
      $("#next_btn").click(function() {
         var id = $(this).val();         
         if(id != '') {
            $("#frm").submit();
         }
      });
      $(".transfer_amount").click(function(){
         transfer_type = $(this).attr('data-type');
         transfer_amount = $(this).attr('data-amount');
         $("#transferAmountModal").modal('toggle');
         if(transfer_type=='loss'){
            $("#type_1").html('<option value="Credit">Credit</option>');
            $("#type_1").val('Credit');
            onTypeChange(1);
            $("#credit_1").val(transfer_amount);
            $("#type_2").html('<option value="Debit">Debit</option>');
            $("#type_2").val('Debit');
            onTypeChange(2);
         }else if(transfer_type=='profit'){
            $("#type_1").html('<option value="Debit">Debit</option>');
            $("#type_1").val('Debit');
            onTypeChange(1);
            $("#debit_1").val(transfer_amount);
           
            $("#type_2").html('<option value="Credit">Credit</option>');
            $("#type_2").val('Credit');
            onTypeChange(2);
         }
         $("#account_1").html('<option value="13319">Profit & Loss</option>')
         $("#account_1").val(13319)
         $("#date").val('{{$to_date}}');
      });
      var add_more_count = 2;
      $(".add_more").click(function(){
         add_more_count++;
         var $curRow = $(this).closest('tr');
         var optionElements = $('#account_1').html();      
         newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]" data-id="' + add_more_count + '" id="type_' + add_more_count + '" onchange="onTypeChange('+add_more_count+')"><option value="">Type</option></select></td><td><select class="form-control account select2-singlee" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
         newRow += optionElements;
         newRow += '</select></td><td><input type="number" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();" step="any"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
         $curRow.before(newRow);
         if(transfer_type=="loss"){
            $("#type_"+add_more_count).html('<option value="Debit">Debit</option>');
            $("#type_"+add_more_count).val('Debit');
            onTypeChange(add_more_count);
         }else if(transfer_type=="profit"){
            $("#type_"+add_more_count).html('<option value="Credit">Credit</option>');
            $("#type_"+add_more_count).val('Credit');
            onTypeChange(add_more_count);
         }
         //$('.select2-single').select2();
      });
      $(".submit_data").click(function(){
         let date = $("#date").val();
         let series_no = $("#series_no").val(); // Added series_no validation
         if (date === "") {
            alert("Please enter the Date.");
            return false;
         }   
         if(series_no === "") {
            alert("Please enter the Series Name/Number.");
            return false;
         }
         var form_data = [];
         let dr = 0;
         let cr = 0;
         let ids = 0;
         let error = false;
         $(".type").each(function(){
            let id = $(this).attr('data-id');
            if($(this).val() != '' && $("#account_" + id).val() != "" && $("#date").val()!='') {
               if($(this).val() == "Credit" && $("#credit_" + id).val() != ""  && $("#account_" + id).val() != ""){
                  form_data.push({
                     "type": "Credit",
                     "credit": $("#credit_" + id).val(),
                     "debit": 0,
                     "user_id": $("#account_" + id).val(),
                     "remark": $("#narration_" + id).val()
                  });
                  cr = parseFloat(cr) + parseFloat($("#credit_" + id).val());
               }else if($(this).val()=="Debit" && $("#debit_" + id).val()!= ""  && $("#account_" + id).val()!= ""){
                  form_data.push({
                     "type": "Debit",
                     "credit": 0,
                     "debit": $("#debit_" + id).val(),
                     "user_id": $("#account_" + id).val(),
                     "remark": $("#narration_" + id).val()
                  });
                  dr = parseFloat(dr) + parseFloat($("#debit_" + id).val());
               }
            }else{
               error = true; // Set error flag if any required field is empty
            }
         });
         if(error) {
            alert("Please fill in all required fields.");
            return false;
         }
         if(form_data.length == 0) {
            alert("Please enter at least one transaction.");
            return false;
         }
         if(cr != dr) {
            alert("Debit and credit amounts should be equal.");
            return false;
         }
         $("#frm").submit();
      });
      $("#credit_1").keyup(function(){
         if(parseFloat(transfer_amount)<parseFloat($(this).val())){
            alert("Amount cannot be greaterthan "+transfer_amount);
            $("#credit_1").val(transfer_amount);
         }
         
      });
      $("#debit_1").keyup(function(){
         if(parseFloat(transfer_amount)<parseFloat($(this).val())){
            alert("Amount cannot be greaterthan "+transfer_amount);
            $("#debit_1").val(transfer_amount);
         }
         
      });
   });
   function onTypeChange(id){
      $("#debit_" + id).val('');
      $("#credit_" + id).val('');
      let debit_total = 0;
      $(".debit").each(function(){
         if($(this).val()!=""){
            debit_total = parseFloat(debit_total) + parseFloat($(this).val());
         }
      });
      let credit_total = 0;
      $(".credit").each(function(){
         if($(this).val()!=""){
            credit_total = parseFloat(credit_total) + parseFloat($(this).val());
         }
      });
      if($("#type_" + id).val() == "Credit"){
         $("#debit_" + id).prop('readonly', true);
         $("#credit_" + id).prop('readonly', false);
         let amount = debit_total - credit_total;
         if(amount>0){
            $("#credit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html("<?php echo $account_html;?>");
      }else if ($("#type_" + id).val() == "Debit"){
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html("<?php echo $account_html;?>");
      }
      $("#account_"+id).html("<?php echo $account_html;?>");
      debitTotal();
      creditTotal();
   }
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      debitTotal();
      creditTotal();
   });
   function debitTotal() {
      let total_debit_amount = 0;
      $(".debit").each(function() {
         if($(this).val() != '') {
            total_debit_amount = parseFloat(total_debit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_debit").html(total_debit_amount.toFixed(2));
   }
   function creditTotal() {
      let total_credit_amount = 0;
      $(".credit").each(function() {
         if ($(this).val() != '') {
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount.toFixed(2));      
   }   
   $(document).on("change",".debit",function(){      
      debitTotal();
      creditTotal();
   });
   $(document).on("change",".credit",function(){     
      debitTotal();
      creditTotal();
   });
   $('#report_design').on('change', function () {

      if ($(this).val() === 'vertical') {

         $('#horizontal_profit_loss').hide();
         $('#vertical_profit_loss').show();

      } else {

         $('#vertical_profit_loss').hide();
         $('#horizontal_profit_loss').show();

      }

   });
</script>
@endsection