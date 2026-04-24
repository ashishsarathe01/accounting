@php     $level = request('level') ?? $level ?? 1;

    $openingDr = 0;
    $openingCr = 0;

    $totalDebit  = 0;
    $totalCredit = 0;

    $closingDr = 0;
    $closingCr = 0;
@endphp


{{-- GROUPS --}}
@foreach($groups as $g)
    @php
        if($g->opening==0 && $g->debit==0 && $g->credit==0){
            continue;
        }

        if(($g->opening_type ?? 'Dr') == 'Dr'){
            $openingDr += $g->opening ?? 0;
        } else {
            $openingCr += $g->opening ?? 0;
        }

        $totalDebit  += $g->debit ?? 0;
        $totalCredit += $g->credit ?? 0;

        if(($g->closing_type ?? 'Dr') == 'Dr'){
            $closingDr += $g->closing ?? 0;
        } else {
            $closingCr += $g->closing ?? 0;
        }
    @endphp

    <tr class="fw-semibold group-row" data-level="{{ $level }}" style="font-weight: bold;">
        <td style="padding-left: {{ ($level * 25) }}px;">
            
            <a href="javascript:void(0)"
               class="text-decoration-none text-primary group-toggle d-inline-flex align-items-center"
               data-id="{{ $g->id }}" >

                @if($g->has_child ?? true)
                    <span class="toggle-icon me-1">▶</span>
                @endif

                {{ $g->name }}
            </a>
        </td>

        <td class="text-center">
            <span class="badge bg-secondary">GROUP</span>
        </td>

        <td class="text-end">
            {{ formatIndianNumber($g->opening ?? 0, 2) }}
            <small>{{ $g->opening_type ?? 'Dr' }}</small>
        </td>

        <td class="text-end text-danger">
            {{ formatIndianNumber($g->debit ?? 0, 2) }}
        </td>

        <td class="text-end text-success">
            {{ formatIndianNumber($g->credit ?? 0, 2) }}
        </td>

        <td class="text-end">
            {{ formatIndianNumber($g->closing ?? 0, 2) }}
            <small>{{ $g->closing_type ?? 'Dr' }}</small>
        </td>
    </tr>

    <tr id="group-children-{{ $g->id }}" class="group-children" style="display:none;"></tr>
@endforeach


{{-- ACCOUNTS --}}
@foreach($accounts->where('under_group', request('id')) as $acc)
    @php
        if($acc->opening==0 && $acc->debit==0 && $acc->credit==0){
            continue;
        }

        if(($acc->opening_type ?? 'Dr') == 'Dr'){
            $openingDr += $acc->opening ?? 0;
        } else {
            $openingCr += $acc->opening ?? 0;
        }

        $totalDebit  += $acc->debit ?? 0;
        $totalCredit += $acc->credit ?? 0;

        if(($acc->closing_type ?? 'Dr') == 'Dr'){
            $closingDr += $acc->closing ?? 0;
        } else {
            $closingCr += $acc->closing ?? 0;
        }
    @endphp

    <tr class="account-row" data-level="{{ $level }}">
        <td style="padding-left: {{ ($level * 25) }}px;">
            <a href="{{ route('account.month.summary', [
                'account_id' => $acc->id,
                'from_date'  => $from,
                'to_date'    => $to
            ]) }}"
               class="text-decoration-none">
                {{ $acc->account_name }}
            </a>
        </td>

        <td class="text-center">
            <span class="badge bg-info text-dark">ACCOUNT</span>
        </td>

        <td class="text-end">
            {{ formatIndianNumber($acc->opening ?? 0, 2) }}
            <small>{{ $acc->opening_type ?? 'Dr' }}</small>
        </td>

        <td class="text-end text-danger">
            {{ formatIndianNumber($acc->debit ?? 0, 2) }}
        </td>

        <td class="text-end text-success">
            {{ formatIndianNumber($acc->credit ?? 0, 2) }}
        </td>

        <td class="text-end">
            {{ formatIndianNumber($acc->closing ?? 0, 2) }}
            <small>{{ $acc->closing_type ?? 'Dr' }}</small>

        </td>
    </tr>
@endforeach