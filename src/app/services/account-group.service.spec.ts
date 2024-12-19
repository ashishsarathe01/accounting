import { TestBed } from '@angular/core/testing';

import { AccountGroupService } from './account-group.service';

describe('AccountGroupService', () => {
  let service: AccountGroupService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(AccountGroupService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
