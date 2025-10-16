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
                        View Deckle ({{$deckle->deckle_no}})
                    </h5>
                    <a href="{{ route('deckle-process.index') }}" class="btn btn-xs-primary">
                        Back
                    </a>
                </div>
                
                @php $last_quality_id = $deckle->item_id;@endphp
                <div class="bg-white table-view shadow-sm">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="#">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-3">
                                <label for="deckle_no" class="form-label font-14 font-heading">DECKLE NO.</label>
                                <input type="text" class="form-control" name="deckle_no" id="deckle_no" value="{{$deckle->deckle_no}}" readonly>
                            </div>
                            <div class="mb-12 col-md-12"></div>
                            <hr>
                            <div class="mb-3 col-md-3">
                                <label for="item_id" class="form-label font-14 font-heading">QUALITY</label>
                                <input type="text" class="form-control" name="item_id" id="item_id" value="{{$deckle->name}}" readonly/>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label for="item_bf" class="form-label font-14 font-heading">BF</label>
                                <input type="text" class="form-control" name="item_bf" id="item_bf" value="{{$deckle->bf}}" readonly/>
                            </div>
                            <div class="mb-3 col-md-3" style="padding-bottom:10px;">
                                <label for="item_gsm" class="form-label font-14 font-heading">GSM</label>
                                <input type="text" class="form-control" name="item_gsm" id="item_gsm" value="{{$deckle->gsm}}" readonly>
                            </div>
                            
                            <div class="mb-3 col-md-3">
                                <label for="start_time_stamp" class="form-label font-14 font-heading">TIME STAMP</label>
                                <input type="text" class="form-control" name="start_time_stamp" id="start_time_stamp" value="{{date('d-m-Y H:i:s',strtotime($deckle->start_time_stamp))}}" readonly>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label for="production_in_kg" class="form-label font-14 font-heading">PRODUCTION IN KG</label>
                                <input type="text" class="form-control" name="production_in_kg" id="production_in_kg" value="{{$deckle->production_in_kg}}" readonly>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label for="speed" class="form-label font-14 font-heading">SPEED</label>
                                <input type="text" class="form-control" name="speed" id="speed" value="{{$deckle->speed}}" readonly>
                            </div> 
                        </div>
                        
                        @foreach($deckle_qualitys as $key => $value)
                            <hr>
                            <div class="row">
                                <div class="mb-12 col-md-12"></div>
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
                                    <input type="text" class="form-control" name="production_in_kg" id="production_in_kg" value="{{$deckle->production_in_kg}}" readonly>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="speed" class="form-label font-14 font-heading">SPEED</label>
                                    <input type="text" class="form-control" name="speed" id="speed" value="{{$value->speed}}" readonly>
                                </div> 
                            </div>
                            @php $last_quality_id = $value->item_id; @endphp
                        @endforeach
                        <div class="text-start">
                            @if($deckle->stop_machine_status==0)
                                <button type="button" id="add_new_quality" class="btn  btn-xs-primary ">
                                    ADD QUALITY
                                </button>
                                <button type="button" id="add_new_deckle" data-quality_id="{{$last_quality_id}}" class="btn  btn-xs-primary ">
                                    ADD NEW DECKLE
                                </button>
                                <button type="button" id="machine_stop" class="btn  btn-xs-primary ">
                                    MACHINE STOP
                                </button>
                            @else
                                <button type="button" id="machine_start" class="btn  btn-xs-primary ">
                                    MACHINE START
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="bg-white table-view shadow-sm add_new_quality_section" style="display:none">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('deckle-process.add_quality')}}">
                        @csrf
                        <div class="row">
                            <input type="hidden" value="{{$deckle->id}}" name="deckle_id">
                            <input type="hidden" value="{{$deckle->deckle_no}}" name="deckle_no">
                            <div class="mb-6 col-md-5">
                                <label for="new_item_id" class="form-label font-14 font-heading">QUALITY</label>
                                <select class="form-select form-select-lg select2-single" name="new_item_id" id="new_item_id" aria-label="form-select-lg example" required >
                                    <option value="">SELECT QUALITY</option>
                                    @foreach ($items as $item)
                                        <option value="{{$item->id}}" data-bf="{{$item->bf}}" data-gsm="{{$item->gsm}}" data-speed="{{$item->speed}}" data-item_id="{{$item->item_id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 col-md-12"></div>
                            <div class="mb-3 col-md-5">
                                <label for="new_item_bf" class="form-label font-14 font-heading">BF</label>
                                <input type="text" class="form-control" name="new_item_bf" id="new_item_bf" placeholder="18" readonly required/>
                            </div>
                            <div class="mb-3 col-md-12"></div>
                            <div class="mb-3 col-md-5" style="padding-bottom:10px;">
                                <label for="new_item_gsm" class="form-label font-14 font-heading">GSM</label>
                                <input type="text" class="form-control" name="new_item_gsm" id="new_item_gsm" placeholder="120" readonly required/>
                            </div>                            
                            <div class="mb-3 col-md-12"></div>
                            <div class="mb-3 col-md-3">
                                <label for="new_start_time_stamp" class="form-label font-14 font-heading">TIME STAMP</label>
                                <input type="text" class="form-control" name="new_start_time_stamp" id="new_start_time_stamp"  readonly value="{{date('d-m-Y H:i:s')}}"/>
                            </div>
                            <div class="mb-3 col-md-12"></div>
                            <div class="mb-3 col-md-3">
                                <label for="new_production_in_kg" class="form-label font-14 font-heading">PRODUCTION IN KG</label>
                                <input type="text" class="form-control" name="new_production_in_kg" id="new_production_in_kg" placeholder="2000" required/>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label for="new_speed" class="form-label font-14 font-heading">SPEED</label>
                                <input type="text" class="form-control" name="new_speed" id="new_speed" placeholder="150" required/>
                            </div> 
                        </div>
                        <div class="text-start">
                            <button type="submit" id="submit" class="btn  btn-xs-primary ">
                                SUBMIT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
   
</body>
@include('layouts.footer')
<script type="text/javascript">
    var deckle_id = "{{$deckle->id}}";
    var deckle_no = "{{$deckle->deckle_no}}";
    $(document).ready(function(){
        // $( ".select2-single, .select2-multiple" ).select2({ width: '100%' }); 
        $("#add_new_quality").click(function(){
            $(".add_new_quality_section").show();
            $('html, body').animate({
                scrollTop: $(document).height()
            }, 800);
        });
        $("#new_item_id").change(function(){
            $("#new_item_bf").val($(this).find(':selected').data('bf'));
            $("#new_item_gsm").val($(this).find(':selected').data('gsm'));
        });
        $("#add_new_deckle").click(function(){
            if(confirm("Are you want to add new deckle?")==true){
                let quality_id = $(this).attr('data-quality_id')
                $.ajax({
                    url:"{{url('stop-deckle-process')}}",
                    type:"POST",
                    data:{
                        "_token": "{{ csrf_token() }}",
                        "id": deckle_id,
                    },
                    success:function(res){
                        if(res!=""){
                            let obj = JSON.parse(res);
                            if(obj.status==true){
                                
                                let url = "{{URl('deckle-process/create')}}?quality_id="+quality_id;
                                window.location = url;
                            }else{
                                alert("Something Went Wrong.");
                            }
                        }else{
                            alert("Something Went Wrong.");
                        }
                    }
                });
               
            }
        });
        $("#machine_stop").click(function(){
            $.ajax({
                url:"{{url('stop-deckle-machine')}}",
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
        $("#machine_start").click(function(){
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
                
   });
</script>
@endsection