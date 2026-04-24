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
                <h3 class="mb-3" style="text-align: center">PART A</h3>
                  @if(isset($id))   
                     <input type="hidden" value="{{ $account->id }}" id="account_id" name="account_id" />  
                  @endif           
                  <div class="mb-4 col-md-4">
                     <label for="account_name" class="form-label font-14 font-heading">ACCOUNT NAME</label>
                     <input type="text" class="form-control" id="account_name" name="account_name" placeholder="ENTER ACCOUNT NAME" required value="@if(isset($id)){{$account->account_name}}@endif"  @if(isset($account) && $account->company_id==0) readonly @endif/>
                     <input type="hidden" name="company_id" id="company_id" value="{{$formCompanyId}}"/>
                     <ul style="color: red;">
                       @error('account_name'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4">
                     <label for="print_name" class="form-label font-14 font-heading">PRINT NAME</label>
                     <input type="text" class="form-control" id="print_name" name="print_name" placeholder="ENTER PRINT NAME" required value="@if(isset($id)){{$account->print_name}}@endif" @if(isset($account) && $account->company_id==0) readonly @endif>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4" @if(isset($account) && $account->company_id==0) style="display:none" @endif>
                     <label for="under_group" class="form-label font-14 font-heading">UNDER GROUP</label>
                     <select class="form-select form-select-lg select2-single" name="under_group" id="under_group" aria-label="form-select-lg example" required>
                        <option value="">SELECT GROUP</option>
                        @foreach($accountgroup as $value)
                           @php $under_debtor_status = 0;$under_creditors_status = 0; $under_dutytaxes_status = 0;$bank_account_status = 0;$capital_account_status = 0;$loan_status = 0; $under_expense_status=0;@endphp
                           @if($value->super_parent_id==11 && $value->heading_type=='group')
                              @php  $under_debtor_status = 1; @endphp
                           @endif

                           @if($value->super_parent_id==3 && $value->heading_type=='group')
                              @php  $under_creditors_status = 1; @endphp
                           @endif
                           
                           
                           @if($value->super_parent_id==12 || $value->super_parent_id==15 && $value->heading_type=='group')
                              @php  $under_expense_status = 1; @endphp
                           @endif

                           @if($value->super_parent_id==1 && $value->heading_type=='group')
                              @php  $under_dutytaxes_status = 1; @endphp
                           @endif

                           @if($value->super_parent_id==7 && $value->heading_type=='group')
                              @php  $bank_account_status = 1; @endphp
                           @endif

                           @if($value->super_parent_id==18 && $value->heading_type=='group')
                              @php  $capital_account_status = 1; @endphp
                           @endif
                           @if(($value->super_parent_id==5 || $value->super_parent_id==6) && $value->heading_type=='group')
                              @php  $loan_status = 1; @endphp
                           @endif
                           
                           <option value="{{$value->id}}" data-type="group" data-under_expense_status="{{$under_expense_status}}" data-under_debtor_status="{{$under_debtor_status}}" data-under_creditors_status="{{$under_creditors_status}}" data-under_dutytaxes_status="{{$under_dutytaxes_status}}" data-bank_account_status="{{$bank_account_status}}" data-capital_account_status="{{$capital_account_status}}" data-loan_status="{{$loan_status}}"  @if(isset($id) && $account->under_group==$value->id && $account->under_group_type=='group') selected  @endif>{{$value->name}}</option>
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
                  
                    <div id="rcmDiv" class="rcm_div row" style="display:none;">

                  <!-- RCM Yes / No -->
                     <div class="mb-4 col-md-4">
                        <label class="form-label fw-semibold">Reverse Charge (RCM)</label>
                        <select name="rcm" class="form-select">
                              <option value="">-- Select RCM --</option>
                              <option value="1" @if(isset($id) && $account->rcm==1) selected  @endif>Yes</option>
                              <option value="0" @if(isset($id) && $account->rcm ==0) selected @endif>No</option>
                        </select>
                     </div>

                     <!-- RCM Rate -->
                     <div class="mb-4 col-md-4 rcm-rate-div" style="display:none;">
                        <label class="form-label fw-semibold">RCM Rate</label>
                        <select name="rcm_rate" class="form-select">
                           <option value="">Select RCM Rate</option>
                           <option value="5"  @if(isset($id) && $account->rcm_rate == 5) selected @endif>5%</option>
                           <option value="18" @if(isset($id) && $account->rcm_rate == 18) selected @endif>18%</option>
                           <option value="28" @if(isset($id) && $account->rcm_rate == 28) selected @endif>28%</option>
                        </select>
                     </div>



                  </div>


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
                        <div class="input-group">
                        <input type="text" class="form-control common_val" id="gstin" name="gstin" placeholder="ENTER GST NO."  value="@if(isset($id)){{$account->gstin}}@endif"/>
                        <button type="button" class="btn btn-info btn-sm" id="validateGSTIN">Validate</button>
                        </div>
                        <ul style="color: red;">
                            @error('gstin'){{$message}}@enderror                        
                        </ul>
                    </div>
                  
                  @php
                     $hasGstin = isset($account) && !empty($account->gstin);
                  @endphp
                 <div class="mb-4 col-md-4 state_div common_div" style="display: none;">
                           <label for="state" class="form-label font-14 font-heading">STATE</label>

                           <select class="form-select form-select-lg common_val select2-single" 
                                    id="state"
                                    name="state_visible"
                                    aria-label="form-select-lg example">
                              <option value="">SELECT STATE</option>
                              @foreach($state_list as $value)
                                    <option value="{{ $value->id }}" 
                                          data-state_code="{{ $value->state_code }}" 
                                          @if(isset($id) && $account->state == $value->id) selected @endif>
                                       {{ $value->state_code }} - {{ $value->name }}
                                    </option>
                              @endforeach
                           </select>

                           <!-- Hidden field to store actual submitted value -->
                           <input type="hidden" name="state" id="state_hidden" value="@if(isset($id)){{ $account->state }}@endif">
                 </div>

                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-7 address_div common_div" style="display: none;">
                     <label for="address" class="form-label font-14 font-heading">ADDRESS</label>
                     <textarea class="form-control common_val" 
                          id="address" 
                          name="address" 
                          placeholder="ENTER ADDRESS" 
                          maxlength="100" 
                  rows="2">@if(isset($id)){{$account->address}}@endif</textarea>
                 </div>
                  <div class="mb-2 col-md-2 pincode_div common_div" style="display: none;">
                     <label for="pincode" class="form-label font-14 font-heading">PINCODE</label>
                     <input type="number" class="form-control common_val" id="pincode" name="pincode" placeholder="ENTER PINCODE" value="@if(isset($id)){{$account->pin_code}}@endif"/>
                  </div>
                  <div class="mb-2 col-md-2 pincode_div common_div" style="display: none;">
                     <label for="location" class="form-label font-14 font-heading">LOCATION/STATION</label>
                     <input type="text" class="form-control common_val" id="location" name="location" placeholder="ENTER STATION" value="@if(isset($id)){{$account->location}}@endif"/>
                  </div>
                  <div class="mb-2 col-md-1 pincode_div common_div" style="display: none;">
                     <svg style="color: green;cursor: pointer;margin-top: 42px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"tabindex="0" class="bg-primary rounded-circle add_address" data-id="" viewBox="0 0 24 24">
                        <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                      </svg>
                  </div>
                  <div class="clearfix"></div>
                  <div class="address-wrapper">
                     @if(isset($other_address) && count($other_address)>0)
                        @foreach($other_address as $address)
                           <div class="clearfix added-address row">
                              <div class="mb-4 col-md-7 address_div common_div">
                                 <label class="form-label font-14 font-heading">ADDRESS</label>
                                 <textarea class="form-control common_val" name="other_address[]" placeholder="ENTER ADDRESS" maxlength="100" rows="2">{{$address->address}}</textarea> 
                              </div>
                              <div class="mb-2 col-md-2 pincode_div common_div">
                                 <label class="form-label font-14 font-heading">PINCODE</label>
                                 <input type="number" class="form-control common_val" name="other_pincode[]" placeholder="ENTER PINCODE" value="{{$address->pincode}}"/> 
                              </div>
                              <div class="mb-2 col-md-2 pincode_div common_div">
                                 <label class="form-label font-14 font-heading">LOCATION/STATION</label>
                                 <input type="text" class="form-control common_val" name="other_location[]" placeholder="ENTER STATION" value="{{$address->location}}"/> 
                              </div>
                              <div class="mb-2 col-md-1 pincode_div common_div">
                                 <svg style="color: red;cursor: pointer;margin-top: 42px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bg-danger rounded-circle remove_address" viewBox="0 0 24 24">
                                    <path d="M19 13H5V11H19V13Z" fill="white"/>
                                 </svg>
                              </div>
                           </div>
                           @endforeach
                           @endif

                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-4 col-md-4 pan_div common_div" style="display: none;">
                     <label for="pan" class="form-label font-14 font-heading">PAN</label>
                     <input type="text" class="form-control common_val" id="pan" name="pan" placeholder="Enter PAN" value="@if(isset($id)){{$account->pan}}@endif"/>
                  </div>
                   <!-- SMS Send Status -->
                  <div class="mb-4 col-md-4 sms_status_div common_div" style="display:none;">
                     <label class="form-label">SMS Send Status</label>
                     <select class="form-select" name="sms_status" id="sms_status">
                        <option value="">Select</option>
                        <option value="Yes" @if(isset($id) && $account->sms_status=="1") selected  @endif>Yes</option>
                        <option value="No" @if(isset($id) && $account->sms_status=="0") selected  @endif>No</option>
                     </select>
                  </div>

                  <div class="mb-4 col-md-4 credit_day_select_div common_div" style="display:none;">
                     <label class="form-label">Credit Days</label>
                     <select class="form-select" name="credit_days" id="credit_days">
                        <option value="">Select</option>
                        @foreach($credit_days as $cd)
                              <option value="{{ $cd->days }}" @if(isset($id) && $account->credit_days==$cd->days) selected  @endif>{{ $cd->days }} Days</option>
                        @endforeach
                     </select>
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
                  <div class="mb-4 col-md-4" @if(isset($account) && $account->company_id==0) style="display:none" @endif>
                     <label for="status" class="form-label font-14 font-heading">STATUS</label>
                     <select class="form-select form-select-lg" name="status" id="status" aria-label="form-select-lg example" required>
                        <option value="1" @if(isset($id) && $account->status=='1') selected  @endif>Enable</option>
                        <option value="0" @if(isset($id) && $account->status=='0') selected  @endif>Disable</option>
                     </select>
                  </div>
               </div>
               <h3 class="mb-3" style="text-align: center">PART B</h3>
               <div class="col-md-4">                    
                    <div id="tds_part_b" style="display:none;">
                        <div class="mb-3 row">
                            <label class="col-5 col-form-label">TDS/TCS</label>
                            <div class="col-7">
                                <select name="tds_tcs" id="tds_tcs" class="form-select">
                                    <option value="">Select</option>
                                    <option value="yes" @if(isset($account) && $account->tds_tcs=='yes') selected @endif>YES</option>
                                    <option value="no" @if(isset($account) && $account->tds_tcs=='no') selected @endif>NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row" id="tds_type_row" style="display:none;">
                            <label class="col-5 col-form-label">TYPE</label>
                            <div class="col-7">
                                <select name="tds_type" id="tds_type" class="form-select">
                                    <option value="">Select</option>
                                    <option value="salary" @if(isset($account) && $account->tds_type=='salary') selected @endif>Salary</option>
                                    <option value="non_salary" @if(isset($account) && $account->tds_type=='non_salary') selected @endif>Non Salary</option>
                                </select>
                            </div>
                        </div>
                        <div id="non_salary_section" style="display:none;">
                            <div class="mb-3 row">
                                <label class="col-5 col-form-label">Section</label>
                                <div class="col-7">
                                    <select name="tds_section" id="tds_section" class="form-select">
                                        <option value="">Select</option>
                                        @foreach($tds_sections as $sec)
                                            <option
                                            value="{{ $sec->id }}"
                                            data-description="{{ $sec->description }}"
                                            data-rate="{{ $sec->rate_individual_huf }}"
                                            data-threshold="{{ $sec->single_transaction_limit }}"
                                            @if(isset($account) && $account->tds_section==$sec->id) selected @endif
                                            >
                                            {{ strtoupper($sec->section) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-5 col-form-label">Description</label>
                                <div class="col-7">
                                    <input type="text" id="tds_description" name="tds_description" value="@if(isset($account)){{$account->tds_description}}@endif" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-5 col-form-label">Rate</label>
                                <div class="col-7">
                                    <input type="text" id="tds_rate" name="tds_rate" value="@if(isset($account)){{$account->tds_rate}}@endif" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-5 col-form-label">Threshold</label>
                                <div class="col-7">
                                    <input type="text" id="tds_threshold" name="tds_threshold" value="@if(isset($account)){{$account->tds_threshold}}@endif" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
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
    window.hasGstinOnLoad = {{ $hasGstin ? 'true' : 'false' }};

    function syncStateValue() {
    $("#state_hidden").val($("#state").val());
}

$(document).ready(function() {
    // Initialize Select2
    $(".select2-single, .select2-multiple").select2();

    // Trigger change on page load for under_group
    $("#under_group").change();

    // Sync account_name to print_name
    $("#account_name").keyup(function() {
        $("#print_name").val($(this).val());
    });

    // Make opening_balance_type required only if opening_balance has value
    $("#opening_balance").keyup(function() {
        $("#opening_balance_type").attr('required', false);
        if ($(this).val() !== "") {
            $("#opening_balance_type").attr('required', true);
        }
    });


       syncStateValue();

    // Sync when state changes (user or JS)
    $("#state").on("change", function() {
        syncStateValue();
    });

    if (window.hasGstinOnLoad) {
        $('#state').on('select2:opening', function(e) {
            e.preventDefault(); // prevents dropdown from opening
        }).css({
            'pointer-events': 'none',
            'background-color': '#e9ecef' // Bootstrap gray
        });
    }

    // GSTIN input event to enable/disable state
      $("#gstin").on('input', function () {
        let gstin = $(this).val().trim();
        if (gstin === "") {
            // Re-enable dropdown
            $("#state").off('select2:opening').css('pointer-events', 'auto');
        }
    });

    $("#gstin").change(function () {
        var inputvalues = $(this).val().trim();
        $("#pan").val("");
        $("#address").val("");
        $("#pincode").val("");
        $("#state").val("").trigger('change');
        $("#state").css('pointer-events', 'auto');

        if (inputvalues === "") return;
        checkGSTIN(inputvalues);
        
    });
});
$("#validateGSTIN").click(function(){
    var inputvalues = $("#gstin").val().trim();
    $("#pan").val("");
    $("#address").val("");
    $("#pincode").val("");
    $("#state").val("").trigger('change');
    $("#state").css('pointer-events', 'auto');

    if (inputvalues === "") return;
    checkGSTIN(inputvalues);
})
function checkGSTIN(inputvalues){
    //$("#cover-spain").show();
    $.ajax({
            url: '{{ url("check-gstin") }}',
            async: false,
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: inputvalues
            },
            success: function (data) {
                if (data && data.status == 1) {
                    var GstateCode = inputvalues.substr(0, 2);
                    var matchedValue = $('#state option[data-state_code="' + GstateCode + '"]').val();
                    if (matchedValue) {
                        $('#state').val(matchedValue).trigger('change');
                        $('#state').on('select2:opening', function(e) {
                            e.preventDefault();
                        }).css({
                            'pointer-events': 'none',
                            'background-color': '#e9ecef'
                        });
                    }

                    var GpanNum = inputvalues.substring(2, 12);
                    $("#pan").val(GpanNum);
                    $("#address").val(data.address.toUpperCase());
                    $("#pincode").val(data.pinCode);

                    syncStateValue(); // update hidden input
                } else if (data.status == 0) {
                    alert(data.message);
                }
            }
        });
}
   $("#under_group").change(function(){
      $(".rcm_div")
        .find("select")
        .val("")
        .removeAttr("required");

         $(".rcm_div").hide();
         $(".rcm-rate-div").hide();

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
         $(".sms_status_div").show();
         $(".credit_day_select_div").show();
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
         $(".sms_status_div").show();
         $(".credit_day_select_div").show();
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
         
      }else if ((($(this).val() == 12 || $(this).val() == 15) && $('option:selected', this).data('type') == 'group') || $('option:selected', this).data('under_expense_status') == 1 ) { 
         $(".rcm_div").show(); 
         $(".rcm_div").find('select, input').prop('required', true); 
     }
         
        $("#under_group_type").val($('option:selected', this).attr('data-type'));
        if($(this).val() !== ""){
        $("#tds_part_b").show();
        }else{
            $("#tds_part_b").hide();
        }
      
   });
   $("#account_name").change(function(){
   let account_name = $(this).val();
   let company_id = $('#company_id').val();

   $.ajax({
      url: '{{ url("check-account-name") }}',
      type: 'POST',
      dataType: 'JSON',
      data: {
         _token: '{{ csrf_token() }}',
         account_name: account_name,
         company_id: company_id
      },
      success: function(data) {
         if(data == 1){
            alert("Account Name Already Exists.");
         }else{
            $(".save_btn").prop('disabled', false);
         }
      }
   });
});

   $(document).on('click', '.add_address', function() {
      let newAddressBlock = `
        <div class="clearfix added-address row">
            <div class="mb-4 col-md-7 address_div common_div">
                <label class="form-label font-14 font-heading">ADDRESS</label>
                <textarea class="form-control common_val" name="other_address[]" placeholder="ENTER ADDRESS" maxlength="100" rows="2"></textarea>
            </div>
            <div class="mb-2 col-md-2 pincode_div common_div">
                <label class="form-label font-14 font-heading">PINCODE</label>
                <input type="number" class="form-control common_val" name="other_pincode[]" placeholder="ENTER PINCODE" />
            </div>
            <div class="mb-2 col-md-2 pincode_div common_div">
                <label class="form-label font-14 font-heading">STATION/LOCATION</label>
                <input type="text" class="form-control common_val" name="other_location[]" placeholder="ENTER STATION" />
            </div>
            <div class="mb-2 col-md-1 pincode_div common_div">
                <svg style="color: red;cursor: pointer;margin-top: 42px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bg-danger rounded-circle remove_address" viewBox="0 0 24 24">
                    <path d="M19 13H5V11H19V13Z" fill="white"/>
                </svg>
            </div>
        </div>
        `;
        $('.address-wrapper').append(newAddressBlock);
   });
   $(document).on('click', '.remove_address', function() {
        $(this).closest('.added-address').remove();
    });
    
      $(document).on('change', 'select[name="rcm"]', function () {

    let rcmRateDiv = $('.rcm-rate-div');
    let rcmRateSelect = $('select[name="rcm_rate"]');

    if ($(this).val() == '1') {
        rcmRateDiv.show();
        rcmRateSelect.attr('required', true);
    } else {
        rcmRateSelect.val('');
        rcmRateSelect.removeAttr('required');
        rcmRateDiv.hide();
    }
});


$(document).ready(function () {
    $('select[name="rcm"]').trigger('change');
});

$(document).ready(function () {

    // Trigger under group logic
    $("#under_group").trigger('change');

    // If editing and RCM = Yes
    @if(isset($id) && $account->rcm == 1)
        $(".rcm_div").show();
        $(".rcm-rate-div").show();
        $('select[name="rcm_rate"]').attr('required', true);
    @endif

});

$(document).ready(function () {

    // Re-sync Select2 values on edit
    @if(isset($id))
        $('select[name="rcm"]').val('{{ $account->rcm }}').trigger('change.select2');
        $('select[name="rcm_rate"]').val('{{ $account->rcm_rate }}').trigger('change.select2');
    @endif

});
    $("#gstin").on("blur", function () {
      let gstin = $(this).val().trim();
      if (gstin === "") return;

      $.ajax({
         url: '{{ url("check-gstin-exists") }}',
         type: 'POST',
         dataType: 'JSON',
         data: {
               _token: '{{ csrf_token() }}',
               gstin: gstin,
               account_id: $("#account_id").val() || null
         },
         success: function (res) {
               if (res.exists === true) {
                  alert("This GST Number already exists. Please enter a new GST Number.");
                  $("#gstin").val("").focus();
                  $("#pan").val("");
                  $("#address").val("");
                  $("#pincode").val("");
                  $("#state").val("").trigger('change');
                  $("#gstin").data("duplicate", true);
               } else {
                  $("#gstin").data("duplicate", false);
               }
         }
      });
   });
    $("#tds_tcs").change(function(){
        if($(this).val()=="yes"){
            $("#tds_type_row").show();
        }else{
            $("#tds_type_row").hide();
            $("#non_salary_section").hide();
            // CLEAR VALUES
            $("#tds_type").val("");
            $("#tds_section").val("");
            $("#tds_description").val("");
            $("#tds_rate").val("");
            $("#tds_threshold").val("");
        }
    });
    $("#tds_type").change(function(){
        if($(this).val()=="non_salary"){
            $("#non_salary_section").show();
        }else{
            $("#non_salary_section").hide();
            // CLEAR VALUES
            $("#tds_section").val("");
            $("#tds_description").val("");
            $("#tds_rate").val("");
            $("#tds_threshold").val("");
        }
    });
    $("#tds_section").change(function(){
        var selected=$(this).find(':selected');
        $("#tds_description").val(selected.data('description'));
        $("#tds_rate").val(selected.data('rate'));
        $("#tds_threshold").val(selected.data('threshold'));
    });
    $(document).ready(function(){
        @if(isset($account) && $account->tds_tcs == 'yes')
            $("#tds_part_b").show();
            $("#tds_type_row").show();
            @if($account->tds_type == 'non_salary')
                $("#non_salary_section").show();
            @endif
        @endif
    });
</script>
@endsection
