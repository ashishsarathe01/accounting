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
               <h5 class="transaction-table-title m-0 py-2">Complete Purchase Voucher</h5>
               <select class="form-select form-select-sm w-auto select2-single" aria-label=".form-select-sm example" id="supplier">
                  <option selected value="">Select Supplier</option>
                  @foreach($accounts as $loc)
                     <option value="{{$loc->id}}" @if($loc->id==$id) selected @endif>{{$loc->account_name}}</option>
                  @endforeach

               </select>
               <a href="{{route('manage-supplier-purchase')}}"><button class="btn btn-primary btn-sm d-flex align-items-center" >Pending Purchase Voucher</button></a>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Voucher No. </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Difference Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Weight (Qty)</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Item Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($purchases as $key => $value)
                        <tr>
                            <td>{{date('d-m-Y',strtotime($value->date))}}</td>
                            <td>{{$value->voucher_no}}</td>
                            <td>{{$value->account['account_name']}}</td>
                            <td style="text-align:right;">{{$value->total}}</td>
                            <td style="text-align:right;">{{$value->difference_total_amount}}</td>
                            <td style="text-align:right;">
                                @php $qty_total = 0; @endphp
                                @foreach($value->purchaseDescription as $v)
                                    @php $qty_total = $qty_total + $v->qty; @endphp
                                @endforeach
                                @php echo $qty_total; @endphp
                            </td>
                            <td>
                                @foreach($value->purchaseDescription as $v)
                                    {{$v->item->name}} ({{$v->qty}} {{$v->units->name}})<br>
                                @endforeach
                            </td>
                            <td><button class="btn btn-info view" data-id="{{$value->id}}">View</button></td>
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
         <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Purchase Detail</h5>
         <br>
         <div class="row">
               <div class="mb-6 col-md-6">
                  <label for="name" class="form-label font-14 font-heading">Voucher Number</label>
                  <input type="text" id="voucher_no" class="form-control"  readonly/>
               </div> 
               <div class="mb-6 col-md-6">
                  <label for="name" class="form-label font-14 font-heading">Area</label>
                  <input type="text" id="location" class="form-control"  readonly/>
               </div>
               <div class="mb-12 col-md-12"></div>
                     <div class="mb-12 col-md-12">
                        <table class="table table-bordered">
                           <thead>
                              <tr>
                                 <th></th>
                                 <th id="purchase_weight" style="text-align: right"></th>
                                 <th style="text-align: right">Bill Rate</th>
                                 <th style="text-align: right">Contract Rate</th>
                                 <th></th>
                              </tr>
                           </thead>
                           <tbody id="report_body">
                              <tr>
                                 <td><input type="text" class="form-control" value="Kraft I" readonly></td>
                                 <td><input type="text" class="form-control calculate" readonly id="kraft_i_qty" style="text-align: right" data-id="kraft_i"></td>
                                 <td><input type="text" class="form-control calculate" readonly id="kraft_i_bill_rate" style="text-align: right" data-id="kraft_i"></td>
                                 <td><input type="text" class="form-control" id="kraft_i_contract_rate" style="text-align: right" readonly></td>
                                 <td><input type="text" class="form-control" id="kraft_i_difference_amount" data-id="kraft_i" style="text-align: right" readonly></td>
                              </tr>
                              <tr>
                                    <td><input type="text" class="form-control" value="Kraft II" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="kraft_ii_qty" style="text-align: right" data-id="kraft_ii"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="kraft_ii_bill_rate" style="text-align: right" data-id="kraft_ii"></td>
                                    <td><input type="text" class="form-control" id="kraft_ii_contract_rate" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control" id="kraft_ii_difference_amount" data-id="kraft_ii" style="text-align: right" readonly></td>
                              </tr>
                                <tr>
                                    <td><input type="text" class="form-control" value="Duplex" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="duplex_qty" style="text-align: right" data-id="duplex"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="duplex_bill_rate" style="text-align: right" data-id="duplex"></td>
                                    <td><input type="text" class="form-control" id="duplex_contract_rate" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control" id="duplex_difference_amount" data-id="duplex" style="text-align: right" readonly></td>
                                </tr>
                                <tr>
                                    <td><input type="text" class="form-control" value="Poor" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="poor_qty" style="text-align: right" data-id="poor"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="poor_bill_rate" style="text-align: right" data-id="poor"></td>
                                    <td><input type="text" class="form-control" id="poor_contract_rate" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control" id="poor_difference_amount" data-id="poor" style="text-align: right" readonly></td>
                                </tr>
                                <tr>
                                    <td><input type="text" class="form-control" value="Cut" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="cut_qty" style="text-align: right" data-id="cut"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="cut_bill_rate" style="text-align: right" data-id="cut"></td>
                                    <td><input type="text" class="form-control" id="cut_contract_rate" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control" id="cut_difference_amount" data-id="cut" style="text-align: right" readonly></td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" id="other_check"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="other_qty" style="text-align: right" data-id="other"></td>
                                    <td><input type="text" class="form-control calculate" readonly id="other_bill_rate" style="text-align: right" data-id="other"></td>
                                    <td><input type="text" class="form-control" id="other_contract_rate" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control" id="other_difference_amount" data-id="other" style="text-align: right" readonly></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>Difference</td>
                                    <td><input type="text" class="form-control" id="difference_total_amount" style="text-align: right" readonly></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layouts.footer')
