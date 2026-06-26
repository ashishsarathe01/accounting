@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>

.vertical-bs-table td,
.vertical-bs-table th{
    vertical-align: middle;
}

.level-0 td{
    font-weight:700;
    font-size:16px;
    background:#f8f9fa;
}

.level-1 td{
    font-weight:600;
    padding-left:25px !important;
}

.level-2 td{
    padding-left:50px !important;
}

.level-3 td{
    padding-left:75px !important;
}

.total-row td{
    font-weight:700;
    border-top:2px solid #000 !important;
}

.difference-row td{
    font-weight:700;
}

</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="d-xxl-flex justify-content-between py-4 px-2 align-items-center">
                    <nav>
                        <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                            <li class="breadcrumb-item">Dashboard</li>
                            <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                            <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Balance Sheet</li>
                        </ol>
                    </nav>
                    <form class="" id="frm" method="GET" action="{{ route('balancesheet.index') }}">
                        @csrf
                        <div class="d-xxl-flex d-block  align-items-center">
                            <div class="ms-xxl-2">
                                <select class="form-select" id="report_design" style="width: 250px;">
                                    <option value="horizontal" selected>Horizontal</option>
                                    <option value="vertical">Vertical</option>
                                </select>
                            </div>
                            <p class="text-nowrap m-0 font-14 fw-500 font-heading my-2 my-xxl-0">FY</p>
                            <select class="form-select w-min-120 ms-xxl-2" aria-label="Default select example" id="financial_year" name="financial_year" required>
                                <option value="{{Session::get('default_fy')}}">{{Session::get('default_fy')}}</option>
                            </select>
                            <div class="calender-administrator my-2 my-xxl-0 ms-xxl-2 w-min-150" style="display:none;">
                                <input type="date" id="from_date" name="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required value="{{$from_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                            </div>
                            
                            <div class="calender-administrator w-min-150 ms-xxl-2">
                                <input type="date" id="to_date" name="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required value="{{$to_date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                            </div>
                            <button class="btn btn-info ms-xxl-2 next_btn">Next</button>
                        </div>
                    </form>
                </div>
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="master-table-title m-0 py-2">Balance Sheet</h5>
                </div>
                <div id="horizontal_balance_sheet">
                <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
                    <div class="col-md-6  font-14 p-0 border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">Liabilities (Rs.)
                                <span class="ms-auto">Amt.(₹)</span>
                            </div>
                            
                            @php 
                                $liability_total = 0; $asset_total = 0;
                                if($prev_year_profitloss!=0){
                                   if($prev_year_profit_status==1){
                                      $liability_total = $liability_total + $prev_year_profitloss;
                                   }else{
                                      $liability_total = $liability_total - $prev_year_profitloss;
                                   }
                                }
                                $liability_total = $liability_total - $current_journal_amount;
                               
                            @endphp
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider profitloss_redirect" style="cursor:pointer;color: #0000EE">
                                <?php 
                                if($profit_loss_amount<0){
                                   echo "<span>PROFIT FOR THE PERIOD</span>";                           
                                }else if($profit_loss_amount==0){
                                   echo "PROFIT/LOSS ADJUSTED";
                                }
                                if($prev_year_profitloss!=0){                           
                                   if($prev_year_profit_status==1){
                                      echo "<br>UNADJUSTED PROFIT AMOUNT (".$prevFy.")";
                                   }else{
                                      echo "<br>UNADJUSTED LOSS AMOUNT (".$prevFy.")";
                                   }                    
                                }
                                ?>
                                <span class="ms-auto" style="text-align: right;">
                                   <?php 
                                   setlocale(LC_MONETARY, 'en_IN');                           
                                   if($profit_loss_amount<0){
                                      echo formatIndianNumber(abs($profit_loss_amount));
                                      if($current_journal_amount>0){
                                         echo "<p style='font-size:10px'>ADJUSTED IN ACCOUNT : ".formatIndianNumber($current_journal_amount)."</p>";
                                      }
                                      $liability_total = $liability_total + abs($profit_loss_amount);
                                   }else{
                                      echo "&nbsp;";
                                   }
                                   if($prev_year_profitloss!=0){
                                      echo "<br>".formatIndianNumber($prev_year_profitloss);
                                   }
                                   
                                   ?>
                                </span>
                             </div>
                            @foreach($heads as $value)
                                @if($value->bs_profile==2)
                                    @continue
                                @endif
                                @if(($value->show_in_balance_sheet==1 || $value->balance!=0) && $value->id!=4)
                                    <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                                        <span class="ms-auto">
                                         <?php 
                                         setlocale(LC_MONETARY, 'en_IN');     
                                         if($value->balance>0){
                                            echo "- ".formatIndianNumber($value->balance);
                                            $liability_total = $liability_total - $value->balance;  
                                         }else{
                                            echo formatIndianNumber(abs($value->balance));
                                            $liability_total = $liability_total + abs($value->balance);  
                                         }                                                                 
                                         ?>
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                            
                        </div>
                    </div>
                    <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">Assets (Rs.)
                                <span class="ms-auto">Amt.(₹)</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider profitloss_redirect" style="cursor:pointer;color: #0000EE">
                                <?php 
                                if($profit_loss_amount>0){
                                   echo "LOSS FOR THE PERIOD";
                                }
                                ?>
                                <span class="ms-auto">
                                   <?php 
                                   setlocale(LC_MONETARY, 'en_IN');                           
                                   if($profit_loss_amount>0){
                                      echo formatIndianNumber(abs($profit_loss_amount));
                                      $asset_total = $asset_total + abs($profit_loss_amount);
                                   }else{
                                      echo "&nbsp;";
                                   }
                                   ?>
                                </span>
                            </div>
                            @foreach($heads as $value)
                                @if($value->bs_profile==1)
                                    @continue
                                @endif
                                @if($value->show_in_balance_sheet==1 || $value->balance!=0)
                                    <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider get_group_detail" data-id="{{$value->id}}" style="cursor:pointer;color: #0000EE">{{$value->name}}
                                        <span class="ms-auto">
                                             <?php 
                                             setlocale(LC_MONETARY, 'en_IN');                                 
                                             echo formatIndianNumber($value->balance);
                                             $asset_total = $asset_total + abs($value->balance);
                                             ?>
                                        </span>
                                    </div>
                                @endif
                            @endforeach   
                        </div>
                    </div>
                    @php 
                       $total = $liability_total - $asset_total;  
                       $total = round($total,2);    
                       
                   @endphp
                   <div class="col-md-6  font-14 p-0 border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                                <?php
                                   if($total<0){ 
                                      echo "OPENING DIFFERENCE";
                                   }else{
                                      echo '&nbsp';
                                   }
                                ?>
                                <span class="ms-auto">
                                   <?php 
                                  if($total<0){ 
                                         echo formatIndianNumber(abs($total));
                                      }                             
                                   ?>
                                </span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                                TOTAL
                                <span class="ms-auto">
                                   <?php                            
                                   if($total>0){ 
                                      echo formatIndianNumber(abs($liability_total));
                                   }else{
                                      echo formatIndianNumber(abs($asset_total));                           
                                   }                              
                                   ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">
                            <?php  
                                if($total>0){ 
                                   echo "OPENING DIFFERENCE";
                                }else{
                                   echo '&nbsp';
                                }
                            ?>
                            <span class="ms-auto">
                                <?php                       
                                if($total>0){ 
                                      echo formatIndianNumber(abs($total));
                                   }                         
                                ?>
                            </span>
                        </div>
                        <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider" style="cursor:pointer;color: #0000EE">TOTAL
                            <span class="ms-auto">
                            <?php                            
                                if($total>0){ 
                                   echo formatIndianNumber(abs($liability_total));
                                }else{
                                  echo formatIndianNumber(abs($asset_total));                           
                                }                              
                            ?>
                            </span>
                        </div>
                    </div>
                </div>
                </div>
<div id="vertical_balance_sheet" style="display:none;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 vertical-bs-table">
                @php
                    [$startYear, $endYear] = explode('-', Session::get('default_fy'));
                    $currentFyEndYear  = '20' . $endYear;
                    $previousFyEndYear = $currentFyEndYear - 1;

                    // helper: format or show dash
                    function vbsAmt($val)
                    {
                        if (round($val,2) == 0) {
                            return '—';
                        }

                        $formatted = function_exists('formatIndianNumber')
                            ? formatIndianNumber(abs($val))
                            : number_format(abs($val), 2);

                        return $val < 0 ? '-' . $formatted : $formatted;
                    }

                    $vb  = $verticalBalances; // shorthand - current year
                    $vb2 = $verticalBalancesPrevious; // shorthand - previous year
                    $drillUrl = url('vertical-bs-drilldown');

                    $totalLiabilities  = 0;
                    $totalAssets       = 0;
                    $totalLiabilities2 = 0;
                    $totalAssets2      = 0;
                @endphp

                <thead>
                    <tr>
                        <th width="60%">Particulars</th>
                        <th width="20%" class="text-end">As at 31st March {{ $currentFyEndYear }}</th>
                        <th width="20%" class="text-end">As at 31st March {{ $previousFyEndYear }}</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- ===================== EQUITY & LIABILITIES ===================== --}}
                    <tr class="level-0">
                        <td><strong>EQUITY AND LIABILITIES</strong></td>
                        <td></td><td></td>
                    </tr>

                    @if($company_info->business_type == 3)
                        <tr class="level-1">
                            <td><strong>Shareholder's funds</strong></td>
                            <td class="text-end">
                                @php
                                    $shFunds = ($vb['Share capital'] ?? 0) + ($vb['Reserves and surplus'] ?? 0);
                                    $totalLiabilities += $shFunds;

                                    $shFunds2 = ($vb2['Share capital'] ?? 0) + ($vb2['Reserves and surplus'] ?? 0);
                                    $totalLiabilities2 += $shFunds2;
                                @endphp
                                {{ vbsAmt($shFunds) }}
                            </td>
                            <td class="text-end">{{ vbsAmt($shFunds2) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Share capital" class="text-primary text-decoration-none vbs-drill">
                                    Share capital
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb['Share capital'] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2['Share capital'] ?? 0) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Reserves and surplus" class="text-primary text-decoration-none vbs-drill">
                                    Reserves and surplus
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb['Reserves and surplus'] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2['Reserves and surplus'] ?? 0) }}</td>
                        </tr>

                    @elseif($company_info->business_type == 2)
                        <tr class="level-1">
                            <td><strong>Partner's funds</strong></td>
                            <td class="text-end">
                                @php
                                    $shFunds = ($vb["Partner's capital account"] ?? 0) + ($vb['Profit and loss account'] ?? 0);
                                    $totalLiabilities += $shFunds;

                                    $shFunds2 = ($vb2["Partner's capital account"] ?? 0) + ($vb2['Profit and loss account'] ?? 0);
                                    $totalLiabilities2 += $shFunds2;
                                @endphp
                                {{ vbsAmt($shFunds) }}
                            </td>
                            <td class="text-end">{{ vbsAmt($shFunds2) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Partner's capital account" class="text-primary text-decoration-none vbs-drill">
                                    Partner's capital account
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb["Partner's capital account"] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2["Partner's capital account"] ?? 0) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Profit and loss account" class="text-primary text-decoration-none vbs-drill">
                                    Profit and loss account
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb['Profit and loss account'] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2['Profit and loss account'] ?? 0) }}</td>
                        </tr>

                    @elseif($company_info->business_type == 1)
                        <tr class="level-1">
                            <td><strong>Proprietor's funds</strong></td>
                            <td class="text-end">
                                @php
                                    $shFunds = ($vb["Proprietor's capital account"] ?? 0) + ($vb['Profit and loss account'] ?? 0);
                                    $totalLiabilities += $shFunds;

                                    $shFunds2 = ($vb2["Proprietor's capital account"] ?? 0) + ($vb2['Profit and loss account'] ?? 0);
                                    $totalLiabilities2 += $shFunds2;
                                @endphp
                                {{ vbsAmt($shFunds) }}
                            </td>
                            <td class="text-end">{{ vbsAmt($shFunds2) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Proprietor's capital account" class="text-primary text-decoration-none vbs-drill">
                                    Proprietor's capital account
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb["Proprietor's capital account"] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2["Proprietor's capital account"] ?? 0) }}</td>
                        </tr>
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name=Profit and loss account" class="text-primary text-decoration-none vbs-drill">
                                    Profit and loss account
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb['Profit and loss account'] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2['Profit and loss account'] ?? 0) }}</td>
                        </tr>
                    @endif

                    {{-- Non-current liabilities --}}
                    @php
                        $nonCurrLiab = ($vb['Long-term borrowings'] ?? 0)
                                     + ($vb['Deferred tax liabilities (Net)'] ?? 0)
                                     + ($vb['Other long term liabilities'] ?? 0)
                                     + ($vb['Long-term provisions'] ?? 0);
                        $totalLiabilities += $nonCurrLiab;

                        $nonCurrLiab2 = ($vb2['Long-term borrowings'] ?? 0)
                                      + ($vb2['Deferred tax liabilities (Net)'] ?? 0)
                                      + ($vb2['Other long term liabilities'] ?? 0)
                                      + ($vb2['Long-term provisions'] ?? 0);
                        $totalLiabilities2 += $nonCurrLiab2;
                    @endphp
                    <tr class="level-1">
                        <td><strong>Non-current liabilities</strong></td>
                        <td class="text-end">{{ vbsAmt($nonCurrLiab) }}</td>
                        <td class="text-end">{{ vbsAmt($nonCurrLiab2) }}</td>
                    </tr>
                    @foreach([
                        'Long-term borrowings',
                        'Deferred tax liabilities (Net)',
                        'Other long term liabilities',
                        'Long-term provisions'
                    ] as $line)
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name={{ urlencode($line) }}" class="text-primary text-decoration-none vbs-drill">
                                    {{ $line }}
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb[$line] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2[$line] ?? 0) }}</td>
                        </tr>
                    @endforeach

                    {{-- Current liabilities --}}
                    @php
                        $currLiab = ($vb['Short-term borrowings'] ?? 0)
                                  + ($vb['Trade payables'] ?? 0)
                                  + ($vb['Other current liabilities'] ?? 0)
                                  + ($vb['Short-term provisions'] ?? 0);
                        $totalLiabilities += $currLiab;

                        $currLiab2 = ($vb2['Short-term borrowings'] ?? 0)
                                   + ($vb2['Trade payables'] ?? 0)
                                   + ($vb2['Other current liabilities'] ?? 0)
                                   + ($vb2['Short-term provisions'] ?? 0);
                        $totalLiabilities2 += $currLiab2;
                    @endphp
                    <tr class="level-1">
                        <td><strong>Current liabilities</strong></td>
                        <td class="text-end">{{ vbsAmt($currLiab) }}</td>
                        <td class="text-end">{{ vbsAmt($currLiab2) }}</td>
                    </tr>
                    <tr class="level-2">
                        <td>
                            <a href="{{ $drillUrl }}?mapping_name=Short-term+borrowings" class="text-primary text-decoration-none vbs-drill">
                                Short-term borrowings
                            </a>
                        </td>
                        <td class="text-end">{{ vbsAmt($vb['Short-term borrowings'] ?? 0) }}</td>
                        <td class="text-end">{{ vbsAmt($vb2['Short-term borrowings'] ?? 0) }}</td>
                    </tr>
                    <tr class="level-2">
    <td>
        <a href="{{ $drillUrl }}?mapping_name=Trade+payables"
           class="text-primary text-decoration-none vbs-drill">
            Trade payables
        </a>
    </td>
    <td class="text-end">{{ vbsAmt($vb['Trade payables'] ?? 0) }}</td>
    <td class="text-end">{{ vbsAmt($vb2['Trade payables'] ?? 0) }}</td>
