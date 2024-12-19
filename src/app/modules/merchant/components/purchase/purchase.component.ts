import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators,FormArray,ValidationErrors } from '@angular/forms';
import {PurchaseService} from '../../../../services/purchase.service';
declare var $: any;
@Component({
  selector: 'app-purchase',
  templateUrl: './purchase.component.html',
  styleUrls: ['./purchase.component.css']
})

export class PurchaseComponent implements OnInit {
  constructor(private purchase :PurchaseService) { }
  party_list:any = [];
  item_list:any = [];
  user_gst:string = "06";
  merchant_gst:string = "";
  gst_percentage:number = 12;
  user_detail:any = [];
  ngOnInit(): void {
    this.party.push(new FormControl('', [Validators.required, this.requireMatch.bind(this)]));
    this.addItemField();
  }
  purchaseForm = new FormGroup({
    party: new FormArray([]),
    item: new FormArray([]),
    date : new FormControl((new Date()).toISOString().substring(0,10)),
    apply_tax: new FormControl(''),
    freight: new FormControl(''),
  });
  get party(): FormArray {
    return this.purchaseForm.get('party') as FormArray;
  }
  displayFn(shop: any){
    return shop.name;
  }
  getPartyList(v:any){
    this.purchase.accountList(3,v.value).subscribe((data: any)=>{
      this.party_list = data.data;
      });
      
  }
  get item(): FormArray {
    return this.purchaseForm.get('item') as FormArray;
  }
  addItemField() { 
    this.item.push(new FormControl('', [Validators.required,this.requireItemMatch.bind(this)])); 
  }
  removeUser(index: number) {
    this.item.removeAt(index);
  }
  getItemList(v:any){
    this.purchase.itemList(v.value).subscribe((data: any)=>{
      this.item_list = data.data;
      });
      
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
  savePurchase(){
    let party = this.party.value[0].id;
    let date = this.purchaseForm.value.date;
    let freight = this.purchaseForm.value.freight;
    let apply_tax = this.purchaseForm.value.apply_tax;
    let data:any = [];
    for(let i = 0; i < this.item.length; i++) {
     
     if(this.item.at(i).value.id!="" && this.item.at(i).value.id!=undefined && $(".qty_"+i).val()!='' && $(".qty_"+i).val()!=undefined)
      data.push({"item":this.item.at(i).value.id,"qty":$(".qty_"+i).val()})
    }
    if(data.length==0){
      alert("Plaese select item")
      return;
    }
    
    this.user_detail = sessionStorage.getItem('merchant_details');
    this.user_detail = JSON.parse(this.user_detail);
    let login_user_id = this.user_detail.id;
    this.purchase.createPurchase(date,party,apply_tax,freight,data,login_user_id).subscribe((data: any)=>{
      alert("Purchase Added Succesfully");
      location.reload();
    });
  }

  private requireMatch(control: FormControl): ValidationErrors | null {
    const selection: any = control.value;
    if (this.party_list && this.party_list.indexOf(selection) < 0) {
      return { requireMatch: true };
    }
    return null;
  }
  private requireItemMatch(control: FormControl): ValidationErrors | null {
    const selection: any = control.value;
    if (this.item_list && this.item_list.indexOf(selection) < 0) {
      return { requireMatch: true };
    }
    return null;
  }
}
