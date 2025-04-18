import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class CustomerService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   customerList(){
    return this.http.post(this.url+'view-customer','',this.con);
   }
   addCustomer(name: string,gst_number: string,gst_name: string,email: string,mobile: string,address: string,city: string,state: string,pincode: string,login_user_id:string) {
      return this.http.post(this.url+'add-customer', {name: name,gst_name: gst_name,gst_number: gst_number,email: email,mobile: mobile,address: address,city: city,state: state,pincode: pincode,created_by: login_user_id},this.con);
   }
   editCustomer(name: string,gst_number: string,gst_name: string,email: string,mobile: string,address: string,city: string,state: string,pincode: string,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-customer', {name: name,gst_name:gst_name,gst_number: gst_number,email: email,mobile: mobile,address: address,city: city,state: state,pincode: pincode,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
