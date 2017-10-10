<?php
	include_once ('./init.php');
	session_start();
	
	$emt = empty($_SESSION['7g_edaa10f6_logined']);
	if ($emt) {
		//ÅĞ¶ÏÊÇ·ñÔÊĞíµÇÂ¼
		if($_SERVER['REQUEST_METHOD'] == "POST"){
			if(d('config')->get('password')==$_POST['password']){
				$_SESSION['7g_edaa10f6_logined']=true;
				$emt = false;
			} else {
				echo "ÃÜÂë´íÎó";
			}
		}
	}
	
	if (!$emt) {
		// logined
		header("Location:/");
		exit();
	}
	
	header('Content-Type: text/html; charset=gbk');
	include tpl('login');
