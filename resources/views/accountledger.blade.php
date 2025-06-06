@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if(session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if(session('success'))
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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Account Ledger</a>
                     </li>
                  </ol>
               </nav>
               <form class="" id="frm" method="get" action="{{ route('accountledger.filter') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">Account</p>
                     <select class="form-select select2-single w-min-230 ms-xxl-2" aria-label="Default select example" id="party" name="party" required>
                        <option value="">Select</option>
                        <?php
                        foreach ($party_list as $value) {
                           $sel = '';
                           if(isset($party_id) && $party_id != '') {
                              if($party_id == $value->id)
                                 $sel = 'selected';
                              } ?>
                           <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                           <?php 
                        } ?>
                     </select>
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-130">
                        <input type="date" id="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="<?php if(isset($_GET['from_date'])){ echo $_GET['from_date'];}else{ echo date('Y-m-').'01';}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator   w-min-130 ms-xxl-1">
                        <input type="date" id="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="<?php  if(isset($_GET['to_date'])){ echo $_GET['to_date']; }else{ echo date('Y-m-t');}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator   w-min-130 ms-xxl-1">
                        <button type="button" class="btn  btn-xs-primary" id="serachBtn">SUBMIT</button>
                     </div>
                  </div>
               </form>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">
                  Account Ledger
               </h5>
               <span class="ms-auto font-14 fw-bold font-heading">
                  Opening Bal. : {{abs($opening)}} @if($opening<0) CR @else DR @endif 
               </span>
            </div>
            <div class="display-sale-month  bg-white table-view shadow-sm">
               <table id="acc_table1" class="table-striped table-bordered table m-0 shadow-sm ">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Type</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: center;">Vch/Bill No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Debit (Rs.)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Credit(Rs.)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Balance(Rs.)</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $tot_blance = $opening;
                     $tot_crt = 0;$tot_dbt = 0;setlocale(LC_MONETARY, 'en_IN');
                     foreach($ledger as $value) {?>
                        <tr class="font-14 font-heading bg-white account_tr" data-type="{{$value['entry_type']}}" data-id="{{$value['entry_type_id']}}" style="cursor: pointer;">
                           <td class="w-min-120 "><?php echo date("d-m-Y", strtotime($value['txn_date'])); ?></td>
                           <td class="w-min-120 ">
                              <?php 
                              if($value['entry_type']==1){
                                 echo "SupO";
                              }else if($value['entry_type']==2){
                                 echo "SupI";
                              }else if($value['entry_type']==3){
                                 echo "Sale Return";
                              }else if($value['entry_type']==4){
                                 echo "Purchase Return";
                              }else if($value['entry_type']==5){
                                 echo "Payment";
                              }else if($value['entry_type']==6){
                                 echo "Receipt";
                              }else if($value['entry_type']==7){
                                 echo "Journal";
                              }else if($value['entry_type']==8){
                                 echo "Contra";
                              }
                              ?>
                           </td>
                           <td class="w-min-120 " style="text-align: center;"><?php echo $value['bill_no'];?></td>
                           <td class="w-min-120 ">
                              <?php                               
                              echo $value['account'];
                              ?>
                           </td>
                           <td class="w-min-120 " style="text-align: right;">
                              <?php
                              if(!empty($value['debit'])){
                                 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    echo $value['debit'];
                                 }else{
                                    echo money_format('%!i', $value['debit']);
                                 }
                                 
                                 $tot_blance = $tot_blance + $value['debit'];
                                 $tot_dbt = $tot_dbt + abs($value['debit']);
                              }
                              ?>
                           </td>
                           <td class="w-min-120 " style="text-align: right;">
                              <?php
                              if(!empty($value['credit'])) {
                                 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    echo $value['credit'];
                                 }else{
                                    echo money_format('%!i', $value['credit']);
                                 }
                                 
                                 $tot_blance = $tot_blance - $value['credit'];
                                 $tot_crt = $tot_crt + abs($value['credit']);
                              }
                              ?>
                           </td>
                           <td style="text-align: right;">
                              <?php 
                              
                              if($tot_blance<0){
                                 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    echo abs($tot_blance). ' Cr';
                                 }else{
                                    echo money_format('%!i', abs($tot_blance)) . ' Cr';
                                 }                                 
                              }else{
                                 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    echo $tot_blance. ' Dr';
                                 }else{
                                    echo money_format('%!i', $tot_blance) . ' Dr';
                                 }                                 
                              }                              
                              ?>
                           </td>
                        </tr>
                        <?php 
                     } ?>
                  </tbody>
                  <div>
                     <tr class=" font-14 font-heading bg-white">
                        <td class="w-min-120" colspan="4"></td>
                        <td class="w-min-120 fw-bold" style="text-align: right;">
                           <?php 
                           if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                              echo $tot_dbt;
                           }else{
                              echo money_format('%!i', $tot_dbt);
                           }
                           ?>
                        </td>
                        <td class="w-min-120 fw-bold" style="text-align: right;">
                           <?php 
                           if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                              echo $tot_crt;
                           }else{
                              echo money_format('%!i', $tot_crt);
                           }                           
                           ?>
                        </td>
                        <td></td>
                     </tr>
                  </div>
                  <div>
                     <tr class="font-14 fw-bold font-heading">
                        <td class="text-end " colspan="7" style="text-align: right;">
                           Closing Bal. : <?php 
                           if($tot_blance>0){
                              if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                 echo abs($tot_blance). ' Cr';
                              }else{
                                 echo money_format('%!i', abs($tot_blance)) . ' Cr';
                              }                                 
                           }else{
                              if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                 echo $tot_blance. ' Dr';
                              }else{
                                 echo money_format('%!i', $tot_blance) . ' Dr';
                              }                                 
                           }?>
                        </td>
                     </tr>
                  </div>
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
      $('#acc_table').DataTable({
         //   order: [0, 'DESC']
      });
   });
   $(document).ready(function() {      
      $("#serachBtn").click(function(){
         getLedger();
      });
      $(".account_tr").click(function(){
         let type = $(this).attr('data-type');
         let id = $(this).attr('data-id');
         if(type!='' && id!=''){
            if(type==1){
               window.location = "edit-sale/"+id;
            }else if(type==2){
               window.location = "purchase-edit/"+id;
            }else if(type==3){
               window.location = "sale-return-edit/"+id;
            }else if(type==4){
               window.location = "purchase-return-edit/"+id;
            }else if(type==5){
               window.location = "payment/"+id+"/edit";
            }else if(type==6){
               window.location = "receipt/"+id+"/edit";
            }else if(type==7){
               window.location = "journal/"+id+"/edit";
            }else if(type==8){
               window.location = "contra/"+id+"/edit";
            }            
         }
      });
   });
   function getLedger(){
      var id = $("#party").val();
      if (id != ''){
         $("#frm").submit();
      }else{
         alert("Please select account.");
      }
   }
   $( ".select2-single, .select2-multiple" ).select2(  );
</script>
@endsection