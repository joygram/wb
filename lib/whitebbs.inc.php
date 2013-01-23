<?php
if(!defined("__wbbs_inc__")) define("__wbbs_inc__","1") ;
else return ;

$C_base["dir"] = $C_base_dir ; //$C_basedir은 외부에서 지정
include("{$C_base["dir"]}/system.ini.php") ;

if($C_use_member == "on")
{
	//$conf[auth_perm] = "7555" ; // $C_auth_perm은 외부에서 지정 ;
	$conf[auth_perm] = $C_auth_perm ; // $C_auth_perm은 외부에서 지정 ;
	$_data = "member" ;
	//관리자도구에서 나온 기본 디렉토리 설정
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	//변수 복사 필요...
	// get_base의 변조 필요
	$C_base = get_base(-1) ;
	//$C_base[member_db_type] = $C_member_db_type ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	$sess = $auth->member_info() ;
	$hide = make_comment($_data, $sess, NOT_USE, "member") ;
}

if($C_use_counter == "on")
{
	if(!empty($counter_data))
	{
		include("{$C_base["dir"]}/counter/counter.php") ;
	}
}
?>
