<?php
	
	class database
	{
			
			public static function connect($host, $name, $user, $pass)
			{
				
				$conn = new PDO("mysql:host=" . $host . ";dbname=" . $name, $user, $pass);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
			}
			
	}
	
?>