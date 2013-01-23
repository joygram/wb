<?php
$my_version = "WhiteBoard 2.4.1 2002/05/06" ;
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2001/1/2" ;
$my_version = "WhiteBoard 2.1.2 2001/1/12" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  

WhiteBoard 1.4.2 :     2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 :     2001/06/17
WhiteBoard 1.2.3 :     2001/05/10
WhiteBoard 1.1.1       2001/4/11
*/
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;

	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;

	$C_base = get_base(1) ; 

	$wb_charset = wb_charset($C_base[language]) ;

	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	require_once("$C_base[dir]/lib/wb.inc.php") ;



	// 시스템 변수등의 호환성을 위해. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	umask(0000) ;//웹서버의 기본 umask를 지워준다.

	$license  = license2() ;	
	$license2 = license2() ;	
	
	
	
	require_once("$C_base[dir]/member/Lister.php") ;	

	
//	$URL = make_url($C_data, $Row, "member") ;
//	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;


	//검색란에 아무것도 입력하지 않으면 전체목록을 나오게 해야 하므로
//	$mode = (empty($key))?"":$mode ;
//	$mode = ($filter_type > "0")?"find":$mode ;
//	if($C_debug) echo("MODE[$mode] $filter_type<br>") ;
		//기본 검색 필드 name
//	$field = empty($field)?"name":$field ;

//	if($C_debug) echo("LIST:filter_type[$filter_type]<br>") ;
	
	
	///
	$lister = new Lister( $data, $auth ) ;
	
	$lister->list() ;


	///////////////////////////////////
	// 머리말 처리
	///////////////////////////////////
		// 외부 머리말 삽입
	if( $C_list_outer_header_use == "1" || !isset($C_list_outer_header_use) )
	{
		for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
		{
			if( !empty($C_OUTER_HEADER[$i]) )
			{
				@include($C_OUTER_HEADER[$i]) ;
			}
		}
	}

	///////////////////////////////////
		// header 삽입
	///////////////////////////////////
		//글쓰기는 한페이지전체에 적용이 되므로 
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;
	if( @file_exists("$C_skindir/HEADER") )
	{
		include("$C_skindir/HEADER") ;
	}
	else if( @file_exists("$C_skindir/header") )
	{
		include("$C_skindir/header") ;
	}
	else
	{
		err_abort("list: $C_skindir/header 파일이 없습니다.") ;
	}
		// list header
	if( @file_exists("$C_skindir/{$list}_header") )
	{
		include("$C_skindir/{$list}_header") ;
	}
	///////////////////////////////////


	$lister->list() ;

	///////////////////////////////////
	// 검색창에 들어갈 변수 준비
	///////////////////////////////////
	$checked[$field] = "checked" ;
	$selected[$field] = "selected" ;
	$Row[field] = $field ;

	$page_bar = wb_page_bar( $C_data, $cur_page, $tot_page, $key, $field, $mode, "member" ) ;

		//글쓰기는 한페이지전체에 적용이 되고 
		//나머지는 글 하나에 적용되므로 이곳에서 적용
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;
	$Row[board_title] = "$C_board_title" ;	
	$URL = make_url($C_data, $Row, "member") ;
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
		// category list 2001/12/09
	//$Row[category_list] = category_list($C_data, $URL['list']) ;

	/////////////////////////////////////
	// 꼬리말 처리 
	/////////////////////////////////////
		// list footer
	if( @file_exists("$C_skindir/{$list}_footer") )
	{
		include("$C_skindir/{$list}_footer") ;
	}

	if( @file_exists("$C_skindir/FOOTER") )
	{
		include("$C_skindir/FOOTER") ;
	}
	else if( @file_exists("$C_skindir/footer") )
	{
		include("$C_skindir/footer") ;
	}
	else
	{
		err_abort("$C_skindir/footer 파일이 없습니다.") ;
	}

	if( $C_list_outer_header_use == "1" || !isset($C_list_outer_header_use) )
	{
		//외부 꼬리말 삽입
		for($i = 0 ; $i < sizeof($C_OUTER_FOOTER) ; $i++ )
   	 	{
			if( !empty($C_OUTER_FOOTER[$i]) )
			{
				@include($C_OUTER_FOOTER[$i]) ;
        	}
    	}
	}
?>
