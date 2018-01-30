<?php
	//error_reporting(0);
	require './_admin/init.php';
	//��ȡ������Ϣ
	$config  = d('config')->get();
	
	//�ж��Ƿ��¼
	session_start();
	if($config['alwaysPwd'] && empty($_SESSION['logined'])){
		header("Location:./_admin/?fwd=1");
		exit();
	}
	
	//��ǰ��url
	$rootUrl = $_SERVER['HTTP_HOST'].siteUri();
	$snoopy = new Snoopy();
	$snoopy->maxredirs = 0;
	$uri = substr($_SERVER['REQUEST_URI'],strlen(siteUri()));
	//ƥ���Զ���ҳ�棬�ϲ�����
	foreach($config['pages'] as $page){
		if(@ereg($page['uri'],$uri)){
			if(!empty($page['replaces'])){
				$config['replaces'] = array_merge($config['replaces'],$page['replaces']);
			}
			if(!empty($page['host'])){
				$uri = substr($uri,strlen(dirname($page['uri']))+1);
			}else{
				unset($page['host']);
			}
			unset($config['pages']);
			$config = array_merge($config,$page);
			break;
		}
	}
	
	//�ж���δ���HTTPS
	if(empty($config['sslMode'])){
		$SSL_PREFIX = '';
		$SSL_PROTOCAL = 'https://';
		$isSSL = !empty($_SERVER['HTTPS']);
	}else{
		$SSL_PREFIX = '7gssl/';
		$SSL_PROTOCAL = 'http://';
		$isSSL = str_starts_with($uri,$SSL_PREFIX);
	}
	
	//��ȡҪ�����url
	$raw_host = $config['host'];
	if(str_starts_with($raw_host,'http://')){
		$raw_host = substr($raw_host,strlen('http://'));
	}
	if(str_starts_with($raw_host,'https://')){
		$raw_host = substr($raw_host,strlen('https://'));
	}
	if($isSSL){
		$url = 'https://'.$raw_host.substr($uri,strlen($SSL_PREFIX));
	}else{
		$url = 'http://'.$raw_host.$uri;
	}
	
	//��ǰ������ļ���׺
	$thisExt = pathinfo($_SERVER['PATH_INFO'],PATHINFO_EXTENSION);
	//��̬�ļ�
	if($config['static'] == 0 && in_array($thisExt,explode("|",$config['diyStatic']))){
		$filename = dirname(ADIR).$_SERVER['PATH_INFO'];
		//������ڣ�ֱ�����
		if(is_file($filename)){
			echo file_get_contents($filename);
			exit();
		}
	}
//-------------��������ͷ��Ϣ------------
	//����cookie
	switch($config['cookies']){
		case 1://ȫ��cookies
			$snoopy->cookies = get_cache('cookies');
			break;
		case 2://�Զ���COOKIES
			$snoopy->cookies = $config['diyCookies'];
			break;
		default://��ͳcookies
			$snoopy->cookies = $_COOKIE;
			break;
	}
	
	//����agent
	switch($config['agent']){
		case 1://��α��
			break;
		case 2://�Զ���agent
			$snoopy->agent = $config['diyAgent'];
			break;
		default://ʹ�ÿͻ���agent
			$snoopy->agent = $_SERVER['HTTP_USER_AGENT'];
			break;
	}
	
	
	//����referer
	switch($config['referer']){
		case 1://�Զ���referer
			$snoopy->referer = $config['diyReferer'];;
			break;
		default://�Զ�α��
			$snoopy->referer = str_replace('http://'.$rootUrl,'http://'.$raw_host,$_SERVER['HTTP_REFERER']);
			$snoopy->referer = str_replace('https://'.$rootUrl,'https://'.$raw_host,$_SERVER['HTTP_REFERER']);
			if($snoopy->referer==$_SERVER['HTTP_REFERER'])
			$snoopy->referer = '';
			break;
	}
	
	//����ip
	switch($config['ip']){
		case 1://ʹ�ÿͻ���ip
			$snoopy->rawheaders["X_FORWARDED_FOR"] = get_ip(); //αװip 
			break;
		case 2://�Զ���ip
			$snoopy->referer = $config['diyReferer'];;
			break;
		default://ʹ�÷�����ip
			break;
	}
	
	//-------����ͷ��Ϣ begin--
	
	//-------����ͷ��Ϣ end----
	
	//�Ƿ�ȫ����
	$snoopy->expandlinks = true;
	
