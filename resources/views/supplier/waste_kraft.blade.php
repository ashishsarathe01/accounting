@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
@php
    $status = request('status'); // 0 = Pending default
@endphp
@if($status === null)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch("{{ url('user-waste-kraft-status') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.status !== undefined) {
                        window.location.href = "{{ url('waste-kraft') }}?status=" + data.status;
                    }
                });
        });
    </script>
@endif


<!-- list-view-company-section -->
<style>
    input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
 .unchange_dropdown{
      pointer-events: none !important;
      touch-action: none !important;
   }
   .carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0,0,0,0.5); /* semi-transparent black */
    border-radius: 50%;
    background-size: 100%, 100%;
}
#imageCarousel img {
    width: 100%;           /* fit the column width */
    height: 200px;         /* fixed height */
    object-fit: contain;   /* keep aspect ratio, no cropping */
    background: #f8f9fa;   /* light gray background so white images are visible */
    border-radius: 8px;
}
/* Fullscreen modal */
.modal-fullscreen .modal-content {
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Scroll only the body */
.modal-fullscreen .modal-body {
    flex: 1;
    overflow-y: auto;
    padding-bottom: 20px;
}

/* Left and right sections scroll if needed */
.left-section,
.right-section {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Optional spacing */
.modal-fullscreen .col-md-6 {
    padding-right: 15px;
}
/* Modal look */
#report_modal .modal-content {
    border-radius: 12px;
    background: #ffffff;
}

#report_modal .modal-header h5 {
    font-size: 20px;
    font-weight: 600;
}

/* Card styling */
#report_modal .card {
    border-radius: 12px;
}

/* Section headers */
#report_modal h6 {
    font-size: 15px;
    font-weight: 600;
    color: #444;
}

/* Form labels */
#report_modal .form-label {
    font-size: 13px;
    font-weight: 500;
    color: #555;
    margin-bottom: 3px;
}

/* Form fields */
#report_modal .form-control,
#report_modal .form-select {
    border-radius: 6px;
    border-color: #cfd4dc;
    font-size: 14px;
}

#report_modal .form-control:focus,
#report_modal .form-select:focus {
    border-color: #7c57ff;
    box-shadow: 0 0 0 0.15rem rgba(124, 87, 255, 0.2);
}

/* Buttons */
#report_modal .btn {
    border-radius: 6px;
    font-size: 14px;
}

#report_modal .btn-info {
    background: #5b9df9;
    border-color: #5b9df9;
}

#report_modal .btn-info:hover {
    background: #448ef5;
    border-color: #448ef5;
}

#report_modal .btn-primary {
    background: #7c57ff;
    border-color: #7c57ff;
}

#report_modal .btn-primary:hover {
    background: #6a46f5;
    border-color: #6a46f5;
}

/* Table styling */
#report_modal table th,
#report_modal table td {
    vertical-align: middle;
    padding: 6px 10px;
    font-size: 14px;
}

#report_modal .table-bordered th {
    background: #f7f7f9;
    font-weight: 600;
}

#report_modal .table input,
#report_modal .table select {
    font-size: 14px;
    border-radius: 6px;
}

/* Carousel images */
#report_modal .carousel-inner img {
    border-radius: 8px;
    max-height: 420px;
    object-fit: contain;
}

/* Scrollable sections */
#report_modal .right-section,
#report_modal .left-section {
    max-height: 83vh;
    overflow-y: auto;
    padding-right: 5px;
}

/* Hide scrollbars but allow scroll */
#report_modal .right-section::-webkit-scrollbar,
#report_modal .left-section::-webkit-scrollbar {
    width: 4px;
}

#report_modal .right-section::-webkit-scrollbar-thumb,
#report_modal .left-section::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 10px;
}

/* Improve spacing */
#report_modal .row.g-4 > div {
    padding-bottom: 5px;
}

/* Headings alignment */
#report_modal h6 {
    margin-bottom: 12px;
}

/* Quality of spacing between cards */
#report_modal .card + .card {
    margin-top: 18px;
}

