<?php
  function send_response($status, $status_message, $data)
	{
		header("HTTP/1.1 $status $status_message");
		$response['status'] = $status;
		$response['status_message'] = $status_message;
		$response['data'] = $data;
		
		$json_responce=json_encode($response);
		echo $json_responce;
	}
	
	header ("Content-Type:text/html");
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
    	$dir    = 'club_highscores';
		$files1 = scandir($dir);
		$delimiter = '(chs)';
		//print_r($files1);
		
		echo "Available scores:<br/>";
		echo '<table cellspacing="1" cellpadding="4" border="3" bgcolor="#f5f5f5"><tbody><tr><th>track</th><th>mode</th></tr>';
		$lastTrack = "";
		foreach ($files1 as $f )
		{							
			if (strpos($f,$delimiter ) !== false) 
			{
				$name = explode(".json",$f);
				$parts = explode($delimiter,$name[0]);
				//print_r ($parts);
				##echo "<td>";
				if ($lastTrack == $parts[0])
				{
					echo "<td><a href='print_scores.php?track=",$parts[0],"&mode=",$parts[1], "'>", $parts[1],"</a></td>";
				}
				else 
				{
					if ($lastTrack != "") echo "</td></tr>";
					echo "<tr>";
					echo "<td>",$parts[0], "</td><td><a href='print_scores.php?track=",$parts[0],"&mode=",$parts[1], "'>", $parts[1],"</a></td>" ;
				}
				//if($lastTrack != "" and lastTrack != $parts[0]) echo "</tr>";
				$lastTrack = $parts[0];
			}
			
		}
		echo "</td></tr>";
		echo '</tbody></table>';
		//send_response(200, "", "");
		break;
	}  
?>