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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Profit Loss</a>
                     </li>
                  </ol>
               </nav>
               <form class="" id="frm" method="GET" action="{{ route('profitloss.filter') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">FY</p>
                     <select class="form-select w-min-120 ms-xxl-2" aria-label="Default select example" id="financial_year" name="financial_year" required>
                        <option value="">Select</option>                        
                        <?php 
                        $y = 22;
                        while($y<=date('y')){
                           $y1 = $y+1;
                           ?>
                           <option value="<?php echo $y."-".$y1;?>" <?php echo isset($data['financial_year']) && $data['financial_year'] == $y."-".$y1 ? 'selected' : ''; ?>><?php echo $y."-".$y1;?></option>
                           <?php
                           $y++;
                        }
                        ?> 
                     </select>
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-150">
                        <input type="date" id="from_date" name="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required value="{{$from_date}}">
                     </div>
                     <div class="calender-administrator w-min-150 ms-xxl-2">
                        <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}">
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
                        <span class="ms-auto"><?php echo number_format($data['opening_stock'], 2); ?></span>
                     </div>
                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        <a class="text-decoration-none text-primary fw-500" href="{{url('purchase-by-month-detail')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">Purchase
                              <span class="ms-auto"><?php echo number_format($data['tot_purchase_amt'], 2); ?></span>
                           </p>
                           <p class="d-flex m-0 py-12">
                              Purchase Return
                              <span class="ms-auto"><?php echo number_format($data['tot_purchase_return_amt'], 2); ?></span>
                           </p>
                           <p class="d-flex m-0 py-12 align-items-center">
                              Net Purchase
                              <span class="ms-auto h-1-divider">----------</span>
                           </p>
                           <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 <?php 
                                 $total_net_purchase = $data['tot_purchase_amt'] - $data['tot_purchase_return_amt'];
                                 echo number_format($total_net_purchase, 2); ?>
                              </span>
                           </p>
                        </a>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/12')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           Expenses (Direct/Mfg.)</a>
                        <span class="ms-auto">
                           <a href="{{url('account-balance-by-group/12')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <?php echo number_format($data['direct_expenses'], 2); ?></a>
                        </span>
                     </div>
                     <?php
                     $gross_profit = 0;$gross_loss = 0;
                     $total_net_sale = $data['closing_stock'] + $data['tot_sale_amt'] + $data['direct_income'];
                     $total_net_purchase = $data['opening_stock'] + $data['tot_purchase_amt'] - $data['direct_expenses'];
                     $balance = $total_net_purchase - $total_net_sale;
                     if($balance < 0) {
                        $gross_profit = str_replace("-","",$balance);
                        ?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                           Gross Profit
                           <span class="ms-auto"><?php echo number_format(str_replace("-","",$gross_profit), 2); ?></span>
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
                           <?php 
                           if($total_net_purchase>$total_net_sale){
                              $first_total = $total_net_purchase;
                           }else if($total_net_purchase<$total_net_sale){
                              $first_total = $total_net_sale;
                           }else{
                              $first_total = $total_net_purchase;
                           }
                           echo number_format($first_total, 2); ?>
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
                           <a href="{{url('account-balance-by-group/15')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}"><?php echo number_format($data['indirect_expenses'],2);?></span></a>
                     </div>
                     <?php 
                     $nett_loss = 0;$nett_profit = 0;
                     $nett_expenses_total = $data['indirect_expenses'] + $gross_loss;
                     $nett_income_total = $data['indirect_income'] + $gross_profit;
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
                        <span class="ms-auto"><?php echo number_format($nett_profit, 2); ?></span>
                           <?php
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Total
                        <span class="ms-auto"><?php echo number_format($nett_final_amount, 2); ?></span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                  </div>
               </div>
               <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                  <div class="px-3 fw-bold py-12 border-bottom-divider">Credit (Rs.)</div>
                  <div class="row p-0 m-0">
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">Closing Stock
                        <span class="ms-auto"><?php echo number_format($data['closing_stock'], 2); ?></span>
                     </div>
                     <div class="col-md-12 fw-500 font-14  px-3 py-12 border-bottom-divider">
                        <a class="text-decoration-none text-primary fw-500" href="{{url('sale-by-month-detail')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                           <p class="d-flex m-0">
                           Sale
                           <span class="ms-auto"><?php echo number_format($data['tot_sale_amt'], 2); ?></span>
                           </p>                        
                           <p class="d-flex m-0 py-12">Sale Return
                              <span class="ms-auto"><?php echo number_format($data['tot_sale_return_amt'], 2); ?></span>
                           </p>
                           <p class="d-flex m-0 py-12 align-items-center">
                              Net Sale
                              <span class="ms-auto h-1-divider">----------</span>
                           </p>
                           <p class="d-flex m-0 ">
                              <span class="ms-auto">
                                 <?php 
                                 $total_net_sale = $data['tot_sale_amt'] - $data['tot_sale_return_amt'];
                                 echo number_format($total_net_sale, 2); ?>
                              </span>
                           </p>
                        </a>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/13')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                        Income (Direct/Opr.)</a>
                        <span class="ms-auto"><a href="{{url('account-balance-by-group/13')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}"><?php echo number_format($data['direct_income'], 2); ?></a></span>
                     </div>
                     <?php                     
                     if($gross_loss > 0) {?>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                           Gross Loss
                           <span class="ms-auto"><?php echo number_format($gross_loss, 2); ?></span>
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
                           <?php                           
                           echo number_format($first_total, 2) ?>
                        </span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <?php 
                        if($gross_profit>0){ ?>
                           Gross Profit b/d
                           <span class="ms-auto"><?php echo number_format($gross_profit,2); ?></span>
                           <?php
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>
                        

                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <a href="{{url('account-balance-by-group/14')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}">
                        Income (Indirect)</a>
                        <span class="ms-auto"><a href="{{url('account-balance-by-group/14')}}/{{$data['financial_year']}}/{{$from_date}}/{{$to_date}}"><?php echo number_format($data['indirect_income'],2);?></a></span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        <?php 
                        if($nett_loss>0){ ?>
                           Net Loss
                        <span class="ms-auto"><?php echo number_format($nett_loss,2);?></span>
                        <?php 
                        }else{
                           echo '<span class="ms-auto">&nbsp;</span>';
                        }
                        ?>
                        
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                        Total
                        <span class="ms-auto"><?php echo number_format($nett_final_amount,2);?></span>
                     </div>
                     <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider h-46"></div>
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
   $(document).ready(function() {
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
   });
</script>
@endsection