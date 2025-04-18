import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AuthService} from '../../../../auth/auth.service';
import { Router } from '@angular/router';
import { IndividualConfig } from 'ngx-toastr';
import { CommonService, toastPayload } from '../../../../services/common.service';
@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {

  constructor(private auth: AuthService,private route: Router,private cs: CommonService) { }
  toast!: toastPayload;
  ngOnInit(): void {
  }
  loginForm = new FormGroup({
    username: new FormControl('',[Validators.required,Validators.minLength(10),Validators.maxLength(10)]),
    password: new FormControl('',[Validators.required,Validators.minLength(5)]),
    
 });
  login() {
    //this.showToasterInfo();
    let mobile = this.loginForm.value.username;
    let password = this.loginForm.value.password;
    this.auth.merchantLogin(mobile, password).subscribe((data: any) => {       
       if(data.success==true){            
          sessionStorage.setItem("token","ddd");
          sessionStorage.setItem("merchant_details", JSON.stringify(data.data));

          //this.route.navigate(['merchant']);
          window.location.href = "merchant";
       }else{
          //this.showToasterSuccess();
          alert("invalid Credential");
       }
       
    },
    (error) => {
      this.buttonClick('error');

    },
    () => {
      //this.showToasterSuccess();
    });
 }
 get emailValidator(){
    return this.loginForm.get('username');
 }
 get passwordValidator(){
    return this.loginForm.get('password');
 }
 showToasterSuccess(){

     //this.toastr.success("Data shown successfully !!", "Data shown successfully !!")
   }
    
   showToasterError(){
     //this.toastr.error("Something is wrong", "Something is wrong")
   }
    
   showToasterInfo(){
     //this.toastr.info("This is info", "This is info")
   }
    
   showToasterWarning(){
     //this.toastr.warning("This is warning", "This is warning")
   }
   isMerchantLoggednIn(): boolean {
    
       return (sessionStorage.getItem('merchant_token') !== null);
}
buttonClick(type: string) {
  this.toast = {
    message: 'Some Message to Show',
    title: 'Title Text',
    type: type,
    ic: {
      timeOut: 120000,
      closeButton: true,
      positionClass: 'toast-top-center',
    } as IndividualConfig,
  };
  this.cs.showToast(this.toast);
}
}
