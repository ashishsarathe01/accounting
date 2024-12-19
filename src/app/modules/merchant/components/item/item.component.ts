import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {ItemService} from '../../../../services/item.service';
import {UnitService} from '../../../../services/unit.service';
import {ItemGroupService} from '../../../../services/item-group.service';
@Component({
  selector: 'app-item',
  templateUrl: './item.component.html',
  styleUrls: ['./item.component.css']
})
export class ItemComponent implements OnInit {

  constructor(private item:ItemService,private unit:UnitService,private item_group_ser:ItemGroupService) { }
  form_title = "Add Item";
  user_detail:any = [];
  item_list = [];
  unit_list = [];
  item_group_list = [];
 displayedColumns: string[] = ['name','unit','hsn_code','gst','item_group','status','action'];
  itemForm = new FormGroup({
    name: new FormControl('',[Validators.required]),
    print_name: new FormControl('',[Validators.required]),
    unit: new FormControl('',[Validators.required]),
    hsn_code: new FormControl('',[Validators.required]),
    GSTIN: new FormControl('',[Validators.required]),
    stock_qty: new FormControl('',[Validators.required]),
    stock_val: new FormControl('',[Validators.required]),
    item_group: new FormControl('',[Validators.required]),
    status: new FormControl('1'),
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.getItem();
    this.getUnit();
    this.getItemGroupList();
  }
  getItem(){
    this.item.itemList().subscribe((data: any)=>{
    this.item_list = data.data;
    });
 }
 getUnit(){
  this.unit.unitList().subscribe((data: any)=>{
  this.unit_list = data.data;  
  });
}
  getItemGroupList(){
    this.item_group_ser.itemGroupList().subscribe((data: any)=>{
    this.item_group_list = data.data;  
    });
}
 saveItem(){
  let name = this.itemForm.value.name;
  let print_name = this.itemForm.value.print_name;
  let unit = this.itemForm.value.unit;
  let hsn_code = this.itemForm.value.hsn_code; 
  let GSTIN = this.itemForm.value.GSTIN; 
  let stock_qty = this.itemForm.value.stock_qty; 
  let stock_val = this.itemForm.value.stock_val;
  let item_group = this.itemForm.value.item_group;
  let status = this.itemForm.value.status;    
  let edit_id = this.itemForm.value.edit_id;
  this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
  let login_user_id = this.user_detail.id;
  if(edit_id==""){
    this.item.addItem(name,print_name,unit,hsn_code,GSTIN,stock_qty,stock_val,item_group,status,login_user_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);
          this.itemForm.reset(); 
          this.itemForm.patchValue({
            status: 1,
          });      
          this.getItem();
       }else{
          alert("Something went wrong");
       }         
    });
 }else{
    this.item.editItem(name,print_name,unit,hsn_code,GSTIN,stock_qty,stock_val,item_group,status,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);
          this.itemForm.reset(); 
          this.itemForm.patchValue({
            status: 1,
          });         
          this.getItem();
       }else{
          alert("Something went wrong");
       }         
    });
 }
  
}
get nameValidator(){
  return this.itemForm.get('name');
}
get printNameValidator(){
  return this.itemForm.get('print_name');
}
get unitValidator(){
  return this.itemForm.get('unit');
}
get hsnCodeValidator(){
  return this.itemForm.get('hsn_code');
}
get GSTINValidator(){
  return this.itemForm.get('GSTIN');
}
get stockQtyValidator(){
  return this.itemForm.get('stock_qty');
}
get stockValValidator(){
  return this.itemForm.get('stock_val');
}
get itemGroupValidator(){
  return this.itemForm.get('item_group');
}
editItemDetail(editDetail:any){
  this.form_title = "Edit Item";
  this.itemForm.patchValue({
    name: editDetail.name,
    print_name: editDetail.print_name,
    unit: editDetail.unit,
    hsn_code: editDetail.hsn_code, 
    GSTIN: editDetail.gst_percentage, 
    stock_qty: editDetail.stock_qty, 
    stock_val: editDetail.stock_val, 
    item_group: editDetail.item_group,
    status: editDetail.status,
    edit_id: editDetail.id
  });
  document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
}
}

