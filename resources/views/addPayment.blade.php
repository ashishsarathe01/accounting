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
            <div class="col-md-12 ml-sm-auto  col-lg-8 px-md-4 bg-mint">
                <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                            <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                            Payment </a>
                            </li>
                        </ol>
                    </nav>
                </div>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Add Payment Voucher
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('payment.store') }}">
                    @csrf
                    <div class="row">

                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
                        </div>
                    </div>
                    <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="example" class="table-striped table m-0 shadow-sm table-bordered">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink ">
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Debit/Credit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Debit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Credit</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Mode</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body ">Narration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="">
                                        <select class="form-control type" name="type[]" data-id="1" id="type_1">
                                            <option value="">Type</option>
                                            <option value="Credit" selected>Credit</option>
                                            <option value="Debit">Debit</option>
                                        </select>
                                    </td>
                                    <td class="transaction-select-opacity">
                                        <select class="form-select" id="account_1" name="account_name[]" required>
                                            <option value="">Select</option>
                                            <?php
                                            foreach ($party_list as $value) { ?>
                                                <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="transaction-select-opacity">
                                        <input type="text" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" disabled>
                                    </td>
                                    <td class="transaction-select-opacity">
                                        <input type="text" name="credit[]" class="form-control credit" data-id="1" id="credit_1" placeholder="Credit Amount">
                                    </td>
                                    <td class="transaction-select-opacity">
                                        <select class="form-control mode" name="mode[]" data-id="1" id="mode_1">
                                            <option value="">Mode</option>
                                            <option value="Cash">Cash</option>
                                            <option value="IMPS/NEFT/RTGS">IMPS/NEFT/RTGS</option>
                                        </select>
                                    </td>
                                    <td class="transaction-select-opacity">
                                        <input type="text" name="narration[]" class="form-control narration" data-id="1" id="narration_1" placeholder="Enter Narration" value="">
                                    </td>
                                </tr>
                            </tbody>
                            <div class="plus-icon">
                                <tr class="font-14 font-heading bg-white">
                                    <!-- icon 3 tr ma joi aavi nathi rahyo -->
                                    <td class="w-min-120 " colspan="7">
                                        <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                            </svg></a>
                                    </td>

                                </tr>
                            </div>
                            <div class="total">
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 fw-bold" colspan="6">
                                        Total
                                    </td>
                                </tr>
                            </div>
                        </table>
                    </div>


                    <div class=" d-flex">

                        <div class="ms-auto">
                            <input type="submit" value="SUBMIT" class="btn btn-xs-primary ">

                        </div>
                        <input type="hidden" clas="max_sale_descrption" name="max_sale_descrption" value="1" id="max_sale_descrption">
                        <input type="hidden" name="max_sale_sundry" id="max_sale_sundry" value="1" />

                    </div>
                </form>
            </div>
            <div class="col-lg-2 d-flex justify-content-center">
                <div class="shortcut-key ">
                    <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
                    <button class="p-2 transaction-shortcut-btn my-2 ">
                        F1
                        <span class="ps-1 fw-normal text-body">Help</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F1</span>
                        <span class="ps-1 fw-normal text-body">Add Account</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F2</span>
                        <span class="ps-1 fw-normal text-body">Add Item</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        F3
                        <span class="ps-1 fw-normal text-body">Add Master</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F3</span>
                        <span class="ps-1 fw-normal text-body">Add Voucher</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F5</span>
                        <span class="ps-1 fw-normal text-body">Add Payment</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F6</span>
                        <span class="ps-1 fw-normal text-body">Add Receipt</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F7</span>
                        <span class="ps-1 fw-normal text-body">Add Journal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F8</span>
                        <span class="ps-1 fw-normal text-body">Add Sales</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        <span class="border-bottom-black">F9</span>
                        <span class="ps-1 fw-normal text-body">Add Purchase</span>
                    </button>

                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">B</span>
                        <span class="ps-1 fw-normal text-body">Balance Sheet</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">T</span>
                        <span class="ps-1 fw-normal text-body">Trial Balance</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">S</span>
                        <span class="ps-1 fw-normal text-body">Stock Status</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">L</span>
                        <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">I</span>
                        <span class="ps-1 fw-normal text-body">Item Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">D</span>
                        <span class="ps-1 fw-normal text-body">Item Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">G</span>
                        <span class="ps-1 fw-normal text-body">GST Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">U</span>
                        <span class="ps-1 fw-normal text-body">Switch User</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F</span>
                        <span class="ps-1 fw-normal text-body">Configuration</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">K</span>
                        <span class="ps-1 fw-normal text-body">Lock Program</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">Training Videos</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">GST Portal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        Search Menu
                    </button>
                </div>
            </div>
        </div>
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>
    $(document).on("change", ".type", function() {
        let id = $(this).attr('data-id');
        if ($("#type_" + id).val() == "Credit") {
            $("#debit_" + id).prop('disabled', true);
            $("#credit_" + id).prop('disabled', false);
        } else if ($("#type_" + id).val() == "Debit") {
            $("#debit_" + id).prop('disabled', false);
            $("#credit_" + id).prop('disabled', true);
        }
    });

    var add_more_count = 1;
    $(".add_more").click(function() {
        add_more_count++;
        var $curRow = $(this).closest('tr');
        var optionElements = $('#account_1').html();
        newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]"  data-id="' + add_more_count + '" id="type_' + add_more_count + '"><option value="">Type</option><option value="Credit">Credit</option><option value="Debit">Debit</option></select></td><td><select class="form-control account select2" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
        newRow += optionElements;
        newRow += '</select></td><td><input type="text" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" disabled></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" disabled></td><td><select class="form-control mode" name="mode[]" data-id="' + add_more_count + '" id="mode_' + add_more_count + '"><option value="">Mode</option><option value="Cash">Cash</option><option value="IMPS/NEFT/RTGS">IMPS/NEFT/RTGS</option></select></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><button class="btn btn-danger btn-xs remove" data-id="' + add_more_count + '">Remove</button></td></tr>';
        $curRow.before(newRow);
    });

    $(document).on("click", ".remove", function() {
        let id = $(this).attr('data-id');
        $("#tr_" + id).remove();
    });
</script>
@endsection