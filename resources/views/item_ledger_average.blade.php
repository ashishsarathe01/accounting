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
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Items Ledger</h5> 
                  <div class="d-flex align-items-center gap-2">
       
      
    </div>
               <form class="" id="frm" method="GET" action="{{ route('item-ledger-average') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <!-- Series Dropdown -->
<p class="text-nowrap m-0 font-14 fw-semibold font-heading">Series:</p>
<select class="form-select select2-single w-min-230 ms-xxl-2 w-25" aria-label="Select Series" id="series" name="series" required>
    <option value="">Select Series</option>
    @foreach($series as $value)
        <option 
            value="{{ $value->series }}" 
            {{ ($selected_series == $value->series) ? 'selected' : '' }}>
            {{ $value->series }}
        </option>
    @endforeach
</select>
                     <p class="text-nowrap font-14 fw-500 font-heading my-2 my-xxl-0" style="margin-left:10px;">Items </p>
                     
                     <select class="form-select select2-single w-min-200 ms-xxl-2 w-25" aria-label="Default select example" id="items_id" name="items_id" required>
                        <option value="">Select</option>
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
                     <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-230">
                        <input type="date" id="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="<?php if(isset($_GET['from_date'])){ echo $_GET['from_date'];}else{ echo $fdate;}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator   w-min-230 ms-xxl-2">
                        <input type="date" id="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="<?php  if(isset($_GET['to_date'])){ echo $_GET['to_date']; }else{ echo $tdate;}?>" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     </div>
                     <div class="calender-administrator   w-min-130 ms-xxl-1">
                        <button type="button" class="btn  btn-xs-primary" id="serachBtn">SUBMIT</button>
                     </div>
                  </div>
               </form>             
            </div>
            <div class="display-sale-month  bg-white table-view shadow-sm">
               <table id="acc_table1" class="table-striped table-bordered table m-0 shadow-sm ">                  
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink">                        
                        <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. In (Kg)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. Out (Kg)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Qty. Balance (Kg)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Average Rate</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Amount</th>          
                     </tr>
                  </thead>
                  <tbody>
                  <tr>
                          <td>Opening</td>
                          <td style="text-align: right;"></td>
                          <td style="text-align: right;"></td>
                          <td style="text-align: right;">{{$opening_weight}}</td>
                          <td style="text-align: right;">                             
                              @php 
                              
                              if($opening_weight != 0 && $opening_weight != ''){
                                 echo $average_price = round($opening_amount/$opening_weight,6);
                              }else{
                                 $average_price = 0;
                              }
                              
                              @endphp
                           </td>
                           <td style="text-align: right;">
                           @php                            
                              echo formatIndianNumber($opening_amount,2);
                           @endphp</td>
                        </tr>
                     @foreach($average_data as $purchase)                        
                        <tr class="average_details" data-date="{{$purchase->stock_date}}" style="cursor: pointer;">
                           <td>{{date('d-m-Y',strtotime($purchase->stock_date))}}</td>
                           <td style="text-align: right;">{{formatIndianNumber($purchase->purchase_weight)}}</td>
                           <td style="text-align: right;">{{formatIndianNumber($purchase->sale_weight)}}</td>
                           <td style="text-align: right;">{{formatIndianNumber($purchase->average_weight)}}</td>
                           <td style="text-align: right;">{{$purchase->price}}</td>
                           <td style="text-align: right;">{{formatIndianNumber($purchase->amount,2)}}</td>
                        </tr>
                     @endforeach
                    
                  </tbody>                  
                  </div>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>

