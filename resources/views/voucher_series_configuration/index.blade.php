@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2">Voucher Series Configuration</h5>
                </div>
                <div class="bg-white table-view shadow-sm">
		            <nav>
                        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-sale-tab" data-bs-toggle="tab" data-bs-target="#nav-sale" type="button" role="tab" aria-controls="nav-sale" aria-selected="true">Series Configuration</button>
                            <!-- <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">CREDIT NOTE
                            </button>
                            <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">DEBIT NOTE
                            </button> -->
                        </div>
		            </nav>
                    <div class="tab-content p-3 border bg-light" id="nav-tabContent">
                        <div class="tab-pane fade active show" id="nav-sale" role="tabpanel" aria-labelledby="nav-sale-tab">
                        <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('add-series-configuration') }} ">
                            @csrf
                            <div class="row">
                                <div class="mb-3 col-md-3">
                                    <label for="configuration_for" class="form-label font-14 font-heading">Configuration For</label>
                                    <select class="form-select form-select-lg" name="configuration_for" id="configuration_for" aria-label="form-select-lg example">
                                        <option value="">SELECT</option>
                                        <option value="SALE">SALES</option>
                                        <option value="DEBIT NOTE">DEBIT NOTE</option>
                                        <option value="CREDIT NOTE">CREDIT NOTE</option>
                                        <option value="STOCK TRANSFER">STOCK TRANSFER</option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="series" class="form-label font-14 font-heading">SERIES</label>
                                    <select class="form-select form-select-lg" name="series" id="series" aria-label="form-select-lg example">
                                        <option value="">SELECT SERIES</option>
                                        @foreach($series_list as $series)
                                            <option value="{{$series->series}}">{{$series->series}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="manual_numbering" class="form-label font-14 font-heading">MANUAL NUMBERING</label>
                                    <select class="form-select form-select-lg" name="manual_numbering" id="manual_numbering" aria-label="form-select-lg example">
                                        <option value="">SELECT</option>
                                        <option value="YES">YES</option>
                                        <option value="NO">NO</option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="duplicate_voucher" class="form-label font-14 font-heading">DUPLICATE VOUCHER </label>
                                    <select class="form-select form-select-lg manual_number" name="duplicate_voucher" id="duplicate_voucher" aria-label="form-select-lg example" disabled>
                                        <option value="">SELECT</option>
                                        <option value="WARNING ONLY">WARNING ONLY</option>
                                        <option value="DON'T ALLOW">DON'T ALLOW</option>
                                        <option value="ALLOW">ALLOW</option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="blank_voucher" class="form-label font-14 font-heading">BLANK VOUCHER</label>
                                    <select class="form-select form-select-lg manual_number" name="blank_voucher" id="blank_voucher" aria-label="form-select-lg example" disabled>
                                    <option value="">SELECT</option>
                                        <option value="WARNING ONLY">WARNING ONLY</option>
                                        <option value="DON'T ALLOW">DON'T ALLOW</option>
                                        <option value="ALLOW">ALLOW</option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="prefix" class="form-label font-14 font-heading">PREFIX</label>
                                    <select class="form-select form-select-lg auto_number" name="prefix" id="prefix" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="ENABLE">ENABLE</option>
                                        <option value="DISABLE">DISABLE</option>    
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="prefix_value" class="form-label font-14 font-heading">&nbsp</label>
                                    <input type="text" class="form-control auto_number" id="prefix_value" disabled name="prefix_value" placeholder="ENTER PREFIX" onKeyup="invoiceNumber();"/>
                                </div>                                
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="year" class="form-label font-14 font-heading">YEAR</label>
                                    <select class="form-select form-select-lg auto_number" name="year" id="year" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="NOT REQUIRED">NOT REQUIRED</option>
                                        <option value="PREFIX TO NUMBER">PREFIX TO NUMBER</option> 
                                        <option value="SUFFIX TO NUMBER">SUFFIX TO NUMBER</option>    
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="year_format" class="form-label font-14 font-heading">YEAR FORMAT</label>
                                    <select class="form-select form-select-lg auto_number" name="year_format" id="year_format" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="YY-YY">YY-YY</option>
                                        <option value="YYYY-YY">YYYY-YY</option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="suffix" class="form-label font-14 font-heading">SUFFIX</label>
                                    <select class="form-select form-select-lg auto_number" name="suffix" id="suffix" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="ENABLE">ENABLE</option>
                                        <option value="DISABLE">DISABLE</option>    
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="suffix_value" class="form-label font-14 font-heading">&nbsp</label>
                                    <input type="text" class="form-control auto_number" id="suffix_value" disabled name="suffix_value" placeholder="ENTER SUFFIX" onKeyup="invoiceNumber();"/>
                                </div> 
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="number_digit" class="form-label font-14 font-heading">NUMBER DIGITS</label>
                                    <select class="form-select form-select-lg auto_number" name="number_digit" id="number_digit" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option> 
                                        <option value="6">6</option> 
                                        <option value="7">7</option> 
                                        <option value="8">8</option>  
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="separator_1" class="form-label font-14 font-heading">SEPARATOR 1</label>
                                    <select class="form-select form-select-lg auto_number" name="separator_1" id="separator_1" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="/">/</option>
                                        <option value="-">-</option>    
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="separator_2" class="form-label font-14 font-heading">SEPARATOR 2</label>
                                    <select class="form-select form-select-lg auto_number" name="separator_2" id="separator_2" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="/">/</option>
                                        <option value="-">-</option>     
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="separator_3" class="form-label font-14 font-heading">SEPARATOR 3</label>
                                    <select class="form-select form-select-lg auto_number" name="separator_3" id="separator_3" aria-label="form-select-lg example" disabled onChange="invoiceNumber();">
                                        <option value="">SELECT</option>
                                        <option value="/">/</option>
                                        <option value="-">-</option>      
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="final_invoice_no" class="form-label font-14 font-heading"></label>
                                    <input type="text" class="form-control auto_number" id="final_invoice_no" disabled name="final_invoice_no" placeholder="" readonly/>
                                    <p style="color:red;display:none" id="error_msg"><strong>Invalid Invoice No. according to gst act</strong></p>
                                    <p><strong style="color:red;display:none" id="error_msg1"></strong></p>
                                    <input type="hidden" name="max_invoice" id="max_invoice"/>
                                </div>
                                <div class="clearfix"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="invoice_start" class="form-label font-14 font-heading">INVOICE START FROM</label>
                                    <input type="text" disabled class="form-control auto_number" id="invoice_start" name="invoice_start" placeholder="INVOICE START FROM"/>
                                </div>
                            </div>
                            <div class="text-start sale_btn">
                                <button type="submit" class="btn btn-xs-primary">SUBMIT</button>
                            </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                            Purchase
                        </div>
                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                            Debit Note
                        </div>
		            </div>              
	            </div>
            </div>
        </div>
    </section>
</div>
</body>
@include('layouts.footer')
<script>
    $(document).ready(function(){
        $('#manual_numbering').change(function(){
            let manual_numbering = $(this).val();
            if(manual_numbering == 'YES'){
                $('.auto_number').prop('disabled', true);
                $('.auto_number').val('');
                $('.manual_number').prop('disabled', false);
            }else{
                $('.auto_number').prop('disabled', false);
                $('.manual_number').prop('disabled', true);
                $('.manual_number').val('');
            }
            $('#prefix_value').prop('disabled', true);
            $('#suffix_value').prop('disabled', true);
            $('#year_format').prop('disabled', true);
        });
        $('#prefix').change(function(){
            let prefix = $(this).val();
            if(prefix == 'ENABLE'){
                $('#prefix_value').prop('disabled', false);
            }else{
                $('#prefix_value').prop('disabled', true);
                $('#prefix_value').val('');
                $('#separator_1').val('');
            }
        });
        $('#suffix').change(function(){
            let prefix = $(this).val();
            if(prefix == 'ENABLE'){
                $('#suffix_value').prop('disabled', false);
            }else{
                $('#suffix_value').prop('disabled', true);
                $('#suffix_value').val('');
                $('#separator_3').val('');
            }
        });
        $('#year').change(function(){
            let year = $(this).val();
            if(year == 'NOT REQUIRED'){
                $('#year_format').prop('disabled', true);
                $('#year_format').val('');
                $('#separator_2').val('');
            }else{
                $('#year_format').prop('disabled', false);                
            }
        });
        $('#configuration_for').change(function(){
            $('#series').change();
        });
        $('#series').change(function(){
            let series = $(this).val();
            let configuration_for = $('#configuration_for').val();
            if(configuration_for==""){
                alert("Please select configuration for");
                $('#series').val('');
                return;
            }
            $(".sale_btn").show();
            if(series != ''){
                $.ajax({
                    url: "{{ route('series-configuration-by-series') }}",
                    type: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "series": series,
                        "configuration_for": configuration_for
                    },
                    success: function(response){
                        if(response.status == 'success'){
                            $('#manual_numbering').val(response.configuration.manual_numbering);
                            $('#duplicate_voucher').val(response.configuration.duplicate_voucher);
                            $('#blank_voucher').val(response.configuration.blank_voucher);
                            $('#prefix').val(response.configuration.prefix);
                            $('#prefix_value').val(response.configuration.prefix_value);
                            $('#year').val(response.configuration.year);
                            $('#year_format').val(response.configuration.year_format);
                            $('#suffix').val(response.configuration.suffix);
                            $('#suffix_value').val(response.configuration.suffix_value);
                            $('#number_digit').val(response.configuration.number_digit);
                            $('#separator_1').val(response.configuration.separator_1);
                            $('#separator_2').val(response.configuration.separator_2);
                            $('#separator_3').val(response.configuration.separator_3);
                            $('#invoice_start').val(response.configuration.invoice_start);
                            if(response.update_status==1){
                                $(".sale_btn").show();
                            }else{
                                $(".sale_btn").hide();
                            }
                            if(response.configuration.manual_numbering == 'YES'){
                                $('.auto_number').prop('disabled', true);
                                $('.manual_number').prop('disabled', false);
                            }else{
                                $('.auto_number').prop('disabled', false);
                                $('.manual_number').prop('disabled', true);
                            }
                            if(response.configuration.prefix == 'ENABLE'){
                                $('#prefix_value').prop('disabled', false);
                            }else{
                                $('#prefix_value').prop('disabled', true);
                            }
                            if(response.configuration.suffix == 'ENABLE'){
                                $('#suffix_value').prop('disabled', false);
                            }else{
                                $('#suffix_value').prop('disabled', true);
                            }
                            if(response.configuration.year == 'NOT REQUIRED'){
                                $('#year_format').prop('disabled', true);
                            }else{
                                $('#year_format').prop('disabled', false);
                            }
                        }else if(response.status == 'failed'){
                            $('.auto_number').prop('disabled', true);
                            $('.auto_number').val('');
                            $('.manual_number').prop('disabled', true);
                            $('.manual_number').val('');
                            $("#manual_numbering").val('');
                        }
                    }
                });
            }
        });
    });
    function invoiceNumber(){
        $("#error_msg").hide();
        $("#error_msg1").hide();
        $(".sale_btn").show();
        let prefix = $("#prefix").val();
        let prefix_value = $("#prefix_value").val();
        let year = $("#year").val();
        let year_format = $("#year_format").val();
        let suffix = $("#suffix").val();
        let suffix_value = $("#suffix_value").val();
        let number_digit = $("#number_digit").val();
        if(suffix=="DISABLE"){
            $("#separator_3").val("");
        }
        if(prefix=="DISABLE"){
            $("#separator_1").val("");
        }
        if(year=="NOT REQUIRED"){
            $("#separator_2").val("");
        }
        let separator_1 = $("#separator_1").val();
        let separator_2 = $("#separator_2").val();
        let separator_3 = $("#separator_3").val();
        let invoice_number = "";        
        if(prefix=="ENABLE" && prefix_value!=""){
            invoice_number+=prefix_value;
        }        
        if(prefix=="ENABLE" && prefix_value!="" && separator_1!=""){
            invoice_number+=separator_1;
        }
        if(year=="PREFIX TO NUMBER" && year_format!=""){
            invoice_number+=year_format;
        }
        
        if(year=="PREFIX TO NUMBER" && year_format!="" && separator_2!=""){
            invoice_number+=separator_2;
        }
        invoice_number+="001";
        if(year=="SUFFIX TO NUMBER" && year_format!="" && separator_2!=""){
            invoice_number+=separator_2;
        }
        if(year=="SUFFIX TO NUMBER" && year_format!=""){
            invoice_number+=year_format;
        }        
        if(suffix=="ENABLE" && suffix_value!="" && separator_3!=""){
            invoice_number+=separator_3;
        }
        if(suffix=="ENABLE" && suffix_value!=""){
            invoice_number+=suffix_value;
        }       
        $("#final_invoice_no").val(invoice_number);
        if(invoice_number.length>16){
            $("#error_msg").show();
            $(".sale_btn").hide();
        }
        let remainstring = 16-invoice_number.length;
        if(invoice_number.length<=16){
            if(remainstring==0){
                $("#error_msg1").html("Maximum Invoice No. : 999");
                $("#error_msg1").show();
            }else if(remainstring>0){
                // if(number_digit!=""){
                //     if(number_digit<remainstring){
                //         remainstring = number_digit;
                //         $("#error_msg1").html("Maximum Invoice No. : "+("9".repeat(remainstring)));
                //         $("#max_invoice").val("9".repeat(remainstring));
                //         $("#error_msg1").show();                        
                //     }else{
                //         remainstring = remainstring + 3;
                //         if(remainstring>number_digit){
                //             remainstring = number_digit;
                //         }else{
                //             remainstring = remainstring;
                //         }                        
                //         $("#error_msg1").html("Maximum Invoice No. : "+("9".repeat(remainstring)));
                //         $("#max_invoice").val("9".repeat(remainstring));
                //         $("#error_msg1").show(); 
                //     }
                                       
                // }else{                    
                    remainstring = remainstring + 3;
                    $("#error_msg1").html("Maximum Invoice No. : "+("9".repeat(remainstring)));
                    $("#max_invoice").val("9".repeat(remainstring));
                    $("#error_msg1").show();
                //}
                
            }
        }
    }
</script>
@endsection