@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
     @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
<div class="container mt-4">

    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Set Aging Buckets</h5>

   

    <form method="POST" 
          action="{{ count($buckets) > 0 ? route('bucket.update') : route('bucket.store') }}"
          id="bucketForm">

        @csrf

        <table class="table table-bordered" id="bucketTable">
            <thead>
                <tr class="bg-light fw-bold">
                    <th style="width: 20%">From Days</th>
                    <th style="width: 20%">To Days</th>
                    <th style="width: 10%">Action</th>
                </tr>
            </thead>

            <tbody>

                @php 
                    $rowIndex = count($buckets) > 0 ? count($buckets) : 1;
                @endphp

                @if(count($buckets) > 0)
                    @foreach($buckets as $i => $b)
                        <tr>
                            <td>
                                <input type="number" 
                                    name="buckets[{{ $i }}][from_days]" 
                                    class="form-control fromDay" 
                                    value="{{ $b->from_days }}" 
                                    readonly>
                            </td>

                            <td>
                                <input type="number" 
                                    name="buckets[{{ $i }}][to_days]" 
                                    class="form-control toDay"
                                    value="{{ $b->to_days }}" required>
                            </td>

                            <td class="actionCol">
                                {{-- delete only on last row --}}
                                @if($i === count($buckets)-1 && count($buckets) > 1)
                                    <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>
                            <input type="number" name="buckets[0][from_days]" 
                                   class="form-control fromDay" value="0" readonly>
                        </td>
                        <td>
                            <input type="number" name="buckets[0][to_days]" 
                                   class="form-control toDay" required>
                        </td>
                        <td class="actionCol"></td>
                    </tr>
                @endif

            </tbody>
        </table>

        <p id="moreThanText" class="fw-bold text-primary mt-1"></p>

        <button type="button" id="addRowBtn" class="btn btn-primary mb-3">+ Add Row</button>

        <br>

        <button type="submit" class="btn btn-success px-4">
            {{ count($buckets) > 0 ? 'Update Buckets' : 'Save Buckets' }}
        </button>

    </form>

</div>
</div>
</div>
</section>
</div>

<script>
let rowIndex = {{ $rowIndex }};
updateLastRowStatus();
updateMoreThanMessage();
makeOnlyLastToDayEditable();

document.getElementById('addRowBtn').addEventListener('click', function () {

    let rows = document.querySelectorAll('#bucketTable tbody tr');
    let lastRow = rows[rows.length - 1];
    let lastToDays = parseInt(lastRow.querySelector('.toDay').value || 0);

    if (!lastToDays) {
        alert("Please enter 'To Days' for the last row before adding a new one.");
        return;
    }

    let newFromDays = lastToDays + 1;

    let tableBody = document.querySelector('#bucketTable tbody');

    let row = `
        <tr>
            <td>
                <input type="number" name="buckets[${rowIndex}][from_days]" 
                       class="form-control fromDay" value="${newFromDays}" readonly>
            </td>
            <td>
                <input type="number" name="buckets[${rowIndex}][to_days]" 
                       class="form-control toDay" required>
            </td>
            <td class="actionCol">
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>
    `;

    tableBody.insertAdjacentHTML('beforeend', row);
    rowIndex++;

    updateLastRowStatus();
    updateMoreThanMessage();
    makeOnlyLastToDayEditable();
});


// Remove Row (only last allowed)
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('removeRow')) {

        let rows = document.querySelectorAll('#bucketTable tbody tr');

        if (rows.length <= 1) return; // only one row → no delete

        event.target.closest('tr').remove();

        updateLastRowStatus();
        updateMoreThanMessage();
        makeOnlyLastToDayEditable();
    }
});


// Ensure only last row shows delete button
function updateLastRowStatus() {
    let rows = document.querySelectorAll('#bucketTable tbody tr');

    rows.forEach((row, i) => {
        let actionCol = row.querySelector('.actionCol');

        actionCol.innerHTML = ""; // remove all delete buttons

        if (i === rows.length - 1 && rows.length > 1) {
            actionCol.innerHTML = `<button type="button" class="btn btn-danger btn-sm removeRow">X</button>`;
        }
    });
}


// 🔥 MAKE ONLY LAST ROW'S TO_DAYS EDITABLE
function makeOnlyLastToDayEditable() {
    let rows = document.querySelectorAll('#bucketTable tbody tr');

    rows.forEach((row, i) => {
        let toDayInput = row.querySelector('.toDay');

        if (i === rows.length - 1) {
            toDayInput.readOnly = false;        // last row editable
            toDayInput.style.background = "";   // normal look
        } else {
            toDayInput.readOnly = true;         // others readonly
            toDayInput.style.background = "#E9ECEF"; // light background
        }
    });
}


// Update the "more than X days" message
function updateMoreThanMessage() {
    let rows = document.querySelectorAll('#bucketTable tbody tr');
    let lastRow = rows[rows.length - 1];
    let toValue = lastRow.querySelector('.toDay').value;

    let msgContainer = document.getElementById('moreThanText');

    if (toValue) {
        msgContainer.textContent = `And more than ${toValue} days`;
    } else {
        msgContainer.textContent = "";
    }
}


// Live update when user types "to days"
document.addEventListener("input", function (e) {
    if (e.target.classList.contains('toDay')) {
        updateMoreThanMessage();
    }
});


document.getElementById('bucketForm').addEventListener('submit', function (event) {
    let valid = true;

    document.querySelectorAll('tr').forEach(function (row) {
        let from = row.querySelector('.fromDay');
        let to = row.querySelector('.toDay');

        if (from && to) {
            let f = parseInt(from.value);
            let t = parseInt(to.value);

            if (t <= f) {
                alert(`"To Days" must be greater than "From Days".  
Row: ${from.value} - ${to.value}`);
                valid = false;
            }
        }
    });

    if (!valid) event.preventDefault();
});
</script>

@endsection
