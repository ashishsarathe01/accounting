@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
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
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Parameterized Configuration</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Parameterized Configuration
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('store-parameterized-configuration') }}">
               @csrf
               <div class="row">
                  <div class="mb-3 col-md-3">
                     <label for="parameterized_status" class="form-label font-14 font-heading">Parameterized Status</label>
                      <select class="form-select form-select-lg" name="parameterized_status" id="parameterized_status" aria-label="form-select-lg example">
                          <option value="">Parameterized Status</option>
                          <option value="1">Yes</option>
                          <option value="0">No</option>
                      </select>
                  </div>
                  <div class="mb-3 col-md-3 parameter_sec" style="display:none">
                     <label for="no_of_parameter" class="form-label font-14 font-heading">Specify No. of Parameters</label>
                     <input type="text" class="form-control" name="no_of_parameter" id="no_of_parameter" placeholder="Specify No. of Parameters" />
                  </div>
                  <div class="clearfix"></div>
                  <div id="parameter_div" class="parameter_sec" style="display:none">                     
                  </div>                      
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary" style="display:block;">SUBMIT</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
@endsection
@include('layouts.footer')
<script type="text/javascript">
   $(document).ready(function(){
      $("#no_of_parameter").keyup(function(){
         $("#parameter_div").html('');
         if($(this).val()!=""){
            let no_of_parameter = $(this).val();
            let i = 1;let html = "";
            while(no_of_parameter>=i){
               html+='<div class="mb-3 col-md-3"><label for="no_of_parameter" class="form-label font-14 font-heading">Parameter '+i+'</label><input type="text" class="form-control parameter" name="parameter_list[]" placeholder="Parameter '+i+'" /></div>';
               i++;
            }
            $(".parameter_sec").show();
            $("#parameter_div").html(html);
         }
      });
      $("#parameterized_status").change(function(){
         $(".parameter_sec").hide();
         if($(this).val()=="1"){
            $(".parameter_sec").show();
         }
      });
   });

</script>