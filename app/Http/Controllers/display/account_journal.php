<?php 
$edit_data_arr = [];
$date = date("Y-m-d");
$limit = 2;
$json_status = 0;
if(isset($_GET['id']) && !empty($_GET['id'])){
   record_set("salary","select account_id from account_ledger where txn_mode='Journal-".$_GET['id']."' and type_id='".$_GET['id']."' and salary_status=1");
   $salary_data = mysqli_fetch_array($salary);
   record_set("journal","select * from account_journal_detail where parent_id='".$_GET['id']."'");
   while($journal_data = mysqli_fetch_array($journal)){
      $date = $journal_data['jdate'];
      if(!empty($journal_data['debit'])){
         $type = "Debit";
      }else if(!empty($journal_data['credit'])){
         $type = "Credit";
      }
      $salary_status = 0;
      if($journal_data['account']==$salary_data['account_id']){
         $salary_status = 1;
      }
      array_push($edit_data_arr,array("type"=>$type,"account"=>$journal_data['account'],"account_type"=>$journal_data['account_type'],"user_account_id"=>$journal_data['user_account_id'],"debit"=>$journal_data['debit'],"credit"=>$journal_data['credit'],"remark"=>$journal_data['remark'],"salary_status"=>$salary_status,"percentage"=>$journal_data['percentage']));
   }
   $limit = mysqli_num_rows($journal);
   record_set("jnrl","select * from account_journal where id='".$_GET['id']."'");
   $jnrl_data = mysqli_fetch_array($jnrl);

}
if(isset($_GET['json']) && !empty($_GET['json'])){
   $json_status = 1;
   $json = json_decode($_GET['json'],true);
   if(count($json)>0){
      
      foreach ($json as $key => $value) {
         if(!empty($value['debit'])){
            $type = "Debit";
         }else if(!empty($value['credit'])){
            $type = "Credit";
         }
         array_push($edit_data_arr,array("type"=>$type,"account"=>$value['account'],"account_type"=>$value['account_type'],"user_account_id"=>"","debit"=>$value['debit'],"credit"=>$value['credit'],"remark"=>"","salary_status"=>"0"));
      }
      $limit = count($json);
   }
}
// echo "<pre>";
// print_r($edit_data_arr);
?>
<style type="text/css">
   .select2-container{
      width: 200px !important;
   }
</style>
<div class="row">
   <div class="col-md-12">
      <div class="box box-primary">
         <div class="box-header with-border">
            <h3 class="box-title">Journal</h3>
            <a href="?page=account-view-journal"><button class="btn btn-info" style="float:right;">Journal List</button></a>
         </div>         
         <div class="box-body">
            <div class="col-md-2">
               <div class="form-group">
                  <label for="date">Select Date</label>
                  <input type="date" id="date" name="date" value="<?php echo $date;?>" class="form-control">
               </div>
            </div> 
            <?php 
            $claim_gst_no = "checked";$claim_gst_yes = "";
            if($jnrl_data['gst_status']==1){
               $claim_gst_no = "";$claim_gst_yes = "checked";
            }
            $show_account_no = "checked";$show_account_yes = "";
            if($_GET['showall']=="Yes"){
               $show_account_no = "";$show_account_yes = "checked";
            }
            ?>
            <div class="col-md-2">
               <div class="form-group">
                  <label for="date">Claim GST</label><br>
                  <input type="radio" name="claim_gst" class="claim_gst" value="Yes" <?php echo $claim_gst_yes;?>> Yes
                  <input type="radio" name="claim_gst" class="claim_gst" value="No" <?php echo $claim_gst_no;?>> No
               </div>
            </div>
            <?php 
            if($_SESSION['user_id']==4){ ?>
               <div class="col-md-2">
                  <div class="form-group">
                     <label for="date">Show All Account</label><br>
                     <input type="radio" name="show_account" class="show_account" value="Yes" <?php echo $show_account_yes;?>> Yes
                     <input type="radio" name="show_account" class="show_account" value="No" <?php echo $show_account_no;?>> No
                  </div>
               </div>
               <?php
            }
            ?>
            
         </div>
      </div>
   </div>
