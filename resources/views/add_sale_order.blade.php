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
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Deal</label>
                            <select id="deal" name="deal" class="form-select" required autofocus>
                                <option value="">Select Deal</option>                        
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <label for="item_1" class="form-label font-14 font-heading">Item *</label>
                            <select id="item_1" name="item[]" class="form-select item" data-id="1" required autofocus>
                                <option value="">Select Item</option>
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="price_1" class="form-label font-14 font-heading">Price *</label>
                            <input type="number" id="price_1" class="form-control price" name="price[]" placeholder="Enter Price" data-id="1" required >
                            <ul style="color: red;">
                                @error('date'){{$message}}@enderror
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="bill_price_1" class="form-label font-14 font-heading">Bill Price <input type="checkbox"></label>
                            <input type="text" id="bill_price_1" class="form-control" name="bill_price[]" placeholder="Enter Price" readonly data-id="1">
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
                            <label for="unit_1" class="form-label font-14 font-heading">Unit *</label>
                            <select id="unit_1" name="unit[]" class="form-select unit" data-id="1" required autofocus >
                                <option value="">Select Unit</option>
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="sub_unit_1" class="form-label font-14 font-heading">Sub Unit *</label>
                            <select id="sub_unit_1" name="sub_unit[]" class="form-select sub_unit" data-id="1" required autofocus>
                                <option value="">Select Sub Unit</option>
                                <option value="INCH">INCH</option>
                                <option value="CM">CM</option>
                                <option value="MM">MM</option>
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror
                            </ul> 
                        </div>
                        <div class="clearfix"></div>
                        <div class="mb-3 col-md-3">
                            <div class="form-group">
                                <table class="table table-bordered">
                                    <tr>
                                    <td style="width: 40%;">GSM <span id="0_1" class="add_gsm" onClick="addGSM(0,1)" style="color:#3c8dbc;cursor: pointer;" >Add GSM</span></td>
                                    <td> <input type="text" id="gsm_1_1" class="form-control gsm_1" name="gsm[]" placeholder="Enter GSM" required ></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="form-group">
                                <table class="table table-bordered" id="table_1_1">
                                    <tr>
                                        <td>SIZES</td>
                                        <td class="reel_name_0">REELS</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text" id="size_1_1_1" class="form-control size_1 size_1_1" name="size[]" placeholder="SIZES" required >
                                        </td>
                                        <td>
                                            <input type="text" id="reel_1_1_1" class="form-control reel_1 reel_1_1" name="reel[]" placeholder="REELS" required >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td id="total_reel_1_1" class="total_reel">0</td>
                                    </tr>
                                </table>
                                <span id="1_1" class="add_row" onClick="addRow(1,1)" style="color:#3c8dbc;cursor: pointer;" >Add Row</span>
                            </div>
                        </div>
                        <div id="dynamic_gsm_0_1" class="col-md-2"></div>
                         
                        <div class="clearfix"></div>
                        <p ><button type="button" class="btn btn-info">ADD ITEM</button></p>
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
    
    function addRow(a,b){
        $("#table_"+a+"_"+b+" tr").eq(-2).before('<tr><td><input type="text" name="size[]"  class="form-control size_1 size_1_1" data-id="'+a+'_'+b+'" placeholder="SIZES"></td><td><input type="number" min="1" class="form-control reel_1 reel_1_1" name="reel[]" placeholder="REELS"></td></tr>');
   }
   let row_id=1;
   function addGSM(id,ids){
      var text = $("#unit_"+id).val();
      let a = ids;
      $("#"+id+"_"+a).hide();
      a++;
      $("#dynamic_gsm_"+id+"_"+ids).append('<div class="form-group"><table class="table table-bordered"><tr><td style="width: 50%;">GSM</td><td><input type="text" name="gsm_'+id+'[]" class="text_width gsm_'+id+'"></td></tr></table></div></div><div class="form-group"><table class="table table-bordered" id="table_'+id+'_'+ids+'"><tr><td>SIZES</td><td>'+text+'</td></tr><tr><td><input type="text" name="size_'+id+'_'+ids+'[]" class="text_width checkSize size_class_'+id+'_'+ids+'" data-id="'+id+'_'+ids+'" onkeyup="totalCount('+id+','+ids+')"></td><td><input min="1" type="number" class="reel_'
      +id+'_'+ids+' text_width" onkeyup="totalCount('+id+','+ids+')" name="reel_'+id+'_'+ids+'[]"></td></tr><tr><td><input type="text" name="size_'+id+'_'+ids+'[]" class="text_width checkSize size_class_'+id+'_'+ids+'" data-id="'+id+'_'+ids+'" onkeyup="totalCount('+id+','+ids+')"></td><td><input type="number" min="1" class="reel_'
      +id+'_'+ids+' text_width" onkeyup="totalCount('+id+','+ids+')" name="reel_'+id+'_'+ids+'[]"></td></tr> <tr><td><input type="text" name="size_'+id+'_'+ids+'[]" class="text_width checkSize size_class_'+id+'_'+ids+'" data-id="'+id+'_'+ids+'" onkeyup="totalCount('+id+','+ids+')"></td><td><input type="number" min="1" class="reel_'
      +id+'_'+ids+' text_width" onkeyup="totalCount('+id+','+ids+')" name="reel_'+id+'_'+ids+'[]"></td></tr><tr><td><input type="text"  name="size_'+id+'_'+ids+'[]" class="text_width checkSize size_class_'+id+'_'+ids+'" data-id="'+id+'_'+ids+'" onkeyup="totalCount('+id+','+ids+')"></td><td><input type="number" min="1" class="reel_'
      +id+'_'+ids+' text_width" onkeyup="totalCount('+id+','+ids+')" name="reel_'+id+'_'+ids+'[]"></td></tr><tr><td><input type="text" name="size_'+id+'_'+ids+'[]" class="text_width checkSize size_class_'+id+'_'+ids+'" data-id="'+id+'_'+ids+'" onkeyup="totalCount('+id+','+ids+')"></td><td><input type="number" min="1" class="reel_'
      +id+'_'+ids+' text_width" onkeyup="totalCount('+id+','+ids+')" name="reel_'+id+'_'+ids+'[]"></td></tr><tr><td>Total</td><td id="total_'+id+'_'+ids+'" class="total_reel">0</td></tr>  <tr style="display: none" class="approx_weight"><td>Approx Weight</td><td id="app_weight_'+id+'_'+ids+'" class="app_weight">0</td></tr></table> <span id="'+id+'_'+ids+'" class="add_row" onClick="addRow('+id+','+ids+')" style="color:#3c8dbc;cursor: pointer;" >Add Row</span></div>');
      
     $("#a_"+id+"_"+ids).append('<div id="dynamic_gsm_'+id+'_'+a+'" class="col-md-2"></div><span id="a_'+id+'_'+a+'"></span><span id="'+id+'_'+a+'" class="add_gsm" onClick="addGSM('+id+','+a+')" style="color:#3c8dbc;cursor: pointer;" >Add GSM</span>');
   }
</script>
@endsection