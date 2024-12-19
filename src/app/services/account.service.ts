import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class AccountService {
  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   accountList(){
    return this.http.post(this.url+'view-account','',this.con);
   }
   addAccount(name: string,under_group: string,opening_balance: string,balance_type: string,login_user_id:string) {
      return this.http.post(this.url+'add-account', {name: name,under_group: under_group,opening_balance:opening_balance,balance_type:balance_type,created_by: login_user_id},this.con);
   }
   editAccount(name: string,under_group: string,opening_balance: string,balance_type: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-account', {name: name,under_group: under_group,opening_balance:opening_balance,balance_type:balance_type,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   accountByGroup(group_id: string) {
      return this.http.post(this.url+'account-by-group', {group_id: group_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
  
}
