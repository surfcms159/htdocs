<?php
	
	$conf = array();
	
	/* Configs */
	require_once("config/db.conf.php");
	
	/* Classes */
	//require_once("class/db.class.php");
	
	/* Database Initiation */
	$conn = new PDO("mysql:host=" . $conf['db']['host'] . ";dbname=" . $conf['db']['name'], $conf['db']['user'], $conf['db']['pass']);
	
	
?>