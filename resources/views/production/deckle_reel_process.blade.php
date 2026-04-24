@extends('layouts.app') 
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center" style="height: 48px;">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="height: 48px; display: flex; align-items: center;">
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        Completed Pop Roll
                    </h5>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Deckle No.</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Start Time</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">End Time</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Quality</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($deckles)==0)
                            <tr>
                                <td colspan="5" style="text-align: center">No Data Found</td>
                            </tr>
                            @endif
                            @foreach($deckles as $key => $deckle)
                                <tr>
                                    <td>{{$deckle->deckle_no}}</td>
                                    <td>{{date('d-m-Y H:i:s',strtotime($deckle->start_time_stamp))}}</td>
                                    <td>{{date('d-m-Y H:i:s',strtotime($deckle->end_time_stamp))}}</td>
                                    <td>
                                        <table class="table table-borderd">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>BF</th>
                                                    <th>GSM</th>
                                                    <th>PRODUCTION IN KG</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{$deckle->name}}</td>
                                                    <td>{{$deckle->bf}}</td>
                                                    <td>{{$deckle->gsm}}</td>
                                                    <td>{{$deckle->production_in_kg}}</td>
                                                </tr>
                                                @foreach($deckle->quality as $key => $quality)
                                                    <tr>
                                                        <td>{{$quality->name}}</td>
                                                        <td>{{$quality->bf}}</td>
                                                        <td>{{$quality->gsm}}</td>
                                                        <td>{{$quality->production_in_kg}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>
                                        <form class="" method="POST" action="{{route('start-deckle')}}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <input type="hidden" name="pop_rolls" value="{{$deckle->id}}">
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="submit" class="btn btn-info btn-sm" style="margin-top: 18px; " @if($start_deckle) disabled title="Finish current Pop Roll first" @endif>Start</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                
                @if($start_deckle)
                    <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <h5 class="table-title m-0 py-2 ">
                            Pop Roll Reel Start Process
                        </h5>
                    </div>
                    <div class="bg-white table-view shadow-sm">
                        <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('store-deckle-item')}}">
                            @csrf
                            @if($start_deckle)
                                <div id="popRollContainer">
                                    <div class="pop-roll" data-index="0">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Pop Roll</label>
                                                <input type="text" name="pop_roll" class="form-control me-2" readonly value="Pop Roll - {{$start_deckle->deckle_no}}" >
                                                <input type="hidden" name="pop_roll_id" class="form-control me-2" readonly value="{{$start_deckle->id}}" >
                                            </div>
                                            <div class="col-md-3"></div>
                                            <div class="col-md-3">
                                                {{-- <button type="button" class="btn btn-success mt-3" id="addPopRoll">+ Add Pop Roll</button> --}}
                                                <input type="hidden" name="add_new_pop_roll" id="add_new_pop_roll" value="">
                                                <button type="button" class="btn btn-warning btn-sm revert-btn" 
                                                        data-id="{{ $start_deckle->id }}" 
                                                        data-url="{{ route('revert-deckle') }}"
                                                        data-token="{{ csrf_token() }}" style="margin-top: 18px;">
                                                    Revert Pop Roll
                                                </button>
                                            </div>
                                        </div>
                                        <div class="reel-section mt-3">
                                            <div class="reel-row mb-2 " data-reel-index="0" style="display: none">
                                                <input type="text" name="pop_rolls[0][reels][0][reel_no]" class="form-control me-2 reel_no" placeholder="Reel No" readonly value="" data-select_type="reel_no" >
                                                <select name="pop_rolls[0][reels][0][quality_id]" class="form-select quality-select me-2" data-select_type="quality"  data-index="0">
                                                   <option value="">Select Quality</option>
                                                    @foreach($start_deckle->quality as $key => $value)
                                                        <option 
                                                            value="{{ $value->id }}" 
                                                            data-bf="{{ $value->bf }}" 
                                                            data-gsm="{{ $value->gsm }}" 
                                                            data-quality_row_id="{{ $value->quality_row_id }}"
                                                        >
                                                            {{ $value->item->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="pop_rolls[0][reels][0][quality_row_id]" id="quality_row_id_0" data-select_type="quality_row_id" >
                                                <input type="text" name="pop_rolls[0][reels][0][bf]" class="form-control me-2" placeholder="BF" id="bf_0"  readonly data-select_type="bf">
                                                <input type="text" name="pop_rolls[0][reels][0][gsm]" class="form-control me-2 gsm" placeholder="GSM" id="gsm_0" data-select_type="gsm" data-index="0" >
                                                <select name="pop_rolls[0][reels][0][unit]" class="form-select me-2" >
                                                    <option value="">Select Unit</option>
                                                    <option value="INCH">INCH</option>
                                                    <option value="CM">CM</option>
                                                    <option value="MM">MM</option>
                                                </select>
                                                <input type="text" name="pop_rolls[0][reels][0][size]" data-select_type="size" class="form-control me-2 size" placeholder="Size" data-index="0" id="size_0">
                                                <input type="text" name="pop_rolls[0][reels][0][weight]" class="form-control me-2" placeholder="Weight"  >
                                                <button type="button" class="btn btn-info btn-sm add-reel ms-2">+</button>
                                                <button type="button" class="btn btn-danger remove-reel">-</button>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-info add-reel default_btn mt-2">+ Add Reel</button>
                                            
                                        </div>
                                        <hr>
                                        <button type="submit" class="btn btn-primary mt-3" id="submit_btn">Complete</button> 
                                    </div>
                                </div>
                            @endif
                            <div class="modal" id="add_new_poproll_Modal">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Add Pop Roll</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <!-- 29-10-2025 khushi code start here-->
                                        <!-- Modal body -->
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3 mt-3">
                                                    <label for="new_actual_production_in_kg" class="form-label">Select Pop Roll:</label>
                                                    <select class="form-select" name="new_pop_roll_id" id="new_pop_roll_id">
                                                        <option value="">Select Pop Roll</option>
                                                        @foreach($completed_poprolls as $poproll)
                                                            <option value="{{ $poproll->id }}">POP ROLL - {{ $poproll->deckle_no }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-success" id="modalSubmitBtn">Submit</button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="position-relative table-title-bottom-line bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap">

        <h5 class="table-title m-0 py-2">
            Generated Pop Roll Reels
        </h5>
            @php
    $searchType = request('search_type')
        ?? session('search_type')
        ?? 'by_reel_cutting_date';
@endphp
        <form action="{{ route('deckle-process.manage-reel') }}" method="GET" class="ms-auto">
            @csrf

            <div class="d-flex align-items-left flex-wrap justify-content-left gap-4">
                <div class="calender-administrator align-items-left">
                <input type="radio" name="search_type" value="by_pop_roll_date" {{ $searchType == 'by_pop_roll_date' ? 'checked' : '' }}> Pop Roll Generated Date
                <input type="radio" name="search_type" value="by_reel_cutting_date" {{ $searchType == 'by_reel_cutting_date' ? 'checked' : '' }} > Reel Cutting Date
                </div>
                <div class="calender-administrator align-items-left">
                    <input type="date"
                        class="form-control calender-bg-icon calender-placeholder"
                        required
                        name="from_date"
                        value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                </div>

                <div class="calender-administrator ">
                    <input type="date"
                        class="form-control calender-bg-icon calender-placeholder"
                        required
                        name="to_date"
                        value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                </div>
                    <button class="btn btn-info">Search</button>
                                     <a href="{{ route('deckle-process.manage-reel.export', [
                                'from_date' => request('from_date'),
                                'to_date'   => request('to_date')
                            ]) }}" 
                            class="btn btn-info">
                            Export CSV
                        </a>
                                    
                                </div>
                            </form>
                        </div>
                    </div>
                <div class="bg-white table-view shadow-sm">
                    <table  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Deckle No.</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Deckle End Time</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Reel Cutting Date</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Details</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $great_total_weight = 0;  // Total weight of all deckles
                                $r = 0;                   // Total reels of all deckles
                                $production_in_kg_grand_total = 0;
                            @endphp
                            @if(count($completed_deckles)==0)
                                <tr>
                                    <td colspan="5" style="text-align: center">No Data Found</td>
                                </tr>
                            @endif
                            
                            @foreach($completed_deckles as $deckle)
                                @php
                                    $hasInactiveReel = false;

                                    foreach ($deckle->quality as $quality) {
                                        foreach ($quality->item_stock as $item) {
                                            if (isset($item->status) && $item->status == 0) {
                                                $hasInactiveReel = true;
                                                break 2; // exit both loops
                                            }
                                        }
                                    }
                                    if($deckle->ledger_id!=""){
                                        $hasInactiveReel = true;
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $deckle->deckle_no }}</td>
                                    <td>{{ date('d-m-Y H:i:s',strtotime($deckle->end_time_stamp)) }} 
                                        @php if($deckle->ledger_id==""){ @endphp
                                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1 edit_end_time" data-id="{{$deckle->id}}" alt="" title="Edit End Time" data-deckle_no="{{ $deckle->deckle_no }}" data-end_time_stamp="{{date('Y-m-d',strtotime($deckle->end_time_stamp))}}" style="cursor: pointer">
                                        @php } @endphp
                                        <table class="mt-2 table table-borderd">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>BF</th>
                                                    <th>GSm</th>
                                                    <th>Production in KG</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $production_in_kg_total = 0;@endphp
                                                @foreach($deckle->quality as $quality)
                                                    <tr>
                                                        <td>{{ $quality->name }}</td>
                                                        <td>{{ $quality->bf }}</td>
                                                        <td>{{ $quality->gsm }}</td>
                                                        <td>{{ $quality->production_in_kg }}</td>
                                                        <td>
                                                            @if(count($quality->item_stock)==0)
                                                                <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1 delete_quality" data-id="{{$quality->id}}" alt="" style="cursor: pointer;">
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $production_in_kg_total = $production_in_kg_total + $quality->production_in_kg;
                                                    $production_in_kg_grand_total = $production_in_kg_grand_total + $quality->production_in_kg;
                                                    
                                                    @endphp
                                                @endforeach
                                                <tr>
                                                    <th class="text-end"></th>
                                                    <th></th>
                                                    <th>Total</th>
                                                    <th>{{ $production_in_kg_total }}</th>
                                                    <th></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>{{ date('d-m-Y H:i:s',strtotime($deckle->reel_generated_at)) }}</td>
                                    <td>
                                        <table class="table table-borderd">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Reel No.</th>
                                                    <th>Size</th>
                                                    <th>Weight</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 @php
                                                    $grand_total_weight = 0; // Weight for this deckle
                                                    $p = 0;                  // Reels for this deckle
                                                @endphp
                                                @foreach($deckle->quality as $quality)
                                                  @php
                                                    $total_weight = 0;  // Weight for this quality row
                                                    $i = 0;             // Count reels for this quality
                                                @endphp
                                                
                                                    @if(count($quality->item_stock)>0)
                                                    <tr>
                                                        <td>{{ $quality->name }} </td>
                                                        <td>
                                                            @foreach($quality->item_stock as $reel)
                                                                {{ $reel->reel_no ?? '-' }}<br>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach($quality->item_stock as $reel)
                                                                {{ $reel->size ?? '-' }}<br>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach($quality->item_stock as $reel)
                                                                @php
                                                                    $total_weight += $reel->weight;
                                                                    $grand_total_weight += $reel->weight;

                                                                    // Global totals
                                                                    $great_total_weight += $reel->weight;
                                                                    $r++;

                                                                    // Deckle-wise totals
                                                                    $i++;
                                                                    $p++;
                                                                @endphp
                                                                @if($reel->status==1)
                                                                    <span style="color:green">{{ $reel->weight ?? '-' }}</span><br>
                                                                @else
                                                                    {{ $reel->weight ?? '-' }}<br>
                                                                @endif
                                                                
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                     <tr>
                                                        <td class="fw-bold text-end">Total</td>
                                                        <td class="fw-bold">{{ $i }}</td>
                                                        <td></td>
                                                        <td class="fw-bold">{{ $total_weight }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                                
                                                <tr>
                                                    <td class="fw-bold">Grand Total</td>
                                                    <td class="fw-bold">{{ $p }}</td>
                                                    <td></td>
                                                    <td class="fw-bold">{{ $grand_total_weight }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>
                                        @if(!$hasInactiveReel)
                                         @can('view-module',136)
                                            <button type="button" class="border-0 bg-transparent cancel_btn" data-id="{{ $deckle->id }}">
                                                <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="" title="Delete Reels">
                                            </button>
                                            @endcan
                                        @endif
                                        
                                       
                                        @if($deckle->stock_journal_status==0)
                                        @can('view-module',135)
                                            <a href="{{ url('edit-pop-roll-reel/'.$deckle->id) }}">
                                                <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt="" title="Edit Reels">
                                            </a>
                                        @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            
                            <tr class="bg-light">
                                <td class="fw-bold"></td>
                                <td class="fw-bold"></td>
                                <td class="fw-bold"><span>Total Deckle Weight : {{ $production_in_kg_grand_total }}</span></td>
                                <td class="fw-bold">
                                    <div style="display: flex; justify-content: space-between; width: 100%;">
                                        
                                        
                                        <span>Total Reels : {{ $r }}</span>
                                        <span>Total Weight : {{ $great_total_weight }}</span>
                                    </div>
                                </td>
                                <td class="fw-bold"></td>
                            </tr>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="delete_sale" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('cancel-pop-roll-reel') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Cancel this pop roll</h5>
                    <p class="font-14 text-body "> Do you really want to cancel these  pop roll? this process cannot be undone.</p>
                </div>
                <input type="hidden" id="cancel_deckle_id" name="pop_roll_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="endTimeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white" id="missingModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <input type="hidden" id="edit_deckle_id">

                <!-- END TIME -->
                <div class="mb-3">
                    <label class="form-label fw-bold">End Time</label>
                    <input type="date" class="form-control" id="edit_end_time_stamp">
                </div>

                <hr>

                <!-- EXISTING QUALITY PRODUCTION -->
                <h6 class="fw-bold mb-2">Existing Qualities</h6>
                <div id="qualityProductionContainer">
                    <!-- dynamically filled -->
                </div>

                <hr>

                <!-- ADD NEW QUALITY -->
                <h6 class="fw-bold mb-3">Add New Quality</h6>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">QUALITY</label>
                        <select class="form-select form-select-lg select2-single"
        name="new_item_id"
        id="new_item_id"
        required>
    <option value="">SELECT QUALITY</option>

    @foreach ($items as $item)
        <option value="{{ $item->id }}"
                data-bf="{{ $item->bf }}"
                data-gsm="{{ $item->gsm }}"
                data-speed="{{ $item->speed }}">
            {{ $item->name }}
        </option>
    @endforeach
</select>

                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">BF</label>
                        <input type="text" class="form-control" id="new_quality_bf" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">GSM</label>
                        <input type="text" class="form-control" id="new_quality_gsm">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">TIME STAMP</label>
                        <input type="text" class="form-control"
                               id="new_quality_time"
                               value="{{ date('d-m-Y H:i:s') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">SPEED</label>
                        <input type="text" class="form-control" id="new_quality_speed">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">PRODUCTION IN KG</label>
                        <input type="number" class="form-control" id="new_quality_production" placeholder="0">
                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-info update_endtime">Submit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


</body>
@include('layouts.footer')

<script>
    var reel_no = "{{$reel_no}}";
    reel_no = ++reel_no
    $(document).ready(function() {
        let popRollIndex = 0;
        // Add new Pop Roll section    
        $('#addPopRoll').on('click', function() {
            $("#add_new_poproll_Modal").modal('toggle');
            $("#add_new_pop_roll").val("YES");
            // popRollIndex++;
            // reel_no++; // increment for new pop roll
            // let newPopRoll = $('#popRollContainer .pop-roll:first').clone();

            // newPopRoll.attr('data-index', popRollIndex);

            // newPopRoll.find('select, input').each(function() {
            //     let name = $(this).attr('name');
            //     let select_type = $(this).attr('data-select_type');
            //     if (name) {
            //         name = name.replace(/\[\d+\]/, '[' + popRollIndex + ']');
            //         name = name.replace(/\[reels\]\[\d+\]/, '[reels][0]');
            //         if(select_type=='reel_no'){
            //             $(this).val(reel_no);
            //         } else {
            //             $(this).val('');
            //         }
            //     }
            // });

            // newPopRoll.find('.reel-section .reel-row:not(:first)').remove();

            // // Show remove button for new pop rolls
            // newPopRoll.find('.remove-poproll').show();

            // $('#popRollContainer').append(newPopRoll);
        });
        // Add new reel row inside a Pop Roll
        $(document).on('click', '.add-reel', function () {
            let popRoll = $(this).closest('.pop-roll');
            let reelSection = popRoll.find('.reel-section');
            let popIndex = popRoll.data('index');
            let reelIndex = reelSection.find('.reel-row').length;

            let newReel = reelSection.find('.reel-row:first').clone();
            newReel.removeAttr('style').addClass('d-flex');
            newReel.attr('data-reel-index', reelIndex);

            let lastReel = reelSection.find('.reel-row.d-flex').last();
            let hasExisting = reelSection.find('.reel-row.d-flex').length > 0;

            newReel.find('select, input').each(function () {
                let name = $(this).attr('name');
                let select_type = $(this).attr('data-select_type');
                $(this).attr('required',true);
                if (name) {
                    name = name.replace(/\[reels\]\[\d+\]/, `[reels][${reelIndex}]`);
                    $(this).attr('name', name);
                }

                if (select_type === 'reel_no') {
                    $(this).val(reel_no);
                } 
                else if (select_type === 'quality') {
                    $(this).attr('data-index', reelIndex);

                    if (hasExisting) {
                        let lastQuality = lastReel.find('[data-select_type="quality"]').val();
                        $(this).val(lastQuality).trigger('change');
                    } else {
                        $(this).trigger('change');
                    }
                } 
                else if (select_type === 'bf') {
                    $(this).attr('id', "bf_" + reelIndex);
                    if (hasExisting) {
                        $(this).val(lastReel.find('[data-select_type="bf"]').val());
                    } else {
                        $(this).val('');
                    }
                } 
                else if (select_type === 'gsm') {
                    $(this).attr('id', "gsm_" + reelIndex);
                    $(this).attr('data-index', reelIndex);
                    if (hasExisting) {
                        $(this).val(lastReel.find('[data-select_type="gsm"]').val());
                    } else {
                        $(this).val('');
                    }

                } 
                else if (select_type === 'quality_row_id') {
                    $(this).attr('id', "quality_row_id_" + reelIndex);
                } 
                else if ($(this).is('select[name*="[unit]"]')) {
                    if (hasExisting) {
                        $(this).val(lastReel.find('select[name*="[unit]"]').val());
                    } else {
                        $(this).val('');
                    }
                } 
                else {
                    $(this).val('');
                }

                if (select_type === 'size') {
                    $(this).attr('data-index', reelIndex);
                    $(this).attr('id', "size_"+reelIndex);
                }
            });

            // Insert new reel row before "+ Add Reel" button
            //reelSection.find('.add-reel').before(newReel);
            reelSection.append(newReel);

            // Trigger auto-fill for BF & GSM if quality already selected
            newReel.find('.quality-select').trigger('change');

            // Increment global reel number for next add
            reel_no++;
            $(".default_btn").hide();
        });
        // Remove a reel
        $(document).on('click', '.remove-reel', function() {
            let reelSection = $(this).closest('.reel-section');
            let reelRows = reelSection.find('.reel-row');

            if (reelRows.length > 1) {
                $(this).closest('.reel-row').remove();
            }

            // Recalculate reel numbers in order
            let start_no = parseInt("{{ $reel_no }}"); // starting from DB last reel no + 1
            $(".reel_no").each(function(i, e) {
                $(this).val(start_no + i);
            });

            // Update global counter for next add
            reel_no = parseInt($(".reel_no:last").val()) + 1;
        });
        // On change of Pop Roll → load qualities via AJAX    
        $(document).on('change','.quality-select',function(){
            let index = $(this).attr('data-index');
            $("#bf_"+index).val($(this).find(":selected").attr('data-bf'));
            $("#gsm_"+index).val($(this).find(":selected").attr('data-gsm'));
            $("#quality_row_id_"+index).val($(this).find(":selected").attr('data-quality_row_id'));
        });
    });
    $(document).on('click', '.remove-poproll', function(){
        $(this).closest('.pop-roll').remove();
        $(".reel_no").each(function(i,e){
            if(i==0){
                reel_no = "{{$reel_no}}";
            }            
            $(this).val(reel_no);
            if (i !== $(".reel_no").length - 1) {
                    reel_no++;
                }
        });
    });
    
    $(document).on('click','.cancel_btn',function(){
        var id = $(this).attr("data-id");
        $("#cancel_deckle_id").val(id);
        $("#delete_sale").modal("show");
    });
    $(".cancel").click(function() {
        $("#delete_sale").modal("hide");
    });
    $(document).on('click', '#modalSubmitBtn', function(e) {
        e.preventDefault();

        let selectedPopRoll = $("#new_pop_roll_id").val();
        if (!selectedPopRoll) {
            alert("Please select a Pop Roll first.");
            return;
        }

        // Target only the main 'store-deckle-item' form (Pop Roll Reel Start Process)
        const form = $('form[action="{{ route('store-deckle-item') }}"]');

        // Remove any existing hidden field (avoid duplicates)
        form.find('input[name="new_pop_roll_id"]').remove();

        // Append hidden input with selected pop roll
        $('<input>').attr({
            type: 'hidden',
            name: 'new_pop_roll_id',
            value: selectedPopRoll
        }).appendTo(form);

        // Close modal
        $("#add_new_poproll_Modal").modal('hide');

        // Trigger form submit
        $("#submit_btn").trigger("click");
    });
    $(document).on('click', '#submit_btn', function(e) {
        let error = false;
        let visibleReels = $('.reel-row.d-flex').length;
        if (visibleReels === 0) {
            e.preventDefault(); 
            alert('Please add at least one reel before completing the Pop Roll.');
            return false;
        }
        $('.reel-row.d-flex').each(function () {
            $(this).find('input, select').each(function () {
                let val = $(this).val()?.trim();
                let isRequired = $(this).attr('required');
                
                if (isRequired && (!val || val === "")) {
                    error = true;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
        });

        if (error) {
            e.preventDefault();
            alert('Please fill all required fields.');
            return false;
        }
        $(this).prop('disabled', true);
        $(this).closest('form').submit();
    });
    $(document).on('click', '.revert-btn', function() {
        let id = $(this).data('id');
        let url = $(this).data('url');
        let token = $(this).data('token');
        let row = $(this).closest('.pop-roll');

        if(!confirm('Are you sure you want to revert this pop roll?')) return;

        $.ajax({
            url: url,
            method: 'POST',
            data: { id: id, _token: token },
            success: function(res) {
                if(res.success) {
                    alert('Pop roll reverted successfully!');
                    setTimeout(function()
                {
                    location.reload();
                })
                    row.remove();
                    let deckleNo = row.find('.deckle-no').val() || row.find('.deckle-no').text();
                    let completedTable = $('#completed-poprolls tbody');
                    let newRow = `
                        <tr data-id="${id}">
                            <td class="deckle-no">${deckleNo}</td>
                            <td>
                                <button class="start-btn btn btn-success btn-sm" data-id="${id}">Start</button>
                            </td>
                        </tr>
                    `;
                    completedTable.append(newRow);
                } else {
                    alert(res.message || 'Something went wrong.');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('AJAX request failed!');
            }
        });
    });
    $(document).on("change", "#new_item_id", function () {

        let selected = $(this).find(":selected");
        let modal = $(this).closest(".modal");

        modal.find("#new_quality_bf").val(selected.data("bf") ?? "");
        modal.find("#new_quality_gsm").val(selected.data("gsm") ?? "");
        modal.find("#new_quality_speed").val(selected.data("speed") ?? "");
    });



    $(".edit_end_time").click(function () {
        let deckleId = $(this).data("id");
        let deckleNo = $(this).data("deckle_no");
        let endTime  = $(this).data("end_time_stamp");
        $("#missingModalLabel").text("Edit Deckle No. " + deckleNo);
        $("#edit_deckle_id").val(deckleId);
        $("#edit_end_time_stamp").val(endTime);
        $("#qualityProductionContainer").html(
            '<p class="text-muted">Loading...</p>'
        );
        $.ajax({
            url: "{{ route('deckle.quality.production', '') }}/" + deckleId,
            type: "GET",
            success: function (res) {
                if (!res || res.length === 0) {
                    $("#qualityProductionContainer").html(
                        '<p class="text-danger">No quality data found</p>'
                    );
                    return;
                }
                let html = '';
                res.forEach(row => {
                    html += `
                    <div class="card border p-3 mb-3">
                        <input type="hidden" class="quality_row_id" value="${row.quality_row_id}">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label>QUALITY</label>
                                <select class="form-select edit_quality_select">
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}"
                                            ${row.item_id == '{{ $item->id }}' ? 'selected' : ''}
                                            data-bf="{{ $item->bf }}"
                                            data-gsm="{{ $item->gsm }}"
                                            data-speed="{{ $item->speed }}">
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>BF</label>
                                <input type="text"
                                    class="form-control edit_bf"
                                    value="${row.bf ? row.bf : ''}"
                                    readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>GSM</label>
                                <input type="text"
                                    class="form-control edit_gsm"
                                    value="${row.gsm ? row.gsm : ''}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>SPEED</label>
                                <input type="text"
                                    class="form-control edit_speed"
                                    value="${row.speed ? row.speed : ''}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>PRODUCTION</label>
                                <input type="number"
                                    class="form-control edit_production"
                                    value="${row.production_in_kg ? row.production_in_kg : 0}">
                            </div>
                        </div>
                    </div>
                    `;
                });
                $("#qualityProductionContainer").html(html);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                $("#qualityProductionContainer").html(
                    '<p class="text-danger">Failed to load data</p>'
                );
            }
        });
        $("#endTimeModal").modal("show");
    });
    $(document).on("click", ".update_endtime", function () {
        let modal = $(this).closest(".modal");   // ✅ VERY IMPORTANT
        let deckle_id = modal.find("#edit_deckle_id").val();
        let end_time_stamp = modal.find("#edit_end_time_stamp").val();
        let token = "{{ csrf_token() }}";
        if (!deckle_id) {
            alert("Deckle ID missing. Please reopen the modal.");
            return;
        }
        if (!end_time_stamp) {
            alert("Please enter end time");
            return;
        }
        /* -----------------------------
        | Existing Quality Updates
        ----------------------------- */
        let quality_data = [];
        modal.find("#qualityProductionContainer .card").each(function () {
            // let quality_row_id = $(this).find('input[name="quality_row_ids[]"]').val();
            // let production_kg  = $(this).find('input[name="production_in_kg[]"]').val();
            // if (quality_row_id) {
            //     quality_data.push({
            //         quality_row_id: quality_row_id,
            //         production_in_kg: production_kg ?? 0
            //     });
            // }
            quality_data.push({
                quality_row_id: $(this).find(".quality_row_id").val(),
                item_id: $(this).find(".edit_quality_select").val(),
                bf: $(this).find(".edit_bf").val(),
                gsm: $(this).find(".edit_gsm").val(),
                speed: $(this).find(".edit_speed").val(),
                production_in_kg: $(this).find(".edit_production").val()
            });
        });
        /* -----------------------------
        | New Quality (Optional)
        ----------------------------- */
        let new_quality = null;
        if (modal.find("#new_item_id").val()) {
            new_quality = {
                item_id: modal.find("#new_item_id").val(),
                bf: modal.find("#new_quality_bf").val(),
                gsm: modal.find("#new_quality_gsm").val(),
                speed: modal.find("#new_quality_speed").val(),
                production_in_kg: modal.find("#new_quality_production").val(),
                start_time_stamp: modal.find("#new_quality_time").val()
            };
        }
        $.ajax({
            url: "{{ route('update-deckle-end-time') }}",
            method: "POST",
            data: {
                deckle_id: deckle_id,   // ✅ NOW GUARANTEED
                end_time_stamp: end_time_stamp,
                quality_data: quality_data,
                new_quality: new_quality,
                _token: token
            },
            success: function (res) {
                if (res.success) {
                    alert("Deckle updated successfully!");
                    location.reload();
                } else {
                    alert(res.message || "Something went wrong.");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("AJAX request failed!");
            }
        });
    });
    $(document).on('change', '.size', function(){
        let size = $(this).val();
        size = size.split('X');
        size = size[0];
        let index = $(this).attr('data-index');
        let gsm = $("#gsm_"+$(this).attr('data-index')).val();

        $(this).val(size+"X"+gsm);
    });
    $(document).on('change', '.gsm', function(){
        let index = $(this).attr('data-index');
        console.log(index);
        let size = $("#size_"+index).val();
        
        size = size.split('X');
        size = size[0];
        
        let gsm = $(this).val();
        $("#size_"+index).val(size+"X"+gsm);
    });
    $(document).on("change", ".edit_quality_select", function () {
        let selected = $(this).find(":selected");
        let card = $(this).closest(".card");
        card.find(".edit_bf").val(selected.data("bf") ? selected.data("bf") : "");
        card.find(".edit_gsm").val(selected.data("gsm") ? selected.data("gsm") : "");
        card.find(".edit_speed").val(selected.data("speed") ? selected.data("speed") : "");
    }); 
    $(document).on("click", ".delete_quality", function () {
        let id = $(this).attr('data-id');
        let token = "{{ csrf_token() }}";
        if(confirm("Are you sure to delete quality?")==true){
            $.ajax({
                url: "{{ route('delete-deckle-quality') }}",
                method: "POST",
                data: {
                    id: id,   // ✅ NOW GUARANTEED
                    _token: token
                },
                success: function (res) {
                    console.log(res.status)
                    if (res.status==true) {
                        alert("Quality Deleted Successfully!");
                        location.reload();
                    } else {
                        alert("Something went wrong.");
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert("AJAX request failed!");
                }
            });
        }
    }); 

</script>



@endsection
