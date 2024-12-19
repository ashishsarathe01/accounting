import { Injectable } from '@angular/core';
import { HttpHeaders } from '@angular/common/http';
@Injectable({
  providedIn: 'root'
})

export class Configuration {	
	public server = 'http://localhost/account_api/public/api/';//not required as we are using proxy
  //public server = 'https://kraftpaperz.com/account_api/api/';
	public httpOptions: any = {
        headers: new HttpHeaders({
           'Content-Type': 'application/json',
           'token': sessionStorage.getItem('token') as string
        })
    }
}