</div>
<div class="row without_gst">
   <div class="col-md-12">
      <div class="box box-primary">
         <div class="box-header with-border">
            <h3 class="box-title"></h3>
         </div>
         <table class="table table-bordered">
            <thead>
               <th>Debit/Credit</th>
               <th>Account</th>
               <th>Debit</th>
               <th>Credit</th>
               <!-- <th>Narration</th> -->
               <th></th>
            </thead>
            <tbody>
               <?php 
               $i=1;
               $loop_count = 0;
               while($i<=$limit){?>
                  <tr id="tr_<?php echo $i;?>">
                     <td>
                        <select class="form-control type" data-id="<?php echo $i;?>" id="type_<?php echo $i;?>">
                           <option value="">Select Type</option>
                           <option value="Debit" <?php if($edit_data_arr[$loop_count]['type']=="Debit"){ echo "selected";}?>>Debit</option>
                           <option value="Credit" <?php if($edit_data_arr[$loop_count]['type']=="Credit"){ echo "selected";}else if($_GET['other_freight']==1 && $loop_count==0){ echo "selected";}?>>Credit</option>
                        </select>
                     </td>
                     <td>
                        <select class="form-control account select2" data-id="<?php echo $i;?>" id="account_<?php echo $i;?>">
                           <option value="">Select Account</option>
                           <?php 
                              $account_list = "";
                              if(isset($_GET['showall']) && $_GET['showall']=="Yes"){
                                 record_set("party","select customer.id,gstA_name,customer.name,gstA,customer.email,gowdown_location,gowdown_location.location from customer inner join gowdown_location on customer.gowdown_location=gowdown_location.id where customer.status='1' and as_mill=0 and old_godown=0 order by gowdown_location");
                              }else{
                                 record_set("party","select customer.id,gstA_name,customer.name,gstA,customer.email,gowdown_location,gowdown_location.location from customer inner join gowdown_location on customer.gowdown_location=gowdown_location.id where customer.status='1' and as_mill=0 and old_godown=0 and gowdown_location='".$_SESSION['user_godown']."'");
                              }
                              
                              while($party_data = mysqli_fetch_array($party)){
                                 $selected = "";
                                 if($edit_data_arr[$loop_count]['account_type']=="party"){ 
                                    if($edit_data_arr[$loop_count]['account']==$party_data['email']){
                                       $selected = "selected";
                                    }
                                 }
                                 
                                 $account_list.='<option value="'.$party_data['email'].'" data-user_type="party" '.$selected.' data-godown="'.$party_data['gowdown_location'].'" '.$selected.'>'.$party_data['gstA_name'].' ('.$party_data['location'].')</option>';
                              }
                              record_set("mill","select id,gst_name from manage_mill where status='1'");
                              while($mill_data = mysqli_fetch_array($mill)){
                                 $selected = "";
                                 if($edit_data_arr[$loop_count]['account_type']=="mill"){ 
                                    if($edit_data_arr[$loop_count]['account']==$mill_data['id']){
                                       $selected = "selected";
                                    }
                                 }
                                 $account_list.='<option value="'.$mill_data['id'].'" data-user_type="mill" '.$selected.' data-godown="">'.$mill_data['gst_name'].'</option>';
                              }
                              record_set("account","select id,account_name,godown from account where status='1' and (godown='".$_SESSION['user_godown']."' || account_for=1)");
                              while($account_data = mysqli_fetch_array($account)){
                                 $selected = "";
                                 if($edit_data_arr[$loop_count]['account_type']=="account"){ 
                                    if($edit_data_arr[$loop_count]['account']==$account_data['id']){
                                       $selected = "selected";
                                    }
                                 }else if($_GET['other_freight']==1 && $account_data['account_name']=="FREIGHT" && $loop_count==0){ 
                                    $selected = "selected";
                                 }
                                 $account_list.='<option value="'.$account_data['id'].'" data-user_type="account" '.$selected.' data-godown="'.$account_data['godown'].'">'.$account_data['account_name'].'</option>';
                              }
                           echo $account_list;
                           $debit_dis = "disabled";
                           $credit_dis = "disabled";
                           if(!empty($edit_data_arr[$loop_count]['credit'])){
                              $credit_dis = "";
                           }
                           if(!empty($edit_data_arr[$loop_count]['debit'])){
                              $debit_dis = "";
                           }
                           if($_GET['other_freight']==1  && $loop_count==0){ 
                              $credit_dis = "";
                           }
                           ?>
                        </select>
                     </td>
                     <td><input type="text" name="" class="form-control debit" data-id="<?php echo $i;?>" id="debit_<?php echo $i;?>" placeholder="Enter Debit Amount" <?php echo $debit_dis;?> value="<?php echo $edit_data_arr[$loop_count]['debit'];?>"></td>
                     <td><input type="text" name="" class="form-control credit" data-id="<?php echo $i;?>" id="credit_<?php echo $i;?>" placeholder="Enter Credit Amount" <?php echo $credit_dis;?> value="<?php if($_GET['other_freight']==1  && $loop_count==0){ echo $_GET['amount'];}else{ echo $edit_data_arr[$loop_count]['credit'];}?>"></td>
                     <!-- <td><input type="text" name="" class="form-control narration" data-id="<?php echo $i;?>" id="narration_<?php echo $i;?>" placeholder="Enter Narration" value="<?php echo $edit_data_arr[$loop_count]['remark'];?>"></td> -->
                     <td>
                        <input type="hidden" class="salary_status" data-id="<?php echo $i;?>" id="salary_status_<?php echo $i;?>" value="<?php echo $edit_data_arr[$loop_count]['salary_status'];?>">
                        <button class="btn btn-danger btn-xs remove" data-id="<?php echo $i;?>">Remove</button>
                     </td>
                  </tr>
                  <?php
                  $loop_count++;
                  $i++;
               }
               ?>
               <tr>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th></th>
                  
                  <th><button class="btn btn-info btn-xs add_more">Add More</button></th>
               </tr>
               <tr>
                  <th></th>
                  <th>Total</th>
                  <th id="total_debit">0</th>
                  <th id="total_credit">0</th>
                  <th></th>
               </tr> 
               <tr>
                  <td colspan="6"><input type="text" class="form-control" id="remark_text" value="<?php echo $jnrl_data['remark_text'];?>" placeholder="Enter Remark.........."></td>
               </tr>            
            </tbody>
         </table>
        
      </div>
   </div>   
