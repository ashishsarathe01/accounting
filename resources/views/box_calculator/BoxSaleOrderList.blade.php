@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- SUCCESS --}}
@if(session('success'))

    <div class="alert alert-success">

        {{ session('success') }}

    </div>

@endif

{{-- TITLE --}}
<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

    <div class="d-flex align-items-center justify-content-between">

        <h5 class="transaction-table-title m-0">

            Box Sale Order – List

        </h5>

        <a href="{{ route('box.sale.order.create') }}"
           class="btn btn-xs-primary">

            ADD

        </a>

    </div>

</div>

{{-- TABLE --}}
<div class="bg-white table-view shadow-sm"
     style="overflow-x:auto;">

<table class="table table-bordered table-striped m-0">

    <thead>

        <tr class="bg-light-pink text-body">

            <th>
                SO No
            </th>

            <th>
                Date
            </th>

            <th>
                Party
            </th>

            <th>
                PO Number
            </th>

            <th style="text-align:right;">
                Total Qty
            </th>

            <th style="text-align:right;">
                Dispatched
            </th>

            <th style="text-align:right;">
                Pending
            </th>

            <th class="text-center">
                Action
            </th>

        </tr>

    </thead>

    <tbody>

        @foreach($saleOrders as $row)

            <tr>

                {{-- SO NUMBER --}}
                <td>

                    {{ $row->sale_order_no }}

                </td>

                {{-- DATE --}}
                <td>

                    {{ date('d-m-Y', strtotime($row->order_date)) }}

                </td>

                {{-- PARTY --}}
                <td>

                    {{ $row->party_name }}

                </td>

                {{-- PO NUMBER --}}
                <td>

                    {{ $row->po_number ?? '-' }}

                </td>

                {{-- TOTAL QTY --}}
                <td style="text-align:right;">

                    {{ number_format($row->total_qty ?? 0,2) }}

                </td>

                {{-- DISPATCHED --}}
                <td style="text-align:right;">

                    {{ number_format($row->dispatched_qty ?? 0,2) }}

                </td>

                {{-- PENDING --}}
                <td style="text-align:right;">

                    {{ number_format($row->pending_qty ?? 0,2) }}

                </td>

                {{-- ACTION --}}
                <td class="text-center">
                    @if($row->used_in_sale == 0)
                        <a href="{{ route('box.sale.order.edit', $row->id) }}">
                            <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}"
                                class="px-1">
                        </a>
                        <button class="border-0 bg-transparent delete"
                                data-id="{{ $row->id }}">
                            <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}"
                                class="px-1">
                        </button>
                    @endif
                    <a title="View Sale Order"
       href="{{ route('box.sale.order.view', $row->id) }}"
       target="_blank">

        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
             class="px-1"
             alt="View">

    </a>
                </td>
            </tr>

        @endforeach

        @if(count($saleOrders) == 0)

            <tr>

                <td colspan="8"
                    class="text-center">

                    No Sale Orders Found

                </td>

            </tr>

        @endif

    </tbody>

</table>

</div>

</div>

</div>

</section>

</div>

{{-- DELETE MODAL --}}
<div class="modal fade"
     id="deleteModal"
     tabindex="-1"
     aria-hidden="true">

   <div class="modal-dialog w-360 modal-dialog-centered">

      <div class="modal-content p-4 border-divider border-radius-8">

         <div class="modal-header border-0 p-0">

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

         </div>

         <form method="GET"
               id="deleteForm">

            <div class="modal-body text-center p-0">

               <button class="border-0 bg-transparent"
                       type="button">

                  <img class="delete-icon mb-3 d-block mx-auto"

                       src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg') }}">

               </button>

               <h5 class="mb-3 fw-normal">

                    Delete this record

               </h5>

               <p class="font-14 text-body">

                    Do you really want to delete this record?

               </p>

            </div>

            <div class="modal-footer border-0 mx-auto p-0">

               <button type="button"
                       class="btn btn-border-body cancel">

                    CANCEL

               </button>

               <button type="submit"
                       class="ms-3 btn btn-red">

                    DELETE

               </button>

            </div>

         </form>

      </div>

   </div>

</div>

@include('layouts.footer')

<script>

$(document).on("click", ".delete", function () {

    let id = $(this).data("id");

    let url = "{{ route('box.sale.order.delete', ':id') }}"
        .replace(':id', id);

    $("#deleteForm").attr('action', url);

    $("#deleteModal").modal('show');

});

$(document).on("click", ".cancel", function () {

    $("#deleteModal").modal('hide');

});

</script>

@endsection