@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- HEADER --}}
<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">Monthly Tasks</h5>

    <a href="{{ route('task.monthly.create') }}" class="btn btn-xs-primary">
        ADD
        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg"
             width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                  fill="white"/>
        </svg>
    </a>
</div>

{{-- TABLE SECTION --}}
<div class="transaction-table bg-white table-view shadow-sm">

<table class="table-striped table m-0 shadow-sm">

<thead>
<tr class="font-12 text-body bg-light-pink">
    <th>#</th>
    <th>Title</th>
    <th>Assigned To</th>
    <th>Start Day</th>
    <th>End Day</th>
    <th>Priority</th>
    <th class="text-center">Action</th>
</tr>
</thead>

<tbody>

@if(count($templates) > 0)

@foreach($templates as $key => $template)

<tr class="font-14 font-heading bg-white">

    <td>{{ $key+1 }}</td>

    <td>{{ $template->title }}</td>

    <td>{{ $template->assigned_user }}</td>

    <td>{{ $template->start_day }}</td>

    <td>{{ $template->end_day }}</td>

    <td>
        @if($template->priority == 'high')
            <span class="badge bg-danger">High</span>
        @elseif($template->priority == 'medium')
            <span class="badge bg-warning text-dark">Medium</span>
        @else
            <span class="badge bg-success">Low</span>
        @endif
    </td>

    <td class="text-center">

        {{-- EDIT --}}
        <a href="{{ route('task.monthly.edit',$template->id) }}"
           title="Edit Monthly Task">
            <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg') }}"
                 class="px-1"
                 alt="Edit">
        </a>

        {{-- DELETE --}}
        <button type="button"
                class="border-0 bg-transparent delete"
                data-id="{{ $template->id }}"
                title="Delete Monthly Task">
            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg') }}"
                 class="px-1"
                 alt="Delete">
        </button>

    </td>

</tr>

@endforeach

@else

<tr>
    <td colspan="7" class="text-center py-4">
        No Monthly Tasks Found
    </td>
</tr>

@endif

</tbody>
</table>

</div>

</div>
</div>
</div>
</section>
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="delete_monthly_task" tabindex="-1">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" action="{{ route('task.monthly.delete') }}">
            @csrf

            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto"
                       src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}"
                       alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this monthly task?</h5>
               <p class="font-14 text-body">
                   This will stop future monthly generation.
               </p>
            </div>

            <input type="hidden" id="monthly_task_id" name="monthly_task_id" />

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel"
                       data-bs-dismiss="modal">
                   CANCEL
               </button>
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
$(document).on('click','.delete',function(){
    var id = $(this).data("id");
    $("#monthly_task_id").val(id);
    $("#delete_monthly_task").modal("show");
});
</script>

@endsection
