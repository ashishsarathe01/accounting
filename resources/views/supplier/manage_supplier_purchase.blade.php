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
            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Pending Purchase Voucher</h5>
               <a href="{{route('complete-supplier-purchase')}}"><button class="btn btn-primary btn-sm d-flex align-items-center" >Complete Purchase Voucher ({{$compete_purchase}})</button></a>
               <a href="{{route('pending-for-approval')}}"><button class="btn btn-primary btn-sm d-flex align-items-center" >Pending For Approval ({{$approval_purchase}})</button></a>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Voucher No. </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Weight (Qty)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Item Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                    @php $group_id = 0; $price = 0;@endphp
                     @foreach($purchases as $key => $value)
                        <tr>
                            <td>{{date('d-m-Y',strtotime($value->date))}}</td>
                            <td>{{$value->voucher_no}}</td>
                            <td>{{$value->account['account_name']}}</td>
                            <td style="text-align:right;">{{$value->total}}</td>
                            <td style="text-align:right;">
                                @php $qty_total = 0; @endphp
                                @foreach($value->purchaseDescription as $v)
                                    @php 
                                        $qty_total = $qty_total + $v->qty; 
                                        $price = $v->price;
                                    @endphp
                                @endforeach
                                @php echo $qty_total; @endphp
                            </td>
                            <td>
                                @foreach($value->purchaseDescription as $v)
                                    @php $group_id = $v->item->group_id; @endphp
                                    {{$v->item->name}} ({{$v->qty}} {{$v->units->name}})<br>
                                @endforeach
                            </td>
                            <td><button class="btn btn-info report" data-id="{{$value->id}}" data-qty="{{$qty_total}}" data-account_id="{{$value->party}}" data-group_id="{{$group_id}}" data-price="{{$price}}" data-account_name="{{$value->account['account_name']}}" data-invoice_date="{{date('d-m-Y',strtotime($value->date))}}" data-invoice_amount="{{$value->total}}" data-invoice_no="{{$value->voucher_no}}">Report</button></td>
                        </tr>
                     @endforeach
                     
                  </tbody>
               </table>
            </div>
         </div>
         <!-- <div class="col-lg-1 d-flex justify-content-center">
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
         </div> -->
      </div>
   </section>
