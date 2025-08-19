@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
    .text-ellipsis {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }
    .w-min-50 {
        min-width: 50px;
    }
    .dataTables_filter,
    .dataTables_info,
    .dataTables_length,
    .dataTables_paginate {
        display: none;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height: 29px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height: 30px !important;
    }
    .select2-container .select2-selection--single{
        height: 30px !important;
    }
    .select2-container{
        width: 300 px !important;
    }
    .select2-container--default .select2-selection--single{
        border-radius: 12px !important;
    }
    .selection{
        font-size: 14px;
    }
    .form-control {
        height: 28px;
    }
    .form-select {
        height: 34px;
    }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0; 
    }
</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif            
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Sale Order</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-order.store')}}" id="purchaseForm">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Series No. *</label>
                            <select id="series_no" name="series_no" class="form-select" required autofocus>
                                <option value="">Select Series</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('series_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Date *</label>
                            <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" value="{{date('Y-m-d')}}" placeholder="Select date" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Freight *</label>
                            <select id="series_no" name="series_no" class="form-select" required autofocus>
                                <option value="">Select Freight</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Item *</label>
                            <select id="series_no" name="series_no" class="form-select" required autofocus>
                                <option value="">Select Item</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Price *</label>
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter Price" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <label for="name" class="form-label font-14 font-heading">GSM <svg xmlns="http://www.w3.org/2000/svg" data-id="1" class="bg-primary rounded-circle add_gsm" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></label>
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter GSM" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <label for="name" class="form-label font-14 font-heading">SIZES <svg xmlns="http://www.w3.org/2000/svg" data-id="1" class="bg-primary rounded-circle add_gsm" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></label>
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter SIZES" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-2 col-md-2">
                            <label for="name" class="form-label font-14 font-heading">REELS</label>
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter REELS" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter SIZES" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-2 col-md-2">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter REELS" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter SIZES" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-2 col-md-2" >
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter REELS" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter SIZES" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-2 col-md-2" >
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter REELS" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-2 col-md-2" style="margin-left: 40px;">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter SIZES" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-2 col-md-2">
                            <input type="text" id="date" class="form-control" name="date" placeholder="Enter REELS" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <p ><button type="button" class="btn btn-info">ADD ITEM</button></p>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Bill To *</label>
                            <select id="series_no" name="series_no" class="form-select" required autofocus>
                                <option value="">Select Account</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Shiip To *</label>
                            <select id="series_no" name="series_no" class="form-select" required autofocus>
                                <option value="">Select Account</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                    </div>
                    <div class=" d-flex">
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-xs-primary" id="purchaseBtn">
                            <a href="{{ route('purchase.index') }}" class="btn  btn-black ">QUIT</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
                <div class="shortcut-key w-100">
                    <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
                    <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                        <span class="border-bottom-black">F1</span>
                        <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                    <span class="border-bottom-black">F2</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">F3
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                    <span class="border-bottom-black">F3</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                    <span class="border-bottom-black">F5</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                    <span class="border-bottom-black">F6</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                    <span class="border-bottom-black">F7</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                    <span class="border-bottom-black">F8</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                    <span class="border-bottom-black">F9</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                    <span class="border-bottom-black">B</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                    <span class="border-bottom-black">T</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                    <span class="border-bottom-black">S</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                    <span class="border-bottom-black">L</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                    <span class="border-bottom-black">I</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                    <span class="border-bottom-black">D</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                    <span class="border-bottom-black">G</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                    <span class="border-bottom-black">U</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                    <span class="border-bottom-black">F</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                    <span class="border-bottom-black">K</span>
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                    <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">
                    Search Menu
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
@include('layouts.footer')
<script>
</script>
@endsection