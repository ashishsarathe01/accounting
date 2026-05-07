@extends('layouts.app')
@section('content')

@include('layouts.header')

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

    .table th,
    .table td{
        vertical-align: middle;
    }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    GST Rate Update Manage Item
                </h5>

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm"
                      method="POST"
                      action="{{ route('gst-rate-update-manage-item-store') }}">

                    @csrf

                    <div class="row">

                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">
                                EFFECTIVE DATE
                            </label>

                            <input type="date"
                                   class="form-control"
                                   name="effective_date"
                                   id="effective_date"
                                   required>
                        </div>

                    </div>

                    <div class="table-responsive mt-3">

                        <table class="table table-bordered">

                            <thead class="bg-light">

                                <tr>
                                    <th width="5%">#</th>
                                    <th width="35%">ITEM NAME</th>
                                    <th width="20%">HSN CODE</th>
                                    <th width="15%">CURRENT GST</th>
                                    <th width="25%">NEW GST RATE</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach($items as $key => $item)

                                    <tr>

                                        <td>
                                            {{ $key + 1 }}
                                        </td>

                                        <td>
                                            {{ $item->name }}
                                        </td>

                                        <td>
                                            {{ $item->hsn_code }}
                                        </td>

                                        <td>
                                            @if($item->gst_rate == 0 && $item->item_type == 'nil_rated')
                                                0% (Nil Rated)
                                            @elseif($item->gst_rate == 0 && $item->item_type == 'exempted')
                                                Exempted
                                            @else
                                                {{ $item->gst_rate }}%
                                            @endif
                                        </td>

                                        <td>

                                            <select class="form-select select2-single gst_rate"
                                                    name="gst_rate[{{$item->id}}]"
                                                    data-id="{{$item->id}}">

                                                <option value="">
                                                    SELECT GST RATE
                                                </option>

                                                <option value="0"
                                                        data-type="nil_rated">
                                                    0% (Nil Rated Goods)
                                                </option>

                                                <option value="0"
                                                        data-type="exempted">
                                                    (Exempted Goods)
                                                </option>

                                                <option value="0.25"
                                                        data-type="taxable">
                                                    0.25% (Precious stones, etc.)
                                                </option>

                                                <option value="3"
                                                        data-type="taxable">
                                                    3% (Gold, jewelry)
                                                </option>

                                                <option value="5"
                                                        data-type="taxable">
                                                    5%
                                                </option>

                                                <option value="12"
                                                        data-type="taxable">
                                                    12%
                                                </option>

                                                <option value="18"
                                                        data-type="taxable">
                                                    18%
                                                </option>

                                                <option value="28"
                                                        data-type="taxable">
                                                    28%
                                                </option>

                                            </select>

                                            <input type="hidden"
                                                   name="item_type[{{$item->id}}]"
                                                   id="item_type_{{$item->id}}">

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="text-start mt-3">

                        <button type="submit"
                                class="btn btn-xs-primary">

                            SUBMIT

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </section>
</div>

@include('layouts.footer')

<script>

    $(document).ready(function(){

        $(".select2-single").select2({
            width:'100%'
        });

        $(document).on('change','.gst_rate',function(){

            var item_id = $(this).data('id');

            var selectedOption = $(this).find('option:selected');

            var gstType = selectedOption.data('type');

            $("#item_type_"+item_id).val(gstType);

        });

    });

</script>

@endsection