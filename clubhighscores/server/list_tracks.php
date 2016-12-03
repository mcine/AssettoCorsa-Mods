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
			foreach ($files1 as $f )
			{
				if (strpos($f,$delimiter ) !== false) 
				{
					$name = explode(".json",$f);
					$parts = explode($delimiter,$name[0]);
					//print_r ($parts);
					echo "<a href='print_scores.php?track=",$parts[0],"&mode=",$parts[1], "'>";
	      	echo "track: ", $parts[0], " Mode: ", $parts[1], "\n";
	      	echo "</a><br/>";
				}
			}
		  //send_response(200, "", "");
		  break;
	}  
	
	
?>