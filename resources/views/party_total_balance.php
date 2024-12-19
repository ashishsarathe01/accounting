<style type="text/css">
   @media print{
      .no_print{
         display:none;
      }
   }
   @media screen {
      #printSection {
         display: none;
      }
   }
   @media print {
      body * {
         visibility:hidden;
         width: 100%;
      }
      #printSection, #printSection * {
         visibility:visible;
      }
      #printSection {
         position:absolute;
         left:0;
         top:0;
      }
   }
   @media print {
      .modal {
         position: absolute;
         left: 0;
         top: 0;
         margin: 0;
         padding: 0;
         overflow: visible!important;
      }   
   } 
   @page { size: auto;  margin: 0mm; }
   table { page-break-inside:auto }
   tr  { page-break-inside:avoid; page-break-after:auto }
   thead { display:table-header-group }
   tfoot { display:table-footer-group }
</style>
<head>
   <meta charset = "utf-8">
   <title>jQuery UI Datepicker functionality</title>
</head>

<div class="row no_print">
   <div class="col-md-12">
      <div class="box box-primary">
         <div class="box-header with-border">
            <h3 class="box-title">Party Total Balance</h3>
            <a href="?page=match-busy-party-account" target="_blank"><button class="btn btn-info">Match Busy Data</button></a>
         </div>
         <div class="box-body">
            <div class="col-md-3">
               <div class="form-group">
                  <label for="exampleInputEmail1">Select Party</label>
                  <select name="customer" id="customer" class="form-control select2" required >
                     <option value="">Select  User</option>
                     <option value="all" <?php if($_GET['id']=='all'){ echo 'selected'; }?>>All</option>
                     <?php record_set("customer","select name,email,gstA_name,group_id from customer where gowdown_location='".$_SESSION['user_godown']."' and as_mill=0 and group_internal=0");
                     while($customerList=mysqli_fetch_array($customer)){?>
                     <option value="<?php echo $customerList['email']; ?>" <?php if($customerList['email']==$_GET['id']){ echo "selected"; }?>><?php echo $customerList['gstA_name'].' ('.$customerList['name'].')';?></option>
                              <?php }?>
                  </select>
               </div>
            </div>
            <?php if(isset($_GET['id']) && !empty($_GET['id'])){?>
            <div class="col-md-2">
               <label for="exampleInputEmail1">Select Date</label>
               <input type="date" name="search_date" id="search_date" class="form-control" style="width:80%;" value="<?php echo $_GET['search_date'];?>">
            </div>
            <div class="col-md-1">
               <label for="exampleInputEmail1">Filter</label>
               <select id="filter" class="form-control">
                  <option value="1" <?php if($_GET['filter']==1){ echo "selected";}?>>All</option>
                  <option value="2" <?php if($_GET['filter']==2){ echo "selected";}?>>All Include 0</option>
                  <option value="3" <?php if($_GET['filter']==3){ echo "selected";}?>>Main Account</option>
                  <option value="4" <?php if($_GET['filter']==4){ echo "selected";}?>>WT Account</option>
               </select>
            </div>
            <div class="col-md-2">
               <a href="party_total_balance_csv.php?id=<?php echo $_GET['id'];?>&search_date=<?php echo $_GET['search_date'];?>&filter=<?php echo $_GET['filter'];?>"><button type="button" class="btn btn-primary btn-xs" style="margin-top: 30px;">Download CSV</button></a>
            </div>
            
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<div class="box">
   <div class="box-header with-border">
      <h3 class="box-title">Balance</h3>
   </div>
   <div class="box-body" style="overflow:scroll">
      <a href="" id="pdf_link"></a>
      <table class="table table-bordered">
         <thead>
            <tr>
               <th style="width: 20%;">Party</th>
               <th style="text-align: right;width: 12%;">Due Amount</th>
               <?php 
               $old_godown_id = "";
               record_set("merge","select id,location from gowdown_location where merge_godown_id='".$_SESSION['user_godown']."'");
               while($merge_data = mysqli_fetch_array($merge)){
                  $old_godown_id = $merge_data['id'];
                  echo '<th style="text-align: right;width: 12%;">'.$merge_data['location'].' (OLD)</th>';
               }
               ?>
               <th style="text-align: right;width: 12%;">Main Amount</th>
               <th style="text-align: right;width: 12%;">WT Amount</th>
               <th style="text-align: right;width: 12%;">Total Amount</th>
               <th style="text-align: center;width: 7%;">Action</th>
            </tr>
         </thead>
         <tbody>
            <?php 
            $total_due_amount = 0;$total_main_amount = 0;$total_wt_amount = 0;$total_final_amount = 0;$total_main_old_amount = 0;
            if(isset($_GET['id']) && !empty($_GET['id'])){
               $global_users = [];
               $getQRTotalArr = [];
               $deal_status = 1;
               if($_GET['id']=='all'){
                  if($_GET['filter']==3){
                     record_set("user","select wt_transfer_amount,customer.id as uid,customer.email,customer.name,customer.due_days,customer.mobile,customer.gstA_name,customer.gstA_address,customer.gstA_city,customer.cust_limit,credit_days.days,whatsapp_msg_type,whatsapp_number,whatsapp_group,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where customer.gowdown_location='".$_SESSION['user_godown']."' and customer.allow_wt='0' and as_mill=0 and group_internal=0 order by customer.gstA_name");
                  }else if($_GET['filter']==4){
                     record_set("user","select wt_transfer_amount,customer.id as uid,customer.email,customer.name,customer.due_days,customer.mobile,customer.gstA_name,customer.gstA_address,customer.gstA_city,customer.cust_limit,credit_days.days,whatsapp_msg_type,whatsapp_number,whatsapp_group,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where customer.gowdown_location='".$_SESSION['user_godown']."' and customer.allow_wt='1' and as_mill=0 and group_internal=0 order by customer.gstA_name");
                  }else{
                     record_set("user","select wt_transfer_amount,customer.id as uid,customer.email,customer.name,customer.due_days,customer.mobile,customer.gstA_name,customer.gstA_address,customer.gstA_city,customer.cust_limit,credit_days.days,whatsapp_msg_type,whatsapp_number,whatsapp_group,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where customer.gowdown_location='".$_SESSION['user_godown']."' and as_mill=0 and group_internal=0 order by customer.gstA_name");
                  }
                  
               }else{
                  record_set("user","select wt_transfer_amount,customer.id as uid,customer.email,customer.name,customer.due_days,customer.mobile,customer.gstA_name,customer.gstA_address,customer.gstA_city,customer.cust_limit,credit_days.days,whatsapp_msg_type,whatsapp_number,whatsapp_group,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where customer.email='".$_GET['id']."' and as_mill=0 and group_internal=0");
               }
               $global_users = [];
               $search_date = date('Y-m-d');
               if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                  $search_date = date('Y-m-d',strtotime($_GET['search_date']));
               }
               while($getUserData = mysqli_fetch_array($user)){
                  if(!in_array($getUserData['email'],$global_users)){
                     $user_name_array = [];
                     $emails_arr = [];
                     $emails_old_arr = [];
                     $main_final_amount_arr = [];
                     $main_final_amount_old_arr = [];
                     $wt_final_amount_arr = [];
                     $wt_final_amount_old_arr = [];

                     $new_due_main_account = [];
                     $due_full_data = [];
                     record_set("extra_user","select customer.id as uid,name,gstA_name,email,due_days,days,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where mobile='".$getUserData['mobile']."' and gowdown_location='".$_SESSION['user_godown']."' and group_internal=0");
                     if(mysqli_num_rows($extra_user)>0){
                        while($extra_user_data = mysqli_fetch_array($extra_user)){
                           //Group Logic
                           if($extra_user_data['group_id']=="Yes"){
                              record_set("group_user","select name,gstA_name,email,due_days,days,group_id from customer inner join credit_days on customer.credit_date=credit_days.id where (group_id='".$extra_user_data['uid']."' || customer.id='".$extra_user_data['uid']."') and group_internal=0");
                              if(mysqli_num_rows($group_user)>0){
                                 $iii = 0;
                                 $group_main_final_amount_arr = [];
                                 $group_wt_final_amount_arr = [];
                                 $group_new_due_main_account = [];
                                 $group_due_full_data = [];
                                 while($group_user_data = mysqli_fetch_array($group_user)){
                                    array_push($global_users,$group_user_data['email']);
                                    if($iii==0){
                                       array_push($user_name_array,array("name"=>$group_user_data['name'],"gstA_name"=>$group_user_data['gstA_name'],"due_day"=>$group_user_data['due_days'],"credit_day"=>$group_user_data['days'],"email"=>$group_user_data['email']));
                                    }
                                    
                                    array_push($emails_arr,$group_user_data['email']);
                                    if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                                       record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$group_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                                       record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$group_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");
                                    }else{
                                       record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$group_user_data['email']."'");

                                       record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$group_user_data['email']."'");
                                    }                        
                                    $main_payment_final = mysqli_fetch_array($main_fpayment);
                                    array_push($group_main_final_amount_arr,$main_payment_final['sumdebit']-$main_payment_final['sumcredit']);
                                    
                                    $wt_payment_final = mysqli_fetch_array($wt_fpayment);
                                    array_push($group_wt_final_amount_arr,$wt_payment_final['sumdebit']-$wt_payment_final['sumcredit']);

                                    //new code start here for due*****************
                                    
                                    $finalTotalAmount = $main_payment_final['sumdebit']-$main_payment_final['sumcredit'];
                                    $creditDay = $group_user_data["due_days"];
                                    $dueDate = date('Y-m-d', strtotime($search_date. ' - '.$creditDay.' days'));

                                    record_set('main_due',"select debit as totalWithfreight from manage_payment where user_id='".$group_user_data['email']."' and status='1' and payment_date>'".$dueDate."' and remark!='Cheque Bounce' and order_id!=''");
                                    $amt = 0;
                                    if(mysqli_num_rows($main_due)>0){
                                       while($getAmount = mysqli_fetch_array($main_due)){
                                          $amt = $amt + $getAmount['totalWithfreight'];
                                       }
                                       $finalDueAmount = $finalTotalAmount-$amt;
                                    }else{
                                       $finalDueAmount=$finalTotalAmount-$amt;  
                                    }
                                    $wtfinalTotalAmount = $wt_payment_final['sumdebit']-$wt_payment_final['sumcredit'];
                                    $wtcreditDay = $group_user_data["due_days"];
                                    $wtdueDate = date('Y-m-d', strtotime($search_date. ' - '.$wtcreditDay.' days'));
                                    record_set("wt_due","select debit as totalWithfreight from wt_manage_payment where user_id='".$group_user_data['email']."' and status='1' and payment_date>'".$wtdueDate."' and order_id!=''");
                                    $wtamt = 0;
                                    if(mysqli_num_rows($wt_due)>0){
                                       while($wtgetAmount = mysqli_fetch_array($wt_due)){
                                          $wtamt = $wtamt+$wtgetAmount['totalWithfreight'];
                                       }
                                       $wtfinalDueAmount = $wtfinalTotalAmount - $wtamt;
                                    }else{
                                       $wtfinalDueAmount = $wtfinalTotalAmount-$wtamt;
                                    }
                                    //echo $wtfinalDueAmount;
                                    array_push($group_new_due_main_account,$finalDueAmount);
                                    array_push($group_new_due_main_account,$wtfinalDueAmount);
                                    array_push($due_full_data,array("amount"=>$finalDueAmount,'type'=>'M','email'=>$group_user_data['email']));
                                    array_push($due_full_data,array("amount"=>$wtfinalDueAmount,'type'=>'WT','email'=>$group_user_data['email']));
                                    $iii++;
                                 }
                                 array_push($main_final_amount_arr,array_sum($group_main_final_amount_arr));
                                 array_push($wt_final_amount_arr,array_sum($group_wt_final_amount_arr));
                                 array_push($new_due_main_account,array_sum($group_new_due_main_account));
                              }
                              //End Logic
                           }else if(empty($extra_user_data['group_id'])){
                              array_push($global_users,$extra_user_data['email']);
                              array_push($user_name_array,array("name"=>$extra_user_data['name'],"gstA_name"=>$extra_user_data['gstA_name'],"due_day"=>$extra_user_data['due_days'],"credit_day"=>$extra_user_data['days'],"email"=>$extra_user_data['email']));
                              array_push($emails_arr,$extra_user_data['email']);
                              if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                                 record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$extra_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                                 record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$extra_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");
                              }else{
                                 record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$extra_user_data['email']."'");

                                 record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$extra_user_data['email']."'");
                              }                        
                              $main_payment_final = mysqli_fetch_array($main_fpayment);
                              array_push($main_final_amount_arr,$main_payment_final['sumdebit']-$main_payment_final['sumcredit']);
                              
                              $wt_payment_final = mysqli_fetch_array($wt_fpayment);
                              array_push($wt_final_amount_arr,$wt_payment_final['sumdebit']-$wt_payment_final['sumcredit']);

                              //new code start here for due*****************
                              
                              $finalTotalAmount = $main_payment_final['sumdebit']-$main_payment_final['sumcredit'];
                              $creditDay = $extra_user_data["due_days"];
                              $dueDate = date('Y-m-d', strtotime($search_date. ' - '.$creditDay.' days'));

                              record_set('main_due',"select debit as totalWithfreight from manage_payment where user_id='".$extra_user_data['email']."' and status='1' and payment_date>'".$dueDate."' and remark!='Cheque Bounce' and order_id!=''");
                              $amt = 0;
                              if(mysqli_num_rows($main_due)>0){
                                 while($getAmount = mysqli_fetch_array($main_due)){
                                    $amt = $amt + $getAmount['totalWithfreight'];
                                 }
                                 $finalDueAmount = $finalTotalAmount-$amt;
                                 
                              }else{
                                 $finalDueAmount=$finalTotalAmount-$amt;
                              }
                              
                              /////////////

                              $wtfinalTotalAmount = $wt_payment_final['sumdebit']-$wt_payment_final['sumcredit'];
                              $wtcreditDay = $extra_user_data["due_days"];
                              $wtdueDate = date('Y-m-d', strtotime($search_date. ' - '.$wtcreditDay.' days'));
                              record_set("wt_due","select debit as totalWithfreight from wt_manage_payment where user_id='".$extra_user_data['email']."' and status='1' and payment_date>'".$wtdueDate."' and order_id!=''");
                              $wtamt = 0;
                              if(mysqli_num_rows($wt_due)>0){
                                 while($wtgetAmount = mysqli_fetch_array($wt_due)){
                                    $wtamt = $wtamt+$wtgetAmount['totalWithfreight'];
                                 }
                                 $wtfinalDueAmount = $wtfinalTotalAmount - $wtamt;
                                 
                              }else{
                                 $wtfinalDueAmount = $wtfinalTotalAmount-$wtamt;
                              }
                              //echo $wtfinalDueAmount;
                              array_push($new_due_main_account,$finalDueAmount);
                              array_push($new_due_main_account,$wtfinalDueAmount);
                              array_push($due_full_data,array("amount"=>$finalDueAmount,'type'=>'M','email'=>$extra_user_data['email']));
                              array_push($due_full_data,array("amount"=>$wtfinalDueAmount,'type'=>'WT','email'=>$extra_user_data['email']));
                           }                          
                        }
                     }
                     //OLD Godown Start

                     record_set("extra_user","select name,gstA_name,email,due_days,group_id from customer where mobile='".$getUserData['mobile']."' and gowdown_location='".$old_godown_id."' and group_internal=0");
                     if(mysqli_num_rows($extra_user)>0){
                        while($extra_user_data = mysqli_fetch_array($extra_user)){
                           array_push($emails_old_arr,$extra_user_data['email']);
                           if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                              record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$extra_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                              record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$extra_user_data['email']."' and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");
                           }else{
                              record_set("main_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from manage_payment where user_id ='".$extra_user_data['email']."'");

                              record_set("wt_fpayment","select sum(debit) as sumdebit,sum(credit) as sumcredit from wt_manage_payment where user_id ='".$extra_user_data['email']."'");
                           }                        
                           $main_payment_final = mysqli_fetch_array($main_fpayment);
                              array_push($main_final_amount_old_arr,$main_payment_final['sumdebit']-$main_payment_final['sumcredit']);
                           $wt_payment_final = mysqli_fetch_array($wt_fpayment);
                           array_push($wt_final_amount_old_arr,$wt_payment_final['sumdebit']-$wt_payment_final['sumcredit']);

                           //New code start here for old godown
                          
                           $finalTotalAmount = $main_payment_final['sumdebit']-$main_payment_final['sumcredit'];
                           $creditDay = $extra_user_data["due_days"];
                           $dueDate = date('Y-m-d', strtotime($search_date. ' - '.$creditDay.' days'));
                           
                           record_set('main_due',"select debit as totalWithfreight from manage_payment where user_id='".$extra_user_data['email']."' and status='1' and payment_date>'".$dueDate."' and remark!='Cheque Bounce' and order_id!=''");
                           $amt = 0;
                           if(mysqli_num_rows($main_due)>0){
                              while($getAmount = mysqli_fetch_array($main_due)){
                                 $amt = $amt + $getAmount['totalWithfreight'];
                              }
                              $finalDueAmount = $finalTotalAmount-$amt;
                              // if($finalDueAmount>0){
                              //    $finalDueAmount = $finalDueAmount;
                              // }else{
                              //    $finalDueAmount = 0;
                              // }
                           }else{
                              $finalDueAmount=$finalTotalAmount-$amt;
                              // if($finalDueAmount>0){
                              //    $finalDueAmount = $finalDueAmount;
                              // }else{
                              //    $finalDueAmount = 0;
                              // }
                           }
                           if($finalTotalAmount==0){
                              $finalDueAmount = 0;
                           }
                           /////////////

                           $wtfinalTotalAmount = $wt_payment_final['sumdebit']-$wt_payment_final['sumcredit'];
                           $wtcreditDay = $extra_user_data["due_days"];
                           $wtdueDate = date('Y-m-d', strtotime($search_date. ' - '.$wtcreditDay.' days'));
                           record_set("wt_due","select debit as totalWithfreight from wt_manage_payment where user_id='".$extra_user_data['email']."' and status='1' and payment_date>'".$wtdueDate."' and order_id!=''");
                           $wtamt = 0;
                           if(mysqli_num_rows($wt_due)>0){
                              while($wtgetAmount = mysqli_fetch_array($wt_due)){
                                 $wtamt = $wtamt+$wtgetAmount['totalWithfreight'];
                              }
                              $wtfinalDueAmount = $wtfinalTotalAmount - $wtamt;
                              // if($wtfinalDueAmount>0){
                              //    $wtfinalDueAmount = $wtfinalDueAmount;
                              // }else{
                              //    $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                              //    if($finalDueAmount==0){
                              //       //$finalDueAmount = $wt_balance;
                              //    }else{
                              //       $finalDueAmount = $finalDueAmount - $wt_balance;
                              //    }
                                 
                              //    $wtfinalDueAmount = 0;
                              // }
                           }else{
                              $wtfinalDueAmount = $wtfinalTotalAmount-$wtamt;
                              // if($wtfinalDueAmount>0){
                              //    $wtfinalDueAmount = $wtfinalDueAmount;
                              // }else{
                              //    $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                              //    if($finalDueAmount==0){
                              //       $finalDueAmount = $wt_balance;
                              //    }else{
                              //       $finalDueAmount = $finalDueAmount - $wt_balance;
                              //    }
                                 
                              //    $wtfinalDueAmount = 0;
                              // }
                           }

                           array_push($new_due_main_account,$finalDueAmount);
                           array_push($new_due_main_account,$wtfinalDueAmount);
                           array_push($due_full_data,array("amount"=>$finalDueAmount,'type'=>'OM','email'=>$extra_user_data['email']));
                           array_push($due_full_data,array("amount"=>$wtfinalDueAmount,'type'=>'OWT','email'=>$extra_user_data['email']));
                        }
                     }

                     //OLD Godown End
                     $old_user_ids = "'".implode("','",$emails_old_arr)."'";
                     $new_user_ids = "'".implode("','",$emails_arr)."'";
                     $emails_arr = array_merge($emails_arr,$emails_old_arr);
                     $user_ids = "'".implode("','",$emails_arr)."'";
                     if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                        record_set("main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$user_ids.") and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d') ");                     
                        record_set("wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$user_ids.")  and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                        record_set("check_main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$new_user_ids.") and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d') ");                     
                        record_set("check_wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$new_user_ids.")  and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                        record_set("check_main_payment_old","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$old_user_ids.") and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");

                        record_set("check_wt_payment_old","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$old_user_ids.") and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");
                     }else{
                        record_set("main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$user_ids.") ");
                     
                        record_set("wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$user_ids.")");
                        ///////////////////////////////////
                        record_set("check_main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$new_user_ids.") ");

                        record_set("check_wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$new_user_ids.")");

                        record_set("check_main_payment_old","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$old_user_ids.") ");

                        record_set("check_wt_payment_old","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$old_user_ids.")");
                     }
                     //Check For condition
                     $check_main_payment_data = mysqli_fetch_array($check_main_payment);
                     $checkfinalTotalAmount = $check_main_payment_data['debitAmount']-$check_main_payment_data['creditAmount'];

                     $check_main_payment_old_data = mysqli_fetch_array($check_main_payment_old);
                     $checkfinalTotalOLDAmount = $check_main_payment_old_data['debitAmount']-$check_main_payment_old_data['creditAmount'];

                     $check_wt_payment_data = mysqli_fetch_array($check_wt_payment);
                     $checkWTfinalTotalAmount = $check_wt_payment_data['debitAmount']-$check_wt_payment_data['creditAmount'];

                      $check_wt_payment_old_data = mysqli_fetch_array($check_wt_payment_old);
                     $checkWTfinalTotalOLDAmount = $check_wt_payment_old_data['debitAmount']-$check_wt_payment_old_data['creditAmount'];
                     if(count($emails_old_arr)==0){
                        $checkfinalTotalOLDAmount = 0;
                        $checkWTfinalTotalOLDAmount = 0;
                     }
                     //////////////////////////////////
                     //Main Account
                     $main_payment_data = mysqli_fetch_array($main_payment);
                     $finalTotalAmount = $main_payment_data['debitAmount']-$main_payment_data['creditAmount'];
                     $creditDay = $getUserData["due_days"];
                     $dueDate = date('Y-m-d', strtotime($search_date. ' - '.$creditDay.' days'));
                     record_set('main_due',"select debit as totalWithfreight from manage_payment where user_id in (".$user_ids.") and status='1' and payment_date>'".$dueDate."' and remark!='Cheque Bounce' and order_id!=''");
                     $amt = 0;
                     if(mysqli_num_rows($main_due)>0){
                        while($getAmount = mysqli_fetch_array($main_due)){
                           $amt = $amt + $getAmount['totalWithfreight'];
                        }
                        $finalDueAmount = $finalTotalAmount-$amt;
                        //echo $amt;
                        if($finalDueAmount>0){
                           $finalDueAmount = $finalDueAmount;
                        }else{
                           $finalDueAmount = 0;
                           
                        }
                     }else{
                        $finalDueAmount=$finalTotalAmount-$amt;
                        if($finalDueAmount>0){
                           $finalDueAmount = $finalDueAmount;
                        }else{
                           $finalDueAmount = 0;
                        }
                     }   
                         
                     //WT Account
                     $wt_payment_data = mysqli_fetch_array($wt_payment);
                     $wtfinalTotalAmount = $wt_payment_data['debitAmount']-$wt_payment_data['creditAmount'];
                     $wtcreditDay = $getUserData["due_days"];
                     $wtdueDate = date('Y-m-d', strtotime($search_date. ' - '.$wtcreditDay.' days'));
                     record_set("wt_due","select debit as totalWithfreight from wt_manage_payment where user_id in (".$user_ids.") and status='1' and payment_date>'".$wtdueDate."' and order_id!=''");
                     $wtamt = 0;
                     if(mysqli_num_rows($wt_due)>0){
                        while($wtgetAmount = mysqli_fetch_array($wt_due)){
                           $wtamt = $wtamt+$wtgetAmount['totalWithfreight'];
                        }                        
                        $wtfinalDueAmount = $wtfinalTotalAmount - $wtamt;
                        if($wtfinalDueAmount>0){
                           $wtfinalDueAmount = $wtfinalDueAmount;
                        }else{
                           $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                           if($finalDueAmount==0){
                              if($wtfinalTotalAmount>0){
                                 //$finalDueAmount = $wt_balance;
                              }                              
                           }else{
                              if($wtfinalTotalAmount<0){
                                 $finalDueAmount = $finalDueAmount - $wt_balance;
                              }                              
                           }
                           $wtfinalDueAmount = 0;
                        }                        
                     }else{
                        $wtfinalDueAmount = $wtfinalTotalAmount-$wtamt;
                        if($wtfinalDueAmount>0){
                           $wtfinalDueAmount = $wtfinalDueAmount;
                        }else{
                           $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                           if($finalDueAmount==0){
                              $finalDueAmount = $wt_balance;
                           }else{
                              $finalDueAmount = $finalDueAmount - $wt_balance;
                           }                           
                           $wtfinalDueAmount = 0;
                        }
                     }
                     //OLD Godown Due Code Start

                     // $user_ids = "'".implode("','",$emails_old_arr)."'";
                     // if(isset($_GET['search_date']) && !empty($_GET['search_date'])){
                     //    record_set("main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$user_ids.") and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d') ");                     
                     //    record_set("wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$user_ids.")  and STR_TO_DATE(payment_date, '%Y-%m-%d')<=STR_TO_DATE('".$_GET['search_date']."', '%Y-%m-%d')");
                     // }else{
                     //    record_set("main_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from manage_payment where user_id in (".$user_ids.") ");
                     
                     //    record_set("wt_payment","select sum(debit) as debitAmount,sum(credit) as creditAmount from wt_manage_payment where user_id in (".$user_ids.")");
                     // }
                     // //Main Account
                     // $main_payment_data = mysqli_fetch_array($main_payment);
                     // $finalTotalAmount = $main_payment_data['debitAmount']-$main_payment_data['creditAmount'];
                     // $creditDay = $getUserData["due_days"];
                     // $dueDate = date('Y-m-d', strtotime(date("Y-m-d"). ' - '.$creditDay.' days'));
                     // record_set('main_due',"select debit as totalWithfreight from manage_payment where user_id in (".$user_ids.") and status='1' and payment_date>'".$dueDate."' and remark!='Cheque Bounce'");
                     // $amt = 0;
                     // if(mysqli_num_rows($main_due)>0){
                     //    while($getAmount = mysqli_fetch_array($main_due)){
                     //       $amt = $amt + $getAmount['totalWithfreight'];
                     //    }
                     //    $finalDueAmount_old = $finalTotalAmount-$amt;
                     //    if($finalDueAmount_old>0){
                     //       $finalDueAmount_old = $finalDueAmount_old;
                     //    }else{
                     //       $finalDueAmount_old = 0;
                     //    }
                     // }else{
                     //    $finalDueAmount_old=$finalTotalAmount-$amt;
                     //    if($finalDueAmount_old>0){
                     //       $finalDueAmount_old = $finalDueAmount_old;
                     //    }else{
                     //       $finalDueAmount_old = 0;
                     //    }
                     // }   
                                  
                     // //WT Account
                     // $wt_payment_data = mysqli_fetch_array($wt_payment);
                     // $wtfinalTotalAmount = $wt_payment_data['debitAmount']-$wt_payment_data['creditAmount'];
                     // $wtcreditDay = $getUserData["due_days"];
                     // $wtdueDate = date('Y-m-d', strtotime(date("Y-m-d"). ' - '.$wtcreditDay.' days'));
                     // record_set("wt_due","select debit as totalWithfreight from wt_manage_payment where user_id in (".$user_ids.") and status='1' and payment_date>'".$wtdueDate."'");
                     // $wtamt = 0;
                     // if(mysqli_num_rows($wt_due)>0){

                     //    while($wtgetAmount = mysqli_fetch_array($wt_due)){
                     //       $wtamt = $wtamt+$wtgetAmount['totalWithfreight'];
                     //    }
                        
                     //    $wtfinalDueAmount_old = $wtfinalTotalAmount - $wtamt;
                     //    if($wtfinalDueAmount_old>0){
                     //       $wtfinalDueAmount_old = $wtfinalDueAmount_old;
                     //    }else{
                     //       $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                     //       if($finalDueAmount_old==0){
                     //          $finalDueAmount_old = $wt_balance;
                     //       }else{
                     //          $finalDueAmount_old = $finalDueAmount_old - $wt_balance;
                     //       }                           
                     //       $wtfinalDueAmount_old = 0;
                     //    }
                     // }else{
                     //    $wtfinalDueAmount_old = $wtfinalTotalAmount-$wtamt;
                     //    if($wtfinalDueAmount_old>0){
                     //       $wtfinalDueAmount_old = $wtfinalDueAmount_old;
                     //    }else{
                     //       $wt_balance = str_replace("-","",$wtfinalTotalAmount);
                     //       if($finalDueAmount_old==0){
                     //          $finalDueAmount_old = $wt_balance;
                     //       }else{
                     //          $finalDueAmount_old = $finalDueAmount_old - $wt_balance;
                     //       }
                           
                     //       $wtfinalDueAmount_old = 0;
                     //    }
                     // }
                     
                     // echo "<pre>";
                     //print_r($user_name_array);
                     // print_r($main_final_amount_old_arr);
                     // print_r($main_final_amount_arr);
                     // print_r($wt_final_amount_arr);
                     //OLD Godown Due Code End
                     if($checkfinalTotalAmount!=0 || $checkWTfinalTotalAmount!=0 || $checkfinalTotalOLDAmount!=0 || $checkWTfinalTotalOLDAmount!=0 || $_GET['filter']==2){                       
                  
                     //if($finalTotalAmount!=0 || $wtfinalTotalAmount!=0 || $_GET['filter']==2){
                        $display_final_amount = 0;?>               
                        <tr>
                           <td class="view" data-email='<?php echo $getUserData['email'];?>' data-name="<?php echo $getUserData['gstA_name'];?>" style="cursor:pointer;"><?php 
                           $vemail = "";
                           $vname = "";
                           $vgstname = "";
                           if(count($user_name_array)==0){
                              continue;
                           }
                                 foreach ($user_name_array as $h=>$uname) {
                                    if($main_final_amount_old_arr[$h]==""){
                                       $main_final_amount_old_arr[$h] = 0;
                                    }
                                    //echo $main_final_amount_arr[$h]."**".$wt_final_amount_arr[$h];
                                    if($main_final_amount_old_arr[$h]=="0" && $main_final_amount_arr[$h]=="0" && $wt_final_amount_arr[$h]=="0" && $wt_final_amount_old_arr[$h]=="0"){
                                       continue;
                                    }else{
                                       $vemail = $uname['email'];
                                       $vname = $uname['name'];
                                       $vgstname = $uname['gstA_name'];
                                       echo $uname['gstA_name']." (".$uname['name'].")(".$uname['credit_day']."/".$uname['due_day'].")<br>";
                                    }
                                    
                                 }
                              ?>                        
                           </td>
                           <td style="text-align: right;cursor:pointer;">
                              <?php 
                              
                              $finalDueAmount_old = 0;$wtfinalDueAmount_old = 0;
                              // echo '<a href="due-deatil.php?due-date='.$dueDate.'&due-amount='.$finalDueAmount.'&user-id='.$vemail.'" target="_blank">'.format_number($finalDueAmount + $wtfinalDueAmount+$finalDueAmount_old + $wtfinalDueAmount_old).'</a>';
                              // $total_due_amount = $total_due_amount + $finalDueAmount + $wtfinalDueAmount+$finalDueAmount_old + $wtfinalDueAmount_old;
                              if(array_sum($new_due_main_account)>0){
                                 $due_url = "due_deatil_new.php?due-date=".$dueDate."&main1_amount=".array_sum($new_due_main_account)."&main1_email=".$vemail;
                                 if(count($due_full_data)>0){
                                    $maniac = 0;
                                    $maniac1 = 0;
                                    $wtac = 0;
                                    $owtac = 0;
                                    $omaniac = 0;

                                    $main1acemail = "";
                                    $main1aceamt = "";
                                    $wt1acemail = "";
                                    $wt1aceamt = "";

                                    $omain1acemail = "";
                                    $omain1aceamt = "";
                                    $owt1acemail = "";
                                    $owt1aceamt = "";

                                    $main2acemail = "";
                                    $main2aceamt = "";
                                    $wt2acemail = "";
                                    $wt2aceamt = "";

                                    $omain2acemail = "";
                                    $omain2aceamt = "";
                                    $owt2acemail = "";
                                    $owt2aceamt = "";
                                    // echo "<pre>";
                                    // print_r($due_full_data);
                                    foreach ($due_full_data as $dk => $dv) {

                                       if($dv['amount']!=0){
                                          if($dv['type']=="M"){
                                             if($maniac == 0){
                                                $maniac = 1;
                                                $main1acemail = $dv['email'];
                                                $main1aceamt = $dv['amount'];
                                                continue;
                                             }
                                             if($maniac == 1){
                                                $main2acemail = $dv['email'];
                                                $main2aceamt = $dv['amount'];
                                                continue;
                                             }
                                             
                                          }
                                          if($dv['type']=="WT"){
                                             if($wtac == 0){
                                                $wtac = 1;
                                                $wt1acemail = $dv['email'];
                                                $wt1aceamt = $dv['amount'];
                                                continue;
                                             }
                                             if($wtac == 1){
                                                $wt2acemail = $dv['email'];
                                                $wt2aceamt = $dv['amount'];
                                                continue;
                                             }                                            
                                          }

                                          if($dv['type']=="OM"){
                                             if($omaniac == 0){
                                                $omaniac = 1;
                                                $omain1acemail = $dv['email'];
                                                $omain1aceamt = $dv['amount'];
                                                continue;
                                             }
                                             if($omaniac == 1){
                                                $omain2acemail = $dv['email'];
                                                $omain2aceamt = $dv['amount'];
                                                continue;
                                             }
                                             
                                          }
                                          if($dv['type']=="OWT"){
                                             if($owtac == 0){
                                                $owtac = 1;
                                                $owt1acemail = $dv['email'];
                                                $owt1aceamt = $dv['amount'];
                                                continue;
                                             }
                                             if($owtac == 1){
                                                $owt2acemail = $dv['email'];
                                                $owt2aceamt = $dv['amount'];
                                                continue;
                                             }                                            
                                          }
                                          // if($dv['amount']>0){
                                          //    if($dv['type']=="M"){
                                          //       $due_url = "due-deatil.php?due-date=".$dueDate."&due-amount=".array_sum($new_due_main_account)."&user-id=".$vemail;
                                          //       $maniac = 1;
                                          //       $maniac1 =$dv['amount'];
                                          //    }
                                          //    if($dv['type']=="WT"){
                                          //       if($maniac==1){
                                          //          $due_url = "due-deatil.php?due-date=".$dueDate."&due-amount=".$maniac1."&wt-due-amount=".$dv['amount']."&user-id=".$vemail;
                                          //       }else{
                                          //          $due_url = "due-deatil.php?due-date=".$dueDate."&wt-due-amount=".array_sum($new_due_main_account)."&user-id=".$vemail;
                                          //       }        
                                          //    }else if($dv['type']=="OM"){
                                          //       if($maniac==0){
                                          //       $due_url = "due-deatil.php?due-date=".$dueDate."&due-amount=".array_sum($new_due_main_account)."&user-id=01".$vemail;
                                          //       }
                                          //    }else if($dv['type']=="OWT"){
                                          //       if($maniac==0){
                                          //          $due_url = "due-deatil.php?due-date=".$dueDate."&wt-due-amount=".array_sum($new_due_main_account)."&user-id=01".$vemail;
                                          //       }
                                          //    }
                                          // }
                                       }
                                    }
                                    $assign_wt1 = 0;
                                    $assign_wt2 = 0;

                                    $assign_m1 = 0;
                                    $assign_m2 = 0;
                                    $url = "";

                                    if($main1aceamt>0){
                                       $assign_m1 = 1;
                                       if($wt1aceamt<0){
                                          $assign_wt1 = 1;
                                          $main1aceamt = $main1aceamt + $wt1aceamt;
                                       }
                                       if($main1aceamt>0){
                                          $url.= "&main1_amount=".$main1aceamt."&main1_email=".$main1acemail;
                                       }
                                    }

                                    if($main2aceamt>0){
                                       $assign_m2 = 0;
                                       if($wt2aceamt<0){
                                          $assign_wt2 = 1;
                                          $main2aceamt = $main2aceamt + $wt2aceamt;
                                       }
                                       if($assign_m1==0 && $main1aceamt<0){
                                          $assign_m1 = 1;
                                          $main2aceamt = $main2aceamt + $main1aceamt;
                                       }
                                       if($main2aceamt>0){
                                          $url.= "&main2_amount=".$main2aceamt."&main2_email=".$main2acemail;
                                       }
                                    }
                                    if($wt1aceamt>0){

                                       $url.= "&wt1_amount=".$wt1aceamt."&wt1_email=".$wt1acemail;
                                    }
                                    if($wt2aceamt>0){
                                       $url.= "&wt2_amount=".$wt2aceamt."&wt2_email=".$wt2acemail;
                                    }
                                    //old......
                                    if($omain1aceamt>0){
                                       if($owt1aceamt<0){
                                          $omain1aceamt = $omain1aceamt + $owt1aceamt;
                                       }
                                       if($assign_wt1==0 && $wt1aceamt<0){
                                          $omain1aceamt = $omain1aceamt + $wt1aceamt;
                                          $assign_wt1 = 1;
                                       }
                                       if($assign_wt2==0 && $wt2aceamt<0){
                                          $omain1aceamt = $omain1aceamt + $wt2aceamt;
                                          $assign_wt2 = 1;
                                       }
                                       if($omain2aceamt<0){
                                          $omain1aceamt = $omain1aceamt + $omain2aceamt;
                                       }
                                       if($omain1aceamt>0){
                                          $url.= "&omain1_amount=".$omain1aceamt."&omain1_email=".$omain1acemail;
                                       }
                                       
                                    }

                                    if($omain2aceamt>0){
                                       if($owt2aceamt<0){
                                          $omain2aceamt = $omain2aceamt + $owt2aceamt;
                                       }
                                       if($assign_wt1==0 && $wt1aceamt<0){
                                          $omain2aceamt = $omain2aceamt + $wt1aceamt;
                                          $assign_wt1 = 1;
                                       }
                                       if($assign_wt2==0 && $wt2aceamt<0){
                                          $omain2aceamt = $omain2aceamt + $wt2aceamt;
                                          $assign_wt2 = 1;
                                       }
                                       if($omain2aceamt>0){
                                          $url.= "&omain2_amount=".$omain2aceamt."&omain2_email=".$omain2acemail;
                                       }
                                    }
                                    if($owt1aceamt>0){
                                       $url.= "&owt1_amount=".$owt1aceamt."&owt1_email=".$owt1acemail;
                                    }
                                    if($owt2aceamt>0){
                                       $url.= "&owt2_amount=".$owt2aceamt."&owt2_email=".$owt2acemail;
                                    }

                                 }
                                 if(!empty($url)){
                                    $due_url = "due_deatil_new.php?due-date=".$dueDate.$url;
                                 }
                                 
                                 echo '<a href="'.$due_url.'" target="_blank">'.format_number(array_sum($new_due_main_account)).'</a><br>';
                                 
                                 
                              $total_due_amount = $total_due_amount + array_sum($new_due_main_account);
                              }else{
                                 echo "0.00";
                              }
                              
                              ?>
                           </td>
                           <?php 
                           record_set("merge","select id,location from gowdown_location where merge_godown_id='".$_SESSION['user_godown']."'");
                           while($merge_data = mysqli_fetch_array($merge)){?>
                              <td style="text-align: right;">
                                 <?php                                         
                                 foreach ($main_final_amount_old_arr as $h1=>$famount) {
                                    if($main_final_amount_old_arr[$h1]=="0" && $main_final_amount_arr[$h1]=="0" && $wt_final_amount_arr[$h1]=="0" && $wt_final_amount_old_arr[$h1]=="0"){

                                    }else{
                                       $display_final_amount = $display_final_amount + $famount;
                                       echo format_number($famount).'<br>';
                                       $total_main_old_amount = $total_main_old_amount + $famount;
                                    }
                                    
                                 }                     
                                 ?>
                              </td>
                              <?php 
                           }
                           ?>
                           <td style="text-align: right;cursor:pointer;" class="view" data-email='<?php echo $getUserData['email'];?>' data-name="<?php echo $getUserData['gstA_name'];?>">
                              <?php                                         
                              foreach ($main_final_amount_arr as $h2=>$famount) {

                                 if($main_final_amount_old_arr[$h2]=="0" && $main_final_amount_arr[$h2]=="0" && $wt_final_amount_arr[$h2]=="0" ){

                                 }else{
                                    $display_final_amount = $display_final_amount + $famount;
                                    echo format_number($famount).'<br>';
                                    $total_main_amount = $total_main_amount + $famount;
                                 }
                              }                     
                              ?>
                           </td>
                           <td style="text-align: right;">
                              <?php  
                              $merge_wt_amount = 0;                   
                              foreach ($wt_final_amount_arr as $k=>$wtfamount) {
                                 if($main_final_amount_old_arr[$k]=="0" && $main_final_amount_arr[$k]=="0" && $wt_final_amount_arr[$k]=="0" && $wt_final_amount_old_arr[$k]=="0"){

                                 }else{
                                    $display_final_amount = $display_final_amount + $wtfamount + $wt_final_amount_old_arr[$k];
                                    if($wtfamount+$wt_final_amount_old_arr[$k]!=0){
                                       echo '<span style="color:red;cursor:pointer;" class="view" data-email="'.$getUserData['email'].'" data-name="'.$getUserData['gstA_name'].'">'.format_number($wtfamount+$checkWTfinalTotalAmount[$k]+$wt_final_amount_old_arr[$k]).'</span><br>';
                                       $total_wt_amount = $total_wt_amount + $wtfamount +$wt_final_amount_old_arr[$k];
                                       if($_SESSION['user_godown']=="10" || $_SESSION['user_godown']=="11"){

                                          if($getUserData['wt_transfer_amount']==1){
                                             echo '<img src="image/whatsapp_double_click.jpg" style="width:30px;">';
                                          }else{
                                             echo '<button class="btn btn-info btn-xs wt_ok_btn" data-email="'.$emails_arr[$k].'" data-amount="'.$wtfamount.'" data-name="'.$getUserData['gstA_name'].'">OK</button><br>';
                                          }
                                       }
                                    }else{
                                       echo "<span style='color:black'>--</span><br>";
                                    }

                                 }                   
                              }
                                           
                              ?>
                           </td>
                           <td style="text-align: right;cursor:pointer;" class="view" data-email='<?php echo $getUserData['email'];?>' data-name="<?php echo $getUserData['gstA_name'];?>">
                              <?php                      
                              echo format_number($display_final_amount);
                              $total_final_amount = $total_final_amount + $display_final_amount;
                              ?>
                           </td>
                           <td style="text-align: center;">
                              <?php 
                              record_set("checkOk","select id from party_total_balance_ok_log where party='".$vemail."' and created_date like '%".date('m-Y')."%'");
                              if(mysqli_num_rows($checkOk)==0){
                                 record_set("ok_pri","select id from edit_delete_privilege where user_id='".$_SESSION['user_id']."' and privilege_name='ok_party_total_balance'");
                                 if(mysqli_num_rows($ok_pri)>0){ ?>
                                    <button type="button" class="btn btn-primary btn-xs verify_account no_print" data-email='<?php echo $vemail;?>' data-id="<?php echo $getUserData['uid'];?>" id="OK_<?php echo $getUserData['uid'];?>" data-amount='<?php echo $getUserData['email'];?>'>OK</button>
                                 <?php  }
                                    record_set("whatsapp_pri","select id from edit_delete_privilege where user_id='".$_SESSION['user_id']."' and privilege_name='whatsapp_party_total_balance'");
                                    if(mysqli_num_rows($whatsapp_pri)>0){?>
                                    <button style="display:none" id="whatsapp_<?php echo $getUserData['uid'];?>" class="btn btn-success btn-xs send_payment_statement" data-whatsapp_msg_type="<?php echo $getUserData['whatsapp_msg_type'];?>" data-whatsapp_number="<?php echo $getUserData['whatsapp_number'];?>" data-whatsapp_group="<?php echo $getUserData['whatsapp_group'];?>" data-gstA_name="<?php echo $vgstname;?>" data-name="<?php echo $vname;?>" data-user_id="<?php echo $vemail;?>" data-resend="0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"></path></svg>
                                    </button>

                                    <img src="image/whatsapp_double_click.jpg" style="width: 30px;display: none" id="dclick_<?php echo $getUserData['uid'];?>" >
                                 <?php  }
                              }else{
                                 record_set("checkWhtsapp","select id from party_total_balance_whatsapp_log where party='".$vemail."' and created_date like '%".date('m-Y')."%'");
                                 if(mysqli_num_rows($checkWhtsapp)==0){
                                     record_set("whatsapp_pri","select id from edit_delete_privilege where user_id='".$_SESSION['user_id']."' and privilege_name='whatsapp_party_total_balance'");
                                    if(mysqli_num_rows($whatsapp_pri)>0){?>
                                       <button id="whatsapp_<?php echo $getUserData['uid'];?>" class="btn btn-success btn-xs send_payment_statement" data-whatsapp_msg_type="<?php echo $getUserData['whatsapp_msg_type'];?>" data-whatsapp_number="<?php echo $getUserData['whatsapp_number'];?>" data-whatsapp_group="<?php echo $getUserData['whatsapp_group'];?>" data-gstA_name="<?php echo $vgstname;?>" data-name="<?php echo $vname;?>" data-user_id="<?php echo $vemail;?>" data-resend="0">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"></path></svg>
                                       </button>
                                       <img src="image/whatsapp_double_click.jpg" style="width: 30px;display: none" id="dclick_<?php echo $getUserData['uid'];?>" >
                                       <?php  
                                    }
                                 }else{ ?>
                                    <img src="image/whatsapp_double_click.jpg" style="width:30px;cursor: pointer;" class="send_payment_statement" data-whatsapp_msg_type="<?php echo $getUserData['whatsapp_msg_type'];?>" data-whatsapp_number="<?php echo $getUserData['whatsapp_number'];?>" data-whatsapp_group="<?php echo $getUserData['whatsapp_group'];?>" data-gstA_name="<?php echo $vgstname;?>" data-name="<?php echo $vname;?>" data-user_id="<?php echo $vemail;?>" data-resend="1"> 
                                    <?php 
                                 }
                              } ?>
                              <button type="button" class="btn btn-primary btn-xs log no_print" data-email='<?php echo $vemail;?>'>Log</button>
                           </td>
                        </tr>
                        <?php 
                     }
                  } 
               } 
            }?>
            <tr>
               <th>Total</th>
               <th style="text-align: right;"><?php echo format_number($total_due_amount); ?></th>
               <?php 
               record_set("merge","select id,location from gowdown_location where merge_godown_id='".$_SESSION['user_godown']."'");
               while($merge_data = mysqli_fetch_array($merge)){?>
                  <th style="text-align: right;"><?php echo format_number($total_main_old_amount); ?></th>
               <?php }
               ?>
               <th style="text-align: right;"><?php echo format_number($total_main_amount); ?></th>
               <th style="text-align: right;"><?php echo format_number($total_wt_amount); ?></th>
               <th style="text-align: right;"><?php echo format_number($total_final_amount); ?></th>
               <th></th>
            </tr>
         </tbody>
      </table>     
   </div>

