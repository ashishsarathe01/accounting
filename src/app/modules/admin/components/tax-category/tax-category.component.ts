import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators } from '@angular/forms';
import {TaxCategoryService} from '../../../../services/tax-category.service';
@Component({
  selector: 'app-tax-category',
  templateUrl: './tax-category.component.html',
  styleUrls: ['./tax-category.component.css']
})
export class TaxCategoryComponent implements OnInit {
  form_title = "Add Tax Category";
  user_detail:any = [];
  tax_category_list = [];
  displayedColumns: string[] = ['name','action'];
  constructor(private tax_category:TaxCategoryService) { }
  taxCategorytForm = new FormGroup({
    name: new FormControl('',[Validators.required]),    
    edit_id : new FormControl(''),
 });
  ngOnInit(): void {
    this.taxCategoryList();
  }
  taxCategoryList(){
    this.tax_category.taxCategoryList().subscribe((data: any)=>{
    this.tax_category_list = data.data;
    });
 }
  savetaxCategory(){
    let name = this.taxCategorytForm.value.name;
    let edit_id = this.taxCategorytForm.value.edit_id;
    this.user_detail = sessionStorage.getItem('user_details');
    this.user_detail = JSON.parse(this.user_detail); 
    let login_user_id = this.user_detail.id;
  if(edit_id==""){
    this.tax_category.addTaxCategory(name,login_user_id).subscribe((data: any) => {
      console.log(data) 
      if(data.success==true){
          alert(data.message);            
          this.taxCategoryList();
       }else{
        console.log("ddd");
          alert("Something went wrong");
       }         
    });
 }else{
    this.tax_category.editTaxCategory(name,login_user_id,edit_id).subscribe((data: any) => {
       if(data.success==true){
          alert(data.message);            
          this.taxCategoryList();
       }else{
          alert("Something went wrong");
       }         
    });
 }
  
}
get nameValidator(){
  return this.taxCategorytForm.get('name');
}
editTaxCategoryDetail(editDetail:any){
  this.form_title = "Edit Tax Category";
  this.taxCategorytForm.patchValue({
    name: editDetail.name,    
    edit_id: editDetail.id
  });
}
}
