@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style>
.table {
    font-size: 13px;
    margin-bottom: 0;
}

.table thead th {
    padding: 6px 6px;
    font-weight: 600;
    vertical-align: middle;
    white-space: nowrap;
}

.table tbody td,
.table tfoot td {
    padding: 4px 6px;
    vertical-align: middle;
}

.table input,
.table select {
    height: 28px;
    padding: 2px 6px;
    font-size: 13px;
    line-height: 1.2;
}

.table input[readonly] {
    background-color: #f8f9fa !important;
    color: #000;
    font-weight: 500;
    border: 1px solid #ced4da;
}

.table select {
    background-color: #fff;
}

.text-end input {
    text-align: right;
}

.card-body {
    padding: 6px;
}

.card {
    border-radius: 6px;
}

.table thead {
    background: #f1f3f5;
}

.card-header {
    padding: 8px 12px;
}

.card-header h5 {
    font-size: 15px;
    margin: 0;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 13px;
}

@media print {
    body {
        font-size: 12px;
    }
    .btn, form {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none;
    }
}

.table .reason {
    height: 26px !important;
    padding: 1px 4px !important;
    font-size: 12px;
}

.table .last-working-day {
    height: 26px !important;
    padding: 1px 4px !important;
    font-size: 12px;
}

.table td:has(.reason),
.table td:has(.last-working-day) {
    padding-left: 4px !important;
    padding-right: 4px !important;
}
.table tbody tr {
    height: 30px;
}
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
<div class="container-fluid col-md-10">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Payroll – ESIC Sheet
            </h5>
            <span class="badge bg-light text-dark">
                ESIC Employee 0.75% | Employer 3.25%
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <form method="GET" class="p-2">
    <div class="row g-2 align-items-center">
        <div class="col-md-3">
            <input type="month" name="month" value="{{ $month }}" class="form-control">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm">Load</button>
        </div>
    </div>
</form>
<form method="POST" action="{{ route('payroll.esic.export') }}" id="esicExportForm">
    @csrf
    <input type="hidden" name="esic_rows" id="esic_rows">
    <button type="submit" class="btn btn-success btn-sm mb-2">
        <i class="bi bi-file-earmark-excel"></i> Export ESIC Excel
    </button>
</form>
<form method="POST" action="{{ route('payroll.esic.save') }}" id="esicSaveForm">
    @csrf
    <input type="hidden" name="month_year" value="{{ $month }}">
    <input type="hidden" name="days_in_month" value="{{ $daysInMonth }}">


                <table class="table table-bordered table-hover table-sm align-middle mb-0"
                       id="payrollTable">

                    <thead class="table-light sticky-top">
                       <th style="width:30px">S.No</th>
                        <th style="width:100px">ESIC No</th>
                        <th style="width:150px">Name</th>
                        <th style="width:80px">Branch</th>
                        <th style="width:210px" class="text-end">Gross</th>
                        <th style="width:90px">Absent</th>
                        <th style="width:80px">Working Days</th>
                        <th style="width:80px">Reason</th>
                        <th style="width:80px">Last Working Day</th>
                        <th style="width:120px" class="text-end">ESIC Salary</th>
                        <th style="width:120px">ESIC (Emp)</th>
                        <th style="width:120px">ESIC (Er)</th>
                        <th style="width:120px">Total ESIC</th>

                    </thead>

                    <tbody>
                        @foreach($payrolls as $index => $row)
                        <tr data-esic-salary="{{ $row->gross_salary }}" data-esi="{{ $row->esi_applicable }}">
<input type="hidden" name="employee_user_id[]" value="{{ $row->user_id }}">
<input type="hidden" name="gross_salary[]" value="{{ $row->gross_salary }}">
<input type="hidden" name="absent[]" value="{{ $row->absent }}">
    <td>{{ $index + 1 }}</td>
    <td>{{ $row->esic_number }}</td>
<td class="text-start fw-semibold">{{ $row->name }}</td>
<td>{{ $row->branch }}</td>

    <td class="text-end">
        <input type="text" class="form-control form-control-sm text-end bg-light"
               value="{{ number_format($row->salary, 2) }}" readonly>
    </td>

    <td>
<input type="text"
class="form-control form-control-sm text-end bg-light"
value="{{ $row->absent }}"
readonly>
</td>

    <td>
        <input type="text" class="form-control form-control-sm text-end bg-light working-days"
               value="{{ $daysInMonth - $row->absent }}" readonly>
    </td>
@php
$fullAbsent = ($row->absent == $daysInMonth);
@endphp
   <td style="min-width:220px">
    <select name="reason[]"
