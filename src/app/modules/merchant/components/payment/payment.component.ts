import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AccountService} from '../../../../services/account.service';
import {PaymentService} from '../../../../services/payment.service';
import { DatePipe } from '@angular/common';
@Component({
  selector: 'app-payment',
  templateUrl: './payment.component.html',
  styleUrls: ['./payment.component.css']
})
export class PaymentComponent implements OnInit {
  current_date:any = "";
  account_list = [];
  bank_list = [];
  group_id = "";
  user_detail:any = [];
  constructor(public datepipe: DatePipe,private payment: PaymentService,private account: AccountService) { 
    let currentDateTime = this.datepipe.transform((new Date), 'yyyy-MM-dd');
    this.current_date = currentDateTime;
  }

  ngOnInit(): void {
    this.getDebtorsAccount(3);
   this.getBankAccount(1);
  }
  getDebtorsAccount(group_id:any){
    this.account.accountByGroup(group_id).subscribe((data: any)=>{
    this.account_list = data.data;
    });
 }
 getBankAccount(group_id:any){
  this.account.accountByGroup(group_id).subscribe((data: any)=>{
  this.bank_list = data.data;
  });
}
paymentForm = new FormGroup({
  date: new FormControl('',[Validators.required]),
  type1: new FormControl('',[Validators.required]),
  account1: new FormControl(''),
  debit1: new FormControl(''),
  mode1: new FormControl(''),
  credit1: new FormControl(''),
  remark1: new FormControl(''),
  type2: new FormControl('',[Validators.required]),
  account2: new FormControl(''),
  debit2: new FormControl(''),
  mode2: new FormControl(''),
  credit2: new FormControl(''),
  remark2: new FormControl(''),
  edit_id : new FormControl(''),
});

savePayment(){
  let date = this.paymentForm.value.date;
  let type1 = this.paymentForm.value.type1;
  let account1 = this.paymentForm.value.account1;
  let debit1 = this.paymentForm.value.debit1;
  let mode1 = this.paymentForm.value.mode1;
  let credit1 = this.paymentForm.value.credit1;
  let remark1 = this.paymentForm.value.remark1;
  let type2 = this.paymentForm.value.type2;
  let account2 = this.paymentForm.value.account2;
  let debit2 = this.paymentForm.value.debit2;
  let mode2 = this.paymentForm.value.mode2;
  let credit2 = this.paymentForm.value.credit2;
  let remark2 = this.paymentForm.value.remark2;
  let edit_id = this.paymentForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  let reqdata:any = [];
  let amount1;
  if(type1=="DEBIT"){
    amount1 = debit1;
  }else{
    amount1 = credit1;
  }
  let amount2;
  if(type2=="DEBIT"){
    amount2 = debit2;
  }else{
    amount2 = credit2;
  }
  reqdata = [
    {
      "type":type1,
      "account_id":account1,
      "amount":amount1,
      "mode":mode1,
      "remark":remark1
    },
    {
      "type":type2,
      "account_id":account2,
      "amount":amount2,
      "mode":mode2,
      "remark":remark2
    }
  ];
  if(edit_id==""){
    this.payment.addPayment(date,reqdata,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          //this.getAccount();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    
 }  
}
}
