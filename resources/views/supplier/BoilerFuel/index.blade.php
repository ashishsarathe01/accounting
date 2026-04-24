@extends('layouts.app')
@section('content')

@include('layouts.header')

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

                {{-- Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between
                align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="transaction-table-title m-0 py-2">Boiler Fuel - Manage Supplier</h5>
                    <div class="d-md-flex d-block"> 
                     <input type="text" id="search" class="form-control" placeholder="Search">
                  </div>
                    @can('view-module', 200)
                        <a href="{{ route('supplier.boiler_fuel_create')}}" class="btn btn-xs-primary">ADD
                            <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                    fill="white"/>
                            </svg>
                        </a>
                    @endcan
                </div>

                <div class="transaction-table bg-white table-view shadow-sm mt-4">
                    <table id="supplierTable" class="table-striped table m-0 shadow-sm supplier_table">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th class="w-min-120 border-none bg-light-pink text-body">Supplier Name</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Items</th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
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

 $(document).ready(function () {

    var table = $('#supplierTable').DataTable({

        processing: true,
        serverSide: true,
        ajax: "{{ route('supplier.boilerfuel.datatable') }}",

        dom: "lrtip",

        columns: [
            { data: 'supplier_name' },
            { data: 'date' },
            { data: 'items', orderable:false, searchable:false },
            { data: 'action', orderable:false, searchable:false }
        ]

    });

    $('#search').on('keyup', function () {
        table.search(this.value).draw();
    });

});
</script>

@endsection
