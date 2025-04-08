@extends('admin-module.layouts.app')
@section('content')
<!-- header-section -->
@include('admin-module.layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Account Heading</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">edit Account Heading</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="post" action="{{ route('admin.account-head.update/'.$editheading->id) }}">
               @csrf
               <input type="hidden" value="{{ $editheading->id }}" id="heading_id" name="heading_id" />
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Name</label>
                     <input type="text" class="form-control" value="{{ $editheading->name }}" id="name" name="name" placeholder="Enter name" required />
                   </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">B/S Profile</label>
                     <select class="form-select" id="bs_profile" name="bs_profile" required>
                        <option value="">Select Profile</option>
                        <option <?php echo $editheading->bs_profile =='1' ? 'selected':'';?> value="1">Liabilities</option>
                        <option <?php echo $editheading->bs_profile =='2' ? 'selected':'';?> value="2">Assets</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4"></div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Name As Sch |||</label>
                     <input type="text" class="form-control" value="{{ $editheading->name_sch_three }}" name="name_sch_three" id="name_sch_three" placeholder="Enter name" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">B/S Profile As Sch |||</label>
                     <select class="form-select" id="bs_profile_three" name="bs_profile_three" >
                        <option value="select">Select Profile</option>
                        <option <?php echo $editheading->bs_profile_three =='1' ? 'selected':'';?> value="1">Equity And Liabilities</option>
                        <option <?php echo $editheading->bs_profile_three =='2' ? 'selected':'';?> value="2">Assets</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option <?php echo $editheading->status ==1 ? 'selected':'';?> value="1">Enable</option>
                        <option <?php echo $editheading->status ==0 ? 'selected':'';?> value="0">Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary ">SUBMIT</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
</html>
@endsection