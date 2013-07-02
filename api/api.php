<?php
	
	header('Content-type: application/json');
	
	require_once("../core/init.inc.php");
	require_once("func/api.func.php");
	
	date_default_timezone_set('Europe/London');
	
	/* Log what is sent from device */
	$req_dump = print_r($_REQUEST, TRUE);
	logtodb($req_dump,0);
	
	if(isset($_REQUEST['auth']) || $_REQUEST['auth'] == 1)
	{
		if(isset($_REQUEST['email']))
		{
			if(isset($_REQUEST['password']))
			{
				$stmt = $conn->prepare('SELECT userid, email, password, firstname, lastname FROM user WHERE email = :email');
				$stmt->bindParam(':email', $_REQUEST['email']);
				$stmt->execute();
				$fetch = $stmt->fetch();
				
				$svr_userid = $fetch['userid'];
				$svr_email = $fetch['email'];
				$svr_firstname = ucfirst($fetch['firstname']);
				$svr_lastname = ucfirst($fetch['lastname']);
				$svr_password = $fetch['password'];
				
				/* Get Users Key */
				$key = $conn->prepare('SELECT key_data FROM api_key WHERE userid = :userid');
				$key->bindParam(':userid', $svr_userid);
				$key->execute();
				$fetchkey = $key->fetch();
				
				$user_key = $fetchkey['key_data'];
				
				$md5_password = md5($_REQUEST['password']);
				
				if($md5_password == $svr_password)
				{
					$send = json_encode(array(
						'success'	=>	true,
						'uid'	=>	$svr_userid,
						'email'	=>	$svr_email,
						'password'	=>	$svr_password,
						'name'	=>	"{$svr_firstname} {$svr_lastname}",
						'created_at'	=>	date("Y-m-d H:i:s"),
						'api_key'	=>	$user_key
					));
					logtodb($send,1);
					echo $send;
				}
				else
				{
					$send = json_encode(array(
						'error'	=>	'00004',
						'error_msg'	=>	'Incorrect Password'
					));
					logtodb($send,1);
					echo $send;
				}
			}
			else
			{
				$send = json_encode(array(
					'error'	=>	'00003',
					'error_msg'	=>	'No Password Passed'
				));
				logtodb($send,1);
				echo $send;
			}
		}
		else
		{
			$send = json_encode(array(
				'error'	=>	'00002',
				'error_msg'	=>	'No Email Passed'
			));
			logtodb($send,1);
			echo $send;
		}
		
	}
	elseif(isset($_REQUEST['register']) || $_REQUEST['register'] == 1)
	{
		if(isset($_REQUEST['email']))
		{
			if(isset($_REQUEST['firstname']))
			{
				if(isset($_REQUEST['lastname']))
				{
					if(isset($_REQUEST['password']))
					{
						$post_email = $_REQUEST['email'];
						$post_firstname = $_REQUEST['firstname'];
						$post_lastname = $_REQUEST['lastname'];
						$post_password = $_REQUEST['password'];
						
						$register = $conn->prepare("INSERT INTO user (email, password, firstname, lastname, date_created) VALUES(:email, :password, :firstname, :lastname, :datetime)");
						$register->bindParam(':email', $post_email);
						$register->bindParam(':password', md5($post_password));
						$register->bindParam(':firstname', $post_firstname);
						$register->bindParam(':lastname', $post_lastname);
						$register->bindParam(':datetime', date("Y-m-d H:i:s"));
						$exec = $register->execute();
						
						if($exec == true)
						{
							$stmt = $conn->prepare('SELECT userid, email, password, firstname, lastname FROM user WHERE email = :email');
							$stmt->bindParam(':email', $post_email);
							$stmt->execute();
							$fetch = $stmt->fetch();
							
							$svr_userid = $fetch['userid'];
							$svr_email = $fetch['email'];
							$svr_firstname = ucfirst($fetch['firstname']);
							$svr_lastname = ucfirst($fetch['lastname']);
							$svr_password = $fetch['password'];
							
							$keyregister = $conn->prepare("INSERT INTO api_key (userid, key_data) VALUES(:userid, :keydata)");
							$keyregister->bindParam(':userid', $svr_userid);
							$keyregister->bindParam(':keydata', apikeygen());
							$exec = $keyregister->execute();
							
							/* Get Users Key */
							$key = $conn->prepare('SELECT key_data FROM api_key WHERE userid = :userid');
							$key->bindParam(':userid', $svr_userid);
							$key->execute();
							$fetchkey = $key->fetch();
							
							$user_key = $fetchkey['key_data'];
							
							$send = json_encode(array(
								'success'	=>	true,
								'userid'	=>	$svr_userid,
								'email'	=>	$svr_email,
								'password'	=>	$svr_password,
								'name'	=>	"{$svr_firstname} {$svr_lastname}",
								'created_at'	=>	date("Y-m-d H:i:s"),
								'api_key'	=>	$user_key
							));
							logtodb($send,1);
							echo $send;
						}
						else
						{
							$send = json_encode(array(
								'error'	=>	'00009',
								'error_msg'	=>	'Registration Failed'
							));	
							logtodb($send,1);
							echo $send;
						}
						
					}
					else
					{
						$send = json_encode(array(
							'error'	=>	'00008',
							'error_msg'	=>	'No Registration Password Passed'
						));	
						logtodb($send,1);
						echo $send;
					}
				}
				else
				{
					$send = json_encode(array(
						'error'	=>	'00007',
						'error_msg'	=>	'No Registration Lastname Passed'
					));	
					logtodb($send,1);
					echo $send;
				}
			}
			else
			{
				$send = json_encode(array(
					'error'	=>	'00006',
					'error_msg'	=>	'No Registration Firstname Passed'
				));
				logtodb($send,1);
				echo $send;
			}
		}
		else 
		{
			$send = json_encode(array(
				'error'	=>	'00005',
				'error_msg'	=>	'No Registration Email Passed'
			));
			logtodb($send,1);
			echo $send;
		}
	}
	/*
	 * Setup Online/Offline Status Change
	 */
	elseif(isset($_REQUEST['friendlist']))
	{
		if(isset($_REQUEST['userid']))
		{
			if(isset($_REQUEST['api_key']))
			{
				$getkey = $conn->prepare('SELECT key_data FROM api_key WHERE userid = :userid');
				$getkey->bindParam(':userid', $_REQUEST['userid']);
				$getkey->execute();
				$fetchsvrkey = $getkey->fetch();
				
				$svr_key_data = $fetchsvrkey['key_data'];
				
				if($_REQUEST['api_key'] == $svr_key_data)
				{
					
					/* Get Array of Friends for the Given Userid */
				
					$friendslist = $conn->prepare("SELECT user.userid, user.email, user.firstname, user.lastname  FROM user_friend INNER JOIN user ON user_friend.friendid = user.userid WHERE user_friend.userid = :userid");
					$friendslist->bindParam(':userid', $_REQUEST['userid']);
					$friendslist->execute();
					$ffl = $friendslist->fetchAll(PDO::FETCH_OBJ);
					
					$send = json_encode($ffl, JSON_FORCE_OBJECT);
					logtodb($send,1);
					echo $send;
					
				}
				
			}
		}
		
	}
	else 
	{
		$send = json_encode(array(
			'error'	=>	'00001',
			'error_msg'	=>	'No Method Passed'
		));
		logtodb($send,1);
		echo $send;
	}
?>