@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger">{{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{session('success')}}</div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Transaction Report</h5>
                    <form action="{{ route('transaction.report') }}" method="GET">
                        <div class="d-md-flex d-block">
                            <div class="calender-administrator my-2 my-md-0">
                                <input type="date" class="form-control calender-bg-icon calender-placeholder" name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}" min="2026-04-01">
                            </div>
                            <div class="calender-administrator ms-md-4">
                                <input type="date" class="form-control calender-bg-icon calender-placeholder" name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}" min="2026-04-01">
                            </div>
                            <!-- NEW MODULE FILTER -->
                            <div class="ms-md-4">
                                <select name="module_filter" class="form-select">
                                    <option value="">All Modules</option>
                                    <option value="sale" {{ request('module_filter')=='sale'?'selected':'' }}>Sale</option>
                                    <option value="purchase" {{ request('module_filter')=='purchase'?'selected':'' }}>Purchase</option>
                                    <option value="WASTE KRAFT" {{ request('module_filter')=='WASTE KRAFT'?'selected':'' }}>Purchase (Waste Kraft)</option>
                                    <option value="BOILER FUEL" {{ request('module_filter')=='BOILER FUEL'?'selected':'' }}>Purchase (Boiler Fuel)</option>
                                    <option value="SPARE PART" {{ request('module_filter')=='SPARE PART'?'selected':'' }}>Purchase (Spare Part)</option>
                                    <option value="credit_note" {{ request('module_filter')=='credit_note'?'selected':'' }}>Credit Note</option>
                                    <option value="debit_note" {{ request('module_filter')=='debit_note'?'selected':'' }}>Debit Note</option>
                                    <option value="payment" {{ request('module_filter')=='payment'?'selected':'' }}>Payment</option>
                                    <option value="receipt" {{ request('module_filter')=='receipt'?'selected':'' }}>Receipt</option>
                                    <option value="journal" {{ request('module_filter')=='journal'?'selected':'' }}>Journal</option>
                                    <option value="contra" {{ request('module_filter')=='contra'?'selected':'' }}>Contra</option>
                                    <option value="stock_journal" {{ request('module_filter')=='stock_journal'?'selected':'' }}>Stock Journal</option>
                                    <option value="stock_transfer" {{ request('module_filter')=='stock_transfer'?'selected':'' }}>Stock Transfer</option>
                                </select>
                            </div>
                            <button class="btn btn-info ms-2">Search</button>
                        </div>
                    </form>
                    <div class="d-md-flex d-block">
                        <input type="text" id="search" class="form-control" placeholder="Search">
                    </div>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm">
                    <table class="table-striped table m-0 shadow-sm activity_table">
                        <thead>
                            <tr>
                                <th width="120">Date</th>
                                <th width="150">Module</th>
                                <th width="120">Series</th>
                                <th width="200">Reference</th>
                                <th>Particulars</th>
                                <th width="120" class="text-end">Debit</th>
                                <th width="120" class="text-end">Credit</th>
                                <th width="100" class="text-center">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $lastVoucher = null;
                                $lastGroupType = null;
                            @endphp
                            @forelse($transactions as $row)
                                <tr>
                                    @if($lastVoucher != $row->transaction_id)
                                        <td>{{ date('d-m-Y',strtotime($row->date)) }}</td>
                                        <td>{{ $row->module }} @if(isset($row->group_type) && !empty($row->group_type)) ({{$row->group_type}}) @endif</td>
                                        <td>{{ $row->series ?? '' }}</td>
                                        <td>{{ $row->reference }}</td>
                                    @else
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endif
                                    <td>{{ $row->party }}</td>
                                    <td class="text-end">{{ !empty($row->debit) ? $row->debit : '' }}</td>
                                    <td class="text-end">{{ !empty($row->credit) ? $row->credit : '' }}</td>
                                    @if($lastVoucher != $row->transaction_id)
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info viewTransaction"
                                                    data-id="{{ $row->transaction_id }}"
                                                    data-module="{{ $row->module_type }}"
                                                    data-sr-nature="{{ $row->sr_nature ?? '' }}"
                                                    data-sr-type="{{ $row->sr_type ?? '' }}">
                                                View
                                            </button>
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                </tr>
                                @php
                                    $lastVoucher = $row->transaction_id;
                                @endphp
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            No Transactions Found
                                        </td>
                                    </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="transactionModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Transaction Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="row mb-3">

<div class="col-md-6">

<b>Type :</b> <span id="modal_type"></span><br>
<b>Voucher No :</b> <span id="modal_voucher"></span>
<div id="journal_fields">
<b>Claim GST :</b> <span id="modal_gst"></span><br>
<b>Invoice No :</b> <span id="modal_invoice"></span><br>
<b>Remark :</b> <span id="long_narration"></span>
</div>

