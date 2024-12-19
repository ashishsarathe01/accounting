import { Component, OnInit,Pipe } from '@angular/core';
import { FormGroup,FormControl,Validators,FormArray,ValidationErrors } from '@angular/forms';
import {SaleService} from '../../../../services/sale.service';

declare var $: any;
@Component({
  selector: 'app-sale',
  templateUrl: './sale.component.html',
  styleUrls: ['./sale.component.css']
})  

export class SaleComponent implements OnInit {
 
  constructor(private sale :SaleService) { }
  party_list = [];
  item_list:any = [];
  user_gst:string = "06";
  merchant_gst:string = "";
  gst_percentage:number = 12;
  user_detail:any = [];
  ngOnInit(): void {
    this.names.push(new FormControl('', [Validators.required,this.requireItemMatch.bind(this)])); 
    this.names.push(new FormControl('', [Validators.required,this.requireItemMatch.bind(this)]));    
    this.getPartyList();
  }
  orderForm = new FormGroup({
    names: new FormArray([]),
    
    party: new FormControl('', Validators.required),
    date: new FormControl('', Validators.required),
    freight: new FormControl(''),
    apply_tax: new FormControl(''),
  });
  getPartyList(){
   
    this.sale.accountList().subscribe((data: any)=>{
      this.party_list = data.data;
      });
  }
  savePayment(){
    //console.log(this.orderForm.value.names);
    let party = this.orderForm.value.party;
    let date = this.orderForm.value.date;
    let freight = this.orderForm.value.freight;
    let apply_tax = this.orderForm.value.apply_tax;
    let data:any = [];
    for(let i = 0; i < this.names.length; i++) {
     console.log()
     if(this.names.at(i).value.id!="" && this.names.at(i).value.id!=undefined && $(".qty_"+i).val()!='' && $(".qty_"+i).val()!=undefined)
      data.push({"item":this.names.at(i).value.id,"qty":$(".qty_"+i).val()})
    }
    if(data.length==0){
      alert("Plaese select item")
      return;
    }
    this.user_detail = sessionStorage.getItem('merchant_details');
  this.user_detail = JSON.parse(this.user_detail);
    let login_user_id = this.user_detail.id;
    this.sale.createSale(date,party,apply_tax,freight,data,login_user_id).subscribe((data: any)=>{
      //this.party_list = data.data;
      alert("Sale Added Succesfully");
      location.reload();
    });
    
  }
  get names(): FormArray {
    return this.orderForm.get('names') as FormArray;
}
onFormSubmit(): void {
   
}
addNameField() { 
  this.names.push(new FormControl('', [Validators.required,this.requireItemMatch.bind(this)])); 
}
removeUser(index: number) {
  this.names.removeAt(index);
}
getItemList(v:any){  
  this.sale.itemList(v.value).subscribe((data: any)=>{
    this.item_list = data.data;
  });
}

ngAfterViewInit(){
  // $("#party").change(function(val:any){
  //   console.log(val)
  // })
}
selectItem(event:any,id:number){  
  $("#unit_"+id).html(event.short_name);
  $("#price_"+id).html(event.price);
  this.calculateItemAmount(id);
}
setItemQuantity(qty:any,id:number){
  this.calculateItemAmount(id);
  this.totalQuantity();
}
calculateItemAmount(id:number){
  let quantity = $("#quantity_"+id).val();
  if(quantity=="" || quantity==undefined){
    quantity = 1;
  }
  let price = $("#price_"+id).html();
  $("#total_"+id).html(quantity*price);
  this.totalAmount();
}
totalQuantity(){
  let total_qty:number = 0;
  $(".quantity").each(function(key:any, val:any){
    if($(val).val()!=""){
      total_qty = total_qty + parseInt($(val).val());
    }
  });
  $("#total_qunatity").html(total_qty);
}
totalAmount(){
  let total_amt:number = 0;
  $(".amount").each(function(key:any, val:any){
    if($(val).html()!=""){
      total_amt = total_amt + parseInt($(val).html());
    }
  });
  $("#total_amount").html(total_amt);
  this.calculateTotalAmount();
}
selectParty(e:any){
console.log(e)
}

applyTax(e:any){
  $("#cgst_div").hide();
  $("#sgst_div").hide();
  $("#igst_div").hide();
  if(e.target.checked==true){
    this.user_detail = sessionStorage.getItem('merchant_details');
    this.user_detail = JSON.parse(this.user_detail);
    this.merchant_gst = this.user_detail.gst;
    this.merchant_gst = this.merchant_gst.substring(0, 2);
    if(this.user_gst==this.merchant_gst){
      $("#cgst_div").show();
      $("#sgst_div").show();
    }else{
      $("#igst_div").show();
    }
  }else{
    
  }
  this.calculateTotalAmount();
}

calculateTotalAmount(){
  let total_amount = $("#total_amount").html();
  let grand_total = 0;
  let freight = $("#freight").val();
  if(freight=="" || freight==undefined){
    freight = 0;
  }
  if($("#applyTax").prop('checked')==true){
    
    if(total_amount=="" || total_amount==undefined || this.user_gst=="" || this.user_gst==undefined || this.merchant_gst=="" || this.merchant_gst==undefined){
      return;
    }
    let percentage = this.gst_percentage;
   
    total_amount = parseFloat(total_amount) + parseFloat(freight);
    if(this.user_gst==this.merchant_gst){
      percentage = percentage/2;
      let cgst:number = (total_amount*percentage)/100;
      let sgst:number = (total_amount*percentage)/100;
      $("#cgst_amount").html(cgst);
      $("#sgst_amount").html(sgst);
      grand_total = grand_total + parseFloat(total_amount) + cgst + sgst;
    }else{
      let igst =(total_amount*percentage)/100;
      $("#igst_amount").html(igst);
      grand_total = grand_total + total_amount + igst;
    }
    
    grand_total = Math.round(grand_total)
    $("#grand_total").html(grand_total);
  }else{
    $("#cgst_div").hide();
    $("#sgst_div").hide();
    $("#igst_div").hide();
    $("#cgst_amount").html('');
    $("#sgst_amount").html('');
    $("#igst_amount").html('');
    total_amount = parseFloat(total_amount) + parseFloat(freight);
    grand_total = grand_total + parseFloat(total_amount);
    grand_total = Math.round(grand_total)
    $("#grand_total").html(grand_total);
  }
  
}
displayFn(shop: any) {
  
  return shop.name;
}
private requireItemMatch(control: FormControl): ValidationErrors | null {
  const selection: any = control.value;
  if (this.item_list && this.item_list.indexOf(selection) < 0) {
    return { requireMatch: true };
  }
  return null;
}
}
