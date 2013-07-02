<?php
	
	function logtodb($request, $way)
	{
			global $conn;
			
			switch($way)
			{
				case 0:
					$way = "Inbound";
				break;
				case 1:
					$way = "Outbound";
				break;
				default:
					$way = "Undefined";
				break;
			}
			
			$stmt = $conn->prepare("INSERT INTO log (request, datetime, way) VALUES(:request, :datetime, :way)");
			$stmt->bindParam(':request', $request);
			$stmt->bindParam(':datetime', date("Y-m-d H:i:s"));
			$stmt->bindParam(':way', $way);
			$stmt->execute();
	}
	
	function apikeygen($length = 20) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	
	function fix_keys($array) {
		foreach ($array as $k => $val) {
			if (is_array($val)){
				$array[$k] = fix_keys($val); //recurse
			}
			if(is_numeric($k)){
				$numberCheck = true;
			}
		}
		if($numberCheck === true){
			return array_values($array);
		} else {
			return $array;
		}
	}
	
	function updatestatus($userid, $email, $password, $status)
	{
		global $conn;
		
		switch($status)
		{
			case 0:
				$status = false;
			break;
			case 1:
				$status = true;
			break;
			default:
				$status = false;
			break;
		}
		
		
			
	}
	
?>