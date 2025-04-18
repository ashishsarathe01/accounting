import { Component, OnInit } from '@angular/core';
import { FormGroup,FormControl,Validators,FormBuilder,FormArray,FormsModule } from '@angular/forms';
@Component({
  selector: 'app-journal',
  templateUrl: './journal.component.html',
  styleUrls: ['./journal.component.css']
})
export class JournalComponent implements OnInit {
  FormArray = [];
  constructor(private fb:FormBuilder) { }
  companyForm = this.fb.group({
    date: new FormControl('', [Validators.required]),
    admins: this.fb.array([])
  });
adminForm = this.fb.group({
  name: new FormControl('', [Validators.required]),
  email: new FormControl('', [Validators.required, Validators.email]),
});
  ngOnInit(): void {
  }
  
  saveJournal(){
    let name = this.companyForm.value.admins;
    console.log(name)
  }
  get admins() {
    return this.companyForm.controls["admins"] as FormArray;
  }
  addNewAdmin(){
    this.admins.push(this.adminForm);
  }

}
