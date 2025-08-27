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
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Set Privileges</h5>
            <ul class="nav nav-fill nav-tabs" role="tablist">
               @foreach ($company as $key=>$value)
                  <li class="nav-item" role="presentation">
                     <a class="nav-link @if($key==0) active @endif" id="fill-tab-{{$value->id}}" data-bs-toggle="tab" href="#fill-tabpanel-{{$value->id}}" role="tab" aria-controls="fill-tabpanel-{{$value->id}}" aria-selected="true">{{$value->company_name}}</a>
                  </li>
               @endforeach
            </ul>
            <div class="tab-content pt-5" id="tab-content">
               @foreach ($company as $key=>$value)
                  <div class="tab-pane @if($key==0) active @endif" id="fill-tabpanel-{{$value->id}}" role="tabpanel" aria-labelledby="fill-tab-{{$value->id}}">
                     <form id="employee_form" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('set-employee-privileges')}}">
                        @csrf
                        <div class="row">
                           <input type="hidden" name="employee_id" value="{{$employee_id}}">
                           <input type="hidden" name="company_id" value="{{$value->id}}">
                           <ul>
                              @foreach ($privileges as $module)
                                 @include('privilege_module', ['module' => $module,'company_id' => $value->id,'employee_id' => $employee_id])
                              @endforeach
                           </ul>
                        </div>
                        <div class="row">
                           <div class="clearfix mb-3"></div>
                           <div class="clearfix mb-3"></div>
                           <div class="clearfix mb-3"></div>
                        </div>
                        <div  style="text-align: center !important;">
                           @if(count($company) > 1)
                              <input type="checkbox" name="apply_all"> <strong>APPLY ON ALL COMPANY</strong> 
                           @endif
                           <button type="submit" class="btn  btn-xs-primary" id="save_btn">SUBMIT</button>
                        </div>
                     </form>
                  </div>
               @endforeach
            </div> 
            
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