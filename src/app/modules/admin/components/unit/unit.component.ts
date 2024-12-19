import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {UnitService} from '../../../../services/unit.service';
@Component({
  selector: 'app-unit',
  templateUrl: './unit.component.html',
  styleUrls: ['./unit.component.css']
})
export class UnitComponent implements OnInit {

  constructor(private unit:UnitService) { }
  form_title = "Add Unit";
  user_detail:any = [];
  unit_list = [];
  displayedColumns: string[] = ['name','short_name','status','action'];
  unitForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    short_name: new FormControl('',[Validators.required]),
    status: new FormControl('',[Validators.required]),
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getUnit();
  }
  getUnit(){
    this.unit.unitList().subscribe((data: any)=>{
    this.unit_list = data.data;
    });
 }
  saveUnit(){
    let name = this.unitForm.value.name;
    let short_name = this.unitForm.value.short_name;
    let status = this.unitForm.value.status;      
    let edit_id = this.unitForm.value.edit_id;
    this.user_detail = sessionStorage.getItem('user_details');
    this.user_detail = JSON.parse(this.user_detail); 
    let login_user_id = this.user_detail.id;
    if(edit_id==""){
      this.unit.addUnit(name,short_name,status,login_user_id).subscribe((data: any) => {
         if(data.success==true){
            alert(data.message);            
            this.getUnit();
         }else{
            alert("Something went wrong");
         }         
      });
   }else{
      this.unit.editUnit(name,short_name,status,login_user_id,edit_id).subscribe((data: any) => {
         if(data.success==true){
            alert(data.message);            
            this.getUnit();
         }else{
            alert("Something went wrong");
         }         
      });
   }
    
  }
  get shortNameValidator(){
    return this.unitForm.get('short_name');
 }
 get unitValidator(){
    return this.unitForm.get('name');
 }
 get statusValidator(){
    return this.unitForm.get('status');
 }
 editUnitDetail(editDetail:any){
  this.form_title = "Edit Unit";
  this.unitForm.patchValue({
    name: editDetail.name,
    short_name: editDetail.short_name,
    status: editDetail.status,     
    edit_id: editDetail.id
  });
}
}
