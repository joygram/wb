<?php
/*
Copyright (c) 2001-2005, WhiteBBS.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  
*/ 

	///////////////////////////
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	//기준이 되는 주소 받아오기, lib.php에 있음.
	$C_base = get_base(1) ; 

	$wb_charset = wb_charset($C_base[language]) ;
 	//권한,인증모듈 선언및 초기화 실행
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	require_once("$C_base[dir]/lib/wb.inc.php") ;

	require_once("$C_base[dir]/member/Member.php") ;


	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	// write mode define
	$write_mode = 0 ;
	define("__ANONYMOUS_WRITE", "1") ;
	define("__MEMBER_WRITE", "2") ;
	define("__ADMIN_WRITE", "3") ;

	// 시스템 변수등의 호환성을 위해. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;


	$C_debug = 1 ;

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	
	$C_data = empty( $data ) ? "member": $data ;
	

	$conf = read_member_config( $data ) ;


	// conf 파일 있는 지 검사 v 1.3.0
	$conf_file = "$C_base[dir]/member/conf/$data.conf.php" ;
	if( ! @file_exists( $conf_file ) ) err_abort( "write: $conf_file 파일이 존재하지 않습니다.") ;
	
	include($conf_file) ;

	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}

	//C_변수 이전버젼 호환성 유지
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	$_skindir = "$C_base[dir]/member/skin/$conf[skin]" ;	
	$_plugindir = "$C_base[dir]/member/plugin" ;


	// default conf변수 처리
	if( empty($conf[attach1_ext]))
	{
		$conf[attach1_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}
	if( empty($conf[attach2_ext]))
	{
		$conf[attach2_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
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


	$license  = license2() ;
	$license2 = license2() ;

	$URL = make_url($data, $Row, "member") ;

	$write_form = "write" ; 




	$_member = new Member() ;


	//////////////////////////////
	// 저장: 새로추가
	if( $mode == "insert" )
	{
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

		// 쿠키에서 header sent문제가 발생하여 순서를 바꿈
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		// data 무결성 검사 필요..
		//$password = wb_encrypt($password, $uname) ;	
		//$name = base64_encode($name) ;

		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //다양한 시간형식을 지원하기 위해서

		if( ! $auth->is_anonymous() )
		{
			if(empty($name)) $name = $auth->alias() ;

			$homepage = $auth->homepage() ;
			$email    = $auth->email() ;
		}
		
		//자료 보정
		// boolean값 보정 mysql에서 boolean지원을 안하기에... ㅡ_ㅡ
		$update_timestamp = $timestamp ;
		$birthday_select = ($birthday_select == "on")?'t':'f' ;
		$lunar_birth 	 = ($lunar_birth=='t')?1:0 ;
		$email_receive   = ($email_receive=='t')?1:0 ;
		$foreigner       = ($foreigner=='t')?1:0 ;
		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $idnum) ;
		
		//국내 거주자인 경우 성별보정
		if(empty($sex)) $sex='t' ;
		if(!empty($idnum) && !$foreigner)
		{
			if(substr($idnum, 6, 1) == "2") $sex = '0' ;
			$sex = ($sex=='t')?1:0 ;
		}

		
		$_member->set( "uid",  $uid ) ;
		$_member->set( "uname", $uname ) ;
		$_member->set( "gid", $gid ) ;
		$_member->set( "password", $password ) ;
		$_member->set( "alias", $alias ) ;
		$_member->set( "access_count", $access_count ) ;
		$_member->set( "point", $point ) ;
		$_member->set( "auth_level", $auth_level ) ;

		$_member->set( "name", $name ) ;
		$_member->set( "firstname", $firstname ) ;
		$_member->set( "sex", $sex ) ;
		$_member->set( "idnum", $idnum ) ;
		$_member->set( "birthday", $birthday ) ;
		$_member->set( "lunar_birth", $lunar_birth ) ;
		$_member->set( "email", $email ) ;
		$_member->set( "homepage", $homepage ) ;
		$_member->set( "mobilephone", $mobilephone ) ;

		$_member->set( "note", $note ) ;

		$_member->set( "final_scholarship", $final_scholarship ) ;
		$_member->set( "job_kind", $job_kind ) ;
		$_member->set( "foreigner", $foreigner ) ;

		$_member->set( "home_country", $home_country ) ;
		$_member->set( "home_city", $home_city ) ;
		$_member->set( "home_district", $home_district ) ;
		$_member->set( "home_address", $home_address ) ;
		$_member->set( "home_zipcode", $home_zipcode ) ;
		$_member->set( "home_phone", $home_phone ) ;
		$_member->set( "home_fax", $home_fax ) ;

		$_member->set( "company_country", $company_country ) ;
		$_member->set( "company_city", $company_city ) ;
		$_member->set( "company_district", $company_district ) ;
		$_member->set( "company_address", $company_address ) ;
		$_member->set( "company_zipcode", $company_zipcode ) ;
		$_member->set( "company_name", $company_name ) ;
		$_member->set( "company_department", $company_department ) ;
		$_member->set( "company_title", $company_title ) ;
		$_member->set( "company_phone", $company_phone ) ;
		$_member->set( "company_fax", $company_fax ) ;
		$_member->set( "company_homepage", $company_homepage ) ;

		$_member->set( "create_time", $timestamp ) ;
		$_member->set( "modify_time", $timestamp ) ;
		$_member->set( "login_time", $login_time ) ;
		$_member->set( "save_dir", $save_dir ) ;

		$_member->set( "password_clue", $password_clue ) ;
		$_member->set( "password_answer", $password_answer ) ;
		$_member->set( "email_receive", $email_receive ) ;


		$index_name = "data" ;
		$ret_data = $dbi->update_index($data, $index_name, $idx_data, "insert") ;	
		switch($ret_data)
		{
			case E_QUERY :
				err_abort("SQL요청 오류입니다.<br> 관리자에게 문의해주세요.") ; 
				break ;

			case E_USER_EXIST :
				err_abort("[$idx_data[uname]]사용자 이름은 사용중입니다. 다른 아이디를 선택해주세요.") ; 
				break ;
		}
		
		//@make_news($data, $Row) ;
		$idx_data = $ret_data ;

		//uid select
		$dbi->init($data, "member", "", "", $uname, "uname", $C_base[member_db_type], "", $C_base[dir]) ;
		$dbi->select_data() ;
		$one_row = $dbi->row_fetch_array(0,"","","member") ;

		$board_group = $one_row[0] ;
		$board_id    = $one_row[0] ;

		$dbi->destroy() ;

		//파일을 받았는지 check
		//파일을 원하는 디렉토리에 복사한 후 삭제한다.
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $C_attach1_ext, $InputFile_name ) )
			{
				err_abort("확장자가[$C_attach1_ext]인 파일만 올리실 수 있습니다."); 
			}
			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($InputFile, "$C_base[dir]/member/data/$data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		$remote_ip = $REMOTE_ADDR ;

		if( $C_subject_html_use != "1" )
		{
			//$subject = strip_tags($subject) ;
			$subject = htmlspecialchars($subject) ;
		}
		//$subject = base64_encode($subject) ;
		$encode_type = "1" ; // 1.4.5이후 자료들에 대해서 적용
		$uid = $W_SES[uid] ;
		$is_reply = "0" ;
		
		if( empty($C_name_html_use) )
		{
			$name = htmlspecialchars($name) ; 
		}

		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;

		//글내용과 정보 저장
		$head = array("") ;
		$head[0] = $password ; //사용안함.
		$head[1] = $name ; 
		$head[2] = $w_date ;
		$head[3] = $email ;
		$head[4] = $homepage ;
		$head[5] = $bgimg ;
		$head[6] = $InputFile_name ;
		$head[7] = $InputFile_size ;
		$head[8] = $InputFile_type ;

		$opt['is_notice'] = $notice_check ;
		$opt['html_use'] = $html_use ;

		$save_filename = "$board_group.$board_id" ;
		save_content($data, $save_filename, $head, $comment, $opt, $auth->is_anonymous()) ;

		err_msg("가입신청을 하였습니다.") ;
		
		$url="$C_base[url]" ;
		redirect( $url, 1 ) ;
		exit ;
	} 
	///////////////////////////////
	// 갱신 
	///////////////////////////////
	else if( $mode == "update" )
	{
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		if( !$auth->is_admin() && empty($password) )
		{
			err_abort("비밀번호를 넣어주세요.") ;
		}

		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $idnum) ;
		/*
		//갱신의 경우는 다른 사람것도 고칠 수 있기때문에 쿠키 설정을 하지 않는다.
		*/
		$field = 'uid' ;
		$key = $auth->uid() ;
		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		//register_shutdown_function($dbi->destroy()) ;

		$org = $dbi->row_fetch_array(0, $board_group, $board_id) ;
			// 성능 향상을 위한 꽁수
			// file_fetch_array에서는 본문에서 내용을 가지고 오는데 
			// 본문에 name, subject, type이 저장되어 있지 않으므로 
			// 인덱스에 있는 내용이 수정되었는지의 여부를 확인하려면 
			// 스킨의 write.html에 넣으두는 것이 제일 간단한 방법이다.
			// @todo if reply do not check index update... 
		$index_name = "data" ;
		$idx_data = $org ;
		$idx_data["password"] = $password ;
	  	$idx_data["alias"] = $alias ;
		$idx_data["lastname"] = $lastname ;
		$idx_data["firstname"]	= $firstname ;
		$idx_data["idnum"] = $idnum ;
		$idx_data["birthday"] = $birthday ;
		$idx_data["birthday_select"] = !empty($birthday_select)?'t':'f' ;
		$idx_data["email"]	= $email ;
		$idx_data["mobilephone"] = $mobilephone ;
		$idx_data["board_group"] = $board_group ;
		$idx_data["sex"] = 't' ;
		//$idx_data = array("board_group" => $board_group, "name" => $name, "subject" => $subject, "type" => $type ) ; 
		$idx_data = $dbi->update_index($data, $index_name, $idx_data, "update") ;	
			// 파일이 첨부되었을 경우...
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $C_attach1_ext, $InputFile_name ) )
			{
				err_abort("확장자가[$C_attach1_ext]인 파일만 올리실 수 있습니다."); 
			}
			unlink("$C_base[dir]/member/data/$data/${board_group}.".$org[InputFile_name]."_attach") ;

			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($InputFile, "data/$data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}
			//추가 처리 
		if( empty($InputFile_name) )
		{
			$InputFile_name = $org[InputFile_name] ;
			$InputFile_size = $org[InputFile_size] ;
			$InputFile_type = $org[InputFile_type] ;
		}

		//수정해도 변하지 않는 것들 원래대로 복원
		$w_date = $org[w_date] ;
		$remote_ip = $org[remote_ip] ;
		$timestamp = $org[timestamp] ;

		//$password = wb_encrypt($password, $name) ;	
		$encode_type = "1" ;
		$uid = $org[uid] ; //원글의 소유자 uid를 넣어준다.
		$is_reply = $org[is_reply] ;

		$head = array("") ;
		$head[0] = $password ;
		$head[1] = $name ; 
		$head[2] = $w_date ;
		$head[3] = $email ;
		$head[4] = $homepage ;
		$head[5] = $bgimg ;
		$head[6] = $InputFile_name ;
		$head[7] = $InputFile_size ;
		$head[8] = $InputFile_type ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;

		$save_filename = "${board_group}${board_id}" ;
		save_content($data, $save_filename, $head, $comment, $opt, $auth->is_anonymous()) ;
	
		err_msg("수정 하였습니다.") ;

		//가입후 가입완료 화면 
		
		if( @file_exists("$C_base[dir]/member/skin/$C_skin/cat.html") )
		{
			$url = "$C_base[url]/member/cat.php?data=$data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
		}
		else
		{
			$url="$C_base[url]/member/$LIST_PHP?data=$data&cur_page=$cur_page&tot_page=$tot_page&filter_type=$filter_type" ;
		}

		redirect( $url, 1 ) ;
		exit ;
	}
	///////////////////////////////
	// 수정 폼
	///////////////////////////////
	else if( $mode == "edit" || $mode == "edit_form" ) 
	{
		$mode = "update" ;

			//DBMS의 경우 SELECT조건 때문에..
			//2002/06/20
		if(empty($field))
		{
			$field = "uid" ;
			$key = $board_group ;
		}

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		
		if( $Row[uid] == __ANONYMOUS ) //anonymous가 쓴글이면...
		{
			// 2.1.2 이하 버젼은 모두 anonymous글이므로
				//암호화 되어 있으면 
			if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
			{
				$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
			}
			$check_data[passwd] = $Row[password]  ;
		}
		else // member가 쓴글이면
		{
			// member관리와 연동준비 2002/02/17
		}

			//check_data 때문에 이 위치가 되어야 한다. 
		$auth->run_mode(EXEC_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

			//1.
		$Row[subject] = stripslashes($subject) ;  
		$Row[subject] = str_replace('"', "&quot;", $Row[subject]) ;
		$Row[type] = $type ;
		$Row['html_use_checked'] = ($Row['html_use']==HTML_NOTUSE)?"":"checked" ;
		$Row['br_use_checked'] = $Row['br_use']?"checked":"" ;
		$Row[is_main_writing] = $main_writing ;
		//$Row[category_select] = category_select($data,$Row[type]) ;

			//수정이며 관리자의 경우에는 alias를 사용하므로. 2002/04/28
			//원래글의 아이디를 넣어주어야 한다.	
		if( ! $auth->is_anonymous() )
		{
			$Row['alias'] = $Row['name'] ;
		}
			//2.
		$hide = make_comment( $data, $Row, NOT_USE, "member" ) ;
			// write_mode set
		if($C_debug) echo("Row[uid]::[$Row[uid]]<br>") ;
	
		if($auth->is_admin())
		{
			//$hide['password'] = "<!--\n" ;
			//$hide['/password'] = "-->\n" ;

				//무명씨 글인 경우 암호를 제외한 모든 내용을 수정할 수 있다.
				//암호를 reset시킬 경우도 있으니까.. 저장시에만 skip할 수 있도록..
			if($Row[uid] == __ANONYMOUS) 
			{
				$hide['password'] = "<!--\n" ;
				$hide['/password'] = "-->\n" ;

				$hide['/*password'] = "/*\n" ;
				$hide['password*/'] = "*/\n" ;

					//관리자 모드가 아닌 것으로 처리 되어야 함.
				$hide['admin'] = "<noframes>\n" ;
				$hide['/admin'] = "</noframes>\n" ;
				$hide['anonymous'] ="" ;
				$hide['/anonymous'] ="" ;
			}
		}

		if( $C_edit_outer_header_use == "1" || 
			!isset($C_edit_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}
	}
	///////////////////////////////
	// 글쓰기 폼 WRITE
	///////////////////////////////
	else 
	{
			//모드가 지정이 안되어 있는 경우 : 처음 글쓰기로 간주...
		$mode = "insert" ;

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		if( ! $auth->is_anonymous() )
		{
			err_abort("로그인 중에는 가입하실 수 없습니다.") ;
		}

		$hide = make_comment($data, $Row, NOT_USE, "member") ;

		if( $C_write_outer_header_use == "1" || 
			!isset($C_write_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}

	}

	
	
	if( $outer_header_use == "1" ) 
	{
		// 외부 머리말 삽입
		for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
		{
			if( !empty($C_OUTER_HEADER[$i]) )
			{
				@include($C_OUTER_HEADER[$i]) ;
			}
		}
	}
	echo("<!--$my_version-->\n") ;

	$C_table_size = empty($C_table_size)?500:$C_table_size ; 
	$Row[table_size] = $C_table_size ;

		//HEADER 대소문자 구분없이 읽기 v 1.3.0 
		// 각모드에 맞는 header가 있으면 그 헤더를 이용
	if( @file_exists("$_skindir/${write_form}_header.html") )
    {
        include("$_skindir/${write_form}_header.html") ;
    }
	else if( @file_exists("$_skindir/HEADER") )
	{
		include("$_skindir/HEADER") ;
	}
	else if( @file_exists("$_skindir/header") )
	{
		include("$_skindir/header") ;
	}
	else
	{
		err_abort( "$_skindir/header 파일이 존재하지 않습니다." ); 
	}


	if( @file_exists("$_skindir/${write_form}_header") )
	{
		include("$_skindir/${write_form}_header") ;
	}	

	include "$_skindir/${write_form}.html" ;

	if( $outer_header_use == "1" ) 
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
	exit ;


	/**
		글의 내용부분을 저장하는 공통모듈
		@todo : 나중에 database쪽으로 옮겨야 되지 않을까?
	*/
	function save_content($data, $file, $head, $comment, $opt, $is_anonymous = 1)
	{
		global $C_base ;
		$C_debug = 0 ;	
		include("$C_base[dir]/lib/wb.inc.php") ;

		if($is_anonymous)
		{
			if(filter_name($data, $head[1], "member"))
			{
				err_abort("죄송합니다! [허용하지 않는 이름]입니다.") ;
			}
		}

		$head[1] = base64_encode($head[1]) ; // 2002/03/25 이곳에서 encoding.
		$cont_head = implode("|", $head) ; 

		$conf_file = "$C_base[dir]/member/conf/$data.conf.php" ;
		if( @file_exists($conf_file) )
		{
			include($conf_file) ;
		} 
		else
		{
			err_abort("save_content: $conf_file 파일이 존재하지 않습니다.") ;
		}

		if(filter_txt($data, $comment, "member"))
		{
			err_abort("죄송합니다! [허용하지 않는 내용, 광고, 욕설]은 타인에게 피해가 될 수 있으므로 올리실 수 없습니다.") ;
		}

		if($C_debug) echo("C_html_use[$C_html_use] opt[html_use][$opt[html_use]]<br>") ;

		switch($opt['html_use'])
		{
			case HTML_NOTUSE:
				if($C_debug) echo("not use html<br>") ;
				$comment = htmlspecialchars($comment) ;
				break ;

			case HTML_FILTER:
				$comment = block_tags($comment, $C_block_tag) ;
				break ;

			case HTML_USE:
			default:
				break ;
		}

		if( $opt[is_notice] == "on" )
		{
			$save_filename = "${file}_notice" ;
		}
		else
		{
			$save_filename = "$file" ;
		}

		$tmp_file = "$C_base[dir]/member/data/$data/".md5(uniqid("")); 
		
		if($C_debug) echo("tmp_file[$tmp_file]<br>") ;

		$fp = @fopen($tmp_file, "w") ;
		if( !$fp )
		{
			err_abort("[$C_base[dir]/member/data/$data/$tmp_file] 파일을 쓰기위해 여는데 실패했습니다.") ;
		}
		fwrite($fp, "$cont_head\n$comment") ;
		fclose($fp) ;

		if( @file_exists("$C_base[dir]/member/data/$data/$save_filename") )
		{
			unlink("$C_base[dir]/member/data/$data/$save_filename") ;
		}
        rename("$tmp_file", "$C_base[dir]/member/data/$data/$save_filename") ;

	}
	
?>
