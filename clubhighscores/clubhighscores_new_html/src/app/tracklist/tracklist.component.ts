import { Component, OnInit, ViewChild } from '@angular/core';
import { HighscoreFetcherService } from './../highscore-fetcher.service';
import { Subscription } from 'rxjs';
import { MatSort, MatSortable, MatTableDataSource } from '@angular/material';
import { ITrack } from './../track.model';

@Component({
  selector: 'app-tracklist',
  templateUrl: './tracklist.component.html',
  styleUrls: ['./tracklist.component.css']
})

export class TracklistComponent implements OnInit {
  @ViewChild(MatSort) sort: MatSort;
  public tracklist = new Array();
  dataSource;
  public displayedColumns = ['name', 'drift',  'onelap', 'race'];
  constructor(private hsFetcher: HighscoreFetcherService) { }

  ngOnInit() {
    this.hsFetcher.getTracks().subscribe(result => {
     /* this.tracklist = Array.of(result);
      this.tracklist.forEach(element => {
        this.dataSource = element;
        this.dataSource.sort = this.sort;
      });*/
      this.dataSource = new MatTableDataSource(result);
      this.dataSource.sort = this.sort;
    });
  }

  applyFilter(filterValue: string) {
    filterValue = filterValue.trim(); // Remove whitespace
    filterValue = filterValue.toLowerCase(); // MatTableDataSource defaults to lowercase matches
    this.dataSource.filter = filterValue;
  }

}
