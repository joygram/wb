<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	$_debug = 0 ;
	if($_debug) ob_start() ;

	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	//$Row['title'] = "���� ����" ;
	$Row['title'] = _L_BOARD_ADMIN ;
	
	include("./html/board_header.html") ;
	$flist = new file_list("$C_base[dir]/board/conf", 1) ;
		//��ü �Խ��� ���� �˾Ƴ���
	$flist->read("conf.php", 0) ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "deleted") || 
			$file_name == "__global.conf.php")
		{
			continue ;
		}

		$nTotal++ ;
	}
	$flist->reset() ;

	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "deleted") || strstr($file_name, "__global.conf") )
		{
			continue ;
		}
	
		$board = explode(".", $file_name) ;
		$Row['no'] = $nTotal-$i ;
		$Row['board'] = $board[0] ;
		$i++ ;

		$idx_filename = file_exists("$C_base[dir]/board/data/$board[0]/data.idx")?"data.idx":"data.idx.php" ;

		if(!file_exists("$C_base[dir]/board/data/$board[0]/total.cnt")) continue ;
		$fp = wb_fopen("$C_base[dir]/board/data/$board[0]/total.cnt","r") ;
		$cnt = fgets($fp, 1024) ;
		fclose($fp) ;
		if(empty($cnt)) 
		{
			$cnt = 0 ;
		}
		$Row['cnt'] = $cnt ;
		$Row['setup'] = "read_config.php?data=$file_name" ;

		$PREVIEW_URL = "$C_base[url]/board/list.php?data=$board[0]" ; 
		$DEL_URL    = "javascript:onClick=Confirm(\"board_del.php?data=$board[0]\",\"$board[0]\",\"del\"); " ;
		//$CONFIG_URL = "javascript:onClick=POP(\"board_open_config.php?conf_name=$file_name\"); " ; 
		$CONFIG_URL = "./config_open.php?conf_name=$file_name&dock=on" ; 
		if( $board[0] == "default" ) 
		{
			$DEL_URL = "#" ;
		}
		include("./html/board_list.html") ;
	}
	include("./html/board_footer.html") ;
?>
