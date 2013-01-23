<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	$_debug = 0 ;
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2, "on") ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	umask(0000) ;
	switch($part)
	{
		case "news":
			$target_dir = "$C_base[dir]/board/plugin/__global/news" ;
			$Row[title] = _L_LATEST ;
			break ;
		case "category":
			$target_dir = "$C_base[dir]/board/plugin/__global/category" ;
			$title = _L_CATEGORY ;
			break ;
		case "pagebar":
			$target_dir = "$C_base[dir]/board/plugin/__global/pagebar" ;
			$title = _L_PAGEBAR ;
			break ;
		default:
			$target_dir = "$C_base[dir]/board/plugin" ;
			$title = _L_BOARD ;
			break ;
	}
	$Row[title] = $title." "._L_PLUGIN_MANAGE ;
	include("./html/plugin_header.html") ;
	if ($_debug) echo("target_dir[$target_dir]<br>") ;	

		// 스킨 디렉토리 이름을 읽어서 출력해주기
	$flist = new file_list($target_dir, 1) ;
	$flist->read("*", 0) ;
		//설치된 스킨 카운트
	$nTotal = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || $file_name == "__global" || eregi("deleted", $file_name))
		{
			continue ;
		}
		$nTotal++ ;
	}

	$flist->reset() ;
	while( ($file_name = $flist->next()) )
	{
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || $file_name == "__global" ||  eregi("deleted", $file_name))
		{
			continue ;
		}

		if ($_debug) echo("file_name[$file_name]") ;
		if (empty($part)) 
		{
			$_plugin_dir = "$C_base[dir]/board/plugin/$file_name" ;
			$_plugin_url = "$C_base[url]/board/plugin/$file_name" ;
		}
		else
		{
			$_plugin_dir = "$C_base[dir]/board/plugin/__global/$part/$file_name" ;
			$_plugin_url = "$C_base[url]/board/plugin/__global/$part/$file_name" ;
		}

		$i++ ;
		$board = explode(".", $file_name) ;
		$Row[no] = $nTotal-$i+1 ;
		$Row[board] = $board[0] ;

		$Row[cnt] = $cnt ;
		$Row[setup] = "read_config.php?data=$file_name" ;

		//$PREVIEW_URL = "$C_base[url]/board/list.php?data=$board[0]" ; 
		$PREVIEW_URL = "#" ;
		$DEL_URL    = "javascript:onClick=Confirm(\"plugin_del.php?data=$board[0]\",\"$board[0]\",\"del\"); " ;
		//$CONFIG_URL = "./board_open_config.php?conf=$file_name&dock=on" ; 

		ob_start() ;
		@readfile("$C_base[dir]/board/plugin/$file_name/author.txt") ;
		$tmp = ob_get_contents() ;
		ob_end_clean() ;
		$tmp = str_replace("<", "&lt;", $tmp) ;
		$tmp = str_replace(">", "&gt;", $tmp) ;
		$tmp_arr = explode("|", $tmp) ;

		$Row[author]       = $tmp_arr[0] ;
		$Row[author_email] = $tmp_arr[1] ;
		$AUTHOR_URL        = $tmp_arr[2] ;

		$hide['author_url'] = "" ;
		$hide['/author_url'] = "" ;
		if(empty($AUTHOR_URL))
		{
			$hide['author_url'] = "<!--\n" ;
			$hide['/author_url'] = "-->\n" ;
		}
		else if(!eregi("http://", $AUTHOR_URL))
		{
			$AUTHOR_URL = "http://".$AUTHOR_URL ;
		}

		$hide['author_email'] = "" ;
		$hide['/author_email'] = "" ;
		if(empty($Row[author_email]))
		{
			$hide['author_email'] = "<!--\n" ;
			$hide['/author_email'] = "-->\n" ;
		}

		$hide['readme'] =  "" ;
		$hide['/readme'] = "" ;
		if(!file_exists("$C_base[dir]/board/plugin/$file_name/readme.txt"))
		{
			$hide['readme'] =  "<!--\n" ;
			$hide['/readme'] = "-->\n" ;
		}
		else
		{
			$README_URL = "$C_base[url]/board/plugin/$file_name/readme.txt" ;
		}

		$DEL_URL    = "javascript:onClick=Confirm(\"plugin_del.php?data=$board[0]&part=$part\",\"$board[0]\",\"del\"); " ;
		$hide['del'] = "" ;
		if(!is_writeable($_plugin_dir))
		{
			$DEL_URL = "" ;
			$hide['del'] = "<!--\n" ;
			$hide['/del'] = "-->\n" ;
		}
		include("./html/plugin_list.html") ;
	}

	$Row[title] = $title ;
	$Row[part] = $part ;
	include("./html/plugin_footer.html") ;
?>
