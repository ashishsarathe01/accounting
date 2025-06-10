@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
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
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}
               </div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Set Privileges
            </h5>
            <form id="employee_form" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('set-employee-privileges')}}">
               @csrf
               <div class="row">
                <input type="hidden" name="employee_id" value="{{$employee_id}}">
                 <ul>
                    @foreach ($privileges as $module)
                        @include('privilege_module', ['module' => $module])
                    @endforeach
                </ul>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary" id="save_btn">
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
<script>
    document.querySelectorAll('.toggle').forEach(function(btn) {
        btn.addEventListener('click', function () {
            const ul = this.closest('li').querySelector('ul');
            if (ul) {
                ul.classList.toggle('hidden');
                this.textContent = ul.classList.contains('hidden') ? '[+]' : '[-]';
            }
        });
    });
</script>
@endsection