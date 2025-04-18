import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class AccountGroupService {
  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   accountGroupList(){
    return this.http.post(this.url+'view-account-group','',this.con);
   }
   addAccountGroup(name: string, primary: string,heading: string,profile:string,login_user_id:string) {
      return this.http.post(this.url+'add-account-group', {name: name,primary_status: primary,heading: heading,profile: profile,created_by: login_user_id},this.con);
   }
   editAccountGroup(name: string, primary: string,heading: string,profile:string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-account-group', {name: name,primary_status: primary,heading: heading,profile: profile,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
