import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class ReceiptService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   merchantList(){
    return this.http.post(this.url+'view-merchant','',this.con);
   }
   addReceipt(date: string,data:String,login_user_id:string) {
      return this.http.post(this.url+'add-receipt', {date: date,data:data,created_by: login_user_id},this.con);
   }
   editMerchant(name: string, email: string,mobile: number,GSTIN: string,gst_name: string,address: string,state: string,pincode: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-merchant', {name: name,email: email,mobile: mobile,GSTIN: GSTIN,gst_name: gst_name,address: address,state: state,pincode: pincode,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
