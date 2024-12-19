import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {ItemGroupService} from '../../../../services/item-group.service';
@Component({
  selector: 'app-item-group',
  templateUrl: './item-group.component.html',
  styleUrls: ['./item-group.component.css']
})
export class ItemGroupComponent implements OnInit {

  constructor(private item_group:ItemGroupService) { }
  user_detail:any = [];
  item_group_list = [];
  form_title = "Add Item Group";
  displayedColumns: string[] = ['name','action'];
  itemGroupForm = new FormGroup({
    name: new FormControl('',[Validators.required]),    
    edit_id : new FormControl(''),
 });
 ngOnInit(): void {
  this.getItemGroup();
}
getItemGroup(){
  this.item_group.itemGroupList().subscribe((data: any)=>{
  this.item_group_list = data.data;
  });
}
saveItemGroup(){
  let name = this.itemGroupForm.value.name;
  let edit_id = this.itemGroupForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('user_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  console.log(name)
  if(edit_id==""){
    this.item_group.addItemGroup(name,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getItemGroup();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    this.item_group.editItemGroup(name,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.getItemGroup();
       }else{
          alert("Something went wrong");
       }         
    });
 }
}
editItemGroup(editDetail:any){
  this.form_title = "Edit Item Group";
  this.itemGroupForm.patchValue({
    name: editDetail.name,
    edit_id: editDetail.id
  });
}
get nameValidator(){
  return this.itemGroupForm.get('name');
}
}