</div>
<div class="modal fade" id="report_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Report</h5>
            <br>
            <div class="row">
                <div class="mb-6 col-md-6">
                    <label for="name" class="form-label font-14 font-heading">Account Name</label>
                    <input type="text" id="account_name" class="form-control" readonly>
                </div> 
                <div class="mb-2 col-md-2">
                    <label for="name" class="form-label font-14 font-heading">Invoice No.</label>
                    <input type="text" id="invoice_no" class="form-control" readonly>
                </div>
                <div class="mb-2 col-md-2">
                    <label for="name" class="form-label font-14 font-heading">Invoice Date</label>
                    <input type="text" id="invoice_date" class="form-control" readonly>
                </div>
                <div class="mb-2 col-md-2">
                    <label for="name" class="form-label font-14 font-heading">Invoice Amount</label>
                    <input type="text" id="invoice_amount" class="form-control" readonly>
                </div>
                <div class="mb-6 col-md-6">
                    <label for="name" class="form-label font-14 font-heading">Voucher Number</label>
                    <input type="text" id="voucher_no" class="form-control" placeholder="Enter Voucher Number"/>
                    <input type="hidden" id="row_id">
                    <input type="hidden" id="account_id">
                </div> 
                <div class="mb-6 col-md-6">
                    <label for="name" class="form-label font-14 font-heading">Area</label>
                    <select id="location" class="form-select">
                        <option value="">Select Area</option>
                        @foreach($locations as $loc)
                            <option value="{{$loc->id}}">{{$loc->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-12 col-md-12"></div>
                <div class="mb-12 col-md-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Head</th>
                                <th id="purchase_weight" style="text-align: right"></th><input type="hidden" id="pur_weight">
                                <th style="text-align: right">Bill Rate</th>
                                <th style="text-align: right">Contract Rate</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="report_body">
                            @foreach($heads as $key => $value)
                                <tr class="head">
                                    <td><input type="text" class="form-control" value="{{$value->name}}" readonly></td>
                                    <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control contract_rate" id="contract_rate_{{$value->id}}" style="text-align: right" readonly data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                </tr>
                            @endforeach
                            <tr id="fuel_row" style="display: none">
                                <td><input type="text" class="form-control" value="Fuel" readonly></td>
                                <td><input type="text" class="form-control calculate" placeholder="Enter Qty" id="qty_fuel" style="text-align: right" data-id="fuel"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_fuel" style="text-align: right" data-id="fuel"></td>
                                <td><input type="text" class="form-control" id="contract_rate_fuel" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control" id="difference_amount_fuel" data-id="fuel" style="text-align: right" readonly></td>
                            </tr>
                            <tr id="cut_row">
                                <td><input type="text" class="form-control" value="Cut" readonly></td>
                                <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_cut" style="text-align: right" data-id="cut"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_cut" style="text-align: right" data-id="cut"></td>
                                <td><input type="text" class="form-control" id="contract_rate_cut" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control difference_amount" id="difference_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                            </tr>
                            <tr id="short_weight_row">
                                <td><input type="text" class="form-control" value="Short Weight" readonly></td>
                                <td><input type="text" class="form-control calculate" readonly id="qty_short_weight" style="text-align: right" data-id="short_weight"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_short_weight" style="text-align: right" data-id="short_weight"></td>
                                <td><input type="text" class="form-control" id="contract_rate_short_weight" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control difference_amount" id="difference_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                            </tr>
                            <tr >
                                <td></td>
                                <td></td>
                                <td></td>
                                <th style="text-align: right">Difference</th>
                                <th><input type="text" class="form-control" id="difference_total_amount" style="text-align: right" readonly></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            <div class="text-start">
                <button type="button" class="btn  btn-xs-primary save_location">
                    SAVE
                </button>
            </div>
        </div>
    </div>
</div>
</body>
@include('layouts.footer')
<script>
    $(document).ready(function() {
        $('.report').on('click', function() {
            var id = $(this).data('id');
            var account_id = $(this).data('account_id');
            let qty = $(this).data('qty');
            let group_id = $(this).data('group_id');
            let price = $(this).data('price');

            let account_name = $(this).data('account_name');
            let invoice_no = $(this).data('invoice_no');
            let invoice_date = $(this).data('invoice_date');
            let invoice_amount = $(this).data('invoice_amount');

            $("#account_name").val(account_name);
            $("#invoice_no").val(invoice_no);
            $("#invoice_date").val(invoice_date);
            $("#invoice_amount").val(invoice_amount);
            $(".bill_rate").val(price);
            if(group_id == 18){
                $("#fuel_row").show();
                $(".head").hide();
                $(".contract_rate").each(function(){
                    $("#qty_"+$(this).attr('data-id')).val('');
                    $("#difference_amount_"+$(this).attr('data-id')).val('');
                });
                
                $("#fuel_contract_rate").val(price);
            }else{
                $("#fuel_row").hide();
                $(".head").show();
                $("#fuel_qty").val('');
                $("#fuel_bill_rate").val('');
            }
            // Reset all fields
            
            $("#difference_total_amount").val('');
           
            $.ajax({
                url : "{{url('get-location-by-supplier')}}",
                method : "POST",
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    account_id : account_id
                },
                success:function(res){
                    location_list = "<option value=''>Select Area</option>";
                    if(res.location.length>0){
                        location_arr = res.location;
                        res.location.forEach(function(e){
                        location_list+="<option value="+e.id+">"+e.name+"</option>";
                        });
                    }
                    $("#location").html(location_list);
                    $("#purchase_weight").html("Purchase Weight : "+qty);
                    $("#pur_weight").val(qty);
                    $("#row_id").val(id);
                    $("#account_id").val(account_id);
                    $("#report_modal").modal('show');
                }
            });
            
        });
        $("#location").change(function(){
            var loc_id = $(this).val();
            var account_id = $("#account_id").val();
            if(loc_id != ''){
                $.ajax({
                    url:"{{url('get-supplier-rate-by-location')}}",
                    type:"POST",
                    data:{
                        "_token": "{{ csrf_token() }}",
                        "location": loc_id,
                        "account_id": account_id
                    },
                    success:function(res){
                        if(res == null){
                            $(".contract_rate").each(function(){
                                if(rate_arr[$(this).attr('data-id')]){
                                    $(this).val('');
                                }
                            });
                            return;
                        }
                        if(res!=""){
                            if(res.length>0){
                                let rate_arr = [];
                                res.forEach(function(e){
                                    rate_arr[e.head_id] = e.head_rate;
                                });
                                $(".contract_rate").each(function(){
                                    if(rate_arr[$(this).attr('data-id')]){
                                        $(this).val(rate_arr[$(this).attr('data-id')]);
                                    }
                                });
                            }
                        }                        
                        $("#contract_rate_cut").val(0);
                        $("#contract_rate_short_weight").val(0);

                        $(".calculate").each(function(){
                            $(this).keyup();
                        });
                    }
                });
            }
        });
        $(".calculate").keyup(function(){
            let short_weight = 0;
            let qty_weight = 0;
            let purchase_weight = $("#pur_weight").val();
            if(purchase_weight==""){
                purchase_weight = 0;
            }
            $(".qty").each(function(){
                if($(this).val()!=""){
                    qty_weight = parseFloat(qty_weight) + parseFloat($(this).val());
                }
            })
            short_weight = parseFloat(purchase_weight) - parseFloat(qty_weight);
            
            $("#qty_short_weight").css({'color':''})
            if(parseFloat(short_weight)<0){
                $("#qty_short_weight").css({'color': 'red'});
            }
            $("#qty_short_weight").val(short_weight);
            $("#difference_amount_short_weight").val(parseFloat(short_weight)*parseFloat($("#bill_rate_short_weight").val()));
            
            var id = $(this).data('id');
            var qty = $("#qty_"+id).val();
            var bill_rate = $("#bill_rate_"+id).val();
            var contract_rate = $("#contract_rate_"+id).val();
            if(qty == ''){
                qty = 0;
            }
            if(bill_rate == ''){
                bill_rate = 0;
            }
            if(contract_rate == ''){
                contract_rate = 0;
            }
            let diff_rate = bill_rate - contract_rate;
            var difference_amount = parseFloat(qty) * parseFloat(diff_rate);
            $("#difference_amount_"+id).val(difference_amount.toFixed(2));
            calculateTotalDifference();
        });
        function calculateTotalDifference(){
            var total = 0;
            $(".difference_amount").each(function(){
                var val = $(this).val();
                var id = $(this).attr('data-id');
                if(val == ''){
                    val = 0;
                }
                total = parseFloat(total) + parseFloat(val);
            });
            $("#difference_total_amount").val(total.toFixed(2));
        }
        $("#other_check").click(function(){
            calculateTotalDifference();
        });
        $(".save_location").click(function(){
            var id = $("#row_id").val();
            var location_id = $("#location").val();
            if(location_id == ''){
                alert("Please select area");
                return;
            }
            var voucher_no = $("#voucher_no").val();
            if(voucher_no == ''){
                alert("Please enter voucher no.");
                return;
            }
            var purchase_id = $("#row_id").val();
            if(purchase_id == ''){
                alert("Purchase id not found");
                return;
            }
            let arr = [];
            $(".bill_rate").each(function(){
                arr.push({'id':$(this).attr('data-id'),'contract_rate':$("#contract_rate_"+$(this).attr('data-id')).val(),'bill_rate':$(this).val(),'qty':$("#qty_"+$(this).attr('data-id')).val(),'difference_amount':$("#difference_amount_"+$(this).attr('data-id')).val()});
            });
            
            var data = {
                "voucher_no": voucher_no,
                "location": location_id,
                "purchase_id": purchase_id,
                "data":JSON.stringify(arr),
                "difference_total_amount": $("#difference_total_amount").val(),
                "_token": "{{ csrf_token() }}"
            };
            $.ajax({
                url:"{{url('store-supplier-purchase-report')}}",
                type:"POST",
                data:data,
                success:function(res){                   
                    response = JSON.parse(res);
                    if(response.status == true){
                        alert(response.message);
                        location.reload();
                    }else{
                        alert(response.message);
                       
                    }
                }
            });
        });
   });
</script>
@endsection