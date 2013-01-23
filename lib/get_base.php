<?php
if(!defined("__wb_get_base__")) define("__wb_get_base__","1") ;
else return ;
/**
시스템 전반에 사용할 기본 URL설정
반드시 setup.php, admin/index.php에서 수행하도록 한다.
없거나 달라지면 conf/system.conf.php에 새로 기록한다.
-> /system.ini.php
conf에 위치한 상대경로를 입력해 둔다.
$depth_level은 시스템의 경로에서 들어간 깊이를 의미한다.
*/

//시스템 전역에서 사용할 수 있도록 
//@todo 확실히 필요한 것인지 잘 모르겠음.
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
/**
php 4.1.2이상에서 사용가능과
php 4.1.2이하에서 호환성을 유지하도록 자체적으로 변수를 사용
get_base가 가장 처음에 호출하는 함수 이므로 위치를 이곳에 두었음.
@param  GET,SET  변수를  기존 서버 변수로  갱신 하는 부분  추가 
@param  세션에 등록 하기 위해  사용하는 전역변수목록 을 넣어주기 위해서. 반드시  맨 끝에 , 를 넣어준다. 1
*/


function prepare_server_vars( $cmd = "GET", $global_param = "" )
{
	session_start() ;
	$_debug = 0 ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	global $_SERVER ; //서버변수가 존재하는지 확인하기 위해서 global = off인경우...
	
	//전역설정 
	$pre_globals = '$HTTP_SERVER_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_POST_FILES, $HTTP_ENV_VARS, $HTTP_SESSION_VARS' ;
	$new_globals = '$_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_ENV, $_SESSION ' ;

	//사용할 변수의 전역설정.
	if(!isset($_SERVER))	// 4.1 미만
	{
		$global_param = "$global_param $pre_globals"  ;
	}
	else  // 4.1 이상 
	{
		$global_param = "$global_param $new_globals"  ;
	}
	$global = "global $global_param  ; " ;
	eval( $global ) ;
	
	if( $cmd == "GET" ) 
	{
		if(!isset($_SERVER))
		{
			$__SERVER  = $HTTP_SERVER_VARS ;
			$__GET     = $HTTP_GET_VARS ;
			$__POST    = $HTTP_POST_VARS ;
			$__COOKIE  = $HTTP_COOKIE_VARS ;
			$__FILES   = $HTTP_POST_FILES ;
			$__ENV     = $HTTP_ENV_VARS ;
			$__SESSION = $HTTP_SESSION_VARS ;
		}
		else
		{
			if ($_debug) echo("your php may be 4.1. higher<br>") ;
			$__SERVER	= $_SERVER ;
			$__GET		= $_GET ;
			$__POST		= $_POST ;
			$__COOKIE	= $_COOKIE ;
			$__FILES		= $_FILES ;
			$__ENV		= $_ENV ;
			$__SESSION	= $_SESSION ;
		}
	}
	else // SET
	{
		if(!isset($_SERVER))
		{
			$HTTP_SERVER_VARS  = $__SERVER ;
			$HTTP_GET_VARS = $__GET ;
			$HTTP_POST_VARS = $__POST ;
			$HTTP_COOKIE_VARS = $__COOKIE ;
			$HTTP_POST_FILES = $__FILES ;
			$HTTP_ENV_VARS = $__ENV ;
			$HTTP_SESSION_VARS = $__SESSION ;
		}
		else
		{
			$_SERVER	= $__SERVER ;
			$_GET		= $__GET ;
			$_POST 		= $__POST ;
			$_COOKIE	= $__COOKIE ;
			$_FILES		= $__FILES ;
			$_ENV		= $__ENV ;
			$_SESSION	= $__SESSION ;
			
		}
	} //if( $cmd = "GET" ) 
	
	//전역으로 eval로 설정할 수 있도록 문자열 리턴
	//받은 문자열을 추가할 때는 항상 앞에다 붙이도록 하자.
	return '$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION' ;
}


