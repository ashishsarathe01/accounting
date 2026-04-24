@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                <h5 class="table-title m-0 py-2">Database Backup</h5>
                <select class="form-select company_id" style="width: 45%;">
                    @foreach($company_list as $key => $value)
                        <option value="{{$value->id}}" @if($company_id==$value->id) selected @endif>{{$value->company_name}} ({{$value->gst}})</option>
                    @endforeach
                </select>
                <button class="btn btn-danger delete_db">Delete & Download Company</button>
                <a href="{{ url('admin/merchant/backup/create') }}/{{$company_id}}" class="btn btn-primary">Create Backup</a>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th>Backup Date</th>
                                <th>Backup File Name</th>
                                <th>Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                                <tr class="font-14 font-heading bg-white">
                                    <td>{{ date('d-m-Y H:i:s',strtotime($backup->created_at))  }}</td>
                                    <td>{{ $backup->file_name }}</td>
                                    <td>{{ round(($backup->file_size/1024)/1024,2) }} MB</td>
                                    <td>
                                        <a href="{{ url('storage/app/')}}/{{ $backup->file_name }}"><button class="btn btn-info">Download</button></a>
                                        <button class="btn btn-danger delete_backup_file" data-id="{{$backup->id}}">Delete</button>
                                        <button class="btn btn-success restore_backup_file" data-id="{{$backup->id}}">Restore</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="delete_backup_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="delete_form_url">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="restore_backup_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="restore_form_url">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Restore This Backup File</h5>
                    <p class="font-14 text-body "> Do you really want to restore these backup file? </p>
                </div>
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel_restore">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-success">PROCEED</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_company_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="" id="delete_company_form_url">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent" type="button">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal delete_company_head"></h5>
                    <p class="font-14 text-body "> Do you really want to delete these company data? </p>
                </div>
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel_delete_company">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-success">PROCEED</button>
                </div>
            </form>
        </div>
    </div>
</div>
@if(session('download_file'))
<script>
    let file = "{{ session('download_file') }}";
    let url = "{{ url('storage/app') }}/" + file;

    window.open(url, '_blank');

    // reload page after download trigger
    setTimeout(function(){
        window.location.reload();
    }, 1000);
</script>
@endif
@include('layouts.footer')
<script>
    $(document).ready(function(){
        let user_id = "{{$user_id}}";
        let baseUrl = "{{ url('/') }}";
        $(".company_id").change(function(){
            let company_id = $(this).val();
            let url = `${baseUrl}/admin/merchant/backup/list/${user_id}/${company_id}`;
            window.location = url;
        });
        $(".delete_backup_file").click(function() {
            var id = $(this).attr("data-id");
            let baseUrl = "{{ url('/') }}";
            let url = `${baseUrl}/admin/merchant/backup/delete/${id}`;
            
            $("#delete_form_url").attr('action',url);
            $("#delete_backup_modal").modal("show");
        });
        $(".cancel").click(function() {
            $("#delete_backup_modal").modal("hide");
        });
        $(".restore_backup_file").click(function() {
            var id = $(this).attr("data-id");
            let baseUrl = "{{ url('/') }}";
            let url = `${baseUrl}/admin/merchant/backup/restore/${id}`;
            $("#restore_form_url").attr('action',url);
            $("#restore_backup_modal").modal("show");
        });
        $(".cancel_restore").click(function() {
            $("#restore_backup_modal").modal("hide");
        });
        $(".delete_db").click(function() {
            let baseUrl = "{{ url('/') }}";
            let company_id = $(".company_id").val();
            let company_name = $(".company_id option:selected").text();
            $(".delete_company_head").html("Delete <strong>"+company_name+"</strong> Company DataBase.");
            let url = `${baseUrl}/admin/merchant/backup/create/${company_id}/1`;
            $("#delete_company_form_url").attr('action',url);
            $("#delete_company_modal").modal("show");
        });
        $(".cancel_delete_company").click(function() {
            $("#delete_company_modal").modal("hide");
        });
        
    });
</script>
@endsection