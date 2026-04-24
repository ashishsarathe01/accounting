@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
.calendar-wrapper {
    width: 100%;
}

.calendar-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-weight: 600;
    margin-bottom: 10px;
}

.calendar-header div {
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
}

.calendar-cell {
    border: 1px solid #e0e0e0;
    height: 85px;
    padding: 8px;
    font-weight: 500;
    background: #fff;
    cursor: pointer;
    display: flex;
    justify-content: flex-end;
    align-items: flex-start;
    transition: 0.2s ease;
}

.calendar-cell:hover {
    background: #f1f1f1;
}

.calendar-cell.empty {
    background: #f9f9f9;
    border: none;
    cursor: default;
}

.calendar-cell.off-day {
    background: #dc3545;
    color: #fff;
}

.calendar-cell-wrapper {
    position: relative;
}

.calendar-checkbox {
    display: none;
}

.calendar-cell {
    border: 1px solid #e0e0e0;
    height: 85px;
    padding: 8px;
    font-weight: 500;
    background: #fff;
    cursor: pointer;
    display: flex;
    justify-content: flex-end;
    align-items: flex-start;
    transition: 0.2s ease;
}

.calendar-cell:hover {
    background: #f1f1f1;
}

/* Weekly off (system rule) */
.calendar-cell.weekly-off {
    background: #ffc107;
    color: #000;
}

/* When user selects holiday */
.calendar-checkbox:checked + .calendar-cell {
    background: #dc3545;
    color: #fff;
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Title --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
                    <h5 class="m-0 py-2">Monthly Off Calendar</h5>
                </div>

                {{-- Filter --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-body">

                        <form method="POST" action="{{ route('attendance.monthlyoff.calendar.filter') }}">
                            @csrf
                            <div class="row align-items-end">

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Month</label>
                                    <select name="month" class="form-select" required>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ sprintf('%02d',$m) }}"
                                                {{ $month == sprintf('%02d',$m) ? 'selected' : '' }}>
                                                {{ date('F', mktime(0,0,0,$m,1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Year</label>
                                    <select name="year" class="form-select" required>
                                        @for($y = date('Y')-5; $y <= date('Y')+5; $y++)
                                            <option value="{{ $y }}"
                                                {{ $year == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <button type="submit" class="btn btn-success">
                                        Filter
                                    </button>
                                </div>

                            </div>
                        </form>

                    </div>
                </div>

                {{-- Calendar --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-body">

                        <h5 class="mb-4">
                            {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
                        </h5>

                        <form method="POST" action="{{ route('attendance.monthlyoff.save') }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">

                            <div class="calendar-wrapper">

                                {{-- Header --}}
                                <div class="calendar-header">
                                    <div>Su</div>
                                    <div>Mo</div>
                                    <div>Tu</div>
                                    <div>We</div>
                                    <div>Th</div>
                                    <div>Fr</div>
                                    <div>Sa</div>
                                </div>

                                {{-- Grid --}}
                                <div class="calendar-grid">

                                    @for($i = 0; $i < $startDayOfWeek; $i++)
                                        <div class="calendar-cell empty"></div>
                                    @endfor

                                    @foreach($dates as $date)

    @php
        $isWeeklyOff = in_array($date, $weeklyOffDates);
        $isHoliday   = in_array($date, $holidayDates);
        $dayNumber   = \Carbon\Carbon::parse($date)->format('d');
    @endphp

    <div class="calendar-cell-wrapper">

        {{-- Checkbox (hidden but functional) --}}
        <input type="checkbox"
               id="day_{{ $date }}"
               name="holiday_dates[]"
               value="{{ $date }}"
               {{ $isHoliday ? 'checked' : '' }}
               class="calendar-checkbox">

        {{-- Clickable Label --}}
        <label for="day_{{ $date }}"
               class="calendar-cell 
               {{ $isWeeklyOff ? 'weekly-off' : '' }}">
            {{ $dayNumber }}
        </label>

    </div>

@endforeach

                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">
                                    Save Holidays
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

@endsection