import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { ItemComponent } from './components/item/item.component';
import { CustomerComponent } from './components/customer/customer.component';
import { BankComponent } from './components/bank/bank.component';
import { ReceiptComponent } from './components/receipt/receipt.component';
import { AccountComponent } from './components/account/account.component';
import { PaymentComponent } from './components/payment/payment.component';
import { LedgerComponent } from './components/ledger/ledger.component';
import { JournalComponent } from './components/journal/journal.component';
import { SaleComponent } from './components/sale/sale.component';
import { PurchaseComponent } from './components/purchase/purchase.component';
const routes: Routes = [
  {
    path:'',component:DashboardComponent,children:[
      {path:'dashboard',component:DashboardComponent},
      {path:'manage-item',component:ItemComponent},
      {path:'customer',component:CustomerComponent},
      {path:'bank',component:BankComponent},
      {path:'receipt',component:ReceiptComponent},
      {path:'account',component:AccountComponent},
      {path:'payment',component:PaymentComponent},
      {path:'ledger',component:LedgerComponent},
      {path:'journal',component:JournalComponent},
      {path:'sale',component:SaleComponent},
      {path:'purchase',component:PurchaseComponent},
    ]
  },
  {path:'login',component:LoginComponent},

];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class MerchantRoutingModule { }
