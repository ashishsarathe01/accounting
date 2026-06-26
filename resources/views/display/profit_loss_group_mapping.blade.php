@extends('layouts.app')

@section('content')

@include('layouts.header')
<style>
.tree-toggle{
    font-size:12px;
    font-weight:bold;
    color:#444;
}

.tree-toggle:hover{
    color:#000;
}
</style>
<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

<h5 class="master-table-title m-0 py-2">
Profit & Loss Group Mapping
</h5>

</div>

<form method="POST"
action="{{ route('profitloss.group.mapping.save') }}">

@csrf

<div class="card shadow-sm">

<div class="card-body p-0">

<table class="table table-bordered mb-0">

<thead>

<tr>
    <th width="50%">Account Group</th>
    <th width="50%">Profit & Loss Mapping</th>
</tr>

</thead>

<tbody>

@foreach($groups as $group)

<tr
    class="tree-row"
    data-id="{{ $group->id }}"
    data-type="{{ $group->record_type }}"
    data-level="{{ $group->level ?? 0 }}"
>

<td>

    {!! str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $group->level) !!}

@if($group->record_type == 'account')
    <i class="fa fa-circle" style="font-size:7px;color:#6c757d;"></i>
@endif

    @if($group->record_type != 'account')

<span class="tree-toggle"
      data-open="0"
      style="display:inline-block;width:18px;cursor:pointer;">
    ▼
</span>

@else

<span style="display:inline-block;width:18px;"></span>

@endif

    @if($group->record_type == 'heading')

    <strong>{{ $group->name }}</strong>

@elseif($group->record_type == 'group')

    {{ $group->name }}

@else

    <span class="text-primary">
        {{ $group->name }}
    </span>

@endif

</td>

<td>

<select
class="form-select profitloss-mapping select2-single"
data-id="{{ $group->id }}"
data-type="{{ $group->record_type }}"
data-level="{{ $group->level ?? 0 }}"
name="mapping[{{$group->unique_key}}]"
>

<option value="">
    Select
</option>

@foreach($mappingOptions as $option)

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

    $('.select2-single').select2({
        width: '100%',
        placeholder: 'Select',
        allowClear: true
    });
// Initially hide everything except headings
$('.tree-row').each(function () {

    let row = $(this);
    let level = parseInt(row.data('level'));

    if(level > 0){
        row.hide();
    }

});

// Check which rows have children
$('.tree-row').each(function(){

    let row = $(this);

    let level = parseInt(row.data('level'));

    let next = row.next();

    let hasChild = false;

    while(next.length){

        let nextLevel = parseInt(next.data('level'));

        if(nextLevel <= level){
            break;
        }

        if(nextLevel == level + 1){
            hasChild = true;
            break;
        }

        next = next.next();
    }

    if(hasChild){

        row.find('.tree-toggle')
            .text('▶')
            .attr('data-open','0');

    }else{

        row.find('.tree-toggle')
            .html('&nbsp;')
            .addClass('no-child')
            .css('cursor','default');

    }

});

});

$(document).on('change', '.profitloss-mapping', function () {

    let selectedValue = $(this).val();

    let currentRow   = $(this).closest('tr');
    let currentLevel = parseInt(currentRow.data('level'));

    currentRow.nextAll().each(function () {

        let rowLevel = parseInt($(this).data('level'));

        if (rowLevel <= currentLevel) {
            return false;
        }

        let childSelect = $(this).find('.profitloss-mapping');

        // Apply to every descendant (heading/group/account) in the UI
        childSelect
            .val(selectedValue)
            .trigger('change.select2');

    });

});
$(document).on('click','.tree-toggle',function(){

    if($(this).hasClass('no-child')){
        return;
    }

    let icon = $(this);

    let row = icon.closest('tr');

    let currentLevel = parseInt(row.data('level'));

    let isOpen = icon.attr('data-open') == "1";

    if(!isOpen){

        if(!icon.hasClass('no-child')){
    icon.text('▼');
}
        icon.attr('data-open','1');

        let next = row.next();

        while(next.length){

            let nextLevel = parseInt(next.data('level'));

            if(nextLevel <= currentLevel){
                break;
            }

            if(nextLevel == currentLevel + 1){
                next.show();
            }

            next = next.next();
        }

    }else{

        if(!icon.hasClass('no-child')){
    icon.text('▶');
}
        icon.attr('data-open','0');

        let next = row.next();

        while(next.length){

            let nextLevel = parseInt(next.data('level'));

            if(nextLevel <= currentLevel){
                break;
            }

            next.hide();

            let childToggle = next.find('.tree-toggle');

if (!childToggle.hasClass('no-child')) {
    childToggle
        .text('▶')
        .attr('data-open','0');
}

            next = next.next();
        }

    }

});
</script>
@endsection