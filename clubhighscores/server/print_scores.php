<?php
  $delimitter = '(chs)';
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
      if(!empty($_GET['track']) && !empty($_GET['mode']))
		  {
				$filename = sprintf("club_highscores/%s%s%s.json",$_GET['track'],$delimitter,$_GET['mode']);
				if(file_exists($filename))
				{
					$scoretable = json_decode(file_get_contents($filename),true);
					$keyarray=array("name","score");
					$keyitem = $scoretable[key($scoretable)];
					foreach ($keyitem as $key => $value )
					{
						if(!in_array($key,$keyarray))
						{
							array_push($keyarray,$key);	
						}
					}
					foreach ($keyarray as $k )
					{
						echo $k, " ";
					}
					echo "<br/>";
					foreach ($scoretable as $i )
					{
						foreach ($keyarray as $k )
						{
							echo $i[$k], " ";
						}
						echo "<br/>";
					}
				}
				
		  }
		  //send_response(200, "", "");
		  break;
	}  
	
	
?>