@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Title Bar --}}
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2">TDS Sections</h5>

                    <a href="{{ route('admin.tds.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg"
                             width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                  fill="white"/>
                        </svg>
                    </a>
                </div>

                {{-- Table --}}
                <div class="bg-white table-view shadow-sm">
    <table id="example" class="table-striped table m-0 shadow-sm">
        <thead>
            <tr class="font-12 text-body bg-light-pink">
                <th>Section</th>
                <th>Rate (Ind/HUF)</th>
                <th>Rate (Others)</th>
                <th>Single Limit</th>
                <th>Aggregate Limit</th>
                <th>Applicable When</th>
                <th width="120">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($tdsSections as $tds)
                <tr>
                    <td>{{ strtoupper($tds->section) }}</td>
                    <td>{{ $tds->rate_individual_huf }}%</td>
                    <td>{{ $tds->rate_others }}%</td>
                    <td>{{ $tds->single_transaction_limit ?? '-' }}</td>
                    <td>{{ $tds->aggregate_transaction_limit ?? '-' }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$tds->applicable_when)) }}</td>

                    <td>
                        {{-- Edit --}}
                        <a href="{{ route('admin.tds.edit', $tds->id) }}">
                            <img src="{{ asset('public/assets/imgs/edit-icon.svg')}}" 
                                 class="px-1" alt="Edit">
                        </a>

                        {{-- Delete --}}
                        <button type="button"
        class="border-0 bg-transparent delete-tds"
        data-id="{{ $tds->id }}">
    <img src="{{ asset('public/assets/imgs/delete-icon.svg')}}" 
         class="px-1" alt="Delete">
</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

            </div>
        </div>
    </section>
</div>
{{-- Delete Modal --}}
<div class="modal fade" id="delete_tds" tabindex="-1">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" action="{{ route('admin.tds.delete') }}">
            @csrf

            <div class="modal-body text-center p-0">
               <img class="delete-icon mb-3 d-block mx-auto"
                    src="{{ asset('public/assets/imgs/administrator-delete-icon.svg')}}">

               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body">
                  Do you really want to delete this record?
                  This process cannot be undone.
               </p>
            </div>

            <input type="hidden" id="tds_id" name="tds_id">

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel"
                       data-bs-dismiss="modal">CANCEL</button>

               <button type="submit" class="ms-3 btn btn-red">
                  DELETE
               </button>
            </div>
         </form>
      </div>
   </div>
</div>
@include('layouts.footer')

<script>
$(document).ready(function(){

    if ($.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().destroy();
    }

    $('#example').DataTable({
        order: [[0, 'asc']],
        responsive: true,
        language: { emptyTable: "No TDS Sections available" }
    });

    $(document).on('click', '.delete-tds', function () {
        let id = $(this).data('id');
        $('#tds_id').val(id);
        $('#delete_tds').modal('show');
    });

});
</script>

@endsection