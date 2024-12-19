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
                  <li class="breadcrumb-item">Dashboard</li><img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Bill Sundry</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Add Bill Sundry
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-bill-sundry.store') }}">
               @csrf
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Name</label>
                     <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Nature Of Sundry</label>
                     <select class="form-select form-select-lg" name="nature_of_sundry" aria-label="form-select-lg example" required>
                        <option value="">Select Nature Of Sundry</option>
                        <option value="CGST">CGST</option>
                        <option value="SGST">SGST</option>
                        <option value="IGST">IGST</option>
                        <option value="TCS">TCS</option>
                        <option value="DISCOUNT">DISCOUNT</option>
                        <option value="FREIGHT">FREIGHT</option>
                        <option value="INSURANCE">INSURANCE</option>
                        <option value="ROUNDED OFF (+)">ROUNDED OFF (+)</option>
                        <option value="ROUNDED OFF (-)">ROUNDED OFF (-)</option>
                        <option value="CESS ON GST">CESS ON GST</option>
                        <option value="TDS">TDS</option>
                        <option value="CUSTOM DUTY">CUSTOM DUTY</option>
                        <option value="OTHER">OTHER</option>
                     </select>
                  </div>
                  
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Bill Sundry Type</label>
                     <select class="form-select form-select-lg" name="bill_sundry_type" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="additive">Additive</option>
                        <option value="subtractive">Subtractive</option>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Adjust in Sale Amount</label>
                     <select class="form-select form-select-lg" name="adjust_sale_amt" id="adjust_sale_amt" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">List Of Account</label>
                     <select class="form-select form-select-lg" name="sale_amt_account" id="sale_amt_account" aria-label="form-select-lg example" disabled>
                        <option selected>Select </option>
                        <?php
                        foreach($account as $value) { ?>
                           <option value="<?php echo $value->id; ?>"><?php echo $value->account_name ?></option>
                           <?php 
                        } ?>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Adjust in Purchase Amount</label>
                     <select class="form-select form-select-lg" name="adjust_purchase_amt" id="adjust_purchase_amt" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">List Of Account</label>
                     <select class="form-select form-select-lg" name="purchase_amt_account" id="purchase_amt_account" aria-label="form-select-lg example" disabled>
                        <option value="">Select </option>
                        <?php
                        foreach ($account as $value) { ?>
                           <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                           <?php 
                        } ?>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="effect_gst_calculation" class="form-label font-14 font-heading">Effect On Gst Calculation</label>
                     <select class="form-select form-select-lg" name="effect_gst_calculation" id="effect_gst_calculation" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-2">
                     <label class="form-label font-14 font-heading">Percent</label>
                     <input type="text" class="form-control" name="sundry_percent" id="sundry_percent" placeholder="Percentage">
                  </div>
                  <div class="mb-4 col-md-2">
                     <label class="form-label font-14 font-heading">Percent Effect On</label>
                     <input type="date" class="form-control" name="sundry_percent_date" id="sundry_percent_date" value="{{$last_invoice_date}}" min="{{$last_invoice_date}}">
                  </div>
               </div>
               <div class="row">
                  
                  <div class="mb-4 col-md-4">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="1">Enable</option>
                        <option value="2">Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn btn-xs-primary">SUBMIT</button>
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
        $("#adjust_sale_amt").change(function() {
            if ($("#adjust_sale_amt").val() == "Yes") {
                $("#sale_amt_account").prop('disabled', true);
            } else if ($("#adjust_sale_amt").val() == "No") {
                $("#sale_amt_account").prop('disabled', false);
            }
        });

        $("#adjust_purchase_amt").change(function() {
            if ($("#adjust_purchase_amt").val() == "Yes") {
                $("#purchase_amt_account").prop('disabled', true);
            } else if ($("#adjust_purchase_amt").val() == "No") {
                $("#purchase_amt_account").prop('disabled', false);
            }
        });
    });
</script>
@endsection