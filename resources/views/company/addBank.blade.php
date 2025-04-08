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
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">
                    Bank Account Information
                </h5>
                <div class="d-md-flex d-block"> 
                <button class="btn btn-info add_bank"  type="button" style="float:right">ADD BANK</button>
               </div>
                <div class="overflow-auto ">
                    <table class="table  mb-4 table-bordered">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink title-border-redius">
                                <th class="w-120">A/C No.</th>
                                <th class="w-120">IFSC Code </th>
                                <th class="w-120">Bank Name</th>
                                <th class="w-120">Branch</th>
                                <th class="w-120">Select for Display in Invoice</th>
                                <th class="w-120 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                                foreach ($bank_data as $value) { ?>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-120"><?php echo $value->account_no?></td>
                                <td class="w-120"><?php echo $value->ifsc?></td>
                                <td class="w-120"><?php echo $value->bank_name?></td>
                                <td class="w-120"><?php echo $value->branch?></td>
                                <td class="w-120"><?php echo $value->branch?></td>
                                <td class="text-center">
                                    <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('add-shareholder') }}" class="btn btn-white mb-4">
                        Previous
                    </a>
                    <!--<input type="submit" value="NEXT" class="btn btn-heading mb-4">-->
                        <a href="{{ route('view-company') }}" class="btn btn-heading mb-4">
                        SUBMIT
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="bank_info_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-4 border-divider border-radius-8">
        <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
        Add Bank Account 
        </h5>
        <br>
        <form method="post" id="form-job-store">
        @csrf
        <input type="hidden" name="form_type" value="bank">
        <input type="hidden" name="status" value="1">
        <div class="row">  
            <div class="mb-4 col-md-4">
                <label for="name" class="form-label font-14 font-heading">ACCOUNT NAME</label>
                <input type="text" class="form-control" id="account_name" name="account_name" placeholder="ENTER ACCOUNT NAME" required="" value="">
            </div>
            <div class="clearfix"></div>
            <div class="mb-4 col-md-4">
                <label for="print_name" class="form-label font-14 font-heading">PRINT NAME</label>
                <input type="text" class="form-control" id="print_name" name="print_name" placeholder="ENTER PRINT NAME" required="" value="">
            </div>
            <div class="clearfix"></div>
                <div class="mb-4 col-md-4">
                    <label for="under_group" class="form-label font-14 font-heading">UNDER GROUP</label>
                    <select class="form-select form-select-lg select2-single" name="under_group" id="under_group" aria-label="form-select-lg example" required="">                                          
                        <option value="7" data-type="group" data-under_debtor_status="0" data-under_creditors_status="0" data-under_dutytaxes_status="0" data-bank_account_status="0" data-capital_account_status="0" data-loan_status="0">BANK ACCOUNTS</option>
                    </select>
                    <input type="hidden" value="group" id="under_group_type" name="under_group_type"> 
                </div>
                <div class="clearfix"></div>                  
                <div class="mb-4 col-md-4 account_no_div common_div">
                    <label for="account_no" class="form-label font-14 font-heading">BANK ACCOUNT NO.</label>
                    <input type="number" class="form-control common_val" id="account_no" name="account_no" placeholder="ENTER BANK ACCOUNT NO." value="">
                </div>
                <div class="mb-4 col-md-4 ifsc_code_div common_div" >
                    <label for="ifsc_code" class="form-label font-14 font-heading">BANK IFSC CODE</label>
                    <input type="text" class="form-control common_val" id="ifsc_code" name="ifsc_code" placeholder="ENTER BANK IFSC CODE" value="">
                </div>
                <div class="clearfix"></div>
                <div class="mb-4 col-md-4 bank_name_div common_div" >
                    <label for="bank_name" class="form-label font-14 font-heading">BANK NAME</label>
                    <input type="text" class="form-control common_val" id="bank_name" name="bank_name" placeholder="ENTER BANK NAME" value="">
                </div>
                <div class="mb-4 col-md-4 nature_of_account_div common_div" >
                    <label for="nature_of_account" class="form-label font-14 font-heading">NATURE OF ACCOUNT</label>
                    <input type="text" class="form-control common_val" id="nature_of_account" name="nature_of_account" placeholder="ENTER NATURE OF ACCOUNT" value="">
                </div>
                <div class="mb-4 col-md-4 bank_name_div common_div">
                     <label for="branch" class="form-label font-14 font-heading">BRANCH</label>
                     <input type="text" class="form-control common_val" id="branch" name="branch" placeholder="ENTER BRANCH" >
                  </div>
                <hr>
                <div class="clearfix"></div>
                <div class="mb-4 col-md-4">
                    <label for="opening_balance" class="form-label font-14 font-heading">OPENING BALANCE</label>
                    <input type="text" class="form-control" id="opening_balance" name="opening_balance" placeholder="ENTER OPENING BALANCE" value="">
                </div>
                <div class="mb-4 col-md-4">
                    <label for="opening_balance_type" class="form-label font-14 font-heading">BALANCE TYPE</label>
                    <select class="form-select form-select-lg" name="opening_balance_type" id="opening_balance_type" aria-label="form-select-lg example">
                        <option value="">SELECT BALANCE TYPE</option>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="clearfix"></div>
                <div class="mb-4 col-md-4 gstin_div common_div">
                    <label for="gstin" class="form-label font-14 font-heading">GST NO.</label>
                    <input type="text" class="form-control common_val" id="gstin" name="gstin" placeholder="ENTER GST NO." value="">
                </div>
                <div class="mb-4 col-md-4 state_div common_div">
                    <label for="state" class="form-label font-14 font-heading">STATE</label>
                    <select class="form-select form-select-lg common_val" id="state" name="state" aria-label="form-select-lg example">
                        <option value="">SELECT STATE</option>
                        @foreach($state_list as $value)
                        <option value="{{$value->id}}" data-state_code="{{$value->state_code}}" @if(isset($id) && $account->state==$value->id) selected  @endif>{{$value->state_code}}-{{$value->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="clearfix"></div>
                <div class="mb-4 col-md-8 address_div common_div">
                    <label for="address" class="form-label font-14 font-heading">ADDRESS</label>
                    <input type="text" class="form-control common_val" id="address" name="address" placeholder="ENTER ADDRESS" value="">
                    <input type="text" class="form-control common_val" id="address2" name="address2" placeholder="ENTER ADDRESS" value="">
                    <input type="text" class="form-control common_val" id="address3" name="address3" placeholder="ENTER ADDRESS" value="">
                </div>
                <div class="clearfix"></div>
                <div class="mb-4 col-md-4 pan_div common_div" >
                    <label for="pan" class="form-label font-14 font-heading">PAN</label>
                    <input type="text" class="form-control common_val" id="pan" name="pan" placeholder="Enter PAN" value="">
                </div>
                <div class="mb-4 col-md-4 pincode_div common_div">
                    <label for="pincode" class="form-label font-14 font-heading">PINCODE</label>
                    <input type="number" class="form-control common_val" id="pincode" name="pincode" placeholder="ENTER PINCODE" value="">
                </div>                 
                
                <div class="clearfix"></div>
                
            </div>
        <br>
        <div class="text-start">
            <button type="button" class="btn  btn-xs-primary save_btn">
                SAVE
            </button>
        </div>
        </form>
    </div>
    </div>
</div>
</body>
@include('layouts.footer')
<!-- <script src="public/public/assets/js/vendors/bootstrap.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function(){
        $(".add_bank").click(function(){
            $("#bank_info_modal").modal('toggle');
        });
        $("#account_name").change(function(){
            let account_name = $(this).val();
            $.ajax({
                url: '{{url("check-account-name")}}',
                async: false,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    account_name: account_name
                },
                success: function(data) {
                    if(data==1){
                    alert("Account Name Already Exists.");
                    $(".save_btn").attr('disabled',true);
                    }else{
                    $(".save_btn").attr('disabled',false);
                    }
                }
            });
        });
        $("#gstin").change(function(){
            var inputvalues = $(this).val();
            $("#pan").val("");
            $("#address").val("");
            $("#pincode").val("");
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
                        $("#pincode").val(data.pinCode);
                    }else if(data.status==0){
                        alert(data.message)
                    }
                }               
                }
            });         
        });
        $("#account_name").keyup(function(){
            $("#print_name").val($(this).val())
        });
        $("#opening_balance").keyup(function(){
            $("#opening_balance_type").attr('required',false);
            if($(this).val()!=""){
                $("#opening_balance_type").attr('required',true);
            }
        });
        $('.save_btn').on('click', function(event){
            if($("#account_name").val()=="" || $("#account_no").val()=="" || $("#ifsc_code").val()=="" || $("#bank_name").val()=="" || $("#nature_of_account").val()=="" || $("#branch").val()==""){
                alert("Please Enter Bank Mandatory Fields");
                return
            }
            var formData = new FormData($('#form-job-store')[0]);
                    event.preventDefault();
                    $.ajax({
                        url:"{{route('account.store')}}",
                        method:"POST",
                       
                        data:formData,
                        dataType:'JSON',
                        contentType: false,
                        cache: false,
                        processData: false,
                        success:function(data){
                            if(data.status==true){
                                alert(data.message);
                                location.reload();
                            }else{
                                alert("Something Wrong");
                            }
                        }
                })
            });
        $(".save_btn3").click(function(){
            var formData = new FormData($('#form-job-store')[0]);
            $.ajax({
                url: '{{route("account.store")}}',
                async: false,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    _token: '<?php echo csrf_token() ?>',
                    gstin: formData
                },
                success: function(data) {

                }
            });         
        });
        
    });
</script>
@endsection