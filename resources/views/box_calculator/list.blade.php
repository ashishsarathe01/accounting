@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">


@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif


<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

<div class="d-flex align-items-center justify-content-between">

    <h5 class="transaction-table-title m-0">
        Box Calculator List
    </h5>

    <div class="d-flex align-items-center">

    <a href="{{ route('box-calculator.configuration') }}"
       class="btn btn-xs-primary text-nowrap me-2">

        CONFIGURATION

    </a>

    <a href="{{ route('box-calculator.advance') }}"
       class="btn btn-xs-primary text-nowrap">

        ADD BOX

    </a>

</div>

</div>

</div>


<div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

<table class="table table-bordered table-striped m-0">

<thead>

<tr class="bg-light-pink text-body">

    <th>Box Name</th>

    <th>Ply</th>

    <th>Dimensions</th>

    <th>Sale Price</th>

    <th class="text-center">Action</th>

</tr>

</thead>

<tbody>

@forelse($boxes as $box)

<tr>

    <td>{{ $box->box_name }}</td>

    <td>{{ $box->ply }} Ply</td>

    <td>
        {{ $box->length }}
        ×
        {{ $box->width }}
        ×
        {{ $box->height }}
        {{ $box->input_unit }}
    </td>

    <td>
        ₹ {{ number_format($box->sale_with_gst,2) }}
    </td>

    <td class="text-center">

        <a href="{{ route('box-calculator.edit',$box->id) }}">

            <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}"
                 class="px-1">

        </a>

        <button class="border-0 bg-transparent delete_btn"
                data-id="{{ $box->id }}">

            <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}"
                 class="px-1">

        </button>

    </td>

</tr>
@empty

<tr>

    <td colspan="5" class="text-center">

        No Boxes Found

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


<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">

         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" id="deleteForm">
            @csrf
            @method('DELETE')

            <div class="modal-body text-center p-0">

               <button class="border-0 bg-transparent" type="button">
                  <img class="delete-icon mb-3 d-block mx-auto"
                       src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg') }}">
               </button>

               <h5 class="mb-3 fw-normal">Delete this record</h5>

               <p class="font-14 text-body">
                  Do you really want to delete these records? this process cannot be undone.
               </p>

            </div>

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>

         </form>

      </div>
   </div>
</div>


@include('layouts.footer')


<script>

    $(document).on("click", ".delete_btn", function () {

        let id = $(this).data("id");

        let url =
        "{{ route('box-calculator.delete', ':id') }}"
        .replace(':id', id);

        $("#deleteForm").attr('action', url);

        $("#deleteModal").modal('show');
    });


    $(document).on("click", ".cancel", function () {

        $("#deleteModal").modal('hide');

    });
</script>

@endsection