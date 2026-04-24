@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
    .select2-container .select2-selection--single {
    height: 38px;
    padding-top: 5px;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Edit Payroll Head
                    </h5>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-body">

                        <form action="{{ route('payroll.update', $payroll_head->id) }}" method="POST">
                            @csrf

                            <div class="row">

                                {{-- Name --}}
                                <div class="col-md-6 mb-3">
                                    <label>Name</label>
                                    <input type="text"
                                           name="name"
                                           value="{{ $payroll_head->name }}"
                                           class="form-control"
                                           required>
                                </div>

                                {{-- Type (Disabled) --}}
                                <div class="col-md-6 mb-3">
                                    <label>Type</label>
                                    <select class="form-select" disabled>
                                        <option value="basic" {{ $payroll_head->type=='basic'?'selected':'' }}>Basic</option>
                                        <option value="da" {{ $payroll_head->type=='da'?'selected':'' }}>Dearness Allowance (DA)</option>
                                        <option value="esic" {{ $payroll_head->type=='esic'?'selected':'' }}>ESIC</option>
                                        <option value="other" {{ $payroll_head->type=='other'?'selected':'' }}>Other</option>
                                    </select>

                                    {{-- Hidden field to submit type --}}
                                    <input type="hidden" name="type" value="{{ $payroll_head->type }}">
                                </div>
                                {{-- Adjustment Type (Only for Other) --}}
                                @if($payroll_head->type == 'other')
                                <div class="col-md-6 mb-3">
                                    <label>Adjustment Type</label>

                                    <select class="form-select" disabled>
                                        <option value="addictive" {{ $payroll_head->adjustment_type=='addictive'?'selected':'' }}>
                                    Addictive
                                </option>
                                        <option value="subtractive" {{ $payroll_head->adjustment_type=='subtractive'?'selected':'' }}>
                                            Subtractive
                                        </option>
                                    </select>

                                    {{-- Hidden field to submit value --}}
                                    <input type="hidden"
                                        name="adjustment_type"
                                        value="{{ $payroll_head->adjustment_type }}">
                                </div>
                                @endif
                                {{-- Income Type --}}
                                <div class="col-md-6 mb-3">
                                    <label>Income Type</label>
                                    <select name="income_type" class="form-select">
                                        <option value="fixed" {{ $payroll_head->income_type=='fixed'?'selected':'' }}>Fixed</option>
                                        <option value="variable" {{ $payroll_head->income_type=='variable'?'selected':'' }}>Variable</option>
                                    </select>
                                </div>

                                {{-- Link Account --}}
                                <div class="col-md-6 mb-3">
                                    <label>Link Account</label>
                                    <select name="linked_account_id" class="form-select select2">
                                        <option value="">Select Account</option>
                                        @foreach($account_list as $account)
                                            <option value="{{ $account->id }}"
                                                {{ $payroll_head->linked_account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Affect Gross --}}
                                <div class="col-md-6 mb-3">
                                    <label>Affect Gross Salary</label>
                                    <select name="affect_gross_salary" id="affect_gross_salary" class="form-select">
                                        <option value="1" {{ $payroll_head->affect_gross_salary==1?'selected':'' }}>Yes</option>
                                        <option value="0" {{ $payroll_head->affect_gross_salary==0?'selected':'' }}>No</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3"
                                    id="affect_net_section"
                                    style="{{ $payroll_head->affect_gross_salary==1 ? 'display:none;' : '' }}">
                                    
                                    <label>Affect Net Salary</label>

                                    <select name="affect_net_salary" class="form-select">
                                        <option value="1" {{ ($payroll_head->affect_net_salary ?? 1) == 1 ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                        <option value="0" {{ ($payroll_head->affect_net_salary ?? 1) == 0 ? 'selected' : '' }}>
                                            No
                                        </option>
                                    </select>

                                </div>
                                
                                {{-- Slip Name --}}
                                <div class="col-md-6 mb-3">
                                    <label>Slip Name</label>
                                    <input type="text"
                                           name="slip_name"
                                           value="{{ $payroll_head->slip_name }}"
                                           class="form-control">
                                </div>

                                {{-- Gratuity --}}
                                @if(in_array($payroll_head->type, ['basic','da']))
                                <div class="col-md-6 mb-3">
                                    <label>Use For Gratuity</label>
                                    <select name="use_for_gratuity" class="form-select">
                                        <option value="1" {{ $payroll_head->use_for_gratuity==1?'selected':'' }}>Yes</option>
                                        <option value="0" {{ $payroll_head->use_for_gratuity==0?'selected':'' }}>No</option>
                                    </select>
                                </div>
                                @endif

                                {{-- Calculation Type --}}
                                <div class="col-md-6 mb-3">
                                    <label>Calculation Type</label>

                                    @if($payroll_head->type == 'basic')
                                        <input type="text"
                                               class="form-control"
                                               value="User Defined"
                                               readonly>
                                        <input type="hidden"
                                               name="calculation_type"
                                               value="user_defined">
                                    @else
                                        <select name="calculation_type"
                                                id="calculation_type"
                                                class="form-select">

                                            <option value="user_defined"
                                                {{ $payroll_head->calculation_type=='user_defined'?'selected':'' }}>
                                                User Defined
                                            </option>

                                            <option value="percentage"
                                                {{ $payroll_head->calculation_type=='percentage'?'selected':'' }}>
                                                As % of Basic
                                            </option>

                                            @if(in_array($payroll_head->type, ['other','esic']))
                                            <option value="custom_formula"
                                                {{ $payroll_head->calculation_type=='custom_formula'?'selected':'' }}>
                                                Custom Formula (Rate %)
                                            </option>
                                            @endif

                                        </select>
                                    @endif
                                </div>

                                {{-- Percentage --}}
                                @if($payroll_head->type != 'basic')
                                <div class="col-md-6 mb-3"
                                id="percentage_section"
                                style="{{ in_array($payroll_head->calculation_type,['percentage','custom_formula']) ? '' : 'display:none;' }}">
                                    <label>Percentage (%)</label>
                                    <input type="number"
                                           step="0.01"
                                           name="percentage"
                                           value="{{ $payroll_head->percentage }}"
                                           class="form-control">
                                </div>
                                @endif
                                @if(in_array($payroll_head->type, ['other','esic']))
                                <div class="col-md-12 mb-3"
                                    id="formula_section"
                                    style="{{ $payroll_head->calculation_type=='custom_formula'?'':'display:none;' }}">

                                    <label>Select Heads for Formula</label>

                                @php
                                $selected_heads = $payroll_head->formula_heads ?? [];
                                @endphp

                                <div class="border p-3 rounded" style="height:120px; overflow-y:auto;">

                                @foreach($existing_heads as $head)

                                    @if(
                                        $head->type == 'basic' ||
                                        $head->type == 'da' ||
                                        $head->type == 'esic' ||
                                        ($head->type == 'other' && $head->adjustment_type == 'addictive')
                                    )

                                        <div class="form-check">
                                            <input type="checkbox"
                                                class="form-check-input formula-head"
                                                name="formula_heads[]"
                                                value="{{ $head->id }}"
                                                {{ in_array($head->id, $selected_heads) ? 'checked' : '' }}>

                                            <label class="form-check-label">
                                                {{ $head->name }}
                                            </label>
                                        </div>

                                    @endif

                                @endforeach

                                </div>
                                </div>

                                <div class="col-md-12 mb-3"
                                    id="formula_preview_section"
                                    style="{{ $payroll_head->calculation_type=='custom_formula'?'':'display:none;' }}">

                                    <strong>Formula Preview:</strong>
                                    <div id="formula_preview" class="text-primary mt-1"></div>
                                </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Update Payroll Head
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

<script>

const calcField = document.getElementById('calculation_type');
const percentageSection = document.getElementById('percentage_section');
const formulaSection = document.getElementById('formula_section');
const formulaPreviewSection = document.getElementById('formula_preview_section');
const formulaPreview = document.getElementById('formula_preview');
const percentageInput = document.querySelector('[name="percentage"]');
const formulaCheckboxes = document.querySelectorAll('.formula-head');
const affectGrossField = document.getElementById('affect_gross_salary');
const affectNetSection = document.getElementById('affect_net_section');

if(affectGrossField && affectNetSection){

    // initial page load
    if(affectGrossField.value === '0'){
        affectNetSection.style.display = 'block';
    } else {
        affectNetSection.style.display = 'none';
    }

    affectGrossField.addEventListener('change', function(){

        if(this.value === '0'){
            affectNetSection.style.display = 'block';
        } else {
            affectNetSection.style.display = 'none';

            // reset to default YES
            const netField = document.querySelector('[name="affect_net_salary"]');
            if(netField) netField.value = '1';
        }

    });

}
if(calcField){
    calcField.addEventListener('change', function(){

        if(this.value === 'percentage'){
            percentageSection.style.display = 'block';
            if(formulaSection) formulaSection.style.display='none';
            if(formulaPreviewSection) formulaPreviewSection.style.display='none';
        }

        else if(this.value === 'custom_formula'){
            percentageSection.style.display = 'block';
            if(formulaSection) formulaSection.style.display='block';
            if(formulaPreviewSection) formulaPreviewSection.style.display='block';
        }

        else{
            percentageSection.style.display = 'none';
            if(formulaSection) formulaSection.style.display='none';
            if(formulaPreviewSection) formulaPreviewSection.style.display='none';
        }
    });
}

function updateFormulaPreview(){

    if(!formulaPreview) return;

    const selected=[];

    formulaCheckboxes.forEach(cb=>{
        if(cb.checked){

            const label = cb.closest('.form-check')
                            .querySelector('label')
                            .innerText;

            selected.push(label);
        }
    });

    const rate = percentageInput.value || 0;

    if(selected.length>0 && rate>0){
        formulaPreview.innerHTML =
            '((' + selected.join(' + ') + ') × ' + rate + '%)';
    }else{
        formulaPreview.innerHTML='';
    }
}

formulaCheckboxes.forEach(cb=>{
    cb.addEventListener('change', updateFormulaPreview);
});

if(percentageInput){
    percentageInput.addEventListener('keyup', updateFormulaPreview);
}

if(percentageInput && percentageInput.value){
    updateFormulaPreview();
}
$(document).ready(function(){

    $('.select2').select2({
        width: '100%',
        placeholder: "Select Account",
        allowClear: true
    });

});
</script>
@endsection