function check_phpversion($version)
{
        // intval used for version like "4.0.4pl1"
        $testVer=intval(str_replace(".", "",$version));
        $curVer=intval(str_replace(".", "",phpversion()));
        if( $curVer < $testVer )
                return false;
        return true;
}

function wb_charset($language)
{
        $_debug = 0 ;

        $charset = array(
                "" => "",
                "en"            => "iso-8859-1",
                "ko"            => "euc-kr",
                "euc-jp"        => "euc-jp",
                "jis"           => "iso-2022-jp",
                "shitf_jis"     => "shitf_jis",
                "euc-cn"        => "euc-cn",
                "big5"          => "big5",
                "utf-8"         => "utf-8" ) ;

        if($_debug) echo("wb_charset[$language][{$charset['$language']}]<br>") ;

        if(empty($language) || empty($charset[$language]))
        {
                $language = "ko" ;
        }

        return $charset[$language] ;
}


function get_base($depth_level=0, $update="off")
{
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	global $PHP_SELF ;
	global $SERVER_SOFTWARE ;
	global $SCRIPT_NAME ;
	global $SERVER_NAME ;
	global $C_base ;

	prepare_server_vars() ;
	
	$_debug = 0 ;
	umask(0000) ;
	$base = array("") ;
	
	if($_debug) ob_start() ;
	
	// Define Server OS
	$iswin = (substr(PHP_OS, 0, 3) == 'WIN')?1:0 ;
	$base['os'] = $iswin?"Win":"Unix" ;
	if($_debug) echo ("base[os]: $base[os]<br>") ;
	
	// GET BASE URL
	$SERVER_NAME = $__SERVER["SERVER_NAME"] ;

	if($__SERVER["SERVER_PORT"] != "80") 
		$SERVER_NAME .= ":".$__SERVER["SERVER_PORT"] ;

	$SCRIPT_NAME = empty($SCRIPT_NAME)?$__SERVER["SCRIPT_NAME"]:$SCRIPT_NAME ;
	
	if($_debug) echo("SCRIPT_NAME:$SCRIPT_NAME, PHP_SELF:$PHP_SELF<br>") ;

	//PHP가 CGI모드일경우에는 SCRIPT이름이 엉뚱하게 나온다.
	//REQUEST_URI를 활용하는 방안도 고려 2002/08/15
	if ($SCRIPT_NAME != $PHP_SELF && !empty($PHP_SELF))
	{
		if($_debug) echo("equal SCRIPT_NAME[$SCRIPT_NAME], PHP_SELF[$PHP_SELF]<br>") ;
		$url_array = explode("/", $PHP_SELF) ;
	}
	else
	{
		$url_array = explode("/", $SCRIPT_NAME) ;
		if($_debug) echo("select SCRIPT_NAME<br>") ;
	}
	unset($url_array[sizeof($url_array)]) ; //마지막 파일명 제거
	$max_no = sizeof($url_array)-1 ;
	for($i = 0 ; $i < $depth_level+1 ; $i++)
	{
		unset($url_array[$max_no]) ;
		$max_no-- ;
	}

	if($_debug) print_r($url_array) ;
	$base_url = implode("/", $url_array) ;
	$base_url = empty($base_url)?"/":$base_url ;
	$base_url = ($base_url == "/")?"":$base_url ;
	$base_url = "http://$SERVER_NAME$base_url" ;
	if($_debug) echo("IMPLODE base_url[$base_url]<br>") ;
	
	        // GET BASE DIRECTORY
	$pwd = getcwd() ;
	if($_debug) echo("[PWD $pwd]") ;
	if(empty($pwd) && check_phpversion("4.1.0"))
	{
		if($_debug) echo("get another way cwd...<br>") ;
		
		$script_name = $_SERVER["SCRIPT_FILENAME"] ;
		if($_debug) echo("[script_name]$script_name<br>") ;
		
		if($_debug) echo("[$SERVER_SOFTWARE][$iswin]<br>") ;
		if($iswin)
		{
		        $pwd = str_replace('\\', '/', $script_name) ;
		        if($_debug) echo("is windows: pwd[$script_name]") ;
		}
		
		$path_array = explode("/", $script_name) ;
		
		$max_no = sizeof($path_array)-1 ;
		
		        // script name delete
		unset($path_array[$max_no]) ;
		$max_no-- ;
	}
	else
	{
		if($_debug) echo("[$SERVER_SOFTWARE][$iswin]<br>") ;
		if($iswin)
		{
		        $pwd = str_replace('\\', '/', $pwd) ;
		        if($_debug) echo("is windows: pwd[$pwd]") ;
		}
		
		$path_array = explode("/", $pwd) ;
		
		$max_no = sizeof($path_array)-1 ;
	}
	
	for($i = 0 ; $i < $depth_level ; $i++)
	{
		unset($path_array[$max_no]) ;
		$max_no-- ;
	}
	
	$base_dir = implode("/", $path_array) ;

	if($_debug) echo("[$base_dir]<br>") ;

	$base_dir = empty($base_dir)?"/":$base_dir ;

	if($_debug) echo("[$base_dir]<br>") ;

	//
	if( isset($C_base["dir"]) )
	{
		$base_dir = $C_base["dir"] ;
	}

	$system_conf = "{$base_dir}/system.ini.php" ;
	@include($system_conf) ;
	$ini['use_board'] = $C_use_board    ;
	$ini['use_counter'] = $C_use_counter  ;
	$ini['use_member'] = $C_use_member   ;
	$ini['mail'] = $C_use_mail     ;
	$ini['language'] = $C_language ;
	$ini['timezone'] = $C_timezone ;
	$ini['lang'] = $C_lang ;
	$ini['uniq_num'] = $C_uniq_num ;
	
	if($_debug) echo("SYSTEM_CONF[$system_conf]<br>") ;
	
	//setting charset
	ini_set("default_charset", $C_lang) ;
	
	if($_debug) echo("base_url[$base_url]<br>\n") ;
	
	//비어있고 같지 않으면 다시 생성해준다.
	//if depth_level minus then use system_conf
	if( (empty($C_base_url) ||
	    $C_base_url != $base_url ||
	    empty($C_base_dir) ||
	    $C_base_dir != $base_dir) && $depth_level >= 0 )
	{
		if($_debug) echo("C_base_url is empty<br>\n") ;
		if($_debug) echo("create $system_conf<br>\n") ;
		
		if($_debug) echo("BASE_URL[$C_base_url]<br>\n") ;
		if( $update != "off" )
		{
		        $ini["base_url"] = $base_url ;
		        $ini["base_dir"] = $base_dir ;
		        save_system_ini($system_conf, $ini) ;
		}
		$base["url"] = $base_url ;
		$base["dir"] = $base_dir ;
	}
	else
	{
		if($_debug) echo("system.conf.php read...<br>\n") ;
		if($_debug) echo("[$C_base_url]<br>\n") ;
		$base["url"] = $C_base_url ;
		$base["dir"] = $C_base_dir ;
	}

	$base["board_db_type"]  = $C_board_db_type ;
	$base["member_db_type"] = $C_member_db_type ;
	$base["db_type"] = $C_db_type ;
	$base["lang"] = $C_lang ;
	$base["language"] = $C_language ;

	// 관리자에서 보드와 카운터 사용 여부 초기화 되는것 해결 2004.3.8
	$base["use_board"] = $C_use_board ;
	$base["use_counter"] = $C_use_counter ;
	$base["use_member"] = $C_member ;
	$base["use_mail"] = $C_mail ;

	// 여기까지 부분 추가
	$base["uniq_num"] = $C_uniq_num ;
	
	if($_debug) echo("before set default_charset:[".ini_get("default_charset")."]<br>") ;
	$_charset = wb_charset($C_language) ;
	ini_set("default_charset", $_charset) ;
	
	if($_debug) echo("default_charset:[".ini_get("default_charset")."]<br>") ;
	if($_debug) echo("base_url:[$base[url]] base_dir:[$base[dir]]<br>") ;
	return $base ;
}
?>
