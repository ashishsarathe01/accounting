@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if (session('success'))
   <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
   <h5 class="m-0">
      @if($type == 'raw')
      List of Job Work In – Raw Material
      @else
      List of Job Work In – Finished Goods
      @endif
   </h5>

   <form method="GET"
action="{{ $type == 'raw' ? route('jobworkin.raw') : route('jobworkin.finished') }}">
      <div class="d-flex">
         <input type="date" class="form-control"
            name="from_date"
            value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">

         <input type="date" class="form-control ms-2"
            name="to_date"
            value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">

         <button class="btn btn-info ms-2">Search</button>
      </div>
   </form>

   <div>
      <input type="text" id="search" class="form-control" placeholder="Search">
   </div>

   <a href="{{ $type == 'raw' ? route('jobworkin.create.raw') : route('jobworkin.create.finished') }}"
   class="btn btn-xs-primary">
      ADD
   </a>
</div>

<div class="transaction-table bg-white shadow-sm">
<table class="table sale_table">
<thead>
<tr class="bg-light-pink">
   <th>Date</th>
   <th>Voucher No</th>
   <th>Party Name</th>
   <th class="text-end">Amount</th>
   <th class="text-center">Action</th>
</tr>
</thead>
<tbody>

@foreach($jobWorks as $row)
<tr>
   <td>{{ date('d-m-Y', strtotime($row->date)) }}</td>
   <td class="text-center">{{ $row->voucher_no_prefix }}{{ $row->voucher_no }}</td>
   <td>{{ $row->account_name }}</td>
   <td class="text-end">{{ $row->total }}</td>
   <td class="text-center">
      <a href="{{ $type == 'raw'
        ? route('jobworkin.edit.raw', $row->id)
        : route('jobworkin.edit.finished', $row->id) }}">
         <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit">
      </a>
      <button type="button"
           class="border-0 bg-transparent delete-jobwork"
           data-id="{{ $row->id }}">
      <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="Delete">
   </button>

   {{-- VIEW --}}
   <!-- <a title="View Job Work"
   href="{{ route('jobworkin.view', $row->id) }}"
   target="_blank">
   <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View">
</a> -->

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
<div class="modal fade" id="delete_jobwork" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">

         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" action="{{ route('jobworkin.delete') }}">
            @csrf

            <div class="modal-body text-center p-0">
               <img class="delete-icon mb-3"
                    src="{{ asset('public/assets/imgs/administrator-delete-icon.svg') }}">
               <h5 class="mb-3 fw-normal">Delete this Job Work</h5>
               <p class="font-14 text-body">
                  This process cannot be undone.
               </p>
            </div>

            <input type="hidden" name="job_work_id" id="job_work_id">

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button"
                       class="btn btn-border-body cancel-jobwork">
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
$("#search").keyup(function () {
   var value = this.value.toLowerCase();
   $(".sale_table tr").each(function (index) {
      if (!index) return;
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
   });
});
$(document).on('click', '.delete-jobwork', function () {
   let id = $(this).data('id');
   $('#job_work_id').val(id);
   $('#delete_jobwork').modal('show');
});

$('.cancel-jobwork').click(function () {
   $('#delete_jobwork').modal('hide');
});

</script>

@endsection