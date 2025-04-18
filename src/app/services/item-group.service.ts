import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class ItemGroupService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   itemGroupList(){
    return this.http.post(this.url+'view-item-group','',this.con);
   }
   addItemGroup(name: string,login_user_id:string) {
      return this.http.post(this.url+'add-item-group', {name: name,created_by: login_user_id},this.con);
   }
   editItemGroup(name: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-item-group', {name: name,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
