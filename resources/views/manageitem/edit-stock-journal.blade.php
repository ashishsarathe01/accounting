@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
   .form-control {
      height: 52px;
   }
</style>
<div class="list-of-view-company ">
   <?php
      $items_list = '<option value="">Select Item</option>';
      foreach($items as $item){
         $items_list.='<option value="'.$item->id.'">'.$item->name.'</option>';
      }
   ?>
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item"><a class="font-12 text-body text-decoration-none" href="#">Dashboard</a></li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Stock Journal</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Stock Journal</h5>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('update-stock-journal') }}">
               @csrf
               <div class="row">
                  <input type="hidden" name="edit_id" value="{{$journal->id}}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required value="{{$journal->jdate}}" >
                  </div>
                  <div class="mb-3 col-md-8">
                     <label for="name" class="form-label font-14 font-heading">Narration</label>
                     <input type="text" id="narration" class="form-control" name="narration" placeholder="Narration" value="{{$journal->narration}}">
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-120 border-none bg-light-pink text-body ">Consume Item</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Weight</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">New Generated Item</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Weight</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $i=1; @endphp
                        @foreach($journal_details as $value)
                           @if($value->consume_item!='')
                              <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                                 <td class="">
                                    <select class="form-control consume_item" name="consume_item[]" data-id="{{$i}}" id="consume_item_{{$i}}">
                                       <option value="">Item</option>      
                                       @foreach($items as $item)
                                          <option value="{{$item->id}}" @if($item->id==$value->consume_item) selected @endif>{{$item->name}}</option>
                                       @endforeach
                                    </select>
                                 </td>
                                 <td class="">
                                    <input type="text" name="consume_weight[]" class="form-control consume_weight" data-id="{{$i}}" id="consume_weight_{{$i}}" placeholder="Weight" value="{{$value->consume_weight}}">
                                 </td>
                                 <td class="">
                                    <input type="text" name="consume_price[]" class="form-control consume_price" data-id="{{$i}}" id="consume_price_{{$i}}" placeholder="Price" value="{{$value->consume_price}}">
                                 </td>
                                 <td class="">
                                    <input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="{{$i}}" id="consume_amount_{{$i}}" placeholder="Amount" readonly value="{{$value->consume_amount}}">
                                 </td>                                 
                                 <td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="{{$i}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td>
                              </tr>
                           @endif
                           @php $i++; @endphp
                        @endforeach
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="consume_weight_total">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total">0</td>
                           <td class="fw-bold"></td>
                        </tr>
                     </div>
                  </table>
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-120 border-none bg-light-pink text-body ">Generated Item</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Weight</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $j=1; @endphp
                        @foreach($journal_details as $value)
                           @if($value->new_item!='')
                              <tr id="tr1_{{$j}}" class="font-14 font-heading bg-white">
                                 <td class="">
                                    <select class="form-control generated_item" name="generated_item[]" data-id="{{$j}}" id="generated_item_{{$j}}">
                                       <option value="">Item</option>      
                                       @foreach($items as $item)
                                          <option value="{{$item->id}}" @if($item->id==$value->new_item) selected @endif>{{$item->name}}</option>
                                       @endforeach
                                    </select>
                                 </td>
                                 <td class="">
                                    <input type="text" name="generated_weight[]" class="form-control generated_weight" data-id="{{$j}}" id="generated_weight_{{$j}}" placeholder="Weight" value="{{$value->new_weight}}">
                                 </td>
                                 <td class="">
                                    <input type="text" name="generated_price[]" class="form-control generated_price" data-id="{{$j}}" id="generated_price_{{$j}}" placeholder="Price" value="{{$value->new_price}}">
                                 </td>
                                 <td class="">
                                    <input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="{{$j}}" id="generated_amount_{{$j}}" placeholder="Amount" readonly value="{{$value->new_amount}}">
                                 </td>                           
                                 <td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="{{$j}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td>
                              </tr>
                           @endif
                           @php $j++; @endphp
                        @endforeach
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more1"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="consume_weight_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>

                        </tr>
                     </div>
                  </table>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('stock-journal') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="button" value="SUBMIT" class="btn btn-xs-primary savebtn">
                  </div>
               </div>
            </form>
         </div>
         <div class="col-lg-1 d-flex justify-content-center">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">F1
                  <span class="ps-1 fw-normal text-body">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  F3
                  <span class="ps-1 fw-normal text-body">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  Search Menu
               </button>
            </div>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   calculateAmount(1);
   calculateAmountNew(1);
   var add_more_count = '<?php echo $i;?>';
   $(".add_more").click(function() {
      add_more_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = '<tr id="tr_'+add_more_count+'" class="font-14 font-heading bg-white"><td class=""><select class="form-control consume_item" name="consume_item[]" data-id="'+add_more_count+'" id="consume_item_1'+add_more_count+'">'+optionElements+'</select></td><td class=""><input type="text" name="consume_weight[]" class="form-control consume_weight" data-id="'+add_more_count+'" id="consume_weight_'+add_more_count+'" placeholder="Weight"></td><td class=""><input type="text" name="consume_price[]" class="form-control consume_price" data-id="'+add_more_count+'" id="consume_price_'+add_more_count+'" placeholder="Price"></td><td class=""><input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="'+add_more_count+'" id="consume_amount_'+add_more_count+'" placeholder="Amount" readonly></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
   });
   var add_more_count1 = '<?php echo $j;?>';
   $(".add_more1").click(function() {
      add_more_count1++;
      var $curRow = $(this).closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = '<tr id="tr1_'+add_more_count1+'" class="font-14 font-heading bg-white"><td class=""><select class="form-control generated_item" name="generated_item[]" data-id="'+add_more_count1+'" id="generated_item_1'+add_more_count1+'">'+optionElements+'</select></td><td class=""><input type="text" name="generated_weight[]" class="form-control generated_weight" data-id="'+add_more_count1+'" id="generated_weight_'+add_more_count1+'" placeholder="Weight"></td><td class=""><input type="text" name="generated_price[]" class="form-control generated_price" data-id="'+add_more_count1+'" id="generated_price_'+add_more_count1+'" placeholder="Price"></td><td class=""><input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="'+add_more_count1+'" id="generated_amount_'+add_more_count1+'" placeholder="Amount" readonly></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="' + add_more_count1+ '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
   });
   $(document).on("click", ".remove", function(){
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      calculateAmount(1)
   });
   $(document).on("click", ".remove1", function(){
      let id = $(this).attr('data-id');
      $("#tr1_" + id).remove();
      calculateAmountNew(1)
   });
   $(".savebtn").click(function(){
      let date = $("#date").val();
      let item_count = 0;
      $(".consume_item").each(function(){
         let id = $(this).attr('data-id');
         let consume_item = $(this).val();
         let consume_weight = $("#consume_weight_"+id).val();
         let new_item = $("#new_item_"+id).val();
         let new_weight = $("#new_weight_"+id).val();
         if(consume_item!="" && consume_weight!="" && new_item!="" && new_weight!=""){
            item_count++;
         }
      });
      if(item_count==0){
         alert("Please item and weight.");
         return false;
      }
      $("#frm").submit();
   });
   $(document).on('keyup','.consume_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.consume_price',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.generated_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   $(document).on('keyup','.generated_price',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   function calculateAmount(id){
      let consume_price = $("#consume_price_"+id).val();
      let consume_weight = $("#consume_weight_"+id).val();
      let cweight = 0;
      $(".consume_weight").each(function(){
         if($(this).val()!=''){
            cweight = parseFloat(cweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total").html(cweight);
      if(consume_price=="" || consume_weight==""){
         return;
      }
      let amount = parseFloat(consume_price)*parseFloat(consume_weight);
      $("#consume_amount_"+id).val(amount.toFixed(2));
      let camount = 0;
      $(".consume_amount").each(function(){
         if($(this).val()!=''){
            camount = parseFloat(camount) + parseFloat($(this).val());
         }
      });
      $("#consume_amount_total").html(camount);
   } 
   function calculateAmountNew(id){
      let generated_price = $("#generated_price_"+id).val();
      let generated_weight = $("#generated_weight_"+id).val();
      let nweight = 0;
      $(".generated_weight").each(function(){
         if($(this).val()!=''){
            nweight = parseFloat(nweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total1").html(nweight);
      if(generated_price=="" || generated_weight==""){
         return;
      }
      let amount = parseFloat(generated_price)*parseFloat(generated_weight);
      $("#generated_amount_"+id).val(amount.toFixed(2));

      let namount = 0;
      $(".generated_amount").each(function(){
         if($(this).val()!=''){
            namount = parseFloat(namount) + parseFloat($(this).val());
         }
      });
      $("#consume_amount_total1").html(namount);
   } 
</script>
@endsection