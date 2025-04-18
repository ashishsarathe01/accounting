import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class UnitService {
  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   unitList(){
    return this.http.post(this.url+'unit-list','',this.con);
   }
   addUnit(name: string, short_name: string,status: number,login_user_id:string) {
      return this.http.post(this.url+'add-unit', {name: name,short_name: short_name,status: status,created_by: login_user_id},this.con);
   }
   editUnit(name: string, short_name: string,status: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-unit', {name: name,short_name: short_name,status: status,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteProfile(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
  
}
