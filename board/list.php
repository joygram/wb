<?php
/*
Whitebbs 2.8.0 2003/12/27 
see also HISTORY.TXT 
Copyright (c) 2001-2004, WhiteBBs.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시( 화이트보드팀과 그 공헌자의 이름)와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  


*/

	//2002/11/10 cat에서 플러그인 으로 리스트 나오게 하기 위해서 구조 변경 
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	$wb_charset = wb_charset($C_base[language]) ;
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	require_once("$C_base[dir]/lib/wb.inc.php") ;

	$_debug = 0 ;
	// 시스템 변수등의 호환성을 위해. 2003/12/28
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	//2002/11/10 plugin에서 호출하는 경우 auth를 이미 호출하기 때문에 객체가 생성안되므로 전역변수로 잡아놓는다.
	global $auth ;
	global $C_base ;

	global $WRITE_URL, $EDIT_URL, $REPLY_URL ;
	global $LIST_URL, $DELETE_URL, $CAT_URL ;
	global $ATTACH_URL, $ATTACH2_URL, $ATTACH_FILE, $ATTACH2_FILE ;
	global $DOWNLOAD_URL, $DOWNLOAD2_URL, $HOMEPAGE_URL ;


	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	$Row = array("") ;

	//스팸방지기능
	//2.6이전버젼에는 기능에 필요한 uniq_num이 없으므로 이곳에서 생성후 시스템설정을 자동으로 업그레이드 하도록 한다.
	if(empty($C_base["uniq_num"]))
	{
		//값이 없는 경우 생성 해줌
		$uniq_num_lists = get_uniq_num_list() ;
		$uniq_num = implode("", $uniq_num_lists) ;
		//system.ini갱신
		$system_conf = "{$C_base[dir]}/system.ini.php" ;
		
		//$ini[uniq_num] = $uniq_num ;
		//save_system_ini($system_conf, $ini) ;  $ini 대신 $C_base 를 넘겨준다.
		$C_base[uniq_num] = $uniq_num ;
		save_system_ini($system_conf, $C_base) ;
		
		if($_debug) echo("generate system uniq_num and update system.ini.php uniq_num[{$C_base[uniq_num]}]<br>") ;
	}

	// CGI variable filtering
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	if( empty($data) )
	{
		err_abort("list: data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}

	if( empty($list) ) $list = "list" ; 
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/board/skin/{$skin}/write.html") )
	{
		$conf[skin] = $skin ;
	}
	//C_변수 이전버젼 호환성 유지
	$C_skin = $conf[skin] ;
	$C_data = $_data ;

	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	//2002/03/18 기본 권한값지정
	if (!isset($conf[auth_perm]))
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
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
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
	//C_변수 이전 버젼 호환성 유지
	$C_skin = $conf[skin] ;
	// skin dir pre setting 2002/04/09
	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;
	$_plugindir = "$C_base[dir]/board/plugin" ;

	$release = get_release($C_base) ;
	echo("<!--VER: $release[1] $release[0]-->\n") ;

	//bsd_license($lang) ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;
	$license  = license2() ; 
	$license2 = license2() ;	
	$new_license = license($C_skin,$conf) ;	

	$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	//////////////////////////
	// START MAIN
	//////////////////////////
	//기본값에 asc로 되어 있다면 통과(검색안하도록...)
	//기본값은 마지막으로 쓴 글을 중심으로 정렬하도록 되어 있다.
	$conf[sort_index] = (!isset($conf[sort_index]))?"0":$conf[sort_index] ;
	//검색모드 설정 db_board안으로 들어가야 하지 않을까?
	//검색란에 아무것도 입력하지 않으면 전체목록을 나오게 해야 하므로
	$mode = (empty($key))?"":$mode ;
	$mode = ($filter_type > "0")?"find":$mode ;
	$mode = ($conf[sort_order]=="asc" && $conf[sort_order] == 0)?"find":$mode ;
	$mode = ($conf[sort_index] != 0)?"find":$mode ;
	if($_debug) echo("LIST:mode[{$mode}] filter_type:$filter_type<br>") ;
	//기본 검색 필드 name
	$field = empty($field)?"name":$field ;

	if($_debug) echo("LIST:conf[sort_order][$conf[sort_order]]conf[sort_index][$conf[sort_index]]filter_type[$filter_type]<br>") ;
	/////////////////////////////////////////////////
	// 전체 데이터 수 계산 : 페이지 바를 위해서.. 
	// DB를 사용하지 않기 때문에 이부분에 시간이 많이 허비됨.
	/////////////////////////////////////////////////
	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

	// select하기전에는 total값을 구할 수 없기 때문에...
	// dbi class에서 limit값을 구할 수 있는 방법이 없다.

	$_time_start = getmicrotime() ;
	$dbi->count_data() ;
	$_time_spend = number_format(getmicrotime() - $_time_start, 3) ;	
	if($_debug) echo("count_data exec time[$_time_spend]<br>") ;

	$tot_page = get_total_page( $dbi->total, $conf[nCol]*$conf[nRow] ) ;
	if($_debug) echo("total[$dbi->total], TOT_PAGE:[$tot_page]<br>") ;
	// 가지고온 전체 데이터의 개수와 현재 페이지가 일치하지 않으면 현재 페이지를 최초로 reset시킨다.	
	// page control variable set
	$cur_page = ($cur_page < 0 )?0:$cur_page ;
	$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;
	// offset calc
	$line_begin = $cur_page * ($conf[nCol] * $conf[nRow]) ;
	if($_debug) echo("line_begin[$line_begin] cur_page[$cur_page]<br>") ;

	$_time_start = getmicrotime() ;
	$dbi->select_data($line_begin, $conf[nCol] * $conf[nRow]) ;
	$_time_spend = number_format(getmicrotime() - $_time_start, 3) ;	
	if($_debug) echo("select_data exec time[$_time_spend]<br>") ;

	// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row[category_list] = category_list($_data, $URL['list']) ;
	//머리말에 들어갈 변수들
	$Row[nTotal]   = $dbi->total ; 
	$Row[cur_page] = empty($cur_page)?1:$cur_page+1 ;
	$Row[tot_page] = $tot_page ;
	$Row[play_list] = $play_list ; //음악 선택곡 목록


	//스팸체크를 위한 값생성 : 글쓰기 시간 제한과 스팸체크값을 폼에서 넘겨 받도록 한다.
	$timestamp = time() ;
	$write_num = "$timestamp" ;
	$spam_check = base64_encode(encrypt($write_num, $C_base["uniq_num"])) ;
	SetCookie('wb_spam_check', $spam_check, time()+604800, '/') ;
	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;



	$hide = make_comment($_data, $Row) ;
	///////////////////////////////////
	// 머리 처리
	///////////////////////////////////
	if(empty($_plugin_use))
	{
		// 외부 머리 삽입
		if( $conf[list_outer_header_use] == "1" || !isset($conf[list_outer_header_use]) )
		{
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}
	}

	//2002/10/26 plugin header 삽입
	$plug[header] = include_plugin("header", $_plugindir, $conf) ;

	///////////////////////////////////
	// header 삽입
	///////////////////////////////////
	//헤더에서 쿠키처리
	if($conf[cookie_use] == "1")
	{
		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row[cookie_name]       = stripslashes($cw_name) ;
		$Row[cookie_email]      = stripslashes($cw_email) ;
		$Row[cookie_homepage]   = stripslashes($cw_home) ;

	}
	$Row[name]     = $Row[cookie_name] ;
	$Row[email]    = $Row[cookie_email] ;
	$Row[homepage] = $Row[cookie_homepage] ;

	if(empty($_plugin_use))
	{
		$Row[board_title] = "$conf[board_title]" ;	
	}

	$conf[table_size] = !isset($conf[table_size])?500:$conf[table_size] ;
	$Row[table_size]  = $conf[table_size] ;

	$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
	$Row[table_align] = $conf[table_align];

	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;

	//글쓰기는 한페이지전체에 적용이 되므로 
	$hide = make_comment($_data, $Row) ;
	if(file_exists("$_skindir/$sess_name/HEADER"))
		include("$_skindir/$sess_name/HEADER") ;
	else if(file_exists("$_skindir/$sess_name/header"))
		include("$_skindir/$sess_name/header") ; 
	else if(file_exists("$_skindir/HEADER"))
		include("$_skindir/HEADER") ;
	else if(file_exists("$_skindir/header"))
		include("$_skindir/header") ;
	else
	{
		err_abort("list: {$_skindir}/header %s", _L_NOFILE) ;
	}

	//2002/10/26 list_header plugin 삽입
	$plug[list_header] = include_plugin("list_header", $_plugindir, $conf) ;

	// list header
	if( @file_exists("$_skindir/$sess_name/{$list}_header") )
	{
		include("$_skindir/$sess_name/{$list}_header") ;
	}
	else if( @file_exists("$_skindir/{$list}_header") )
	{
		include("$_skindir/{$list}_header") ;
	}
	///////////////////////////////////
	$nPos = $start ; //검색할 경우  라인 br을 위해서 선언 
	$nCnt = $line_begin ; // 넘버링을 위한 숫자
	if($_debug) echo("[$dbi->row_begin][$dbi->row_end]<br>") ;
	echo("$conf[BOX_START]") ;
	for($i = $dbi->row_begin ; $i < $dbi->row_end ; $i ++)
	{
		///////////////////////////////////////
		//1.
		$Row = $dbi->row_fetch_array($i) ;
		if( $Row == -1)
		{
			if($_debug) echo("Row [$i]th is -1<br>") ;
			err_abort(_L_INDEX_BROKEN) ;
		}

		//제목 길이 제한 적용 이동( row_fetch_array  에 들어 있는 것을 cat 에서도 적용되는 문제로 list.php 로 이동함
		if(!empty($conf[subject_max]))
		{
			$Row[subject] = cutting($Row[subject], $conf[subject_max]) ;
		}
			//내용길이 제한적용 2002/01/24
			// list에서만 적용함.
			// 리스트에서 내용길이 제한이 있는 경우에는 태그를 막음.
		if( !empty($conf[comment_max])) 
		{
			$Row[comment] = cutting($Row[comment], $conf[comment_max]) ;
			$Row[comment] = block_tags($Row[comment],"ALL") ;
		}

		$Row[comment_raw] = $Row[comment];
			// use br의 구현 2002/05/15
		if($br_use == "no")
		{
		}
		else
		{
			if( $Row[html_use] == HTML_NOTUSE || $Row[br_use] != "no" ) 
			{
				$Row[comment] = nl2br($Row[comment]) ;
				$Row[comment] = str_replace("  ", "&nbsp;&nbsp;", $Row[comment]) ;
				$Row[comment] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $Row[comment]) ;
			}
		}
		//자동으로 링크 만들기 옵션선택시 
		//html사용을 하면 주소 자동링크는 사용을 안하도록 함.
		if($conf[url2link_use] == "1" && $Row[html_use] == HTML_NOTUSE ) 
		{
			$Row[comment] = url2link( $Row[comment] ) ;
		}

		$Row[no] = $dbi->total - $nCnt ;
		if(!empty($_plugin_use))
		{
			if($board_group == $Row[board_group])  
			{
				if(empty($_plugin_list_no))
				{
					$Row[no] = ">>" ;
				}
				else
				{
					$Row[no] = $_plugin_list_no ;
				}
			}
		}
		$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;
		$Row[grad_color] = make_gradation_color($_data, $dbi->total, $conf[nCol]*$conf[nRow], "board") ;

		$Row[cnt_download]  = $Row[cnt3] ;
		$Row[cnt_download2] = $Row[cnt2] ;

		$Row[is_main_writing] = 1 ;
		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;
		$Row[filter_type] = $filter_type ;
		$Row[to] = $conf[list_php] ;


		if(!$auth->is_anonymous())
		{
			$Row[alias] = $auth->alias() ;
		}
			//2002/04/21 리스트 안에 폼이 들어간 경우 쿠키 처리를 위해서.
		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row[cookie_name]     = stripslashes($cw_name) ;
			$Row[cookie_email]    = stripslashes($cw_email) ;
			$Row[cookie_homepage] = stripslashes($cw_home) ;
		}

		//2.
		$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
		/*
		if( $URL[no_img] == "1" )
		{
			$size = GetImageSize($URL[attach_filename]) ;
			$Row[img_width] = $size[0] ;
			$Row[img_height] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}
		*/

		//3.
		$hide = make_comment($_data, $Row, $i) ;

		// 2002/10/26 plugin list.php 삽입
		$plug['list'] = include_plugin("list", $_plugindir, $conf) ;
		if($_plugin_list_control == "skip") 
		{
			continue ;
		}

		echo("$conf[BOX_DATA_START]") ;
		if(file_exists("$_skindir/$sess_name/{$list}.html"))
			include "$_skindir/$sess_name/{$list}.html" ;
		else 
			include "$_skindir/{$list}.html" ;
		echo("$conf[BOX_DATA_END]") ;
		if( ($nPos % $conf[nCol]) == ($conf[nCol]-1) )
		{
			echo("$conf[BOX_BR]") ;
		}
		$nPos++ ;
		$nCnt++ ;
	}
	echo("$conf[BOX_END]") ;

	///////////////////////////////////
	// 검색창에 들어갈 변수 준비
	///////////////////////////////////
	$checked[$field] = "checked" ;
	$selected[$field] = "selected" ;
	$Row[field] = $field ;
	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;

	$page_bar = wb_page_bar( $_data, $cur_page, $tot_page, $key, $field, $mode ) ;

	//글쓰기는 한페이지전체에 적용이 되고 
	//나머지는 글 하나에 적용되므로 이곳에서 적용
	$hide = make_comment($_data, $Row) ;
	$Row[board_title] = "$conf[board_title]" ;	
	$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	// category list 2001/12/09
	$Row[category_list] = category_list($_data, $URL['list']) ;

	/////////////////////////////////////
	// 꼬리말 처리 
	/////////////////////////////////////
	//2002/10/26 plugin list_footer 삽입
	$plug[list_footer] = include_plugin("list_footer", $_plugindir, $conf) ;
	
	// list footer
	if( @file_exists("$_skindir/$sess_name/{$list}_footer") )
		include("$_skindir/$sess_name/{$list}_footer") ;
	else if( @file_exists("$_skindir/{$list}_footer") )
		include("$_skindir/{$list}_footer") ;

	//2002/10/26 plugin footer 삽입
	$plug[footer] = include_plugin("footer", $_plugindir, $conf) ;

	if(file_exists("$_skindir/$sess_name/FOOTER") )
		include("$_skindir/$sess_name/FOOTER") ;
	else if(file_exists("$_skindir/$sess_name/footer"))
		include("$_skindir/$sess_name/footer") ;
	else if(file_exists("$_skindir/FOOTER"))
		include("$_skindir/FOOTER") ;
	else if(file_exists("$_skindir/footer"))
		include("$_skindir/footer") ;
	else
	{
		err_abort("list: $_skindir/footer %s", _L_NOFILE) ;
	}

	if(empty($_plugin_use))
	{
		//라이센스 무조건 출력 2002/09/23
		echo $new_license ;

		if ($conf[list_outer_header_use] == "1" || !isset($conf[list_outer_header_use]))
		{
			//외부 꼬리말 삽입
			for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
			{
				if( !empty($conf[OUTER_FOOTER][$i]) )
				{
					@include($conf[OUTER_FOOTER][$i]) ;
				}
			}
		}
	}
?>
