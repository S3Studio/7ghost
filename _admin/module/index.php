<?php
class index{
	function actionIndex(){
		header("Location:./?m=site&a=Index");
		exit();
		//include tpl('index');
	}
	
	function actionLogin(){
		if($_SERVER['REQUEST_METHOD'] == "POST"){
			if(d('config')->get('password')==$_POST['password']){
				$_SESSION['7g_edaa10f6_logined']=true;
				if($_GET['fwd']=='1'){
					header("Location:..");
				}else{
					header("Location:./?m=site&a=index");
				}
				exit();
			}
			echo "√‹¬Î¥ÌŒÛ";
		}
		include tpl('login');
	}
	
	function actionLogout(){
		unset($_SESSION['7g_edaa10f6_logined']);
		header("Location:./");
		exit();
	}
}
