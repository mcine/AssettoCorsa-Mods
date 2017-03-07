# all credits mcine
# user is responsible for any harm caused by this sw.

import sys
import os
import platform
import ac
import acsys
import json
from collections import OrderedDict


if platform.architecture()[0] == "64bit":
    sysdir = "stdlib64"
else:
    sysdir = "stdlib"
sys.path.insert(0, os.path.join(os.path.dirname(__file__), sysdir))
os.environ['PATH'] = os.environ['PATH'] + ";."
    
import urllib.request as urllib2
from sim_info import info
import threading

server_url = "http://clubhighscores.000webhostapp.com/club_highscores.php";
server_connection_ok=0
scorelabel=0
currentscorelabel=0
cumulativescore=0
lastlaptimelabel=0
lapcount=0
lastDrift=0
storeDriftScore=0
highscore = 0
driftInvalid=0
waitForInvalid=0
waitTime = 1
lastdi = 0
maxdi = 3
updateinterval = 0.5
deltaTime = 0;
sessionDataSent = 0
invalidDriftCounter = 0
invalidDriftInterval = 3
lastLapCumulativeScore = 0
wasInPit = 1;


def acMain(ac_version):
  global scorelabel,currentscorelabel
  appWindow = ac.newApp("clubhighscores")
  ac.setSize(appWindow, 200, 200)
  ac.console("Club highsscores active!")  
  currentscorelabel = ac.addLabel(appWindow, "");
  ac.setPosition(currentscorelabel, 3, 27);
  #lastlaptimelabel = ac.addLabel(appWindow, "");
  #ac.setPosition(lastlaptimelabel, 3, 50);
  scorelabel = ac.addLabel(appWindow, "No connection to Server");
  ac.setPosition(scorelabel, 3, 50); #73
  UpdateScores();
  return "clubhighscores"

def checkForInvalidDrift(deltaT):
    global waitForInvalid,lastdi,driftInvalid,maxdi,storeDriftScore
    waitForInvalid = waitForInvalid - deltaT;
    di = ac.getCarState(0, acsys.CS.IsDriftInvalid);
    if di != lastdi:
      driftInvalid = driftInvalid + 1;      
    if driftInvalid >= maxdi or info.physics.numberOfTyresOut > 1:
      waitForInvalid = 0;
      lastdi = 0;
      driftInvalid = 0;
      storeDriftScore = 0;
      ac.setText(currentscorelabel, "Drift invalid");      
    else:
      lastdi = di;
    return;
	
def acUpdate(deltaT):
  global scorelabel, lapcount,currentscorelabel,lastDrift,driftInvalid,waitTime,waitForInvalid,maxdi,lastdi
  global deltaTime,updateinterval,lastlaptimelabel,storeDriftScore,server_connection_ok,cumulativescore
  global invalidDriftCounter,invalidDriftInterval,highscore,wasInPit
  if server_connection_ok == 0:
    return;

  if waitForInvalid > 0 and invalidDriftCounter >= invalidDriftInterval:
    checkForInvalidDrift(deltaT);
    invalidDriftCounter=0;
  invalidDriftCounter = invalidDriftCounter + 1;     
    
  if waitForInvalid < 0:
    SendCurrentScore();
    
  deltaTime = deltaTime+deltaT;
  if deltaTime <= updateinterval:    
    return;
  deltaTime = 0;

  handleDrifting()
  
  laps = ac.getCarState(0, acsys.CS.LapCount);
  if laps > lapcount or (info.graphics.isInPit and wasInPit != info.graphics.isInPit):
      wasInPit = info.graphics.isInPit;
      lapcount = laps;
      t = threading.Thread(target=UpdateScores, args = ())
      t.start()
      #ac.console("lap inc");      
      #   ac.setText(lastlaptimelabel, str(ac.getCarState(0, acsys.CS.LastLap)));
      #ac.console("Send Lap score");
      SendLapScore();

  if info.graphics.numberOfLaps == info.graphics.completedLaps and info.graphics.numberOfLaps > 0:      
      SendSessionData();
      #ac.console("Session data sent")  
  return;