//--------------ץȡ��ҳ-----------------
	//�ж���POST����GET
	
	if($_SERVER['REQUEST_METHOD']=="POST"){
		$snoopy->submit($url,$_POST);
	}else{
		$snoopy->fetch($url);
	}
//---------------��������Ϣ------------
		//����cookie
	switch($config['cookies']){
		case 1://ȫ��cookies
			$snoopy->cookies = set_cache('cookies');
			break;
		default:
			break;
	}
	
	//�滻header�е�����
	if(empty($config['replaceDomain'])){
		$replaced_header = $snoopy->headers;
		foreach($replaced_header as &$eachheader){
			$eachheader = str_replace('http://'.$raw_host,'http://'.$rootUrl,$eachheader);
			$eachheader = str_replace('https://'.$raw_host,$SSL_PROTOCAL.$rootUrl.$SSL_PREFIX,$eachheader);
		}
		unset($eachheader);
	}
	
	$contentType = send_header($replaced_header);
	$charset = empty($contentType[1])?'utf-8':$contentType[1];
	$charset = trim($charset,"\n\r");
	
	//�滻���� relativeHTML relativeCSS
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('','php','html'))){
			//�滻����
			$snoopy->results = str_replace('http://'.$raw_host,'http://'.$rootUrl,$snoopy->results);
			$snoopy->results = str_replace('https://'.$raw_host,$SSL_PROTOCAL.$rootUrl.$SSL_PREFIX,$snoopy->results);
		}
	}
	
	//�滻��Ե�ַrelativeHTML
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('','php','html'))){
			if($isSSL){
				$snoopy->results = preg_replace('/="\/(?!\/)/','="'.siteUri().$SSL_PREFIX,$snoopy->results);
				$snoopy->results = preg_replace('/=\'\/(?!\/)/','=\''.siteUri().$SSL_PREFIX,$snoopy->results);
			}else{
				$snoopy->results = preg_replace('/="\/(?!\/)/','="'.siteUri(),$snoopy->results);
				$snoopy->results = preg_replace('/=\'\/(?!\/)/','=\''.siteUri(),$snoopy->results);
			}
			//����ȷ����ν���SSLת��
			$snoopy->results = preg_replace('/<base href=.*?\/>/','',$snoopy->results);
			$snoopy->results = preg_replace('/<base href=.*?>.*<\/base>/','',$snoopy->results);
		}
	}
	
	//�滻CSS��Ե�ַ
	if(empty($config['relativeCSS'])){
		if(in_array($thisExt,array('css'))){
			if($isSSL){
				$snoopy->results = preg_replace('/url\("\/(?!\/)/','url("'.siteUri().$SSL_PREFIX,$snoopy->results);
				$snoopy->results = preg_replace('/url\(\/(?!\/)/','url('.siteUri().$SSL_PREFIX,$snoopy->results);
			}else{
				$snoopy->results = preg_replace('/url\("\/(?!\/)/','url("'.siteUri(),$snoopy->results);
				$snoopy->results = preg_replace('/url\(\/(?!\/)/','url('.siteUri(),$snoopy->results);
			}
		}
	}
	
	//�����滻
	if(is_array($config['replaces'])&&!empty($config['replaces']))
	
	foreach($config['replaces'] as $replace){
		$seach = addcslashes(iconv("gb2312",$charset,v($replace['seach'])),'/');
		$replace = iconv("GB2312",$charset,v($replace['replace']));
		$snoopy->results = preg_replace('/'.$seach.'/',$replace,$snoopy->results);
	}
	
	//ģ��
	if(!empty($config['template'])){
		@include(ADIR.'data/tpl/'.$config['template']);
		exit();
	}
	//��̬�ļ�
	if($config['static'] == 0 && in_array($thisExt,explode("|",$config['diyStatic']))){
		$filename = dirname(ADIR).$_SERVER['PATH_INFO'];
		save_file($filename,$snoopy->results);
	}
	
	//���
	echo $snoopy->results;
	//echo htmlspecialchars($snoopy->results);
