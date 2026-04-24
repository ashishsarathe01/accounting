@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                {{-- ===== SUCCESS / ERROR (LIKE SALES PAGE) ===== --}}
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet shadow-sm">
                    Spare Part Configuration
                </h5>

                <form method="POST" action="{{ route('supplier.sparepart.configuration.save') }}">
                    @csrf

                    <div class="card-body mt-4">

                        {{-- ================= PO CONFIGURATION ================= --}}
                        <div class="card mb-4 shadow-sm">
                            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                                PO Configuration
                            </div>

                            <div class="card-body row">

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">PO Prefix</label>
                                    <input type="text"
                                           class="form-control"
                                           name="po_prefix"
                                           value="{{ $config->po_prefix ?? '' }}"
                                           placeholder="Ex: SP-PO-">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Starting From</label>
                                    <input type="number"
                                           class="form-control"
                                           name="po_start_from"
                                           value="{{ $config->po_start_from ?? '' }}"
                                           placeholder="Ex: 1001">
                                </div>

                            </div>
                        </div>

                        {{-- ================= TERMS & CONDITIONS ================= --}}
                        <div class="card shadow-sm">
                        <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                                <span>Terms & Conditions</span>
                                <button type="button" class="btn btn-sm btn-primary" id="addTerm">
                                    + Add
                                </button>
                            </div>

                            <div class="card-body">

                                <table class="table table-bordered" id="termsTable">
                                    <thead>
                                        <tr>
                                            <th width="70%">Term</th>
                                            <th width="15%">Sequence</th>
                                            <th width="15%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    @forelse($terms as $i => $term)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="terms[{{ $i }}][id]" value="{{ $term->id }}">
                                                <textarea class="form-control"
                                                          name="terms[{{ $i }}][term_text]"
                                                          rows="2">{{ $term->term_text }}</textarea>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       class="form-control sequence"
                                                       name="terms[{{ $i }}][sequence]"
                                                       value="{{ $term->sequence }}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-sm btn-danger removeTerm">−</button>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- empty --}}
                                    @endforelse

                                    </tbody>
                                </table>

                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success">
                                Save Configuration
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
$(document).ready(function () {

    // Add new term row
    $('#addTerm').click(function () {

        let index = $('#termsTable tbody tr').length;
        let maxSeq = 0;

        $('.sequence').each(function () {
            let val = parseInt($(this).val()) || 0;
            maxSeq = Math.max(maxSeq, val);
        });

        let row = `
            <tr>
                <td>
                    <input type="hidden" name="terms[${index}][id]" value="">
                    <textarea class="form-control"
                              name="terms[${index}][term_text]"
                              rows="2"></textarea>
                </td>
                <td>
                    <input type="number"
                           class="form-control sequence"
                           name="terms[${index}][sequence]"
                           value="${maxSeq + 1}">
                </td>
                <td class="text-center">
                    <button type="button"
                            class="btn btn-sm btn-danger removeTerm">−</button>
                </td>
            </tr>
        `;

        $('#termsTable tbody').append(row);
    });

    // Remove term row
    $(document).on('click', '.removeTerm', function () {
        $(this).closest('tr').remove();
    });

});
</script>

@endsection
