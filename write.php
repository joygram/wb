<?php
	include_once("lib/system_ini.php") ;
	include_once("lib/get_base.php") ;
	$C_base = get_base(0) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	include("lib/wb.inc.php") ;
	include("${C_base[dir]}/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행

	$url = "board/write.php?auth_param=$auth_param" ;
	redirect($url) ;
?>
