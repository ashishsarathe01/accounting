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
            <nav>
               <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                  <li class="breadcrumb-item">Dashboard</li>
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Edit Items Group</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Items Group</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-item-group.update') }}">
               @csrf
               <input type="hidden" value="{{ $itemsgroup->id }}" id="itemgroup_id" name="itemgroup_id" />
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Name</label>
                     <input type="text" class="form-control" id="group_name" name="group_name" value="{{ $itemsgroup->group_name }}" placeholder="Enter name" required />
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-3 col-md-3">
                     <label class="form-label font-14 font-heading">Parameterized Stock Status</label>
                     <select class="form-select form-select-lg" name="parameterized_stock_status" id="parameterized_stock_status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="1" <?php echo $itemsgroup->parameterized_stock_status ==1 ? 'selected':'';?>>Enable</option>
                        <option value="0" <?php echo $itemsgroup->parameterized_stock_status ==0 ? 'selected':'';?>>Disable</option>
                     </select>
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-3 col-md-3 parameter_stock" style="display:none;">
                     <label class="form-label font-14 font-heading">Parameterized Stock Details Configuration</label><br>
                     <input type="radio" name="config_status" class="config_status" value="DEFAULT" <?php if($itemsgroup->config_status=="DEFAULT"){ echo 'checked';} ?>> Use Default Config <input type="radio" name="config_status" class="config_status" value="SEPARATE" <?php if($itemsgroup->config_status=="SEPARATE"){ echo 'checked';} ?>> Use Separate Config
                  </div>
                  <div class="clearfix"></div>
                  <div class="mb-3 col-md-3 parameter_sec" style="display:none;">
                     <label for="no_of_parameter" class="form-label font-14 font-heading">Specify No. of Parameters</label>
                     <input type="text" class="form-control" name="no_of_parameter" id="no_of_parameter" placeholder="Specify No. of Parameters" value="{{$itemsgroup->no_of_parameter}}" />
                  </div>
                  <div class="clearfix"></div>
                  <div id="parameter_div" class="parameter_sec row" style="display:none"></div>
                  <div class="mb-3 col-md-3">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option <?php echo $itemsgroup->status ==1 ? 'selected':'';?> value="1">Enable</option>
                        <option <?php echo $itemsgroup->status ==0 ? 'selected':'';?> value="0">Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary">UPDATE</button>
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="addPredefinedValueModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <h4 class="modal-title"><span id="modal_parameter_name"></span> Parameter Value Details</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center p-0">
            <div class="row">
               <div class="mb-12 col-md-12">
                  <label for="no_of_parameter" class="form-label font-14 font-heading">Parameter Value (Max : 10)</label>
                  <input type="text" class="form-control" id="parameter_value" placeholder="Enter Parameter Value">
               </div>
               <div class="mb-12 col-md-12">
                  <label for="no_of_parameter" class="form-label font-14 font-heading">Alias</label>
                  <input type="text" class="form-control" id="parameter_value_alias" placeholder="Enter Alias">
               </div>
            </div>
         </div>
         <input type="hidden" id="hideid">
         <br><br>
         <table class="table table-bordered parameter_value_tbl" id="">
            <thead>
               <tr>
                  <td>Parameter Value</td>
                  <td>Parameter Alias</td>
               </tr>
            </thead>
            <tbody></tbody>
         </table>
         <br><br>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-danger cancel">CANCEL</button>
            <button type="button" class="ms-3 btn btn-info addDefinedValueBtn">ADD</button>
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script type="text/javascript">
   var predefined_value_arr = [];
   $(document).ready(function(){
      parameterized_stock_status('{{$itemsgroup->parameterized_stock_status}}');
      callParameterInput();
      $("#no_of_parameter").keyup(function(){
        callParameterInput();
      });
   });
   function callParameterInput(){
      $("#parameter_div").html('');
      if($("#no_of_parameter").val()!=""){
         let predefined_check_arr = [];
         let no_of_parameter = $("#no_of_parameter").val();
         let i = 1;let html = "";let j = 0;
         let para = [];
         para = <?php if($itemsgroup){ echo $itemsgroup->parameters; }else{ echo "[]";}?>;
         while(no_of_parameter>=i){
            let ind = "";
            let v = "";
            let open_check = "checked";let predefined_check = "";let predefined_values = "";
            if(para.length>0){
               if(para[j]){
                  v = para[j]['paremeter_name'];
                  if(para[j]['parameter_type']=="PREDEFINED"){
                     predefined_check = "checked";
                     open_check = "";
                     predefined_values = para[j]['predefined_value'];
                  }
               }
            }
            let input_predefined_values = "";let input_predefined_values_alias = "";
            if(predefined_values!=""){
               predefined_values.forEach(function(e){
                  if(e.predefined_value!="" && e.predefined_value!=null){
                     input_predefined_values+=","+e.predefined_value;
                  }
                  input_predefined_values_alias+=","+e.predefined_value_alias;                  
               });
               input_predefined_values = input_predefined_values.replace(/^,/, '');
               input_predefined_values_alias = input_predefined_values_alias.replace(/^,/, '');
            }
            html+='<div class="mb-3 col-md-3"><label for="no_of_parameter" class="form-label font-14 font-heading">Parameter '+i+'</label><input type="text" class="form-control parameter" name="parameter_list[]" placeholder="Parameter '+i+' Name" value="'+v+'" id="parameter_name_'+j+'"></div><div class="mb-1 col-md-1"><label for="no_of_parameter" class="form-label font-14 font-heading">Open</label><br><input type="radio" class="parameter_type_'+j+'" name="parameter_type_'+j+'" value="OPEN" '+open_check+' onclick="hideAddValueButton('+j+')"></div><div class="mb-1 col-md-1"><label for="no_of_parameter" class="form-label font-14 font-heading">Predefined</label><br><input type="radio" class="parameter_type_'+j+'" name="parameter_type_'+j+'" '+predefined_check+' value="PREDEFINED" onclick="showAddValueButton('+j+')"></div><div class="mb-3 col-md-3"><label for="no_of_parameter" class="form-label font-14 font-heading"><button type="button" class="btn btn-primary add_value"  data-id="'+j+'" id="add_value_'+j+'" style="display:none">Add Values</button></label></div><input type="hidden" name="defined_value_'+j+'" id="defined_value_'+j+'" value="'+input_predefined_values+'"><input type="hidden" name="defined_value_alias_'+j+'" id="defined_value_alias_'+j+'" value="'+input_predefined_values_alias+'"><div class="clearfix"></div>';
            if(predefined_check=="checked"){
               predefined_check_arr.push(j);
               predefined_value_arr[j] = predefined_values;
            }
            i++;
            j++;
         }
         $(".parameter_sec").show();
         $("#parameter_div").html(html);
         predefined_check_arr.forEach(function(e){
            showAddValueButton(e)
         });
      }
   }
   function showAddValueButton(i){
      $("#add_value_"+i).show();
   }
   function hideAddValueButton(i){
      $("#add_value_"+i).hide();
   }
   $(document).on('click','.add_value',function(){
      let id = $(this).attr('data-id');
      let html = "";
      if(predefined_value_arr[id]){
         predefined_value_arr[id].forEach(function(e){         
            if(e.predefined_value_alias==null){
               e.predefined_value_alias="";
            }
            html+='<tr><td>'+e.predefined_value+'</td><td>'+e.predefined_value_alias+'</td></tr>';
         });
      }
      $(".parameter_value_tbl tbody").html(html);
      $("#modal_parameter_name").html($("#parameter_name_"+id).val());
      $("#hideid").val(id);
      $("#addPredefinedValueModal").modal('toggle');
   });
   $(".cancel").click(function() {
       $("#addPredefinedValueModal").modal("hide");
   });
   $("#parameterized_stock_status").change(function(){
      parameterized_stock_status($(this).val());
   });
   function parameterized_stock_status(status){
      if(status=="1"){
         $(".parameter_stock").show();
      }else if(status=="0"){
         $(".parameter_stock").hide();
      }
   }
   $(".config_status").click(function(){
      if($(this).val()=="DEFAULT"){
         $(".parameter_sec").hide();
      }else if($(this).val()=="SEPARATE"){
         $(".parameter_sec").show();
      }
   });
   $(".addDefinedValueBtn").click(function(){
      let id = $("#hideid").val();
      var result = $("#defined_value_"+id).val().split(',');
      if($("#parameter_value").val()==""){
         alert("Please Enter Parameter Value");
         return;
      }
      if(jQuery.inArray($("#parameter_value").val(), result) !== -1){
         alert("Value Already Exists.");
         return;
      }
      let defined_value = $("#defined_value_"+id).val();
      defined_value+=','+$("#parameter_value").val();
      defined_value = defined_value.replace(/^,/, '');
      $("#defined_value_"+id).val(defined_value);

      let defined_value_alias = $("#defined_value_alias_"+id).val();
      defined_value_alias+=','+$("#parameter_value_alias").val();
      defined_value_alias = defined_value_alias.replace(/^,/, '');
      $("#defined_value_alias_"+id).val(defined_value_alias);


      alert("Add Successfully");
      $("#parameter_value").val('');
      $("#parameter_value_alias").val('');
      $("#addPredefinedValueModal").modal('toggle');
   })
</script>
@endsection