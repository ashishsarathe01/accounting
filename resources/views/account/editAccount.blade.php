@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Account</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Account</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account.update') }}">
               @csrf
               <input type="hidden" value="{{ $account->id }}" id="account_id" name="account_id" />
               <div class="row">
                  <h6 class="font-heading mb-4">General Info.</h6>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Account Name</label>
                     <input type="text" class="form-control" id="account_name" value="{{$account->account_name}}" name="account_name" placeholder="Enter account name" required/>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Print Name</label>
                     <input type="text" class="form-control" id="print_name" value="{{$account->print_name}}" name="print_name" placeholder="Enter here" required/>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Under Group</label>
                     <select class="form-select form-select-lg " name="under_group" id="under_group" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <?php
                        foreach ($accountgroup as $value) {
                           $sel = '';
                           if ($account->under_group == $value->id)
                              $sel = 'selected'; ?>
                              <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                              <?php 
                           } ?>
                     </select>
                  </div>
                  @if(Session::get('business_type')==2)
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Under Group S-|||</label>
                     <select class="form-select form-select-lg " name="under_group_s" id="under_group_s" aria-label="form-select-lg example">
                        <option value="">Select </option>
                        <?php
                        foreach ($accountgroup as $value) {
                           $sel = '';
                           if ($account->under_group_s == $value->id)
                              $sel = 'selected'; ?>
                           <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name_as_sch; ?></option>
                           <?php 
                        } ?>
                     </select>
                  </div>
                  @endif
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Opening Balance</label>
                     <input type="text" class="form-control" id="opening_balance" name="opening_balance" placeholder="Enter Opening Balance" value="<?php echo $account->opening_balance; ?>"/>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Balance Type</label>
                     <select class="form-select form-select-lg" name="opening_balance_type" id="opening_balance_type" aria-label="form-select-lg example">
                        <option value="">Select </option>
                        <option <?php echo $account->dr_cr == 'debit' ? 'selected' : ''; ?> value="debit">Debit</option>
                        <option <?php echo $account->dr_cr == 'credit' ? 'selected' : ''; ?> value="credit">Credit</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Address</label>
                     <input type="text" class="form-control" id="address" value="{{$account->address}}" name="address" placeholder="Enter address" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">GSTIN</label>
                     <input type="text" class="form-control" id="gstin" value="{{$account->gstin}}" name="gstin" placeholder="Enter GSTIN" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="contact-number" class="form-label font-14 font-heading">State</label>
                     <select class="form-select form-select-lg " name="state" id="state" aria-label="form-select-lg example">
                        <option value="">Select </option>
                        <?php
                        foreach ($state_list as $value) {
                           $sel = '';
                           if($account->state == $value->id)
                              $sel = 'selected'; ?>
                              <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                           <?php 
                        } ?>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Pincode</label>
                     <input type="text" class="form-control" id="pin_code" value="{{$account->pin_code}}" name="pin_code" placeholder="Enter pincode" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">PAN</label>
                     <input type="text" class="form-control" id="pan" value="{{$account->pan}}" name="pan" placeholder="Enter PAN" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Email</label>
                     <input type="text" class="form-control" id="email" value="{{$account->email}}" name="email" placeholder="Enter email" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                     <div class="position-relative">
                        <input type="text" class="form-control pl-56 pr-70" id="mobile" name="mobile" value="{{$account->mobile}}" placeholder="Enter mobile number" />
                        <span class="position-absolute number-divider font-14">+91</span>
                     </div>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Contact Person</label>
                     <input type="text" class="form-control" id="contact_person" value="{{$account->contact_person}}" name="contact_person" placeholder="Enter contact person" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Whatsapp Number</label>
                     <div class="position-relative">
                        <input type="text" class="form-control pl-56 pr-70" id="whatsup_number" value="{{$account->whatsup_number}}" name="whatsup_number" placeholder="Enter mobile number" />
                        <span class="position-absolute number-divider font-14">+91</span>
                     </div>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg " name="status" id="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option <?php echo $account->status == '1' ? 'selected' : ''; ?> value="1">Enable</option>
                        <option <?php echo $account->status == '0' ? 'selected' : ''; ?> value="0">Disable</option>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <h6 class="font-heading mb-4">Other Info.</h6>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Maintain Bill by Bill Details</label>
                     <select class="form-select form-select-lg " name="maintain_bill_by_details" id="maintain_bill_by_details" aria-label="form-select-lg example">
                        <option value="">Select </option>
                        <option <?php echo $account->maintain_bill_by_details == '1' ? 'selected' : ''; ?> value="1">Yes</option>
                        <option <?php echo $account->maintain_bill_by_details == '0' ? 'selected' : ''; ?> value="0">No</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Credit Days</label>
                     <input type="text" class="form-control" placeholder="Enter number" value="{{$account->credit_days}}" name="credit_days" id="credit_days">
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Limit (Rs.)</label>
                     <input type="text" class="form-control" name="limit" value="{{$account->limit}}" id="limit" placeholder="Enter limit (rs.)" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Price Change SMS</label>
                     <select class="form-select form-select-lg " name="price_change_sms" id="price_change_sms" aria-label="form-select-lg example">
                        <option value="">Select </option>
                        <option <?php echo $account->price_change_sms == '1' ? 'selected' : ''; ?> value="1">Yes</option>
                        <option <?php echo $account->price_change_sms == '0' ? 'selected' : ''; ?> value="0">No</option>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Bank Name</label>
                     <input type="text" class="form-control" id="bank_name" value="{{$account->bank_name}}" name="bank_name" placeholder="Enter bank name" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="contact-number" class="form-label font-14 font-heading">Bank Account No.</label>
                     <input type="text" class="form-control" id="bank_account_no" value="{{$account->bank_account_no}}" name="bank_account_no" placeholder="Enter bank account no." />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">IFSC Code</label>
                     <input type="text" class="form-control" id="ifsc_code" value="{{$account->ifsc_code}}" name="ifsc_code" placeholder="Enter IFSC code" />
                  </div>
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Depreciation Rate</label>
                     <input type="text" class="form-control" id="depreciation_rate" value="{{$account->depreciation_rate}}" name="depreciation_rate" placeholder="Enter depreciation rate" />
                  </div>
                  <div class="mb-4 col-md-4 position-relative">
                     <label for="name" class="form-label font-14 font-heading">Yearly</label>
                     <input type="date" id="yearly" name="yearly" value="{{$account->yearly}}" class="form-control calender-bg-icon calender-placeholder" placeholder="Select date" >
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="contact-number" class="form-label font-14 font-heading">As per I.Tax</label>
                     <input type="text" class="form-control" value="{{$account->per_tax}}" id="per_tax" name="per_tax" placeholder="Enter as per i.tax" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">As per Company Act</label>
                     <input type="text" class="form-control" id="company_act" value="{{$account->company_act}}" name="company_act" placeholder="Enter as per company act" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">GST Rate</label>
                     <input type="text" class="form-control" id="gst_rate" value="{{$account->gst_rate}}" name="gst_rate" placeholder="Enter GST rate" />
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">HSN/SAC Code</label>
                     <input type="text" class="form-control" id="hsn_code" value="{{$account->hsn_code}}" name="hsn_code" placeholder="Enter HSN/SAC code" />
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
<script type="text/javascript">
   $("#account_name").keyup(function(){
      $("#print_name").val($(this).val())
   });
</script>