</div>

<div class="col-md-6 text-end">

<b>Date :</b> <span id="modal_date"></span><br>

<b>Series :</b> <span id="modal_series"></span>
<div id="stock_transfer_fields" style="display:none;">
<b>From :</b> <span id="modal_from"></span><br>
<b>To :</b> <span id="modal_to"></span>
</div>
</div>

</div>


<table class="table table-bordered">

<thead id="modal_head"></thead>

<tbody id="modal_table">

</tbody>

</table>
<div id="stock_journal_narration" style="display:none;" class="mt-2">
<b>Narration :</b>
<p id="modal_narration" style="word-break: break-word; white-space: pre-wrap;"></p>
</div>
<div class="text-center">

<button class="btn btn-secondary" data-bs-dismiss="modal">Quit</button>
@can('view-module', 254)
    <button class="btn btn-warning editTransaction">Edit</button>
@endcan
@can('view-module', 255)
    <button class="btn btn-success approveTransaction">Approve</button>
@endcan
</div>

</div>

</div>
</div>
</div>
@include('layouts.footer')

<script>

let saleInvoiceUrl = "{{ url('sale-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let purchaseInvoiceUrl = "{{ url('purchase-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let saleReturnWithItemUrl = "{{ url('sale-return-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let saleReturnWithoutItemUrl = "{{ url('sale-return-without-item-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let saleReturnWithoutGstUrl = "{{ url('sale-return-without-gst-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let purchaseReturnWithItemUrl = "{{ url('purchase-return-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let purchaseReturnWithoutItemUrl = "{{ url('purchase-return-without-item-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
let purchaseReturnWithoutGstUrl = "{{ url('purchase-return-without-gst-invoice') }}/:id?source=approve&return_url={{ urlencode(request()->fullUrl()) }}";
$(document).on("click",".viewTransaction",function(){

    let id = $(this).data("id");
    let module = $(this).data("module");
    $("#transactionModal").data("id",id);
$("#transactionModal").data("module",module);

    // Redirect modules
    if(module === "sale"){
        window.location.href = saleInvoiceUrl.replace(':id', id);
        return;
    }

    if(module === "purchase"){
        window.location.href = purchaseInvoiceUrl.replace(':id', id);
        return;
    }

    if(module === "credit_note"){

    let srNature = $(this).data("sr-nature");
    let srType = $(this).data("sr-type");

    if(srNature === "WITH GST" && (srType === "WITH ITEM" || srType === "RATE DIFFERENCE")){
        window.location.href = saleReturnWithItemUrl.replace(':id', id);
        return;
    }

    if(srNature === "WITH GST" && srType === "WITHOUT ITEM"){
        window.location.href = saleReturnWithoutItemUrl.replace(':id', id);
        return;
    }

    if(srNature === "WITHOUT GST"){
        window.location.href = saleReturnWithoutGstUrl.replace(':id', id);
        return;
    }

}

    if(module === "debit_note"){

    let srNature = $(this).data("sr-nature");
    let srType = $(this).data("sr-type");

    if(srNature === "WITH GST" && (srType === "WITH ITEM" || srType === "RATE DIFFERENCE")){
        window.location.href = purchaseReturnWithItemUrl.replace(':id', id);
        return;
    }

    if(srNature === "WITH GST" && srType === "WITHOUT ITEM"){
        window.location.href = purchaseReturnWithoutItemUrl.replace(':id', id);
        return;
    }

    if(srNature === "WITHOUT GST"){
        window.location.href = purchaseReturnWithoutGstUrl.replace(':id', id);
        return;
    }

}
    // Existing AJAX logic continues here

    $.ajax({

        url: "{{ route('transaction.view') }}",
        type: "GET",

        data:{
            id:id,
            module:module
        },

        success:function(res){

    $("#modal_type").text(res.module);
    $("#modal_voucher").text(res.voucher);
    $("#modal_date").text(res.date);
    $("#modal_series").text(res.series);
    $("#modal_from").text(res.from ?? '');
    $("#modal_to").text(res.to ?? '');
    $("#long_narration").text(res.long_narration ?? '');
    let rows = "";
    let head = "";

    if(res.module === "stock_journal")
{
$("#stock_transfer_fields").hide();
$("#journal_fields").hide();
$("#stock_journal_narration").show();

$("#modal_narration").text(res.narration ?? '');


head = `
<tr>
<th>Particulars</th>
<th>Consume Qty</th>
<th>Consume Amount</th>
<th>Production Qty</th>
<th>Production Amount</th>
</tr>
`;

res.details.forEach(function(row){

rows += `
<tr>
<td>${row.item ?? ''}</td>
<td>${row.consume_qty ?? ''}</td>
<td>${row.consume_amount ?? ''}</td>
<td>${row.production_qty ?? ''}</td>
<td>${row.production_amount ?? ''}</td>
</tr>
`;

});

rows += `
<tr style="font-weight:bold;background:#f5f5f5">
<td>Total</td>
<td>${res.total_consume_qty ?? ''}</td>
<td>${res.total_consume_amount ?? ''}</td>
<td>${res.total_production_qty ?? ''}</td>
<td>${res.total_production_amount ?? ''}</td>
</tr>
`;

}
else if(res.module === "stock_transfer")
{
$("#stock_transfer_fields").show();
$("#journal_fields").hide();
$("#stock_journal_narration").hide();

head = `
<tr>
<th>Particular</th>
<th>Qty</th>
<th>Price</th>
<th>Amount</th>
</tr>
`;

res.details.forEach(function(row){

rows += `
<tr>
<td>${row.item ?? ''}</td>
<td>${row.qty ?? ''}</td>
<td>${row.price ?? ''}</td>
<td>${row.amount ?? ''}</td>
</tr>
`;

});

if(res.sundries){

res.sundries.forEach(function(s){

rows += `
<tr>
<td colspan="3">${s.name}</td>
<td>${s.amount}</td>
</tr>
`;

});

}

rows += `
<tr style="font-weight:bold;background:#f5f5f5">
<td>Total</td>
<td>${res.total_qty ?? ''}</td>
<td></td>
<td>${res.grand_total ?? ''}</td>
</tr>
`;

}
else if(res.module === "journal")
{
$("#stock_transfer_fields").hide();
$("#stock_journal_narration").hide();
$("#journal_fields").show();

$("#modal_gst").text(res.claim_gst ?? '');
$("#modal_invoice").text(res.invoice_no ?? '');

head = `
<tr>
<th>Dr / Cr</th>
<th>Particulars</th>
<th>Amount Dr</th>
<th>Amount Cr</th>
<th>Remark</th>
</tr>
`;

res.details.forEach(function(row){

rows += `
<tr>
<td>${row.type ?? ''}</td>
<td>${row.account ?? ''}</td>
<td>${row.debit ?? ''}</td>
<td>${row.credit ?? ''}</td>
<td>${row.remark ?? ''}</td>
</tr>
`;

});

}
else
{
$("#stock_transfer_fields").hide();
$("#stock_journal_narration").hide();
$("#journal_fields").hide();

head = `
<tr>
<th>Dr / Cr</th>
<th>Particulars</th>
<th>Amount Dr</th>
<th>Amount Cr</th>
</tr>
`;

res.details.forEach(function(row){

rows += `
<tr>
<td>${row.type ?? ''}</td>
<td>${row.account ?? ''}</td>
<td>${row.debit ?? ''}</td>
<td>${row.credit ?? ''}</td>
</tr>
`;

});

}

    $("#modal_head").html(head);
    $("#modal_table").html(rows);

    $("#transactionModal").modal("show");

}

    });

});


$(document).on("click",".approveTransaction",function(){

let id = $("#transactionModal").data("id");
let module = $("#transactionModal").data("module");

$.ajax({

url: "{{ route('transaction.approve') }}",
type: "POST",

data:{
    _token:"{{ csrf_token() }}",
    id:id,
    module:module
},

success:function(res){

if(res.status){

alert("Transaction Approved ✅");

$("#transactionModal").modal("hide");

location.reload();

}

}

});

});
$("#search").on("keyup", function(){
        var value = $(this).val().toLowerCase();
        $(".activity_table tbody tr").filter(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    $(document).on("click",".editTransaction",function(){

    let id = $("#transactionModal").data("id");
    let module = $("#transactionModal").data("module");


    // JOURNAL
    if(module === "journal"){
        window.open("{{ url('journal') }}/" + id + "/edit","_blank");
        return;
    }

    // STOCK JOURNAL
    if(module === "stock_journal"){
         window.open("{{ url('edit-stock-journal') }}/" + id);
        return;
    }

    // STOCK TRANSFER
    if(module === "stock_transfer"){
         window.open("{{ url('stock-transfer') }}/" + id + "/edit","_blank");
        return;
    }

    // PAYMENT
    if(module === "payment"){
         window.open("{{ url('payment') }}/" + id + "/edit","_blank");
        return;
    }

    // RECEIPT
    if(module === "receipt"){
         window.open("{{ url('receipt') }}/" + id + "/edit","_blank");
        return;
    }

    // CONTRA
    if(module === "contra"){
         window.open("{{ url('contra') }}/" + id + "/edit","_blank");
        return;
    }

});
</script>
@endsection