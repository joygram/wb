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

WhiteBoard 1.4.2: 2001/08/15
WhiteBoard 1.4.0 pre: 2001/08/11
WhiteBoard 1.3.0: 2001/06/17
WhiteBoard 1.2.3: 2001/05/10
WhiteBoard 1.1.1: 2001/4/11
*/
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////

	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	$_debug = 0 ;	

	//메인 글을 지우는 경우에는 답글도 모두 삭제한다.

		//필터링
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	$board_id = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_id) ;
	if( empty($data) )
	{
		err_abort("data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	$conf = read_board_config($_data) ;
	//C_변수 이전 버젼 호환성 유지
	$C_skin = $conf[skin] ;
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

	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
	$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
	if( empty($Row[user]) ) //글이 anonymous인가 아닌가 검사
	{
		// 2.1.2 이하 버젼은 모두 anonymous글이므로
			//암호화 되어 있으면 
		if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
		{
			$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
		}
		$check_data[passwd] = $Row[password]  ;
		if($_debug) echo("$PHP_SELF:check_data[passwd][$check_data[passwd]]<br>") ;
	}
	else 
	{
		// member관리와 연동준비 2002/02/17
	}

	$auth->run_mode( EXEC_MODE ) ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$sess = $auth->member_info() ;

	
	$index_name = "data" ;
	$idx_data = array("board_group" => $board_group, "board_id" => $board_id, "nWriting" => "-1"  ) ; 
	$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "delete") ;	

	if( $idx_data[main_writing_delete] == "1" )
	{
			// 전체 글개수 감소
		$fp = wb_fopen("$C_base[dir]/board/data/$_data/total.cnt", "w") ;
			//
		fwrite($fp, $dbi->total ) ;
		fclose($fp) ;

		$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
		$flist->read("$board_group") ;
		$nCnt = 0 ;
		while( ($file_name = $flist->next()) )
		{
			unlink("data/$_data/$file_name") ;
		} // end of while 
	}
	else
	{
			//댓글만 삭제하도록 처리
		unlink("data/$_data/$board_group$board_id") ;
	}
	make_news($_data, $Row)  ;

	err_msg(_L_DELETE_COMPLETE) ;
	if(@file_exists("skin/$conf[skin]/cat.html") && 
		$idx_data[main_writing_delete] != "1" )
	{
		$url = "$C_base[url]/board/cat.php?data=$_data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
	}
	else
	{
		$url = "$C_base[url]/board/$conf[list_php]?data=$data&tot_page=$tot_page&cur_page=$cur_page" ;
	}
	redirect( $url, 1 ) ;
?>
