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
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">Edit Privileges</h5>
               <a href="{{ route('admin.merchant-privilege.create') }}" class="btn btn-xs-primary">
                  ADD
                  <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                     <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                  </svg>
               </a>
            </div>
            <div class="bg-white table-view shadow-sm">
               <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('admin.merchant-privilege.update',$privilege_for_edit->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="module_name" name="module_name" value="{{ $privilege_for_edit->module_name }}" placeholder="Enter name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Parent</label>
                            <select class="form-select" name="parent">
                                <option value="">Parent</option>
                                @foreach($privileges as $key => $value)
                                    <option value="{{$value->id}}" @if($value->id==$privilege_for_edit->parent_id) selected @endif>{{$value->module_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option <?php echo $privilege_for_edit->status ==1 ? 'selected':'';?> value="1">Enable</option>
                                <option <?php echo $privilege_for_edit->status ==0 ? 'selected':'';?> value="0">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            UPDATE
                        </button>
                    </div>
                </form>
            </div>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
             
   });
</script>
@endsection