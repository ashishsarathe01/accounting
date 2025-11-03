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
                    <h5 class="table-title m-0 py-2">Stock</h5>
                    <a href="{{ route('item-size-stocks.index') }}" class="btn btn-info btn-sm">
    <i class="bi bi-box-seam"></i> View All Reels
</a>

                    <a href="{{ route('production.add.stock') }}" 
                    class="btn btn-xs-primary d-flex align-items-center">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>

                </div>

                <div class="bg-white table-view shadow-sm">
                    <table class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th class="w-min-120 border-none bg-light-pink text-body">Item</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Quantity</th>
                            </tr>
                        </thead>    
                        <tbody>
                            @foreach($stocks as $key => $stock)
                                <tr data-id="{{ $stock->new_item_id }}">
                                    <td>{{ $stock->name }}</td>
                                    <td>{{ $stock->total_stock }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Reel Details Modal -->
                    <div class="modal fade" id="reelDetailsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-plum-viloet text-white">
                                    <h5 class="modal-title" id="reelModalTitle">Reel Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Size</th>
                                                <th>Count</th>
                                                <th>Reel No.</th>
                                                <th>Weight</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reelDetailsBody">
                                            <!-- dynamically filled -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
$('table tbody tr').on('dblclick', function() {
    let itemId = $(this).data('id'); 
    let itemName = $(this).find('td:first').text(); // get the item name

    if (!itemId) return;

    // Update modal title to show the item name instead of "Reel Details"
    $('#reelModalTitle').text(itemName + ' Details');

    let url = "{{ route('stock.details', ['item_id' => ':id']) }}".replace(':id', itemId);

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            let html = '';
            response.forEach(row => {
                for (let i = 0; i < row.reels.length; i++) {
                    html += `
                        <tr>
                            <td>${i === 0 ? row.size : ''}</td>
                            <td>${i === 0 ? row.count : ''}</td>
                            <td>${row.reels[i]}</td>
                            <td>${row.weights[i]}</td>
                        </tr>
                    `;
                }
            });
            $('#reelDetailsBody').html(html);
            $('#reelDetailsModal').modal('show');
        },
        error: function(xhr) {
            if (xhr.status === 404) {
                alert('No details found for this item.');
            } else {
                alert('Something went wrong.');
            }
        }
    });
});
</script>
@endsection
