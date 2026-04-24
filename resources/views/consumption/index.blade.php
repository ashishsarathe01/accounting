@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Pending Consumption Deckles
                    </h5>
                    @can('view-module', 176)
                        <a href="{{ route('ConsumptionRate') }}" class="btn btn-outline-primary d-flex align-items-center gap-1">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    @endcan
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item Name</th>
                            <th>Total Weight</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($pending_stock_journal_consumption_deckles)==0)
                            <tr><td colspan="4" style="text-align: center">No Data Found</td></tr>
                        @endif
                        @foreach ($pending_stock_journal_consumption_deckles as $key=>$row)
                            @php
                            $grouped = [];$pending_reel_cutting_arr = [];$deckle_weight = [];
                            foreach ($row as $value) {
                                foreach ($value['quality'] as $v1) {
                                    $name = $v1['name'];
                                    if($value['status']==4){
                                        $weight = $v1['item_stock_sum_weight'];
                                    }else{
                                        $weight = $v1['production_in_kg'];
                                        array_push($pending_reel_cutting_arr,$value['deckle_no']);
                                    }
                                    array_push($deckle_weight,array("deckle_no"=>$value['deckle_no'],"item"=>$name,"weight"=>$weight));
                                    if (!isset($grouped[$name])) {
                                        $grouped[$name] = 0;
                                    }
                                    $grouped[$name] += $weight;
                                }
                            }
                            $item_weight_grouped = [];
                            foreach ($deckle_weight as $rows) {
                                $item_weight_grouped[$rows['item']][] = $rows;
                            }
                            @endphp
                            <tr>
                                <td>{{ date('d-m-Y',strtotime($key)) }}</td>
                                <td>
                                    @foreach($grouped as $k1=>$v1)
                                        @if($v1 > 0)
                                        {{$k1}}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($grouped as $k1=>$v1)
                                        @if($v1 > 0)
                                            <span data-detail="{{json_encode($item_weight_grouped[$k1])}}" class="view_weight_detail" style="cursor: pointer;color:#0000FF">{{$v1}}</span><br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @if(count($pending_reel_cutting_arr)==0)
                                        <a href="{{ route('account-production.create') }}?date={{$key}}"><button type="button" class="btn btn-primary">Proceed</button></a>
                                    @else
                                        <strong>Pending Deckle For Reel Cutting</strong><br>
                                        @foreach($pending_reel_cutting_arr as $k1=>$v1)
                                            Deckle No. : {{$v1}}<br>
                                        @endforeach
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Completed Consumption Deckles
                    </h5>
                    <form  action="{{ route('consumption.index') }}" method="GET">
                        @csrf
                        <div class="d-md-flex d-block">                  
                            <div class="calender-administrator my-2 my-md-0">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                            </div>
                            <div class="calender-administrator ms-md-4">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                            </div>
                            <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                        </div>
                    </form>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item Name</th>
                            <th>Total Weight</th>
                            <th>Deckles</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($completeStockJournalItems)==0)
                            <tr><td colspan="4" style="text-align: center">No Data Found</td></tr>
                        @endif
                        @foreach ($completeStockJournalItems as $key=>$row)
                            @php
                            $grouped = []; $stock_journal_id = "";$deckle_no = [];
                            foreach ($row as $value) {
                                foreach ($value['quality'] as $v1) {
                                    $name = $v1['name'];
                                    $weight = $v1['item_stock_sum_weight'];
                                    if (!isset($grouped[$name])) {
                                        $grouped[$name] = 0;
                                    }
                                    $grouped[$name] += $weight;
                                    
                                }
                                array_push($deckle_no,$value['deckle_no']);
                                $stock_journal_id = $value['stock_journal_status'];
                            }
                            @endphp
                            <tr>
                                <td>{{ date('d-m-Y',strtotime($key)) }}</td>
                                <td>
                                    @foreach($grouped as $k1=>$v1)
                                        {{$k1}}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($grouped as $k1=>$v1)
                                        {{$v1}}<br>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($deckle_no as $k1=>$v1)
                                        Deckle No. : {{$v1}}<br>
                                    @endforeach
                                </td>
                                
                                <td>
                                    <a href="{{ route('account-production.edit',$stock_journal_id) }}"><button type="button" class="btn btn-primary">View</button></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="tableModal" tabindex="-1" aria-labelledby="tableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tableModalLabel">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 weight_detail_table">
                        
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@include('layouts.footer')
<script>
    $(".view_weight_detail").click(function(){
        var detail = $(this).data('detail');
        var total_weight = 0;
        var detail_html = '<table class="table table-bordered"><thead><tr><th>Deckle No.</th><th>Item Name</th><th>Weight</th></tr></thead><tbody>';
        for(var i=0;i<detail.length;i++){
            if(detail[i].weight==0 || detail[i].weight=="" || detail[i].weight==null){
                continue;
            }
            detail_html += '<tr><td>'+detail[i].deckle_no+'</td><td>'+detail[i].item+'</td><td>'+detail[i].weight+'</td></tr>';
            total_weight += parseFloat(detail[i].weight);
        }
        detail_html += '<tr><th></th><th>Total</th><th>'+total_weight+'</th></tr>';
        detail_html += '</tbody></table>';
        $(".weight_detail_table tbody").html(detail_html);
        $("#tableModal").modal('show');
        
    });
</script>
@endsection