class="form-select form-select-sm reason {{ $fullAbsent ? '' : 'd-none' }}">
    <option value="">Select Reason</option>
    @foreach([
        0=>'Without Reason',
        1=>'On Leave',
        2=>'Left Service',
        3=>'Retired',
        4=>'Out of Coverage',
        5=>'Expired',
        6=>'Non Implemented Area',
        7=>'Compliance by Immediate Employer',
        8=>'Suspension of Work',
        9=>'Strike / Lockout',
        10=>'Retrenchment',
        11=>'No Work',
        12=>'Doesn’t Belong To This Employer',
        13=>'Duplicate IP'
    ] as $code => $label)
        <option value="{{ $code }}"
            {{ (string)$row->reason_code === (string)$code ? 'selected' : '' }}>
            {{ $label }} ({{ $code }})
        </option>
    @endforeach
</select>
</td>


    <td>
        <input type="date"
name="last_working_day[]"
value="{{ $row->last_working_day }}"
class="form-control form-control-sm last-working-day
{{ in_array((int)$row->reason_code, [2,3,4,5,6,10]) ? '' : 'd-none' }}">
    </td>
<td>
    <input type="text"
           class="form-control form-control-sm text-end bg-light"
           value="{{ number_format($row->gross_salary, 2) }}"
           readonly>
</td>
    <td><input type="text" class="form-control form-control-sm text-end bg-warning-subtle esi-emp" readonly></td>
    <td><input type="text" class="form-control form-control-sm text-end bg-info-subtle esi-er" readonly></td>
    <td><input type="text" class="form-control form-control-sm text-end fw-bold bg-success-subtle esi-total" readonly></td>
</tr>

                        @endforeach
                    </tbody>

                    <tfoot class="">
                        <tr>
                            <td colspan="9" class="text-end fw-bold">TOTAL</td>
                            <td class="text-end fw-bold">
                                ₹ <span id="totalEmpEsi">0.00</span>
                            </td>
                            <td class="text-end fw-bold ">
                                ₹ <span id="totalErEsi">0.00</span>
                            </td>
                            <td class="text-end fw-bold fs-6">
                                ₹ <span id="grandTotalEsi">0.00</span>
                            </td>
                        </tr>
                    </tfoot>

                </table>
                <button type="submit" class="btn btn-primary btn-sm mt-2">
                    Save ESIC Details
                </button>

</form>  
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

function calculateRow(row)
{
    let esicSalary = parseFloat(row.dataset.esicSalary) || 0;
    let esiApplicable = row.dataset.esi === 'Yes';

    let empEsi = 0;
    let erEsi  = 0;

    if (esiApplicable && esicSalary <= 21000) {
        empEsi = esicSalary * 0.0075;
        erEsi  = esicSalary * 0.0325;
    }

    row.querySelector('.esi-emp').value   = empEsi.toFixed(2);
    row.querySelector('.esi-er').value    = erEsi.toFixed(2);
    row.querySelector('.esi-total').value = (empEsi + erEsi).toFixed(2);
}


const esicReasons = {
    0:{needLwd:false},1:{needLwd:false},2:{needLwd:true},3:{needLwd:true},
    4:{needLwd:true},5:{needLwd:true},6:{needLwd:true},
    7:{needLwd:false},8:{needLwd:false},9:{needLwd:false},
    10:{needLwd:true},11:{needLwd:false},12:{needLwd:false},13:{needLwd:false}
};

function syncReasonAndLwd(row)
{
const reasonSelect = row.querySelector('.reason');
const lwdInput = row.querySelector('.last-working-day');


if (reasonSelect.classList.contains('d-none')) {
lwdInput.classList.add('d-none');
lwdInput.value = '';
return;
}


const reasonCode = reasonSelect.value;


if (reasonCode !== '' && esicReasons[reasonCode]?.needLwd) {
lwdInput.classList.remove('d-none');
} else {
lwdInput.classList.add('d-none');
lwdInput.value = '';
}
}


document.querySelectorAll('.reason').forEach(select => {
select.addEventListener('change', function () {
syncReasonAndLwd(this.closest('tr'));
});
});

document.getElementById('esicExportForm').addEventListener('submit', function () {

    let rows = [];

    document.querySelectorAll('#payrollTable tbody tr').forEach(tr => {

        let tds = tr.querySelectorAll('td');

        rows.push({
            ip_number : tds[1]?.innerText.trim() ?? '',
            ip_name   : tds[2]?.innerText.trim() ?? '',
            days      : tr.querySelector('.working-days')?.value ?? '',
            wages     : parseFloat(tr.dataset.esicSalary || 0).toFixed(2),
            reason    : tr.querySelector('.reason')?.value ?? '',
            lwd       : tr.querySelector('.last-working-day')?.value ?? ''
        });
    });

    document.getElementById('esic_rows').value = JSON.stringify(rows);
});



document.querySelectorAll('#payrollTable tbody tr').forEach(row => {
calculateRow(row);
syncReasonAndLwd(row);
});
</script>

@endsection
