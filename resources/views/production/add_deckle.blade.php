@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
 <style>
    .select2-selection{
        height:48px !important;
    }
    .select2-selection__rendered{
        line-height: 46px !important;
    }
    .select2-selection__arrow{
        height: 43px !important;
    }
 </style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">                
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Deckle</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('deckle-process.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="mb-6 col-md-5">
                                    <label for="item_id" class="form-label font-14 font-heading">QUALITY</label>
                                    <select class="form-select form-select-lg select2-single" name="item_id" id="item_id" aria-label="form-select-lg example" required >
                                        <option value="">SELECT QUALITY</option>
                                        @foreach ($items as $item)
                                            <option value="{{$item->id}}" data-bf="{{$item->bf}}" data-gsm="{{$item->gsm}}" data-speed="{{$item->speed}}" data-item_id="{{$item->item_id}}" @if($item->id==$quality_id) selected @endif>{{$item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5">
                                    <label for="item_bf" class="form-label font-14 font-heading">BF</label>
                                    <input type="text" class="form-control" name="item_bf" id="item_bf" placeholder="18" readonly required/>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5" style="padding-bottom:10px;">
                                    <label for="item_gsm" class="form-label font-14 font-heading">GSM</label>
                                    <input type="text" class="form-control" name="item_gsm" id="item_gsm" placeholder="120" readonly required/>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="deckle_no" class="form-label font-14 font-heading">DECKLE NO.</label>
                                    <input type="text" class="form-control" name="deckle_no" id="deckle_no" placeholder="1" readonly required value="{{$deckle_no}}">
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="start_time_stamp" class="form-label font-14 font-heading">TIME STAMP</label>
                                    <input type="text" class="form-control" name="start_time_stamp" id="start_time_stamp"  readonly value="{{date('d-m-Y H:i:s')}}"/>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="speed" class="form-label font-14 font-heading">SPEED</label>
                                    <input type="text" class="form-control" name="speed" id="speed" placeholder="150" required/>
                                </div>                                
                            </div>
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
    </section>
</div>
</body>
@include('layouts.footer')
<script type="text/javascript">
    $(document).ready(function(){
        $( ".select2-single, .select2-multiple" ).select2({ width: '100%' });  
        $("#item_id").change(function(){
            $("#item_bf").val($(this).find(':selected').data('bf'));
            $("#item_gsm").val($(this).find(':selected').data('gsm'));
        });
        $("#item_id").change();
    });


</script>

@endsection