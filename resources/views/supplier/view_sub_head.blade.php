@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}} </div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif            
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">
                  List of Sub Head
               </h5>
                @can('view-module', 102)
                    <a href="{{ route('supplier-sub-head.create') }}" class="btn btn-xs-primary">ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                @endcan
               
               
            </div>
            <div class="   bg-white table-view shadow-sm">
               <table id="example" class="table-striped table m-0 shadow-sm">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Group</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Sequence</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Staus</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach ($heads as $value)
                        <tr>
                            <td class="w-min-120">{{$value->name}}</td>
                            <td class="w-min-120">{{$value->group->group_name}}</td>
                            <td class="w-min-120">{{$value->sequence}}</td>
                            <td class="w-min-120">@if($value->status==1) Enable @else Disable @endif</td>
                            <td class="w-min-120 text-center">
                                @can('view-module', 103)
                                <a href="{{ URL::to('supplier-sub-head/' . $value->id . '/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                @endcan
                                {{-- <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->id; ?>">
                                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                </button> --}}
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
<div class="modal fade" id="delete_subhead" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog w-360  modal-dialog-centered  ">
            <div class="modal-content p-4 border-divider border-radius-8">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="" method="POST" action="">
                    @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <input type="hidden" value="" id="heading_id" name="heading_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
@include('layouts.footer')
<script>
    $(document).ready(function() {
        $(".delete").click(function() {
            var id = $(this).attr("data-id");
            $("#heading_id").val(id);
            $("#delete_heading").modal("show");
        });

        $(".cancel").click(function() {
           
            $("#delete_heading").modal("hide");
        });
    });
</script>
@endsection