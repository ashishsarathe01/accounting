@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
    .supplier_table {
        table-layout: fixed !important;
        width: 100% !important;
    }

    .supplier_table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: inherit !important;   
        border-bottom: 2px solid #cfcfcf !important;
        white-space: nowrap;
        text-align: left;
    }

    .supplier_table tbody tr:nth-child(even) {
        background: #fafafa !important;
    }

    .supplier_table th,
    .supplier_table td {
        padding: 6px 10px !important;
        border-right: 1px solid #e2e2e2 !important;
        border-bottom: 1px solid #dcdcdc !important;
        white-space: normal !important;      
        word-break: break-word !important;    
        text-align: left;
    }

    .supplier_table th:last-child,
    .supplier_table td:last-child {
        border-right: none !important;
    }

    .supplier_table thead tr {
        border-bottom: 2px solid #cfcfcf !important;
    }

    .supplier-separator td {
        padding: 0 !important;
        height: 10px !important;
        background: #d9d9d9 !important;
        border: none !important;
    }

</style>



<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                {{-- Page Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">Waste Kraft - Manage Supplier</h5>
                    @can('view-module', 204)
                    <button class="btn btn-primary btn-sm d-flex align-items-center supplier_bonus" >Supplier Bonus</button>
                    @endcan
                  <div class="d-md-flex d-block"> 
                     <input type="text" id="search" class="form-control" placeholder="Search">
                  </div>
                    @can('view-module', 104)
                        <a href="{{route('supplier.waste_kraft_create')}}" class="btn btn-xs-primary">ADD
                            <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 20 20" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                    fill="white" />
                            </svg>
                        </a>
                    @endcan
                </div>

                <div class="transaction-table bg-white table-view shadow-sm mt-4" style="max-height: 70vh; overflow-y: auto; overflow-x: hidden;">

                    <table id="supplierTable" class="table-striped table m-0 shadow-sm supplier_table">

                        @php
                            // build global item list from ALL suppliers
                            //$globalItems = [];

                            //foreach ($suppliers as $supplier) {
                                //foreach ($supplier->latestLocationRate as $r) {
                                    //$globalItems[$r->name] = $r->name;
                                //}
                            //}

                            //$globalItems = array_values($globalItems); // item names (KRAFT I, KRAFT II...)
                        @endphp

                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th class="text-nowrap">Supplier Name</th>
                                <th class="text-nowrap">Date</th>
                                <th class="text-nowrap">Location</th>

                                @foreach ($globalItems as $item)
                                    <th class="text-nowrap">{{ $item }}</th>
                                @endforeach

                                <th class="w-min-120 text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
   <!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="supplierDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="" id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="bonus_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>  
               
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Supplier Bonus</h5>
        <div class="modal-body">
        <div class="row">
            <table class="table table-bordered bonus_tbl">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Bonus</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        </div>
    </div>
   </div>
</div>
@include('layouts.footer')

<script>
   $(document).on("click", ".delete", function () {

    let id = $(this).attr("data-id");
    let type = $(this).attr("data-type"); // Waste or Fuel
    let url = "";

    if (type === "Waste") {
        url = "{{ route('supplier.wastekraft.destroy', ':id') }}";
    } else {
        url = "{{ route('fuel-supplier.destroy', ':id') }}";
    }

    url = url.replace(':id', id);

    $("#deleteForm").attr('action', url);
    $("#supplierDeleteModal").modal('show');
});
$(document).on("click", ".cancel", function(){
      $("#supplierDeleteModal").modal('hide');
   });
   $(document).on("click", ".supplier_bonus", function () {

    $.ajax({
        url: "{{ url('get-supplier-bonus') }}",
        method: "POST",
        data: { _token: "{{ csrf_token() }}" },

        success: function(res) {

            let html = "";

            if (res.bonus && res.bonus.length > 0) {

                // grouping manually (Object.groupBy does NOT exist)
                let grouped = {};
                res.bonus.forEach(function(item) {
                    let key = item.account_name || "Unknown";
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(item);
                });

                for (let supplier in grouped) {
                    let bonusTable = `<table class="table table-bordered mb-0">`;
                    grouped[supplier].forEach(function(row) {
                        bonusTable += `<tr>
                            <td>${row.name}</td>
                            <td>${row.bonus}</td>
                        </tr>`;
                    });
                    bonusTable += `</table>`;

                    html += `<tr>
                        <td><strong>${supplier}</strong></td>
                        <td>${bonusTable}</td>
                    </tr>`;
                }
            } else {
                html = `<tr><td colspan="2" class="text-center">No bonus available</td></tr>`;
            }

            $(".bonus_tbl tbody").html(html);

            // Bootstrap 5 modal show
            var modal = new bootstrap.Modal(document.getElementById('bonus_modal'));
            modal.show();
        }
    });

});

document.addEventListener('DOMContentLoaded', function () {
        // Restore the last active tab from localStorage
        let activeTab = localStorage.getItem('addSupplierActiveTab');
        if (activeTab) {
            let triggerEl = document.querySelector(`[data-bs-toggle="tab"][href="${activeTab}"]`);
            if (triggerEl) {
                new bootstrap.Tab(triggerEl).show();
            }
        }

        // Save the active tab on click
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('addSupplierActiveTab', event.target.getAttribute('href'));
            });
        });
    });
$(document).ready(function () {

    $('#supplierTable').DataTable({

        processing: true,
        serverSide: true,

        ajax: "{{ route('supplier.wastekraft.datatable') }}",

        columns: [

            { data: 'supplier_name', name: 'supplier_name' },
            { data: 'date', name: 'date' },
            { data: 'location', name: 'location' },

            @foreach ($globalItems as $item)
            { data: '{{ $item }}', name: '{{ $item }}', defaultContent: '' },
            @endforeach

            { data: 'action', orderable:false, searchable:false }

        ]

    });

});
</script>

@endsection
