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
               
               <form class="" id="frm" method="GET" action="{{ route('itemledger.filter') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <select class="form-select select2-single w-min-230 ms-xxl-2" aria-label="Default select example" id="items_id" name="items_id" required>
                        <option value="">Select Item</option>
                        <option value="all" <?php if(isset($item_id) && $item_id != '' && $item_id=='all'){ echo "selected";}?>>All Item</option>
                        <?php
                        foreach($item_list as $value){
                           $sel = '';
                           if(isset($item_id) && $item_id != '') {
                              if($item_id == $value->id){
                                 $sel = 'selected';
                              }
                           }?>
                           <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                              <?php 
                        } ?>
                     </select>
                     <select class="form-select select2-single w-min-230 ms-xxl-2" aria-label="Default select example" id="selected_series" name="selected_series" required>
                        
                        <option value="all" <?php if(isset($selected_series) && $selected_series != '' && $selected_series=='all'){ echo "selected";}?>>All Series</option>
                        <?php
                        foreach($series as $value){
                           $sel = '';
                           if(isset($selected_series) && $selected_series != '') {
                              if($selected_series == $value->series){
                                 $sel = 'selected';
                              }
                           }?>
                           <option <?php echo $sel; ?> value="<?php echo $value->series; ?>"><?php echo $value->series; ?></option>
                              <?php 
                        } ?>
                     </select>
                     
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-230 from_date_div">
                        <input type="date" id="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="<?php if(isset($_GET['from_date'])){ echo $_GET['from_date'];}else{ echo $fdate;}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" style="height: 33px;">
                     </div>
                     <div class="calender-administrator   w-min-230 ms-xxl-2">
                        <input type="date" id="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="<?php  if(isset($_GET['to_date'])){ echo $_GET['to_date']; }else{ echo $tdate;}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" style="height: 33px;">
                     </div>
                     <div class="calender-administrator   w-min-130 ms-xxl-1">
                        <button type="button" class="btn  btn-xs-primary" id="serachBtn">SUBMIT</button>
                     </div>
                  </div>
               </form>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Items Ledger</h5>               
               <span class="ms-auto font-14 fw-bold font-heading">
                   Opening Bal. : {{formatIndianNumber(abs($opening))}} @if($opening<0)  @else  @endif
               </span>
            </div>
            <div class="display-sale-month  bg-white table-view shadow-sm">
               <table id="acc_table1" class="table-striped table-bordered table m-0 shadow-sm ">                  
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        @if(isset($item_id) && $item_id=='all')
                           <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Type</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Amount</th>
                        @else
                           <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Type</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: center;">Vch/Bill No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Particulars</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. In</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. Out</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Balance</th>
                        @endif                        
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $tot_blance = $opening;
                     $in = 0; $out = 0;setlocale(LC_MONETARY, 'en_IN');$qty = 0;
                     $tot_blance1 = $opening;
                     foreach($items as $value){
                        if(isset($item_id) && $item_id=='all'){
                           ?>
                           <tr class="font-14 font-heading bg-white redirect_average_page"  data-item_id="{{$value['item_id']}}" data-from_date="{{$fdate}}" data-to_date="{{$_GET['to_date']}}" style="cursor: pointer;">
                              <td class="w-min-120"><?php echo $value['item_name']; ?></td>
                              <td class="w-min-120">Item</td>
                              <td class="w-min-120" style="text-align: right;">
                                 <?php 
                                 echo formatIndianNumber($value['average_weight']);
                                 ?>
                              </td>
                              <td class="w-min-120"><?php echo $value['unit_name'];?></td>
                              <td class="w-min-120" style="text-align: right;">
                                 <?php 
                                  
                                 echo formatIndianNumber($value['amount']);?>                                    
                              </td>
                           </tr>
                           <?php
                           $qty = $qty + $value['average_weight'];
                           $tot_blance = $tot_blance + $value['amount'];
                        }else{
                           $inWeight = isset($value['in_weight']) ? $value['in_weight'] : 0;
                           $outWeight = isset($value['out_weight']) ? $value['out_weight'] : 0;
                           $tot_blance1 += $inWeight - $outWeight;
                           $in += $inWeight;
                           $out += $outWeight;
                           $redStyle = $tot_blance1 < 0 ? 'background-color: white !important; color: red !important;' : '';
                           ?>
                           <tr class="font-14 font-heading bg-white account_tr" style="cursor: pointer; " data-type="{{$value['source']}}" data-id="{{$value['source_id']}}" data-einvoice_status="{{$value['einvoice_status']}}">
                              <td class="w-min-120 " style="<?= $redStyle ?>"><?php echo date("d-m-Y", strtotime($value['txn_date'])); ?></td>
                              <td class="w-min-120" style="<?= $redStyle ?>"><?php echo $value['type']; ?></td>
                              <td class="w-min-120" style="text-align: center;<?= $redStyle ?>"><?php echo $value['bill_no']; ?></td>
                              <td class="w-min-120" style="<?= $redStyle ?>"><?php echo $value['account_name'];?></td>
                              <td class="w-min-120 " style="text-align: right;<?= $redStyle ?>">
                                 <?php 
                                 echo formatIndianNumber($value['in_weight']);
                                 
                                 ?>
                              </td>
                              <td class="w-min-120 " style="text-align: right;<?= $redStyle ?>">
                                 <?php 
                                 echo formatIndianNumber($value['out_weight']);
                                ?>
                              </td>
                              <td class="w-min-120 " style="text-align: right;<?= $redStyle ?>">
                                 <?php
                                 if(isset($value['in_weight'])){
                                    $tot_blance = $tot_blance + $value['in_weight'];
                                    echo formatIndianNumber($tot_blance);
                                    
                                     //$in = $in + $value['in_weight'];
                                 }else if(isset($value['out_weight'])) {
                                    $tot_blance = $tot_blance - $value['out_weight'];
                                    echo formatIndianNumber($tot_blance);
                                    
                                    //$out = $out + $value['out_weight'];
                                 }?>
                              </td>
                           </tr>
                           <?php
                        }                       
                     } ?>
                  </tbody>
                  <?php 
                  if(isset($item_id) && $item_id!='all'){?>
                     <div>
                        <tr class=" font-14 font-heading bg-white">
                           <td class="w-min-120" colspan="4"></td>
                           <td class="w-min-120 fw-bold" style="text-align: right;">
                              <?php 
                              echo formatIndianNumber($in);?>
                           </td>
                           <td class="w-min-120 fw-bold" style="text-align: right;">
                              <?php 
                              echo formatIndianNumber($out);                             
                              ?>
                           </td>
                           <td></td>
                        </tr>
                     </div>
                     <?php 
                  } ?>
                  <div>
                  <?php 
                  if(isset($item_id) && $item_id!='all'){?>
                     <tr class="font-14 fw-bold font-heading">
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th style="text-align: right;">Closing Bal</th>
                        <th class="text-end" style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($tot_blance);                         
                           ?>
                        </th>
                        
                     </tr>
                     
                     <?php 
                  }else{ ?>
                     <tr class="font-14 fw-bold font-heading">
                        <th></th>
                        <th></th>
                        <th style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($qty);                           
                           ?>                           
                        </th>
                        <th></th>
                        <th class="text-end" style="text-align: right;">
                           <?php 
                           echo formatIndianNumber($tot_blance);                          
                           ?>
                        </th>                        
                     </tr>
                     <?php 
                  }?>
                  
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
      changeItem();
   });
    
   $(document).ready(function() {      
      $("#serachBtn").click(function(){
         getLedger();
      });
      $(".account_tr").click(function(){
         let type = $(this).attr('data-type');
         let id = $(this).attr('data-id');
         let einvoice_status = $(this).attr('data-einvoice_status');
         if(type!='' && id!=''){
            $.ajax({
               url: '{{url("set-redircet-url")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  url: window.location.href
               },
               success: function(data){
                  if(type==1){
                     if(einvoice_status==1){
                        window.location = "sale-invoice/"+id;
                     }else{
                        window.location = "edit-sale/"+id;
                     }
                  }else if(type==2){
                     window.location = "purchase-edit/"+id;
                  }else if(type==3){
                     window.location = "edit-stock-journal/"+id;
                  }
               }
            });
         }
      });   
   });
   $("#items_id").change(function(){
      changeItem();
   });
   function changeItem(){
      $(".from_date_div").show();
      if($("#items_id").val()=='all'){
         $(".from_date_div").hide();
      }
   }
   function getLedger(){
      var id = $("#items_id").val();
      if(id != ''){
         $("#frm").submit();
      }else{
         alert("Please select item.");
      }
   }
   $(".select2-single").select2();
   $(".redirect_average_page").click(function(){
      let item_id = $(this).attr('data-item_id');
      let from_date = $(this).attr('data-from_date');
      let to_date = $(this).attr('data-to_date');
      if($("#selected_series").val()=="all"){
         window.location = "{{url('item-ledger-average-by-godown')}}/?items_id="+item_id+"&from_date="+from_date+"&to_date="+to_date;
      }else{
         window.location = "{{url('item-ledger-average')}}/?items_id="+item_id+"&from_date="+from_date+"&to_date="+to_date+"&series="+$("#selected_series").val();
      }
      
   })
</script>
@endsection