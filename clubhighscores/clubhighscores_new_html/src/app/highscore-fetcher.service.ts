import { Injectable } from '@angular/core';
/*import { QueueingSubject } from 'queueing-subject';*/
import { Observable } from 'rxjs/Observable';
/*import websocketConnect from 'rxjs-websockets';*/
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ITrack } from './track.model';

@Injectable({
  providedIn: 'root'
})
export class HighscoreFetcherService {

  private server = ''; //'/fetch/'; // 'http://clubhighscores.000webhostapp.com/';
  private getTracksUrl = 'club_highscores.php?list=list';
  private getDetailsUrl = 'print_scores.php?track=ks_vallelunga-extended_circuit&mode=OneLapDrifting';

  private httpOptions = {
    headers: new HttpHeaders({
      'Access-Control-Allow-Origin': '*',
      Accept: 'application/json',
      responseType: 'json'
    })
  };

  constructor(private http: HttpClient) { }

  public getTracks(): Observable<ITrack[]> {
    return this.http.get<ITrack[]>(this.server + this.getTracksUrl, this.httpOptions);
  }
}
