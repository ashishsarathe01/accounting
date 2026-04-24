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
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <h5 class="transaction-table-title m-0 py-2">
                            Create Payroll Head
                        </h5>
                    </div>

                    <div class="card shadow-sm mt-4">
                        <div class="card-body">

                            <form action="{{ route('payroll.store') }}" method="POST">
                                @csrf

                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" id="name"
                                            class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="type" id="type"
                                                class="form-select" required>
                                            <option value="">Select Type</option>
                                            <option value="basic">Basic</option>
                                            <option value="da">Dearness Allowance (DA)</option>
                                            <option value="esic">ESIC</option>
                                            <option value="pf">PF</option>
                                            <option value="tds">TDS</option>
                                            <option value="other">Other</option>
                                        </select>
                                                                        </div>
                                        {{-- Adjustment Type (Only for Other) --}}
                                        <div class="col-md-6 mb-3" id="adjustment_section" style="display:none;">
                                            <label class="form-label">Adjustment Type</label>
                                            <select name="adjustment_type" class="form-select">
                                                <option value="">Select</option>
                                                <option value="addictive">Addictive</option>
                                                <option value="subtractive">Subtractive</option>
                                            </select>
                                        </div>
                                    <div class="col-md-6 mb-3" id="income_type_section">
                                        <label class="form-label">Income Type</label>
                                        <select name="income_type" class="form-select">
                                            <option value="fixed" selected>Fixed</option>
                                            <option value="variable">Variable</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Link Account For Posting</label>
                                        <select name="linked_account_id" class="form-select select2" required>
                                            <option value="">Select Account</option>
                                            @foreach($account_list as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3" id="affect_gross_section">
                                        <label class="form-label">Affect Gross Salary</label>
                                        <select name="affect_gross_salary"
                                                class="form-select">
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    {{-- Affect Net Salary (Only if Gross = No & Type = Other) --}}
                                    <div class="col-md-6 mb-3" id="affect_net_section" style="display:none;">
                                        <label class="form-label">Affect Net Salary</label>
                                        <select name="affect_net_salary" class="form-select">
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Name To Display On Salary Slip
                                        </label>
                                        <input type="text" name="slip_name"
                                            id="slip_name"
                                            class="form-control">
                                    </div>

                                    <div class="col-md-6 mb-3"
                                        id="gratuity_section">
                                        <label class="form-label">
                                            Use For Gratuity
                                        </label>
                                        <select name="use_for_gratuity"
                                                class="form-select">
                                            <option value="1" selected>Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Calculation Type
                                        </label>

                                        <select id="calculation_type_display"
                                                class="form-select">
                                            <option value="user_defined" selected>
                                                User Defined
                                            </option>
                                            <option value="percentage">
                                                As % of Basic
                                            </option>
                                            <option value="custom_formula" id="custom_formula_option" style="display:none;">
                                                Custom Formula (Rate %)
                                            </option>
                                        </select>

                                        <input type="hidden"
                                            name="calculation_type"
                                            id="calculation_type"
                                            value="user_defined">
                                    </div>

                                    {{-- Percentage --}}
                                    <div class="col-md-6 mb-3"
                                        id="percentage_section"
                                        style="display:none;">

                                        <label class="form-label">
                                            Percentage (%)
                                        </label>

                                        <input type="number"
                                            step="0.01"
                                            name="percentage"
                                            class="form-control"
                                            placeholder="Enter percentage">

                                    </div>

                                    {{-- Formula Heads (Beside Percentage) --}}
                                    <div class="col-md-6 mb-3"
                                        id="formula_section"
                                        style="display:none;">

                                        <label class="form-label">Select Heads for Formula</label>

                                        <div class="border p-3 rounded" style="height: 120px; overflow-y:auto;">

                                            @foreach($existing_heads as $head)

                                        @if(
                                            $head->type == 'basic' ||
                                            $head->type == 'da' ||
                                            $head->type == 'esic' ||
                                            ($head->type == 'other' && $head->adjustment_type == 'addictive')
                                        )

                                            <div class="form-check">
                                                <input class="form-check-input formula-head"
                                                    type="checkbox"
                                                    name="formula_heads[]"
                                                    value="{{ $head->id }}">
                                                <label class="form-check-label">
                                                    {{ $head->name }}
                                                </label>
                                            </div>

                                        @endif

                                    @endforeach

                                    </div>

                                    {{-- Formula Preview Full Width --}}
                                    <div class="col-md-12 mb-3"
                                        id="formula_preview_section"
                                        style="display:none;">

                                        <strong>Formula Preview:</strong>
                                        <div id="formula_preview" class="text-primary mt-1"></div>

                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit"
                                            class="btn btn-success">
                                        Save Payroll Head
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

    {{-- SCRIPT --}}
    <script>

    const nameField = document.getElementById('name');
    const slipField = document.getElementById('slip_name');
    const typeField = document.getElementById('type');
    const calcDisplay = document.getElementById('calculation_type_display');
    const calcHidden = document.getElementById('calculation_type');
    const percentageSection = document.getElementById('percentage_section');
    const gratuitySection = document.getElementById('gratuity_section');
    const adjustmentSection = document.getElementById('adjustment_section');
    const affectGrossField = document.querySelector('[name="affect_gross_salary"]');
    const affectNetSection = document.getElementById('affect_net_section');
    const customFormulaOption = document.getElementById('custom_formula_option');
    const formulaSection = document.getElementById('formula_section');
    const formulaPreviewSection = document.getElementById('formula_preview_section');
    const percentageInput = document.querySelector('[name="percentage"]');
    const formulaPreview = document.getElementById('formula_preview');
    const formulaCheckboxes = document.querySelectorAll('.formula-head');


    nameField.addEventListener('keyup', function(){
        slipField.value = this.value;
    });


    function adjustForm() {

        const type = typeField.value;

        adjustmentSection.style.display = 'none';
        affectNetSection.style.display = 'none';
        gratuitySection.style.display = 'none';
        formulaSection.style.display = 'none';
        formulaPreviewSection.style.display = 'none';
        percentageSection.style.display = 'none';

        customFormulaOption.style.display = 'none';

        calcDisplay.removeAttribute('disabled');
    if (type === 'esic' || type === 'pf') {

        affectGrossField.value = '1'; // auto select YES
        affectGrossField.setAttribute('readonly', true); // make readonly

        // optional: disable instead of readonly (better UX)
        // affectGrossField.setAttribute('disabled', true);

    } else {

        affectGrossField.removeAttribute('readonly');
        // affectGrossField.removeAttribute('disabled');
    }

        if (type === 'basic') {

            calcDisplay.value = 'user_defined';
            calcDisplay.setAttribute('disabled', true);
            gratuitySection.style.display = 'block';
        }

        else if (type === 'da') {

            gratuitySection.style.display = 'block';
        }

        else if (type === 'esic') {

            gratuitySection.style.display = 'none';

            calcDisplay.removeAttribute('disabled');

            customFormulaOption.style.display = 'block';

            // Default selection (better UX)
            if (calcDisplay.value === 'user_defined') {
                calcDisplay.value = 'percentage';
            }

            adjustmentSection.style.display = 'none';

            affectNetSection.style.display = 'none';
        }

        else if (type === 'other') {

            adjustmentSection.style.display = 'block';

            customFormulaOption.style.display = 'block';

            
        }

        handleCalculationDisplay();
        // universal logic for all types
if (affectGrossField.value === '0') {
    affectNetSection.style.display = 'block';
} else {
    affectNetSection.style.display = 'none';
}
    }

    function handleCalculationDisplay(){

        const type = typeField.value;

        if (calcDisplay.value === 'percentage') {

            percentageSection.style.display = 'block';
            formulaSection.style.display = 'none';
            formulaPreviewSection.style.display = 'none';
        }

        else if (
        calcDisplay.value === 'custom_formula' &&
        (type === 'other' || type === 'esic')
    ) {

            percentageSection.style.display = 'block';
            formulaSection.style.display = 'block';
            formulaPreviewSection.style.display = 'block';
        }

        else {

            percentageSection.style.display = 'none';
            formulaSection.style.display = 'none';
            formulaPreviewSection.style.display = 'none';
        }

        calcHidden.value = calcDisplay.value;
    }


    typeField.addEventListener('change', adjustForm);
    calcDisplay.addEventListener('change', handleCalculationDisplay);

    affectGrossField.addEventListener('change', function(){

    if (this.value === '0') {
        affectNetSection.style.display = 'block';
    } else {
        affectNetSection.style.display = 'none';

        // optional reset to default YES
        document.querySelector('[name="affect_net_salary"]').value = '1';
    }

});


    function updateFormulaPreview() {

        const selected = [];

        formulaCheckboxes.forEach(cb => {
            if (cb.checked) {

                // get label text (head name)
                const label = cb.closest('.form-check')
                                .querySelector('label')
                                .innerText;

                selected.push(label);
            }
        });

        const rate = percentageInput.value || 0;

        if (selected.length > 0 && rate > 0) {
            formulaPreview.innerHTML =
                '((' + selected.join(' + ') + ') × ' + rate + '%)';
        } else {
            formulaPreview.innerHTML = '';
        }
    }

    formulaCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateFormulaPreview);
    });

    percentageInput.addEventListener('keyup', updateFormulaPreview);


    adjustForm();
    $(document).ready(function(){

        $('.select2').select2({
            width: '100%',
            placeholder: "Select Account",
            allowClear: true
        });

    });
    </script>

    @endsection