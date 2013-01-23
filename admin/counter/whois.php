<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	$_debug = 0 ;
	if($_debug) ob_start() ;

	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	include("$C_base[dir]/admin/counter/html/whois_header.html") ;
	$Row['func'] = nl2br(whois($ip)) ;	
	include("$C_base[dir]/admin/counter/html/whois_list.html") ;
	include("$C_base[dir]/admin/counter/html/whois_footer.html") ;
?>
