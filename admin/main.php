<?php
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; 

	// 시스템 변수등의 호환성을 위해. 2003/12/28
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	$_debug = 0 ;
	if($_debug) echo("main.php:check_data[$check_data]<br>") ;

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	if(@file_exists("$C_base[dir]/release_no"))
	{
		//버젼 검사
		$cont = file("$C_base[dir]/release_no") ;
		$installed_release_no = chop($cont[0]) ;
		$installed_ver_str = chop($cont[1]) ;
		$installed_ver = chop($cont[2]) ;
	}
	else
	{
		err_abort("$C_base[dir]/release_no "._L_NOFILE ) ;
	}

	$Row['base_dir'] = $C_base[dir] ;
	$Row['base_url'] = $C_base[url] ;
	$Row['version'] = $installed_ver_str ;
	$Row['release'] = $installed_release_no ;
	$Row['alias'] = $auth->alias() ;

	//쿠키 설정 하여 하루에 한번씩만 점검하도록 한다.
	$update_check = $__COOKIE["cw_update_check"] ;
	$wb_ver_str = $__COOKIE["cw_ver_str"] ;


	if( (time()-$update_check) > 60*60*24 || empty($update_check) )
	{
		$update_check = time() ;

		//이 파일 안에 release_no와 최근 갱신 사항을 넣어둔다.
		$Row['news'] = 		@file_get_contents("http://whitebbs.net/update/news.{$C_base[language]}") ;

		//1,2,3줄에 release_no와 같은 순서의 정보가 들어간다.
		$news_array = explode("\n", $Row['news']) ;

		$wb_ver_str = $news_array[1] ;
		if($news_array[0] > $Row['release']) 
		{
				//새 갱신 정보 출력 준비
				//release정보제거
			$news_array[0] = "" ; // release_no
			$news_array[1] = "" ; // ver_str
			$news_array[2] = "" ; // ver_no
			$Row['news'] = implode("\n", $news_array) ;
		}
		else
		{
			$Row['news'] = "" ;
		}

		@setcookie("cw_update_check",  $update_check,    time()+604800, "/") ;
		@setcookie("cw_ver_str",  $wb_ver_str,    time()+604800, "/") ;
	}

	$Row['wb_version'] = $wb_ver_str ;
	include("./html/main_header.html") ;

?>

