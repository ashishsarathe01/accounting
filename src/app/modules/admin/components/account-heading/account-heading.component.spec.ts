import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountHeadingComponent } from './account-heading.component';

describe('AccountHeadingComponent', () => {
  let component: AccountHeadingComponent;
  let fixture: ComponentFixture<AccountHeadingComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ AccountHeadingComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountHeadingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
