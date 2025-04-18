import { TestBed } from '@angular/core/testing';

import { ItemGroupService } from './item-group.service';

describe('ItemGroupService', () => {
  let service: ItemGroupService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ItemGroupService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
