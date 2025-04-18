import { TestBed } from '@angular/core/testing';

import { AccountHeadingService } from './account-heading.service';

describe('AccountHeadingService', () => {
  let service: AccountHeadingService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AccountHeadingService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
