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
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Account Group</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Edit Account Group
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-group.update') }}">
               @csrf
               <input type="hidden" value="{{ $editGroup->id }}" id="group_id" name="group_id" />
               <div class="row">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Name</label>
                     <input type="text" class="form-control" value="{{ $editGroup->name }}" name="name" id="name" placeholder="Enter name" required />
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Primary</label>
                     <select class="form-select primary_sel" id="primary" name="primary" required>
                        <option value="">Select</option>
                        <option <?php echo $editGroup->primary =='Yes' ? 'selected':'';?> value="Yes">Yes</option>
                        <option <?php echo $editGroup->primary =='No' ? 'selected':'';?> value="No">No</option>
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Heading</label>
                     <select class="form-select" id="heading" name="heading" <?php echo $editGroup->primary =='No' ? '':'disabled';?> >
                        <option value="">Select Heading</option>
                        <?php
                        foreach($heading as $value){?>
                           <option <?php if($editGroup->heading == $value->id && $editGroup->heading_type == "head"){ echo 'selected';} ?> value="<?php echo $value->id; ?>" data-type="head"><?php echo $value->name; ?></option>
                              <?php 
                        } ?>
                        <?php
                        foreach ($accountgroup as $value) {?>
                           <option <?php if($editGroup->heading == $value->id && $editGroup->heading_type == "group"){ echo 'selected';} ?> value="<?php echo $value->id; ?>" data-type="group"><?php echo $value->name; ?></option>
                           <?php 
                        } ?>
                     </select>
                     <input type="hidden" name="heading_type" id="heading_type" value="{{$editGroup->heading_type}}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">B/S Profile</label>
                     <select class="form-select" id="bs_profile" name="bs_profile" disabled>
                        <option value="select">Select Profile</option>
                        <option <?php echo $editGroup->bs_profile =='1' ? 'selected':'';?> value="1">Liabilities</option>
                        <option <?php echo $editGroup->bs_profile =='2' ? 'selected':'';?> value="2">Assets</option>
                     </select>
                  </div>
                  @if(Session::get('business_type')==2)
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Name As Sch ||| </label>
                     <input type="text" class="form-control" value="{{ $editGroup->name_as_sch }}" name="name_as_sch" id="name_as_sch" placeholder="Enter name" />
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="contact-number" class="form-label font-14 font-heading">Primary As Sch |||
                     </label>
                     <select class="form-select getHeadingSch" id="primary_as_sch" name="primary_as_sch">
                        <option value="">Select</option>
                        <option <?php echo $editGroup->primary_as_sch =='Yes' ? 'selected':'';?> value="Yes">Yes</option>
                        <option <?php echo $editGroup->primary_as_sch =='No' ? 'selected':'';?> value="No">No</option>
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Heading As Sch |||</label>
                     <select class="form-select " id="heading_as_sch" name="heading_as_sch" <?php echo $editGroup->primary_as_sch =='No' ? '':'disabled';?>>
                        <option value="">Select</option>
                        <?php
                        foreach ($heading as $value) { 
                           $sel='';
                           if($editGroup->heading_as_sch == $value->id) 
                              $sel= 'selected';?>
                              <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name_sch_three; ?></option>
                              <?php 
                        } ?>
                        <?php
                        foreach ($accountgroup as $value) { ?>
                            <option value="<?php echo $value->id; ?>" data-type="group"><?php echo $value->name_as_sch; ?></option>
                        <?php } ?>
                     </select>
                     <input type="hidden" name="heading_as_sch_type" id="heading_as_sch_type" value="{{$editGroup->heading_as_sch_type}}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">B/S Profile As Sch |||</label>
                     <select class="form-select " id="bs_profile_as_sch" name="bs_profile_as_sch" disabled>
                        <option value="">Select</option>
                        <option <?php echo $editGroup->bs_profile_as_sch =='1' ? 'selected':'';?> value="1">Equity And Liabilities</option>
                        <option <?php echo $editGroup->bs_profile_as_sch =='2' ? 'selected':'';?> value="2">Assets</option>
                     </select>
                  </div>
                  @endif
                  <div class="mb-3 col-md-3">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option <?php echo $editGroup->status =='1' ? 'selected':'';?> value="1">Enable</option>
                        <option <?php echo $editGroup->status =='0' ? 'selected':'';?> value="0">Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary ">UPDATE</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      $(".primary_sel").change(function() {
         if ($("#primary").val() == "Yes") { 
            $("#heading").prop('disabled', true);
            $("#bs_profile").prop('disabled', false);
         }else if ($("#primary").val() == "No") {
            $("#heading").prop('disabled', false);
            $("#bs_profile").prop('disabled', true);
         }
      });        
      $(".getHeadingSch").change(function() { 
         if($("#primary_as_sch").val() == "Yes") {
            $("#heading_as_sch").prop('disabled', true);
            $("#bs_profile_as_sch").prop('disabled', false);
         }else if ($("#primary_as_sch").val() == "No") {
            $("#heading_as_sch").prop('disabled', false);
            $("#bs_profile_as_sch").prop('disabled', true);
         }
      });
      $("#heading").change(function(){
         $("#heading_type").val('');
         $("#heading_type").val($('option:selected',this).attr('data-type'));
      });
   });
</script>
@endsection