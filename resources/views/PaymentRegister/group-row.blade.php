<tr class="group-row {{ $level > 0 ? 'hidden child-of-'.$parentGroup : '' }}"
    data-group="{{ $grp['group_id'] }}">

    <td></td>

    <td style="padding-left: {{ $level * 25 }}px; font-weight:bold;">

        <span class="arrow">
            ▶
        </span>

        {{ $grp['group_name'] }}

    </td>

    <td style="text-align:right; font-weight:bold;">

        {{ formatIndianNumber($grp['total_amount'],2) }}

    </td>

</tr>


{{-- DIRECT ACCOUNTS --}}
@foreach($grp['accounts'] as $acc)

<tr class="child-of-{{ $grp['group_id'] }} hidden">

    <td></td>

    <td style="padding-left: {{ ($level + 1) * 35 }}px;">

        {{ $acc['party_name'] }}

    </td>

    <td style="text-align:right;">

        {{ formatIndianNumber($acc['amount'],2) }}

    </td>

</tr>

@endforeach


{{-- CHILD GROUPS --}}
@foreach($grp['children'] as $child)

    @include(
        'PaymentRegister.group-row',
        [
            'grp' => $child,
            'level' => $level + 1,
            'parentGroup' => $grp['group_id']
        ]
    )

@endforeach