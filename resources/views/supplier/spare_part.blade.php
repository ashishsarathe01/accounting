@extends('layouts.app')
@section('content')
@include('layouts.header')

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
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <!-- ================= PENDING SPARE PART ================= -->
                <div class="tab-content pt-5" id="tab-content">
                    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <h5 class="transaction-table-title m-0 py-2">PENDING REQUIREMENT</h5>
                        <h5 class="transaction-table-title m-0 py-2">SPARE PART</h5>
                        <a href="{{ route('spare-part.create') }}">
                            <button class="btn btn-primary btn-sm d-flex align-items-center">CREATE REQUIREMENT</button>
                        </a>
                        <a href="{{ route('spare-part.vehicle.index') }}">
                            <button class="btn btn-secondary btn-sm d-flex align-items-center">
                                MANAGE VEHICLE ENTRY
                            </button>
                        </a>
                    </div>

                    <div class="transaction-table bg-white table-view shadow-sm mt-3">
                        <table class="table-striped table m-0 shadow-sm payment_table">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Item</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Quantity</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($spare_parts as $spare)
                                    @php $rowSpan = count($spare->items); @endphp
                                    @foreach($spare->items as $index => $item)
                                        <tr>
                                            @if($index == 0)
                                                <td rowspan="{{ $rowSpan }}">{{ $spare->created_at->format('d-m-Y') }}</td>
                                                <td rowspan="{{ $rowSpan }}">{{ $spare->account->account_name ?? '' }}</td>
                                            @endif
                                            <td>{{ $item->item->name ?? '' }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            @if($index == 0)
                                                <td class="text-center" rowspan="{{ $rowSpan }}">
                                                    <a title="Edit Spare Part" href="{{ route('spare-part.edit', $spare->id) }}">
                                                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt="Edit">
                                                    </a>
                                                    <a title="View Spare Part" href="{{ route('spare-part.show', $spare->id) }}" target="_blank">
                                                        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View">
                                                    </a>
                                                    <a href="javascript:void(0);" class="delete" data-id="{{ $spare->id }}" title="Delete Spare Part">
                                                        <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="Delete">
                                                    </a>
                                                    <a title="Start Spare Part" href="{{ route('spare-part.start', $spare->id) }}">
                                                        <img src="{{ asset('public/assets/imgs/start.svg') }}" class="px-1 start" alt="Start" style="width:30px;cursor:pointer;">
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= PENDING FOR ADD PURCHASE ================= -->
                    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-5">
                        <h5 class="transaction-table-title m-0 py-2">PENDING FOR PURCHASE</h5>
                    </div>

                    <div class="transaction-table bg-white table-view shadow-sm mt-3">
                        <table class="table-striped table m-0 shadow-sm payment_table">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">PO Number</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Item</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Quantity</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pending_purchase as $spare)
                                    @php $rowSpan = count($spare->items); @endphp
                                    @foreach($spare->items as $index => $item)
                                        <tr>
                                            @if($index == 0)
                                                <td rowspan="{{ $rowSpan }}">{{ $spare->created_at->format('d-m-Y') }}</td>
                                                <td rowspan="{{ $rowSpan }}">{{ $spare->po_number ?? '' }}</td>
                                                <td rowspan="{{ $rowSpan }}">{{ $spare->account->account_name ?? '' }}</td>
                                                
                                                
                                            @endif
                                            
                                            <td>{{ $item->item->name ?? '' }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            @if($index == 0)
                                                <td class="text-center" rowspan="{{ $rowSpan }}">
                                                    <!-- Edit -->
                                                    <a title="Edit Spare Part" href="{{ route('spare-part.start', $spare->id) }}">
                                                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt="Edit">
                                                    </a>

                                                    <!-- View -->
                                                    <a title="View Spare Part" href="{{ route('spare-part.start.view', $spare->id) }}" target="_blank">
                                                        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View">
                                                    </a>

                                                    <!-- Delete -->
                                                    <a href="javascript:void(0);" class="delete" data-id="{{ $spare->id }}" title="Delete Spare Part">
                                                        <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="Delete">
                                                    </a>

                                                    <!-- Start -->
                                                    <a title="Start Spare Part" href="{{ route('spare-part.start.new', $spare->id) }}">
                                                        <img src="{{ asset('public/assets/imgs/start.svg') }}" class="px-1 start" alt="Start" style="width:30px;cursor:pointer;">
                                                    </a>
                                                </td>

                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= COMPLETED PURCHASE ================= -->
                    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-5">
                        <h5 class="transaction-table-title m-0 py-2">COMPLETED PURCHASE</h5>
                        <form class="" id="frm" method="get" action="{{ route('spare-part.index') }}">
                          @csrf
                          <div class="d-md-flex d-block">
                             <div class="calender-administrator my-2 my-md-0">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{!empty($from_date) ? date('Y-m-d', strtotime($from_date)) : ''}}">
                             </div>
                             <div class="calender-administrator ms-md-4">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{!empty($to_date) ? date('Y-m-d', strtotime($to_date)) : ''}}">
                             </div>
                             <div class="calender-administrator ms-md-2">
                                <button type="submit" class="btn btn-info next">Next</button>
                             </div>
                          </div>
                       </form>
                    </div>

                    <div class="transaction-table bg-white table-view shadow-sm mt-3">
                        <table class="table-striped table m-0 shadow-sm payment_table">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">PO Number</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Purchase Voucher No.</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Account Name</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Item</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Unit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Quantity</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                                @foreach($completed_purchase as $purchase)
                                    @php $rowSpan = count($purchase->descriptions); @endphp

                                    @foreach($purchase->descriptions as $index => $desc)
                                        <tr>
                                            @if($index == 0)
                                                <td rowspan="{{ $rowSpan }}">
                                                    {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}
                                                </td>
                                                <td rowspan="{{ $rowSpan }}">
                                                    {{ $purchase->sparePart->po_number ?? '' }} 
                                                </td>
                                                <td rowspan="{{ $rowSpan }}">
                                                    {{ $purchase->voucher_no ?? '' }} 
                                                </td>
                                                <td rowspan="{{ $rowSpan }}">
                                                    {{ $purchase->sparePart->account->account_name ?? '' }}
                                                </td>
                                            @endif

                                            <td>{{ $desc->item->name ?? '' }}</td>
                                            <td>{{ $desc->unitMaster->name ?? '' }}</td>


                                            <td>{{ $desc->qty }}</td>

                                            @if($index == 0)
                                                <td class="text-center" rowspan="{{ $rowSpan }}">
                                                    <a title="View Purchase Invoice"
                                                    href="{{ url('/purchase-invoice/' . $purchase->id) }}"
                                                    target="_blank">
                                                        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}">
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($completed_journal)>0)
                            <h3 style="text-align: center">Journal</h3>
                            <table class="table-striped table m-0 shadow-sm payment_table">
                                <thead>
                                    <tr class="font-12 text-body bg-light-pink">
                                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">PO Number</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body">Amount</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completed_journal as $purchase)
                                        <tr>
                                            <td>
                                                {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}
                                            </td>
                                            <td>
                                                {{ $purchase->sparePart->po_number ?? '' }}
                                            </td>
                                            <td>
                                                {{ $purchase->sparePart->account->account_name ?? '' }}
                                            </td>
                                            <td>{{ $purchase->total_amount ?? '' }}</td>

                                            
                                                <td class="text-center">
                                                    <a title="View Purchase Invoice"
                                                    href="{{ URL::to('journal/' . $purchase->id . '/edit') }}"
                                                    target="_blank">
                                                        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}">
                                                    </a>
                                                </td>
                                            
                                        </tr>
                                        
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>


                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="delete_heading" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog w-360 modal-dialog-centered">
                        <div class="modal-content p-4 border-divider border-radius-8">
                            <div class="modal-header border-0 p-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="" id="delete_form_id">
                                @csrf
                                @method('DELETE')
                                <div class="modal-body text-center p-0">
                                    <button class="border-0 bg-transparent">
                                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ asset('public/assets/imgs/administrator-delete-icon.svg') }}" alt="">
                                    </button>
                                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                                    <p class="font-14 text-body">Do you really want to delete this record?</p>
                                </div>
                                <div class="modal-footer border-0 mx-auto p-0">
                                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
$(document).ready(function() {
    $(".cancel").click(function() {
        $("#delete_heading").modal("hide");
    });

    $(document).on('click', '.delete', function() {
        let id = $(this).data("id");
        let url = '{{ route("spare-part.destroy", ":id") }}';
        url = url.replace(':id', id);
        $("#delete_form_id").attr("action", url);
        $("#delete_heading").modal("show");
    });
});
</script>

@endsection
