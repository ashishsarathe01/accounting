@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
/* Remove number arrows */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] { -moz-appearance: textfield; }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Error messages --}}
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
                    title-border-redius border-divider shadow-sm">
                    Add Supplier (Boiler Fuel)
                </h5>

                {{-- FUEL FORM (scoped IDs & classes) --}}
                <form id="fuelForm"
                      class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm mt-3"
                      method="POST"
                      action="{{ route('fuel-supplier.store') }}">

                    @csrf
                    <input type="hidden" id="fuel_rate_date" name="fuel_rate_date">

                    <div class="row">

                        {{-- Account --}}
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Account</label>
                            <select class="form-select select2-single-fuel" id="fuel_account" name="account" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Date</label>
                            <input type="date" class="form-control" id="fuel_date" name="fuel_date" required>
                        </div>

                        {{-- ITEM --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Item</label>
                            <select class="form-select item-fuel" name="item[]" id="item_1" data-id="1" required>
                                <option value="">Select Item</option>
                                @foreach($items as $i)
                                    <option value="{{ $i->id }}">{{ $i->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Price --}}
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Price</label>
                            <input type="text" class="form-control item_price_fuel" name="item_price[]" 
                                   id="item_price_1" data-id="1" required placeholder="Price">
                        </div>

                        {{-- Add More --}}
                        <div class="mb-1 col-md-1">
                            <button type="button" id="addFuelRow" class="btn btn-primary" title="Add item" style="margin-top:35px;">
                                +
                            </button>
                        </div>

                        <div class="w-100"></div>
                        <div class="add_fuel_div w-100 px-3"></div>

                        {{-- STATUS --}}
                        <div class="mb-3 col-md-3 mt-3">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="">Select</option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-xs-primary mt-2">SUBMIT</button>

                </form>
                {{-- FORM END --}}
            </div>

        </div>
    </section>
</div>

@include('layouts.footer')

{{-- Scoped JS for Boiler Fuel page --}}
<script>
(function() {
    // Items from server
    const items = @json($items || []);

    // Keep index for rows
    let fuelIndex = 1;

    // Init Select2 only for this form
    function initSelects() {
        if (jQuery && jQuery().select2) {
            $(".select2-single-fuel").select2({
                width: '100%',
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') return data;
                    function normalize(str) { return (str || '').toLowerCase().replace(/[.\s]/g, ''); }
                    return normalize(data.text).indexOf(normalize(params.term)) > -1 ? data : null;
                }
            });
        }
    }

    // Build a new item row
    function buildFuelRow(idx) {
        const options = items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
        return `
        <div class="row mt-3 new-row-fuel" data-idx="${idx}">
            <div class="mb-3 col-md-3">
                <label class="form-label">Item</label>
                <select class="form-select item-fuel" name="item[]" id="item_${idx}" data-id="${idx}" required>
                    <option value="">Select Item</option>
                    ${options}
                </select>
            </div>
            <div class="mb-3 col-md-3">
                <label class="form-label">Price</label>
                <input type="text" class="form-control item_price_fuel" name="item_price[]" id="item_price_${idx}" data-id="${idx}" required placeholder="Price">
            </div>
            <div class="mb-1 col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove_fuel_row">X</button>
            </div>
        </div>`;
    }

    // On document ready
    document.addEventListener('DOMContentLoaded', function() {
        initSelects();

        // Add row handler
        document.getElementById('addFuelRow').addEventListener('click', function(e){
            e.preventDefault();
            fuelIndex++;
            const html = buildFuelRow(fuelIndex);
            document.querySelector('.add_fuel_div').insertAdjacentHTML('beforeend', html);
        });

        // Remove row (delegate)
        document.addEventListener('click', function(e){
            if(e.target && e.target.matches('.remove_fuel_row')){
                e.target.closest('.new-row-fuel').remove();
            }
        });

        // When item select changes, request price
        document.addEventListener('change', function(e){
            if (e.target && e.target.matches('.item-fuel')) {
                const select = e.target;
                const idx = select.dataset.id;
                const itemId = select.value;
                const accountId = document.querySelector('#fuel_account').value;
                const date = document.querySelector('#fuel_date').value;

                // Clear price field first
                const priceField = document.getElementById('item_price_' + idx);
                if(priceField) priceField.value = '';

                if(!itemId) return;

                // Basic validation
                if(!date){
                    alert('Please select Date first');
                    select.value = '';
                    select.focus();
                    return;
                }
                // POST to your existing endpoint
                fetch("{{ url('fuel_price-by-item') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        account_id: accountId,
                        date: date
                    })
                }).then(r => r.json())
                  .then(res => {
                      if(res && res.rate){
                          if(priceField) priceField.value = res.rate.item_price;
                          // update hidden rate date if returned
                          if(res.latestDate) document.getElementById('fuel_rate_date').value = res.latestDate;
                      } else {
                          // no rate found, leave empty
                      }
                  }).catch(err => {
                      console.error('fuel_price-by-item error', err);
                      // optionally show user
                  });
            }
        });

        // When date changes - re-trigger item selects to refresh prices
        document.getElementById('fuel_date').addEventListener('change', function(){
            // loop through all item selects and trigger change
            document.querySelectorAll('.item-fuel').forEach(s => {
                if(s.value) s.dispatchEvent(new Event('change'));
            });
        });
    });
})();
</script>

@endsection
