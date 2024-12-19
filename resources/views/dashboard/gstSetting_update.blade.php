@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">{{session('success')}}</div>
            @endif
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="{{ URL::asset('public/assets/imgs/right-img.svg')}}" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">GST Configuration</li>
               </ol>
            </nav>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="master-table-title m-0 py-2">GST Configuration</h5>
            </div>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('gst-setting.update') }}">
               @csrf
               <div class="bg-white px-4 py-4 border-divider rounded-bottom-8 shadow-sm mb-4">
                  <?php
                  $total_gst_count = 0;
                  $total_gst_count = $gstConfigData->count();
                  ?>
                  @foreach ($gstConfigData as $index=>$value)
                     <div class="row">
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">GST Type</label>
                           <select class="form-select form-select-lg " id="gst_type" name="gst_type" aria-label="form-select-lg example"  >
                              <option value="">Select </option>
                              <option value="single_gst" @if($value->gst_type=="single_gst") selected @endif >Single GST</option>
                              <option value="multiple_gst" @if($value->gst_type=="multiple_gst") selected @endif>Multiple GST</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">GST No.</label>
                           <input type="text" class="form-control" value="{{$value->gst_no }}" id="gst_no" name="gst_no[{{$index}}][]" placeholder="Enter here" / >
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Business Type</label>
                           <select class="form-select form-select-lg" name="business_type[{{$index}}][]" aria-label="form-select-lg example" >
                              <option selected>Select </option>
                              <option <?php echo $value->business_type == 1 ? 'selected' : ''; ?> value="1">Properitor</option>
                              <option <?php echo $value->business_type == 2 ? 'selected' : ''; ?> value="2">Partnership</option>
                              <option <?php echo $value->business_type == 3 ? 'selected' : ''; ?> value="3">Company Pvt.Ltd.</option>
                           </select>
                        </div>
                        <div class="calender-administrator mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Validity From</label>
                           <input type="date" id="validity_from" name="validity_from[{{$index}}][]" value="{{$value->validity_from }}" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" >
                        </div>
                        <div class="calender-administrator mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Validity To</label>
                           <input type="date" id="validity_to" name="validity_to[{{$index}}][]" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" value="{{$value->validity_to}}" >
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Address</label>
                           <input type="text" class="form-control" id="address" value="{{$value->address }}" name="address[{{$index}}][]" placeholder="Enter address"  />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">State</label>
                           <select class="form-select form-select-lg" name="state[{{$index}}][]" id="state" aria-label="form-select-lg example" >
                              <option value="">Select </option>
                              <?php
                              foreach ($state_list as $val) {
                                 $sel = '';
                                 if ($value->state == $val->id)
                                    $sel = 'selected'; ?>
                                 <option <?php echo $sel; ?> value="<?php echo $val->id; ?>"><?php echo $val->name; ?></option>
                                 <?php 
                              } ?>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Pincode</label>
                           <input type="text" class="form-control" id="pincode" value="{{$value->pincode }}" name="pincode[{{$index}}][]" placeholder="Enter pincode"  />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Scheme</label>
                           <select class="form-select form-select-lg" name="scheme[{{$index}}][]" aria-label="form-select-lg example" >
                              <option value="">Select </option>
                              <option value="regular" @if($value->scheme=="regular") selected @endif>Regular</option>
                              <option value="composition" @if($value->scheme=="composition") selected @endif>Composition</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label class="w-100">Add GST Certificate *
                              <div class="border-divider border-radius-8 d-flex mt-6 cursor-pointer">
                                 <span class="transaction-select-opacity py-12 ps-3">choose file</span>
                                 <div class="ms-auto py-12 px-3 bg-pink">
                                    <span class="font-heading fw-bold font-14">Browse</span>
                                    <img src="{{ URL::asset('public/assets/imgs/upload-icon.svg')}}" class="">
                                 </div>
                                 <input type="file" class="d-none" name="gst_certificate[{{$index}}][]" >
                              </div>
                           </label>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Mat Center</label>
                           <input type="text" class="form-control" name="mat_center[{{$index}}][]" placeholder="Enter mat center" value="{{$value->mat_center }}"  />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Series</label>
                           <input type="text" class="form-control" name="series[{{$index}}][]" placeholder="Enter series" value="{{$value->series }}"  />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Invoice Start from</label>
                           <input type="text" class="form-control" name="invoice_start_from[{{$index}}][]" placeholder="Enter " value="{{$value->invoice_start_from }}"  />
                        </div>
                     </div>                        
                     <!-- E - Invoice -->
                     <label for="" class="form-label font-14 font-heading pb-2">E - Invoice</label>
                     <div class="d-sm-flex mb-4">
                        <div class="me-4">
                           <input type="radio" class="custom-radio-input me-2 e-invoice-show" name="e_invoice[{{$index}}][]"  id="flexRadioDefault1" value="1" @if($value->einvoice==1) checked @endif>
                           <label for="flexRadioDefault1" class="custom-radio-label pl-32 ">Yes</label>
                        </div>
                        <div class="">
                           <input type="radio" class="custom-radio-input e-invoice-hide" name="e_invoice[{{$index}}][]"  id="flexRadioDefault2" value="0" @if($value->einvoice==0) checked @endif>
                           <label for="flexRadioDefault2" class="custom-radio-label pl-32 ">No</label>
                        </div>
                     </div>
                     @if($value->einvoice==1)
                     <div class="row e-invoice-yes">
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Username (Invoice Portal)</label>
                           <input type="text" class="form-control"  id="einvoice_username" name="einvoice_username[{{$index}}][]" placeholder="Enter username" value="{{$value->einvoice_username }}" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Password</label>
                           <input type="password" class="form-control"  id="einvoice_password" name="einvoice_password[{{$index}}][]" placeholder="Enter password" value="{{$value->einvoice_password }}" />
                        </div>
                     </div>
                     @endif
                     <!-- E--- Way Bill -->
                     <label for="" class="form-label font-14 font-heading pb-2">E - Way Bill</label>
                     <div class="d-sm-flex mb-4">
                        <div class="me-4">
                           <input type="radio" class="custom-radio-input me-2 e-waybill-show" name="e_way_bill[{{$index}}][]"  id="flexRadioDefault3" value="1" @if($value->ewaybill==1) checked @endif>
                           <label for="flexRadioDefault3" class="custom-radio-label pl-32">Yes</label>
                        </div>
                        <div class="">
                           <input type="radio" class="custom-radio-input e-waybill-hide" name="e_way_bill[{{$index}}][]"  id="flexRadioDefault4" value="0" @if($value->ewaybill==0) checked @endif>
                           <label for="flexRadioDefault4" class="custom-radio-label pl-32">No</label>
                        </div>
                     </div>
                     @if($value->ewaybill==1)
                     <div class="row e-waybill-yes">
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Username (Invoice Portal)</label>
                           <input type="text" class="form-control"  id="ewaybill_username" name="ewaybill_username[{{$index}}][]" placeholder="Enter username" value="{{$value->ewaybill_username }}" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Password</label>
                           <input type="password" class="form-control"  id="ewaybill_password" name="ewaybill_password[{{$index}}][]" placeholder="Enter password" value="{{$value->ewaybill_password }}" />
                        </div>
                     </div>
                     @endif
                     <!-- addbranch toggle -->
                     <div id="add_branch_{{$index}}" class="add-branch">
                        @php
                        $multiple_branch_count = 0;
                        $single_branch_count = 0;
                        if($value->gst_type=="single_gst"){
                           $branchData = DB::table('gst_branches')->where('gst_setting_id',$value->id)->get();
                           $single_branch_count = $branchData->count();
                        }else if($value->gst_type=="multiple_gst"){
                           $branchData = DB::table('gst_branches')->where('gst_setting_multiple_id',$value->id)->get();
                           $multiple_branch_count =   $branchData->count();
                        }
                        @endphp
                        @if($branchData->count()>0)
                           @foreach($branchData as $branchs)
                              <div class="row" id="branch_{{$index}}"><h6 class="font-heading mb-4 font-14">Branch</h6><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Address</label><input type="text" class="form-control"  placeholder="Enter address" name="branch_address[{{$index}}][]" value="{{$branchs->branch_address}}" id="branch_address"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">City</label><input class="form-control"  name="branch_city[{{$index}}][]" value="{{$branchs->branch_city}}" id="branch_city" aria-label="form-select-lg example"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Pincode</label><input type="text" class="form-control"  name="branch_pincode[{{$index}}][]" id="branch_pincode" value="{{$branchs->branch_pincode}}" placeholder="Enter pincode" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Mat Center</label><input type="text"  class="form-control" name="branch_matcenter[{{$index}}][]" value="{{$branchs->branch_matcenter}}" id="branch_matcenter" placeholder="Enter mat center" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Series</label><input type="text" class="form-control" name="branch_series[{{$index}}][]"  value="{{$branchs->branch_series}}" id="branch_series" placeholder="Enter series" /></div><div class="mb-3 col-md-3"><label for="name" class="form-label font-14 font-heading">Invoice Start from</label><input type="text" class="form-control"  name="branch_invoice_start_from[{{$index}}][]" id="branch_invoice_start_from" placeholder="Enter " value="{{$branchs->branch_invoice_start_from}}" /></div><div class="mb-1 col-md-1 remove_div" style="margin-top:30px;"></div></div>
                           @endforeach
                         @endif
                     </div>                    
                  @endforeach
                  <a onclick="add_more_branch('{{$index}}',0);" class="add_more_branch btn btn-sm-black d-block ms-auto add-branch-toogle">ADD BRANCH
                  </a>
                  <div id="append_add_more_branch"></div>
               </div>
               <a style="display: none;" class="btn btn-secondary add_more_gst">ADD GST
                   <svg class="ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                       <path d="M9.16663 15.8334V10.8334H4.16663V9.16675H9.16663V4.16675H10.8333V9.16675H15.8333V10.8334H10.8333V15.8334H9.16663Z" fill="white" />
                   </svg>
               </a>
               <div class="text-start py-4">
                   <input type="submit" value="SUBMIT" class="btn btn-xs-primary">
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
    var add_more_counts = 0;
    function add_more_branch(id,setting_index) {
       // alert(add_more_counts);
        //var add_more_count = id;
        //$(".add_more_branch").click(function() {
        //let id = $(this).attr('data-id');
        //alert(id);
        add_more_counts++;
       // id = add_more_counts;

        newRow = '<div class="row" id="branch_' + add_more_counts + '"><h6 class="font-heading mb-4 font-14">Branch</h6><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Address</label><input type="text" class="form-control" placeholder="Enter address" name="branch_address['+setting_index+'][]" id="branch_address"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">City</label><input class="form-control" name="branch_city['+setting_index+'][]" id="branch_city" aria-label="form-select-lg example"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Pincode</label><input type="text" class="form-control" name="branch_pincode['+setting_index+'][]" id="branch_pincode" placeholder="Enter pincode" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Mat Center</label><input type="text" class="form-control" name="branch_matcenter['+setting_index+'][]" id="branch_matcenter" placeholder="Enter mat center" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Series</label><input type="text" class="form-control" name="branch_series['+setting_index+'][]" id="branch_series" placeholder="Enter series" /></div><div class="mb-3 col-md-3"><label for="name" class="form-label font-14 font-heading">Invoice Start from</label><input type="text" class="form-control"  name="branch_invoice_start_from['+setting_index+'][]" id="branch_invoice_start_from" placeholder="Enter " /></div><div class="mb-1 col-md-1 remove_div" style="margin-top:30px;"><a class="btn btn-danger"  onclick="remove_branch(' + add_more_counts + ');">Remove</a></div></div>';
        
        setting_index++;
        $("#add_branch_" + id).append(newRow);
    }

    var add_more_multiple_counts = 0;
    function add_more_branch_multiple(id,setting_index) 
    {
        //var add_more_count = id;
        add_more_multiple_counts++;
        
        newRow = '<div class="row" id="branch_multiple_' + add_more_multiple_counts + '"><h6 class="font-heading mb-4 font-14">Branch</h6><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Address</label><input type="text" class="form-control" placeholder="Enter address" name="branch_address['+setting_index+'][]" id="branch_address"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">City</label><input class="form-control" name="branch_city['+setting_index+'][]" id="branch_city" aria-label="form-select-lg example"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Pincode</label><input type="text" class="form-control" name="branch_pincode['+setting_index+'][]" id="branch_pincode" placeholder="Enter pincode" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Mat Center</label><input type="text" class="form-control" name="branch_matcenter['+setting_index+'][]" id="branch_matcenter" placeholder="Enter mat center" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Series</label><input type="text" class="form-control" name="branch_series['+setting_index+'][]" id="branch_series" placeholder="Enter series" /></div><div class="mb-3 col-md-3"><label for="name" class="form-label font-14 font-heading">Invoice Start from</label><input type="text" class="form-control"  name="branch_invoice_start_from['+setting_index+'][]" id="branch_invoice_start_from" placeholder="Enter " /></div><div class="mb-1 col-md-1 remove_div" style="margin-top:30px;"><a class="btn btn-danger"  onclick="remove_branch_multiple(' + add_more_multiple_counts + ');">Remove</a></div></div>';
        setting_index++;
        $("#add_branch_multiple_" + id).append(newRow);
    }

    // });


    var add_more = '{{$index}}';
    $(".add_more_gst").click(function() {
      add_more++;
        var add_more_gst = '<div id="add_multiple_branch_html" class="bg-white px-4 py-4 border-divider border-radius-8 shadow-sm multipal-show "><div class="row"><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">GST No.</label><input type="text" class="form-control" name="gst_no['+add_more+'][]" id="name" placeholder="Enter here" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Business Type</label><select class="form-select form-select-lg " aria-label="form-select-lg example" name="business_type['+add_more+'][]" ><option selected>Select </option><option value="1">Properitor</option><option value="2">Partnership</option><option value="3">Pvt.Ltd.</option></select></div><div class="calender-administrator mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Validity From</label><input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" name="validity_from['+add_more+'][]"></div><div class="calender-administrator mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Validity To</label><input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" name="validity_to['+add_more+'][]" placeholder="To date"></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Address</label><input type="text" class="form-control" id="name" name="address['+add_more+'][]" placeholder="Enter address" /></div><div class="mb-4 col-md-4"><label for="contact-number" class="form-label font-14 font-heading">State</label><select class="form-select form-select-lg " aria-label="form-select-lg example" name="state['+add_more+'][]"><option selected>Select </option><option>Andaman and Nicobar Islands</option><option>Andhra Pradesh</option><option>Arunachal Pradesh</option><option>Assam, Bihar</option><option>Chandigarh</option><option>Chattisgarh</option></select></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Pincode</label><input type="text" class="form-control" id="name" name="pincode['+add_more+'][]" placeholder="Enter pincode" /></div><div class="mb-4 col-md-4"><label for="contact-number" class="form-label font-14 font-heading">Scheme</label><select class="form-select form-select-lg " aria-label="form-select-lg example" name="scheme['+add_more+'][]"><option selected>Select </option><option value="regular">Regular</option><option value="composition">Composition</option></select></div><div class="mb-4 col-md-4"><label class="w-100">Add GST Certificate *<div class="border-divider border-radius-8 d-flex mt-6 cursor-pointer"><span class="transaction-select-opacity py-12 ps-3">choose file</span><div class="ms-auto py-12 px-3 bg-pink"><span class="font-heading fw-bold font-14">Browse</span><img src="/assets/imgs/upload-icon.svg" class=""></div><input type="file" name="gst_certificate['+add_more+'][]" class="d-none" name="myfile"></div></label></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Mat Center</label><input type="text" class="form-control" name="mat_center['+add_more+'][]" placeholder="Enter mat center" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Series</label><input type="text" name="series['+add_more+'][]" class="form-control" placeholder="Enter series" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Invoice Start from</label><input type="text" class="form-control" placeholder="Enter " name="invoice_start_from['+add_more+'][]" /></div></div><label for="" class="form-label font-14 font-heading pb-2">E - Invoice</label><div class="d-sm-flex mb-4"><div class="me-4"><input type="radio" class="custom-radio-input me-2 e-invoice-show-two" name="e_invoice['+add_more+'][]" value="1" id="flexDefault1"><label for="flexDefault1" class="custom-radio-label pl-32 ">Yes</label></div><div class=""><input type="radio" class="custom-radio-input e-invoice-hide-two" name="e_invoice['+add_more+'][]" id="flexDefault2" value="0" checked><label for="flexDefault2" class="custom-radio-label pl-32 ">No</label></div></div><div class="row e-invoice-yes-two"><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Username (Invoice Portal)</label><input type="text" class="form-control" id="name" placeholder="Enter username" name="einvoice_username['+add_more+'][]" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Password</label><input type="password" class="form-control" id="name" name="einvoice_password['+add_more+'][]" placeholder="Enter password" /></div></div><label for="" class="form-label font-14 font-heading pb-2">E - Way Bill</label><div class="d-sm-flex mb-4"><div class="me-4"><input type="radio" class="custom-radio-input me-2 e-waybill-show-two" name="e_way_bill['+add_more+'][]" value="1" id="flexRadioDefaul3"><label for="flexRadioDefaul3" class="custom-radio-label pl-32">Yes</label></div><div class=""><input type="radio" class="custom-radio-input e-waybill-hide-two" name="e_way_bill['+add_more+'][]" id="flexRadioDefaul4" value="0" checked> <label for="flexRadioDefaul4" class="custom-radio-label pl-32">No</label></div></div><div class="row e-waybill-yes-two"><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Username (Invoice Portal)</label><input type="text" class="form-control" id="name" placeholder="Enter username" name="ewaybill_username['+add_more+'][]" /></div><div class="mb-4 col-md-4"><label for="name" class="form-label font-14 font-heading">Password</label><input type="password" class="form-control" id="name" placeholder="Enter password" name="ewaybill_password['+add_more+'][]"/></div></div><div id="add_branch_multiple_' + add_more + '" class="add-branch11"></div><a onclick="add_more_branch_multiple(1,' + add_more + ');" class="add_more_branch btn btn-sm-black d-block ms-auto add-branch-toogle">ADD BRANCH</a></div></div>';
        
        $("#append_add_more_branch").append(add_more_gst);
    });

    function remove_branch(id) {
        //alert(id);
        $("#branch_" + id).remove();
    }

    function remove_branch_multiple(id) {
        //alert(id);
        $("#branch_multiple_" + id).remove();
    }

    $(document).on("change", "#gst_type", function() {
        let type = $(this).val();
        if (type == 'multiple_gst') {
            $('.add_more_gst').show();
        } else {
            $('.add_more_gst').hide();
        }
    });


    $('.e-invoice-show').change(function() 
    {
        //alert(this.value);
        $('.e-invoice-yes').css('display','flex');
    });

    $('.e-invoice-hide').change(function() 
    {
        $('.e-invoice-yes').css('display','none');
    });

    $('.e-waybill-show').change(function() 
    {
        //alert(this.value);
        $('.e-waybill-yes').css('display','flex');
    });

    $('.e-waybill-hide').change(function() 
    {
        $('.e-waybill-yes').css('display','none');
    });
    

    $(document).on("click", ".remove11", function() {
        let id = $(this).attr('data-id');
        $("#branch_" + id).remove();
        var max_val = $("#max_sale_descrption").val();
        max_val--;
        $("#max_sale_descrption").val(max_val);
        //calculateAmount();
    });
</script>
@endsection