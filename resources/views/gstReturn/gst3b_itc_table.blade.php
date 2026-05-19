@extends('layouts.app')

@section('content')

@include('layouts.header')

<style>

    .gst3b-wrapper{
        background:#f3f5f7;
        min-height:100vh;
        padding:20px;
    }

    .gst-card{
        background:#fff;
        border:1px solid #d7d7d7;
        margin-top:15px;
    }

    .gst-header{
        background:#19b7b5;
        color:#fff;
        padding:14px 18px;
        font-size:30px;
        font-weight:600;
    }

    .gst-info{
        background:#d9edf7;
        border:1px solid #c5dceb;
        color:#3d6b85;
        margin:18px;
        padding:14px 16px;
        font-size:15px;
    }

    .gst-table{
        width:100%;
        border-collapse:collapse;
    }

    .gst-table th{
        background:#f3f3f3;
        border:1px solid #d6d6d6;
        padding:12px;
        text-align:center;
        font-size:15px;
        font-weight:600;
    }

    .gst-table td{
        border:1px solid #d6d6d6;
        padding:10px;
        vertical-align:middle;
    }

    .section-row td{
        background:#f5f5f5;
        font-weight:700;
        font-size:17px;
    }

    .details-cell{
        width:42%;
        font-size:16px;
        line-height:1.4;
    }

    .tax-input{
        width:100%;
        height:38px;
        border:1px solid #d7d7d7;
        background:#fafafa;
        text-align:right;
        padding-right:10px;
        font-size:15px;
    }

    .tax-input:focus{
        outline:none;
        border-color:#19b7b5;
        background:#fff;
    }

    .disabled-box{
        width:100%;
        height:38px;
        border:1px solid #d7d7d7;
        background:#ececec;
    }

    .bottom-btns{
        text-align:right;
        padding:20px;
    }

    .btn-cancel{
        background:#234b87;
        color:#fff;
        border:none;
        padding:10px 34px;
        font-size:15px;
        margin-right:8px;
    }

    .btn-confirm{
        background:#234b87;
        color:#fff;
        border:none;
        padding:10px 34px;
        font-size:15px;
    }

</style>

@php

$itcAvailable = collect($portal['itc_elg']['itc_avl'] ?? []);

$impg = $itcAvailable->firstWhere('ty', 'IMPG');
$isrc = $itcAvailable->firstWhere('ty', 'ISRC');
$oth  = $itcAvailable->firstWhere('ty', 'OTH');
$isd  = $itcAvailable->firstWhere('ty', 'ISD');
$imps = $itcAvailable->firstWhere('ty', 'IMPS');

$itcReverse = collect($portal['itc_elg']['itc_rev'] ?? []);

$rul = $itcReverse->firstWhere('ty', 'RUL');
$revOther = $itcReverse->firstWhere('ty', 'OTH');

$itcNet = $portal['itc_elg']['itc_net'] ?? [];

$itcIneligible = collect($portal['itc_elg']['itc_inelg'] ?? []);

$inelgRule = $itcIneligible->firstWhere('ty', 'RUL');
$inelgOther = $itcIneligible->firstWhere('ty', 'OTH');

