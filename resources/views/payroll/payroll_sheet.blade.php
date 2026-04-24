@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
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
                <div class="d-flex justify-content-between align-items-center 
                    table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
                    position-relative title-border-redius border-divider shadow-sm">

                    <h5 class="m-0">Payroll Sheet</h5>
                    <a href="{{ route('payroll.settings') }}" class="btn btn-xs-primary d-flex align-items-center">
                        SETTINGS
                    </a>
                </div>
                <form method="GET" action="{{ route('payroll.index') }}" class="mb-2 d-flex align-items-center gap-2 px-3">
    <label class="fw-bold mb-0">Month:</label>
    <input type="month"
           name="month_year"
           class="form-control form-control-sm"
           style="width:180px"
           value="{{ $monthYear }}"
           onchange="this.form.submit()">
</form>
   <form method="POST" action="{{ route('payroll.store') }}">
    @csrf

    <input type="hidden" name="month_year" value="{{ $monthYear }}">

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center" id="payrollTable">

            <thead class="table-light">
                <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Branch</th>
                    <th>Salary (Month)</th>
                    <th>Absent</th>
                    <th>Basic Salary</th>
                    <th>DA</th>
                    <th>Incentive</th>
                    <th>Gross Salary</th>
                    <th>TDS</th>
                    <th>ESI</th>
                    <th>PF</th>
                    <th>LWF</th>
                    <th>Other Deduction</th>
                    <th>Salary in Hand</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $user)
                @php
                $pay = $payrolls[$user->id] ?? null;
                @endphp
                    <tr data-salary="{{ $pay ? $pay->salary : $user->salary }}"
                        data-esi="{{ $user->esi_applicable }}"
                        data-pf="{{ $user->pf_applicable }}">
                        <input type="hidden" name="payroll_id[]" value="{{ $pay->id ?? '' }}">

                         <td>{{ $index + 1 }}</td> 
                         <td>{{ $user->name }}</td>
                        <input type="hidden" name="employee_id[]" value="{{ $user->id }}">
                        <input type="hidden" name="branch[]" value="{{ $user->branch }}" >
                        <td>{{ $user->branch }}</td>

                        <td>
                        <input type="hidden"
                        name="salary[]"
                        value="{{ $pay->salary ?? $user->salary }}">


                        <input type="text"
                        class="form-control form-control-sm text-end"
                        value="{{ number_format($pay->salary ?? $user->salary, 2) }}"
                        disabled>
                        </td>

                        <td>
                            <input type="number" class="form-control form-control-sm text-end absent"
                                name="absent[]" value="{{ $pay->absent ?? 0 }}">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end basic" readonly>
                            <input type="hidden" name="basic_salary[]" class="basic-hidden">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end da" readonly>
                            <input type="hidden" name="da[]" class="da-hidden">
                        </td>

                        <td>
                            <input type="number" class="form-control form-control-sm text-end incentive"
                                name="incentive[]" value="{{ $pay->incentive ?? 0 }}">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end gross" readonly>
                            <input type="hidden" name="gross_salary[]" class="gross-hidden">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end tds"
                                name="tds[]" value="{{ $pay->tds ?? 0 }}">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end esi"
                                name="esi[]" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end pf"
                                name="pf[]" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end lwf"
                                name="lwf[]" value="34" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end other"
                                name="other_deductions[]" value="{{ $pay->other_deductions ?? 0 }}">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm text-end net" readonly>
                            <input type="hidden" name="net_payment[]" class="net-hidden">
                        </td>
                    </tr>
                    @endforeach

            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary mt-3">
    Save Payroll
    </button>
</form>

</div>
<input type="hidden" id="monthYear" value="{{ $monthYear }}">
<div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key w-100">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">F3
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">
                  Search Menu
               </button>
            </div>
         </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<script>
function getDaysInMonth(monthYear) {
    if (!monthYear) return 30; // fallback
    let parts = monthYear.split('-'); // YYYY-MM
    return new Date(parts[0], parts[1], 0).getDate();
}

document.querySelectorAll('#payrollTable tbody tr').forEach(row => {
    calculateRow(row);
});

document.querySelectorAll('.absent, .incentive, .other, .tds').forEach(input => {
    input.addEventListener('input', function () {
        calculateRow(this.closest('tr'));
    });
});

function calculateRow(row) {

    let salary    = parseFloat(row.dataset.salary) || 0;
    let absent    = parseFloat(row.querySelector('.absent').value) || 0;
    let incentive = parseFloat(row.querySelector('.incentive').value) || 0;
    let other     = parseFloat(row.querySelector('.other').value) || 0;
    let tds       = parseFloat(row.querySelector('.tds').value) || 0;

    let monthYear  = document.getElementById('monthYear').value;
    let daysInMonth = getDaysInMonth(monthYear);

    if (absent > daysInMonth) absent = daysInMonth;

    let basic = (salary / daysInMonth) * (daysInMonth - absent);
    let da = 0;

    let gross = basic + da + incentive;

    let esi = (row.dataset.esi === 'Yes') ? (gross * 0.0075) : 0;
    let pf  = (row.dataset.pf === 'Yes')  ? Math.min(gross * 0.12, 1800) : 0;
    let lwf = 34;

    let net = gross - (esi + pf + lwf + other + tds);

    row.querySelector('.basic').value = basic.toFixed(2);
    row.querySelector('.da').value    = da.toFixed(2);
    row.querySelector('.gross').value = gross.toFixed(2);
    row.querySelector('.esi').value   = esi.toFixed(2);
    row.querySelector('.pf').value    = pf.toFixed(2);
    row.querySelector('.net').value   = net.toFixed(2);

    row.querySelector('.basic-hidden').value = basic.toFixed(2);
    row.querySelector('.da-hidden').value    = da.toFixed(2);
    row.querySelector('.gross-hidden').value = gross.toFixed(2);
    row.querySelector('.net-hidden').value   = net.toFixed(2);
}
</script>
@endsection
