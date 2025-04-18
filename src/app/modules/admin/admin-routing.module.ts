import { NgModule } from '@angular/core';
import { AuthGuard } from './../../auth/auth.guard';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { EmployeeComponent } from './components/employee/employee.component';
import { TaxCategoryComponent } from './components/tax-category/tax-category.component';
import { LoginComponent } from './components/login/login.component';
import { MerchantComponent } from './components/merchant/merchant.component';
import { UnitComponent } from './components/unit/unit.component';

import { StateComponent } from './components/state/state.component';
import { ItemGroupComponent } from './components/item-group/item-group.component';
import { ItemComponent } from './components/item/item.component';
import { AccountGroupComponent } from './components/account-group/account-group.component';
import { AccountHeadingComponent } from './components/account-heading/account-heading.component';
const routes: Routes = [
  {
    path:'',component:DashboardComponent,children:[
      {path:'dashboard',component:DashboardComponent},
      {path:'employee',component:EmployeeComponent},
      {path:'tax-category',component:TaxCategoryComponent},
      {path:'merchant',component:MerchantComponent},
      {path:'unit',component:UnitComponent},
      {path:'account-heading',component:AccountHeadingComponent},
      {path:'item-group',component:ItemGroupComponent},
      {path:'account-group',component:AccountGroupComponent},
      {path:'item',component:ItemComponent},
      // {path:'',redirectTo:'/admin/dashboard',pathMatch:'full'}
    ],
    canActivate: [AuthGuard]
  },
  {path:'login',component:LoginComponent},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
