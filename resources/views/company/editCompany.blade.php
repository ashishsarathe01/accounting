@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            @if($errors->any())
               <div class="alert alert-danger">
                  <ul>
                     @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                     @endforeach
                  </ul>
               </div>
            @endif
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">Edit Company Info</h5><br>
            <ul class="nav nav-fill nav-tabs" role="tablist">
               <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true"> Company</a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false">@if($company->business_type=='1') Proprietor @elseif($company->business_type=='2') Partner @elseif($company->business_type=='3') Director @endif </a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-2" data-bs-toggle="tab" href="#fill-tabpanel-2" role="tab" aria-controls="fill-tabpanel-2" aria-selected="false">Share Holder</a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-3" data-bs-toggle="tab" href="#fill-tabpanel-3" role="tab" aria-controls="fill-tabpanel-3" aria-selected="false">Bank</a>
               </li>
            </ul>
            <div class="tab-content pt-5" id="tab-content">
               <div class="tab-pane active" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                  <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-edit-company')}}">
                     @csrf
                     <div class="row">                        
                        <div class="mb-4 col-md-4">
                           <label class="form-label font-14 font-heading">Business Structure</label>
                           <select name="business_type" id="business_type" class="form-select form-select-lg mb-3" aria-label="form-select-lg example" required>
                              <option value="">Select</option>
                              <option value="1" @if($company->business_type=='1') selected @endif>Proprietorship</option>
                              <option value="2" @if($company->business_type=='2') selected @endif>Partnership</option>
                              <option value="3" @if($company->business_type=='3') selected @endif>Company (PVT LTD)</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">GST Applicable</label>
                           <select class="form-select" id="gst_applicable" name="gst_applicable" required>
                              <option value="">Select</option>
                              <option <?php echo $company->gst_applicable =='1' ? 'selected':'';?> value="1">Yes</option>
                              <option <?php echo $company->gst_applicable =='0' ? 'selected':'';?> value="0">No</option>
                           </select>
                        </div>
                        <div class="mb-3 col-md-3" id="gst_filed">
                           <label for="name" class="form-label font-14 font-heading">GSTN (If Yes)</label>
                           <input type="text" class="form-control" value="{{ $company->gst;}}" id="gst" name="gst" placeholder="Enter GSTN" />
                        </div>
                        <div class="mb-1 col-md-1" id="gst_filed1">
                            <button type="button" class="btn btn-info btn-sm validate_gst" style="margin-top: 26px;">Validate</button>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Firm’s Name</label>
                           <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $company->company_name;?>" placeholder="Enter firm’s name" required />
                        </div>
                        <input type="hidden" class="form-control" id="legal_name" name="legal_name" placeholder="Enter firm’s name" required value=""/>
                        <div class="mb-8 col-md-8">
                           <label for="name" class="form-label font-14 font-heading">Address</label>
                           <input type="text" class="form-control" id="address" name="address" value="{{ $company->address;}}" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">State</label>
                           <select class="form-select" id="state" name="state" required>
                              <option value="">Select</option>
                              <?php
                                foreach ($state_list as $val) { ?>
                                <option value="<?= $val->id;?>" data-state_code="{{$val->state_code}}" <?php echo $company->state ==$val->id ? 'selected':'';?>><?= $val->name;?></option>
                                <?php }?>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Pin Code</label>
                           <input type="number" class="form-control" id="pin_code" value="{{ $company->pin_code;}}" name="pin_code" placeholder="Enter pin code" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Country Name</label>
                           <select class="form-select" id="country_name" name="country_name" required>
                              <option value="">Select</option>
                              <option value="INDIA" <?php echo $company->country_name =='INDIA' ? 'selected':'';?>>INDIA</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">PAN</label>
                           <input type="text" class="form-control" id="pan" value="{{ $company->pan;}}" name="pan" placeholder="Enter PAN" />
                        </div>
                        <div class="mb-4 col-md-4 cin_div" style="display:none">
                            <label for="name" class="form-label font-14 font-heading">CIN</label>
                            <input type="text" class="form-control" id="cin" name="cin" placeholder="Enter CIN" value="{{ $company->cin;}}"/>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Date of Incorporation</label>
                           <input type="date" class="form-control" id="date_of_incorporation" name="date_of_incorporation" placeholder="Select date" value="{{ $company->date_of_incorporation;}}"/>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Books start From</label>
                           <input type="date" class="form-control" id="books_start_from" value="<?php echo date('Y-m-d', strtotime($company->books_start_from))?>" name="books_start_from" placeholder="Enter books start from" />
                        </div>
                        
                        
                        
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Current Financial Year</label>
                           <select class="form-select " id="current_finacial_year" name="current_finacial_year" required>
                              <option>Select</option>
                              <?php 
                              $y = 22;
                              while($y<=date('y')){
                                 $y1 = $y+1;
                                 ?>
                                 <option value="<?php echo $y."-".$y1;?>" <?php echo $company->current_finacial_year ==$y."-".$y1 ? 'selected':'';?>><?php echo $y."-".$y1;?></option>
                                 <?php
                                 $y++;
                              }
                              ?> 
                           </select>
                        </div>
                        
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Email ID</label>
                           <input type="email" class="form-control" id="email_id" value="{{ $company->email_id;}}" name="email_id" placeholder="Enter email ID" required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                           <div class="position-relative">
                              <input type="text" min="10" max="10" class="form-control" value="{{ $company->mobile_no;}}" id="mobile_no" name="mobile_no" placeholder="Enter mobile number" required />
                           </div>
                        </div>
                     </div>
                     <div class="text-end">
                        <input type="submit" value="UPDATE" class="btn btn-small-primary mb-4">
                     </div>
                  </form>
               </div>
               <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                  <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-owner') }}" id="owner_form" style="display: none;">
                     <input type="hidden" name="owner_id" id="owner_id">
                     <input type="hidden" name="company_id" value="{{$company->id}}">
                     @csrf
                     <div class="row">                        
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Name</label>
                           <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Enter name"  />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Father’s Name</label>
                           <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Enter father’s name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Date of Birth</label>
                           <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Address</label>
                           <input type="text" class="form-control" id="owner_address" name="address" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">PAN</label>
                           <input type="text" class="form-control" id="owner_pan" name="pan" placeholder="Enter PAN" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Designation</label>
                           <select class="form-select" id="designation" name="designation" required>
                              <option value="">Select</option>
                              <option value="proprietor">Proprietor</option>
                              <option value="partner">Partner</option>
                              <option value="director">Director</option>
                              <option value="authorised_signatory">Authorised Signatory</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4" id="dateofjoing_section">
                           <label for="name" class="form-label font-14 font-heading">Date of Joining</label>
                           <input type="date" class="form-control" id="date_of_joining" name="date_of_joining" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                           <div class="position-relative">
                              <input type="text" class="form-control" id="owner_mobile_no" name="mobile_no" placeholder="Enter mobile number" />
                           </div>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="name" class="form-label font-14 font-heading">Email ID</label>
                           <input type="email" class="form-control" id="owner_email_id" name="email_id" placeholder="Enter email ID" required />
                        </div>
                        <div class="mb-4 col-md-4" id="din_sectioon">
                           <label for="name" class="form-label font-14 font-heading">DIN</label>
                           <input type="text" class="form-control" id="din" name="din" placeholder="Enter DIN" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label class="form-label font-14 font-heading">Authorized Signatory</label>
                           <select class="form-select form-select-lg mb-3" name="authorized_signatory" id="authorized_signatory" aria-label="form-select-lg example">
                              <option selected>Select </option>
                              <option value="1">Yes</option>
                              <option value="0">No</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-4" id="share_per_div">
                           <label class="form-label font-14 font-heading">Share Percentage</label>
                           <input class="form-control form-select-lg mb-3" name="share_percentage" id="share_percentage" aria-label="form-select-lg example" placeholder="Enter Share Percentage">
                        </div>
                     </div>
                     <div class="text-start">
                        <input type="submit" value="SUBMIT" class="btn  btn-small-primary mb-4 owner_btn">
                     </div>
                  </form>
                  <div id="joint_patner_div">
                     <p class="font-14 fw-bold font-heading bg-white "><button class="btn btn-info add_new_owner">Add New Owner</button></p> 
                     <div class="overflow-auto">
                        <table class="table  table-bordered mb-4">
                           <thead>
                              <tr class="font-12 text-body bg-light-pink title-border-redius">
                                 <th class="w-120">Name</th>
                                 <th class="w-120">Father’s Name </th>
                                 @if($company->business_type!=1)
                                    <th class="w-120">Date of Joining</th>
                                 @endif
                                 <th class="w-120">Mo. No.</th>
                                 <th class="w-120 text-center">Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach($owner_data as $value)
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-120">{{$value->owner_name}}</td>
                                    <td class="w-120">{{$value->father_name}}</td>
                                    @if($company->business_type!= 1)
                                       <td class="w-120">{{$value->date_of_joining}}</td>
                                    @endif
                                    <td class="w-120">{{$value->mobile_no}}</td>
                                    <td class="text-center">
                                       <a target="__blank" href="{{ URL::to('view-owner/' . $value->id . '/') }}"><img src="public/assets/imgs/eye-icon.svg" class="px-1" alt=""></a>
                                       <img src="public/assets/imgs/edit-icon.svg" class="px-1 edit_owner"  data-id="{{$value->id}}" data-owner_name="{{$value->owner_name}}" data-father_name="{{$value->father_name}}" data-date_of_birth="{{$value->date_of_birth}}" data-address="{{$value->address}}" data-pan="{{$value->pan}}" data-designation="{{$value->designation}}" data-date_of_joining="{{$value->date_of_joining}}" data-mobile_no="{{$value->mobile_no}}" data-email_id="{{$value->email_id}}" data-din="{{$value->din}}" data-share_percentage="{{$value->share_percentage}}" data-authorized_signatory="{{$value->authorized_signatory}}" data-date_of_resigning="{{$value->date_of_resigning}}" alt="" style="cursor:pointer;">
                                       @if($company->business_type!= 1)
                                          <button type="button" data-id="<?php echo $value->id; ?>" class="border-0 bg-transparent delete_partner">
                                             <img src="public/assets/imgs/delete-icon.svg" alt="">
                                          </button>
                                       @endif
                                      </td>
                                  </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
                  @if($company->business_type!= 1 && !empty(json_decode($owner_delete_data, 1)))
                     <div id="resigned_patner_div">
                        <p class="font-14 fw-bold font-heading bg-white ">Resigned Partner/Director</p>
                        <div class="overflow-auto">
                           <table class="table table-bordered mb-4">
                              <thead>
                                 <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-120">Name</th>
                                    <th class="w-120">Father’s Name</th>
                                    <th class="w-120">Date of Joining</th>
                                    <th class="w-120">Date of Resigning</th>
                                    <th class="w-120">M.O.B No.</th>
                                    <th class="w-120 text-center">Action</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach($owner_delete_data as $value)
                                    <tr class="font-14 font-heading bg-white">
                                       <td class="w-120">{{$value->owner_name}}</td>
                                       <td class="w-120">{{$value->father_name}}</td>
                                       <td class="w-120">{{$value->date_of_joining}}</td>
                                       <td class="w-120">{{$value->date_of_resigning}}</td>
                                       <td class="w-120">{{$value->mobile_no}}</td>
                                       <td class="text-center">
                                          <a target="__blank" href="{{ URL::to('view-owner/' . $value->id . '/') }}"><img src="public/assets/imgs/eye-icon.svg" class="px-1" alt=""></a>
                                          <!-- <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt=""> -->
                                       </td>
                                    </tr>
                                 @endforeach
                              </tbody>
                           </table>
                        </div>
                     </div>
                  @endif                                    
               </div>
               <div class="tab-pane" id="fill-tabpanel-2" role="tabpanel" aria-labelledby="fill-tab-2">
                  @if($company->business_type==3)
                     <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-shareholder') }}" id="shareholder_form" style="display: none;">
                     <input type="hidden" name="company_id" value="{{$company->id}}">
                     <input type="hidden" name="shareholder_id" id="shareholder_id">
                        @csrf
                        <div class="row">                           
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">Name</label>
                              <input type="text" class="form-control" id="shareholders_name" name="shareholders_name" placeholder="Enter name" required />
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">Father’s Name</label>
                              <input type="text" class="form-control" id="shareholders_father_name" name="father_name" placeholder="Enter father’s name" required />
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">Date of Birth</label>
                              <input type="date" class="form-control" id="shareholders_date_of_birth" name="date_of_birth" placeholder="Select date" required />
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">Address</label>
                              <input type="text" class="form-control" id="shareholders_address" name="address" placeholder="Enter address" />
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">PAN</label>
                              <input type="text" class="form-control" id="shareholders_pan" name="pan" placeholder="Enter PAN" required />
                           </div>                       
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">No. of Share (Opening)</label>
                              <input type="text" class="form-control" id="no_of_share" name="no_of_share" placeholder="Select date" />
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                              <div class="position-relative">
                                 <input type="text" class="form-control" id="shareholders_mobile_no" name="mobile_no" placeholder="Enter mobile number" required />
                              </div>
                           </div>
                           <div class="mb-4 col-md-4">
                              <label for="name" class="form-label font-14 font-heading">Email ID</label>
                              <input type="email" class="form-control" id="shareholders_email_id" name="email_id" placeholder="Enter email ID" required />
                           </div>
                        </div>
                        <div class="text-start">
                           <button type="submit" class="btn btn-small-primary mb-4 shareholder_btn">SAVE</button>
                        </div>
                     </form>
                     <p class="font-14 fw-bold font-heading bg-white ">Share holding pattern <button class="btn btn-info add_new_shareholder">Add New Share Holder</button></p>
                     <div class="overflow-auto ">
                        <table class="table  mb-4 table-bordered">
                           <thead>
                              <tr class="font-12 text-body bg-light-pink title-border-redius">
                                 <th class="w-120">Name</th>
                                 <th class="w-120">Father’s Name </th>
                                 <th class="w-120">No. of Shares</th>
                                 <th class="w-120">M.O.B No.</th>
                                 <th class="w-120">Email ID</th>
                                 <th class="w-120 text-center">Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach($shareholder_data as $value)
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-120">{{$value->shareholders_name}}</td>
                                    <td class="w-120">{{$value->father_name}}</td>
                                    <td class="w-120">{{$value->no_of_share}}</td>
                                    <td class="w-120">{{$value->mobile_no}}</td>
                                    <td class="w-120">{{$value->email_id}}</td>
                                    <td class="text-center">
                                       <a target="__blank" href="{{ URL::to('view-shareholder/' . $value->id . '/') }}"><img src="public/assets/imgs/eye-icon.svg" class="px-1" alt=""></a>
                                       <img src="public/assets/imgs/edit-icon.svg" class="px-1 edit_shareholder" data-id="{{$value->id}}" data-shareholders_name="{{$value->shareholders_name}}" data-father_name="{{$value->father_name}}" data-no_of_share="{{$value->no_of_share}}" data-mobile_no="{{$value->mobile_no}}" data-email_id="{{$value->email_id}}" data-date_of_birth="{{$value->date_of_birth}}" data-pan="{{$value->pan}}" data-address="{{$value->address}}" alt="" style="cursor:pointer;">
                                       <img src="public/assets/imgs/round-move-up.svg" alt="">
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div> 
                  @else
                     <p class="font-14 fw-bold font-heading bg-white  ">Share holding pattern</p>
                     <div class="overflow-auto ">
                        <table class="table  mb-4 table-bordered">
                           <thead>
                              <tr class="font-12 text-body bg-light-pink title-border-redius">
                                 <th class="w-120">Name</th>
                                 <th class="w-120">Father’s Name </th>
                                 <th class="w-120">Share Percentage</th>
                                 <th class="w-120">M.O.B No.</th>
                                 <th class="w-120">Email ID</th>
                                 <th class="w-120 text-center">Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              @foreach($owner_data as $value)
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-120">{{$value->owner_name}}</td>
                                    <td class="w-120">{{$value->father_name}}</td>
                                    <td class="w-120">{{$value->share_percentage}}</td>
                                    <td class="w-120">{{$value->mobile_no}}</td>
                                    <td class="w-120">{{$value->email_id}}</td>
                                    <td class="text-center">
                                       <a target="__blank" href="{{ URL::to('view-owner/' . $value->id . '/') }}"><img src="public/assets/imgs/eye-icon.svg" class="px-1" alt=""></a>
                                       <img src="public/assets/imgs/round-move-up.svg" alt="">
                                    </td>
                                 </tr>
                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  @endif             
                  <div id='transfer_entries' style='display:none'>
                     <p class="font-14 fw-bold font-heading bg-white  ">Show here Transfer Entries</p>
                     <div class="overflow-auto ">
                        <table class="table table-bordered mb-4">
                           <thead>
                              <tr class="font-12 text-body bg-light-pink title-border-redius">
                                 <th class="w-120">Date of Transfer</th>
                                 <th class="w-120">Transfer From</th>
                                 <th class="w-120">No. of shares Transferred</th>
                                 <th class="w-120">Transfer To</th>
                                 <th class="w-120 text-center">Action</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-120">11/03/2019</td>
                                 <td class="w-120">John Doe</td>
                                 <td class="w-120">153</td>
                                 <td class="w-120">Andrew Doe</td>
                                 <td class="text-center">
                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-120">11/03/2019</td>
                                 <td class="w-120">John Doe</td>
                                 <td class="w-120">153</td>
                                 <td class="w-120">Andrew Doe</td>
                                 <td class="text-center">
                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>                  
               </div>
               <div class="tab-pane" id="fill-tabpanel-3" role="tabpanel" aria-labelledby="fill-tab-3">
                  <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-bank')}}" id="bank_form" style="display: none;">
                     <input type="hidden" name="bank_id" id="bank_id">
                     <input type="hidden" name="company_id" value="{{$company->id}}">
                     @csrf
                     <div class="row">
                        <div class="mb-4 col-md-4">
                           <label for="account_name" class="form-label font-14 font-heading">Account Name</label>
                           <input type="text" class="form-control" id="account_name" name="account_name" placeholder="Enter Account Name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="account_no" class="form-label font-14 font-heading">A/C no.</label>
                           <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Enter A/C no." required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="ifsc" class="form-label font-14 font-heading">IFSC Code</label>
                           <input type="text" class="form-control" id="ifsc" name="ifsc" placeholder="Enter IFSC code " required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="bank_name" class="form-label font-14 font-heading">Bank Name</label>
                           <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter bank name " required />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="branch" class="form-label font-14 font-heading">Branch</label>
                           <input type="text" class="form-control" id="branch" name="branch" placeholder="Enter branch name" required />
                        </div>
                     </div>
                     <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn  btn-small-primary mb-4 bank_btn">SUBMIT</button>
                     </div>
                  </form>
                  <p class="font-14 fw-bold font-heading bg-white ">Bank List <button class="btn btn-info add_new_bank">Add New Bank</button></p> 
                  <div class="overflow-auto">
                     <table class="table  mb-4 table-bordered">
                        <thead>
                           <tr class="font-12 text-body bg-light-pink title-border-redius">
                              <th class="w-120">A/C Name</th>
                              <th class="w-120">A/C No.</th>
                              <th class="w-120">IFSC Code </th>
                              <th class="w-120">Bank Name</th>
                              <th class="w-120">Branch</th>
                              <th class="w-120">Select for Display in Invoice</th>
                              <th class="w-120 text-center">Action</th>
                           </tr>
                        </thead>
                        <tbody>
                           
                           @foreach($bank_data as $value)
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-120">{{$value->name}}</td>
                                 <td class="w-120">{{$value->account_no}}</td>
                                 <td class="w-120">{{$value->ifsc}}</td>
                                 <td class="w-120">{{$value->bank_name}}</td>
                                 <td class="w-120">{{$value->branch}}</td>
                                 <td class="w-120"><input type="radio" name="primary_bank" class="primary_bank" data-id="{{$value->id}}" value="{{$value->primary_bank}}" @if($value->primary_bank==1) checked @endif></td>
                                 <td class="text-center">          
                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1 edit_bank" data-account_name="{{$value->name}}" data-account_no="{{$value->account_no}}" data-ifsc="{{$value->ifsc}}" data-bank_name="{{$value->bank_name}}" data-branch="{{$value->branch}}" data-id="{{$value->id}}" alt="" style="cursor:pointer;">
                                    <button type="button" data-id="{{$value->id}}" class="border-0 bg-transparent delete_bank">
                                       <img src="public/assets/imgs/delete-icon.svg" alt="">
                                    </button>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>            
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="resigningpopup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content w-460">
         <div class="modal-header py-12">
            <h5 class="modal-title" id="exampleModalLabel">Resigned Partner/Director</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-delete-owner') }}">
            @csrf
            <input type="hidden" name="del_id" id="del_id" value="">
            <div class="modal-body p-4">
               <div class=" position-relative">
                  <label for="name" class="form-label font-14 font-heading">Date of Resigning</label>
                  <input type="date" id="date_of_resigning" name="date_of_resigning" class="form-control" placeholder="Select date">
               </div>
            </div>
            <div class="modal-footer p-3">
               <button type="submit" class="btn btn-red w-100">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="delete_bank" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('delete-bank') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records.</p>
                </div>
                <input type="hidden" value="" id="bank_delete_id" name="bank_delete_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel_delete">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('layouts.footer')
