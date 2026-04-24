
@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                <div class="card mt-3">
                    <div class="card-header">
                        <h4>Add Sub Items</h4>
                    </div>
                    <div class="card-body">
                        <form id="itemForm" method="POST" action="{{route('store-sub-item')}}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{$item_id}}" id="item_id">
                            <div id="itemRows">
                                <!-- Initial item row -->
                                <div class="row item-row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Item Name</label>
                                            <input type="text" name="items[0][name]" class="form-control next-input" required placeholder="Enter item Name">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="number" name="items[0][quantity]" class="form-control next-input" required placeholder="Enter Quantity">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-row" style="margin-top: 32px;" disabled>Remove</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary" id="addMore">Add More Items</button>
                                    <button type="submit" class="btn btn-success" id="submit">Save Items</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script type="text/javascript">
$(document).ready(function(){
    // ...existing code...
    
    let rowCount = 0;
    
    // Add more items
    $('#addMore').click(function(){
        rowCount++;
        const newRow = `
            <div class="row item-row mb-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" name="items[${rowCount}][name]" class="form-control next-input" required placeholder="Enter Item Name">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="items[${rowCount}][quantity]" class="form-control next-input" required placeholder="Enter Quantity">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-row" style="margin-top: 32px;">Remove</button>
                </div>
            </div>
        `;
        $('#itemRows').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function(){
        $(this).closest('.item-row').remove();
    });

    // Form submission
    $('#itemForm').submit(function(e){
        e.preventDefault();
        var item_id = $("#item_id").val();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                if(response.success){
                    alert('Items saved successfully!');
                   
                        //window.location.reload();
                        window.location = "{{ url('manage-sub-item') }}/" + item_id;

                   
                } else {
                    any('Error saving items');
                }
            },
            error: function(){
                any('Error saving items');
            }
        });
    });

    // Handle Enter key navigation
    $(document).on('keydown', '.next-input', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            let inputs = $('.next-input');
            let idx = inputs.index(this);
            if (idx === inputs.length - 1) {
                $('#submit').focus();
            } else {
                inputs[idx + 1].focus();
            }
        }
    });
});
</script>

@endsection