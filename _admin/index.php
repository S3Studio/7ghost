<?php
	header('Content-Type: text/html; charset=gbk');
	/**
	 * ����ļ�
	 */
	//error_reporting(0);
	$_GET['m'] = empty($_GET['m'])?'index':$_GET['m'];
	 //�ж�����
	include_once ('./init.php');
	if(!ctype_alnum($_GET['m'])){
		echo 'm error';exit;
	}
	session_start();
	//�ж��Ƿ��¼
	if(empty($_SESSION['7g_edaa10f6_logined'])){
		include ('./module/index.php');
		$m = new index();
		$m->actionLogin();
		exit();
	}
	if(@include ('./module/'.$_GET['m'].'.php')){
		$m = new $_GET['m']();
	 	$a = empty($_GET['a'])?'actionIndex':'action'.$_GET['a'];
	 	$m->$a();
	}
