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
            <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Items Ledger Average</a>
                     </li>
                  </ol>
               </nav>
               <form class="" id="frm" method="GET" action="{{ route('item-ledger-average') }}">
                  @csrf
                  <div class="d-xxl-flex d-block  align-items-center">
                     <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">Items </p>
                     <select class="form-select select2-single w-min-230 ms-xxl-2" aria-label="Default select example" id="items_id" name="items_id" required>
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
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">Items Ledger</h5>               
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
                     @php $average_price = 0;  @endphp
                     
                     @if($opening_weight!=0 && $opening_weight!='')
                        <tr>
                          <td>Opening</td>
                          <td style="text-align: right;"></td>
                          <td style="text-align: right;"></td>
                          <td style="text-align: right;">{{$opening_weight}}</td>
                          <td style="text-align: right;">
                              @if($opening_weight!=0)
                                 {{number_format($opening_amount/$opening_weight,2)}}
                              @endif
                              @php $average_price = round($opening_amount/$opening_weight,2);@endphp
                           </td>
                           <td style="text-align: right;">
                           @php 
                           if($second_total_amount==0){
                              $opening_amount = round($opening_amount,2);
                           }else{
                              $opening_amount = $opening_weight*$average_price;
                           } 
                           
                           echo number_format($opening_amount,2);
                           @endphp</td>
                        </tr>
                     @endif
                     @php 
                        $total_amount_result = array();
                        foreach ($item_in_data as $element){                          
                           $total_amount_result[$element->txn_date][] = array("amount"=>$element->total_price,"weight"=>$element->in_weight);
                        }             
                     @endphp
                     @foreach($item_data as $i=>$item)
                        @php                                                     
                           if($item->in_weight!=0 && $item->in_weight!=''){
                              foreach($total_amount_result as $k=>$v){
                                 if($k==$item->txn_date){
                                    $average_price = ($opening_amount + $v[0]['amount'])/($v[0]['weight'] + $opening_weight);
                                 }
                              }                              
                           }
                           $average_price = round($average_price,2);                        
                           $opening_weight = $opening_weight + $item->in_weight - $item->out_weight;
                           $opening_amount = $opening_weight * $average_price;
                        @endphp
                        <tr>
                           <td>{{date('d-m-Y',strtotime($item->txn_date))}}</td>
                           <td style="text-align: right;">{{$item->in_weight}}</td>
                           <td style="text-align: right;">{{$item->out_weight}}</td>
                           <td style="text-align: right;">{{$opening_weight}}</td>
                           <td style="text-align: right;">{{number_format($average_price,2)}}</td>
                           <td style="text-align: right;">{{number_format($opening_amount,2)}}</td>
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
</script>
@endsection