</div> 
<div class="row without_gst">
   <div class="col-md-12">
      <div class="box box-primary">                
         <div class="box-body">
            <?php if(isset($_GET['id']) && !empty($_GET['id'])){?>
               <p style="text-align: center;"><button type="button" class="btn btn-info edit_data">Edit</button></p>
            <?php }else{?>
               <p style="text-align: center;"><button type="button" class="btn btn-info submit_data">Submit</button></p>
            <?php } ?>
            
         </div>
      </div>
   </div>
</div>
<div class="row with_gst" style="display:none">
   <div class="col-md-12">
      <div class="box box-primary">
         <div class="box-header with-border">
            <h3 class="box-title">Journal</h3>
         </div>
         <?php
         $per = 0;
         if(count($edit_data_arr)>0){
            $tax = $edit_data_arr['0']['credit']-$edit_data_arr['1']['debit'];; 
            $per = ($tax*100)/$edit_data_arr['1']['debit'];
         }         
         ?>
         <div class="box-body">
            <div class="col-md-6">
               <div class="form-group">
                  <label for="date">Vendor</label><br>
                  <select class="form-control select2" id="vendor">
                     <option value="">Select Vendor</option>
                     <?php
                     record_set("account","select id,account_name,gst_no from account where status='1' and (godown='".$_SESSION['user_godown']."' || account_for=1) and gst_no!=''");
                     while($account_data = mysqli_fetch_array($account)){ ?>
                        <option value="<?php echo $account_data['id'];?>" data-gst="<?php echo $account_data['gst_no'];?>" data-type='account' <?php if($edit_data_arr['0']['account']==$account_data['id'] && $edit_data_arr['0']['account_type']=='account'){ echo 'selected';}?>><?php echo $account_data['account_name'];?></option>
                        <?php
                     } ?>
                     <?php
                     record_set("account","select customer.id,gstA_name,customer.name,gstA,customer.email,gowdown_location,gowdown_location.name as gname from customer inner join gowdown_location on customer.gowdown_location=gowdown_location.id where customer.status='1' and (gowdown_location='".$_SESSION['user_godown']."' || linked_user_id!='' || linked_user_id is not null) and old_godown=0");
                     while($account_data = mysqli_fetch_array($account)){ ?>
                        <option value="<?php echo $account_data['email'];?>" data-gst="<?php echo $account_data['gstA'];?>" data-type='party' <?php if($edit_data_arr['0']['account']==$account_data['email']){ echo 'selected';}?>><?php echo $account_data['gstA_name']." (".$account_data['name'].")";?></option>
                        <?php
                     } ?>
                     <?php
                     record_set("mill","select id,gst_name,gst_number from manage_mill where status='1'");
                     while($mill_data = mysqli_fetch_array($mill)){ ?>
                        <option value="<?php echo $mill_data['id'];?>" data-gst="<?php echo $mill_data['gst_number'];?>" data-type='mill' <?php if($edit_data_arr['0']['account']==$mill_data['id'] && $edit_data_arr['0']['account_type']=='mill'){ echo 'selected';}?>><?php echo $mill_data['gst_name'];?></option>
                        <?php
                     } ?>
                  </select>
               </div>
            </div>
            <div class="col-md-12"></div>
            <div class="col-md-12">
               <div class="form-group">
                  <table class="table table-bordered">
                     <thead>
                        <tr>
                           <th>Item</th>
                           <th>GST(%)</th>
                           <th>Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php 
                        $edit_status = count($edit_data_arr);
                        if(count($edit_data_arr)==0){
                           array_push($edit_data_arr,array("type"=>"","account"=>"","account_type"=>"","user_account_id"=>"","debit"=>"","credit"=>"","remark"=>"","salary_status"=>"","percentage"=>"Add"));
                        }
                        $index_of = 1;
                        foreach ($edit_data_arr as $key => $value){
                           if($value['percentage']!=''){?>
                              <tr id="row_<?php echo $index_of;?>">
                                 <td>
                                    <select class="form-control item select2" id="item_<?php echo $index_of;?>" data-index="<?php echo $index_of;?>">
                                       <option value="">Select Item</option>
                                       <?php
                                       record_set("account","select id,account_name from account where status='1' and (godown='".$_SESSION['user_godown']."' || account_for=1)");
                                       while($account_data = mysqli_fetch_array($account)){ ?>
                                          <option value="<?php echo $account_data['id'];?>" <?php if($edit_data_arr[$key]['account']==$account_data['id']){ echo 'selected';}?>><?php echo $account_data['account_name'];?></option>
                                          <?php
                                       } ?>
                                    </select>
                                 </td>
                                 <td>
                                    <select class="form-control percentage" id="percentage_<?php echo $index_of;?>" data-index="<?php echo $index_of;?>" onchange="gstCalculation();">
                                       <option value="">Select GST(%)</option>
                                       <option value="5" <?php if(trim($edit_data_arr[$key]['percentage'])==5){ echo "selected";}?>>5%</option>
                                       <option value="12" <?php if(trim($edit_data_arr[$key]['percentage'])==12){ echo "selected";}?>>12%</option>
                                       <option value="18" <?php if(trim($edit_data_arr[$key]['percentage'])==18){ echo "selected";}?>>18%</option>
                                       <option value="28" <?php if(trim($edit_data_arr[$key]['percentage'])==28){ echo "selected";}?>>28%</option>
                                    </select>
                                 </td>
                                 <td>
                                    <input type="text" class="form-control amount" id="amount_<?php echo $index_of;?>" data-index="<?php echo $index_of;?>" onkeyup="gstCalculation();" placeholder="Amount" value="<?php echo $edit_data_arr[$key]['debit']; ?>">
                                 </td>
                                 <td>
                                    <?php 
                                    if($value['percentage']=='Add'){
                                       echo '<button type="button" class="btn btn-info btn-xs add_more_gst" >Add More</button>';
                                    }else{
                                       echo '<button type="button" class="btn btn-danger btn-xs tbl_row" data-index="'.$index_of.'">Remove</button>';
                                    }
                                    ?>
                                    
                                 </td>
                              </tr>
                              <?php
                              $index_of++;
                           }
                        }
                        if($edit_status>0){
                           echo '<tr><td></td><td></td><td></td><td><button type="button" class="btn btn-info btn-xs add_more_gst" >Add More</button></td></tr>';
                        }
                        ?>
                        <tr>
                           <td></td>
                           <td style="text-align:right">Net Amount</td>
                           <td><input type="text" class="form-control" id="net_amount" placeholder="Net Amount" value="<?php echo $edit_data_arr['1']['debit'];?>" readonly></td>
                        </tr>
                        <tr class="cgst_div" style="display:none">
                           <td></td>
                           <td style="text-align:right">CGST</td>
                           <td><input type="text" class="form-control" id="cgst" placeholder="CGST" readonly value="<?php echo $edit_data_arr['2']['debit'];?>"></td>
                        </tr>
                        <tr class="sgst_div" style="display:none">
                           <td></td>
                           <td style="text-align:right">SGST</td>
                           <td><input type="text" class="form-control" id="sgst" placeholder="SGST" readonly value="<?php echo $edit_data_arr['1']['debit'];?>"></td>
                        </tr>
                        <tr class="igst_div" style="display:none">
                           <td></td>
                           <td style="text-align:right">IGST</td>
                           <td><input type="text" class="form-control" id="igst" placeholder="IGST" readonly value="<?php echo $edit_data_arr['1']['debit'];?>"></td>
                        </tr>
                        <tr>
                           <td></td>
                           <td style="text-align:right">Total Amount</td>
                           <td><input type="text" class="form-control" id="total_amount" placeholder="Total Amount" readonly value="<?php echo $edit_data_arr['0']['credit'];?>"></td>
                        </tr>
                        <tr>
                           <td></td>
                           <td style="text-align:right">Remark</td>
                           <td><input type="text" class="form-control" id="remark" placeholder="Remark" value="<?php echo $edit_data_arr['0']['remark'];?>"></td>
                        </tr>
                     </tbody>
                  </table>                  
               </div>
            </div>
         </div>
         <div class="box-footer" style="text-align: center;">
            <button type="button" class="btn btn-primary" id="add_entry">Submit</button>
         </div>
      </div>
   </div>
