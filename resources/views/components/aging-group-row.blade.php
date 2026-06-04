<tr class="group-row {{ $level > 0 ? 'child-of-'.$grp['parent_id'].' hidden' : '' }}"
    data-group="{{ $grp['group_id'] }}">

    <td></td>

    <td style="padding-left: {{ $level * 25 }}px;">
        <span class="arrow">▶</span>
        {{ $grp['group_name'] }}
    </td>

    <td class="text-end">
        {{ FormatIndianNumber($grp['total'],2) }}
    </td>

    @foreach($buckets as $b)
        <td class="text-end">
            {{ FormatIndianNumber($grp['bucketTotals'][$b->id],2) }}
        </td>
    @endforeach

    <td class="text-end">
        {{ FormatIndianNumber($grp['moreThan'],2) }}
    </td>
</tr>

{{-- Accounts --}}
@foreach($grp['accounts'] as $acc)

<tr class="child-row child-of-{{ $grp['group_id'] }} hidden">

    <td></td>

    <td style="padding-left: {{ ($level + 1) * 25 }}px;">
        {{ $acc['party'] }}
    </td>

    <td class="text-end">
        {{ FormatIndianNumber($acc['total'],2) }}
    </td>

    @foreach($buckets as $b)
        <td class="text-end">
            {{ FormatIndianNumber($acc['buckets'][$b->id],2) }}
        </td>
    @endforeach

    <td class="text-end">
        {{ FormatIndianNumber($acc['moreThan'],2) }}
    </td>

</tr>

@endforeach

{{-- Child Groups --}}
@foreach($grp['children'] as $child)

    @include('components.aging-group-row', [
        'grp' => $child,
        'level' => $level + 1,
        'buckets' => $buckets
    ])

@endforeach