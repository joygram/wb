<?php
/*
WhiteBBS 2.5.0_B 2002/10/11
WhiteBoard 2.0.6 2001/11/20
WhiteBoard 2.1.0 2002/1/2
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.
 
소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  

저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  

*/



class cat 
{
}







///////////////////////////////////////////////////////////////////////////////
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	$wb_charset = wb_charset($C_base[language]) ;
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	//권한,인증모듈 선언및 초기화 실행
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	umask(0000) ;//웹서버의 기본 umask를 지워준다.

	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;
	///////////////////////////

	$_debug = 0 ;	

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	if( empty($data) )
	{
		err_abort("cat: data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	if( empty($board_group) )
	{
		err_abort("cat: board_group %s", _L_INVALID_LINK) ;
	}	
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if (!empty($skin) && @file_exists("$C_base[dir]/board/skin/$skin/write.html") )
	{
		$conf[skin] = $skin ;
	}

	$plug = array("") ;

	$timestamp = time() ;
	$write_num = "$timestamp" ;
	$spam_check = base64_encode(encrypt($write_num, $C_base["uniq_num"])) ;
	SetCookie('wb_spam_check', $spam_check, time()+604800, '/') ;

	//C_변수 이전버젼 호환성 유지
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	// support outer_header use set with CGI var 2002/05/11
	if($outer_header=="0") 
	{
		$conf[cat_outer_header_use] = "0" ;
	}	
	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;
	$_plugindir = "$C_base[dir]/board/plugin" ;

	//2002/03/18 기본 권한값지정
	if( !isset($conf[auth_perm]) )
	{
		if($conf[write_admin_only] == 1)
		{
			$conf[auth_perm] = "7555" ; //기본 권한 지정
			$conf[auth_cat_perm] = "7555" ;
			$conf[auth_reply_perm] = "7555" ;
		}
		else
		{
			$conf[auth_perm] = "7667" ; //기본 권한 지정
			$conf[auth_cat_perm] = "7667" ;
			$conf[auth_reply_perm] = "7667" ;
		}

		$conf[auth_user] = "root" ; //기본 관리자 아이디 
		$conf[auth_group] = "wheel" ; //기본 관리자 그룹
	}
	
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_cat_perm], $check_data) ;
	$sess = $auth->member_info() ;
	
	//세션별 스킨 사용을 위해추가 2002/09/21
	if($auth->is_admin())
		$sess_name = "admin" ;
	else if($auth->is_group())
		$sess_name = "group" ;
	else if($auth->is_member())
		$sess_name = "member" ;
	else
		$sess_name = "" ;
	if($_debug) echo("sess_name[$sess_name]<br>") ;

	// cat에 해당하는 스킨파일이 반드시 존재하여야 한다.
	// 스킨에서 cat을 링크하였으므로...
	if( !file_exists("$_skindir/$sess_name/cat.html") && 
		!file_exists("$_skindir/cat.html") )
	{
		err_abort("{$_skindir}/cat.html %s", _L_NOSKINFILE) ;
	}

	$release = get_release($C_base) ;
	echo("<!--VER: $release[1] $release[0]-->\n") ;

	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;

	$Row['board_title'] = "$conf[board_title]" ;	
	if( empty($conf[board_title]) )
	{
		$hide[board_title_start] = "<!--\n" ;
		$hide[board_title_end] = "-->\n" ;
	}
	else
	{
		$hide[board_title_start] = "" ;
		$hide[board_title_end] = "" ;
	}

	$license  = license2() ;	
	$license2 = license2() ;	
	$new_license = license($C_skin,$conf) ;
	
	if(empty($DOWNLOAD_PHP))
	{
		$DOWNLOAD_PHP = "download.php" ;
	}
		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row['category_list'] = category_list($_data, $URL['list']) ;

	$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;

	if($conf[cookie_use] == "1")
	{
		$Row['name'] = "" ;
		$Row['email'] = "" ;
		$Row['homepage'] = "" ;

		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row['cookie_name']     = stripslashes($cw_name) ;
		$Row['cookie_email']    = stripslashes($cw_email) ;
		$Row['cookie_homepage'] = stripslashes($cw_home) ;
	}
	//이전버젼 호환성 위해서.
	$Row['name'] = $Row['cookie_name'] ;
	$Row['email'] = $Row['cookie_email'] ;
	$Row['homepage'] = $Row['cookie_homepage'] ;

	$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
	$Row['table_size'] = $conf[table_size] ;

	$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
	$Row['table_align'] = $conf[table_align];

	$hide = make_comment($_data, $Row) ;


	///////////////////////////////////
	// 외부 머리말 처리
	///////////////////////////////////
	if( $conf[cat_outer_header_use] == "1" || !isset($conf[cat_outer_header_use]))
	{
    	for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
    	{
			if( !empty($conf[OUTER_HEADER][$i]) )
        	{
				@include($conf[OUTER_HEADER][$i]) ;
        	}
    	}
	}

	$Row['spam_check'] = base64_encode("$spam_check|$timestamp") ;
	///////////////////////////////////
	// header 처리
	///////////////////////////////////
	//2002/10/31
	$plug[header] = include_plugin("header", $_plugindir, $conf) ;
	if( @file_exists("$_skindir/$sess_name/HEADER") )
		include("$_skindir/$sess_name/HEADER") ;
	else if( @file_exists("$_skindir/$sess_name/header") )
		include("$_skindir/$sess_name/header") ;
	else if( @file_exists("$_skindir/HEADER") )
		include("$_skindir/HEADER") ;
	else if( @file_exists("$_skindir/header") )
		include("$_skindir/header") ;
	else
	{
		err_abort("$_skindir/header %s", _L_NOFILE) ;
	}

	// cat header 
	if( @file_exists("$_skindir/$sess_name/cat_header") )
		include("$_skindir/cat_header") ;
	else if( @file_exists("$_skindir/cat_header") )
		include("$_skindir/cat_header") ;

	///////////////////////////////////
	$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
	$flist->read("$board_group") ;
	echo("<table border=0 width=100%>") ;
	$main_writing = 1 ;
	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if($_debug) echo("file_name[$file_name]<br>") ;
		if( strstr($file_name, "attach") ) { continue ; }
		//1.
		$tmp = explode(".", $file_name) ;
		$board_id = ".".$tmp[1] ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;

		if( $Row['secret'] )
		{
			//퍼미션검사하고..
			//redirect가 되는가?
			$auth->run_mode( EXEC_MODE ) ;
			
			if($_debug ) echo("비교[{$Row[secret_passwd]}::{$auth->auth_data[passwd]}] <br>") ;
			//여기서 걸리는 것이아니고 여기서는 폼만 나온다. 
			//위에 첫번째 권한 검사에서 걸림 
			//위의 권한 검사를 벗어날 수 있는 방법이 있어야 함. 
			
			if( $auth->mode == "on_check_secret" ) 	
				$auth->auth_mode( "auth_anonymous" ) ;			
			else 
				$auth->auth_mode( "check_secret" );			

			$check_data[passwd] = $Row['secret_passwd']  ;
			$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		}

		//2002/10/20 내용 처리 보강
		$cmt_token = wb_token($Row[comment]) ;
		$tmp_comment = "" ;
		for($i = 0; $i < count($cmt_token["cont"]); $i++ )
		{
			if( $cmt_token["attr"][$i] == "NORMAL" )
			{
				// use br의 구현 2002/05/15
				if($_debug) echo("cat:Row[br_use][$Row[br_use]]<br>") ;
				if($Row['br_use'] == "no")
				{
				}
				else
				{
					if( $Row['html_use'] == HTML_NOTUSE || $Row['br_use'] != "no" ) 
					{
						$cmt_token["cont"][$i] = nl2br($cmt_token["cont"][$i]) ;
						$cmt_token["cont"][$i] = clear_br($cmt_token["cont"][$i]) ;  //table 사용시 <br />에 의한 공백 제거
						$cmt_token["cont"][$i] = str_replace("  ", "&nbsp;&nbsp;", $cmt_token["cont"][$i]) ;
						$cmt_token["cont"][$i] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $cmt_token["cont"][$i]) ;
					}
				}
				//자동으로 링크 만들기 옵션선택시 
				if($conf[url2link_use] == "1" && $Row['html_use'] == HTML_NOTUSE ) 
				{
					$cmt_token["cont"][$i] = url2link( $cmt_token["cont"][$i] ) ;
				}

			}
			else if($cmt_token["attr"][$i] == "W_CODE") 
			{
				$cmt_token["cont"][$i] = wb_highlight($cmt_token["cont"][$i]) ;
			}
			$tmp_comment .= $cmt_token["cont"][$i] ;
		}
		$Row['comment'] = $tmp_comment ;
		
		//이전 버젼 스킨에서 카운트의 위치를 바꿔서 사용한 것이 있어서 호환성 유지를 위해...
		$Row['is_main_writing'] = $main_writing ;
		$Row['tot_page'] = $tot_page ;
		$Row['cur_page'] = $cur_page ;
		$Row['filter_type'] = $filter_type ;
		$Row[cnt3] = $Row['cnt_download'] ;
		$Row[cnt2] = $Row[cnt_download2] ;
		$Row['alias'] = $auth->alias() ;
		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row['cookie_name']     = stripslashes($cw_name) ;
			$Row['cookie_email']    = stripslashes($cw_email) ;
			$Row['cookie_homepage'] = stripslashes($cw_home) ;
		}

		$hide = make_comment($_data, $Row, $i) ;

		$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;
		if( $URL[no_img] == "1" )
		{
			$size = GetImageSize($URL[attach_filename]) ;
			$Row['img_width'] = $size[0] ;
			$Row['img_height'] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}

		echo("<tr><td>") ;
		if( $main_writing == 1 )
		{
			$first_reply_url = $URL[reply] ;

			$main_board_id = $board_id ; 
			$Row['main_board_id'] = $main_board_id ;

			$plug[cat] = include_plugin("cat", $_plugindir, $conf) ;
			if(file_exists("$_skindir/$sess_name/cat.html"))
				include "$_skindir/$sess_name/cat.html" ;
			else
				include "$_skindir/cat.html" ;
			$main_writing = 0 ;
				//cat에서 답글목록을 보고 싶지 않다면 2002/05/11 
			if($no_reply_list) break ;
		}
		else
		{
			$Row['main_board_id'] = $main_board_id ;
			$plug[reply_list] = include_plugin("reply_list", $_plugindir, $conf) ;
			if($_debug) echo("include REPLY_LIST.html<br>") ;
			if( @file_exists("$_skindir/$sess_name/reply_list.html") )
				include "$_skindir/$sess_name/reply_list.html" ;
			else if( @file_exists("$_skindir/reply_list.html") )
				include "$_skindir/reply_list.html" ;
			else if( @file_exists("$_skindir/$sess_name/cat.html") )
				include "$_skindir/$sess_name/cat.html" ;
			else
				include "$_skindir/cat.html" ;
		}
		//내용에서 xmp태그에 대한 방어 추가 필요.
		echo("</td></tr>") ;
		$i++ ;
	} // end of while 
	echo("</table>") ;

	// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row['category_list'] = category_list($_data, $URL['list']) ;

	$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;
	// use first reply_url 
	$URL[reply] = $first_reply_url ;

	$Row['spam_check'] = base64_encode("$spam_check|$timestamp") ;
	/////////////////////////////////////
	// footer 처리 부분 
	/////////////////////////////////////
	//쿠키처리
	if($conf[cookie_use] == "1")
	{
		$Row['name'] = "" ;
		$Row['email'] = "" ;
		$Row['homepage'] = "" ;

		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row['cookie_name']     = stripslashes($cw_name) ;
		$Row['cookie_email']    = stripslashes($cw_email) ;
		$Row['cookie_homepage'] = stripslashes($cw_home) ;
	}
	//이전버젼 호환성 위해서.
	$Row['name']     = $Row['cookie_name'] ;
	$Row['email']    = $Row['cookie_email'] ;
	$Row['homepage'] = $Row['cookie_homepage'] ;

	//2002/06/17
	if( ! $auth->is_anonymous() )
	{
		$Row['email'] = $auth->email() ;
		$Row['member_info'] = $auth->member_info() ;
		if($_debug) echo("write:".print_r($W_SES) ) ;
	}

	//plugin을 사용할때 이전 변수의 내용을 복원 해야 하는가?
	//변수치환을 하는 경우에는 복원하면 안되고 변수 치환을 해서는 안되는 경우 복원 을 반드시 해야한다.
	$plug[cat_footer] = include_plugin("cat_footer", $_plugindir, $conf) ;

	// cat footer
	if( @file_exists("$_skindir/$sess_name/cat_footer") )
		include("$_skindir/cat_footer") ;
	else if( @file_exists("$_skindir/cat_footer") )
		include("$_skindir/cat_footer") ;

	$plug[footer] = include_plugin("footer", $_plugindir, $conf) ;
	if( @file_exists("$_skindir/$sess_name/FOOTER") )
		include("$_skindir/$sess_name/FOOTER") ;
	else if( @file_exists("$_skindir/$sess_name/footer") )
		include("$_skindir/$sess_name/footer") ;
	else if( @file_exists("$_skindir/FOOTER") )
		include("$_skindir/FOOTER") ;
	else if( @file_exists("$_skindir/footer") )
		include("$_skindir/footer") ;
	else
	{
		err_abort("$_skindir/footer %s", _L_NOFILE) ;
	}

	//라이센스 무조건 출력 2002/09/23
	echo $new_license ;
	///////////////////////////
	//외부 꼬리말 처리
	///////////////////////////
	if ($conf[cat_outer_header_use] == "1" || !isset($conf[cat_outer_header_use]))
	{
    	for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
    	{
			if( !empty($conf[OUTER_FOOTER][$i]) )
        	{
				@include($conf[OUTER_FOOTER][$i]) ;
        	}
    	}
	}
	//2002/04/21 관리자 인경우 count수가 올라가지 않도록 조정.
	if(! $auth->is_admin() )
	{
		// count_pos is empty when reply data saved. please this correct.
		// 조회수 증가 시키기
		if(empty($count_pos))
		{
			$count_pos = "4" ;
		}
		$idx_data = array("board_group" => $board_group, "count_pos" => $count_pos ) ;
		$index_name = "data" ;
		$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "count") ;	
	}
	exit ;
?>
