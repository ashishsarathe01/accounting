import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {CustomerService} from '../../../../services/customer.service';
@Component({
  selector: 'app-customer',
  templateUrl: './customer.component.html',
  styleUrls: ['./customer.component.css']
})
export class CustomerComponent implements OnInit {

  constructor(private customer:CustomerService) { }
  form_title = "Add Customer";
  user_detail:any = [];
  customer_list = []; 
  displayedColumns: string[] = ['name','gst_number','gst_name','email','mobile','action'];
  customerForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    gst_number: new FormControl('',[Validators.required]),
    gst_name: new FormControl('',[Validators.required]),
    email: new FormControl('',[Validators.required]),
    mobile: new FormControl('',[Validators.required]),
    address: new FormControl('',[Validators.required]),
    city: new FormControl('',[Validators.required]),
    state: new FormControl('',[Validators.required]),
    pincode: new FormControl('',[Validators.required]),
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getCustomer();
  }
  getCustomer(){
    this.customer.customerList().subscribe((data: any)=>{
    this.customer_list = data.data;
    });
 }
 saveCustomer(){
  let name = this.customerForm.value.name;
  let gst_number = this.customerForm.value.gst_number;
  let gst_name = this.customerForm.value.gst_name;
  let email = this.customerForm.value.email; 
  let mobile = this.customerForm.value.mobile; 
  let address = this.customerForm.value.address;
  let city = this.customerForm.value.city; 
  let state = this.customerForm.value.state;
  let pincode = this.customerForm.value.pincode;   
  let edit_id = this.customerForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  if(edit_id==""){
    this.customer.addCustomer(name,gst_number,gst_name,email,mobile,address,city,state,pincode,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getCustomer();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    this.customer.editCustomer(name,gst_number,gst_name,email,mobile,address,city,state,pincode,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getCustomer();
       }else{
          alert("Something went wrong");
       }         
    });
 }
  
}
get nameValidator(){
  return this.customerForm.get('name');
}
get gstNumberValidator(){
  return this.customerForm.get('gst_number');
}
get gstNameValidator(){
  return this.customerForm.get('gst_name');
}
get emailValidator(){
  return this.customerForm.get('email');
}
get mobileValidator(){
  return this.customerForm.get('mobile');
}
get addressValidator(){
  return this.customerForm.get('address');
}
get cityValidator(){
  return this.customerForm.get('city');
}
get stateValidator(){
  return this.customerForm.get('state');
}
get pincodeValidator(){
  return this.customerForm.get('pincode');
}
editCustomerDetail(editDetail:any){
  this.form_title = "Edit Customer";
  this.customerForm.patchValue({
    name: editDetail.name,
    gst_number: editDetail.gst_number,
    gst_name: editDetail.gst_name,
    email: editDetail.email,
    mobile: editDetail.mobile, 
    address: editDetail.address, 
    city: editDetail.city, 
    state: editDetail.state, 
    pincode: editDetail.pincode,
    edit_id: editDetail.id
  });
}
}
