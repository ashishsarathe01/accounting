import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class AccountHeadingService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   accountHeadingList(){
    return this.http.post(this.url+'view-account-heading','',this.con);
   }
   addAccountHeading(name: string, profile: string,login_user_id:string) {
      return this.http.post(this.url+'add-account-heading', {name: name,profile: profile,created_by: login_user_id},this.con);
   }
   editAccountHeading(name: string, profile: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-account-heading', {name: name,profile: profile,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
