import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AccountService} from '../../../../services/account.service';
import {AccountGroupService} from '../../../../services/account-group.service';
@Component({
  selector: 'app-account',
  templateUrl: './account.component.html',
  styleUrls: ['./account.component.css']
})
export class AccountComponent implements OnInit {

  constructor(private account:AccountService,private account_group:AccountGroupService) { }
  form_title = "Add Account";
  user_detail:any = [];
  account_list = [];
  account_group_list = [];
  displayedColumns: string[] = ['name','under_group','action'];
  accountForm = new FormGroup({
      name: new FormControl('',[Validators.required]),
      under_group: new FormControl('',[Validators.required]),
      opening_balance: new FormControl(''),
      balance_type: new FormControl(''),
      edit_id : new FormControl(''),
  });
  ngOnInit(): void {
   this.getAccount();
    this.getGroup();
  }
  getAccount(){
    this.account.accountList().subscribe((data: any)=>{
    this.account_list = data.data;
    });
 }
 getGroup(){
  this.account_group.accountGroupList().subscribe((data: any)=>{
    this.account_group_list = data.data;
  });
}
 saveAccount(){
  let name = this.accountForm.value.name;
  let under_group = this.accountForm.value.under_group;
  let opening_balance = this.accountForm.value.opening_balance;
  let balance_type = this.accountForm.value.balance_type;
  let edit_id = this.accountForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  if(edit_id==""){
    this.account.addAccount(name,under_group,opening_balance,balance_type,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getAccount();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    this.account.editAccount(name,under_group,opening_balance,balance_type,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getAccount();
       }else{
          alert("Something went wrong");
       }         
    });
 }  
}
get nameValidator(){
  return this.accountForm.get('name');
}
get underGroupValidator(){
  return this.accountForm.get('under_group');
}
editAccountDetail(editDetail:any){
  this.form_title = "Edit Account";
  this.accountForm.patchValue({
    name: editDetail.name,
    under_group: editDetail.under_group,
    opening_balance: editDetail.opening_balance,
    balance_type: editDetail.balance_type,
    edit_id: editDetail.id
  });
}
}
