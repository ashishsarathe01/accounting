@extends('admin-module.layouts.app')
@section('content')
<!-- header-section -->
@include('admin-module.layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if(session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Merchant</li>
               </ol>
            </nav>
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">List of Merchant</h5>
               <a href="{{ route('account.create') }}" class="btn btn-xs-primary">
                  ADD
                  <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                     <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                  </svg>
               </a>
            </div>
            <div class="bg-white table-view shadow-sm">
               <table id="example" class="table-striped table m-0 shadow-sm">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Mobile</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Email</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Status</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($merchants as $merchant)
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120">{{$merchant->name}}</td>
                           <td class="w-min-120">{{$merchant->mobile_no}}</td>
                           <td class="w-min-120">{{$merchant->email}}</td>
                           <td class="w-min-120">
                              <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                 @if($merchant->status=='1')
                                    Enable
                                 @else
                                    Disable
                                 @endif
                              </span>
                           </td>
                           <td class="w-min-120 text-center">
                              <a href="{{ URL::to('admin/merchant/'.$merchant->id.'/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                              <button type="button" class="border-0 bg-transparent delete" data-id="">
                                 <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
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
</body>
@include('layouts.footer')
<script>
    $(document).ready(function() {

        $(".delete").click(function() {
            var id = $(this).attr("data-id");
            $("#group_id").val(id);
            $("#delete_heading").modal("show");
        });

        $(".cancel").click(function() {

            $("#delete_heading").modal("hide");
        });
    });
</script>
@endsection