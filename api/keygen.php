<?php
	require_once("../core/init.inc.php");
	
	//header('Content-type: application/json');
	
	$friendslist = $conn->prepare("SELECT `user`.`userid`, `user`.`email`, `user`.`firstname`, `user`.`lastname`  FROM `user_friend` INNER JOIN `user` ON `user_friend`.`friendid` = `user`.`userid` WHERE `user_friend`.`userid` = 1");
	$friendslist->execute();
	$ffl = $friendslist->fetchAll(PDO::FETCH_OBJ);
	
	echo json_encode($ffl, JSON_FORCE_OBJECT);
	
	/*
	$container = array();
	
	foreach($ffl as $key => $friend)
	{
		$friends = array();
		foreach($friend as $fkey => $fvalue)
		{
			if(is_numeric($fkey)){ unset($fkey); continue; }
			$friends[$fkey] = $fvalue;
		}
		$container[] = $friends;
	}*/
	
	
	
?>