<script type="text/javascript">
   
   var tab_id = "{{ Session::get('tab_id') }}";
   $(document).ready(function(){
      if(tab_id!="" && tab_id!=undefined){
         document.getElementById(tab_id).click();
      }
   })
   var business_type = "{{$company->business_type}}";
   if(business_type==3){
      $(".cin_div").show();
   }
   $("#business_type").change(function(){
        $(".cin_div").hide();
        if($(this).val()=="3"){
            $(".cin_div").show();
        }
    });
   $("#gst_applicable").on("keyup change", function(e) {
      if($("#gst_applicable").val() == 1) {
         $("#gst_filed").show();
         $("#gst_filed1").show();
      }else{
         $("#gst_filed").hide();
         $("#gst_filed1").hide();
      }
   });
   $("#gst").change(function() {
      var inputvalues = $("#gst").val();
      var gstinformat = new RegExp("^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9]{1}Z[a-zA-Z0-9]{1}$");
      if(gstinformat.test(inputvalues)) {
         var GstateCode = inputvalues.substr(0, 2);
         $('#state').val(GstateCode);
         $('#country_name').val('india');
         var GpanNum = inputvalues.substring(2, 12);
         $("#pan").val(GpanNum);
         var GEnd = inputvalues.substring(12, 14);
         return true;
      }else{
         alert('Please Enter Valid GSTIN Number');
         $("#gst").val('');
         $("#gst").focus();
      }
   });   
   $("#pin_code").change(function() {
      var inputvalues = $("#pin_code").val();
      var pincode = new RegExp("^[1-9]{1}[0-9]{2}\\s{0,1}[0-9]{3}$");
      if(pincode.test(inputvalues)) {
         return true;
      }else{
         alert('Please Enter Valid pin Number');
         $("#pin_code").val('');
         $("#pin_code").focus();
      }
   });
   $(".delete_partner").click(function() {
      var id = $(this).attr("data-id");
      $("#del_id").val(id);
      $("#resigningpopup").modal("show");
   });
   $("#pan").change(function() {
      var inputvalues = $("#pan").val();
      var paninformat = new RegExp("^[A-Z]{5}[0-9]{4}[A-Z]{1}$");
      if(paninformat.test(inputvalues)) {
         return true;
      }else{
         alert('Please Enter Valid PAN Number');
         $("#pan").val('');
         $("#pan").focus();
      }
   });
   setTimeout(function(){
      if(business_type == 1){
         $("#dateofjoing_section").hide();
         $("#din_sectioon").hide();
         $("#share_per_div").show();
         var html = '<option value="proprietor">Proprietor</option>';
         $("#designation").html('<option value="proprietor">Proprietor</option><option value="authorised_signatory">Authorised Signatory</option>');
      }else if(business_type == 2){
         $("#dateofjoing_section").show();
         $("#din_sectioon").hide();
         $("#share_per_div").show();
         $("#designation").html('<option value="partner">Partner</option><option value="authorised_signatory">Authorised Signatory</option>');
      }else{
         $("#dateofjoing_section").show();
         $("#din_sectioon").show();
         $("#share_per_div").hide();
         $("#designation").html('<option value="director">Director</option><option value="authorised_signatory">Authorised Signatory</option>');
      }
   }, 1000);
   $(".edit_bank").click(function(){
      $("#account_name").val($(this).attr('data-account_name'));
      $("#account_no").val($(this).attr('data-account_no'));
      $("#ifsc").val($(this).attr('data-ifsc'));
      $("#bank_name").val($(this).attr('data-bank_name'));
      $("#branch").val($(this).attr('data-branch'));
      $("#bank_id").val($(this).attr('data-id'));
      $("#bank_form").prop('action','{{ route("submit-edit-bank.update") }}');
      $(".bank_btn").html('EDIT');     
      $("#bank_form").show();  
   });
   $(".delete_bank").click(function(){      
      let id = $(this).attr('data-id');
      $("#bank_delete_id").val(id);
      $("#delete_bank").modal('toggle');
   });
   $(".cancel_delete").click(function(){
      $("#delete_bank").modal('toggle');
   });
   $(".edit_owner").click(function(){
      $("#owner_name").val($(this).attr('data-owner_name'));
      $("#father_name").val($(this).attr('data-father_name'));
      $("#date_of_birth").val($(this).attr('data-date_of_birth'));
      $("#owner_address").val($(this).attr('data-address'));
      $("#owner_pan").val($(this).attr('data-pan'));
      $("#designation").val($(this).attr('data-designation'));
      $("#date_of_joining").val($(this).attr('data-date_of_joining'));
      $("#owner_mobile_no").val($(this).attr('data-mobile_no'));
      $("#din").val($(this).attr('data-din'));
      $("#owner_email_id").val($(this).attr('data-email_id'));
      $("#authorized_signatory").val($(this).attr('data-authorized_signatory'));
      $("#share_percentage").val($(this).attr('data-share_percentage'));
      $("#owner_id").val($(this).attr('data-id'));
      $("#owner_form").prop('action','{{ route("submit-edit-owner.update") }}');
      $(".owner_btn").val('EDIT');    
      $("#owner_form").show();   
   });
   $(".edit_shareholder").click(function(){
      $("#shareholders_name").val($(this).attr('data-shareholders_name'));
      $("#shareholders_father_name").val($(this).attr('data-father_name'));
      $("#shareholders_date_of_birth").val($(this).attr('data-date_of_birth'));
      $("#shareholders_address").val($(this).attr('data-address'));
      $("#shareholders_pan").val($(this).attr('data-pan'));
      $("#no_of_share").val($(this).attr('data-no_of_share'));
      $("#shareholders_mobile_no").val($(this).attr('data-mobile_no'));
      $("#shareholders_email_id").val($(this).attr('data-email_id'));
      $("#shareholder_id").val($(this).attr('data-id'));
      $("#shareholder_form").prop('action','{{ route("submit-edit-shareholder.update") }}');
      $(".shareholder_btn").html('EDIT');    
      $("#shareholder_form").show();   
   });
   $(".primary_bank").click(function(){
      let id = $(this).attr('data-id');
      $.ajax({
         url : "{{url('set-primary-bank')}}",
         async: false,
         method : "post",
         data: {
            _token: '<?php echo csrf_token() ?>',
            id: id
         },
         success : function(res){
            if(res==1){
               alert("Updated Successfully.");
            }else{
               alert("Something went wrong.")
            }
         }
      });

   });
   $(".add_new_owner").click(function(){
      $('#owner_form')[0].reset();
      $("#owner_form").show();
   });
   $(".add_new_bank").click(function(){
      $('#bank_form')[0].reset();
      $("#bank_form").show();
   });
   $(".add_new_shareholder").click(function(){
      $('#shareholder_form')[0].reset();
      $("#shareholder_form").show();
   });
   $(".validate_gst").click(function() {
        $("#cover-spin").show();
        var inputvalues = $("#gst").val();
        var gstinformat = new RegExp("^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9]{1}Z[a-zA-Z0-9]{1}$");
        if (gstinformat.test(inputvalues)) {
            var GstateCode = inputvalues.substr(0, 2); 
            //$('#state').val(GstateCode);
            $('#country_name').val('INDIA');
            var GpanNum = inputvalues.substring(2, 12);
            $("#pan").val(GpanNum);
            var GEnd = inputvalues.substring(12,14);
            $("#pan").val("");
            $("#address").val("");
            $("#pin_code").val("");
            $("#state").val("");
            $.ajax({
                url: '{{url("check-gstin")}}',
                async: false,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    gstin: inputvalues
                },
                success: function(data) {
                    if(data!=""){
                        if(data.status==1){
                            var GstateCode = inputvalues.substr(0, 2);
                            $('#state [data-state_code = "'+GstateCode+'"]').prop('selected', true); 
                            var GpanNum = inputvalues.substring(2, 12);
                            $("#pan").val(GpanNum);
                            $("#address").val(data.address.toUpperCase());
                            $("#pin_code").val(data.pinCode);
                            $("#company_name").val(data.tradeName);
                             $("#legal_name").val(data.legalName);
                            $("#date_of_incorporation").val(data.DtReg);
                        }else if(data.status==0){
                            alert(data.message)
                        }
                        $("#cover-spin").hide();
                    }               
                }
            }); 
            return true;
        }else {
            alert('Please Enter Valid GSTIN Number');
            $("#gst").focus();
            $("#cover-spin").hide();
        }
    });
    $("#gst").keyup(function(){
        $("#gst").val($(this).val().toUpperCase());
    });
    $("#pan").keyup(function(){
            $("#pan").val($(this).val().toUpperCase());
        });
        $("#owner_pan").keyup(function(){
            $("#owner_pan").val($(this).val().toUpperCase());
        });
        
   $("#email_id").keyup(function(){
        $("#email_id").val($(this).val().toLowerCase());
    });
    $("#owner_email_id").keyup(function(){
        $("#owner_email_id").val($(this).val().toLowerCase());
    });
    
    $("#books_start_from").change(function() {
        let date = $(this).val();
        date = new Date(date);
        var financialYear;
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        year = year % 100;
        if (month >= 4) {
            financialYear = year + '-' + (year + 1);
        } else {
            financialYear = (year - 1) + '-' + year;
        }
        $("#current_finacial_year").val(financialYear);
    });
</script>
@endsection