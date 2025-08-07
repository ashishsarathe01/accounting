@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-10 col-sm-12 px-4">
                <div class="container-fluid">

                    <div class="card mt-4">
                     <table class="table table-bordered text-center align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th rowspan="2">Details</th>
                                        <th colspan="4">Books (₹)</th>
                                        <th colspan="4">Portal (₹)</th>
                                    </tr>
                                    <tr>
                                        <th>Integrated</th>
                                        <th>Central</th>
                                        <th>State/UT</th>
                                        <th>CESS</th>

                                        <th>Integrated</th>
                                        <th>Central</th>
                                        <th>State/UT</th>
                                        <th>CESS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Row: (1) Import of goods --}}
                                    <tr>
                                        <td>(1) Import of goods</td>
                                        <td>{{ formatIndianNumber($books['itc4a1']['det']['itcavl']['igst'] ?? 0, 2) }}</td>
                                        <td> </td>
                                        <td> </td>
                                        <td>{{ formatIndianNumber($books['itc4a1']['det']['itcavl']['cess'] ?? 0, 2) }}</td>

                                        <td>{{ formatIndianNumber($portal['itc4a1']['det']['itcavl']['igst'] ?? 0, 2) }}</td>
                                        <td> </td>
                                        <td> </td>
                                        <td>{{ formatIndianNumber($portal['itc4a1']['det']['itcavl']['cess'] ?? 0, 2) }}</td>
                                    </tr>

                                    {{-- Row: (3) RCM --}}
                                    <tr>
                                        <td>(3) RCM (other than above)</td>
                                        <td>{{ formatIndianNumber($books['itc4a5']['det']['itcavl']['igst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a5']['det']['itcavl']['cgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a5']['det']['itcavl']['sgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a5']['det']['itcavl']['cess'] ?? 0, 2) }}</td>

                                        <td>{{ formatIndianNumber($portal['itc4a5']['det']['itcavl']['igst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['itc4a5']['det']['itcavl']['cgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['itc4a5']['det']['itcavl']['sgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['itc4a5']['det']['itcavl']['cess'] ?? 0, 2) }}</td>
                                    </tr>

                                    {{-- Row: (5) All Other ITC --}}
                                    <tr>
                                        <td>(5) All other ITC</td>
                                        <td>{{ formatIndianNumber($books['itc4a4']['det']['itcavl']['igst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a4']['det']['itcavl']['cgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a4']['det']['itcavl']['sgst'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($books['itc4a4']['det']['itcavl']['cess'] ?? 0, 2) }}</td>

                                       <td>{{ formatIndianNumber($portal['elgitc']['itc4a5']['subtotal']['iamt'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['elgitc']['itc4a5']['subtotal']['camt'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['elgitc']['itc4a5']['subtotal']['samt'] ?? 0, 2) }}</td>
                                        <td>{{ formatIndianNumber($portal['elgitc']['itc4a5']['subtotal']['csamt'] ?? 0, 2) }}</td>

                                    </tr>

                                

                                    {{-- Add more rows as needed, e.g., ISD, Import of services, etc. --}}
                                </tbody>
                            </table>
                      </div>

                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection
