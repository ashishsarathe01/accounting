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
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Bill Sundry</li>
                    </ol>
                </nav>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Bill Sundry
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-bill-sundry.update') }}">
                    @csrf
                    <input type="hidden" value="{{ $editbill->id }}" id="bill_id" name="bill_id" />
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $editbill->name }}" placeholder="Enter name" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label class="form-label font-14 font-heading">Nature Of Sundry</label>
                           <select class="form-select form-select-lg" name="nature_of_sundry" aria-label="form-select-lg example" required>
                              <option value="">Select Nature Of Sundry</option>
                              <option value="CGST" @if($editbill->nature_of_sundry=='CGST') selected @endif>CGST</option>
                              <option value="SGST" @if($editbill->nature_of_sundry=='SGST') selected @endif>SGST</option>
                              <option value="IGST" @if($editbill->nature_of_sundry=='IGST') selected @endif>IGST</option>
                              <option value="TCS" @if($editbill->nature_of_sundry=='TCS') selected @endif>TCS</option>
                              <option value="DISCOUNT" @if($editbill->nature_of_sundry=='DISCOUNT') selected @endif>DISCOUNT</option>
                              <option value="FREIGHT" @if($editbill->nature_of_sundry=='FREIGHT') selected @endif>FREIGHT</option>
                              <option value="INSURANCE" @if($editbill->nature_of_sundry=='INSURANCE') selected @endif>INSURANCE</option>
                              <option value="ROUNDED OFF (+)" @if($editbill->nature_of_sundry=='ROUNDED OFF (+)') selected @endif>ROUNDED OFF (+)</option>
                              <option value="ROUNDED OFF (-)" @if($editbill->nature_of_sundry=='ROUNDED OFF (-)') selected @endif>ROUNDED OFF (-)</option>
                              <option value="CESS ON GST" @if($editbill->nature_of_sundry=='CESS ON GST') selected @endif>CESS ON GST</option>
                              <option value="TDS" @if($editbill->nature_of_sundry=='TDS') selected @endif>TDS</option>
                              <option value="CUSTOM DUTY" @if($editbill->nature_of_sundry=='CUSTOM DUTY') selected @endif>CUSTOM DUTY</option>
                              <option value="OTHER" @if($editbill->nature_of_sundry=='OTHER') selected @endif>OTHER</option>
                           </select>
                        </div>
                        

                    </div>
                    <div class="row">
                     <div class="mb-4 col-md-4">
                         <label for="name" class="form-label font-14 font-heading">Bill Sundry Type</label>
                         <select class="form-select form-select-lg" name="bill_sundry_type" id="bill_sundry_type" aria-label="form-select-lg example">
                             <option value="">Select </option>
                             <option <?php echo $editbill->bill_sundry_type == 'additive' ? 'selected' : ''; ?> value="additive">Additive</option>
                             <option <?php echo $editbill->bill_sundry_type == 'subtractive' ? 'selected' : ''; ?> value="subtractive">Subtractive</option>
                         </select>
                     </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Adjust in Sale Amount</label>
                            <select class="form-select form-select-lg" name="adjust_sale_amt" id="adjust_sale_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_sale_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_sale_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg" name="sale_amt_account" id="sale_amt_account" aria-label="form-select-lg example" <?php echo $editbill->adjust_sale_amt =='No' ? '':'disabled';?>>
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) {
                                    $sel='';
                                    if($editbill->sale_amt_account == $value->id) 
                                     $sel= 'selected';?>
                                    <option <?php echo $sel; ?>  value="<?php echo $value->id; ?>"><?php echo $value->account_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Adjust in Purchase Amount</label>
                            <select class="form-select form-select-lg" name="adjust_purchase_amt" id="adjust_purchase_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_purchase_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_purchase_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg" name="purchase_amt_account" id="purchase_amt_account" aria-label="form-select-lg example" <?php echo $editbill->adjust_purchase_amt =='No' ? '':'disabled';?>>
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) { 
                                    $sel='';
                                    if($editbill->purchase_amt_account == $value->id) 
                                     $sel= 'selected';?>
                                    <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="mb-4 col-md-4">
                           <label for="effect_gst_calculation" class="form-label font-14 font-heading">Effect On Gst Calculation</label>
                           <select class="form-select form-select-lg" name="effect_gst_calculation" id="effect_gst_calculation" aria-label="form-select-lg example" required>
                              <option value="">Select </option>
                              <option value="1" @if($editbill->effect_gst_calculation==1) selected @endif>Yes</option>
                              <option value="0" @if($editbill->effect_gst_calculation==0) selected @endif>No</option>
                           </select>
                        </div>
                        <div class="mb-4 col-md-2">
                           <label class="form-label font-14 font-heading">Percentage</label>
                           <input type="text" class="form-control" name="sundry_percent" id="sundry_percent" placeholder="Percentage" value="{{$editbill->sundry_percent}}">
                        </div>
                        <div class="mb-4 col-md-2">
                           <label class="form-label font-14 font-heading">Percent Effect On</label>
                           <input type="date" class="form-control" name="sundry_percent_date" id="sundry_percent_date" value="{{$editbill->sundry_percent_date}}" >
                        </div>
                     </div>
                     <div class="row">
                        
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" id="status" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->status == 1 ? 'selected' : ''; ?> value="1">Enable</option>
                                <option <?php echo $editbill->status == 2 ? 'selected' : ''; ?> value="2">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn btn-xs-primary ">
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