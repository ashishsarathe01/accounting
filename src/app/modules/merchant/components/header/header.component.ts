import { Component, OnInit } from '@angular/core';
import {AuthService} from '../../../../auth/auth.service';
import { Router } from '@angular/router';
@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {

  constructor(private auth: AuthService,private route: Router) { }

  ngOnInit(): void {
  }
  logout(){
    this.auth.merchantLogout()
    this.route.navigate(["merchant/login"]);
   }
}
