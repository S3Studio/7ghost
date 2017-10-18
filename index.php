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
	$rootUrl = 'http://'.$_SERVER['HTTP_HOST'].siteUri();
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
	//��ȡҪ�����url
	$url = $config['host'].$uri;
	//��ǰ������ļ���׺
	$thisExt = pathinfo($_SERVER['PATH_INFO'],PATHINFO_EXTENSION);
	//��̬�ļ�
	if(in_array($thisExt,explode("|",$config['diyStatic']))){
		$filename = dirname(ADIR).'/'.substr($_SERVER['REDIRECT_URL'],strlen(siteUri()));
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
			$snoopy->referer = str_replace($rootUrl,$config['host'],$_SERVER['HTTP_REFERER']);
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
	$replaced_header = $snoopy->headers;
	foreach($replaced_header as &$eachheader){
		$eachheader = str_replace($config['host'],$rootUrl,$eachheader);
	}
	unset($eachheader);
	
	$contentType = send_header($replaced_header);
	$charset = empty($contentType[1])?'utf-8':$contentType[1];
	$charset = trim($charset,"\n\r");
	
	//�滻���� relativeHTML relativeCSS
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('','php','html'))){
			//�滻����
			$snoopy->results = str_replace($config['host'],$rootUrl,$snoopy->results);
		}
	}
	
	//�滻��Ե�ַrelativeHTML
	if(empty($config['replaceDomain'])){
		if(in_array($thisExt,array('','php','html'))){
			$snoopy->results = preg_replace('/="\/?!\//','="'.siteUri(),$snoopy->results);
			$snoopy->results = preg_replace('/=\'\/?!\//','=\''.siteUri(),$snoopy->results);
			$snoopy->results = preg_replace('/<base href=.*?\/>/','',$snoopy->results);
		}
	}
	
	//�滻CSS��Ե�ַ
	if(empty($config['relativeCSS'])){
		if(in_array($thisExt,array('css'))){
			$snoopy->results = preg_replace('/url\("\/?!\//','url("'.siteUri(),$snoopy->results);
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
	if(in_array($thisExt,explode("|",$config['diyStatic']))){
		$filename = dirname(ADIR).'/'.substr($_SERVER['REDIRECT_URL'],strlen(siteUri()));
		save_file($filename,$snoopy->results);
	}
	
	//���
	echo $snoopy->results;
	//echo htmlspecialchars($snoopy->results);
