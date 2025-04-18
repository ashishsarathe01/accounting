import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class ItemService {

  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   itemList(){
    return this.http.post(this.url+'view-item','',this.con);
   }
   addItem(name: string,print_name: string,unit: string,hsn_code: string,GSTIN: string,stock_qty: string,stock_val: string,item_group: string,status: number,login_user_id:string) {
      return this.http.post(this.url+'add-item', {name: name,print_name: print_name,unit: unit,hsn_code: hsn_code,gst_percentage: GSTIN,stock_qty: stock_qty,stock_val: stock_val,item_group: item_group,status:status,created_by: login_user_id},this.con);
   }
   editItem(name: string,print_name: string,unit: string,hsn_code: string,GSTIN: string,stock_qty: string,stock_val: string,item_group: string,status: number,login_user_id:string,edit_id:string) {
      return this.http.post(this.url+'edit-item', {name: name,print_name: print_name,unit: unit,hsn_code: hsn_code,gst_percentage: GSTIN,stock_qty: stock_qty,stock_val: stock_val,item_group: item_group,status:status,updated_by: login_user_id,edit_id:edit_id},this.con);
   }
   deleteMerchant(id: number) {
      return this.http.post(this.url+'delete-company-profile', {id: id},this.con);
   }
}
