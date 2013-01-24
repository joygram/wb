<?php
$my_version = "WhiteBoard 2.4.1 2002/05/06" ;
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2002/1/2" ;
$my_version = "WhiteBoard 2.1.2 2002/1/12" ;
$my_version = "WhiteBoard 2.1.3 2002/1/12" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001, WhiteBBs.net, All rights reserved.

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
	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	include("../lib/wb.inc.php") ;
	include_once($C_base[dir]."/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	include_once("$C_base[dir]/lib/database.php") ;
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;

	$C_debug = 0 ;	

	//메인 글을 지우는 경우에는 답글도 모두 삭제한다.

		//필터링
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	$board_id = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_id) ;
	if( empty($data) )
	{
		err_abort("링크가 올바르지 않습니다.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	$conf_file = "$C_base[dir]/board/conf/{$C_data}.conf.php" ;
	if(@file_exists($conf_file))
	{
		include("$conf_file") ;
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

	$dbi = new db_interface($C_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

	$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
	if( empty($Row[user]) ) //글이 anonymous인가 아닌가 검사
	{
		// 2.1.2 이하 버젼은 모두 anonymous글이므로
			//암호화 되어 있으면 
		if( strlen($Row[password]) > 15 || $Row['encode_type'] == "1" )
		{
			$Row['password'] = wb_decrypt($Row[password], $Row[name]) ;
		}
		$check_data[passwd] = $Row['password']  ;
		if($C_debug) echo("$PHP_SELF:check_data[passwd][$check_data[passwd]]<br>") ;
	}
	else 
	{
		// member관리와 연동준비 2002/02/17
	}

	$auth->run_mode(EXEC_MODE) ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	$sess = $auth->member_info() ;

	if( $debug == "1" )
	{
		echo("$PHP_SELF $my_version") ;
		exit ;	
	}
	
	$index_name = "data" ;
	$idx_data = array("board_group" => $board_group, "board_id" => $board_id, "nWriting" => "-1"  ) ; 
	$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "delete") ;	

	if( $idx_data[main_writing_delete] == "1" )
	{
			// 전체 글개수 감소
		$fp = wb_fopen("$C_base[dir]/board/data/$C_data/total.cnt", "w") ;
			//
		fwrite($fp, $dbi->total ) ;
		fclose($fp) ;

		$flist = new file_list("$C_base[dir]/board/data/$C_data/", 1) ;
		$flist->read("$board_group") ;
		$nCnt = 0 ;
		while( ($file_name = $flist->next()) )
		{
			unlink("data/$C_data/$file_name") ;
		} // end of while 
	}
	else
	{
			//댓글만 삭제하도록 처리
		unlink("data/$C_data/$board_group$board_id") ;
	}

	$dbi->destroy() ;

	make_news($C_data, $Row)  ;

	err_msg("삭제 하였습니다.") ;
	if(@file_exists("skin/$C_skin/cat.html") && 
		$idx_data[main_writing_delete] != "1" )
	{
		$url = "$C_base[url]/board/cat.php?data=$C_data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
	}
	else
	{
		$url = "$C_base[url]/board/$LIST_PHP?data=$data&tot_page=$tot_page&cur_page=$cur_page" ;
	}
	redirect( $url, 1 ) ;
?>