<script>
   $( ".select2-single" ).select2({
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            // Normalize: remove dots + spaces, lowercase everything
            function normalize(str) {
                return (str || '')
                    .toLowerCase()
                    .replace(/[.\s]/g, ''); // remove '.' and spaces
            }
            var term = normalize(params.term);
            var text = normalize(data.text);
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
    $("#supplier").change(function(){
        window.location = "{{url('complete-supplier-purchase/')}}/"+$(this).val();
    });
    
    $(".view").click(function(){
         let id = $(this).data('id');
         $.ajax({
            url:"{{url('view-complete-purchase-info/')}}/"+id,
            type:"POST",
            data:{_token:'{{csrf_token()}}'},
            success:function(res){
               if(res!=""){
                  let obj = JSON.parse(res);
                  $("#voucher_no").val(obj.reports.voucher_no);
                  $("#location").val(obj.reports.location_name);

                  $("#kraft_i_qty").val(obj.reports.kraft_i_qty);
                  $("#kraft_i_bill_rate").val(obj.reports.kraft_i_bill_rate);
                  $("#kraft_i_contract_rate").val(obj.reports.kraft_i_contract_rate);
                  $("#kraft_i_difference_amount").val(obj.reports.kraft_i_difference_amount);

                  $("#kraft_ii_qty").val(obj.reports.kraft_ii_qty);
                  $("#kraft_ii_bill_rate").val(obj.reports.kraft_ii_bill_rate);
                  $("#kraft_ii_contract_rate").val(obj.reports.kraft_ii_contract_rate);
                  $("#kraft_ii_difference_amount").val(obj.reports.kraft_ii_difference_amount);

                  $("#duplex_qty").val(obj.reports.duplex_qty);
                  $("#duplex_bill_rate").val(obj.reports.duplex_bill_rate);
                  $("#duplex_contract_rate").val(obj.reports.duplex_contract_rate);
                  $("#duplex_difference_amount").val(obj.reports.duplex_difference_amount);

                  $("#poor_qty").val(obj.reports.poor_qty);
                  $("#poor_bill_rate").val(obj.reports.poor_bill_rate);
                  $("#poor_contract_rate").val(obj.reports.poor_contract_rate);
                  $("#poor_difference_amount").val(obj.reports.poor_difference_amount);

                  $("#cut_qty").val(obj.reports.cut_qty);
                  $("#cut_bill_rate").val(obj.reports.cut_bill_rate);
                  $("#cut_contract_rate").val(obj.reports.cut_contract_rate);
                  $("#cut_difference_amount").val(obj.reports.cut_difference_amount);

                  $("#other_qty").val(obj.reports.other_qty);
                  $("#other_bill_rate").val(obj.reports.other_bill_rate);
                  $("#other_contract_rate").val(obj.reports.other_contract_rate);
                  $("#other_difference_amount").val(obj.reports.other_difference_amount);

                  $("#difference_total_amount").val(obj.reports.difference_total_amount);
                  if(obj.reports.other_check==1){
                     $("#other_check").prop('checked', true);
                  }
               }
               
               $("#report_modal").modal('show');
            }
         });
    });
</script>
@endsection