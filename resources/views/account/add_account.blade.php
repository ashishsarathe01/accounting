@extends('layouts.app')
@section('content')
@include('layouts.header')
<style type="text/css">
   .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 49px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 50px !important;
   }
   .select2-container .select2-selection--single{
      height: 50px !important;
   }
   .select2-container{
          width: 335.519px !important;
   }
   ..select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Account</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="@if(isset($id)){{ route('account.update') }}@else{{ route('account.store') }} @endif">
               @csrf
               <div class="row">  
                  @if(isset($id))   
                     <input type="hidden" value="{{ $account->id }}" id="account_id" name="account_id" />  
                  @endif           
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">ACCOUNT NAME</label>
                     <input type="text" class="form-control" id="account_name" name="account_name" placeholder="ENTER ACCOUNT NAME" required value="@if(isset($id)){{$account->account_name}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4">
                     <label for="print_name" class="form-label font-14 font-heading">PRINT NAME</label>
                     <input type="text" class="form-control" id="print_name" name="print_name" placeholder="ENTER PRINT NAME" required value="@if(isset($id)){{$account->print_name}}@endif">
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4">
                     <label for="under_group" class="form-label font-14 font-heading">UNDER GROUP</label>
                     <select class="form-select form-select-lg select2-single" name="under_group" id="under_group" aria-label="form-select-lg example" required>
                        <option value="">SELECT GROUP</option>
                        @foreach($accountgroup as $value)
                           @php $under_debtor_status = 0;$under_creditors_status = 0; $under_dutytaxes_status = 0;$bank_account_status = 0;$capital_account_status = 0;$loan_status = 0;@endphp
                           @if($value->heading==11 && $value->heading_type=='group')
                              @php  $under_debtor_status = 1; @endphp
                           @endif

                           @if($value->heading==3 && $value->heading_type=='group')
                              @php  $under_creditors_status = 1; @endphp
                           @endif

                           @if($value->heading==1 && $value->heading_type=='group')
                              @php  $under_dutytaxes_status = 1; @endphp
                           @endif

                           @if($value->heading==7 && $value->heading_type=='group')
                              @php  $bank_account_status = 1; @endphp
                           @endif

                           @if($value->heading==18 && $value->heading_type=='group')
                              @php  $capital_account_status = 1; @endphp
                           @endif
                           @if(($value->heading==5 || $value->heading==6) && $value->heading_type=='group')
                              @php  $loan_status = 1; @endphp
                           @endif
                           
                           <option value="{{$value->id}}" data-type="group" data-under_debtor_status="{{$under_debtor_status}}" data-under_creditors_status="{{$under_creditors_status}}" data-under_dutytaxes_status="{{$under_dutytaxes_status}}" data-bank_account_status="{{$bank_account_status}}" data-capital_account_status="{{$capital_account_status}}" data-loan_status="{{$loan_status}}"  @if(isset($id) && $account->under_group==$value->id && $account->under_group_type=='group') selected  @endif>{{$value->name}}</option>
                        @endforeach
                        @foreach($accountheading as $value)
                           <option value="{{$value->id}}" data-type="head" data-under_debtor_status="0" data-under_creditors_status="0" data-under_dutytaxes_status="0" data-bank_account_status="0" data-capital_account_status="0" data-loan_status="0" @if(isset($id) && $account->under_group==$value->id && $account->under_group_type=='head') selected  @endif>{{$value->name}}</option>
                        @endforeach                      
                     </select>
                     <input type="hidden" value="@if(isset($id)){{ $account->under_group_type }}@endif" id="under_group_type" name="under_group_type" /> 
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4">
                     <label for="opening_balance" class="form-label font-14 font-heading">OPENING BALANCE</label>
                     <input type="text" class="form-control" id="opening_balance" name="opening_balance" placeholder="ENTER OPENING BALANCE" value="@if(isset($id)){{$account->opening_balance}}@endif">
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="opening_balance_type" class="form-label font-14 font-heading">BALANCE TYPE</label>
                     <select class="form-select form-select-lg" name="opening_balance_type" id="opening_balance_type" aria-label="form-select-lg example">
                        <option value="">SELECT BALANCE TYPE</option>
                        <option value="debit" @if(isset($id) && $account->dr_cr=='debit') selected  @endif>Debit</option>
                        <option value="credit" @if(isset($id) && $account->dr_cr=='credit') selected  @endif>Credit</option>
                     </select>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 tax_type_div common_div" style="display: none;">
                     <label for="tax_type" class="form-label font-14 font-heading">TAX TYPE</label>
                     <select class="form-select form-select-lg common_val" name="tax_type" id="tax_type" aria-label="form-select-lg example">
                        <option value="">SELECT TAX TYPE</option>
                        <option value="GST" @if(isset($id) && $account->tax_type=='GST') selected  @endif>GST</option>
                        <option value="TDS/TCS" @if(isset($id) && $account->tax_type=='TDS/TCS') selected  @endif>TDS/TCS</option>
                        <option value="ESI" @if(isset($id) && $account->tax_type=='ESI') selected  @endif>ESI</option>
                        <option value="PF" @if(isset($id) && $account->tax_type=='PF') selected  @endif>PF</option>
                        <option value="OTHERS" @if(isset($id) && $account->tax_type=='OTHERS') selected  @endif>OTHERS</option>
                     </select>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 gstin_div common_div" style="display: none;">
                     <label for="gstin" class="form-label font-14 font-heading">GST NO.</label>
                     <input type="text" class="form-control common_val" id="gstin" name="gstin" placeholder="ENTER GST NO."  value="@if(isset($id)){{$account->gstin}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 state_div common_div" style="display: none;">
                     <label for="state" class="form-label font-14 font-heading">STATE</label>
                     <select class="form-select form-select-lg common_val" id="state" name="state" aria-label="form-select-lg example">
                        <option value="">SELECT STATE</option>
                        @foreach($state_list as $value)
                           <option value="{{$value->id}}" data-state_code="{{$value->state_code}}" @if(isset($id) && $account->state==$value->id) selected  @endif>{{$value->state_code}}-{{$value->name}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-8 address_div common_div" style="display: none;">
                     <label for="address" class="form-label font-14 font-heading">ADDRESS</label>
                     <input type="text" class="form-control common_val" id="address" name="address" placeholder="ENTER ADDRESS" value="@if(isset($id)){{$account->address}}@endif"/>
                     <input type="text" class="form-control common_val" id="address2" name="address2" placeholder="ENTER ADDRESS" value="@if(isset($id)){{$account->address2}}@endif"/>
                     <input type="text" class="form-control common_val" id="address3" name="address3" placeholder="ENTER ADDRESS" value="@if(isset($id)){{$account->address3}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 pan_div common_div" style="display: none;">
                     <label for="pan" class="form-label font-14 font-heading">PAN</label>
                     <input type="text" class="form-control common_val" id="pan" name="pan" placeholder="Enter PAN" value="@if(isset($id)){{$account->pan}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 pincode_div common_div" style="display: none;">
                     <label for="pincode" class="form-label font-14 font-heading">PINCODE</label>
                     <input type="number" class="form-control common_val" id="pincode" name="pincode" placeholder="ENTER PINCODE" value="@if(isset($id)){{$account->pin_code}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 due_day_div common_div" style="display: none;">
                     <label for="due_day" class="form-label font-14 font-heading">DUE DAYS</label>
                     <input type="number" class="form-control common_val" id="due_day" name="due_day" placeholder="ENTER DUE DAYS" value="@if(isset($id)){{$account->due_day}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 credit_limit_div common_div" style="display: none;">
                     <label for="credit_limit" class="form-label font-14 font-heading">CREDIT LIMIT</label>
                     <input type="number" class="form-control common_val" id="credit_limit" name="credit_limit" placeholder="ENTER CREDIT LIMIT" value="@if(isset($id)){{$account->credit_limit}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 contact_person_div common_div" style="display: none;">
                     <label for="contact_person" class="form-label font-14 font-heading">CONTACT PERSON</label>
                     <input type="text" class="form-control common_val" id="contact_person" name="contact_person" placeholder="ENTER CONTACT PERSON" value="@if(isset($id)){{$account->contact_person}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 mobile_no_div common_div" style="display: none;">
                     <label for="mobile_no" class="form-label font-14 font-heading">MOBILE NO.</label>
                     <input type="number" class="form-control common_val" id="mobile_no" name="mobile_no" placeholder="ENTER MOBILE NO." value="@if(isset($id)){{$account->mobile}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 whatsapp_no_div common_div" style="display: none;">
                     <label for="whatsapp_no" class="form-label font-14 font-heading">WHATSAPP NO.</label>
                     <input type="number" class="form-control common_val" id="whatsapp_no" name="whatsapp_no" placeholder="ENTER WHATSAPP NO." value="@if(isset($id)){{$account->whatsup_number}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 email_div common_div" style="display: none;">
                     <label for="email" class="form-label font-14 font-heading">E-MAIL ID</label>
                     <input type="email" class="form-control common_val" id="email" name="email" placeholder="ENTER E-MAIL ID" value="@if(isset($id)){{$account->email}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 account_no_div common_div" style="display: none;">
                     <label for="account_no" class="form-label font-14 font-heading">BANK ACCOUNT NO.</label>
                     <input type="number" class="form-control common_val" id="account_no" name="account_no" placeholder="ENTER BANK ACCOUNT NO." value="@if(isset($id)){{$account->bank_account_no}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 ifsc_code_div common_div" style="display: none;">
                     <label for="ifsc_code" class="form-label font-14 font-heading">BANK IFSC CODE</label>
                     <input type="text" class="form-control common_val" id="ifsc_code" name="ifsc_code" placeholder="ENTER BANK IFSC CODE" value="@if(isset($id)){{$account->ifsc_code}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 bank_name_div common_div" style="display: none;">
                     <label for="bank_name" class="form-label font-14 font-heading">BANK NAME</label>
                     <input type="text" class="form-control common_val" id="bank_name" name="bank_name" placeholder="ENTER BANK NAME" value="@if(isset($id)){{$account->bank_name}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 bank_name_div common_div" style="display: none;">
                     <label for="branch" class="form-label font-14 font-heading">BRANCH</label>
                     <input type="text" class="form-control common_val" id="branch" name="branch" placeholder="ENTER BRANCH" value="@if(isset($id)){{$account->branch}}@endif"/>
                  </div>
                  <div class="mb-4 col-md-4 nature_of_account_div common_div" style="display: none;">
                     <label for="nature_of_account" class="form-label font-14 font-heading">NATURE OF ACCOUNT</label>
                     <input type="text" class="form-control common_val" id="nature_of_account" name="nature_of_account" placeholder="ENTER NATURE OF ACCOUNT" value="@if(isset($id)){{$account->nature_of_account}}@endif"/>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 income_tax_class_div common_div" style="display: none;">
                     <label for="income_tax_class" class="form-label font-14 font-heading">INCOME TAX CLASS</label>
                     <select class="form-select form-select-lg common_val" id="income_tax_class" name="income_tax_class" aria-label="form-select-lg example">
                        <option value="">SELECT INCOME TAX CLASS</option>
                        <option value="FURNITURE AND FIXTURES" @if(isset($id) && $account->income_tax_class=='FURNITURE AND FIXTURES') selected  @endif>FURNITURE AND FIXTURES</option>
                        <option value="COMPUTERS" @if(isset($id) && $account->income_tax_class=='COMPUTERS') selected  @endif>COMPUTERS</option>
                        <option value="PLANT AND MACHINERY" @if(isset($id) && $account->income_tax_class=='PLANT AND MACHINERY') selected  @endif>PLANT AND MACHINERY</option>
                        <option value="BUILDING" @if(isset($id) && $account->income_tax_class=='BUILDING') selected  @endif>BUILDING</option>
                        <option value="INTANGIBLE ASSETS" @if(isset($id) && $account->income_tax_class=='INTANGIBLE ASSETS') selected  @endif>INTANGIBLE ASSETS</option>
                        <option value="LAND" @if(isset($id) && $account->income_tax_class=='LAND') selected  @endif>LAND</option>
                        <option value="SHIP" @if(isset($id) && $account->income_tax_class=='SHIP') selected  @endif>SHIP</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4 income_tax_dep_method_div common_div" style="display: none;">
                     <label for="income_tax_dep_method" class="form-label font-14 font-heading">INCOME TAX DEP METHOD</label>
                     <select class="form-select form-select-lg common_val" id="income_tax_dep_method" name="income_tax_dep_method" aria-label="form-select-lg example">
                        <option value="">SELECT INCOME TAX DEP METHOD</option>
                        <option value="WDV" @if(isset($id) && $account->income_tax_dep_method=='WDV') selected  @endif>WDV</option>
                        <option value="SLM" @if(isset($id) && $account->income_tax_dep_method=='SLM') selected  @endif>SLM</option>
                     </select>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 income_tax_dep_rate_div common_div" style="display: none;">
                     <label for="income_tax_dep_rate" class="form-label font-14 font-heading">INCOME TAX DEP RATE</label>
                     <select class="form-select form-select-lg common_val" id="income_tax_dep_rate" name="income_tax_dep_rate" aria-label="form-select-lg example">
                        <option value="">SELECT INCOME TAX DEP RATE</option>
                        <option value="5" @if(isset($id) && $account->income_tax_dep_rate=='5') selected  @endif>5</option>
                        <option value="10" @if(isset($id) && $account->income_tax_dep_rate=='10') selected  @endif>10</option>
                        <option value="15" @if(isset($id) && $account->income_tax_dep_rate=='15') selected  @endif>15</option>
                        <option value="30" @if(isset($id) && $account->income_tax_dep_rate=='30') selected  @endif>30</option>
                        <option value="40" @if(isset($id) && $account->income_tax_dep_rate=='40') selected  @endif>40</option>
                        <option value="45" @if(isset($id) && $account->income_tax_dep_rate=='45') selected  @endif>45</option>
                        <option value="100" @if(isset($id) && $account->income_tax_dep_rate=='100') selected  @endif>100</option>
                     </select>
                  </div>                
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">STATUS</label>
                     <select class="form-select form-select-lg" name="status" id="status" aria-label="form-select-lg example" required>
                        <option value="1" @if(isset($id) && $account->status=='1') selected  @endif>Enable</option>
                        <option value="0" @if(isset($id) && $account->status=='0') selected  @endif>Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn btn-xs-primary save_btn">SUBMIT</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>

@include('layouts.footer')
<script>
   var edit_id = "@if(isset($id)){{$id}} @endif";
   $(document).ready(function(){    
      $( ".select2-single, .select2-multiple" ).select2();   
      $("#under_group").change();
      $("#account_name").keyup(function(){
         $("#print_name").val($(this).val())
      });
      $("#opening_balance").keyup(function(){
         $("#opening_balance_type").attr('required',false);
         if($(this).val()!=""){
            $("#opening_balance_type").attr('required',true);
         }
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
   });
   $("#under_group").change(function(){
      $(".common_div").hide();
      $("#state").attr('required',false);
      $("#tax_type").attr('required',false);
      if(edit_id==""){
         $(".common_val").val('');
      }            
      if(($(this).val()==1 && $('option:selected', this).attr('data-type')=='group') || ($('option:selected', this).attr('data-under_dutytaxes_status')=='1')){
         $(".tax_type_div").show();
      }else if(($(this).val()==11 && $('option:selected', this).attr('data-type')=='group') || $('option:selected', this).attr('data-under_debtor_status')=='1'){
         $("#state").attr('required',true);
         $(".gstin_div").show();
         $(".state_div").show();
         $(".address_div").show();
         $(".pan_div").show();
         $(".pincode_div").show();
         $(".due_day_div").show();
         $(".credit_limit_div").show();
         $(".contact_person_div").show();
         $(".mobile_no_div").show();
         $(".whatsapp_no_div").show();
         $(".email_div").show();
         $(".account_no_div").show();
         $(".ifsc_code_div").show();
      }else if(($(this).val()==3 && $('option:selected', this).attr('data-type')=='group') || ($(this).val()==10 && $('option:selected', this).attr('data-type')=='head') || $('option:selected', this).attr('data-under_creditors_status')=='1'){
         $("#state").attr('required',true);
         $(".gstin_div").show();
         $(".state_div").show();
         $(".address_div").show();
         $(".pan_div").show();
         $(".pincode_div").show();
         $(".due_day_div").show();         
         $(".contact_person_div").show();
         $(".mobile_no_div").show();
         $(".whatsapp_no_div").show();
         $(".email_div").show();
         $(".account_no_div").show();
         $(".ifsc_code_div").show();
      }else if(($(this).val()==7 && $('option:selected', this).attr('data-type')=='group') || ($('option:selected', this).attr('data-bank_account_status')=='1')){
         $("#state").attr('required',true);
         $(".gstin_div").show();
         $(".state_div").show();
         
         $(".address_div").show();
         $(".pan_div").show();
         $(".pincode_div").show();
         $(".account_no_div").show();
         $(".ifsc_code_div").show();
         $(".nature_of_account_div").show();
         $(".bank_name_div").show();
      }else if(($(this).val()==18 && $('option:selected', this).attr('data-type')=='group') || ($('option:selected', this).attr('data-capital_account_status')=='1')){
         $(".account_no_div").show();
         $(".ifsc_code_div").show();
         $(".mobile_no_div").show();
         $(".email_div").show();
      }else if((($(this).val()==5 || $(this).val()==6) && $('option:selected', this).attr('data-type')=='group') || ($('option:selected', this).attr('data-loan_status')=='1')){
         $(".account_no_div").show();
         $(".ifsc_code_div").show();
         $(".mobile_no_div").show();
         $(".email_div").show();
      }else if($(this).val()==6 && $('option:selected', this).attr('data-type')=='head'){
         $(".income_tax_class_div").show();
         $(".income_tax_dep_method_div").show();
         $(".income_tax_dep_rate_div").show();
      }else if($(this).val()==1 && $('option:selected', this).attr('data-type')=='group'){
         $("#tax_type").attr('required',true);
         
      }
      $("#under_group_type").val($('option:selected', this).attr('data-type'));
      
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
</script>
@endsection
