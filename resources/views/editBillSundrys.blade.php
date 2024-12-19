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
                            <label for="name" class="form-label font-14 font-heading">Bill Sundry Type</label>
                            <select class="form-select form-select-lg" name="bill_sundry_type" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->bill_sundry_type == 'additive' ? 'selected' : ''; ?> value="additive">Additive</option>
                                <option <?php echo $editbill->bill_sundry_type == 'subtractive' ? 'selected' : ''; ?> value="subtractive">Subtractive</option>
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Adjust in Sale Amount</label>
                            <select class="form-select form-select-lg" name="adjust_sale_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_sale_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_sale_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg" name="sale_amt_account" aria-label="form-select-lg example">
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Adjust in Purchase Amount</label>
                            <select class="form-select form-select-lg" name="adjust_purchase_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_sale_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_sale_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg" name="purchase_amt_account" aria-label="form-select-lg example">
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example">
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
@endsection