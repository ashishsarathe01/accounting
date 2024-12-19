import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {BankService} from '../../../../services/bank.service';
@Component({
  selector: 'app-bank',
  templateUrl: './bank.component.html',
  styleUrls: ['./bank.component.css']
})
export class BankComponent implements OnInit {

  constructor(private bank:BankService) { }
  form_title = "Add Bank";
  user_detail:any = [];
  bank_list = []; 
  displayedColumns: string[] = ['name','account_number','ifsc_code','action'];
  bankForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    account_number: new FormControl('',[Validators.required]),
    ifsc_code: new FormControl('',[Validators.required]),
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getBank();
  }
  getBank(){
    this.bank.bankList().subscribe((data: any)=>{
    this.bank_list = data.data;
    });
 }
 saveBank(){
  let name = this.bankForm.value.name;
  let account_number = this.bankForm.value.account_number;
  let ifsc_code = this.bankForm.value.ifsc_code;  
  let edit_id = this.bankForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  if(edit_id==""){
    this.bank.addBank(name,account_number,ifsc_code,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getBank();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    this.bank.editBank(name,account_number,ifsc_code,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getBank();
       }else{
          alert("Something went wrong");
       }         
    });
 }
  
}
get nameValidator(){
  return this.bankForm.get('name');
}
get accountNumberValidator(){
  return this.bankForm.get('account_number');
}
get ifscCodeValidator(){
  return this.bankForm.get('ifsc_code');
}
editBankDetail(editDetail:any){
  this.form_title = "Edit Bank";
  this.bankForm.patchValue({
    name: editDetail.name,
    account_number: editDetail.account_number,
    ifsc_code: editDetail.ifsc_code,
    edit_id: editDetail.id
  });
}
}
