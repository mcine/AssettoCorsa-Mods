<?php
  $delimitter = '(chs)';
  $server_version = 1;
  function send_response($status, $status_message, $data)
	{
		header("HTTP/1.1 $status $status_message");
		$response['status'] = $status;
		$response['status_message'] = $status_message;
		$response['data'] = $data;
		
		$json_responce=json_encode($response);
		echo $json_responce;
	}
	
  function log_POST($msg)
	{		
		$log = sprintf("%s -- %s,%s,%s,%s",date("Ymd H:i:s"),$msg['name'],$msg['track'],$msg['mode'],$msg['score']);
		$myfile = file_put_contents('club_highscores/scorelog.txt', $log.PHP_EOL , FILE_APPEND);
	}
	
	function myScoreSort($a, $b) { //Sort the array using a user defined function
    			return ((int)$a['score']) > ((int)$b['score']) ? -1 : 1; //Compare the scores
			}
	function myTimeSort($a, $b) { //Sort the array using a user defined function
    			return ($a['time']) < ($b['time']) ? -1 : 1; //Compare the scores
			}
			
	function update_drift_score($msg)
	{
    global $delimitter;
    $donotupdate = false;
		$name = $msg['name'];
		if(!empty($msg['track']) && !empty($msg['mode']) && !empty($msg['name'])  && !empty($msg['score']))
		{
			// highscore entry
			$removechars = array(' ','.','.');
			$entry[$name] = $msg;
			$msg["updateTime"] = date("Ymd H:i:s");
			$filename = sprintf("club_highscores/%s%s%s.json",$msg['track'],$delimitter,$msg['mode']);
	
			if(file_exists($filename))
			{
				$scoretable = json_decode(file_get_contents($filename),true);
			}
			if(!empty($scoretable[$name]))
			{
				if(((int)$scoretable[$name]['score']) < ((int)$msg['score']))
				{
					 $scoretable[$name]=$msg;
				}
				else $donotupdate = true;
				
			}
			else $scoretable[$name]=$msg;
			if(!$donotupdate)
			{
				usort($scoretable, 'myScoreSort');
				//ksort($scoretable['score']);
				foreach ($scoretable as $i )
				{
					$newscores[$i['name']] = $i;
				}
				file_put_contents($filename, json_encode($newscores));
			}
		}
	}
	
	function update_time_score($msg)
	{
    global $delimitter;
    $donotupdate = false;
		$name = $msg['name'];
		if(!empty($msg['track']) && !empty($msg['mode']) && !empty($msg['name'])  && !empty($msg['time']))
		{
			// highscore entry
			$removechars = array(' ','.','.');
			$update_entry['time'] = $msg['time'];// str_replace($removechars, "", $msg['score']);
			$update_entry['name'] = $name;
			//$entry = [];
			$entry[$name] = $entry;
			$filename = sprintf("club_highscores/%s%s%s.json",$msg['track'],$delimitter,$msg['mode']);
	
			if(file_exists($filename))
			{
				$scoretable = json_decode(file_get_contents($filename),true);
			}
   		if(!empty($scoretable[$name]))
   		{
   			if (strcmp($scoretable[$name]['time'], $update_entry['time'])>0)
   			{
  				 $scoretable[$name]=$update_entry;
   			}
   			else $donotupdate = true;
   			
   		}
   		else $scoretable[$name]=$update_entry;
   		if(!$donotupdate)
   		{
   			usort($scoretable, 'myTimeSort' );
			//ksort($scoretable['score']);
			foreach ($scoretable as $i )
			{
				$newscores[$i['name']] = $i;
			}
   			file_put_contents($filename, json_encode($newscores));
   		}
		}
	}


	header ("Content-Type:application/json");
  $method = $_SERVER['REQUEST_METHOD'];
   
  date_default_timezone_set('Europe/Helsinki');
  
	if (!file_exists('club_highscores')) 
	{
		 mkdir('club_highscores', 0777, true);
	}
   //echo $method;
/*	if(!empty($_GET['name'])) // get parameter
	{
	   echo $_SERVER['REQUEST_URI'];
	}
	else
	{
		
	}
	*/
	
	switch ($method)
	{
    case 'GET':
		  if(!empty($_GET['track']) && !empty($_GET['mode']))
		  {
        if(empty($_GET['client_version']) or ((int)$_GET['client_version'])!=$server_version)
        {   
          echo '{"Error":{"name":"Error","track":"testirata","mode":"race","score":"Please update plugin"}}';      
        }
        else
        {
          $filename = sprintf("club_highscores/%s%s%s.json",$_GET['track'],$delimitter,$_GET['mode']);
          echo file_get_contents($filename);	            
        }			
		  }
		  else echo file_get_contents("club_highscores/scorelog.txt");
		  //send_response(200, "", "");
		  break;
	  
	  case 'PUT':
		  send_response(405, "", "");
		  break;
	  
	  case 'POST': // curl -H "Content-Type: application/json" -X POST -d {\"test\":\"test\"} http://localhost:8080/test.php
	  //{"name":"player3","track":"testirata","mode":"drift","score":"300"}
		  $test = json_decode(file_get_contents('php://input'),true);		  
	  	log_POST($test);
		
		if(!isset($test['client_version']) or ((int)$test['client_version'])!=$server_version)
		{
			echo '{"Error":{"score":"Please upgrade version"}}';
		}
		else if('time' == strtolower($test['mode']))
		{
			update_time_score($test);
			send_response(200, "Score updated", "OK");
		}
		else if (array_key_exists("mode",$test))
		{
			update_drift_score($test);
		  send_response(200, "Score updated", "OK");
		}
		else send_response(405, "Mode Not supported", "Error");
		  break;
	  
	  default:
	  case 'DELETE':
		  send_response(405, "", "");
		  break;
	}  
	
	
?>