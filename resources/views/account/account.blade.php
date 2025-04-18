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
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Account Group</li>
               </ol>
            </nav>
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2">List of Account</h5>
               <form class="" method="Get" action="{{ route('account.index') }}" id="filter_form">
                  @csrf
                  <div class="modal-body text-center p-0">
                  <select class="form-select form-select-lg" name="filter" id="filter" aria-label="form-select-lg example">
                        <option value="">Filter</option>
                        <option value="Enable" @if(isset($_GET['filter']) && $_GET['filter']=="Enable") selected @endif>Enable</option>
                        <option value="Disable" @if(isset($_GET['filter']) && $_GET['filter']=="Disable") selected @endif>Disable</option>
                        <option value="InComplete" @if(isset($_GET['filter']) && $_GET['filter']=="InComplete") selected @endif>InComplete</option>
                     </select>
                  </div>
                  
                  
               </form>
               <a href="{{ url('add-account') }}" class="btn btn-xs-primary">
                  ADD
                  <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                     <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                  </svg>
               </a>
            </div>
            <div class="   bg-white table-view shadow-sm">
               <table id="example" class="table-striped table m-0 shadow-sm">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Print Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Group </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">GSTIN </th>                                
                        <th class="w-min-120 border-none bg-light-pink text-body ">Status </th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     foreach ($account as $value) { ?>
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120"><?php echo $value->account_name ?></td>
                           <td class="w-min-120"><?php echo $value->print_name ?></td>
                           <td class="w-min-120"><?php if($value->group_name!=""){ echo $value->group_name;}else if($value->head_name!=""){ echo $value->head_name; } ?></td>
                           <td class="w-min-120"><?php echo $value->gstin ?></td>
                           <td class="w-min-120">
                              <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                 <?php
                                 if ($value->status == 1)
                                    echo 'Enable';
                                 else
                                    echo 'Disable'; 
                                 ?>
                              </span>
                           </td>
                           <td class="w-min-120 text-center">
                              <?php 
                              if($value->company_id!=0){ ?>
                                 <a href="{{ URL::to('account/' . $value->id . '/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                 <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->id; ?>">
                                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                 </button>
                                 <?php
                              }
                              ?>                              
                           </td>
                        </tr>
                        <?php 
                     } ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="delete_heading" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('account.delete') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
            </div>
            <input type="hidden" value="" id="account_id" name="account_id" />
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')

<script>
   $(document).ready(function() {      
      $(".cancel").click(function(){
         $("#delete_heading").modal("toggle");
      });
      var table = $('#example').DataTable({
         "bDestroy": true,
         //stateSave: true,
        
      });
      $("#filter").change(function(){
         $("#filter_form").submit();
      });
   });
   $(document).on('click','.delete',function(){ 
      var id = $(this).attr("data-id");
      $("#account_id").val(id);
      $("#delete_heading").modal("toggle");
   });
</script>
@endsection