def handleDrifting():
  global currentscorelabel,lastDrift,highscore,waitForInvalid,waitTime,storeDriftScore
  cs = round(ac.getCarState(0, acsys.CS.InstantDrift));     
  if cs > 0:
    if ac.getCarState(0, acsys.CS.IsDriftInvalid):      
      resetDriftScoring()
      ac.setText(currentscorelabel, "Drift invalid");
    else:
      ac.setText(currentscorelabel, "Drift Score: " + str(cs));
  if lastDrift > cs: # drift done
    #if lastDrift > highscore: # do not send if not highscore drift
    waitForInvalid = waitTime;
    storeDriftScore = lastDrift;    
  lastDrift = cs

  
def GetScoresFromServer(params):
  try:    
    with urllib2.urlopen(server_url+params) as resp:
      respo = resp.read();
      scores = json.loads(respo.decode('utf-8'), object_pairs_hook=OrderedDict)      
      return scores 
  except Exception as e: 
    scorestring = {"No Scores":{"name":"No Scores","score":""}}
    ac.log(str(e))
    #jsonscores = json.loads(scorestring);  
  return scorestring 

def UpdateScores():
  global server_connection_ok
  params = "?track="+ac.getTrackName(0)+"-"+ac.getTrackConfiguration(0)+"&mode=drift";
  scorejson = GetScoresFromServer(params);
  scores = ''
  for (key) in scorejson:
    scores += key + ':' + scorejson[key]['score']+'\n'
  ac.setText(scorelabel, scores);
  server_connection_ok = 1;
  #ac.console("update scores");
  return;
  
def SendScore(pdata):
  try:
    req = urllib2.Request(server_url)
    req.add_header('Content-Type', 'application/json')
    urllib2.urlopen(req, json.dumps(pdata).encode('utf-8'))
  except:     
    response = ""
  return ""
  
def SendCurrentScore():
  global highscore, storeDriftScore
  if highscore < storeDriftScore:
    highscore = storeDriftScore
  data = {"name":ac.getDriverName(0),"track":ac.getTrackName(0)+"-"+ac.getTrackConfiguration(0),"mode":"drift","score":str(storeDriftScore),"car":ac.getCarName(0)}
  t = threading.Thread(target=SendScore, args = (data,))
  #t.daemon = True
  t.start()
  resetDriftScoring()
  #ac.console("current score sent: "+str(storeDriftScore))  
  return;

def resetDriftScoring():
  global driftInvalid,cumulativescore,storeDriftScore,lastDrift,lastdi,waitForInvalid,storeDriftScore
  cumulativescore = cumulativescore + storeDriftScore;
  driftInvalid = 0;
  lastdi = 0;
  waitForInvalid = 0;
  storeDriftScore = 0;
  
def SendLapScore():
  global lastLapCumulativeScore,cumulativescore
  lapscore = cumulativescore - lastLapCumulativeScore;
  lastLapCumulativeScore = cumulativescore;
  data = {"name":ac.getDriverName(0),"track":ac.getTrackName(0)+"-"+ac.getTrackConfiguration(0),"mode":"OneLapDrifting","car":ac.getCarName(0),
  "score":str(lapscore),"laptime":str(ac.getCarState(0,acsys.CS.LastLap))}
  t = threading.Thread(target=SendScore, args = (data,))
  t.start()
  #SendScore(data);
  return;  

def SendSessionData():
    #send cumulatice score
    global cumulativescore, server_connection_ok,sessionDataSent
    if sessionDataSent > 0:
        return;
    sessionDataSent = sessionDataSent + 1;
    if server_connection_ok == 0:
        return;

    session = info.graphics.session
    session_string = "other";
    laps = ac.getCarState(0, acsys.CS.LapCount)
    if laps < 1:
        laps = 1;
    if session == 0:
        session_string = "practise";
    if session == 1:
        session_string = "qualify";
    if session == 2:
        session_string = "race";
    data = {"name":ac.getDriverName(0),"track":ac.getTrackName(0)+"-"+ac.getTrackConfiguration(0),
            "mode":session_string,"car":ac.getCarName(0),"score":str(cumulativescore),"laps":str(laps),
            "bestlap":str(info.graphics.iBestTime),"average_score_per_lap":str(cumulativescore/laps),
            "score_per_km":str(cumulativescore/(info.graphics.distanceTraveled/1000))}
    SendScore(data);
    return;

def acShutdown():
    SendSessionData();
    return;
