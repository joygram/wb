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

	$Row['title'] = "카운터 관리" ;
	include("./html/counter_header.html") ;
	$flist = new file_list("$C_base[dir]/counter/conf", 1) ;
		//전체 게시판 개수 알아내기
	$nTotal = 0 ;
	$flist->read("conf.php", 0) ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "deleted") || $file_name == "__global.conf.php")
		{
			continue ;
		}
		$nTotal++ ;
	}
	$flist->reset() ;
	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "deleted") || $file_name == "__global.conf.php" )
		{
			continue ;
		}

		$board = explode(".", $file_name) ;
		$Row[no] = $nTotal-$i ;
		$Row[board] = $board[0] ;
		$i++ ;

		$idx_filename = file_exists("$C_base[dir]/counter/data/$board[0]/data.idx")?"data.idx":"data.idx.php" ;
		$Row[setup] = "read_config.php?data=$file_name" ;

		$PREVIEW_URL = "$C_base[url]/counter/counter.php?data=$board[0]" ; 
		$STAT_URL = "$C_base[url]/admin/counter/stat.php?data=$board[0]&dock=yes" ; 
		$DEL_URL    = "javascript:onClick=Confirm(\"counter_del.php?data=$board[0]\",\"$board[0]\",\"del\"); " ;
		//$CONFIG_URL = "javascript:onClick=POP(\"counter_open_config.php?conf_name=$file_name\"); " ; 
		$CONFIG_URL = "./config_open.php?conf_name=$file_name&dock=on" ; 
		if( $board[0] == "default" ) 
		{
			$DEL_URL = "#" ;
		}
		include("./html/counter_list.html") ;
	}
	include("./html/counter_footer.html") ;
?>
