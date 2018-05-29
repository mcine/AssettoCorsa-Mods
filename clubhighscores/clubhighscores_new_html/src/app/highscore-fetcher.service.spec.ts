import { TestBed, inject } from '@angular/core/testing';

import { HighscoreFetcherService } from './highscore-fetcher.service';

describe('HighscoreFetcherService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [HighscoreFetcherService]
    });
  });

  it('should be created', inject([HighscoreFetcherService], (service: HighscoreFetcherService) => {
    expect(service).toBeTruthy();
  }));
});
