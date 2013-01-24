<?php
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2002/1/2" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001, WhiteBBs.net, All rights reserved.

 
소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  

WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
*/
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	include("../lib/wb.inc.php") ;
	include_once($C_base[dir]."/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	//include_once("$C_base[dir]/lib/database.php") ;
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;
	unset($C_debug) ;

	$C_debug = 0 ;	
	
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	
	if( empty($data) )
	{
		err_abort("cat: data 링크가 올바르지 않습니다.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	if( empty($board_group) )
	{
		err_abort("cat: board_group 링크가 올바르지 않습니다.") ;
	}	

	if($C_debug) echo("cat:board_group[$board_group]<br>") ;

		// conf 파일 있는 지 검사 v 1.3.0
	$conf_file = "$C_base[dir]/member/conf/${C_data}.conf.php" ;
	if( @file_exists($conf_file) )
	{
		include($conf_file) ;
	} 
	else
	{
		err_abort("cat: $conf_file 파일이 존재하지 않습니다.") ;
	}

		// support outer_header use set with CGI var 2002/05/11
	if($outer_header=="0") 
	{
		$C_cat_outer_header_use = "0" ;
	}	
		// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}
	$C_skindir = "$C_base[dir]/member/skin/$C_skin" ;

		//2002/03/18 기본 권한값지정
	if( !isset($C_auth_perm) )
	{
		if($C_write_admin_only == 1)
		{
			$C_auth_perm = "7555" ; //기본 권한 지정
			$C_auth_cat_perm = "7555" ;
			$C_auth_reply_perm = "7555" ;
		}
		else
		{
			$C_auth_perm = "7667" ; //기본 권한 지정
			$C_auth_cat_perm = "7667" ;
			$C_auth_reply_perm = "7667" ;
		}

		$C_auth_user = "root" ; //기본 관리자 아이디 
		$C_auth_group = "wheel" ; //기본 관리자 그룹
	}
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_cat_perm, $check_data) ;
	$sess = $auth->member_info() ;


		// cat에 해당하는 스킨파일이 반드시 존재하여야 한다.
		// 스킨에서 cat을 링크하였으므로...
	if( !@file_exists("$C_skindir/cat.html") )
	{
		err_abort("스킨파일 $C_skindir/cat.html이 존재하지 않습니다.") ;
	}

	echo("<!--$my_version-->\n") ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;

		//인덱스에만 존재하는 자료는 URL로 부터 받는다.
	$Row['subject'] = stripslashes($subject) ;  
	$Row['type'] = $type ; 
	$Row['board_title'] = "$C_board_title" ;	
	if( empty($C_board_title) )
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
	
	if(empty($DOWNLOAD_PHP))
	{
		$DOWNLOAD_PHP = "download.php" ;
	}

		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row['category_list'] = category_list($C_data, $URL['list']) ;

	$URL = make_url($C_data, $Row, "member") ;
	///////////////////////////////////
	// header 처리
	///////////////////////////////////
	if($C_cookie_use == "1")
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

	$C_table_size = empty($C_table_size)?500:$C_table_size ; 
	$Row['table_size'] = $C_table_size ;
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;


	///////////////////////////////////
	// 외부 머리말 처리
	///////////////////////////////////
	if( $C_cat_outer_header_use == "1" || !isset($C_cat_outer_header_use) )
	{
    	for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
    	{
			if( !empty($C_OUTER_HEADER[$i]) )
        	{
				@include($C_OUTER_HEADER[$i]) ;
        	}
    	}
	}


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
		err_abort("$C_skindir/header 파일이 없습니다.") ;
	}
		// cat header
	if( @file_exists("$C_skindir/cat_header") )
	{
		include("$C_skindir/cat_header") ;
	}
	///////////////////////////////////
	
	$flist = new file_list("$C_base[dir]/member/data/$C_data/", 1) ;

		//DBMS를 이용할경우 기본적으로 가져야하는 값이다.
		//DB쪽으로 옮길지 고려.
		//2002/06/20
	if(empty($field))
	{
		$field = "uid" ;
		$key = $board_group ;
	}

	$dbi = new db_member($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
	$dbi->select_data() ;
	
	$flist->read("$board_group") ;
	echo("<table border=0 width=100%>") ;
	$main_writing = 1 ;
	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "attach") ) { continue ; }
			//1.
		$tmp = explode(".", $file_name) ;
		$board_id = ".".$tmp[1] ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id, "member") ;

		$Row['type'] = $type ;
		$Row['tot_page'] = $tot_page ;
		$Row['cur_page'] = $cur_page ;
		$Row['filter_type'] = $filter_type ;
		$Row['alias'] = $auth->alias() ;
			//2.
		$URL = make_url($C_data, $Row, "member") ;
		if( $URL[no_img] == "1" )
		{
			if(@file_exists($URL[attach_filename])) 
				$size = GetImageSize($URL[attach_filename]) ;
			$Row['img_width'] = $size[0] ;
			$Row['img_height'] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			if(@file_exists($URL[attach2_filename])) 
				$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}
			//3.
		$hide = make_comment($C_data, $Row, $i, "member") ;
		
		echo("<tr><td>") ;
		if( $main_writing == 1 )
		{
			$first_reply_url = $URL[reply] ;

			include "$C_skindir/cat.html" ;
			$main_writing = 0 ;
				//cat에서 답글목록을 보고 싶지 않다면 2002/05/11 
			if($no_reply_list) break ;
		}
		else
		{
			if( @file_exists("$C_skindir/reply_list.html") )
			{
				include "$C_skindir/reply_list.html" ;
			}
			else 
			{
				include "$C_skindir/cat.html" ;
			}
		}
		echo("</td></tr>") ;
		$i++ ;

	} // end of while 

	echo("</table>") ;


		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row['category_list'] = category_list($C_data, $URL['list']) ;

	$URL = make_url($C_data, $Row, "member") ;
		// use first reply_url 
	$URL[reply] = $first_reply_url ;
	/////////////////////////////////////
	// footer 처리 부분 
	/////////////////////////////////////
		//쿠키처리
	if($C_cookie_use == "1")
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

		// cat footer
	if( @file_exists("$C_skindir/cat_footer") )
	{
		include("$C_skindir/cat_footer") ;
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

	///////////////////////////
	//외부 꼬리말 처리
	///////////////////////////
	if( $C_cat_outer_header_use == "1" || !isset($C_cat_outer_header_use) )
	{
    	for($i = 0 ; $i < sizeof($C_OUTER_FOOTER) ; $i++ )
    	{
			if( !empty($C_OUTER_FOOTER[$i]) )
        	{
				@include($C_OUTER_FOOTER[$i]) ;
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
		//$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "count") ;	
	}

	exit ;
?>
