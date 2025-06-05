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
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Profit & Loss Account</h5>
            </div>
            <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
               <!-- for debit -->
               <div class="col-md-6  font-14 p-0 border-bottom-divider">
                  <div class="px-3 py-12 fw-bold border-bottom-divider">Debit (Rs.)</div>
                  <div class="row p-0 m-0">
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Opening Stock
                        <span class="ms-auto">
                            @php
                              use Carbon\Carbon;
                              $previous_date = Carbon::parse($from_date)->subDay()->format('Y-m-d');
                              $formatted_stock = formatIndianNumber(round($data['opening_stock'], 2));
                           @endphp

                           <a href="{{ url('itemledger-filter') }}?items_id=all&from_date={{ $from_date }}&to_date={{ $previous_date }}">
                              {{ $formatted_stock }}
                           </a>
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        <a class="text-decoration-none text-primary fw-500" href="{{url('purchase-by-month-detail')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">Purchase
                              <span class="ms-auto">
                                 @php
                                    echo formatIndianNumber($data['tot_purchase_amt'],2);
                                 @endphp
                              </span>
                           </p>
                           <p class="d-flex m-0 py-12">
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
                           </p>
                           <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 @php
                                    $total_net_purchase = $data['tot_purchase_amt'] - $data['tot_purchase_return_amt'];
                                    echo formatIndianNumber(round($total_net_purchase,2));
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
                     <?php
                     $gross_profit = 0;$gross_loss = 0;
                     $total_net_sale = $data['closing_stock'] + $data['tot_sale_amt'] - $data['tot_sale_return_amt'] + $data['direct_income']  - $debit_direct_income;
                     $total_net_purchase = $data['opening_stock'] + $data['tot_purchase_amt'] - $data['tot_purchase_return_amt'] + $data['direct_expenses'] - $direct_expenses_credit;
                     $balance = $total_net_purchase - $total_net_sale;
                     if($balance < 0) {
                        $gross_profit = str_replace("-","",$balance);
                        ?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                           Gross Profit
                           <span class="ms-auto">
                              @php
                                 echo formatIndianNumber(round((float)str_replace("-", "", $gross_profit), 2));
                              @endphp
                           </span>
                        </div>
                        <?php 
                     }else{
                        $gross_loss = $balance;
                        ?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="height: 46px;"></div>
                        <?php 
                     } ?>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <span class="ms-auto">--------------</span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Total
                        <span class="ms-auto">
                           @php
                              if($total_net_purchase>$total_net_sale){
                                 $first_total = $total_net_purchase;
                              }else if($total_net_purchase<$total_net_sale){
                                 $first_total = $total_net_sale;
                              }else{
                                 $first_total = $total_net_purchase;
                              }
                              echo formatIndianNumber(round($first_total,2));
                           @endphp                           
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        
                        <?php if($gross_loss>0){
                           echo 'Gross Loss B/D<span class="ms-auto">'.number_format($gross_loss,2).'</span>';
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        } ?>
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
                     <?php 
                     $nett_loss = 0;$nett_profit = 0;
                     $nett_expenses_total = $data['indirect_expenses'] - $indirect_expenses_credit + $gross_loss;
                     $nett_income_total = $data['indirect_income'] - $debit_indirect_income + $gross_profit;
                     $nett_income_total = str_replace("-","",$nett_income_total);
                     $nett_diff = $nett_expenses_total - $nett_income_total;
                     if($nett_diff>0){
                        $nett_loss = $nett_diff;
                     }
                     if($nett_diff<0){
                        $nett_profit = str_replace("-","",$nett_diff);
                     }
                     if($nett_expenses_total>$nett_income_total){
                        $nett_final_amount = $nett_expenses_total;
                     }else if($nett_expenses_total<$nett_income_total){
                        $nett_final_amount = $nett_income_total;
                     }else{
                        $nett_final_amount = $nett_expenses_total;
                     }             
                     ?>
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
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Closing Stock
                           <span class="ms-auto">
                              <a href="{{url('itemledger-filter')}}?&items_id=all&from_date={{$from_date}}&to_date={{$to_date}}">@php
                                 echo formatIndianNumber(round($data['closing_stock'],2));
                              @endphp</a>
                           </span>
                        
                     </div>
                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        <a class="text-decoration-none text-primary fw-500" href="{{url('sale-by-month-detail')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">
                           Sale
                           <span class="ms-auto">
                           @php
                              echo formatIndianNumber(round($data['tot_sale_amt'], 2));
                           @endphp
                           </span>
                           </p>                        
                           <p class="d-flex m-0 py-12">Sale Return
                              <span class="ms-auto">
                                 @php
                                    echo formatIndianNumber(round($data['tot_sale_return_amt'], 2));
                                 @endphp
                              </span>
                           </p>
                           <p class="d-flex m-0 py-12 align-items-center">
                              Net Sale
                              <span class="ms-auto h-1-divider">----------</span>
                           </p>
                           <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 @php
                                    $total_net_sale = $data['tot_sale_amt'] - $data['tot_sale_return_amt'];
                                    echo formatIndianNumber(round($total_net_sale, 2));
                                 @endphp
                                 
                              </span>
                           </p>
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
   
</script>
@endsection