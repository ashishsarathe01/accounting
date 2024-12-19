import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {MerchantService} from '../../../../services/merchant.service';
@Component({
  selector: 'app-merchant',
  templateUrl: './merchant.component.html',
  styleUrls: ['./merchant.component.css']
})
export class MerchantComponent implements OnInit {
  form_title = "Add Merchant";
  user_detail:any = [];
  merchant_list = [];
  displayedColumns: string[] = ['name','email','mobile','GSTIN','gst_name','address','state','pincode','action'];
  constructor(private merchant:MerchantService) { }
  merchantForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    mobile: new FormControl('',[Validators.required]),
    email: new FormControl('',[Validators.required]),
    GSTIN: new FormControl('',[Validators.required]),
    gst_name: new FormControl('',[Validators.required]),
    address: new FormControl('',[Validators.required]),
    state: new FormControl('',[Validators.required]),
    pincode: new FormControl('',[Validators.required]),    
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getMerchant();
  }
  getMerchant(){
    this.merchant.merchantList().subscribe((data: any)=>{
    this.merchant_list = data.data;
    });
 }
  saveMerchant(){
      let name = this.merchantForm.value.name;
      let email = this.merchantForm.value.email;
      let mobile = this.merchantForm.value.mobile;
      let GSTIN = this.merchantForm.value.GSTIN;         
      let gst_name = this.merchantForm.value.gst_name;   
      let address = this.merchantForm.value.address;   
      let state = this.merchantForm.value.state;   
      let pincode = this.merchantForm.value.pincode;
      let edit_id = this.merchantForm.value.edit_id;
      this.user_detail = sessionStorage.getItem('user_details');
      this.user_detail = JSON.parse(this.user_detail); 
      let login_user_id = this.user_detail.id;
    if(edit_id==""){
      this.merchant.addMerchant(name,email,mobile,GSTIN,gst_name,address,state,pincode,login_user_id).subscribe((data: any) => {
        console.log(data) 
        if(data.success==true){
            alert(data.message);            
            this.getMerchant();
         }else{
          console.log("ddd");
            alert("Something went wrong");
         }         
      });
   }else{
      this.merchant.editMerchant(name,email,mobile,GSTIN,gst_name,address,state,pincode,login_user_id,edit_id).subscribe((data: any) => {
         if(data.success==true){
            alert(data.message);            
            this.getMerchant();
         }else{
            alert("Something went wrong");
         }         
      });
   }
    
  }
  get nameValidator(){
    return this.merchantForm.get('name');
 }
 get emailValidator(){
    return this.merchantForm.get('email');
 }
 get mobileValidator(){
    return this.merchantForm.get('mobile');
 }
 get gstValidator(){
  return this.merchantForm.get('GSTIN');
}
get gstNameValidator(){
  return this.merchantForm.get('gst_name');
}
get addressValidator(){
  return this.merchantForm.get('address');
}
get stateValidator(){
  return this.merchantForm.get('state');
}
get pincodeValidator(){
  return this.merchantForm.get('pincode');
}
editMerchantDetail(editDetail:any){
  this.form_title = "Edit Merchant";
  this.merchantForm.patchValue({
    name: editDetail.name,
    email: editDetail.email,
    mobile: editDetail.mobile,
    GSTIN: editDetail.GSTIN,
    gst_name: editDetail.gst_name,     
    address: editDetail.address,
    state: editDetail.state,
    pincode: editDetail.pincode,
    edit_id: editDetail.id
  });
}
accountType(event:any){
  console.log(event.target.value)
}
}