</div><div id="printThis">
<div class="modal fade" id="myModal" role="dialog">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header no_print">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Statement</h4>
            <span><input type="text" name="filter_date" id="filter_date"></span>  <button type="button" class="btn btn-primary btn-xs" id="btnPrint">Print</button> <a href="" target="_blank" id="overall_url"><button type="button" class="btn btn-info btn-xs">OverAll</button></a> <input type="checkbox" value="main" class="pdfcheck">Main PDF <input type="checkbox" value="wt" class="pdfcheck">WT PDF <button class="btn btn-info btn-xs download_pdf_btn">Download Pdf</button>
            
         </div>
         <input type="hidden" name="hidden_id" id="hidden_id">
         <input type="hidden" name="hidden_name" id="hidden_name">
         <div class="modal-body first_table">
            
         </div>
         <div class="modal-footer no_print">
            <button type="button" class="btn btn-default no_print" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div></div>

<div id="logModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">OK Log</h4>
      </div>
      <div class="modal-body">
         <table class="table table-bordered ok_tbl">
            <thead>
               <tr>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Remark</th>
                  <th>By</th> 
               </tr>
            </thead>
            <tbody></tbody>
         </table>
      </div>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Whatsapp Log</h4>
      </div>
      <div class="modal-body">
         <table class="table table-bordered Whatsapp_tbl">
            <thead>
               <tr>
                  <th>From Date</th>
                  <th>To Date</th>
                  <th>Whatsapp By</th>
                  <th>Whatsapp Date</th>
               </tr>
            </thead>
            <tbody></tbody>
         </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<div id="verifyModal" class="modal fade" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Verify Account</h4>
         </div>
         <div class="modal-body">
            <table class="table table-bordered verify_tbl">
               <thead>
                  <tr>
                     <th>Date</th>
                     <th>Amount</th>
                     <th>Remark</th>
                     <th>By</th>                     
                  </tr>
               </thead>
               <tbody></tbody>
            </table>
            <div class="col-md-6">
               <div class="form-group">
                  <label for="email">Verify Amount:</label>
                  <input type="text" class="form-control" id="vamount" placeholder="Enter Verify Amount" readonly="">
               </div>
            </div>            
            <div class="col-md-6">
               <div class="form-group">
                  <label for="pwd">Verify Date:</label>
                  <?php 
                  $verDate = date("Y-m-d");
                  if(isset($_GET['search_date']) && !empty($_GET['search_date'])){ 
                     $verDate = $_GET['search_date'];
                     ?>
                     <?php 
                  } ?>
                  <input type="date" class="form-control" id="vdate" name="date" value="<?php echo $verDate;?>">
               </div>
            </div>
            <div class="col-md-12">
               <div class="form-group">
                  <label for="pwd">Remark:</label>
                  <input type="text" class="form-control" id="remark" placeholder="Enter Remark">
               </div>
            </div>
            <input type="hidden" id="verify_email">
            <input type="hidden" id="verify_row">
         </div>         
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="verifyAccountBtn">Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="okWtBalanceModal" role="dialog">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="modalTitle"></h4>
         </div>
         <div class="modal-body">
            <div class="form-group">
                <label for="wt_transfer_amount">Amount:</label>
                <input type="text" class="form-control" id="wt_transfer_amount" disabled>
            </div>
            <input type="hidden" id="transfer_wt_email">
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-info transferWtAmountbtn">Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
<div id="whatsappModal" class="modal fade" role="dialog">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Select Statement Date</h4>
         </div>
         <div class="modal-body">
            <input type="text" name="statement_date">
            <input type="hidden" id="st_type">
            <input type="hidden" id="st_mobile">
            <input type="hidden" id="st_group">
            <input type="hidden" id="st_user_id">
            <input type="hidden" id="st_gstA_name">
            <input type="hidden" id="st_name">
            <input type="hidden" id="st_uid">
            <input type="hidden" id="st_resend">
            <input type="hidden" id="st_from">
            <input type="hidden" id="st_to">
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-info send_statement">Send</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
<a href="" download="" id="send_payment_pdf_file" style="display: none">Send Mail</a>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
   var from_date = "";
   var to_date = "";
   var fdate = '<?php echo $_GET['from_date'];?>';
   var tdate = '<?php echo $_GET['to_date'];?>';
   var date_status = 0;
   $("#customer").change(function(){
      window.location = "?page=party-total-balance&id="+$(this).val();
   });
   $(".view").click(function(){
      date_status = 0;
      $("#cover-spin").show();
      $(".first_table").html('');
      var user_id = $(this).attr('data-email');
      var name = $(this).attr('data-name');
      $("#hidden_id").val(user_id);
      $("#hidden_name").val(name);
      //$('#filter_date').val('<?php echo date('m/');?>01/<?php echo date('Y');?>+ ' - ' +<?php echo date('m/d/Y');?>');
      from_date = '01-<?php echo date('m-Y');?>';
      to_date = '<?php echo date('d-m-Y');?>';
      $("#overall_url").attr('href',"?page=overall-party-statement&user_id="+user_id+"&from_date=<?php echo date('Y-m-');?>01&to_date=<?php echo date('Y-m-d');?>");
      $.ajax({
         url:'ajaxApi.php',
         method:'post',
         data:"action=get_total_balance_data&from_date="+from_date+"&to_date="+to_date+"&user_id="+user_id+"&name="+name,
         success:function(res){
            $("#cover-spin").hide();
            $(".first_table").html(res);
         }
      });
      $("#myModal").modal('toggle');

   });

   $('input[name="filter_date"]').daterangepicker({
      opens: 'left'
      }, function(start, end, label) {
         from_date = start.format('YYYY-MM-DD');
         to_date = end.format('YYYY-MM-DD');
         date_status = 1;
         $("#cover-spin").show();
         $("#overall_url").attr('href',"?page=overall-party-statement&user_id="+$("#hidden_id").val()+"&from_date="+from_date+"&to_date="+to_date);
         $.ajax({
            url:'ajaxApi.php',
            method:'post',
            data:"action=get_total_balance_data&from_date="+from_date+"&to_date="+to_date+"&user_id="+$("#hidden_id").val()+"&name="+$("#hidden_name").val(),
            success:function(res){
               $("#cover-spin").hide();
               $(".first_table").html(res);
            }
         });
   });

   $("#search_date").change(function(){
      if(fdate!='' && tdate!=''){
         window.location = "?page=party-total-balance&id=<?php echo $_GET['id'];?>&search_date="+$(this).val()+"&filter="+$("#filter").val()+"&from_date="+fdate+"&to_date="+tdate;
      }else{
         window.location = "?page=party-total-balance&id=<?php echo $_GET['id'];?>&search_date="+$(this).val()+"&filter="+$("#filter").val();
      }
      
   });
   $("#filter").change(function(){
      if(fdate!='' && tdate!=''){
         window.location = "?page=party-total-balance&id=<?php echo $_GET['id'];?>&search_date="+$("#search_date").val()+"&filter="+$("#filter").val()+"&from_date="+fdate+"&to_date="+tdate;
      }else{
         window.location = "?page=party-total-balance&id=<?php echo $_GET['id'];?>&search_date="+$("#search_date").val()+"&filter="+$("#filter").val();
      }
      
   });
   function print_po() {
      window.print();
   }
   // function myFunction() {
   //   window.print();
   // }
   document.getElementById("btnPrint").onclick = function () {
      printElement(document.getElementById("printThis"));
   }

   function printElement(elem) {
      var domClone = elem.cloneNode(true);       
      var $printSection = document.getElementById("printSection");       
      if (!$printSection) {
        var $printSection = document.createElement("div");
        $printSection.id = "printSection";
        document.body.appendChild($printSection);
      }

      $printSection.innerHTML = "";
      $printSection.appendChild(domClone);
      window.print();
      $printSection.remove();
      $("#myModal").css({"display": "block"});
   }
   function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("table tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) 
            row.push(cols[j].innerText.replace(",", ""));
        
        csv.push(row.join(","));        
    }

    // Download CSV file
    downloadCSV(csv.join("\n"), filename);
}
function downloadCSV(csv, filename) {
    var csvFile;
    var downloadLink;

    // CSV file
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Hide download link
    downloadLink.style.display = "none";

    // Add the link to DOM
    document.body.appendChild(downloadLink);

    // Click download link
    downloadLink.click();
}

