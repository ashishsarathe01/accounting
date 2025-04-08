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
                
                <!-- Display validation errors -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <?php 
                // echo "<pre>";
                //     print_r($company_info);
                ?>
                <!-- company info--------------------------------------------------------------------- -->
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">Add Company
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-company') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Business Structure</label>
                            <select name="business_type" id="business_type" class="form-select form-select-lg mb-3" aria-label="form-select-lg example" required>
                                <option value="">Select</option>
                                <option value="1" <?php if(!empty($company_info) && $company_info->business_type=="1"){ echo "selected";} ?>>Proprietorship</option>
                                <option value="2" <?php if(!empty($company_info) && $company_info->business_type=="2"){ echo "selected";} ?>>Partnership</option>
                                <option value="3" <?php if(!empty($company_info) && $company_info->business_type=="3"){ echo "selected";} ?>>Company (PVT LTD)</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">GST Applicable</label>
                            <select class="form-select" id="gst_applicable" name="gst_applicable" required>
                                <option value="">Select</option>
                                <option value="1" <?php if(!empty($company_info) && $company_info->gst_applicable=="1"){ echo "selected";} ?>>Yes</option>
                                <option value="0" <?php if(!empty($company_info) && $company_info->gst_applicable=="0"){ echo "selected";} ?>>No</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-3" id="gst_filed">
                            <label for="name" class="form-label font-14 font-heading">GSTN (If Yes)</label>
                            <input type="text" class="form-control" id="gst" name="gst" placeholder="Enter GSTN" value="<?php if(!empty($company_info) && $company_info->gst){ echo $company_info->gst;} ?>"/>
                        </div>
                        <div class="mb-1 col-md-1" id="gst_filed1">
                            <button type="button" class="btn btn-info btn-sm validate_gst" style="margin-top: 26px;">Validate</button>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Firm’s Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter firm’s name" required value="<?php if(!empty($company_info) && $company_info->company_name){ echo $company_info->company_name;} ?>"/>
                        </div>
                        <div class="mb-8 col-md-8">
                            <label for="name" class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" value="<?php if(!empty($company_info) && $company_info->address){ echo $company_info->address;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">State</label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select</option>
                                <?php
                                foreach ($state_list as $val) { ?>
                                <option value="<?= $val->id;?>" data-state_code="{{$val->state_code}}" <?php if(!empty($company_info) && $company_info->state==$val->id){ echo "selected";} ?>><?= $val->name;?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Pin Code</label>
                            <input type="number" class="form-control" id="pin_code" name="pin_code" placeholder="Enter pin code" value="<?php if(!empty($company_info) && $company_info->pin_code){ echo $company_info->pin_code;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">Country Name</label>
                            <select class="form-select" id="country_name" name="country_name" required>
                                <option value="">Select</option>
                                <option value="INDIA" <?php if($company_info && !empty($company_info) && $company_info->country_name=="INDIA"){ echo "selected";} ?>>INDIA</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">PAN</label>
                            <input type="text" class="form-control" id="pan" name="pan" placeholder="Enter PAN" value="<?php if(!empty($company_info) && $company_info->pan){ echo $company_info->pan;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4 cin_div" style="display:none">
                            <label for="name" class="form-label font-14 font-heading">CIN</label>
                            <input type="text" class="form-control" id="cin" name="cin" placeholder="Enter CIN" value="<?php if(!empty($company_info) && $company_info->cin){ echo $company_info->cin;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date of Incorporation</label>
                            <input type="date" class="form-control" id="date_of_incorporation" name="date_of_incorporation" placeholder="Select date" value="<?php if(!empty($company_info) && $company_info->date_of_incorporation){ echo $company_info->date_of_incorporation;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Books start From</label>
                            <input type="date" class="form-control" id="books_start_from" name="books_start_from" placeholder="Enter books start from" min="2022-04-01" value="<?php if(!empty($company_info) && $company_info->books_start_from){ echo $company_info->books_start_from;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Current FinancialYear</label>
                           <select class="form-select " id="current_finacial_year" name="current_finacial_year" required>
                              <option value="">Select</option>
                              <?php 
                              $y = 22;
                              while($y<=date('y')){
                                 $selected = "";
                                 $y1 = $y+1;
                                 if(!empty($company_info) &&        $company_info->current_finacial_year==$y."-".$y1){ 
                                    $selected = "selected";
                                }
                                 ?>
                                 <option value="<?php echo $y."-".$y1;?>" <?php echo $selected;?>><?php echo $y."-".$y1;?></option>
                                 <?php
                                 $y++;
                              }
                              ?>                              
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Email ID</label>
                            <input type="email" class="form-control" id="email_id" name="email_id" placeholder="Enter email ID" required value="<?php if(!empty($company_info) && $company_info->email_id){ echo $company_info->email_id;}else if($user && !empty($user) && $user->email){ echo $user->email;} ?>"/>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                            <div class="position-relative">
                                <input type="text" min="10" max="10" class="form-control" id="mobile_no" name="mobile_no" placeholder="Enter mobile number" required value="<?php if(!empty($company_info) && $company_info->mobile_no){ echo $company_info->mobile_no;}else if($user && !empty($user) && $user->mobile_no){ echo $user->mobile_no;} ?>"/>
                                <!--<span class="position-absolute number-divider font-14">+91</span>
                                <button type="button" class="btn btn-link-primary border-0 font-12 position-absolute verify-button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    VERIFY
                                </button>-->
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <input type="submit" value="NEXT" class="btn btn-heading mb-4">
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

<script type="text/javascript">
    $("#gst_applicable").on("keyup change", function(e) {
        if ($("#gst_applicable").val() == 1) {
            $("#gst_filed").show();
            $("#gst_filed1").show();
        } else {
            $("#gst_filed").hide();
            $("#gst_filed1").hide();
        }
    });
    $(".validate_gst").click(function() {
        $("#cover-spin").show();
        var inputvalues = $("#gst").val();
        var gstinformat = new RegExp("^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9]{1}Z[a-zA-Z0-9]{1}$");
        if (gstinformat.test(inputvalues)) {
            var GstateCode = inputvalues.substr(0, 2); 
            //$('#state').val(GstateCode);
            $('#country_name').val('INDIA');
            var GpanNum = inputvalues.substring(2, 12);
            $("#pan").val(GpanNum);
            var GEnd = inputvalues.substring(12,14);
            $("#pan").val("");
            $("#address").val("");
            $("#pin_code").val("");
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
                            $("#pin_code").val(data.pinCode);
                            $("#company_name").val(data.tradeName);
                            $("#date_of_incorporation").val(data.DtReg);
                        }else if(data.status==0){
                            alert(data.message)
                        }
                        $("#cover-spin").hide();
                    }               
                }
            }); 
            return true;
        }else {
            alert('Please Enter Valid GSTIN Number');
            $("#gst").focus();
            $("#cover-spin").hide();
        }
    });
    $("#gst").keyup(function(){
        $("#gst").val($(this).val().toUpperCase());
    });
    $("#books_start_from").change(function() {
        let date = $(this).val();
        date = new Date(date);
        var financialYear;
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        year = year % 100;
        if (month >= 4) {
            financialYear = year + '-' + (year + 1);
        } else {
            financialYear = (year - 1) + '-' + year;
        }
        $("#current_finacial_year").val(financialYear);
    });
    $("#pin_code").change(function() {
        var inputvalues = $("#pin_code").val();
        var pincode = new RegExp("^[1-9]{1}[0-9]{2}\\s{0,1}[0-9]{3}$");
        if (pincode.test(inputvalues)) {
            return true;
        } else {
            alert('Please Enter Valid pin Number');
            $("#pin_code").val('');
            $("#pin_code").focus();
        }
    });
    $("#business_type").change(function(){
        $(".cin_div").hide();
        if($(this).val()=="3"){
            $(".cin_div").show();
        }
    });
    $("#email_id").keyup(function(){
        $("#email_id").val($(this).val().toLowerCase());
    });
    
</script>
@endsection