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
                        List Pop Roll (Running)
                    </h5>
                    @empty($running_deckle)
                        <a href="{{ route('deckle-process.create') }}" class="btn btn-xs-primary">
                            ADD
                            <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                            </svg>
                        </a>
                    @endempty
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $last_row_id = ""; @endphp
                            <tr>
                                <td>
                                    <div class="row">
                                        <div class="mb-3 col-md-3">
                                            <label for="deckle_no" class="form-label font-14 font-heading">DECKLE NO.</label>
                                            <input type="text" class="form-control" name="deckle_no" id="deckle_no" value="@if($deckle) {{$deckle->deckle_no}} @endif" readonly>
                                        </div>
                                        <div class="mb-12 col-md-12"></div>
                                        @if($deckle)
                                            @foreach($deckle->quality as $key => $value)
                                                <hr>
                                                <div class="mb-3 col-md-3">
                                                    <label for="item_id" class="form-label font-14 font-heading">QUALITY</label>
                                                    <input type="text" class="form-control" name="item_id" id="item_id" value="{{$value->name}}" readonly/>
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label for="item_bf" class="form-label font-14 font-heading">BF</label>
                                                    <input type="text" class="form-control" name="item_bf" id="item_bf" value="{{$value->bf}}" readonly/>
                                                </div>
                                                <div class="mb-3 col-md-3" style="padding-bottom:10px;">
                                                    <label for="item_gsm" class="form-label font-14 font-heading">GSM</label>
                                                    <input type="text" class="form-control" name="item_gsm" id="item_gsm" value="{{$value->gsm}}" readonly>
                                                </div>
                                                
                                                <div class="mb-3 col-md-3">
                                                    <label for="start_time_stamp" class="form-label font-14 font-heading">TIME STAMP</label>
                                                    <input type="text" class="form-control" name="start_time_stamp" id="start_time_stamp" value="{{date('d-m-Y H:i:s',strtotime($value->start_time_stamp))}}" readonly>
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label for="production_in_kg" class="form-label font-14 font-heading">PRODUCTION IN KG</label>
                                                    <input type="text" class="form-control" name="production_in_kg" id="production_in_kg" value="{{$value->production_in_kg}}" readonly>
                                                </div>
                                                <div class="mb-3 col-md-3">
                                                    <label for="speed" class="form-label font-14 font-heading">SPEED</label>
                                                    <input type="text" class="form-control" name="speed" id="speed" value="{{$value->speed}}" readonly>
                                                </div>
                                                @php $last_row_id = $value->quality_row_id; @endphp
                                                @php $last_quality_id = $value->item_id; @endphp
                                                @php $last_speed = $value->speed; @endphp
                                            @endforeach
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @if($deckle)
                                        @php                                    
                                        $stop_logs = \App\Models\DeckleMachineStopLog::where('deckle_id', $deckle->id)
                                                    ->where('company_id', Session::get('user_company_id'))
                                                    ->orderBy('stopped_at', 'desc')
                                                    ->get();
                                        @endphp
                                        @if($stop_logs->count() > 0)
                                            <div class="mb-3 col-md-12 mt-2 p-2 border border-danger rounded bg-light">
                                                <h6 class="font-14 font-heading text-danger">Machine Stop Details</h6>
                                                <table class="table table-bordered table-sm mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Stopped By</th>
                                                            <th>Stopped At</th>
                                                            <th>Start By</th>
                                                            <th>Start At</th>
                                                            <th>Reason</th>
                                                            <th>Remark</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($stop_logs as $log)
                                                        <tr>
                                                            <td>{{ \App\Models\Admin::where('id', $log->stopped_by)->value('name') ?? 'N/A' }}</td>
                                                            <td>{{ date('d-m-Y H:i:s', strtotime($log->stopped_at)) }}</td>
                                                            <td>{{ \App\Models\Admin::where('id', $log->start_by)->value('name') ?? 'N/A' }}</td>
                                                            <td>{{ $log->start_at ? date('d-m-Y H:i:s', strtotime($log->start_at)) : 'N/A' }}</td>
                                                            <td>{{ $log->reason }}</td>
                                                            <td>{{ $log->remark }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endif
                                    <div class="text-start">
                                        @if($deckle && $deckle->stop_machine_status==0)
                                            <button type="button" id="add_new_quality" data-speed="{{$last_speed}}" data-deckle_id="{{$deckle->id}}" data-last_row_id="{{$last_row_id}}" data-deckle_no="{{$deckle->deckle_no}}" class="btn  btn-xs-primary ">
                                                ADD QUALITY
                                            </button>
                                            <button type="button" id="add_new_deckle" data-quality_id="{{$last_quality_id}}" data-deckle_id="{{$deckle->id}}" data-speed="{{$last_speed}}" data-deckle_no="{{$deckle->deckle_no}}" data-speed="{{$deckle->speed}}"  data-last_row_id="{{$last_row_id}}" class="btn  btn-xs-primary ">ADD NEW POP ROLL
                                            </button>
                                            <button type="button" id="machine_stop" data-deckle_id="{{$deckle->id}}" data-deckle_no="{{$deckle->deckle_no}}" class="btn  btn-xs-primary ">
                                                MACHINE STOP
                                            </button>
                                        @elseif($deckle)
                                            <button type="button" id="machine_start" data-deckle_id="{{$deckle->id}}"  data-deckle_no="{{$deckle->deckle_no}}"class="btn  btn-xs-primary ">
                                                MACHINE START
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        List Pop Roll (Completed)
                    </h5>
                </div>
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
                        @foreach($completed_deckles as $key => $deckle)
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
                                    <a href="{{ URL::to('deckle-process/'.$deckle->id.'/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pop Roll Summary Section -->
                @php
                    $today = date('Y-m-d');
                @endphp

                <h4 class="mt-4">Pop Roll Summary</h4>

                <form method="GET" action="{{ route('deckle-process.index') }}" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="quality_id" class="form-label">Quality</label>
                        <select name="quality_id" id="quality_id" class="form-select" required>
                            <option value="all" {{ empty($selected_quality) || $selected_quality == 'all' ? 'selected' : '' }}>
                                -- All Items --
                            </option>
                            @foreach($qualities as $quality)
                                <option value="{{ $quality->item_id }}" {{ $selected_quality == $quality->item_id ? 'selected' : '' }}>
                                    {{ $quality->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control"
                            value="{{ $from_date ?? $today }}">
                    </div>

                    <div class="col-md-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control"
                            value="{{ $to_date ?? $today }}">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>

                @if($summary_records->count() > 0)
                @php $total_production = 0; @endphp
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Quality</th>
                            <th>BF</th>
                            <th>GSM</th>
                            <th>Production in KG</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary_records as $record)
                        <tr>
                            <td>{{ $record->quality_name }}</td>
                            <td>{{ $record->bf }}</td>
                            <td>{{ $record->gsm }}</td>
                            <td>{{ $record->production_in_kg }}</td>
                        </tr>
                        @php $total_production += $record->production_in_kg; @endphp
                        @endforeach
                        <!-- Total Production Row -->
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Total Production in KG</td>
                            <td>{{ $total_production }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p>No records found for selected filters.</p>
                @endif


                </div>
            </div>
        </div>
    </section>
</div>

<!-- Quality Modal -->
<div class="modal" id="qualityModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Production</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('deckle-process.add_quality')}}">
                    @csrf
                    <div class="row">
                        <input type="hidden" id="deckle_id" name="deckle_id" required>
                        <input type="hidden" id="deckle_number" name="deckle_number" required>
                        <input type="hidden" id="quality_last_row_id" name="last_row_id" required>
                        <div class="mb-3 mt-3">
                            <label for="actual_production_in_kg" class="form-label">Production In Kg:</label>
                            <input type="number" class="form-control" id="actual_production_in_kg" placeholder="2000" name="actual_production_in_kg" required>
                        </div>
                        <div class="mb-3">
                            <label for="actual_speed" class="form-label">Speed:</label>
                            <input type="number" class="form-control" id="actual_speed"  name="actual_speed" required>
                        </div>
                    </div>
                    <div class="row">
                        <h4>Add New Quality</h4>
                        <div class="mb-3 mt-3">
                            <label for="actual_production_in_kg" class="form-label">QUALITY:</label>
                            <select class="form-select form-select-lg select2-single" name="new_item_id" id="new_item_id" aria-label="form-select-lg example" required >
                                <option value="">SELECT QUALITY</option>
                                @foreach ($items as $item)
                                    <option value="{{$item->id}}" data-bf="{{$item->bf}}" data-gsm="{{$item->gsm}}" data-speed="{{$item->speed}}" data-item_id="{{$item->item_id}}">{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="new_item_bf" class="form-label">BF:</label>
                            <input type="text" class="form-control" name="new_item_bf" id="new_item_bf" placeholder="18" readonly required/>
                        </div>
                        <div class="mb-3">
                            <label for="new_item_gsm" class="form-label">GSM:</label>
                            <input type="text" class="form-control" name="new_item_gsm" id="new_item_gsm" placeholder="120" readonly required/>
                        </div>
                        <div class="mb-3">
                            <label for="new_start_time_stamp" class="form-label">TIME STAMP:</label>
                            <input type="text" class="form-control" name="new_start_time_stamp" id="new_start_time_stamp"  readonly value="{{date('d-m-Y H:i:s')}}"/>
                        </div>
                        <div class="mb-3">
                            <label for="new_item_bf" class="form-label">SPEED:</label>
                            <input type="text" class="form-control" name="new_speed" id="new_speed" placeholder="150" required/>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </form>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- MACHINE STOP MODAL START -->
<div class="modal" id="machineStopModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Stopping the Machine</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="stop_deckle_id">
                <input type="hidden" id="stop_deckle_no">
                <div class="mb-3 mt-3">
                    <label for="stop_reason" class="form-label">Reason for Stop:</label>
                    <select class="form-select" id="stop_reason" name="stop_reason" required>
                        <option value="">Select Reason</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Power Failure">Power Failure</option>
                        <option value="Shift Change">Shift Change</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="stop_remarks" class="form-label">Remarks:</label>
                    <textarea class="form-control" id="stop_remarks" name="stop_remarks" rows="3" placeholder="Enter remarks..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="save_machine_stop">Submit</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="deckleModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Production</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" id="new_deckle_id"  required>
                    <input type="hidden" id="new_deckle_number"  required>
                    <input type="hidden" id="new_quality"  required>
                    <input type="hidden" id="last_row_id"  required>
                    
                    <div class="mb-3 mt-3">
                        <label for="new_actual_production_in_kg" class="form-label">Production In Kg:</label>
                        <input type="number" class="form-control" id="new_actual_production_in_kg" placeholder="2000"  required>
                    </div>
                    <div class="mb-3">
                        <label for="new_actual_speed" class="form-label">Speed:</label>
                        <input type="number" class="form-control" id="new_actual_speed"  required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success add_new_deckle_btn">Submit</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
@include('layouts.footer')
<script>
$(document).ready(function(){
    // Add Quality
    $("#add_new_quality").click(function(){
        let deckle_id = $(this).attr('data-deckle_id');
        let deckle_no = $(this).attr('data-deckle_no');
        let speed = $(this).attr('data-speed');
        $("#deckle_id").val(deckle_id);
        $("#deckle_number").val(deckle_no);
        $("#actual_speed").val(speed)
        $("#quality_last_row_id").val($(this).attr('data-last_row_id'))
        $("#qualityModal").modal('toggle');
    });
    $("#new_item_id").change(function(){
        $("#new_item_bf").val($(this).find(':selected').data('bf'));
        $("#new_item_gsm").val($(this).find(':selected').data('gsm'));
    });
    

    // Machine Stop
    $("#machine_stop").click(function(){
        let deckle_id = $(this).data('deckle_id');
        let deckle_no = $(this).data('deckle_no');
        $("#stop_deckle_id").val(deckle_id);
        $("#stop_deckle_no").val(deckle_no);
        $("#stop_reason").val('');
        $("#stop_remarks").val('');
        $("#machineStopModal").modal('toggle');
    });

    $("#save_machine_stop").click(function(){
        let deckle_id = $("#stop_deckle_id").val();
        let deckle_no = $("#stop_deckle_no").val();
        let reason = $("#stop_reason").val().trim();
        let remarks = $("#stop_remarks").val().trim();

        if(reason === "" || remarks === ""){
            alert("Both Reason and Remarks are required.");
            return;
        }

        $.ajax({
            url:"{{url('stop-deckle-machine')}}",
            type:"POST",
            data:{
                "_token": "{{ csrf_token() }}",
                "id": deckle_id,
                "deckle_no": deckle_no,
                "reason": reason,
                "remark": remarks
            },
            success:function(res){
                if(res!=""){
                    let obj = JSON.parse(res);
                    if(obj.status==true){
                        $("#machineStopModal").modal('hide');
                        alert("Machine stopped!");
                        location.reload();
                    }else{
                        alert("Something Went Wrong.");
                    }
                }else{
                    alert("Something Went Wrong.");
                }
            }
        });
    });
    $("#machine_start").click(function(){
            let deckle_id = $(this).attr('data-deckle_id');
            let deckle_no = $(this).attr('data-deckle_no');
            $.ajax({
                url:"{{url('start-deckle-machine')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "id": deckle_id,
                    "deckle_no":deckle_no
                },
                success:function(res){
                    if(res!=""){
                        let obj = JSON.parse(res);
                        if(obj.status==true){
                           location.reload();
                        }else{
                            alert("Something Went Wrong.");
                        }
                    }else{
                        alert("Something Went Wrong.");
                    }
                }
            });
        });
        $("#add_new_deckle").click(function(){
            let quality_id = $(this).attr('data-quality_id');
            let deckle_id = $(this).attr('data-deckle_id');
            let deckle_no = $(this).attr('data-deckle_no');
            let speed = $(this).attr('data-speed');
            let last_row_id = $(this).attr('data-last_row_id');
            $("#new_quality").val(quality_id);
            $("#new_deckle_id").val(deckle_id);
            $("#new_deckle_number").val(deckle_no);
            $("#new_actual_speed").val(speed);
            $("#last_row_id").val(last_row_id);
            $("#deckleModal").modal('toggle');
        });
        $(".add_new_deckle_btn").click(function(){
            
            let quality_id = $("#new_quality").val();
            let deckle_id = $("#new_deckle_id").val();
            let deckle_no = $("#new_deckle_number").val();
            let new_actual_production_in_kg = $("#new_actual_production_in_kg").val();
            let new_actual_speed = $("#new_actual_speed").val();
            let last_row_id = $("#last_row_id").val();
            
            $.ajax({
                url:"{{url('stop-deckle-process')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "id": deckle_id,
                    'production_in_kg':new_actual_production_in_kg,
                    'new_actual_speed':new_actual_speed,
                    'last_row_id':last_row_id

                },
                success:function(res){
                    if(res!=""){
                        let obj = JSON.parse(res);
                        if(obj.status==true){
                            let url = "{{URl('deckle-process/create')}}?quality_id="+quality_id+"&deckle_id="+deckle_id;
                            window.location = url;
                        }else{
                            alert("Something Went Wrong.");
                        }
                    }else{
                        alert("Something Went Wrong.");
                    }
                }
            });
        });
});
</script>
@endsection
