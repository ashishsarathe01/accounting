import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
const routes: Routes = [
   {
      path:'admin',loadChildren:()=>import('./modules/admin/admin.module').then((m)=>m.AdminModule)
   },
   {
      path:'merchant',loadChildren:()=>import('./modules/merchant/merchant.module').then((m)=>m.MerchantModule)
   },
   // {
   //    path:'company-profile',
   //    component:CompanyprofileComponent,
   //    canActivate: [AuthGuard]
   // },
   
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
