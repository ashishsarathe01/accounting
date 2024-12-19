import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {EmployeeService} from '../../../../services/employee.service';
@Component({
  selector: 'app-employee',
  templateUrl: './employee.component.html',
  styleUrls: ['./employee.component.css']
})
export class EmployeeComponent implements OnInit {

  constructor(private emp:EmployeeService) { }
  user_detail:any = [];
  emp_list = [];
  form_title = "Add Employee";
  displayedColumns: string[] = ['name','mobile','email','action'];
  empForm = new FormGroup({
    email: new FormControl('',[Validators.required]),
    mobile: new FormControl('',[Validators.required,Validators.minLength(10),Validators.maxLength(10)]),
    name: new FormControl('',[Validators.required]),
    edit_id : new FormControl(''),
 });
   ngOnInit(): void {
    //this.getEmployee();
  }
  getEmployee(){
    this.emp.empList().subscribe((data: any)=>{
    this.emp_list = data.data;
    });
 }
  saveProfile(){
      let email = this.empForm.value.email;
      let mobile = this.empForm.value.mobile;
      let name = this.empForm.value.name;      
      let edit_id = this.empForm.value.edit_id;
      this.user_detail = sessionStorage.getItem('user_details');
      this.user_detail = JSON.parse(this.user_detail);  
        
      let login_user_id = this.user_detail.id;
      if(edit_id==""){
        this.emp.addEmployee(email,mobile,name,login_user_id).subscribe((data: any) => {
           if(data.success==true){
              alert(data.message);            
              this.getEmployee();
           }else{
              alert("Something went wrong");
           }         
        });
     }else{
        this.emp.editEmp(email,mobile,name,login_user_id,edit_id).subscribe((data: any) => {
           if(data.success==true){
              alert(data.message);            
              this.getEmployee();
           }else{
              alert("Something went wrong");
           }         
        });
     }
  }
  editEmpDetail(editDetail:any){
    this.form_title = "Edit Employee";
    this.empForm.patchValue({
      name: editDetail.name,
      email: editDetail.email,
      mobile: editDetail.mobile,     
      edit_id: editDetail.id
    });
  }
  deleteEmpDetail (id:number){
    if (confirm("Press a button!") == true) {
       this.emp.deleteProfile(id).subscribe((data: any) => {
          if(data.success==true){
             alert(data.message);            
             this.getEmployee();
          }else{
             alert("Something went wrong");
          }         
       });
    }
 }
  get emailValidator(){
    return this.empForm.get('email');
 }
 get nameValidator(){
    return this.empForm.get('name');
 }
 get mobileValidator(){
    return this.empForm.get('mobile');
 }

}
