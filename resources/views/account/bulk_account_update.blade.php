@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
.bulk-table-wrapper {
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
}

.bulk-scroll {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 65vh;
    border: 1px solid #ddd;
}

.bulk-table {
    min-width: 1400px;
    white-space: nowrap;
}

.bulk-table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 5;
}

.bulk-table tbody td:first-child,
.bulk-table thead th:first-child {
    position: sticky;
    left: 0;
    background: #ffffff;
    z-index: 6;
    min-width: 220px;
}

.bulk-table input,
.bulk-table select {
    min-width: 150px;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
Bulk Account Update
</h5>
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Error Message --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
<div class="bg-white p-4 shadow-sm rounded-bottom-8">

<div class="row mb-3">
    <div class="col-md-4">
        <select id="group_id" class="form-select select2-single">
            <option value="">Select Group</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div id="columnSelector" class="mb-3" style="display:none;">
    <div class="row">

        @php
        $fields = [
            'opening_balance' => 'Opening Balance',
            'dr_cr' => 'Balance Type',
            'gstin' => 'GST',
            'state' => 'State',
            'address' => 'Address',
            'pin_code' => 'Pincode',
            'location' => 'Location',
            'pan' => 'PAN',
            'sms_status' => 'SMS',
            'credit_days' => 'Credit Days',
            'due_day' => 'Due Days',
            'contact_person' => 'Contact Person',
            'mobile' => 'Mobile',
            'whatsup_number' => 'WhatsApp',
            'email' => 'Email',
            'bank_account_no' => 'Bank Account No',
            'ifsc_code' => 'IFSC Code',
        ];
        @endphp

        @foreach($fields as $key => $label)
        <div class="col-md-3 mb-2">
            <label>
                <input type="checkbox" class="columnCheck" value="{{ $key }}">
                {{ $label }}
            </label>
        </div>
        @endforeach

    </div>

    <button type="button" id="loadAccounts" class="btn btn-primary mt-2">
        Load Accounts
    </button>
</div>

<form method="POST" action="{{ route('account.bulk.update.save') }}">
@csrf

<div id="accountsTable"></div>

<button type="submit" id="saveBtn" class="btn btn-success mt-3" style="display:none;">
    Save Bulk Changes
</button>

</form>

</div>
</div>
</div>
</section>
</div>

@include('layouts.footer')

<script>
let stateList = @json($state_list);
let creditDaysList = @json($credit_days);
</script>

<script>
$(document).ready(function(){
    $(".select2-single").select2();
    $('#group_id').change(function(){
        if($(this).val() !== ''){
            $('#columnSelector').show();
        } else {
            $('#columnSelector').hide();
            $('#accountsTable').html('');
            $('#saveBtn').hide();
        }
    });

    $('#loadAccounts').click(function(){

        let group_id = $('#group_id').val();
        let selectedFields = [];

        $('.columnCheck:checked').each(function(){
            selectedFields.push($(this).val());
        });

        if(group_id === ''){
            alert('Please select group');
            return;
        }

        if(selectedFields.length === 0){
            alert('Select at least one column');
            return;
        }

        $.ajax({
            url: "{{ route('account.bulk.update.fetch') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                group_id: group_id
            },
            success: function(response){

                let html = `
                <div class="bulk-table-wrapper">
                    <div class="bulk-scroll">
                        <table class="table table-bordered table-sm bulk-table">
                        <thead><tr>
                        <th style="width: 20%;">Account</th>`;

                selectedFields.forEach(function(field){
                    html += '<th>'+field.replace(/_/g,' ').toUpperCase()+'</th>';
                });

                html += '</tr></thead><tbody>';

                response.accounts.forEach(function(acc){

                    html += '<tr>';
                    html += '<td>'+acc.account_name+'</td>';

                    selectedFields.forEach(function(field){

                        html += '<td>';

                        if(field === 'opening_balance'){
                            html += `<input type="number" class="form-control"
                                name="accounts[${acc.id}][opening_balance]"
                                value="${acc.opening_balance ?? ''}">`;
                        }

                        else if(field === 'dr_cr'){
                            html += `
                            <select class="form-select"
                                name="accounts[${acc.id}][dr_cr]">
                                <option value="">Select</option>
                                <option value="debit" ${acc.dr_cr=='debit'?'selected':''}>Debit</option>
                                <option value="credit" ${acc.dr_cr=='credit'?'selected':''}>Credit</option>
                            </select>`;
                        }

                        else if(field === 'state'){
                            html += `<select class="form-select select2-single"
                                name="accounts[${acc.id}][state]">
                                <option value="">Select State</option>`;

                            stateList.forEach(function(st){
                                html += `<option value="${st.id}"
                                    ${acc.state==st.id?'selected':''}>
                                    ${st.state_code} - ${st.name}
                                </option>`;
                            });

                            html += `</select>`;
                        }

                        else if(field === 'credit_days'){
                            html += `<select class="form-select"
                                name="accounts[${acc.id}][credit_days]">
                                <option value="">Select</option>`;

                            creditDaysList.forEach(function(cd){
                                html += `<option value="${cd.days}"
                                    ${parseInt(acc.credit_days) === parseInt(cd.days) ? 'selected' : ''}>
                                    ${cd.days} Days
                                </option>`;
                            });

                            html += `</select>`;
                        }

                        else if(field === 'sms_status'){
                            html += `
                            <select class="form-select"
                                name="accounts[${acc.id}][sms_status]">
                                <option value="1" ${acc.sms_status==1?'selected':''}>Yes</option>
                                <option value="0" ${acc.sms_status==0?'selected':''}>No</option>
                            </select>`;
                        }
                        else if(field === 'due_day'){
                            html += `<input type="number" class="form-control"
                                name="accounts[${acc.id}][${field}]"
                                value="${acc[field] ?? ''}">`;
                            
                        }

                        else{
                            html += `<input type="text" class="form-control"
                                name="accounts[${acc.id}][${field}]"
                                value="${acc[field] ?? ''}">`;
                        }

                        html += '</td>';

                    });

                    html += '</tr>';
                });

                html += `
                    </tbody>
                    </table>
                    </div>
                </div>
                `;

                $('#accountsTable').html(html);

                $('.select2-single').select2();

                $('#saveBtn').show();
            }
        });

    });

});
</script>

@endsection