</div>


<script type="text/javascript">
   var merchant_gst = '<?php echo $_SESSION['gst'];?>';
   var account_gst = '';
   var other_freight = '<?php echo $_GET['other_freight']?>';
   var order_no = '<?php echo $_GET['order_no']?>';
   var eid = '<?php echo $_GET['id']?>';
   var etype = '<?php echo $edit_data_arr['0']['account_type'];?>';
   $(document).ready(function(){      
      $(document).on("change",".type",function(){
         let id = $(this).attr('data-id');
         $("#debit_"+id).val('');
         $("#credit_"+id).val('');
         let debit_total = 0;
         $(".debit").each(function(){
            if($(this).val()!=""){
               debit_total = parseFloat(debit_total) + parseFloat($(this).val());
            }
         });
         let credit_total = 0;
         $(".credit").each(function(){
            if($(this).val()!=""){
               credit_total = parseFloat(credit_total) + parseFloat($(this).val());
            }
         });
         if($(this).val()=="Credit"){
            $("#debit_"+id).prop('disabled',true);
            $("#credit_"+id).prop('disabled',false);
            let amount = debit_total - credit_total;
            if(amount>0){
               $("#credit_"+id).val(amount);
            }            
         }else{            
            $("#debit_"+id).prop('disabled',false);
            $("#credit_"+id).prop('disabled',true);
            let amount = credit_total - debit_total;
            if(amount>0){
               $("#debit_"+id).val(amount);
            }
         }
         debitTotal();
         creditTotal();
      });
      $(".submit_data").click(function(){
         var form_data = [];
         let dr = 0;
         let cr = 0;
         $(".type").each(function(){
            let id = $(this).attr('data-id');
            if($(this).val()!='' && $("#account_"+id).val()!=""){
               if($(this).val()=="Credit" && $("#credit_"+id).val()!=""){
                  form_data.push({"type":"Credit","credit":$("#credit_"+id).val(),"debit":0,"user_id":$("#account_"+id).val(),"user_type":$("#account_"+id+" option:selected").attr("data-user_type"),"godown":$("#account_"+id+" option:selected").attr("data-godown")});
                  cr = parseFloat(cr) + parseFloat($("#credit_"+id).val());
               }else if($(this).val()=="Debit" && $("#debit_"+id).val()!=""){
                  form_data.push({"type":"Debit","credit":0,"debit":$("#debit_"+id).val(),"user_id":$("#account_"+id).val(),"user_type":$("#account_"+id+" option:selected").attr("data-user_type"),"godown":$("#account_"+id+" option:selected").attr("data-godown")});
                  dr = parseFloat(dr) + parseFloat($("#debit_"+id).val());
               }               
            }
         });
         if(form_data.length==0){
            alert("Please enter required field")
            return;
         }
         if(cr!=dr){
            alert("Debit and credit amount should be same")
            return;
         }
         
         $("#cover-spin").show();
         $.ajax({
            url : "accountAjax.php",
            method : "post",
            data : 'action=account_journal&date='+$("#date").val()+"&req="+JSON.stringify(form_data)+"&json_status=<?php echo $json_status;?>&other_freight="+other_freight+"&order_no="+order_no+"&remark_text="+$("#remark_text").val(),
            success : function(res){
               if(res==1){
                  alert("Save Successfully");
                  window.location="?page=account-journal";
               }else{
                  alert("Something Went Wrong");
                  $("#cover-spin").hide();
               }
            }
         });
      });
      $(".edit_data").click(function(){
         var form_data = [];
         let dr = 0;
         let cr = 0;
         $(".type").each(function(){
            let id = $(this).attr('data-id');
            if($(this).val()!='' && $("#account_"+id).val()!=""){
               if($(this).val()=="Credit" && $("#credit_"+id).val()!=""){
                  form_data.push({"type":"Credit","credit":$("#credit_"+id).val(),"debit":0,"user_id":$("#account_"+id).val(),"user_type":$("#account_"+id+" option:selected").attr("data-user_type"),"godown":$("#account_"+id+" option:selected").attr("data-godown"),"salary_status":$("#salary_status_"+id).val()});
                  cr = parseFloat(cr) + parseFloat($("#credit_"+id).val());
               }else if($(this).val()=="Debit" && $("#debit_"+id).val()!=""){
                  form_data.push({"type":"Debit","credit":0,"debit":$("#debit_"+id).val(),"user_id":$("#account_"+id).val(),"user_type":$("#account_"+id+" option:selected").attr("data-user_type"),"godown":$("#account_"+id+" option:selected").attr("data-godown"),"salary_status":$("#salary_status_"+id).val()});
                  dr = parseFloat(dr) + parseFloat($("#debit_"+id).val());
               }               
            }
         });
         if(form_data.length==0){
            alert("Please enter required field")
            return;
         }
         if(cr!=dr){
            alert("Debit and credit amount should be same")
            return;
         }
         $("#cover-spin").show();
         $.ajax({
            url : "accountAjax.php",
            method : "post",
            data : 'action=edit_account_journal&date='+$("#date").val()+"&req="+JSON.stringify(form_data)+"&parent_id=<?php echo $_GET['id'];?>&remark_text="+$("#remark_text").val(),
            success : function(res){
               if(res==1){
                  alert("Update Successfully");
                  window.location="?page=account-journal";
               }else{
                  alert("Something Went Wrong");
                  $("#cover-spin").hide();
               }
            }
         });
      });
      $(document).on("keyup",".debit",function(){
         let id = $(this).attr('data-id');
         debitTotal();         
      });
      $(document).on("keyup",".credit",function(){
         creditTotal();         
      });
      if(eid!=""){
         debitTotal();
         creditTotal();
         let claim_gst = $('input[name="claim_gst"]:checked').val();
         $(".without_gst").hide();
         $(".with_gst").hide();
         if(claim_gst=="No"){
            $(".without_gst").show();
         }else{
            $(".with_gst").show();
         }
         account_gst = $("#vendor").select2().find(":selected").data("gst");
         $("#percentage").change();
         gstCalculation();
         
      }
   });
   function debitTotal(){
      let total_debit_amount = 0;
      $(".debit").each(function(){
         if($(this).val()!=''){
            total_debit_amount = parseFloat(total_debit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_debit").html(total_debit_amount);
   }
   function creditTotal(){
      let total_credit_amount = 0;
      $(".credit").each(function(){
         if($(this).val()!=''){
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount);
   }
   var add_more_count = '<?php echo $limit;?>';
   $(".add_more").click(function(){
      add_more_count++;
      var $curRow = $(this).closest('tr');
      $newRow = '<tr id="tr_'+add_more_count+'"><td><select class="form-control type" data-id="'+add_more_count+'" id="type_'+add_more_count+'"><option value="">Select Type</option><option value="Debit">Debit</option><option value="Credit">Credit</option></select></td><td><select class="form-control account select2" data-id="'+add_more_count+'" id="account_'+add_more_count+'"><option value="">Select Account</option><?php 
            $account_list = "";
            if(isset($_GET['showall']) && $_GET['showall']=="Yes"){
               record_set("party","select customer.id,gstA_name,customer.name,gstA,customer.email,gowdown_location,gowdown_location.location from customer inner join gowdown_location on customer.gowdown_location=gowdown_location.id where customer.status='1' and as_mill=0 and old_godown=0 order by gowdown_location");
            }else{
               record_set("party","select customer.id,gstA_name,customer.name,gstA,customer.email,gowdown_location,gowdown_location.location from customer inner join gowdown_location on customer.gowdown_location=gowdown_location.id where customer.status='1' and as_mill=0 and old_godown=0 and gowdown_location='".$_SESSION['user_godown']."'");
            }
            while($party_data = mysqli_fetch_array($party)){
               $account_list.='<option value="'.$party_data['email'].'" data-user_type="party" data-godown="'.$party_data['gowdown_location'].'">'.$party_data['gstA_name'].' ('.$party_data['location'].')</option>';
            }
            record_set("mill","select id,gst_name from manage_mill where status='1'");
            while($mill_data = mysqli_fetch_array($mill)){
               $account_list.='<option value="'.$mill_data['id'].'" data-user_type="mill" data-godown="">'.$mill_data['gst_name'].'</option>';
            }
            record_set("account","select id,account_name,godown from account where status='1' and (godown='".$_SESSION['user_godown']."' || account_for=1)");
            while($account_data = mysqli_fetch_array($account)){
               $account_list.='<option value="'.$account_data['id'].'" data-user_type="account" data-godown="'.$account_data['godown'].'">'.$account_data['account_name'].'</option>';
            }
         echo $account_list;
         $debit_dis = "disabled";
         $credit_dis = "disabled";?>
      </select></td><td><input type="text" name="" class="form-control debit" data-id="'+add_more_count+'" id="debit_'+add_more_count+'" placeholder="Enter Debit Amount" <?php echo $debit_dis;?> ></td><td><input type="text" name="" class="form-control credit" data-id="'+add_more_count+'" id="credit_'+add_more_count+'" placeholder="Enter Credit Amount" <?php echo $credit_dis;?>></td><td><button class="btn btn-danger btn-xs remove" data-id="'+add_more_count+'">Remove</button></td></tr>';
      $curRow.before($newRow);
      $("#account_"+add_more_count).select2();
   });
   var add_more_count_gst = '<?php echo $index_of;?>';
   $(".add_more_gst").click(function(){
      add_more_count_gst++;
      var $curRow = $(this).closest('tr');
      $newRow = '<tr id="row_'+add_more_count_gst+'"><td><select class="form-control item select2" id="item_'+add_more_count_gst+'" data-index="'+add_more_count_gst+'"><option value="">Select Item</option><?php record_set("account","select id,account_name from account where status='1' and (godown='".$_SESSION['user_godown']."' || account_for=1)");while($account_data = mysqli_fetch_array($account)){ ?><option value="<?php echo $account_data['id'];?>"><?php echo $account_data['account_name'];?></option><?php } ?></select></td><td><select class="form-control percentage" id="percentage_'+add_more_count_gst+'" data-index="'+add_more_count_gst+'" onchange="gstCalculation();"><option value="">Select GST(%)</option><option value="5">5%</option><option value="12">12%</option><option value="18">18%</option><option value="28">28%</option></select></td><td><input type="text" class="form-control amount" id="amount_'+add_more_count_gst+'" data-index="'+add_more_count_gst+'" onkeyup="gstCalculation();" placeholder="Amount"></td><td><button type="button" class="btn btn-danger btn-xs tbl_row" data-index="'+add_more_count_gst+'">Remove</button></td></tr>';
      $curRow.after($newRow);
      $("#item_"+add_more_count_gst).select2();
   });
   $(document).on("click",".tbl_row",function(){
      let id = $(this).attr('data-index');      
      $("#row_"+id).remove();
      gstCalculation();   
   });
   $(document).on("click",".remove",function(){
      let id = $(this).attr('data-id');      
      $("#tr_"+id).remove();      
   });
   $(".claim_gst").click(function(){
      let claim_gst = $('input[name="claim_gst"]:checked').val();
      $(".without_gst").hide();
      $(".with_gst").hide();
      if(claim_gst=="No"){
         $(".without_gst").show();
      }else{
         $(".with_gst").show();
      }
   });
   function gstCalculation(){
      let mgst = merchant_gst.substr(0,2);
      let agst = account_gst.substr(0,2);
      let net_total = 0;
      let total_cgst = 0;
      let total_sgst = 0;
      let total_igst = 0;
      $(".item").each(function(){
         if($(this).val()!=""){
            let id = $(this).attr('data-index');
            let percentage = $("#percentage_"+id).val();
            let amount = $("#amount_"+id).val();
            if(percentage!="" && amount!=""){
               let IGST = amount*percentage/100;
               let CGST = amount*(percentage/2)/100;
               let SGST = CGST;
               
               IGST = IGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               CGST = CGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               SGST = SGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0]; 
               total_cgst = parseFloat(total_cgst) + parseFloat(CGST);
               total_sgst = parseFloat(total_sgst) + parseFloat(SGST);
               total_igst = parseFloat(total_igst) + parseFloat(IGST);
               net_total = parseFloat(net_total) + parseFloat(amount);
            }                        
         }
      });
      $("#cgst").val("");
      $("#sgst").val("");
      $("#igst").val("");
      if(mgst==agst){
         $(".cgst_div").show();
         $(".sgst_div").show();
         $(".igst_div").hide();          
         $("#cgst").val(total_cgst);
         $("#sgst").val(total_sgst); 
      }else{
         $("#igst").val(total_igst);
         $(".cgst_div").hide();
         $(".sgst_div").hide();
         $(".igst_div").show();
      }
      $("#net_amount").val(net_total);
      let tamount = parseFloat(net_total) + parseFloat(total_igst);
      $("#total_amount").val(Math.round(tamount));
   }
   $("#vendor").change(function(){
      account_gst = $(this).select2().find(":selected").data("gst");
      gstCalculation();
   });
   $("#add_entry").click(function(){
      let vendor = $("#vendor").val();
      let net_amount = $("#net_amount").val();      
      let cgst = $("#cgst").val();
      let sgst = $("#sgst").val();
      let igst = $("#igst").val();
      let type = $("#vendor option:selected").attr("data-type");
      let total_amount = $("#total_amount").val();
      let item_arr = [];
      $(".item").each(function(){
         if($(this).val()!=""){
            let id = $(this).attr('data-index');
            let percentage = $("#percentage_"+id).val();
            let amount = $("#amount_"+id).val();
            if(percentage!="" && amount!=""){
               item_arr.push({'item':$(this).val(),'percentage':percentage,'amount':amount});
            }                        
         }
      });
      if(vendor=="" || net_amount=="" || total_amount=="" || item_arr.length==0){
         alert("All field required");
         return;
      }
      $("#cover-spin").show();
      $.ajax({
         url : "accountAjax.php",
         method : "post",
         data : 'action=account_journal_withgst&date='+$("#date").val()+"&vendor="+vendor+"&net_amount="+net_amount+"&total_amount="+total_amount+"&cgst="+cgst+"&sgst="+cgst+"&igst="+igst+"&remark="+$('#remark').val()+"&type="+type+"&eid="+eid+"&etype="+etype+"&item_arr="+JSON.stringify(item_arr),
         success : function(res){
            if(res==1){
               alert("Save Successfully");
               window.location = "?page=account-view-journal";
               
               $("#cover-spin").hide();
            }else{
               alert("Something Went Wrong");
               $("#cover-spin").hide();
            }
         }
      });
   });

   $(".show_account").click(function(){
      window.location= "?page=account-journal&showall="+$(this).val()
   });
</script>