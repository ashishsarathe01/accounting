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
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                <div class="alert alert-danger" role="alert"> {{session('error')}}
                </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Sale Invoice Configuration
                    </h5>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm purchase_table">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('add-sale-invoice-configuration')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-3">
                                <label for="company_logo_status" class="form-label font-14 font-heading">SHOW COMPANY LOGO</label>
                                <select class="form-select form-select-lg select2-single" name="company_logo_status" id="company_logo_status" aria-label="form-select-lg example" required>
                                    <option value="">Select Status</option>
                                    <option value="1" @if($configuration && $configuration->company_logo_status==1) selected @endif>Yes</option>
                                    <option value="0" @if($configuration && $configuration->company_logo_status==0) selected @endif>No</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4 company_logo_div" style="display:none">
                                <label for="company_logo" class="form-label font-14 font-heading">ATTACH LOGO</label>
                                <input type="file" class="form-control" id="company_logo" name="company_logo">
                                @if($configuration && !empty($configuration->company_logo))
                                    <img src="{{ URL::asset('public/images')}}/{{$configuration->company_logo}}" style="width: 50px;">
                                @endif
                            </div>
                            <div class="mb-4 col-md-4 company_logo_div" style="display:none">
                                <label for="pricompany_logont_name" class="form-label font-14 font-heading">LOGO SHOW IN</label><br>
                                <input type="checkbox" class="logo_position" id="logo_position_left" name="logo_position_left" @if($configuration && $configuration->logo_position_left==1) checked @endif> LEFT

                                <input type="checkbox" class="logo_position_right" id="logo_position_right" name="logo_position_right" @if($configuration && $configuration->logo_position_right==1) checked @endif> RIGHT
                            </div>
                            <div class="clearfix"></div>
                            <div class="mb-43 col-md-3">
                                <label for="bank_detail_status" class="form-label font-14 font-heading">SHOW BANK DETAILS</label>
                                <select class="form-select form-select-lg select2-single" name="bank_detail_status" id="bank_detail_status" aria-label="form-select-lg example" required>
                                    <option value="">Select Status</option>
                                    <option value="1" @if($configuration && $configuration->bank_detail_status==1) selected @endif>Yes</option>
                                    <option value="0" @if($configuration && $configuration->bank_detail_status==0) selected @endif>No</option>
                                </select>
                            </div>
                            <div class="mb-4 col-md-4 bank_div" style="display:none">
                                <label for="bank_name" class="form-label font-14 font-heading">SELECT BANK</label>
                                <select class="form-select form-select-lg select2-single" name="bank_name" id="bank_name" aria-label="form-select-lg example" required>
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{$bank->id}}" @if($configuration && $configuration->bank_name==$bank->id) selected @endif>{{$bank->bank_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div class="clearfix"></div>
                            <div class="mb-3 col-md-3">
                                <label for="term_status" class="form-label font-14 font-heading">SHOW TERMS & CONDITIONS</label>
                                <select class="form-select form-select-lg select2-single" name="term_status" id="term_status" aria-label="form-select-lg example" required>
                                    <option value="">Select Status</option>
                                    <option value="1" @if($configuration && $configuration->term_status==1) selected @endif>Yes</option>
                                    <option value="0" @if($configuration && $configuration->term_status==0) selected @endif>No</option>
                                </select>
                            </div>
                            <div class="mb-8 col-md-8 term_div" style="display:none">
                                <label for="terms" class="form-label font-14 font-heading">TERMS & CONDITIONS</label>
                                @if($configuration && $configuration->terms && count($configuration->terms)>0)
                                    @foreach($configuration->terms as $k=>$t)
                                        <span style="display: flex">
                                            <input type="text" class="form-control terms term_remove_div_{{$k}}" id="terms_{{$k}}" name="terms[]" placeholder="ENTER TERMS & CONDITIONS" data-id="{{$k}}" value="{{$t->term}}">

                                            <svg style="color: red;cursor: pointer;margin-top:12px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove_term terms term_remove_div_{{$k}}" data-id="{{$k}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"></path></svg>
                                        </span>
                                    @endforeach
                                    @php $term_index = $k; @endphp
                                @else
                                @php $term_index = 1; @endphp
                                    <input type="text" class="form-control terms" id="terms_1" name="terms[]" placeholder="ENTER TERMS & CONDITIONS" data-id="1">
                                @endif                                
                            </div>                            
                            <div class="mb-1 col-md-1 term_div" style="display:none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_terms" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor:pointer;margin-top: 40px;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                            </div>
                            <div id="append_div" class="row"></div>
                            <div class="clearfix"></div>
                            <div class="mb-4 col-md-4">
                                <label for="signature" class="form-label font-14 font-heading">SIGNATURE INFO.</label>
                                <input type="file" class="form-control" id="signature" name="signature">
                                
                            </div>
                            <div class="mb-4 col-md-4">
                                @if($configuration && !empty($configuration->signature))
                                    <img src="{{ URL::asset('public/images')}}/{{$configuration->signature}}" style="width: 50px;">
                                @endif
                            </div>
                        </div>
                        <div class="text-start">
                            <button type="submit" class="btn btn-xs-primary save_btn">SUBMIT</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
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
    $(document).ready(function() {  
        changeLogoStatus();
        changeBankStatus();
        changeTerm();
        $("#bank_detail_status").change();
        $("#term_status").change();
        $("#company_logo_status").change(function(){
            changeLogoStatus();
        });
        $("#bank_detail_status").change(function(){
            changeBankStatus();
        });
        $("#term_status").change(function(){
            changeTerm();
        });
        let term_index = {{$term_index}};
        $(".add_terms").click(function(){
            term_index++;
            $("#append_div").append('<div class="mb-3 col-md-3 term_remove_div_'+term_index+'"></div><div class="mb-8 col-md-8 term_div term_remove_div_'+term_index+'" style="display:block"><input type="text" class="form-control terms" id="terms_'+term_index+'" name="terms[]" placeholder="ENTER TERMS & CONDITIONS" data-id="'+term_index+'"></div><div class="mb-1 col-md-1 term_div term_remove_div_'+term_index+'" style="display:block"><svg style="color: red;cursor: pointer;margin-top:12px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove_term" data-id="'+term_index+'" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></div>');
        });
    });
    $(document).on('click','.remove_term',function(){
        $(".term_remove_div_"+$(this).attr('data-id')).remove();
    });  
    function changeLogoStatus(){
        $(".company_logo_div").hide();
        $("#company_logo").attr("required",false);
        if($("#company_logo_status").val()=="1"){
            $(".company_logo_div").show();
            //$("#company_logo").attr("required",true);
        }
    }
    function changeBankStatus(){
        $(".bank_div").hide();
        $("#bank_name").attr("required",false);
        if($("#bank_detail_status").val()=="1"){
            $(".bank_div").show();
            $("#bank_name").attr("required",true);
        }
    }
    function changeTerm(){
        $(".term_div").hide();
        if($("#term_status").val()=="1"){
            $(".term_div").show();
        }
    }
</script>
@endsection