import { Injectable } from '@angular/core';
import {HttpClient,HttpHeaders} from '@angular/common/http';
import { Configuration } from '../app.constant';
@Injectable({
  providedIn: 'root'
})
export class DashboardService {
private url: string;
private con:any =  {};
  constructor(private http:HttpClient,private configuration: Configuration) { 
this.url = configuration.server;
this.con = configuration.httpOptions;
  }
  orderListByStatus(){
   return this.http.post(this.url+'order-list-status','',this.con);
  }
}
