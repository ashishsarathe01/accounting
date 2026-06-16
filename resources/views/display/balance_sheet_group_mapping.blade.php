@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

<h5 class="master-table-title m-0 py-2">
Balance Sheet Group Mapping
</h5>

</div>
@if ($errors->has('trade_payable_type'))
<div class="alert alert-danger">
    {{ $errors->first('trade_payable_type') }}
</div>
@endif
<form method="POST"
action="{{ route('balancesheet.group.mapping.save') }}">

@csrf

<div class="card shadow-sm">

<div class="card-body p-0">

<table class="table table-bordered mb-0">

<thead>

<tr>
    <th width="35%">Account Group</th>
    <th width="45%">Balance Sheet Mapping</th>
    <th width="20%">Trade Payable Type</th>
</tr>

</thead>

<tbody>

@foreach($groups as $group)

<tr>

<td>
    {{$group->name}}
</td>

<td>

<select
class="form-select balance-mapping"
name="mapping[{{$group->unique_key}}]"
>

<option value="">
    Select
</option>

@foreach($balanceSheetOptions as $option)

<option
value="{{$option}}"
@if(
    isset($mappings[$group->unique_key])
    &&
    ($mappings[$group->unique_key]['mapping_name'] ?? '') == $option
)
selected
@endif
>
{{$option}}
</option>

@endforeach

</select>

</td>

<td>

<select
class="form-select trade-payable-type"
name="trade_payable_type[{{$group->unique_key}}]"
>

<option value="">
    Select
</option>

<option
value="A"
@if(
    isset($mappings[$group->unique_key])
    &&
    ($mappings[$group->unique_key]['trade_payable_type'] ?? '') == 'A'
)
selected
@endif
>
(A) Micro enterprises and small enterprises
</option>

<option
value="B"
@if(
    isset($mappings[$group->unique_key])
    &&
    ($mappings[$group->unique_key]['trade_payable_type'] ?? '') == 'B'
)
selected
@endif
>
(B) Others
</option>

</select>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

<div class="mt-3 text-end">

<button
type="submit"
class="btn btn-primary"
>
Save Mapping
</button>

</div>

</form>

</div>

</div>

</section>
</div>

@include('layouts.footer')

<script>

$(document).ready(function(){

    function toggleTradePayable()
    {
        $('.balance-mapping').each(function(){

            let value = $(this).val();

            let tradeDropdown = $(this)
                .closest('tr')
                .find('.trade-payable-type');

            if(value === 'Trade payables')
            {
                tradeDropdown.prop('disabled', false);
                tradeDropdown.prop('required', true);
            }
            else
            {
                tradeDropdown.val('');
                tradeDropdown.prop('disabled', true);
                tradeDropdown.prop('required', false);
            }
        });
    }

    toggleTradePayable();

    $(document).on(
        'change',
        '.balance-mapping',
        function(){
            toggleTradePayable();
        }
    );

    $('form').on('submit', function(e){

        let isValid = true;

        $('.balance-mapping').each(function(){

            let value = $(this).val();

            let tradeDropdown = $(this)
                .closest('tr')
                .find('.trade-payable-type');

            if(
                value === 'Trade payables'
                &&
                !tradeDropdown.val()
            )
            {
                alert(
                    'Please select Trade Payable Type (A or B) for all Trade Payables mappings.'
                );

                tradeDropdown.focus();

                isValid = false;

                return false;
            }

        });

        if(!isValid)
        {
            e.preventDefault();
            return false;
        }

    });

});

</script>

@endsection