<div class="modal fade" id="item_ledger_details" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog  modal-dialog-centered modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <h4 class="modal-title">Average Details <span id="modal_parameter_name"></span></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center p-0">
            <table class="table table-striped table-bordered table-sm m-0 shadow-sm">
               <thead>
                  <tr class=" font-12 text-body bg-light-pink">                        
                     <th class="w-min-120 border-none bg-light-pink text-body">Type </th>
                     <th class="w-min-120 border-none bg-light-pink text-body">Invoice No.</th>
                     <th class="w-min-120 border-none bg-light-pink text-body">Party</th>
                     <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Quantity</th>
                     <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;">Amount</th>     
                  </tr>
               </thead>
               <tbody>
               </tbody>
            </table>
         </div>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-danger cancel">Close</button>
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      $('#acc_table').DataTable({
            // order: [0, 'DESC']
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
                     window.location = "edit-sale/"+id;
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
   function getLedger(){
      var id = $("#items_id").val();
      if(id != ''){
         $("#frm").submit();
      }else{
         alert("Please select item.");
      }
   }
    $(".select2-single").select2();

    $(".average_details").click(function(){
      var date = $(this).attr('data-date');      
      if(date != ''){
         $.ajax({
            url: '{{url("get-item-average-details")}}',
            type: 'POST',
            dataType: 'JSON',
            data: {
               _token: '<?php echo csrf_token() ?>',
               date: date,
               items_id: '<?php echo $_GET['items_id'];?>',
               series: '<?php echo $_GET['series'];?>'
            },
            success: function(res){
               if(res.status == true){
                  let html = "";
                  let total_weight = 0;
                  let total_amount = 0;
                  res.data.forEach(function(item) {
                     let redirect_url = "";
                     if(item.type=="PURCHASE"){
                        const saleInvoiceBaseUrl = "{{ route('purchase-invoice', 'ID') }}";
                        redirect_url = saleInvoiceBaseUrl.replace('ID', item.purchase_id);
                     }else if(item.type=="SALE"){                        
                        const saleInvoiceBaseUrl = "{{ route('sale-invoice', 'ID') }}";
                        redirect_url = saleInvoiceBaseUrl.replace('ID', item.sale_id);
                     }else if(item.type=="PURCHASE RETURN"){
                        const saleInvoiceBaseUrl = "{{ route('purchase-return-invoice', 'ID') }}";
                        redirect_url = saleInvoiceBaseUrl.replace('ID', item.purchase_return_id);
                     }else if(item.type=="SALE RETURN"){
                        const saleInvoiceBaseUrl = "{{ route('sale-return-invoice', 'ID') }}";
                        redirect_url = saleInvoiceBaseUrl.replace('ID', item.sale_return_id);
                     }else if(item.type=="STOCK TRANSFER IN"){
                        redirect_url = "";
                     }else if(item.type=="STOCK TRANSFER OUT"){
                        redirect_url = "";
                     }
                     html += "<tr style='cursor:pointer' onclick='reDirectFun(\"" + redirect_url + "\")'>";
                     html += "<td>"+item.type+"</td>";
                     if(item.type=="PURCHASE"){
                        total_weight += parseFloat(item.purchase_weight);
                        total_amount += parseFloat(item.purchase_total_amount);
                        html += "<td>"+item.purchase_voucher+"</td>";
                        html += "<td>"+item.purchase_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.purchase_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.purchase_total_amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                     }else if(item.type=="SALE"){
                        total_weight -= parseFloat(item.sale_weight);
                        html += "<td>"+item.sale_voucher+"</td>";
                        html += "<td>"+item.sale_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.sale_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'></td>";
                     }else if(item.type=="PURCHASE RETURN"){
                        if(item.purchase_return_weight!=null && item.purchase_return_weight!=""){
                           total_weight-= parseFloat(item.purchase_return_weight);
                        }
                        total_amount -= parseFloat(item.purchase_return_amount);
                        html += "<td>"+item.pr_voucher+"</td>";
                        html += "<td>"+item.pr_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.purchase_return_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.purchase_return_amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                     }else if(item.type=="SALE RETURN"){
                        total_weight += parseFloat(item.sale_return_weight);
                        html += "<td>"+item.sr_voucher+"</td>";
                        html += "<td>"+item.sr_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.sale_return_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'></td>";
                     }else if(item.type=="STOCK TRANSFER IN"){
                        total_weight += parseFloat(item.stock_transfer_in_weight);
                        total_amount += parseFloat(item.stock_transfer_in_amount);
                        html += "<td>"+item.st_in_voucher+"</td>";
                        html += "<td>"+item.st_in_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_transfer_in_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_transfer_in_amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                     }else if(item.type=="STOCK TRANSFER OUT"){
                        total_weight -= parseFloat(item.stock_transfer_weight);
                        html += "<td>"+item.st_out_voucher+"</td>";
                        html += "<td>"+item.st_ot_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_transfer_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'></td>";
                     }else if(item.type=="STOCK JOURNAL GENERATE"){
                        total_weight += parseFloat(item.stock_journal_in_weight);
                        total_amount += parseFloat(item.stock_journal_in_amount);
                        html += "<td>"+item.sj_in_voucher+"</td>";
                        html += "<td>"+item.sj_in_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_journal_in_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_journal_in_amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                     }else if(item.type=="STOCK JOURNAL CONSUME"){
                        total_weight -= parseFloat(item.stock_journal_out_weight);
                        html += "<td>"+item.sj_out_voucher+"</td>";
                        html += "<td>"+item.sj_out_account+"</td>";
                        html += "<td style='text-align: right;'>"+Number(item.stock_journal_out_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</td>";
                        html += "<td style='text-align: right;'></td>";
                     }
                     html += "</tr>";
                  });
                  html += "<tr>";
                  html += "<th></th>";
                  html += "<th></th>";
                  html += "<th>Total</th>";
                  html += "<th style='text-align: right;'>"+Number(total_weight).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</th>";
                  html += "<th style='text-align: right;'>"+Number(total_amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</th>";         
                  html += "</tr>";
                  html += "<tr>";
                  
                  $("#modal_parameter_name").html(" | Opening Weight : "+res.opening_weight+" | Opening Amount : "+res.opening_amount);
                  $("#item_ledger_details tbody").html(html);
                  $("#item_ledger_details").modal('show');
               }else{
                  alert("No data found.");
               }
            }
         });
      }
   });
   $(".cancel").click(function() {
       $("#item_ledger_details").modal("hide");
   });
   function reDirectFun(url){
      window.location.href = url;
   }
</script>
@endsection