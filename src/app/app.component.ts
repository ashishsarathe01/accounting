import { Component } from '@angular/core';
import { Router } from '@angular/router';
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'Meri Accounting';
  router;
  constructor(
   public route : Router  
  ) {
      this.router = route.url
      // this.bnIdle.startWatching(1800).subscribe((res) => {
        
      //   if(res) { 
        
      //     if((Object.keys(localStorage).length === 0) ==  false){
            
      //       localStorage.clear();
      //       window.location.href = this.configuration.adminURL+'/login';
      //     }
      //   }
      // })
    }
  isLoggednIn(): boolean {
    return (sessionStorage.getItem('token') !== null);
  }
}
