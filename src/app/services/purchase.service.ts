import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class PurchaseService {
  private url: string;
  private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) {
    this.url = configuration.server;
    this.con = configuration.httpOptions;
   }
   accountList(id:number,keyword:string){
    return this.http.post(this.url+'account-by-group',{group_id:id,keyword:keyword},this.con);
   }
   itemList(keyword:string){
    return this.http.post(this.url+'item-list-search',{keyword:keyword},this.con);
   }
   createPurchase(date:string,user:number,apply_tax:string,freight:string,data:string,login_user_id:number ){
    return this.http.post(this.url+'create-purchase',{date:date,user:user,apply_tax:apply_tax,freight:freight,data:data,created_by: login_user_id},this.con);
   }
  
}
