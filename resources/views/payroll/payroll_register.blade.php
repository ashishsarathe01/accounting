@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
.bg-mint {
    background: #f8fafc !important;
}

.table th {
    font-size: 13px;
    white-space: nowrap;
    vertical-align: middle;
}

.table td {
    font-size: 13px;
    vertical-align: middle;
}

.absent-input {
    width: 80px;
    margin: auto;
    text-align: center;
}

.gross-total,
.net-total {
    font-weight: 600;
    background: #f1f5f9;
}

.card {
    border-radius: 10px;
}

.btn {
    padding: 8px 25px;
    border-radius: 6px;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- Alerts --}}
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- Title Bar --}}
<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">
        Payroll Register
    </h5>
</div>

{{-- Month / Year Filter --}}
<div class="card shadow-sm mt-4">
<div class="card-body">

<form method="POST" action="{{ route('payroll.register.generate') }}">
@csrf

<div class="row align-items-end">

    <div class="col-md-3 mb-3">
        <label class="form-label">Month</label>
        <select name="month" class="form-select" required>
            <option value="">Select Month</option>
            @for($m=1; $m<=12; $m++)
                <option value="{{ $m }}"
                    {{ (isset($month) && $month == $m) ? 'selected' : '' }}>
                    {{ date('F', mktime(0,0,0,$m,1)) }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Year</label>
        <select name="year" class="form-select" required>
            <option value="">Select Year</option>
            @for($y = date('Y'); $y >= date('Y')-5; $y--)
                <option value="{{ $y }}"
                    {{ (isset($year) && $year == $y) ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <button type="submit" class="btn btn-primary w-100">
            Generate
        </button>
    </div>

</div>
</form>

</div>
</div>

@if(isset($employees))

<form method="POST" action="{{ route('payroll.register.store') }}">
@csrf

<input type="hidden" name="month" value="{{ $month }}">
<input type="hidden" name="year" value="{{ $year }}">
<input type="hidden" name="total_days" value="{{ $totalDays }}">

<div class="card shadow-sm mt-4">
<div class="card-body">

<div class="table-responsive">
<table class="table table-hover table-bordered align-middle text-center">

<thead class="table-light">

<tr>
    <th rowspan="2">Name</th>
    <th rowspan="2">Absent</th>

    @if($grossAddHeads->count() > 0)
        <th colspan="{{ $grossAddHeads->count() }}">Gross Addictive</th>
    @endif

    @if($grossSubHeads->count() > 0)
        <th colspan="{{ $grossSubHeads->count() }}">Gross Subtractive</th>
    @endif

    <th rowspan="2">Gross Salary</th>

    @if($netAddHeads->count() > 0)
        <th colspan="{{ $netAddHeads->count() }}">Net Addictive</th>
    @endif

    @if($netSubHeads->count() > 0)
        <th colspan="{{ $netSubHeads->count() }}">Net Subtractive</th>
    @endif

    <th rowspan="2">Net Payable</th>
</tr>

<tr>
    @foreach($grossAddHeads as $head)
        <th>{{ ucfirst($head->name) }}</th>
    @endforeach

    @foreach($grossSubHeads as $head)
        <th>{{ ucfirst($head->name) }}</th>
    @endforeach

    @foreach($netAddHeads as $head)
        <th>{{ ucfirst($head->name) }}</th>
    @endforeach

    @foreach($netSubHeads as $head)
        <th>{{ ucfirst($head->name) }}</th>
    @endforeach
</tr>

</thead>

<tbody>

@foreach($employees as $employee)
<tr>

<td>{{ $employee->name }}</td>

<td>
    <input type="number"
           name="employees[{{ $employee->id }}][absent]"
           class="form-control absent-input"
           min="0"
           max="{{ $totalDays ?? 31 }}"
           value="{{ $existingSlips[$employee->id]->absent_days ?? 0 }}">
</td>

{{-- ================= GROSS HEADS ================= --}}
@foreach($grossAddHeads as $head)

@php
    $amount = 0;
    if(isset($salaryStructures[$employee->id])) {
        foreach($salaryStructures[$employee->id] as $structure) {
            if($structure->payroll_head_id == $head->id) {
                $amount = $structure->amount;
            }
        }
    }
@endphp

<td class="gross-head-amount"
    data-original="{{ $amount }}"
    data-head-id="{{ $head->id }}"
    data-head-type="{{ $head->type }}"
    data-adjustment-type="{{ $head->adjustment_type }}"
    data-type="{{ $head->calculation_type }}"
    data-percentage="{{ $head->percentage ?? 0 }}"
    data-formula='@json($head->formula_heads)'>

    @php
$displayAmount = $amount;

if(isset($existingSlips[$employee->id])){

    $slipId = $existingSlips[$employee->id]->id;

    if(isset($existingSlipDetails[$slipId])){
        foreach($existingSlipDetails[$slipId] as $detail){
            if($detail->payroll_head_id == $head->id){
                $displayAmount = $detail->amount;
            }
        }
    }
}
@endphp

<span class="head-value">
    {{ number_format($displayAmount, 2) }}
</span>

    <input type="hidden"
           class="head-hidden"
           name="employees[{{ $employee->id }}][heads][{{ $head->id }}]"
           value="{{ $amount }}">
</td>
@endforeach


@foreach($grossSubHeads as $head)

@php
    $amount = 0;
    if(isset($salaryStructures[$employee->id])) {
        foreach($salaryStructures[$employee->id] as $structure) {
            if($structure->payroll_head_id == $head->id) {
                $amount = $structure->amount;
            }
        }
    }
@endphp

<td class="gross-head-amount"
    data-original="{{ $amount }}"
    data-head-id="{{ $head->id }}"
    data-head-type="{{ $head->type }}"
    data-adjustment-type="{{ $head->adjustment_type }}"
    data-type="{{ $head->calculation_type }}"
    data-percentage="{{ $head->percentage ?? 0 }}"
    data-formula='@json($head->formula_heads)'>

    <span class="head-value">
        {{ number_format($amount, 2) }}
    </span>

    <input type="hidden"
           class="head-hidden"
           name="employees[{{ $employee->id }}][heads][{{ $head->id }}]"
           value="{{ $amount }}">
</td>
@endforeach


{{-- ================= GROSS TOTAL ================= --}}
@php
$grossDisplay = 0;

if(isset($existingSlips[$employee->id])){
    $grossDisplay = $existingSlips[$employee->id]->gross_salary;
}
@endphp

<td class="gross-total">

    <span class="gross-display">
        {{ number_format($grossDisplay, 2) }}
    </span>

    <input type="hidden"
           class="gross-hidden"
           name="employees[{{ $employee->id }}][gross]"
           value="{{ $grossDisplay }}">
</td>



{{-- ================= NET HEADS ================= --}}
@foreach($netAddHeads as $head)

@php
    $amount = 0;
    if(isset($salaryStructures[$employee->id])) {
        foreach($salaryStructures[$employee->id] as $structure) {
            if($structure->payroll_head_id == $head->id) {
                $amount = $structure->amount;
            }
        }
    }
@endphp

<td class="net-head-amount"
    data-original="{{ $amount }}"
    data-adjustment-type="{{ $head->adjustment_type }}">

    <span class="head-value">
        {{ number_format($amount, 2) }}
    </span>

    <input type="hidden"
           class="head-hidden"
           name="employees[{{ $employee->id }}][heads][{{ $head->id }}]"
           value="{{ $amount }}">
</td>
@endforeach


@foreach($netSubHeads as $head)

@php
    $amount = 0;
    if(isset($salaryStructures[$employee->id])) {
        foreach($salaryStructures[$employee->id] as $structure) {
            if($structure->payroll_head_id == $head->id) {
                $amount = $structure->amount;
            }
        }
    }
@endphp

<td class="net-head-amount"
    data-original="{{ $amount }}"
    data-adjustment-type="{{ $head->adjustment_type }}">

    <span class="head-value">
        {{ number_format($amount, 2) }}
    </span>

    <input type="hidden"
           class="head-hidden"
           name="employees[{{ $employee->id }}][heads][{{ $head->id }}]"
           value="{{ $amount }}">
</td>
@endforeach


{{-- ================= NET TOTAL ================= --}}
@php
$netDisplay = 0;

if(isset($existingSlips[$employee->id])){
    $netDisplay = $existingSlips[$employee->id]->net_salary;
}
@endphp

<td class="net-total">

    <span class="net-display">
        {{ number_format($netDisplay, 2) }}
    </span>

    <input type="hidden"
           class="net-hidden"
           name="employees[{{ $employee->id }}][net]"
           value="{{ $netDisplay }}">
</td>

</tr>
@endforeach

</tbody>
</table>
    </div>

@if($existingSlips->count() > 0)
    <button type="submit" class="btn btn-warning mt-3">
        Update Payroll
    </button>
@else
    <button type="submit" class="btn btn-success mt-3">
        Finalize Payroll
    </button>
@endif
</div>

</div>
</div>

</form>
@endif

</div>
</div>
</section>
</div>
@include('layouts.footer')

<script>
document.querySelectorAll('.absent-input').forEach(function(input){

    input.addEventListener('input', function(){

        let row = this.closest('tr');
        let totalDays = {{ $totalDays ?? 0 }};
        let absent = parseFloat(this.value) || 0;

        if(absent > totalDays){
            this.value = totalDays;
            absent = totalDays;
        }

        let present = totalDays - absent;

        let grossTotal = 0;
        let deductionTotal = 0;

        let calculatedHeads = {}; // store calculated values by head ID

        // ==========================================
        // 1️⃣ CALCULATE BASIC FIRST
        // ==========================================
        row.querySelectorAll('.gross-head-amount').forEach(function(cell){

            if(cell.dataset.headType === 'basic'){

                let headId = cell.dataset.headId;
                let originalBasic = parseFloat(cell.dataset.original) || 0;

                let perDayBasic = originalBasic / totalDays;
                let basicAmount = perDayBasic * present;

                calculatedHeads[headId] = basicAmount;

                // ✅ UPDATE SPAN ONLY
                cell.querySelector('.head-value').innerText = basicAmount.toFixed(2);

                // ✅ UPDATE HIDDEN INPUT
                cell.querySelector('.head-hidden').value = basicAmount.toFixed(2);

                grossTotal += basicAmount;
            }

        });

        // ==========================================
        // 2️⃣ CALCULATE OTHER GROSS HEADS
        // ==========================================
        row.querySelectorAll('.gross-head-amount').forEach(function(cell){

            let headId = cell.dataset.headId;
            let headType = cell.dataset.headType;

            if(headType !== 'basic'){

                let calcType = cell.dataset.type;
                let percentage = parseFloat(cell.dataset.percentage) || 0;
                let original = parseFloat(cell.dataset.original) || 0;
                let formula = cell.dataset.formula ? JSON.parse(cell.dataset.formula) : null;

                let value = 0;

                if(calcType === 'percentage'){

                    let basicHead = Object.keys(calculatedHeads).find(id => {
                        return row.querySelector(
                            `.gross-head-amount[data-head-id="${id}"]`
                        )?.dataset.headType === 'basic';
                    });

                    let basicValue = calculatedHeads[basicHead] || 0;
                    value = (basicValue * percentage) / 100;
                }

                else if(calcType === 'custom_formula' && formula){

                    let formulaBase = 0;

                    row.querySelectorAll('.gross-head-amount').forEach(function(otherCell){

                        let otherId = otherCell.dataset.headId;
                        let otherValue = parseFloat(
                            otherCell.querySelector('.head-value').innerText
                        ) || 0;

                        if(formula.includes(otherId)){
                            formulaBase += otherValue;
                        }

                    });

                    value = (formulaBase * percentage) / 100;
                }

                else {

                    let perDay = original / totalDays;
                    value = perDay * present;
                }

                calculatedHeads[headId] = value;

                // ✅ UPDATE SPAN
                cell.querySelector('.head-value').innerText = value.toFixed(2);

                // ✅ UPDATE HIDDEN
                cell.querySelector('.head-hidden').value = value.toFixed(2);

                grossTotal += value;
            }

        });

        // ==========================================
        // 3️⃣ CALCULATE NET HEADS
        // ==========================================
        row.querySelectorAll('.net-head-amount').forEach(function(cell){

            let calcType = cell.dataset.type;
            let percentage = parseFloat(cell.dataset.percentage) || 0;
            let original = parseFloat(cell.dataset.original) || 0;
            let formula = cell.dataset.formula ? JSON.parse(cell.dataset.formula) : null;
            let adjustmentType = cell.dataset.adjustmentType;

            let value = 0;

            if(calcType === 'percentage'){

                let basicCell = row.querySelector(
                    '.gross-head-amount[data-head-type="basic"]'
                );

                let basicValue = basicCell
                    ? parseFloat(basicCell.querySelector('.head-value').innerText) || 0
                    : 0;

                value = (basicValue * percentage) / 100;
            }

            else if(calcType === 'custom_formula' && formula){

                let formulaBase = 0;

                row.querySelectorAll('.gross-head-amount').forEach(function(otherCell){

                    let otherId = otherCell.dataset.headId;
                    let otherValue = parseFloat(
                        otherCell.querySelector('.head-value').innerText
                    ) || 0;

                    if(formula.includes(otherId)){
                        formulaBase += otherValue;
                    }

                });

                value = (formulaBase * percentage) / 100;
            }

            else {

                let perDay = original / totalDays;
                value = perDay * present;
            }

            // ✅ UPDATE SPAN
            cell.querySelector('.head-value').innerText = value.toFixed(2);

            // ✅ UPDATE HIDDEN
            cell.querySelector('.head-hidden').value = value.toFixed(2);

            if(adjustmentType === 'subtractive'){
                deductionTotal += value;
            }
            else if(adjustmentType === 'addictive'){
                deductionTotal -= value;
            }

        });

        // ==========================================
        // FINAL TOTALS
        // ==========================================
        let finalNet = grossTotal - deductionTotal;

        // ✅ UPDATE GROSS
        row.querySelector('.gross-display').innerText = grossTotal.toFixed(2);
        row.querySelector('.gross-hidden').value = grossTotal.toFixed(2);

        // ✅ UPDATE NET
        row.querySelector('.net-display').innerText = finalNet.toFixed(2);
        row.querySelector('.net-hidden').value = finalNet.toFixed(2);

    });

});
</script>
@endsection