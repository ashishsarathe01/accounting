@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
    /* ===== SLIGHTLY MORE VISIBLE SECTION SEGREGATION ===== */

.bg-mint {
    background: #f1f5f9 !important; /* slightly clearer page bg */
}

/* All major sections */
#employee_sections > div,
#accountant_section,
#ca_section,
#other_section {
    background: #f8fafc;  /* slightly stronger than before */
    border: 1px solid #dbe3ec; /* little more visible border */
    border-radius: 8px;
    padding: 20px !important;
    margin-bottom: 30px !important;
}

/* Section headings */
h6.fw-bold {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 18px !important;
    padding: 10px 14px;
    background: #e9eff6;  /* slightly stronger header bar */
    border-radius: 6px;
}

/* Inputs */
.form-control,
.form-select {
    height: 42px;
    border-radius: 6px;
    border: 1px solid #cfd8e3;
    font-size: 14px;
}

.form-control:focus,
.form-select:focus {
    border-color: #64748b;
    box-shadow: 0 0 0 2px rgba(100,116,139,0.15);
}

/* Family member block */
.family_member {
    background: #ffffff;
    border: 1px solid #dbe3ec;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 18px;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading">User</li>
                    </ol>
                </nav>

                <h5
                    class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit User
                </h5>

                <form id="employee_form" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm"
                    method="POST"
                    action="{{ route('manage-merchant-employee.update', $employee->id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" name="name" id="name"
                                value="{{ $employee->name }}" placeholder="Enter Name" />
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Mobile</label>
                            <input type="text" class="form-control" name="mobile" id="mobile"
                                value="{{ $employee->mobile_no }}" placeholder="Enter Mobile" minlength="10"
                                maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Email</label>
                            <input type="text" class="form-control" name="email" id="email"
                                value="{{ $employee->email }}" placeholder="Enter Email" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Type of User</label>
                            <select class="form-select" id="type_of_user" name="type_of_user" required>
                                <option value="">Select Type</option>
                                <option value="EMPLOYEE"
                                    {{ $employee->type=='EMPLOYEE'?'selected':'' }}>
                                    EMPLOYEE</option>
                                <option value="ACCOUNTANT"
                                    {{ $employee->type=='ACCOUNTANT'?'selected':'' }}>
                                    ACCOUNTANT</option>
                                <option value="CA"
                                    {{ $employee->type=='CA'?'selected':'' }}>
                                    CA</option>
                                <option value="OTHER"
                                    {{ $employee->type=='OTHER'?'selected':'' }}>
                                    OTHER</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" name="address" id="address"
                                value="{{ $employee->address }}" placeholder="Enter Address" />
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" required>
                                <option value="">Select </option>
                                <option value="1"
                                    {{ $employee->status=='1'?'selected':'' }}>
                                    Enable</option>
                                <option value="0"
                                    {{ $employee->status=='0'?'selected':'' }}>
                                    Disable</option>
                            </select>
                        </div>
                    </div>

                    <!-- EMPLOYEE SECTION -->
                     <div id="employee_sections" style="display:none;">
                        <!-- Employee Details Box -->
                        <div class="border p-3 mb-4 rounded">
                            <h6 class="fw-bold mb-3">Employee Details</h6>
                            <div class="row">
                                <div class="mb-4 col-md-4">
                                    <label>Branch</label>
                                    <select class="form-select" name="employee_branch">
                                        <option value="">Select Branch</option>
                                        <option value="Branch1"
                                            {{ $employee->branch=='Branch1'?'selected':'' }}>
                                            Branch 1</option>
                                        <option value="Branch2"
                                            {{ $employee->branch=='Branch2'?'selected':'' }}>
                                            Branch 2</option>
                                        <option value="Branch3"
                                            {{ $employee->branch=='Branch3'?'selected':'' }}>
                                            Branch 3</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-4">
                                    <label>Attendance Status</label>
                                    <select class="form-select" id="attendance_status" name="attendance_status">
                                        <option value="">Select</option>
                                        <option value="Enable"
                                            {{ $employee->attendance_status=='Enable'?'selected':'' }}>
                                            Enable</option>
                                        <option value="Disable"
                                            {{ $employee->attendance_status=='Disable'?'selected':'' }}>
                                            Disable</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-4" id="attendance_location_div" style="display:none;">
                                    <label>Attendance Location</label>
                                    <select class="form-select" name="attendance_location">
                                        <option value="">Select Location</option>
                                        <option value="Location1"
                                            {{ $employee->attendance_location=='Location1'?'selected':'' }}>
                                            Location 1</option>
                                        <option value="Location2"
                                            {{ $employee->attendance_location=='Location2'?'selected':'' }}>
                                            Location 2</option>
                                        <option value="Location3"
                                            {{ $employee->attendance_location=='Location3'?'selected':'' }}>
                                            Location 3</option>
                                        <option value="Location4"
                                            {{ $employee->attendance_location=='Location4'?'selected':'' }}>
                                            Location 4</option>
                                        <option value="Location5"
                                            {{ $employee->attendance_location=='Location5'?'selected':'' }}>
                                            Location 5</option>
                                        <option value="Location6"
                                            {{ $employee->attendance_location=='Location6'?'selected':'' }}>
                                            Location 6</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-4">
                                    <label>Upload Photo</label>
                                    <input type="file" class="form-control" name="employee_photo">
                                     @if($employee->aadhar_attachment)
                                                <a href="{{ asset('storage/app/'.$employee->aadhar_attachment) }}"
                                                    target="_blank" class="d-block mt-1 text-primary">
                                                    View Existing File
                                                </a>
                                            @endif
                                </div>
                            </div>
                        </div>

                        <!-- Salary Details -->
                        <div class="border p-3 mb-4 rounded">
                            <h6 class="fw-bold mb-3">Salary Details</h6>
                            <div class="row">
                                            <div class="mb-4 col-md-4">
                                                <label class="d-flex justify-content-between align-items-center">
                                                    <span>Create Account / Link Account</span>
                                                    <a href="javascript:void(0);"
                                                    class="text-primary fw-bold"
                                                    id="openAccountModal">
                                                        + Link Account
                                                    </a>
                                                </label>

                                                <select class="form-select select2-single" name="salary_account" id="salary_account" >
                                                    <option value="">Select Account</option>
                                                    @foreach($party_list as $party)
                                                        <option value="{{ $party->id }}"
                                                            {{ (isset($employee) && $party->id == $employee->account_name) ? 'selected' : '' }}>
                                                            {{ $party->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-4 col-md-4">
                                            <label>Salary / Wages Account</label>
                                            <select class="form-select select2-single" name="salary_wages_account" id="salary_wages_account">
                                                <option value="">Select Salary / Wages Account</option>
                                                @foreach($salary_account_list as $acc)
                                                    <option value="{{ $acc->id }}"
                                                        {{ $employee->salary_wages_account == $acc->id ? 'selected' : '' }}>
                                                        {{ $acc->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            </div>
                                    
                                            <div class="mb-4 col-md-4">
    <label class="d-flex justify-content-between align-items-center">
        <span>Salary Structure</span>
        <a href="javascript:void(0);" 
           class="text-primary fw-bold"
           id="openSalaryModal">
            + Define Salary
        </a>
    </label>

    <!-- Visible Salary Display -->
    <input type="text"
           class="form-control"
           id="final_salary_display"
           value="{{ $employee->salary }}"
           readonly>

    <!-- Hidden total salary -->
    <input type="hidden"
           name="salary_amount"
           id="salary_amount"
           value="{{ $employee->salary }}">
</div>
                                            <div class="mb-4 col-md-4"><label>Under This</label><input type="text"
                                                    class="form-control" name="salary_under" value="{{ $employee->under }}">
                                            </div>
                                            <div class="mb-4 col-md-3"><label>TDS Applicable</label>
                                                <select class="form-select" name="tds_applicable">
                                                    <option value="">Select</option>
                                                    <option value="Yes"
                                                        {{ $employee->tds_applicable=='Yes'?'selected':'' }}>
                                                        Yes</option>
                                                    <option value="No"
                                                        {{ $employee->tds_applicable=='No'?'selected':'' }}>
                                                        No</option>
                                                </select>
                                            </div>
                                            <!-- ESI -->
                                            <div class="mb-4 col-md-3">
                                                <label>ESI Applicable</label>
                                                <select class="form-select" name="esi_applicable" id="esi_applicable">
                                                    <option value="">Select</option>
                                                    <option value="Yes" {{ $employee->esi_applicable == 'Yes' ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                    <option value="No" {{ $employee->esi_applicable == 'No' ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="mb-4 col-md-3" id="esic_number_div" style="display:none;">
                                                <label>ESIC Number</label>
                                                <input type="text"
                                                    class="form-control"
                                                    name="esic_number"
                                                    value="{{ $employee->esic_number }}">
                                            </div>

                                            <!-- PF -->
                                            <div class="mb-4 col-md-3">
                                                <label>PF Applicable</label>
                                                <select class="form-select" name="pf_applicable" id="pf_applicable">
                                                    <option value="">Select</option>
                                                    <option value="Yes" {{ $employee->pf_applicable == 'Yes' ? 'selected' : '' }}>
                                                        Yes
                                                    </option>
                                                    <option value="No" {{ $employee->pf_applicable == 'No' ? 'selected' : '' }}>
                                                        No
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="mb-4 col-md-3" id="uan_number_div" style="display:none;">
                                                <label>UAN Number</label>
                                                <input type="text"
                                                    class="form-control"
                                                    name="uan_number"
                                                    value="{{ $employee->uan_number }}">
                                            </div>
                                            <div class="mb-4 col-md-3"><label>Start Date</label><input type="date"
                                                    class="form-control" name="salary_start_date"
                                                    value="{{ $employee->start_date }}"></div>
                                            <div class="mb-4 col-md-3"><label>Edit From</label><input type="date"
                                                    class="form-control" name="salary_edit_from"
                                                    value="{{ $employee->edit_from }}">
                                            </div>
                            </div>
                        </div>

                        <!-- Bank Details -->
                        <div class="border p-3 mb-4 rounded">
                            <h6 class="fw-bold mb-3">Bank Details</h6>
                            <div class="row">
                                <div class="mb-4 col-md-4"><label>Bank Name</label><input type="text"
                                        class="form-control" name="bank_name" value="{{ $employee->bank_name }}">
                                </div>
                                <div class="mb-4 col-md-4"><label>Bank Account Number</label><input type="text"
                                        class="form-control" name="bank_account_number"
                                        value="{{ $employee->account_number }}"></div>
                                <div class="mb-4 col-md-4"><label>Branch IFSC Code</label><input type="text"
                                        class="form-control" name="bank_ifsc" value="{{ $employee->ifsc_code }}">
                                </div>
                                <div class="mb-4 col-md-4"><label>Branch Address</label><input type="text"
                                        class="form-control" name="bank_branch_address"
                                        value="{{ $employee->branch_address }}"></div>
                                <div class="mb-4 col-md-4"><label>Attach PassBook</label>
                                <input type="file" class="form-control" name="bank_passbook">
                                 @if($employee->passbook_attachment)
                                                <a href="{{ asset('storage/app/'.$employee->passbook_attachment) }}"
                                                    target="_blank" class="d-block mt-1 text-primary">
                                                    View Existing File
                                                </a>
                                            @endif
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <div class="border p-3 mb-4 rounded">
                            <h6 class="fw-bold mb-3">Personal Details</h6>
                            <div class="row">
                                <div class="mb-4 col-md-3"><label>Marital Status</label>
                                    <select class="form-select" name="marital_status">
                                        <option value="">Select</option>
                                        <option value="Married"
                                            {{ $employee->marital_status=='Married'?'selected':'' }}>
                                            Married</option>
                                        <option value="Unmarried"
                                            {{ $employee->marital_status=='Unmarried'?'selected':'' }}>
                                            Unmarried</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-3"><label>Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="">Select</option>
                                        <option value="Male"
                                            {{ $employee->gender=='Male'?'selected':'' }}>
                                            Male</option>
                                        <option value="Female"
                                            {{ $employee->gender=='Female'?'selected':'' }}>
                                            Female</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-3"><label>Date of Birth</label><input type="date"
                                        class="form-control" name="dob" value="{{ $employee->dob }}"></div>
                                <div class="mb-4 col-md-3"><label>Aadhar ID</label><input type="text"
                                        class="form-control" name="aadhar_id" value="{{ $employee->aadhar_id }}">
                                </div>
                                <div class="mb-4 col-md-3"><label>Attach Aadhar Card</label><input type="file"
                                        class="form-control" name="aadhar_file"></div>
                                <div class="mb-4 col-md-3"><label>Disability</label>
                                    <select class="form-select" id="disability" name="disability">
                                        <option value="">Select</option>
                                        <option value="Yes"
                                            {{ $employee->disability=='Yes'?'selected':'' }}>
                                            Yes</option>
                                        <option value="No"
                                            {{ $employee->disability=='No'?'selected':'' }}>
                                            No</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-3" id="disability_type_div" style="display:none;">
                                    <label>Type of Disability</label>
                                    <select class="form-select" name="disability_type">
                                        <option value="">Select</option>
                                        <option value="Visual"
                                            {{ $employee->disability_type=='Visual'?'selected':'' }}>
                                            Visual</option>
                                        <option value="Hearing"
                                            {{ $employee->disability_type=='Hearing'?'selected':'' }}>
                                            Hearing</option>
                                        <option value="Physical"
                                            {{ $employee->disability_type=='Physical'?'selected':'' }}>
                                            Physical</option>
                                    </select>
                                </div>
                                <div class="mb-4 col-md-3"><label>PAN Number</label><input type="text"
                                        class="form-control" name="pan_number" value="{{ $employee->pan_number }}">
                                </div>
                                <div class="mb-4 col-md-6"><label>Present Address</label><input type="text"
                                        class="form-control" name="present_address"
                                        value="{{ $employee->present_address }}"></div>
                                <div class="mb-4 col-md-6"><label>Permanent Address</label><input type="text"
                                        class="form-control" name="permanent_address"
                                        value="{{ $employee->permanent_address }}"></div>
                                <div class="mb-4 col-md-6">
                                    <input type="checkbox" id="permanent_same" /> Permanent same as present
                                </div>
                            </div>
                        </div>

                        <!-- Family Details -->
                        <div class="border p-3 mb-4 rounded">
                            <h6 class="fw-bold mb-3">Family Details</h6>
                            <div id="family_container">
                                @forelse($family_details as $member)
                                    <div class="family_member row mb-3">
                                        <input type="hidden" name="family_id[]" value="{{ $member->id }}">

                                        <div class="mb-3 col-md-4">
                                            <label>Name</label>
                                            <input type="text" class="form-control" name="family_name[]"
                                                value="{{ $member->name }}">
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Relation with Employee</label>
                                            <input type="text" class="form-control" name="family_relation[]"
                                                value="{{ $member->relation }}">
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Date of Birth</label>
                                            <input type="date" class="form-control" name="family_dob[]"
                                                value="{{ $member->dob ? \Carbon\Carbon::parse($member->dob)->format('Y-m-d') : '' }}">
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Aadhar ID</label>
                                            <input type="text" class="form-control" name="family_aadhar[]"
                                                value="{{ $member->aadhar_id }}">
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Attach Aadhar Card</label>
                                            <input type="file" class="form-control" name="family_aadhar_file[]">
                                            @if($member->aadhar_attachment)
                                                <a href="{{ asset('storage/app/'.$member->aadhar_attachment) }}"
                                                    target="_blank" class="d-block mt-1 text-primary">
                                                    View Existing File
                                                </a>
                                            @endif
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Present Address</label>
                                            <input type="text" class="form-control" name="family_present_address[]"
                                                value="{{ $member->present_address }}">
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label>Permanent Address</label>
                                            <input type="text" class="form-control" name="family_permanent_address[]"
                                                value="{{ $member->permanent_address }}">
                                        </div>

                                        <div class="mb-3 col-md-4 d-flex align-items-center mt-4">
                                            <input type="checkbox" class="family_permanent_same me-2"
                                                {{ $member->present_address == $member->permanent_address ? 'checked' : '' }}>
                                            <label class="mb-0">Permanent same as present</label>
                                        </div>

                                        <div class="mb-3 col-md-3">
                                            <label>Nominee</label>
                                            <select class="form-select family_nominee" name="family_nominee[]">
                                                <option value="">Select</option>
                                                <option value="Yes"
                                                    {{ $member->nominee == 'Yes' ? 'selected' : '' }}>
                                                    Yes</option>
                                                <option value="No"
                                                    {{ $member->nominee == 'No' ? 'selected' : '' }}>
                                                    No</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 col-md-3 nominee_percent_div"
                                            style="{{ $member->nominee == 'Yes' ? '' : 'display:none;' }}">
                                            <label>Nominee %</label>
                                            <input type="text" class="form-control" name="family_nominee_percent[]"
                                                value="{{ $member->nominee_percentage }}">
                                        </div>
                                        <div class="mb-3 col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove_family">
                                                Remove
                                            </button>
                                        </div>

                                    </div>
                                @empty
                                    {{-- Default empty row if no family members exist --}}
                                    <div class="family_member row mb-3">
                                        <div class="mb-3 col-md-4"><label>Name</label><input type="text"
                                                class="form-control" name="family_name[]"></div>
                                        <div class="mb-3 col-md-4"><label>Relation with Employee</label><input
                                                type="text" class="form-control" name="family_relation[]"></div>
                                        <div class="mb-3 col-md-4"><label>Date of Birth</label><input type="date"
                                                class="form-control" name="family_dob[]"></div>
                                        <div class="mb-3 col-md-4"><label>Aadhar ID</label><input type="text"
                                                class="form-control" name="family_aadhar[]"></div>
                                        <div class="mb-3 col-md-4"><label>Attach Aadhar Card</label>
                                        <input type="file" class="form-control" name="family_aadhar_file[]">
                                    
                                        </div>
                                        <div class="mb-3 col-md-4"><label>Present Address</label><input type="text"
                                                class="form-control" name="family_present_address[]"></div>
                                        <div class="mb-3 col-md-4"><label>Permanent Address</label><input type="text"
                                                class="form-control" name="family_permanent_address[]"></div>
                                        <div class="mb-3 col-md-4 d-flex align-items-center mt-4">
                                            <input type="checkbox" class="family_permanent_same me-2">
                                            <label class="mb-0">Permanent same as present</label>
                                        </div>
                                        <div class="mb-3 col-md-3"><label>Nominee</label>
                                            <select class="form-select family_nominee" name="family_nominee[]">
                                                <option value="">Select</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-3 nominee_percent_div" style="display:none;"><label>Nominee %</label><input type="text"
                                                class="form-control" name="family_nominee_percent[]"></div>
                                        <div class="mb-3 col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove_family">
                                                Remove
                                            </button>
                                        </div>

                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-secondary" id="add_family">Add Family Member</button>
                        </div>
                     </div>

                        <!-- ACCOUNTANT SECTION -->
                     <div id="accountant_section" class=" border p-3 mb-4 rounded" style="display:none;">
                           <h6 class="fw-bold mb-3">Accountant Details</h6>
                           <div class="row">
                              <div class="mb-4 col-md-4">
                                 <label>Date of Joining</label>
                                 <input type="date" class="form-control" name="accountant_doj"
                                       value="{{ $employee->date_of_joining }}">
                              </div>
                              <div class="mb-4 col-md-4">
                                 <label>Attach ID</label>
                                 <input type="file" class="form-control" name="accountant_id_file">
                                 
                                 @if($employee->id_attachment)
                                    <img src="{{ url('storage/app/'.$employee->id_attachment) }}"
                                    alt="Accountant Photo"
                                    class="img-thumbnail"
                                    style="max-width:100px; height:auto;">
                                 @endif
                              </div>
                           </div>
                     </div>

                        <!-- CA SECTION -->
                     <div id="ca_section" class=" border p-3 mb-4 rounded" style="display:none;">
                           <h6 class="fw-bold mb-3">CA Details</h6>
                           <div class="row">
                              <div class="mb-4 col-md-4">
                                 <label>Date of Joining</label>
                                 <input type="date" class="form-control" name="ca_doj"
                                       value="{{ $employee->date_of_joining }}">
                              </div>
                              <div class="mb-4 col-md-4">
                                 <label>Attach ID</label>
                                 <input type="file" class="form-control" name="ca_id_file">
                                 @if($employee->id_attachment)
                                    <img src="{{ url('storage/app/'.$employee->id_attachment) }}"
                                    alt="Accountant Photo"
                                    class="img-thumbnail"
                                    style="max-width:100px; height:auto;">
                                 @endif
                              </div>
                           </div>
                     </div>

                        <!-- OTHER SECTION -->
                     <div id="other_section" class=" border p-3 mb-4 rounded" style="display:none;">
                           <h6 class="fw-bold mb-3">Other Details</h6>
                           <div class="row">
                              <div class="mb-4 col-md-4">
                                 <label>Date of Joining</label>
                                 <input type="date" class="form-control" name="other_doj"
                                       value="{{ $employee->date_of_joining }}">
                              </div>
                              <div class="mb-4 col-md-4">
                                 <label>Attach ID</label>
                                 <input type="file" class="form-control" name="other_id_file">
                                 @if($employee->id_attachment)
                                    <img src="{{ url('storage/app/'.$employee->id_attachment) }}"
                                    alt="Accountant Photo"
                                    class="img-thumbnail"
                                    style="max-width:100px; height:auto;">
                                 @endif
                              </div>
                           </div>
                     </div>

                     <div class="text-start">
                           <button type="submit" class="btn btn-xs-primary" id="save_btn">Save</button>
                           <a href="{{ url()->previous() }}" class="btn btn-secondary">Quit</a>
                     </div>

                </form>
            </div>
        </div>
    </section>
</div>
<!-- ADD ACCOUNT MODAL -->
<div class="modal fade" id="accountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="addAccountModalForm" method="POST" action="{{ route('account.store') }}">
                    @csrf

                    {{-- FORCE UNDER GROUP = 2 --}}
                    <input type="hidden" name="under_group" value="2">
                    <input type="hidden" name="under_group_type" value="group">

                    <div class="row">

                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">ACCOUNT NAME</label>
                            <input type="text" class="form-control" name="account_name" required>
                        </div>

                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">PRINT NAME</label>
                            <input type="text" class="form-control" name="print_name" required>
                        </div>

                        <!-- DISABLED UNDER GROUP -->
                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">UNDER GROUP</label>
                            <select class="form-select" disabled>
                                <option selected>PROVISIONS / EXPENSES PAYABLE</option>
                            </select>
                        </div>

                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">OPENING BALANCE</label>
                            <input type="text" class="form-control" name="opening_balance">
                        </div>

                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">BALANCE TYPE</label>
                            <select class="form-select" name="opening_balance_type">
                                <option value="">Select</option>
                                <option value="debit">Debit</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>

                        <div class="mb-4 col-12">
                            <label class="form-label font-14 font-heading">STATUS</label>
                            <select class="form-select" name="status">
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer-custom">
    <button type="submit" class="btn btn-xs-primary">
        SUBMIT
    </button>
</div>


                </form>

            </div>
        </div>
    </div>
</div>

<!-- DEFINE SALARY MODAL -->
<div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Define Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-bordered" id="salaryTable">
                        <thead class="table-light">
                            <tr>
                                <th>Include</th>
                                <th>Head</th>
                                <th>Calculation</th>
                                <th width="180">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payroll_heads as $head)
                            <tr 
                                data-id="{{ $head->id }}"
                                data-name="{{ strtolower($head->name) }}"
                                data-type="{{ $head->adjustment_type }}"
                                data-calculation="{{ $head->calculation_type }}"
                                data-percentage="{{ $head->percentage }}"
                                data-formula='{{ $head->formula_heads }}'
                            >
                                <td class="text-center">
    <input type="checkbox"
           class="include-head"
           {{ isset($user_salary_structure[$head->id]) ? 'checked' : '' }}>
</td>
                                <td><strong>{{ $head->name }}</strong></td>
                                <td>
                                    {{ ucfirst($head->calculation_type) }}
                                    @if($head->percentage)
                                        ({{ $head->percentage }}%)
                                    @endif
                                </td>
                                <td>
                                    @if($head->calculation_type == 'user_defined')
    <input type="number"
           class="form-control salary-input"
           name="salary_heads[{{ $head->id }}]"
           value="{{ $user_salary_structure[$head->id] ?? 0 }}">
@else
    <input type="number"
           class="form-control salary-input"
           name="salary_heads[{{ $head->id }}]"
           value="{{ $user_salary_structure[$head->id] ?? 0 }}"
           readonly>
@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <h5>Total Salary: â‚¹ <span id="salaryTotal">0</span></h5>
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-xs-primary" id="applySalaryBtn">
                        APPLY
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@include('layouts.footer')

<script>
    $(document).ready(function () {



        // === Toggle user sections based on Type of User ===
        function toggleUserSections() {

            // Hide all
            $('#employee_sections').hide();
            $('#accountant_section, #ca_section, #other_section').css('display', 'none');

            var type = $('#type_of_user').val();
            type = type ? type.toUpperCase().trim() : '';

            console.log('TYPE =', type); // keep once for confirmation

            if (type === 'EMPLOYEE') {
                $('#employee_sections').show();
            } else if (type === 'ACCOUNTANT') {
                $('#accountant_section').css('display', 'block');
            } else if (type === 'CA') {
                $('#ca_section').css('display', 'block');
            } else if (type === 'OTHER') {
                $('#other_section').css('display', 'block');
            }
        }



        // Run once when the page loads
        toggleUserSections();

        // Run when dropdown changes
        $('#type_of_user').change(function () {
            toggleUserSections();
        });

        // === Attendance location toggle ===
        function toggleAttendanceLocation() {
            if ($('#attendance_status').val() === 'Enable') {
                $('#attendance_location_div').show();
            } else {
                $('#attendance_location_div').hide();
            }
        }
        toggleAttendanceLocation();
        $('#attendance_status').change(toggleAttendanceLocation);
        // === ESI toggle ===
        function toggleESI() {
            if ($('#esi_applicable').val() === 'Yes') {
                $('#esic_number_div').show();
            } else {
                $('#esic_number_div').hide();
                $('input[name="esic_number"]').val('');
            }
        }

        // === PF toggle ===
        function togglePF() {
            if ($('#pf_applicable').val() === 'Yes') {
                $('#uan_number_div').show();
            } else {
                $('#uan_number_div').hide();
                $('input[name="uan_number"]').val('');
            }
        }

        toggleESI();
        togglePF();

        $('#esi_applicable').change(toggleESI);
        $('#pf_applicable').change(togglePF);

        // === Disability toggle ===
        function toggleDisabilityType() {
            if ($('#disability').val() === 'Yes') {
                $('#disability_type_div').show();
            } else {
                $('#disability_type_div').hide();
            }
        }
        toggleDisabilityType();
        $('#disability').change(toggleDisabilityType);

        // === Copy permanent address ===
        $('#permanent_same').change(function () {
            if ($(this).is(':checked')) {
                $('input[name="permanent_address"]').val($('input[name="present_address"]').val());
            } else {
                $('input[name="permanent_address"]').val('');
            }
        });

        // === Family permanent address autofill ===
        $(document).on('change', '.family_permanent_same', function () {
            var parent = $(this).closest('.family_member');
            if ($(this).is(':checked')) {
                parent.find('input[name="family_permanent_address[]"]').val(
                    parent.find('input[name="family_present_address[]"]').val()
                );
            } else {
                parent.find('input[name="family_permanent_address[]"]').val('');
            }
        });

        // === Add new family member ===
        $('#add_family').click(function () {
            var clone = $('#family_container .family_member:first').clone();
            clone.find('input').val('');
            clone.find('select').val('');
            $('#family_container').append(clone);
        });

        // === Validation before submit ===
        $('#employee_form').on('submit', function (e) {
            if (!confirm("Are you sure you want to save changes?")) {
                e.preventDefault();
                return false;
            }
        });
        // === Nominee % toggle (Edit page) ===
        $(document).on('change', '.family_nominee', function () {
            var parent = $(this).closest('.family_member');

            if ($(this).val() === 'Yes') {
                parent.find('.nominee_percent_div').show();
            } else {
                parent.find('.nominee_percent_div').hide();
                parent.find('input[name="family_nominee_percent[]"]').val('');
            }
        });
        $('select[name="status"]').change(function () {

            if ($(this).val() === '0') {  // Disable selected
                $('#employee_form').attr('novalidate', true);
            } else {
                $('#employee_form').removeAttr('novalidate');
            }

        });

        // === Remove family member block ===
        $(document).on('click', '.remove_family', function () {
            if ($('#family_container .family_member').length > 1) {
                $(this).closest('.family_member').remove();
            } else {
                alert('At least one family member is required.');
            }
        });

    });
    $("#openAccountModal").on("click", function () {
    $("#accountModal").modal("show");
});
$(document).on('submit', '#addAccountModalForm', function (e) {
    e.preventDefault(); // ðŸš« stops redirect

    let form = $(this);
    let formData = new FormData(this);

    $.ajax({
        url: form.attr('action'), // account.store
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {

            // ðŸŸ¢ IF RESPONSE IS JSON
            if (res.status === true) {

                let option = new Option(res.name, res.id, true, true);
                $('#salary_account').append(option).trigger('change');

                $('#addAccountModalForm')[0].reset();
                $('#accountModal').modal('hide');
            }
            else {
                alert(res.message || 'Account save failed');
            }
        },
        error: function (xhr) {

    if (xhr.status === 422) {
        let errors = xhr.responseJSON.errors;

        if (errors.account_name) {

            alert(errors.account_name[0]); 
            // ðŸ‘† user clicks OK here

            // ðŸ”¹ Clear fields
            let form = $('#addAccountModalForm');
            form.find('input[name="account_name"]').val('');
            form.find('input[name="print_name"]').val('');

            // ðŸ”¹ Focus back to Account Name
            form.find('input[name="account_name"]').focus();

            return;
        }
    }

    console.log(xhr.responseText);
    alert('Account save failed');
}


    });
});
$(document).on('keyup change', '#addAccountModalForm input[name="account_name"]', function () {
    let val = $(this).val();
    $('#addAccountModalForm input[name="print_name"]').val(val);
});


// Open Salary Modal
$("#openSalaryModal").on("click", function () {

    // ✅ disable unchecked rows on open
    $("#salaryTable tbody tr").each(function () {
        let row = $(this);

        if (!row.find(".include-head").is(":checked")) {
            row.find(".salary-input").prop("disabled", true);
        }
    });

    calculateSalary();
    $("#salaryModal").modal("show");
});

// Calculate total
function calculateSalary() {

    let values = {};
    let total = 0;

    // ðŸ”¹ PASS 1 â†’ collect user_defined first
    $("#salaryTable tbody tr").each(function () {

        let row = $(this);
         // ✅ ADD THIS
    if (!row.find(".include-head").is(":checked")) {
        return;
    }
        let headId = row.data("id");
        let calcType = row.data("calculation");
        let input = row.find(".salary-input");

        if (calcType === "user_defined") {
            values[headId] = parseFloat(input.val()) || 0;
        }
    });

    // ðŸ”¹ PASS 2 â†’ calculate others
    $("#salaryTable tbody tr").each(function () {

        let row = $(this);
        
    // ✅ ADD THIS
    if (!row.find(".include-head").is(":checked")) {
        return;
    }
        let headId = row.data("id");
        let type = row.data("type");
        let calcType = row.data("calculation");
        let percent = parseFloat(row.data("percentage")) || 0;
        let formula = row.data("formula");

        let input = row.find(".salary-input");
        let amount = values[headId] || 0;

        // PERCENTAGE
        if (calcType === "percentage") {

            let basicRow = $("#salaryTable tbody tr")
                .filter(function(){
                    return $(this).data("name") === "basic";
                });

            let basicId = basicRow.data("id");
            let basic = values[basicId] || 0;

            amount = (basic * percent) / 100;
            input.val(amount.toFixed(2));
        }

        // CUSTOM FORMULA
        else if (calcType === "custom_formula" && formula) {

            let base = 0;
            let heads = [];

            if (Array.isArray(formula)) {
                heads = formula;
            } else if (typeof formula === "string") {
                try {
                    heads = JSON.parse(formula);
                } catch (e) {
                    heads = [];
                }
            }

            heads.forEach(function (h) {
                base += values[h] || 0;
            });

            amount = (base * percent) / 100;
            input.val(amount.toFixed(2));
        }

        values[headId] = amount;

        if (type === "subtractive") {
            total -= amount;
        } else {
            total += amount;
        }
    });

    $("#salaryTotal").text(total.toFixed(2));
    return total;
}

$(document).on("keyup change", ".salary-input", function () {
    calculateSalary();
});
// Apply button
$("#applySalaryBtn").on("click", function () {

    let final = calculateSalary();

    $("#final_salary_display").val(final.toFixed(2));
    $("#salary_amount").val(final.toFixed(2));

    // Remove old hidden inputs
    $(".salary-hidden-input").remove();

    // Add hidden inputs (same as ADD page)
    $("#salaryTable tbody tr").each(function () {

    let row = $(this);

    let isIncluded = row.find(".include-head").is(":checked");

    if (isIncluded) {
        let headId = row.data("id");
        let amount = row.find(".salary-input").val();

        $("<input>")
            .attr("type", "hidden")
            .attr("name", "salary_heads[" + headId + "]")
            .attr("value", amount)
            .addClass("salary-hidden-input")
            .appendTo("#employee_form");
    }
});

    $("#salaryModal").modal("hide");
});

$(document).on("change", ".include-head", function () {

    let row = $(this).closest("tr");

    if ($(this).is(":checked")) {
        row.find(".salary-input").prop("disabled", false);
    } else {
        row.find(".salary-input").prop("disabled", true).val(0);
    }

    calculateSalary();
});
</script>

@endsection
