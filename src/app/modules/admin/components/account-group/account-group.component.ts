import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AccountGroupService} from '../../../../services/account-group.service';
@Component({
  selector: 'app-account-group',
  templateUrl: './account-group.component.html',
  styleUrls: ['./account-group.component.css']
})
export class AccountGroupComponent implements OnInit {
  form_title = "Add Merchant";
  user_detail:any = [];
  account_group_list = [];
  displayedColumns: string[] = ['name','primary','heading','profile','action'];
  constructor(private account_group:AccountGroupService) { }
  accountGroupForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    primary: new FormControl('',[Validators.required]),
    heading : new FormControl(''),
    profile : new FormControl(''), 
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getAccountGroup();
  }
  getAccountGroup(){
    this.account_group.accountGroupList().subscribe((data: any)=>{
    this.account_group_list = data.data;
    });
 }
 saveAccountGroup(){
  let name = this.accountGroupForm.value.name;
  let primary = this.accountGroupForm.value.primary;
  let heading = this.accountGroupForm.value.heading;
  let profile = this.accountGroupForm.value.profile; 
  let edit_id = this.accountGroupForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('user_details');
  this.user_detail = JSON.parse(this.user_detail); 
  let login_user_id = this.user_detail.id;
  console.log(edit_id)
if(edit_id=="" || edit_id==null){
  this.account_group.addAccountGroup(name,primary,heading,profile,login_user_id).subscribe((data: any) => {
    console.log(data) 
    if(data.success==true){
        alert(data.message);            
        this.getAccountGroup();
        this.accountGroupForm.reset();
     }else{
      console.log("ddd");
        alert("Something went wrong");
     }         
  });
}else{
  this.account_group.editAccountGroup(name,primary,heading,profile,login_user_id,edit_id).subscribe((data: any) => {
     if(data.success==true){
        alert(data.message);            
        this.getAccountGroup();
        this.accountGroupForm.reset();
     }else{
        alert("Something went wrong");
     }         
  });
}

}
get nameValidator(){
  return this.accountGroupForm.get('name');
}
get primaryValidator(){
  return this.accountGroupForm.get('primary');
}
editAccountGroup(editDetail:any){
  this.form_title = "Edit Account Group";
  this.accountGroupForm.patchValue({
    name: editDetail.name,
    primary: editDetail.primary_status,
    heading: editDetail.heading,
    profile: editDetail.profile,
    edit_id: editDetail.id
  });
}
}
