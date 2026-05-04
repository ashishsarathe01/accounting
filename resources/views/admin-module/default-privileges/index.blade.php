@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<style>
    ul {
        list-style: none;
        margin-left: 20px;
    }
    .toggle {
        cursor: pointer;
        font-weight: bold;
        margin-right: 5px;
    }
    .hidden {
        display: none;
    }
</style>

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">

         @include('admin-module.layouts.leftnav')

         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

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

            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                Set Default Privileges
            </h5>

            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm"
                  method="POST"
                  action="{{ route('admin.default-privileges.store') }}">

                @csrf

                <ul>
                    @foreach ($privileges as $module)
                        @include('admin-module.default-privileges.module', [
                            'module' => $module,
                            'assigned' => $assign_privilege
                        ])
                    @endforeach
                </ul>

                <div style="text-align: center !important; margin-top:20px;">
                    <button type="submit" class="btn btn-xs-primary">
                        SAVE DEFAULT PRIVILEGES
                    </button>
                </div>

            </form>

         </div>

      </div>
   </section>
</div>

@include('admin-module.layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Expand / Collapse
    document.querySelectorAll('.toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const ul = this.closest('li').querySelector('ul');
            if (ul) {
                ul.classList.toggle('hidden');
                this.textContent = ul.classList.contains('hidden') ? '[+]' : '[-]';
            }
        });
    });

});
</script>

@endsection