import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class BankService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   bankList(){
    return this.http.post(this.url+'view-bank','',this.con);
   }
   addBank(name: string,account_number: string,ifsc_code: string,login_user_id:string) {
      return this.http.post(this.url+'add-bank', {name: name,account_number: account_number,ifsc_code:ifsc_code,created_by: login_user_id},this.con);
   }
   editBank(name: string,account_number: string,ifsc_code: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-bank', {name: name,account_number: account_number,ifsc_code:ifsc_code,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