@endphp

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

    <div class="row vh-100">

        @include('layouts.leftnav')

        <div class="col-md-10 col-sm-12 px-4">

            <div class="gst3b-wrapper">

                <div class="gst-card">

                    <div class="gst-header">
                        4. Eligible ITC
                    </div>

                    <div class="gst-info">
                        Tables 4(A)(1), (3), (4), (5) and 4(D)(2) are auto-drafted based on portal values.
                    </div>
                    
                     <form method="POST"
                        action="{{ isset($gstr3b)
                            ? route('gstr3b.itc.update',$gstr3b->id)
                            : route('gstr3b.itc.store') }}">

                        @csrf

                        <input type="hidden"
                            name="gstin"
                            value="{{ $gstr3b->gstin ?? request('gstin') }}">

                        <input type="hidden"
                            name="return_month"
                            value="{{ $gstr3b->return_month ?? request('month') }}">


                    <table class="gst-table">

                        <thead>
                            <tr>
                                <th>Details</th>
                                <th>Integrated Tax (₹)</th>
                                <th>Central Tax (₹)</th>
                                <th>State/UT Tax (₹)</th>
                                <th>CESS (₹)</th>
                            </tr>
                        </thead>

                        <tbody>

                            {{-- A --}}
                            <tr class="section-row">
                                <td colspan="5">
                                    (A) ITC Available (whether in full or part)
                                </td>
                            </tr>

                            {{-- IMPORT GOODS --}}
                            <tr>

                                <td class="details-cell">
                                    (1) Import of goods
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($impg['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <div class="disabled-box"></div>
                                </td>

                                <td>
                                    <div class="disabled-box"></div>
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($impg['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- IMPORT SERVICES --}}
                            <tr>

                                <td class="details-cell">
                                    (2) Import of services
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($imps['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <div class="disabled-box"></div>
                                </td>

                                <td>
                                    <div class="disabled-box"></div>
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($imps['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- RCM --}}
                            <tr>

                                <td class="details-cell">
                                    (3) Inward supplies liable to reverse charge
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isrc['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isrc['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isrc['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isrc['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- ISD --}}
                            <tr>

                                <td class="details-cell">
                                    (4) Inward supplies from ISD
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isd['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isd['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isd['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($isd['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- OTHER ITC --}}
                            <tr>

                                <td class="details-cell">
                                    (5) All other ITC
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($oth['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($oth['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($oth['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($oth['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- B --}}
                            <tr class="section-row">
                                <td colspan="5">
                                    (B) ITC Reversed
                                </td>
                            </tr>

                            <tr>

                                <td class="details-cell">
                                    (1) As per rules 38,42 & 43
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($rul['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($rul['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($rul['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($rul['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            <tr>

                                <td class="details-cell">
                                    (2) Others
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($revOther['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($revOther['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($revOther['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($revOther['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- C --}}
                            <tr class="section-row">

                                <td class="details-cell">
                                    (C) Net ITC Available (A) - (B)
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($itcNet['iamt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($itcNet['camt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($itcNet['samt'] ?? 0,2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber($itcNet['csamt'] ?? 0,2) }}">
                                </td>

                            </tr>

                            {{-- D --}}
                            <tr class="section-row">
                                <td colspan="5">
                                    (D) Other Details
                                </td>
                            </tr>

                            <tr>

                                <td class="details-cell">
                                    (1) ITC reclaimed reversed earlier
                                </td>

                                <td><input type="text" readonly class="tax-input" value="0.00"></td>
                                <td><input type="text" readonly class="tax-input" value="0.00"></td>
                                <td><input type="text" readonly class="tax-input" value="0.00"></td>
                                <td><input type="text" readonly class="tax-input" value="0.00"></td>

                            </tr>

                            <tr>

                                <td class="details-cell">
                                    (2) Ineligible ITC
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber(($inelgRule['iamt'] ?? 0) + ($inelgOther['iamt'] ?? 0),2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber(($inelgRule['camt'] ?? 0) + ($inelgOther['camt'] ?? 0),2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber(($inelgRule['samt'] ?? 0) + ($inelgOther['samt'] ?? 0),2) }}">
                                </td>

                                <td>
                                    <input type="text"
                                        readonly
                                        class="tax-input"
                                        value="{{ formatIndianNumber(($inelgRule['csamt'] ?? 0) + ($inelgOther['csamt'] ?? 0),2) }}">
                                </td>

                            </tr>

                        </tbody>

                    </table>
                     <div class="bottom-btns">

                            <button type="button" class="btn-cancel">
                                CANCEL
                            </button>

                            <button type="submit" class="btn-confirm">
                                CONFIRM
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