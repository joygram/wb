<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1, "on") ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	umask(0000) ;
	$_debug = 0 ;
	if($_debug) echo("menu.php:check_data[$check_data]<br>") ;

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	include("../system.ini.php") ;

	require_once("./html/menu_header.html") ;

	$_debug = 0 ;
	///////////////////////// 
	//상단 메뉴(폴더)/////////////
	$tree_idx = 0 ;
	$menu_num = 1 ;
	$parent_idx = 0 ;
	$Row[title] = _L_SYSTEM_SETUP ;
	$Row[url] = "" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;
	$parent_idx = $menu_num ;

	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = _L_LANGUAGE ;
	$Row[url] = "setup/language.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;

	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = "DB" ;
	$Row[title] = _L_DATABASE ;
	$Row[url] = "setup/db.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;

	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = _L_ADMIN ;
	$Row[url] = "setup/admin.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;

	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = _L_TIMEZONE ; 
	$Row[url] = "setup/timezone.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;

	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = _L_PROGRAM ;
	$Row[url] = "setup/program.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;

	/*
	$tree_idx++ ;
	$menu_num++ ;
	$Row[title] = "테마" ;
	$Row[title] = "_L_THEME" ;
	$Row[url] = "setup/theme.php" ;
	$Row[url_target] = "mainFrame" ;
	$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
	include("./html/menu_list.html") ;	
	*/

	if ($_debug) echo("C_use_board[$C_use_board]<br>") ;
	if($C_use_board == "on")
	{
		//상단 메뉴(폴더)
		$tree_idx++ ;
		$menu_num++ ;
		$parent_idx = 0 ;
		$Row[title] = _L_BOARD ;
		$Row[url] = "" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;
		$parent_idx = $menu_num ; 

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_BOARD ;
		$Row[url] = "board/board.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_SKIN ;
		$Row[url] = "board/skin.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_PARTSKIN ;
		$Row[url] = "" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;
		//부분스킨의 하위메뉴로 갔다가 사촌메뉴를 사용할 수 있으므로 저장한다. 
		$prev_parent_idx = $parent_idx ; 
		$parent_idx = $menu_num ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_LATEST ;
		$Row[url] = "board/skin.php?part=news" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_CATEGORY ;
		$Row[url] = "board/skin.php?part=category" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_PAGEBAR ;
		$Row[url] = "board/skin.php?part=pagebar" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		//이 아래로 사촌메뉴를 사용하기위한 준비. 
		$parent_idx = $prev_parent_idx ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_GLOBALSETUP ;
		$Row[url] = "board/config_open.php?conf_name=__global.conf.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_PLUGIN ;
		$Row[url] = "board/plugin.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;
	}

	if($C_use_member == "on") 
	{
		$Row[title] = _L_MEMBER ;
		$Row[func] = "" ;
		$URL['list'] = "" ; 
		include("./html/menu_title.html") ;	
		//////////////////////////////////////
		$Row[title] = "" ;
		$Row[func] = "회원 목록" ;	
		$URL['list'] = "member/member.php" ; 
		include("./html/menu_list.html") ;

		$Row[title] = "" ;
		$Row[func] = "그룹 관리" ;	
		$URL['list'] = "member/group.php" ; 
		include("./html/menu_list.html") ;

		$Row[title] = "" ;
		$Row[func] = "스킨 관리" ;	
		$URL['list'] = "member/open_config.php?conf_name=__global.conf.php" ; 
		include("./html/menu_list.html") ;

		$Row[title] = "" ;
		$Row[func] = "플러그인 관리" ;	
		$URL['list'] = "member/open_config.php?conf_name=__global.conf.php" ; 
		include("./html/menu_list.html") ;
	}

	if ($C_use_counter == "on") 
	{
		$tree_idx++ ;
		$menu_num++ ;
		$parent_idx = 0 ;
		$Row[title] = _L_COUNTER ;
		$Row[url] = "" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;
		$parent_idx = $menu_num ; 

		//////////////////////////////////////
		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_COUNTER ;
		$Row[url] = "counter/counter.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_SKIN ;
		$Row[url] = "counter/skin.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

		$tree_idx++ ;
		$menu_num++ ;
		$Row[title] = _L_GLOBALSETUP ;
		$Row[url] = "counter/config_open.php?conf_name=__global.conf.php" ;
		$Row[url_target] = "mainFrame" ;
		$Row[func] = "<script>Tree[$tree_idx] ='$menu_num|$parent_idx|$Row[title]|$Row[url]|$Row[url_target]';</script>" ;
		include("./html/menu_list.html") ;

	}

	//////////////////////////////////////


	include("./html/menu_footer.html") ;
?>
