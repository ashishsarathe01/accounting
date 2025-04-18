import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AccountService} from '../../../../services/account.service';
import {LedgerService} from '../../../../services/ledger.service';
@Component({
  selector: 'app-ledger',
  templateUrl: './ledger.component.html',
  styleUrls: ['./ledger.component.css']
})
export class LedgerComponent implements OnInit {
  account_list = [];
  ledger_list = [];
  user_detail:any = [];
  selectedDevice:any;
  balance:string = "1000";
  displayStyle = "none";
  constructor(private account:AccountService,private ledger:LedgerService) { }
  ledgerForm = new FormGroup({
    edit_id: new FormControl('',[Validators.required]),
    txn_date: new FormControl('',[Validators.required]),
    debit: new FormControl(''),
    credit: new FormControl(''),
    mode: new FormControl('',[Validators.required]),
    remark: new FormControl(''),
    account_id: new FormControl(''),
});
displayedColumns: string[] = ['date','debit','credit','balance','mode','remark','action'];
  ngOnInit(): void {
    this.getAccount();
    this.selectedDevice="";
  }
  getAccount(){
    this.account.accountList().subscribe((data: any)=>{
    this.account_list = data.data;
    });
 }
 getAccountLedger(id:number){
  let aid = id;
  this.ledger.viewLedger(aid).subscribe((data: any)=>{
  this.ledger_list = data.data;
  });
}
openPopup() {
  this.displayStyle = "block";
}
closePopup() {
  this.displayStyle = "none";
}
 getAccountData(e:any){
  this.getAccountLedger(e);
 }
 editLedger(editDetail:any){
  this.ledgerForm.patchValue({
    txn_date: editDetail.txn_date,
    debit: editDetail.debit,
    credit: editDetail.credit,
    mode: editDetail.mode,
    remark: editDetail.remark,
    edit_id: editDetail.id,
    account_id: editDetail.account_id,
  });
  this.openPopup();
}
updateLedger(){
  let txn_date = this.ledgerForm.value.txn_date;
  let account_id = this.ledgerForm.value.account_id;
  let debit = this.ledgerForm.value.debit;
  let credit = this.ledgerForm.value.credit;
  let mode = this.ledgerForm.value.mode;
  let remark = this.ledgerForm.value.remark;
  let edit_id = this.ledgerForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  this.ledger.editLedger(txn_date,debit,credit,mode,remark,login_user_id,edit_id).subscribe((data: any) => {
    if(data.success==true){
       alert(data.message);        
       this.getAccountLedger(account_id)
       this.closePopup();
    }else{
       alert("Something went wrong");
    }         
 });
}
}
