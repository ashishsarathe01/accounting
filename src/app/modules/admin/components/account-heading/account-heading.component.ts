import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AccountHeadingService} from '../../../../services/account-heading.service';
@Component({
  selector: 'app-account-heading',
  templateUrl: './account-heading.component.html',
  styleUrls: ['./account-heading.component.css']
})
export class AccountHeadingComponent implements OnInit {
  form_title = "Add Account Heading";
  user_detail:any = [];
  account_heading_list = [];
  displayedColumns: string[] = ['name','profile','action'];
  constructor(private accounting_heading:AccountHeadingService) { }
  accountHeadingForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    profile: new FormControl('',[Validators.required]),  
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getAccoiuntHeading();
  }
  getAccoiuntHeading(){
    this.accounting_heading.accountHeadingList().subscribe((data: any)=>{
    this.account_heading_list = data.data;
    });
 }
 saveAccountHeading(){
  let name = this.accountHeadingForm.value.name;
  let profile = this.accountHeadingForm.value.profile;
  let edit_id = this.accountHeadingForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('user_details');
  this.user_detail = JSON.parse(this.user_detail); 
  let login_user_id = this.user_detail.id;
if(edit_id==""){
  this.accounting_heading.addAccountHeading(name,profile,login_user_id).subscribe((data: any) => {
    console.log(data) 
    if(data.success==true){
        alert(data.message);            
        this.getAccoiuntHeading();
        this.accountHeadingForm.reset();
     }else{
      console.log("ddd");
        alert("Something went wrong");
     }         
  });
}else{
  this.accounting_heading.editAccountHeading(name,profile,login_user_id,edit_id).subscribe((data: any) => {
     if(data.success==true){
        alert(data.message);            
        this.getAccoiuntHeading();
        
     }else{
        alert("Something went wrong");
     }         
  });
}

}
get nameValidator(){
return this.accountHeadingForm.get('name');
}
get profileValidator(){
return this.accountHeadingForm.get('profile');
}
editAccountHeading(editDetail:any){
  this.form_title = "Edit Merchant";
  this.accountHeadingForm.patchValue({
    name: editDetail.name,
    profile: editDetail.profile,
    email: editDetail.email,
    
    edit_id: editDetail.id
  });
}
}