.left-section, 
.right-section {
    overflow: visible !important;
}
.form-select{
    height: 25px;
}
.form-control{
    height: 25px;
}
.modal-scroll-body {
    max-height: calc(100vh - 80px);
    overflow-y: auto;
    outline: none; /* removes focus outline */
}
</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint pb-5">
                @if (session('error'))
                <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                <div class="tab-content pt-0" id="tab-content">
                    @if($status == 0)
                    @can('view-module', 184)
                        <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                          <div class="d-flex align-items-center gap-3">
                                    <h5 class="transaction-table-title m-0">Pending</h5>
                                    <select class="form-select form-select-sm"
                                            style="width:220px"
                                            onchange="window.location='{{ url('waste-kraft') }}?status=' + this.value">
                                         @can('view-module', 184)
        <option value="0" {{ $status == 0 ? 'selected' : '' }}>
            Pending, In Process
        </option>
    @endcan

    @can('view-module', 185)
        <option value="2" {{ $status == 2 ? 'selected' : '' }}>
            Pending For Approval
        </option>
    @endcan

    @can('view-module', 186)
        <option value="3" {{ $status == 3 ? 'selected' : '' }}>
            Approved
        </option>
    @endcan
                                    </select>
                                </div>
                                <h5 class="transaction-table-title m-0"
                                    style="cursor:pointer"
                                    data-bs-toggle="modal"
                                    data-bs-target="#missingModal">
                                    WASTE KRAFT <span id="missing">({{ $count }})</span>
                                </h5>
                                @can('view-module', 100)
                                <a href="{{ route('add-purchase-info') }}">
                                    <button class="btn btn-info">ADD</button>
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="transaction-table bg-white table-view shadow-sm">
                            <table class="table-striped table m-0 shadow-sm payment_table">
                                <thead>
                                    <tr class=" font-12 text-body bg-light-pink ">
                                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pending_report as $k1 => $v1)                                        
                                        @foreach($v1 as $key => $value)
                                            @php $value = json_decode(json_encode($value)); @endphp
                                            <tr>
                                                <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                                                <td>{{$value->vehicle_no}}</td>
                                                <td>{{$value->group_name}}</td>
                                                <td>{{$value->account_name}}</td>
                                                <td>{{$value->gross_weight}}</td>
                                                <td class="w-min-120 text-center">
                                                    <a href="{{ URL::to('edit-purchase-info/' . $value->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                                    <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->id; ?>">
                                                        <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                                    </button>
                                                    <img src="{{ URL::asset('public/assets/imgs/start.svg')}}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;" id="start_btn_{{$value->id}}"  data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="0" data-vehicle_no="{{$value->vehicle_no}}" data-entry_date="{{$value->entry_date}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-purchase_taxable_amount="{{$value->purchase_taxable_amount}}" data-purchase_price="">
                                                </td>
                                            </tr>
                                        @endforeach                                        
                                    @endforeach                                        
                                </tbody>
                            </table>
                        </div>
                    @endcan
                    @endif
                    @if($status == 0)
                    @can('view-module', 184)
                        <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="transaction-table-title m-0">In Process</h5>
                        </div>

                        <button class="btn btn-info" id="printTable">Print</button>
                        </div>
                        <div class="transaction-table bg-white table-view shadow-sm">
                        <table class="table-striped table m-0 shadow-sm payment_table" id="processTable">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink ">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Slip No.</th>
                                    <th></th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($in_process_report as $k1 => $v1)
                                    @foreach($v1 as $key => $value)
                                        @php $value = json_decode(json_encode($value)); @endphp
                                        <tr>
                                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                                            <td>{{$value->vehicle_no}}</td>
                                            <td>{{$value->group_name}}</td>
                                            <td>{{$value->account_name}}</td>
                                            <td>{{$value->gross_weight}}</td>
                                            <td>{{$value->voucher_no}}</td>
                                            <td> @if($value->purchase_voucher_no=="") <img src="{{ URL::asset('public/assets/imgs/purchase-order-24.png')}}" >  @endif
                                                @if($value->image_1=="") <img src="{{ URL::asset('public/assets/imgs/file-jpg-color-red-icon.png')}}" > @endif</td>
                                            <td class="w-min-120 text-center">
                                                 @can('view-module', 192)
                                                <button class="btn btn-info upload_image" data-id="{{$value->id}}">Click</button>
                                                @endcan
                                                @can('view-module', 191)
                                                <img src="{{ URL::asset('public/assets/imgs/start.svg')}}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;" id="start_btn_{{$value->id}}" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="1" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}" data-purchase_taxable_amount="{{$value->purchase_taxable_amount}}" data-purchase_price="<?php echo $value->prices; ?>">
                                                 @endcan
                                                @if($value->purchase_voucher_no=="")
                                                {{-- <img style="width: 17px;cursor:pointer;" src="{{ URL::asset('public/assets/imgs/merge.svg')}}" class="merge" data-id="{{$value->id}}"> --}}

                                                <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1 delete" data-id="{{$value->id}}" alt="" style="cursor:pointer;">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endcan
                    @endif
                    @if($status == 2)
                    @can('view-module', 185)
                        <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <div class="d-flex align-items-center gap-3">
                                <h5 class="transaction-table-title m-0">Pending For Approval</h5>

                                <select class="form-select form-select-sm"
                                        style="width:220px"
                                        onchange="window.location='{{ url('waste-kraft') }}?status=' + this.value">
                                          @can('view-module', 184)
        <option value="0" {{ $status == 0 ? 'selected' : '' }}>
            Pending, In Process
        </option>
    @endcan

    @can('view-module', 185)
        <option value="2" {{ $status == 2 ? 'selected' : '' }}>
            Pending For Approval
        </option>
    @endcan

    @can('view-module', 186)
        <option value="3" {{ $status == 3 ? 'selected' : '' }}>
            Approved
        </option>
    @endcan
                                </select>
                        </div>
                        </div>
                        <div class="transaction-table bg-white table-view shadow-sm">
                        <table class="table-striped table m-0 shadow-sm payment_table" id="pendingForApprovelTable">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink ">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Net Weight</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Slip No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pending_for_approval_report as $k1 => $v1)
                                    @foreach($v1 as $key => $value)
                                        @php $value = json_decode(json_encode($value)); @endphp
                                        <tr @if($value->reapproval==1) style="background:grey" @endif>
                                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                                            <td>{{$value->vehicle_no}}</td>
                                            <td>{{$value->group_name}}</td>
                                            <td>{{$value->account_name}}</td>
                                            <td>{{$value->gross_weight-$value->tare_weight}}</td>
                                            <td>{{$value->voucher_no}}</td>
                                            <td class="w-min-120 text-center">
                                                <button class="btn btn-info start" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" id="start_btn_{{$value->id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="2" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}" data-purchase_taxable_amount="{{$value->purchase_taxable_amount}}" data-purchase_price="<?php echo $value->prices; ?>">View</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                
                            </tbody>
                        </table>
                        </div>
                    @endcan
                    @endif
                    @if($status == 3)
                    @can('view-module', 186)
                        <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="transaction-table-title m-0">Approved</h5>

                            <select class="form-select form-select-sm"
                                    style="width:220px"
                                    onchange="window.location='{{ url('waste-kraft') }}?status=' + this.value">
                                           @can('view-module', 184)
        <option value="0" {{ $status == 0 ? 'selected' : '' }}>
            Pending, In Process
        </option>
    @endcan

    @can('view-module', 185)
        <option value="2" {{ $status == 2 ? 'selected' : '' }}>
            Pending For Approval
        </option>
    @endcan

    @can('view-module', 186)
        <option value="3" {{ $status == 3 ? 'selected' : '' }}>
            Approved
        </option>
    @endcan
                            </select>
                        </div>
                        <div class="d-md-flex d-block">
                            <div class="calender-administrator my-2 my-md-0">
                                <input type="date" id="approve_from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="approve_from_date" value="{{$approve_from_date}}">
                            </div>
                            <div class="calender-administrator ms-md-4">
                                <input type="date" id="approve_to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date"  name="approve_to_date" value="{{$approve_to_date}}">
                            </div>
                            <button class="btn btn-info search_approved_btn" style="margin-left: 5px;">Search</button>
                        </div>
                        </div>
                        <div class="transaction-table bg-white table-view shadow-sm">
                        <table class="table-striped table m-0 shadow-sm payment_table" id="approvedTable">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink ">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Net Weight</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Slip No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approved_report as $k1 => $v1)                                    
                                    @foreach($v1 as $key => $value)
                                        @php $value = json_decode(json_encode($value)); @endphp
                                        <tr>
                                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                                            <td>{{$value->vehicle_no}}</td>
                                            <td>{{$value->group_name}}</td>
                                            <td>{{$value->account_name}}</td>
                                            <td>{{$value->gross_weight-$value->tare_weight}}</td>
                                            <td>{{$value->voucher_no}}</td>
                                            <td class="w-min-120 text-center">
                                                <button class="btn btn-info start" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" id="start_btn_{{$value->id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="3" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}" data-purchase_taxable_amount="{{$value->purchase_taxable_amount}}" data-purchase_price="<?php echo $value->prices; ?>">View</button>
                                            </td>
                                        </tr>
                                    @endforeach                                    
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endcan
                    @endif
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
<div class="modal fade" id="delete_subhead" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form  method="POST" action="{{route('delete-purchase-info')}}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <input type="hidden" value="" id="delete_id" name="delete_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="report_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content p-4 border-divider border-radius-8">

            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close report_modal_close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            
            <div class="modal-body p-4 modal-scroll-body">
                <div class="row">

                    <div class="col-md-7 left-section">
                        <!-- FORM FIELDS START -->
                    <div class="row">

                        <div class="mb-3 col-md-3">
                            <label for="account_id" class="form-label font-14 font-heading">Account Name</label>
                            <select id="account_id" class="form-select">
                                @foreach($accounts as $key => $value)
                                    <option value="{{$value->id}}">{{$value->account_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="entry_date" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="entry_date" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="group_id" class="form-label font-14 font-heading">Item Group</label>
                            <select id="group_id" class="form-select">
                                @foreach($item_groups as $key => $value)
                                    <option value="{{$value->id}}">{{$value->group_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="vehicle_no" class="form-label font-14 font-heading">Vehicle No.</label>
                            <input type="text" id="vehicle_no" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3 short_weight_div">
                            <label for="tare_weight" class="form-label font-14 font-heading">Gross Weight</label>
                            <input type="text" id="gross_weight" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="tare_weight" class="form-label font-14 font-heading">Tare Weight</label>
                            <input type="number" step="any" min="1" id="tare_weight" class="form-control" placeholder="Tare Weight"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="voucher_no" class="form-label font-14 font-heading">Slip Number</label>
                            <input type="text" id="voucher_no" class="form-control" placeholder="Slip Number"/>
                        </div>

                        <div class="mb-3 col-md-3 area_div">
                            <label for="location" class="form-label font-14 font-heading">Area</label>
                            <select id="location" class="form-select">
                                <option value="">Select Area</option>
                                @foreach($locations as $loc)
                                    <option value="{{$loc->id}}">{{$loc->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @can('view-module', 193)
                        <div class="mb-3 col-md-3 bill_url_div">
                            <a href="" id="bill_url"><button class="btn btn-info" style="margin-top: 28px;">Add Bill</button></a>
                        </div>
                        @endcan
                        <div class="mb-12 col-md-12"></div>

                        <div class="mb-3 col-md-3 purchase_div">
                            <label for="purchase_invoice_no" class="form-label font-14 font-heading">Purchase Invoice No.</label>
                            <input type="text" id="purchase_invoice_no" class="form-control" readonly/>
                        </div>

                        <div class="mb-3 col-md-3 purchase_div">
                            <label for="purchase_invoice_date" class="form-label font-14 font-heading">Purchase Invoice Date</label>
                            <input type="text" id="purchase_invoice_date" class="form-control" readonly/>
                        </div>

                        <div class="mb-3 col-md-3 purchase_div">
                            <label for="purchase_invoice_qty" class="form-label font-14 font-heading">Purchase Invoice Qty</label>
                            <input type="text" id="purchase_invoice_qty" class="form-control" readonly/>
                        </div>

                        <div class="mb-3 col-md-3 purchase_div">
                            <label for="purchase_invoice_amount" class="form-label font-14 font-heading">Purchase Invoice Amount</label>
                            <input type="text" id="purchase_invoice_amount" class="form-control" readonly/>
                        </div>

                    </div>
                    <!-- FORM FIELDS END -->

                    <!-- FIX: CLOSE THE ABOVE ROW BEFORE TABLE -->
                   

                    <div class="mb-12 col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Head</th>
                                    <th id="net_weight_view" style="text-align: right;width: 18%;"></th>
                                    <input type="hidden" id="net_weight">
                                    <input type="hidden" id="row_id">
                                    <th style="text-align: right;width: 16%;">Bill Rate</th>
                                    <th style="text-align: right;width: 15%;">Contract Rate</th>
                                    <th style="text-align: right">Report Amount</th>
                                    <th style="width: 19%;">Difference Amount</th>
                                </tr>
                            </thead>

                            <tbody id="report_body">

                                @foreach($heads as $key => $value)
                                    @if($value->group_type=='WASTE KRAFT')
                                        <tr class="head waste_head">
                                            <td><input type="text" class="form-control" value="{{$value->name}}" readonly></td>
                                            <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                            <td>
                                                <select class="form-select calculate bill_rate" id="bill_rate_{{$value->id}}" data-id="{{$value->id}}"></select>
                                            </td>
                                            <td><input type="text" class="form-control contract_rate calculate" id="contract_rate_{{$value->id}}" style="text-align: right" readonly data-id="{{$value->id}}"></td>
                                            <td><input type="text" class="form-control report_amount" id="report_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                            <td><input type="text" class="form-control difference_amount" id="difference_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                        </tr>
                                    @endif
                                @endforeach

                                <tr id="cut_row">
                                    <td><input type="text" class="form-control" value="Cut" readonly></td>
                                    <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_cut" style="text-align: right" data-id="cut"></td>
                                    <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_cut" style="text-align: left" data-id="cut"></td>
                                    <td><input type="text" class="form-control" id="contract_rate_cut" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control report_amount" id="report_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                                </tr>

                                <tr id="short_weight_row">
                                    <td><input type="text" class="form-control" value="Short Weight" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="qty_short_weight" style="text-align: right" data-id="short_weight"></td>
                                    <td>
                                        <select class="form-select calculate bill_rate" id="bill_rate_short_weight" data-id="short_weight"></select>
                                    </td>
                                    <td><input type="text" class="form-control" id="contract_rate_short_weight" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control report_amount" id="report_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                                </tr>

                                <tr>
                                    <td></td><td></td><td></td>
                                    <th style="text-align: right"></th>
                                    <td><input type="text" class="form-control" id="report_amount_total" style="text-align: right" readonly></td>
                                    <th><input type="text" class="form-control" id="difference_total_amount" style="text-align: right" readonly></th>
                                </tr>

                                <tr>
                                    <th colspan="6" style="text-align: right">
                                        <span id="invoice_taxable_amount"></span> | 
                                        <span id="total_report_amount"></span>
                                    </th>
                                </tr>

                            </tbody>
                        </table>
                        <div class="text-start">
                            @can('view-module', 194)
                            <button type="button" class="btn btn-xs-primary save_location" style="padding: 2px 6px;
                            font-size: 15px;
                            line-height: 1.2;">SAVE</button>
                            @endcan
                            @can('view-module', 197)
                            <button class="btn btn-success approve" style="display: none;    padding: 2px 6px;
                            font-size: 15px;
                            line-height: 1.2;">Approve</button>
                            @endcan
                             @can('view-module', 198)
                            <a href="" id="edit_purchase_url"><button class="btn btn-success edit_purchase" style="display: none;    padding: 2px 6px;
                            font-size: 15px;
                            line-height: 1.2;">Edit Purchase</button></a>
                             @endcan
                             @can('view-module', 199)
                            <button class="btn btn-warning revert_in_process" style="display: none;    padding: 2px 6px;
                            font-size: 15px;
                            line-height: 1.2;">Revert In Process</button> 
                            @endcan
                        </div>
                    </div>
                    </div>

                    <div class="col-md-5 right-section">                       
                        <div id="imageCarousel" class="carousel slide"  style="display: none;margin-top:115px">
                            <div class="carousel-inner image_div"></div>
    
                            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                               <span class="carousel-control-prev-icon"></span>
                            </button>
    
                            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                               <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- <div class="modal-footer">
                <button class="btn btn-xs-primary save_location">SAVE</button>
                <button class="btn btn-success approve" style="display:none">Approve</button>
            </div> --}}

            <div class="row">

                <!-- LEFT SIDE -->
                <div class="col-md-6 left-scroll-area">

                    
                    
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-md-6 left-scroll-area">
                    
                </div>

            </div>

            

        </div>
    </div>
</div>

<div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3 border-radius-8">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="imageUploadLabel">Upload Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{-- Upload Form --}}
        <form action="{{ route('upload-purchase-image') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div id="image-inputs">
            <div class="mb-3 d-flex align-items-center">
              <input type="file" name="images[]" class="form-control me-2 image-input" accept="image/*" capture="environment" required>
              <button type="button" class="btn btn-sm btn-primary add-more">+</button>
            </div>
          </div>
          <input type="hidden" name="image_purchase_id" id="image_purchase_id">
          {{-- Live Preview Section --}}
          <div id="preview" class="row g-3"></div>
          <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">Upload</button>
          </div>
        </form>

        {{-- Display Uploaded Images --}}
        @if(session('images'))
          <div class="mt-4">
            <p class="text-success">Images uploaded successfully!</p>
            <div class="row g-3">
              @foreach(session('images') as $img)
                <div class="col-md-4">
                  <img src="{{ asset('storage/' . $img) }}" class="img-fluid rounded border">
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="missingModal" tabindex="-1" aria-labelledby="missingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="missingModalLabel">Missing Entries</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          @if(!empty($missing))
          
              <table class="table table-bordered table-striped">
                  <thead class="table-dark">
                      <tr>
                          <th>#</th>
                          <th>Missing Value</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($missing->take(100) as $index => $value)
                          <tr>
                              <td>{{ $index + 1 }}</td>
                              <td>{{ $value }}</td>
                          </tr>
                      @endforeach
                  </tbody>
              </table>
          @else
              <p class="text-muted text-center mb-0">No missing values found.</p>
          @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="missingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="missingModalLabel">Merge</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="merge_slip_no" class="form-label">Merge Slip No.</label>
                    <input type="text" class="form-control" id="merge_slip_no" placeholder="Merge Slip No.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info save_merge">Submit</button>
                <button type="button" class="btn btn-secondary " data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
@include('layouts.footer')
<script>
    let open_id = "{{ request('id') }}";
    let approve_from_date = "{{ request('approve_from_date') }}";
    let approve_to_date = "{{ request('approve_to_date') }}";
    var modal_open_status = 0;
    var save_approve_status = 0;
    let page_status = "{{ request()->get('status', 0) }}";
    $(document).ready(function(){
        if(open_id!=""){
            $("#start_btn_"+open_id).trigger('click');
        }
        if(sessionStorage.getItem("click_id")!=""){
            $("#start_btn_"+sessionStorage.getItem("click_id")).trigger('click');
        }
        $('#openMissingModal').on('click', function(){
            $('#missingModal').modal('show');
        });
        $(".merge").click(function(){
            let id = $(this).attr("data-id");
            $(".save_merge").attr("data-id",id);
            $("#mergeModal").modal("show");
        });
        $(".save_merge").click(function(){
            let merge_slip_no = $("#merge_slip_no").val();
            let id = $(this).attr("data-id");
            if(merge_slip_no==""){
                alert("Please enter merge slip no.");
                return false;
            }            
            $.ajax({
                url:"{{url('supplier-waste-kraft-merge')}}",
                type:"POST",
                data:{
                    _token:"{{csrf_token()}}",
                    id:id,
                    merge_slip_no:merge_slip_no
                },
                success:function(res){
                    if(res!=""){
                        let obj = JSON.parse(res);
                        console.log(res);
                        if(obj.status==1){
                            alert(obj.message);
                            location.reload();
                        }else{
                            alert(obj.message);
                        }
                    }else{
                        alert("Something went wrong.");
                    }
                    
                }
            });
        });

    })
    $(".delete").click(function() {
        var id = $(this).attr("data-id");
        $("#delete_id").val(id);
        $("#delete_subhead").modal("show");
    });
    $(".cancel").click(function() {
        $("#delete_subhead").modal("hide");
    });
    $(".start").click(function(){
        $("#cover-spin").show();
        $("#imageCarousel").hide();
        $(".approve").hide();
        $(".edit_purchase").hide();
        $(".revert_in_process").hide();
        $(".contract_rate").attr('readonly',true);
        $(".waste_head").show();
        $(".fuel_head").hide();
        $(".item_div").hide();
        $(".area_div").show();
        let id = $(this).attr("data-id");
        
        let account_id = $(this).attr('data-account_id');
        let group_id = $(this).attr('data-group_id');
        let gross_weight = $(this).attr('data-gross_weight');
        let map_purchase_id = $(this).attr('data-map_purchase_id');
        let purchase_voucher_no = $(this).attr('data-purchase_voucher_no');
        let purchase_date = $(this).attr('data-purchase_date');
        let purchase_amount = $(this).attr('data-purchase_amount');
        let purchase_qty = $(this).attr('data-purchase_qty');
        let purchase_taxable_amount = $(this).attr('data-purchase_taxable_amount');
        let purchase_price = $(this).attr('data-purchase_price');
        let bill_price_options = "<option value=''>Bill Price</option>";
        let max_purchase_price = 0;
        let purchase_price_count = 1;
        if(purchase_price!=""){
            purchase_price = JSON.parse(purchase_price);
            max_purchase_price = Math.max(...purchase_price);
            purchase_price.forEach(function(e){
                bill_price_options+="<option value='"+e+"' data-qty_status='1'>"+e+"</option>";
            });
            purchase_price_count = purchase_price.length;
        }
        
        $(".bill_rate").html(bill_price_options);
        let price = "";
        if(purchase_taxable_amount!="" && purchase_qty!=""){
            price = purchase_taxable_amount/purchase_qty;
            price = price.toFixed(2);
        }
        $("#invoice_taxable_amount").html("Purchase Taxable Amount : "+purchase_taxable_amount);
        $("#difference_total_amount").css({ color: "black" });
        let status = $(this).attr('data-status');
        let vehicle_no = $(this).attr('data-vehicle_no');
        let entry_date = $(this).attr('data-entry_date');
        $(".save_location").attr('data-status',status);
        $(".qty").attr('readonly',false);
        if(status==2){
            $(".contract_rate").attr('readonly',false);
            sessionStorage.setItem("click_id", id);
        }
        $(".approve").attr('data-id',id);        
        $("#account_id").val(account_id);
        $("#account_id").attr('data-id',id);  
        $("#entry_date").attr('data-id',id);
        $("#row_id").val(id);
        $("#gross_weight").val(gross_weight);
        $("#vehicle_no").val(vehicle_no);
        $("#group_id").val(group_id);
        $("#entry_date").val(entry_date);
        $(".purchase_div").hide();
        if((status==0 || status==1) && purchase_amount=="" && modal_open_status==0){
            $("#voucher_no").val('');
            $(".qty").val('');
            $("#qty_short_weight").val('');
            $(".contract_rate").val('');
            $("#contract_rate_short_weight").val('');
            $(".difference_amount").val('');
            $("#difference_amount_short_weight").val('');
            $("#difference_total_amount").val('');
            $("#tare_weight").val('');
        }else if(modal_open_status==1){
            $(".contract_rate").val('');
        }
        if((status==0 || status==1) && purchase_amount==""){
            $("#entry_date").attr('readonly',false);
            $("#vehicle_no").attr('readonly',false);
            $("#group_id").removeClass('unchange_dropdown');
            $("#account_id").removeClass('unchange_dropdown');
        }
        if(status==2 || status==3){
            let edit_purchase_url = "{{url('purchase-edit/map_id')}}?row_id=ids";
            edit_purchase_url = edit_purchase_url.replace('map_id',map_purchase_id);
            edit_purchase_url = edit_purchase_url.replace('ids',id);
            $("#edit_purchase_url").attr('href',edit_purchase_url);
            $(".edit_purchase").show();
            $(".revert_in_process").show();
            
        }
        $(".save_location").show();
        if(status==2){
            $(".save_location").hide();
        }
        if(map_purchase_id!="" && map_purchase_id!=0){
            $(".bill_url_div").hide();
            $("#purchase_invoice_no").val(purchase_voucher_no);
            let parts = purchase_date.split('-');// ["2025", "12", "01"]
            let formatted = parts[2] + '-' + parts[1] + '-' + parts[0];
            $("#purchase_invoice_date").val(formatted);
            $("#purchase_invoice_amount").val(purchase_amount);
            $("#purchase_invoice_qty").val(purchase_qty);
            $("#bill_url").attr('href','');
            $(".purchase_div").show();
        }else{
            let bill_url = "{{url('purchase/create?row_id=row_id_value&account_id=account_id_value&group_id=group_id_value')}}";
            bill_url = bill_url.replace('row_id_value',id);
            bill_url = bill_url.replace('account_id_value',account_id);
            bill_url = bill_url.replace('group_id_value',group_id);
            bill_url = bill_url.replace(/&amp;/g, "&");
            $("#bill_url").attr('href',bill_url);
            $(".bill_url_div").show();
        }
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
                //Get Store All Data
                $.ajax({
                    url:"{{url('view-complete-purchase-info/')}}/"+id,
                    type:"POST",
                    data:{_token:'{{csrf_token()}}'},
                    success:function(res){
                        $("#cover-spin").hide();
                        if(res!=""){
                            let obj = JSON.parse(res);
                            
                            if(obj.purchase==null){
                                if(modal_open_status==0){
                                    $("#report_modal").modal('toggle');
                                }else if(modal_open_status==1){
                                    modal_open_status = 0;
                                }
                                return;
                            }
                            
                            let head_data_arr = [];
                            $("#difference_total_amount").val(obj.purchase.difference_total_amount);
                            $("#voucher_no").val(obj.purchase.voucher_no);
                            if(modal_open_status==0){
                                $("#location").val(obj.purchase.location);
                            
                            }
                            $("#tare_weight").val(obj.purchase.tare_weight);
                            let gross_weight = $("#gross_weight").val();
                            let tare_weight = $("#tare_weight").val();
                            if(tare_weight=="" || tare_weight==null){
                                tare_weight = 0;
                            }
                            let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
                            
                            $("#net_weight").val(net_weight);
                            $("#net_weight_view").html("Net Weight : "+net_weight);
                            
                            obj.reports.forEach(element => {
                                head_data_arr[element.head_id] = element;
                            });
                            let purchase_image = "";
                            $(".image_div").html(purchase_image);
                            if(obj.purchase.image_1!="" && obj.purchase.image_1!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_1); 
                                img_url = img_url.replace("images_names", obj.purchase.image_1); 
                                
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item active"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded" style="width: 100% !important;height: 100% !important;"></a></div>';
                            }
                            if(obj.purchase.image_2!="" && obj.purchase.image_2!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_2); 
                                img_url = img_url.replace("images_names", obj.purchase.image_2); 
                                
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded" style="width: 100% !important;height: 100% !important;"></a></div>';
                            }
                            if(obj.purchase.image_3!="" && obj.purchase.image_3!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_3); 
                                img_url = img_url.replace("images_names", obj.purchase.image_3); 
                                
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded" style="width: 100% !important;height: 100% !important;"></a></div>';
                            }
                            if(purchase_image!=""){
                                $("#imageCarousel").show();
                            }else{
                                $("#imageCarousel").hide();
                            }
                            $(".image_div").html(purchase_image);
                            
                            if(status==2){
                                $(".approve").show();
                            }
                            let report_amount_total = 0;
                            
                            $(".qty").each(function(){
                                let id = $(this).attr('data-id');
                                if(head_data_arr[id]){
                                    $("#qty_"+id).val(head_data_arr[id].head_qty);
                                    if(head_data_arr[id].head_qty!=0){
                                        if(purchase_price_count==1){
                                            $("#bill_rate_"+id).val(max_purchase_price);
                                        }else{
                                            let rate = parseFloat(head_data_arr[id].head_bill_rate).toString();
                                            $("#bill_rate_"+id).val(rate);
                                        }
                                    }else{
                                        $("#bill_rate_"+id).val(max_purchase_price);
                                    }
                                    if(modal_open_status==0){
                                        $("#contract_rate_"+id).val(head_data_arr[id].head_contract_rate);
                                        let report_amount  = head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty;
                                        report_amount = report_amount.toFixed(2);
                                        $("#report_amount_"+id).val(report_amount);
                                        $("#difference_amount_"+id).val(head_data_arr[id].head_difference_amount);
                                        report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty);
                                    }
                                }
                            });
                            let short_weight_id = "short_weight";
                            if(head_data_arr[short_weight_id]){
                                $("#qty_"+short_weight_id).val(head_data_arr[short_weight_id].head_qty);
                                //if(status!=1){
                                    let rate = parseFloat(head_data_arr[short_weight_id].head_bill_rate).toString();
                                    //$("#bill_rate_"+short_weight_id).val(rate);
                                    $("#bill_rate_"+short_weight_id).val(max_purchase_price);
                                    
                                //}
                                $("#contract_rate_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate);
                                $("#report_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                                $("#difference_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_difference_amount);
                                report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                            }
                            $("#bill_rate_cut").val(max_purchase_price);
                            $("#report_amount_total").val(report_amount_total);
                            let diff_cal_amount = parseFloat(purchase_taxable_amount) - parseFloat(report_amount_total);
                            let diff_cal_rate_amount = $("#difference_total_amount").val();
                            $("#total_report_amount").html("Total Report Amount : "+report_amount_total);
                            if(parseFloat(diff_cal_amount)!=parseFloat(diff_cal_rate_amount)){
                                $("#difference_total_amount").css({ color: "red" });
                            }
                            $(".calculate").each(function(){
                                $(this).keyup();
                            });

                            if(status==3){
                                //$(".save_location").hide();
                            }
                            // $("#bill_url").hide();
                            if(modal_open_status==0){
                                $("#report_modal").modal('toggle');
                            }else if(modal_open_status==1){
                                modal_open_status = 0;
                            }
                            if(open_id!=""){
                                $(".save_location").click();
                            }
                        }
                        
                    }
                });
               
                
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
                    "account_id": account_id,
                    "date":$("#entry_date").val(),
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
        let net_weight = $("#net_weight").val();
        if(net_weight==""){
            net_weight = 0;
        }
        let purchase_invoice_qty = $("#purchase_invoice_qty").val();
        if(purchase_invoice_qty==""){
            purchase_invoice_qty = 0;
        }
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_weight = parseFloat(qty_weight) + parseFloat($(this).val());
            }
        })
        
        short_weight = parseFloat(purchase_invoice_qty) - parseFloat(net_weight);
        $("#qty_short_weight").val(short_weight);
        let bill_rate_short_weight = $("#bill_rate_short_weight").val();
        if(bill_rate_short_weight==undefined || bill_rate_short_weight==""){
            bill_rate_short_weight = 0;
        }
        $("#difference_amount_short_weight").val(parseFloat(short_weight)*parseFloat(bill_rate_short_weight));
        
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
        let report_amount = contract_rate*qty;
        report_amount = report_amount.toFixed(2);
        $("#report_amount_"+id).val(report_amount);
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

        var total = 0;
        $(".report_amount").each(function(){
            var val = $(this).val();
            if(val == ''){
                val = 0;
            }
            total = parseFloat(total) + parseFloat(val);
        });
        $("#report_amount_total").val(total.toFixed(2));
        $("#total_report_amount").html("Total Report Amount : "+total.toFixed(2));
    }
    $("#tare_weight").keyup(function(){
        $("#net_weight").val('');
        $("#net_weight_view").html("");
        let gross_weight = $("#gross_weight").val();
        let tare_weight = $("#tare_weight").val();
        let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
        if(net_weight<0){
            alert("Please Enter Valid Tare Weight");
            return;
        }
        $("#net_weight").val(net_weight);
        $("#net_weight_view").html("Net Weight : "+net_weight);
        
        $(".calculate").each(function(){
            $(this).keyup();
        });
    });
    $(".save_location").click(function(){
        var id = $("#row_id").val();
        let group_type = "waste_craft";
        var location_id = $("#location").val();
        let status = $(this).attr('data-status');
        if(status==1){
            sessionStorage.setItem("click_table_state","processTable");
        }else if(status==2){
            sessionStorage.setItem("click_table_state","pendingForApprovelTable");
        }else if(status==3){
            sessionStorage.setItem("click_table_state","approvedTable");
        }
        
        group_type = "waste_craft";
        if(location_id == ''){
            alert("Please select area");
            return;
        }
        var voucher_no = $("#voucher_no").val();
        if(voucher_no == ''){
            alert("Please enter Slip Number");
            return;
        }
        var purchase_id = $("#row_id").val();
        if(purchase_id == ''){
            alert("Purchase id not found");
            return;
        }
        let tare_weight = $("#tare_weight").val();
        if(tare_weight == ''){
            alert("Please enter tare weight");
            return;
        }
        let qty_total = 0;
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_total = parseFloat(qty_total) + parseFloat($(this).val());
            }
        });
        let net_weight = $("#net_weight").val();
        if(parseFloat(qty_total)!=parseFloat(net_weight)){
            alert("Total head quantity must be equal to net quantity.")
            return;
        }
        let arr = [];let bill_rate_status = 1;
        let purchase_invoice_no = $("#purchase_invoice_no").val();
        $(".bill_rate").each(function(){
            if(($(this).val()=="" || $(this).val()==null) && purchase_invoice_no!=""){
                bill_rate_status = 0;
            }
            arr.push({'id':$(this).attr('data-id'),'contract_rate':$("#contract_rate_"+$(this).attr('data-id')).val(),'bill_rate':$(this).val(),'qty':$("#qty_"+$(this).attr('data-id')).val(),'difference_amount':$("#difference_amount_"+$(this).attr('data-id')).val()});
        });
        
        if(bill_rate_status==0 && status!=0){
            alert("Please select bill rate");
            return;
        }
        
        let account_id = $("#account_id").val();
        let entry_date = $("#entry_date").val();
        let group_id = $("#group_id").val();
        let vehicle_no = $("#vehicle_no").val();
        let gross_weight = $("#gross_weight").val();
        var data = {
            "voucher_no": voucher_no,
            "location": location_id,
            'item_id':"",
            "purchase_id": purchase_id,
            "tare_weight": tare_weight,
            "account_id": account_id,
            "entry_date": entry_date,
            "group_id": group_id,
            "vehicle_no": vehicle_no,
            "group_type":group_type,
            "gross_weight":gross_weight,
            "data":JSON.stringify(arr),
            "save_approve_status":save_approve_status,
            "difference_total_amount": $("#difference_total_amount").val(),
            "_token": "{{ csrf_token() }}"
        };
        $("#cover-spin").show();
        $.ajax({
            url:"{{url('store-supplier-purchase-report')}}",
            type:"POST",
            data:data,
            success:function(res){
                response = JSON.parse(res);
                if(response.status == true){
                    alert(response.message);
                    if(approve_from_date!="" && approve_to_date!=""){
                        window.location = "waste-kraft?status="+page_status
                            + "&approve_from_date=" + approve_from_date
                            + "&approve_to_date=" + approve_to_date;
                    } else {
                        window.location = "waste-kraft?status="+page_status;
                    }

                    
                }else{
                    alert(response.message);
                }
                $("#cover-spin").hide();
            }
        });
    });
    $(".upload_image").click(function(){
        let id = $(this).attr('data-id');
        //sessionStorage.setItem("click_id", id);
        $("#image_purchase_id").val(id)
        $("#imageUploadModal").modal('toggle');
    });
    var image_count  = 1;
    document.addEventListener("DOMContentLoaded", function() {
        const imageInputs = document.getElementById("image-inputs");
        const preview = document.getElementById("preview");
        // Add & Remove file inputs
        imageInputs.addEventListener("click", function(e) {
            if (e.target.classList.contains("add-more")) {
                if(image_count==3){
                    alert('Upload Max 3 Image')
                    return;
                }
                image_count++;
                let newInput = `
                <div class="mb-3 d-flex align-items-center">
                <input type="file" name="images[]" class="form-control me-2 image-input" accept="image/*" capture="environment" required>
                <button type="button" class="btn btn-sm btn-danger remove">-</button>
                </div>`;
                e.target.closest(".mb-3").insertAdjacentHTML("afterend", newInput);
            }
            if (e.target.classList.contains("remove")) {
                image_count--;
                e.target.closest(".mb-3").remove();
                refreshPreview(); // refresh preview after removing
            }
        });
        // Live preview for selected images
        imageInputs.addEventListener("change", function(e) {
            if (e.target.classList.contains("image-input")) {
                refreshPreview();
            }
        });
        function refreshPreview() {
            preview.innerHTML = ""; // clear old previews
            const files = document.querySelectorAll(".image-input");
            files.forEach(input => {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let col = document.createElement("div");
                        col.className = "col-md-3";
                        col.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded border">`;
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });
        }
    });
    $(".approve").click(function(){
        if(confirm("Are you sure to approve purchase ?")==true){    
            save_approve_status = 1;
            $(".save_location").click();
            return; 
            $("#cover-spin").show();
            let id = $(this).attr('data-id');
            $.ajax({
                url:"{{url('approve-purchase-report')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "purchase_id": id,
                },
                success:function(res){
                    $("#cover-spin").hide();
                    if(res!=''){
                        let obj = JSON.parse(res);
                        if(obj.status==1){
                            //$(".save_location").click();
                            alert(obj.message);
                            location.reload();
                        }else{
                            alert("Something went wrong");
                        }
                    }
                }
            });
        }
    });
    $(".qty").keyup(function(){
        let qty_total = 0;
        if($(".save_location").attr("data-status")!=2){
            $(".save_location").show();
        }
        
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_total = parseFloat(qty_total) + parseFloat($(this).val());
            }
        });

        let net_weight = $("#net_weight").val();
        if(parseFloat(qty_total)!=parseFloat(net_weight)){
            $(".save_location").hide();
        }
    })
    $("#account_id").change(function(){
        modal_open_status = 1;
        $("#start_btn_"+$(this).attr('data-id')).attr('data-account_id',$(this).val());
       $("#start_btn_"+$(this).attr('data-id')).click();
    });
    $("#entry_date").change(function(){
        modal_open_status = 1;
        $("#start_btn_"+$(this).attr('data-id')).attr('data-entry_date',$(this).val());
        $("#start_btn_"+$(this).attr('data-id')).click();
    });
    $(document).on('click', '#printTable', function () {
    // Get the table rows
        const rows = document.querySelectorAll('#processTable tbody tr');

        let printContent = `
            <h3 style="text-align:center;">In Process Report</h3>
            <table border="1" cellspacing="0" cellpadding="6" style="width:100%; border-collapse:collapse; font-family:Arial; font-size:14px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle No</th>
                        <th>Account Name</th>
                        <th>Slip No.</th>
                    </tr>
                </thead>
                <tbody>
        `;

        rows.forEach(row => {
            const cols = row.querySelectorAll('td');
                // Check for image icons inside the 7th column (cols[6])
            const images = cols[6].querySelectorAll('img');
            let statusText = 0;

            // Determine condition based on images
            if (images.length === 0) {
                statusText = 0; // No icons means everything done
            } else {
                images.forEach(img => {
                    const src = img.getAttribute('src') || '';
                    if (src.includes('purchase-order-24.png')) {
                        statusText = 1;
                    }
                });
            }
            if(statusText==1){
                printContent += `
                <tr>
                    <td>${cols[0].innerText}</td>
                    <td>${cols[1].innerText}</td>
                    <td>${cols[3].innerText}</td>
                    <td>${cols[5].innerText}</td>
                </tr>
                `;
            }
            
        });

        printContent += `
                </tbody>
            </table>
        `;

        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Report</title></head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
    $(document).on('click', '.search_approved_btn', function () {

        let approve_from_date = $("#approve_from_date").val();
        let approve_to_date   = $("#approve_to_date").val();

        if (!approve_from_date || !approve_to_date) {
            alert("Please select both dates");
            return;
        }

        window.location.href =
            "{{ url('waste-kraft') }}" +
            "?status=3" +
            "&approve_from_date=" + approve_from_date +
            "&approve_to_date=" + approve_to_date;
    });

    $(document).on('change','.bill_rate',function(){
        // let selected_val_arr = [];let currentVal = $(this).val();
        // $(".bill_rate").each(function(){
        //     if($(this).find("option:selected").attr("data-qty_status")==1 && $(this).val()!="" && $(this).val()!=null){
        //         selected_val_arr.push($(this).val());
        //     }
        // });
        // // check if duplicate exists
        // let isDuplicate = selected_val_arr.filter(v => v == currentVal).length > 1;

        // if (isDuplicate) {
        //     alert("This rate is already selected");
        //     // reset this select
        //     $(this).val("").change();

        //     // clear the array
        //     selected_val_arr = [];

        //     return;
        // }
        $(".calculate").each(function(){
            $(this).keyup();
        });
    });

    $(".revert_in_process").click(function(){
        if(confirm("Are you sure to revert purchase to in process ?")==true){
            $("#cover-spin").show();
            let id = $("#row_id").val();
            $.ajax({
                url:"{{url('revert-in-process-purchase-report')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "row_id": id,
                },
                success:function(res){
                    $("#cover-spin").hide();
                    if(res!=''){
                        let obj = JSON.parse(res);
                        if(obj.status==1){
                            alert(obj.message);
                            location.reload();
                        }else{
                            alert("Something went wrong");
                        }
                    }
                }
            });
        }
    });
    // $(".report_modal_close").click(function(){
    //     sessionStorage.setItem("click_id", '');
    //     sessionStorage.setItem("click_table_state", '');
    // });
    $('#report_modal').on('hidden.bs.modal', function () {
        sessionStorage.setItem("click_id", '');
        sessionStorage.setItem("click_table_state", '');
    });
    document.addEventListener("DOMContentLoaded", function () {
        let target = "";
        console.log(sessionStorage.getItem("click_table_state"))
        if(sessionStorage.getItem("click_table_state")!=""){
            target = sessionStorage.getItem("click_table_state");
        }
        if (target) {
            let section = document.getElementById(target);
            if (section) {
                section.scrollIntoView({ behavior: "smooth" });
            }
        }
});

document.addEventListener('DOMContentLoaded', function () {

    // If status already present in URL → do nothing
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        document.getElementById('statusFilter').value = urlParams.get('status');
        return;
    }

    fetch("{{ url('user-waste-kraft-status') }}")
        .then(res => res.json())
        .then(data => {

            let status = data.status;
            let select = document.getElementById('statusFilter');

            // Ensure option exists (permission check)
            if (select.querySelector(`option[value="${status}"]`)) {
                select.value = status;
                window.location = "{{ url('waste-kraft') }}?status=" + status;
            }
        });
});
document.getElementById('report_modal')
        .addEventListener('shown.bs.modal', function () {
            const body = this.querySelector('.modal-scroll-body');
            if (body) body.focus();
        });
</script>
@endsection