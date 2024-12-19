import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {AuthService} from '../../../../auth/auth.service';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr'
@Component({
   selector: 'app-login',
   templateUrl: './login.component.html',
   styleUrls: ['./login.component.css']
})

export class LoginComponent implements OnInit {
   constructor(private auth: AuthService,private route: Router,private toastr: ToastrService) { 

   }
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
      this.auth.login(mobile, password).subscribe((data: any) => {
         console.log(data);
         if(data.success==true){            
            sessionStorage.setItem("token",data.data.token);
            sessionStorage.setItem("user_details", JSON.stringify(data.data));
            this.route.navigate(['admin']);
         }else{
            //this.showToasterSuccess();
            alert("invalid Credential");
         }
         
      },
      (error) => {
        //  alert()
        // this.showToasterSuccess();

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

       this.toastr.success("Data shown successfully !!", "Data shown successfully !!")
     }
      
     showToasterError(){
       this.toastr.error("Something is wrong", "Something is wrong")
     }
      
     showToasterInfo(){
       this.toastr.info("This is info", "This is info")
     }
      
     showToasterWarning(){
       this.toastr.warning("This is warning", "This is warning")
     }
     isLoggednIn(): boolean {
      //alert(sessionStorage.getItem('token'))
         return (sessionStorage.getItem('token') !== null);
  }
}
