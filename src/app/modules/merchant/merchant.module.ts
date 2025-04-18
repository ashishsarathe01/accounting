import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { DatePipe } from '@angular/common';
import { MerchantRoutingModule } from './merchant-routing.module';
import { LoginComponent } from './components/login/login.component';
import { FormsModule } from '@angular/forms';
import { ReactiveFormsModule } from '@angular/forms';
import { NgHttpLoaderModule } from 'ng-http-loader';
import { MatTableModule } from '@angular/material/table';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { SidebarComponent } from './components/sidebar/sidebar.component';
import { ItemComponent } from './components/item/item.component';
import { CustomerComponent } from './components/customer/customer.component';
import { BankComponent } from './components/bank/bank.component';
import { ReceiptComponent } from './components/receipt/receipt.component';
import { AccountComponent } from './components/account/account.component';
import { PaymentComponent } from './components/payment/payment.component';
import { LedgerComponent } from './components/ledger/ledger.component';
import { JournalComponent } from './components/journal/journal.component';
import { SaleComponent } from './components/sale/sale.component';
import {MatAutocompleteModule} from '@angular/material/autocomplete';
import { MatInputModule } from '@angular/material/input';
import { PurchaseComponent } from './components/purchase/purchase.component'
import { MatFormFieldModule } from "@angular/material/form-field";
import { ToastrModule } from 'ngx-toastr';
@NgModule({
  declarations: [
    LoginComponent,
    DashboardComponent,
    HeaderComponent,
    FooterComponent,
    SidebarComponent,
    ItemComponent,
    CustomerComponent,
    BankComponent,
    ReceiptComponent,
    AccountComponent,
    PaymentComponent,
    LedgerComponent,
    JournalComponent,
    SaleComponent,
    PurchaseComponent,
  ],
  imports: [
    CommonModule,
    MerchantRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    NgHttpLoaderModule,
    MatTableModule,
    MatInputModule,
    MatAutocompleteModule,
    MatFormFieldModule,
    ToastrModule.forRoot({
      positionClass: 'toast-top-center',
    }),
  ],
  providers: [DatePipe],
})
export class MerchantModule { }