$(".verify_account").click(function(){
   let email = $(this).attr('data-email');
   let id = $(this).attr('data-id');
   $("#verify_email").val(email);
   $("#verify_row").val(id);
   let search_date = "";
   <?php 
   if(isset($_GET['search_date']) && !empty($_GET['search_date'])){?>
      search_date = "<?php echo $_GET['search_date'];?>";
   <?php } ?>
   $("#cover-spin").show();
   $.ajax({
      url:"ajaxApi.php",
      method:'post',
      data:"action=partyVerifyData&user_id="+email+"&search_date="+search_date,
      success:function(resp){
         let obj = JSON.parse(resp);
         $(".verify_tbl tbody").html(obj.data);
         $("#vamount").val(obj.amount);
         $("#verifyModal").modal('toggle');
         $("#cover-spin").hide();
      }
   });   
});

$("#verifyAccountBtn").click(function(){
   let id = $("#verify_row").val();
   let email = $("#verify_email").val();
   let vamount = $("#vamount").val();
   let vdate = $("#vdate").val();
   let remark = $("#remark").val();
   $("#cover-spin").show();
   $.ajax({
      url : "ajaxApi.php",
      method :"post",
      data : "action=okPartyTotalBalance&email="+email+"&vamount="+vamount+"&vdate="+vdate+"&remark="+remark,
      success : function(res){
         if(res==1){
            alert("Successfully")
            $("#OK_"+id).hide();
            $("#whatsapp_"+id).show();
            $("#verifyModal").modal('toggle');
            $("#cover-spin").hide();
         }
      }
   });
});

