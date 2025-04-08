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
                
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                Add Manage Item
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-manage-item.store') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">ITEM NAME</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="ENTER ITEM NAME" required/>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">PRINT NAME</label>
                            <input type="text" class="form-control" name="p_name" id="p_name" placeholder="ENTER PRINT NAME" />
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">UNDER GROUP</label>
                            <select class="form-select form-select-lg" name="g_name" id="g_name" aria-label="form-select-lg example" required>
                                <option value="">SELECT GROUP</option>
                                <?php
                                foreach ($itemGroups as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->group_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">OPENING BALANCE (Value in Rs.)</label>
                            <input type="text" class="form-control" id="opening_balance" name="opening_balance" placeholder="OPENING BALANCE (Value in Rs.)" />
                                
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">BALANCE TYPE</label>
                            <select class="form-select form-select-lg" name="opening_balance_type" id="opening_balance_type" aria-label="form-select-lg example">
                                <option value="">SELECT BALANCE TYPE</option>
                                <option value="Debit">Debit</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">OPENING BALANCE(Value in Quantity)</label>
                            <input type="text" class="form-control" id="opening_balance_qty" name="opening_balance_qty" placeholder="OPENING BALANCE(Value in Quantity)" />
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">BALANCE TYPE</label>
                            <select class="form-select form-select-lg" name="opening_balance_qt_type" id="opening_balance_qt_type" aria-label="form-select-lg example">
                                <option value="">SELECT BALANCE TYPE</option>
                                <option value="Debit">Debit</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">UNIT NAME</label>
                            <select class="form-select form-select-lg " name="u_name" aria-label="form-select-lg example" required>
                                <option value="">SELECT UNIT</option>
                                <?php
                                foreach ($accountunit as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">GST RATE</label>
                            <input type="text" class="form-control" id="gst_rate" name="gst_rate" placeholder="ENTER GST RATE" />
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">HSN CODE</label>
                            <input type="text" class="form-control" id="hsn_code" name="hsn_code" placeholder="ENTER HSN CODE" />
                        </div>
                        <div class="clearfix"></div>                        
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">STATUS</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">SELECT STATUS</option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </div>

                <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
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
@include('layouts.footer')
<script type="text/javascript">
   $(document).ready(function(){
      $("#name").keyup(function(){
         $("#p_name").val($(this).val());
      });
   });
</script>