import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { throwError } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
// import { Configuration } from '../app/app.constant';
@Injectable({
   providedIn: 'root'
})
export class AuthService {
   private url: string;
   constructor(private http: HttpClient) { 
      this.url = "";
   }
   login(mobile: string, password: string) {
      //return this.http.post(this.url+'https://kraftpaperz.com/account_api/api/admin-signin', {mobile: mobile,password: password});
      return this.http.post(this.url+'http://localhost/account_api/public/api/admin-signin', {mobile: mobile,password: password});
   }
   merchantLogin(mobile: string, password: string) {
      //return this.http.post(this.url+'https://kraftpaperz.com/account_api/api/admin-signin', {mobile: mobile,password: password});
      return this.http.post(this.url+'http://localhost/account_api/public/api/merchant-signin', {mobile: mobile,password: password});
   }
   merchantLogout() {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('merchant_details');
   }
   logout() {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user_details');
   }
   IsLoggedIn(){
      return !!sessionStorage.getItem('token');
   }
   IsMerchantLoggedIn(){
      return !!sessionStorage.getItem('token ');
   }
}