</tr>

<tr class="level-3">
    <td>
        <a href="{{ $drillUrl }}?mapping_name=Trade+payables+(A)+Micro+enterprises+and+small+enterprises"
           class="text-primary text-decoration-none vbs-drill">
            (A) Micro enterprises and small enterprises
        </a>
    </td>
    <td class="text-end">
        {{ vbsAmt($vb['Trade payables (A)'] ?? 0) }}
    </td>
    <td class="text-end">
        {{ vbsAmt($vb2['Trade payables (A)'] ?? 0) }}
    </td>
</tr>

<tr class="level-3">
    <td>
        <a href="{{ $drillUrl }}?mapping_name=Trade+payables+(B)+Others"
           class="text-primary text-decoration-none vbs-drill">
            (B) Others
        </a>
    </td>
    <td class="text-end">
        {{ vbsAmt($vb['Trade payables (B)'] ?? 0) }}
    </td>
    <td class="text-end">
        {{ vbsAmt($vb2['Trade payables (B)'] ?? 0) }}
    </td>
</tr>
                    <tr class="level-2">
                        <td>
                            <a href="{{ $drillUrl }}?mapping_name=Other+current+liabilities" class="text-primary text-decoration-none vbs-drill">
                                Other current liabilities
                            </a>
                        </td>
                        <td class="text-end">{{ vbsAmt($vb['Other current liabilities'] ?? 0) }}</td>
                        <td class="text-end">{{ vbsAmt($vb2['Other current liabilities'] ?? 0) }}</td>
                    </tr>
                    <tr class="level-2">
                        <td>
                            <a href="{{ $drillUrl }}?mapping_name=Short-term+provisions" class="text-primary text-decoration-none vbs-drill">
                                Short-term provisions
                            </a>
                        </td>
                        <td class="text-end">{{ vbsAmt($vb['Short-term provisions'] ?? 0) }}</td>
                        <td class="text-end">{{ vbsAmt($vb2['Short-term provisions'] ?? 0) }}</td>
                    </tr>

                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-end"><strong>{{ vbsAmt($totalLiabilities) }}</strong></td>
                        <td class="text-end"><strong>{{ vbsAmt($totalLiabilities2) }}</strong></td>
                    </tr>

                    {{-- ===================== ASSETS ===================== --}}
                    <tr class="level-0">
                        <td><strong>ASSETS</strong></td>
                        <td></td><td></td>
                    </tr>

                    {{-- Non-current assets --}}
                    @php
                        $ppe = ($vb['Property, Plant and Equipment'] ?? 0)
                             + ($vb['Intangible assets'] ?? 0)
                             + ($vb['Capital work-in-progress'] ?? 0)
                             + ($vb['Intangible assets under development'] ?? 0);

                        $nonCurrAssets = $ppe
                                       + ($vb['Non-current investments'] ?? 0)
                                       + ($vb['Deferred tax assets (Net)'] ?? 0)
                                       + ($vb['Long-term loans and advances'] ?? 0)
                                       + ($vb['Other non-current assets'] ?? 0);

                        $totalAssets += $nonCurrAssets;

                        $ppe2 = ($vb2['Property, Plant and Equipment'] ?? 0)
                              + ($vb2['Intangible assets'] ?? 0)
                              + ($vb2['Capital work-in-progress'] ?? 0)
                              + ($vb2['Intangible assets under development'] ?? 0);

                        $nonCurrAssets2 = $ppe2
                                        + ($vb2['Non-current investments'] ?? 0)
                                        + ($vb2['Deferred tax assets (Net)'] ?? 0)
                                        + ($vb2['Long-term loans and advances'] ?? 0)
                                        + ($vb2['Other non-current assets'] ?? 0);

                        $totalAssets2 += $nonCurrAssets2;
                    @endphp
                    <tr class="level-1">
                        <td><strong>Non-current assets</strong></td>
                        <td class="text-end">{{ vbsAmt($nonCurrAssets) }}</td>
                        <td class="text-end">{{ vbsAmt($nonCurrAssets2) }}</td>
                    </tr>
                    <tr class="level-2">
                        <td><strong>Property, Plant and Equipment and Intangible assets</strong></td>
                        <td class="text-end">{{ vbsAmt($ppe) }}</td>
                        <td class="text-end">{{ vbsAmt($ppe2) }}</td>
                    </tr>
                    @foreach([
                        'Property, Plant and Equipment',
                        'Intangible assets',
                        'Capital work-in-progress',
                        'Intangible assets under development'
                    ] as $line)
                        <tr class="level-3">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name={{ urlencode($line) }}" class="text-primary text-decoration-none vbs-drill">
                                    {{ $line }}
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb[$line] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2[$line] ?? 0) }}</td>
                        </tr>
                    @endforeach
                    @foreach([
                        'Non-current investments',
                        'Deferred tax assets (Net)',
                        'Long-term loans and advances',
                        'Other non-current assets'
                    ] as $line)
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name={{ urlencode($line) }}" class="text-primary text-decoration-none vbs-drill">
                                    {{ $line }}
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb[$line] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2[$line] ?? 0) }}</td>
                        </tr>
                    @endforeach

                    {{-- Current assets --}}
                    @php
                        $currAssets = ($vb['Current investments'] ?? 0)
                                    + ($vb['Inventories'] ?? 0)
                                    + ($vb['Trade receivables'] ?? 0)
                                    + ($vb['Cash and cash equivalents'] ?? 0)
                                    + ($vb['Short-term loans and advances'] ?? 0)
                                    + ($vb['Other current assets'] ?? 0);
                        $totalAssets += $currAssets;

                        $currAssets2 = ($vb2['Current investments'] ?? 0)
                                     + ($vb2['Inventories'] ?? 0)
                                     + ($vb2['Trade receivables'] ?? 0)
                                     + ($vb2['Cash and cash equivalents'] ?? 0)
                                     + ($vb2['Short-term loans and advances'] ?? 0)
                                     + ($vb2['Other current assets'] ?? 0);
                        $totalAssets2 += $currAssets2;
                    @endphp
                    <tr class="level-1">
                        <td><strong>Current assets</strong></td>
                        <td class="text-end">{{ vbsAmt($currAssets) }}</td>
                        <td class="text-end">{{ vbsAmt($currAssets2) }}</td>
                    </tr>
                    @foreach([
                        'Current investments',
                        'Inventories',
                        'Trade receivables',
                        'Cash and cash equivalents',
                        'Short-term loans and advances',
                        'Other current assets'
                    ] as $line)
                        <tr class="level-2">
                            <td>
                                <a href="{{ $drillUrl }}?mapping_name={{ urlencode($line) }}" class="text-primary text-decoration-none vbs-drill">
                                    {{ $line }}
                                </a>
                            </td>
                            <td class="text-end">{{ vbsAmt($vb[$line] ?? 0) }}</td>
                            <td class="text-end">{{ vbsAmt($vb2[$line] ?? 0) }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td class="text-end"><strong>{{ vbsAmt($totalAssets) }}</strong></td>
                        <td class="text-end"><strong>{{ vbsAmt($totalAssets2) }}</strong></td>
                    </tr>

                    @php
                        $difference  = round($totalLiabilities - $totalAssets, 2);
                        $difference2 = round($totalLiabilities2 - $totalAssets2, 2);
                    @endphp
                    <tr class="difference-row">
                        <td>Difference</td>
                        <td class="text-end">{{ $difference != 0 ? vbsAmt($difference) : '—' }}</td>
                        <td class="text-end">{{ $difference2 != 0 ? vbsAmt($difference2) : '—' }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
            </div>
        </div>
    </section>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(".get_group_detail").click(function(){
         let id = $(this).attr('data-id');
         if(id==4){
            window.location = "{{url('profitloss-filter')}}?financial_year=24-25&from_date={{$from_date}}&to_date={{$to_date}}";
         }else{
            window.location = "{{url('group-balance-by-head')}}/"+id+"/{{$from_date}}/{{$to_date}}";
         }
         
      });
   });
   $(".profitloss_redirect").click(function(){
      window.location = "{{url('profitloss-filter')}}?financial_year={{ Session::get('default_fy') }}&from_date={{$from_date}}&to_date={{$to_date}}";
   });
   $('#report_design').on('change', function () {
        if ($(this).val() === 'vertical') {
            $('#horizontal_balance_sheet').hide();
            $('#vertical_balance_sheet').show();
        } else {
            $('#vertical_balance_sheet').hide();
            $('#horizontal_balance_sheet').show();
        }

    });
</script>
@endsection