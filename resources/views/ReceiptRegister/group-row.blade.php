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

        <a href="javascript:void(0)"
            class="receipt-register-details"
            data-account="{{ $acc['id'] }}"
            data-from="{{ request('from_date') }}"
            data-to="{{ request('to_date') }}"
            style="text-decoration:none;">
            {{ formatIndianNumber($acc['amount'],2) }}
        </a>

    </td>

</tr>

@endforeach


{{-- CHILD GROUPS --}}
@foreach($grp['children'] as $child)

    @include(
        'ReceiptRegister.group-row',
        [
            'grp' => $child,
            'level' => $level + 1,
            'parentGroup' => $grp['group_id']
        ]
    )

@endforeach