
<tr class="group-row {{ $level > 0 ? 'child-of-'.$grp['parent_id'].' hidden' : '' }}"
    data-group="{{ $grp['group_id'] }}">


    <td></td>
    <td style="padding-left: {{ $level * 25 }}px;">
        <span class="arrow">▶</span>
        {{ $grp['group_name'] }}
    </td>
    <td style="text-align:right;">{{ formatIndianNumber($grp['total_receivable'],2) }}</td>
    <td style="text-align:right;">{{ formatIndianNumber($grp['total_overdue'],2) }}</td>
</tr>

{{-- DIRECT ACCOUNTS --}}
@foreach($grp['accounts'] as $acc)
<tr class="child-row child-of-{{ $grp['group_id'] }} hidden">
    <td></td>
    <td style="padding-left: {{ ($level + 1) * 25 }}px;">{{ $acc['party_name'] }} ({{ $acc['credit_days'] ?? '-' }}/{{ $acc['due_day'] ?? '-' }})<br>{{ $acc['mobile'] }}</td>
    <td class="get_info" data-id="{{ $acc['id'] }}" style="text-align:right;">{{ formatIndianNumber($acc['receivable'],2) }}</td>
    <td style="
        text-align:right;
        cursor:pointer;
    "
    onmouseover="this.style.color='#0000FF';"
    onmouseout="this.style.textDecoration='none'; this.style.color='#000000';" onclick="window.location='{{ route($overdueRoute,$acc['id']) }}?date={{ $today }}'" >{{ formatIndianNumber($acc['overdue'],2) }}</td>
    <td class="latest-response"
                    data-account-id="{{ $acc['id'] }}"
                    style="text-align:center; padding-left:0; padding-right:0; font-size:16px!important;">
                   <span class="resp-text-{{ $acc['id'] }}">
                    {{ $acc['response'] ?? '' }}
                    @if ($acc['response_date'] ?? '')
                        ({{ date('d-m-Y', strtotime($acc['response_date'])) }})
                    @endif
                     </span>
                </td>
    <td class="action" style="text-align:center;">
                <span class="record_response"
                 data-id="{{ $acc['id'] ?? '' }}"
                        data-name="{{ $acc['party_name'] ?? '' }}"
                    style="
                        background:#007bff;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">
                    Response
                </span>
                <span class="log-btn"
                data-account-id="{{ $acc['id'] }}"
                data-account-name="{{ $acc['party_name'] }}"
                style="
                        background:#385E3C;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:6px;
                        font-weight:bold;
                        cursor:pointer;
                        display:inline-block;
                    ">log</span>
            </td>
</tr>
@endforeach

{{-- SUBGROUPS --}}
@foreach($grp['children'] as $child)
    @include('components.group-row', ['grp' => $child, 'level' => $level + 1])
@endforeach