$(".send_payment_statement").click(function(){   
   
   var type = $(this).attr("data-whatsapp_msg_type");
   var mobile = $(this).attr("data-whatsapp_number");
   var group = $(this).attr("data-whatsapp_group");
   var user_id = $(this).attr("data-user_id");
   var gstA_name = $(this).attr("data-gstA_name");
   var name = $(this).attr("data-name");
   var uid = $(this).attr("data-uid");
   var resend = $(this).attr("data-resend");
   if(type=="" || mobile==""){
      alert("Please add party whatsapp detail");
      return;
   }
   $("#st_type").val(type);
   $("#st_mobile").val(mobile);
   $("#st_group").val(group);
   $("#st_user_id").val(user_id);
   $("#st_gstA_name").val(gstA_name);
   $("#st_name").val(name);
   $("#st_uid").val(uid);
   $("#st_resend").val(resend);
   $("#whatsappModal").modal('toggle');
   return;
});
$(".send_statement").click(function(){   
   let type = $("#st_type").val();
   let mobile = $("#st_mobile").val();
   let group = $("#st_group").val();
   let user_id = $("#st_user_id").val();
   let gstA_name = $("#st_gstA_name").val();
   let name = $("#st_name").val();
   let uid = $("#st_uid").val();
   let resend = $("#st_resend").val();
   let st_from  = $("#st_from").val();;
   let st_to = $("#st_to").val();;
   if(type=="" || mobile==""){
      alert("Please add party whatsapp detail");
      return;
   }
   if(st_from=="" || st_to==""){
      alert("Please select date for statement");
      return;
   }
   // if(user_id=='ANK@GMAIL.COM'){
   //    mobile = 7503328776;
   // }
   $("#send_payment_pdf_file").attr("href","TCPDF/examples/payment_pdf.php?id="+user_id+"&from_date="+st_from+"&to_date="+st_to)
   document.getElementById('send_payment_pdf_file').click();
   $("#cover-spin").show();
   setTimeout(function(){
      $.ajax({
         url:"ajaxApi.php",
         method:'post',
         data:"action=send_whatsapp_statement&user_id="+user_id+"&type="+type+"&mobile="+mobile+"&group="+group+'&purpose=receipt&gstA_name='+gstA_name+"&name="+name+"&from_date="+st_from+"&to_date="+st_to,
         success:function(resp){
            let e = JSON.parse(resp);
            if(e.status==1){
               if(resend==0){
                  $("#dclick_"+uid).show();
                  $("#whatsapp_"+uid).hide();
               }else{
                  alert("Sent Successfully")
               }
               $("#cover-spin").hide();  
               $("#whatsappModal").modal('toggle');             
            }else{
               $("#cover-spin").hide();              
            }
         }
      });
   }, 10000);
});
$('input[name="statement_date"]').daterangepicker({
      opens: 'left',
      locale: {
         format: 'DD-MM-YYYY'
      },              
      }, function(start, end, label) {
         from_date = start.format('DD-MM-YYYY');
         to_date = end.format('DD-MM-YYYY');
         $("#st_from").val(from_date);
         $("#st_to").val(to_date);
         //$("#cover-spin").show();
         //window.location = "?page=party-total-balance&id=<?php echo $_GET['id'];?>&search_date="+$("#search_date").val()+"&filter="+$("#filter").val()+"&from_date="+from_date+"&to_date="+to_date;
         
   });
