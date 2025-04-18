import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';

import {HttpClientModule} from '@angular/common/http';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { ReactiveFormsModule } from '@angular/forms';
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatSortModule } from '@angular/material/sort';
import { MatCardModule } from '@angular/material/card';

import { AuthModule } from './auth/auth.module';
import { ToastrModule } from 'ngx-toastr';

import { NgHttpLoaderModule } from 'ng-http-loader';

import { NgxDatatableModule } from '@swimlane/ngx-datatable';
import { MatPaginatorModule } from '@angular/material/paginator';

import { MatProgressBarModule } from '@angular/material/progress-bar';

import { HomeComponent } from './components/home/home.component';
@NgModule({
  declarations: [
    AppComponent,
   
    HomeComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    BrowserAnimationsModule,
    FormsModule,
    ReactiveFormsModule,
    MatTableModule,
        MatSortModule,
        MatCardModule,
        AuthModule,
        ToastrModule.forRoot({
          positionClass: 'toast-top-center',
        }),
        NgHttpLoaderModule.forRoot(),
        NgxDatatableModule,
        MatPaginatorModule,
        MatProgressBarModule
        
        
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
