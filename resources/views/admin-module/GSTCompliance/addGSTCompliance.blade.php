@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')
<style>
    select.form-select {
    height: 38px !important;
    padding: 6px 12px !important;
}
.custom-select-fix {
    height: 38px !important;
    min-height: 38px !important;
    padding: 6px 12px !important;
    line-height: 1.5 !important;
    display: block !important;
    width: 100% !important;
}

.custom-select-fix option {
    padding: 5px;
}
select.company-select {
    height: 38px !important;
    max-height: 38px !important;
    overflow: hidden !important;
}
</style>
<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            {{-- Success Message --}}
            @if(session('success'))
               <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Title --}}
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-3 px-4 mb-4">
               <h5 class="m-0">GST Compliance</h5>
            </div>

            {{-- Card --}}
            <div class="bg-white shadow-sm rounded-3">
               <div class="card-body p-4">

                  <form action="{{ route('admin.gst-compliance.store') }}" method="POST">
                     @csrf

                     <table class="table table-bordered" id="companyTable">
                        <thead>
                           <tr>
                              <th style="width:30%">Company</th>
                              <th>GST</th>
                              <th>ESIC</th>
                              <th>TDS</th>
                              <th>PF</th>
                              <th style="width:10%">Action</th>
                           </tr>
                        </thead>
                        <tbody>

                        @if(isset($settings) && $settings->count() > 0)

                            @foreach($settings as $setting)
                            <tr>
                                <td>
                                    <select name="company_id[]" class="form-control company-select custom-select-fix">
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ $company->id == $setting->company_id ? 'selected' : '' }}>
                                                {{ $company->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <input type="checkbox" name="gst[{{ $setting->company_id }}]" value="1"
                                        {{ $setting->gst ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <input type="checkbox" name="esic[{{ $setting->company_id }}]" value="1"
                                        {{ $setting->esic ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <input type="checkbox" name="tds[{{ $setting->company_id }}]" value="1"
                                        {{ $setting->tds ? 'checked' : '' }}>
                                </td>

                                <td>
                                    <input type="checkbox" name="pf[{{ $setting->company_id }}]" value="1"
                                        {{ $setting->pf ? 'checked' : '' }}>
                                </td>

                                <td class="action-cell">
                                    <button type="button" class="btn btn-success addRow">+</button>
                                    <button type="button" class="btn btn-danger removeRow">-</button>
                                </td>
                            </tr>
                            @endforeach

                        @else

                        <tr>
                            <td>
                                <select name="company_id[]" class="form-control company-select custom-select-fix">
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td><input type="checkbox" class="gst-checkbox"></td>
                            <td><input type="checkbox" class="esic-checkbox"></td>
                            <td><input type="checkbox" class="tds-checkbox"></td>
                            <td><input type="checkbox" class="pf-checkbox"></td>

                            <td class="action-cell">
                                <button type="button" class="btn btn-success addRow">+</button>
                                <button type="button" class="btn btn-danger removeRow">-</button>
                            </td>
                        </tr>

                        @endif

                        </tbody>
                     </table>

                     {{-- Buttons --}}
                     <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                           Save
                        </button>
                     </div>

                  </form>

               </div>
            </div>

         </div>
      </div>
   </section>
</div>

@include('layouts.footer')

<script>
    let rowIndex = $('#companyTable tbody tr').length;

    $(document).on('click', '.addRow', function () {

        let row = `
            <tr>
                <td>
                    <select name="company_id[]" class="form-select company-select">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                </td>

                <td><input type="checkbox" class="gst-checkbox"></td>
                <td><input type="checkbox" class="esic-checkbox"></td>
                <td><input type="checkbox" class="tds-checkbox"></td>
                <td><input type="checkbox" class="pf-checkbox"></td>

                <td class="action-cell">
                    <button type="button" class="btn btn-success addRow">+</button>
                    <button type="button" class="btn btn-danger removeRow">-</button>
                </td>
            </tr>
        `;

        $('#companyTable tbody').append(row);
        rowIndex++;
        updateButtons();
        updateCompanyDropdowns();
    });

    // Remove Row
    $(document).on('click', '.removeRow', function () {
        if ($('#companyTable tbody tr').length > 1) {
            $(this).closest('tr').remove();

            updateButtons();   
            updateCompanyDropdowns();
        }
    });

    // Prevent duplicate company selection
    $(document).on('change', '.company-select', function () {
        updateCompanyDropdowns();
    });

    function updateCompanyDropdowns() {

        let selected = [];

        $('.company-select').each(function () {
            if ($(this).val()) {
                selected.push($(this).val());
            }
        });

        $('.company-select').each(function () {

            let current = $(this).val();

            $(this).find('option').each(function () {

                if ($(this).val() !== "" && selected.includes($(this).val()) && $(this).val() !== current) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }

            });

        });
    }
    function updateButtons() {

        let rows = $('#companyTable tbody tr');
        let total = rows.length;

        // Hide all first
        rows.find('.addRow').hide();
        rows.find('.removeRow').hide();

        if (total === 1) {
            // Only one row → only +
            rows.eq(0).find('.addRow').show();
        } else {
            // Multiple rows

            // Show - on all
            rows.find('.removeRow').show();

            // Show + only on last row
            rows.last().find('.addRow').show();
        }
    }
    $(document).ready(function () {
        updateButtons();
        updateCompanyDropdowns();
    });
    $(document).on('change', '.company-select', function () {

        let companyId = $(this).val();
        let row = $(this).closest('tr');

        if (!companyId) return;

        row.find('.gst-checkbox').attr('name', 'gst[' + companyId + ']');
        row.find('.esic-checkbox').attr('name', 'esic[' + companyId + ']');
        row.find('.tds-checkbox').attr('name', 'tds[' + companyId + ']');
        row.find('.pf-checkbox').attr('name', 'pf[' + companyId + ']');

    });
</script>

@endsection