$(".log").click(function(){
   let email = $(this).attr("data-email");
   $("#cover-spin").show();
   $.ajax({
         url:"ajaxApi.php",
         method:'post',
         data:"action=partyTotalBalanceLog&user_id="+email,
         success:function(resp){
            let obj = JSON.parse(resp);
            $(".ok_tbl tbody").html(obj.okDetail);
            $(".Whatsapp_tbl tbody").html(obj.whatsappDetail);
            $("#logModal").modal('toggle');
            $("#cover-spin").hide();
         }
      });
});

$(".wt_ok_btn").click(function(){
   let email = $(this).attr('data-email');
   let amount = $(this).attr('data-amount');
   if(email=="" || amount==""){
      return;
   }
   $("#modalTitle").html("Transfer WT Amount ("+$(this).attr('data-name')+")");
   $("#wt_transfer_amount").val(amount);
   $("#transfer_wt_email").val(email);
   $("#okWtBalanceModal").modal('toggle');
});
$(".transferWtAmountbtn").click(function(){
   let transfer_wt_email = $("#transfer_wt_email").val();
   let wt_transfer_amount = $("#wt_transfer_amount").val();
   $("#cover-spin").show();
   $.ajax({
      url : "ajaxApi.php",
      method : "post",
      data : "action=transferWtAmount&transfer_wt_email="+transfer_wt_email+"&wt_transfer_amount="+wt_transfer_amount,
      success : function(res){
         if(res==1){
            alert("Updated");
            location.reload();
         }
      }
   });
});

