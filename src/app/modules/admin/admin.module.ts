import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdminRoutingModule } from './admin-routing.module';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { SidebarComponent } from './components/sidebar/sidebar.component';
import { EmployeeComponent } from './components/employee/employee.component';
import { AccountGroupComponent } from './components/account-group/account-group.component';
import { AccountHeadingComponent } from './components/account-heading/account-heading.component';
import { ItemGroupComponent } from './components/item-group/item-group.component';
import { ItemComponent } from './components/item/item.component';
import { MerchantComponent } from './components/merchant/merchant.component';
import { StateComponent } from './components/state/state.component';
import { TaxCategoryComponent } from './components/tax-category/tax-category.component';
import { UnitComponent } from './components/unit/unit.component';
import { LoginComponent } from './components/login/login.component';


import { FormsModule } from '@angular/forms';
import { ReactiveFormsModule } from '@angular/forms';
import { NgHttpLoaderModule } from 'ng-http-loader';
import { MatTableModule } from '@angular/material/table';
@NgModule({
  declarations: [
    HeaderComponent,
    FooterComponent,
    DashboardComponent,
    SidebarComponent,
    EmployeeComponent,
    AccountGroupComponent,
    AccountHeadingComponent,
    ItemGroupComponent,
    ItemComponent,
    StateComponent,
    TaxCategoryComponent,
    MerchantComponent,
    UnitComponent,
    LoginComponent
    
  ],
  imports: [
    CommonModule,
    AdminRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    NgHttpLoaderModule,
    MatTableModule
  ]
})
export class AdminModule { }
