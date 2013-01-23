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


	$C_debug = 0 ;
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	include_once("${C_base[dir]}/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	include("../lib/wb.inc.php") ;
	///////////////////////////
	//include_once("${C_base[dir]}/lib/database.php") ;
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;

		// CGI variable filtering
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;

	if( empty($data) )
	{
		// 폼을 거치지 않고 직접 넣었을때 data변수의 처리방법이 애매모호함.
		//임시 처리 방편으로 data를 member로 preset함.
		// 2002/0618
		//err_abort("링크에 [data]를 넣어주세요.") ;
		$C_data = "member" ;
	}	
	else
	{
		$C_data = $data ;
	}

	if( empty($list) ) $list = "list" ; 

	$conf_file = "${C_base[dir]}/member/conf/${C_data}.conf.php" ;
	if( @file_exists($conf_file) )
	{
		include($conf_file) ;
	} 
	else
	{
		err_abort("$conf_file 파일이 존재하지 않습니다.") ;
	}

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
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	$sess = $auth->member_info() ;

		// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}

		// skin dir pre setting 2002/04/09
	$C_skindir = "$C_base[dir]/member/skin/$C_skin" ;

	echo("<!--$my_version-->\n") ;

	//bsd_license($lang) ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;
	$license  = license2() ;	
	$license2 = license2() ;	

	
	$URL = make_url($C_data, $Row, "member") ;
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//////////////////////////
	// START MAIN
	//////////////////////////

		//기본값에 asc로 되어 있다면 통과(검색안하도록...)
		//기본값은 마지막으로 쓴 글을 중심으로 정렬하도록 되어 있다.
	$C_sort_index = (!isset($C_sort_index))?"0":$C_sort_index ;

		//검색란에 아무것도 입력하지 않으면 전체목록을 나오게 해야 하므로
	$mode = (empty($key))?"":$mode ;
	$mode = ($filter_type > "0")?"find":$mode ;
	if($C_debug) echo("MODE[$mode] $filter_type<br>") ;
		//기본 검색 필드 name
	$field = empty($field)?"name":$field ;

	if($C_debug) echo("LIST:filter_type[$filter_type]<br>") ;

	/////////////////////////////////////////////////
	// 전체 데이터 수 계산 : 페이지 바를 위해서.. 
	// DB를 사용하지 않기 때문에 이부분에 시간이 많이 허비됨.
	/////////////////////////////////////////////////
	$dbi = new db_member($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

		// select하기전에는 total값을 구할 수 없기 때문에...
		// dbi class에서 limit값을 구할 수 있는 방법이 없다.
	$dbi->count_data() ;

	$tot_page = get_total_page( $dbi->total, $C_nCol*$C_nRow ) ;
	if($C_debug) echo("total[$dbi->total], TOT_PAGE:[$tot_page]<br>") ;

		// 가지고온 전체 데이터의 개수와 현재 페이지가 일치하지 않으면 현재 페이지를 최초로 reset시킨다.	
		// page control variable set
	$cur_page = ($cur_page < 0 )?0:$cur_page ;
	$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;

		// offset calc
	$line_begin = $cur_page * ($C_nCol * $C_nRow) ;
	if($C_debug) echo("line_begin[$line_begin] cur_page[$cur_page]<br>") ;

	$dbi->select_data($line_begin, $C_nCol * $C_nRow) ;

		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row[category_list] = category_list($C_data, $URL['list']) ;
		//머리말에 들어갈 변수들
	$Row[nTotal]   = $dbi->total ; 
	$Row[cur_page] = empty($cur_page)?1:$cur_page ;
	$Row[tot_page] = $tot_page ;
	$Row[play_list] = $play_list ; //음악 선택곡 목록
	

	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;


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


	$nPos = $start ; //검색할 경우  라인 br을 위해서 선언 
	$nCnt = $line_begin ; // 넘버링을 위한 숫자
	if($C_debug) echo("[$dbi->row_begin][$dbi->row_end]<br>") ;
	echo("$C_BOX_START") ;
	for($i = $dbi->row_begin ; $i < $dbi->row_end ; $i ++)
	{
		///////////////////////////////////////
			//1.
		$Row = $dbi->row_fetch_array($i) ;
		if( $Row == -1)
		{
			echo("Row [$i]th is -1<br>") ;
			break ;
		}

		$Row[no] = $dbi->total - $nCnt ;

		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;
		$Row[filter_type] = $filter_type ;


			//plug_in 처리 필요 2003/06/13
		$Row[name] = $Row[firstname].$Row[lastname] ;
		$Row[mobilephone] = mobile_phone($Row[mobilephone]) ;
		$Row[sex] = $Row[sex]?"남":"여" ;
		$result = get_department($Row[interest_department]) ;
		$Row[interest_department] = $result["name"] ;
		$Row[job] = get_job($Row[job_kind]) ;

		if(!$auth->is_anonymous())
		{
			//$Row[alias] = $auth->alias() ;
		}
			//2.
		$URL = make_url($C_data, $Row, "member") ;
		if( $URL[no_img] == "1" )
		{
			if(@file_exists($URL[attach_filename]))
				$size = GetImageSize($URL[attach_filename]) ;
			$Row[img_width] = $size[0] ;
			$Row[img_height] = $size[1] ;
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

		echo("$C_BOX_DATA_START") ;
		include "$C_skindir/{$list}.html" ;
		echo("$C_BOX_DATA_END") ;
		if( ($nPos % $C_nCol) == ($C_nCol-1) )
		{
			echo("$C_BOX_BR") ;
		}
		$nPos++ ;
		$nCnt++ ;
	}
	echo("$C_BOX_END") ;

	$dbi->destroy() ;

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