$(".download_pdf_btn").click(function(){
   let filter_date = $("#filter_date").val();
   const myArray = filter_date.split(" - ");   
   let wt = 0;let main = 0;
   $(".pdfcheck").each(function(){
      if($(this).prop('checked')==true){
         if($(this).val()=="main"){
            main = 1;
         }else if($(this).val()=="wt"){
            wt = 1;
         }
      }
   });
   if(wt==0 && main==0){
      alert("Please Check option for pdf");
      return;
   }
   if(date_status==0){
      myArray[0] = "<?php echo '01'.date('-m-Y');?>";
      myArray[1] = "<?php echo date('d-m-Y');?>";
   }
   if(wt==1 && main==1){
   
      window.location = "TCPDF/examples/party_combine_payment.php?id="+$("#hidden_id").val()+"&from_date="+myArray[0]+"&to_date="+myArray[1];      
   }else if(wt==1){
      window.location = "TCPDF/examples/wt_payment_pdf.php?id="+$("#hidden_id").val()+"&from_date="+myArray[0]+"&to_date="+myArray[1];
   }else if(main==1){
      window.location = "TCPDF/examples/payment_pdf.php?id="+$("#hidden_id").val()+"&from_date="+myArray[0]+"&to_date="+myArray[1];
   } 
});
</script> 