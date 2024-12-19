import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class EmployeeService {
  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) { 
this.url = configuration.server;
this.con = configuration.httpOptions;
  }

  empList(){
   return this.http.post(this.url+'employee-list','',this.con);
  }
  addEmployee(email: string, mobile: string,name: string,login_user_id:string) {
     return this.http.post(this.url+'admin-signup', {name: name,email: email,mobile: mobile,created_by: login_user_id},this.con);
  }
  editEmp(email: string, mobile: string,name: string,login_user_id:string,edit_id:string) {
     return this.http.post(this.url+'edit-employee', {name: name,email: email,mobile: mobile,updated_by: login_user_id,edit_id:edit_id},this.con);
  }
  deleteProfile(id: number) {
     return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
  }
}
