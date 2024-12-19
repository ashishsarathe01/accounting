import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class LedgerService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   viewLedger(account_id: number){
    return this.http.post(this.url+'view-ledger',{account_id: account_id},this.con);
   }
   editLedger(txn_date: string,debit: string,credit: string,mode: string,remark: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-ledger', {txn_date: txn_date,debit:debit,credit:credit,mode:mode,remark:remark,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   editMerchant(name: string, email: string,mobile: number,GSTIN: string,gst_name: string,address: string,state: string,pincode: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-merchant', {name: name,email: email,mobile: mobile,GSTIN: GSTIN,gst_name: gst_name,address: address,state: state,pincode: pincode,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
