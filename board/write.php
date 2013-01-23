<?php
/*
Whitebbs 2.8.0 2003/12/27 
see also HISTORY.TXT 

Copyright (c) 2001-2004, WhiteBBs.net, All rights reserved.

소스와 바이너리 형태의 재배포와 사용은 수정을 가하건 그렇지 않건 아래의 조건을 만족할 경우에 한해서만 허용합니다:

1. 소스코드를 재배포 할때는 위에 명시된 저작권 표시와 이 목록에 있는 조건와 아래의 성명서를 반드시 표시 해야만 합니다.

2. 실행할 수 있는 형태의 재배포에서는 위의 저작권 표시와 이 목록의 조건과 아래의 성명서의 내용이 그 문서나 같이 배포되는 다른 것들에도 포함 되어야 합니다.

3. 본 소프트웨어로부터 파생되어 나온 제품에 구체적인 사전 서면 승인 없이 화이트보드팀의 이름이나 그 공헌자의 이름으로 보증이나 제품판매를 촉진할 수 없습니다.  


저작권을 가진사람과 그 공헌자는 본 소프트웨어를 ‘원본 그대로’ 제공할 뿐 어떤 표현이나 이유 등을 갖는 특별한 목적을 위한 매매보증이나 합목적성을 부인합니다. 저작권을 가진 사람이나 공헌자는 어떠한 직접적, 간접적, 부수적, 특정적, 전형적, 필연적인 손상, 즉 상품이나 서비스의 대체, 사용상의 손실, 데이터의 손실, 이득, 영업의 중단 등에 대해 그것이 어떠한 원인에 의한 것이나, 어떠한 책임론에 의하거나, 계약에 의하거나 절대적인 책임, 민사상 불법행위가 과실에 의하거나 그렇지 아니하거나 본 소프트웨어의 사용중에 발생한 손상에 대해서는 비록 그것이 이미 예고된 것이라 할지라도 책임이 없습니다.  
*/ 

//write.php재구성 필요.
	/**
		글의 내용부분을 저장하는 공통모듈
		@todo : 나중에 database쪽으로 옮겨야 되지 않을까?
	*/
	function save_content($_data, $file, $head, $comment, $opt, $conf, $is_anonymous = 1)
		//$conf[block_tag] 가 넘어오지 않아 $conf 추가함  2004.01.13 by 체리토마토
	{
		global $C_base ;
		$_debug = 0 ;	
		require_once("$C_base[dir]/lib/wb.inc.php") ;

		if($is_anonymous)
		{
			if(filter_name($_data, $head[1]))
			{
				err_abort(_L_FOBIDDEN_NAME) ;
			}
		}

		$conf_file = "$C_base[dir]/board/conf/${_data}.conf.php" ;
		if( @file_exists($conf_file) )
		{
			include($conf_file) ;
		} 
		else
		{
			err_abort("save_content: $conf_file %s", _L_NOFILE) ;
		}

		if(filter_subject($_data,$opt['subject']))
		{
			err_abort(_L_FORBIDDEN_SUBJECT) ;
		}

		if(filter_ip($_data, $head[13]))
		{
			err_abort(_L_FORBIDDEN_IP) ;
		}
		
		$filtered_word = filter_txt($_data, $comment);		
		if (!empty($filtered_word))
		{			
			err_abort("${filtered_word}%s",_L_FORBIDDEN_CONTENT) ;
		}

		if($_debug) echo("conf[html_use][$conf[html_use]] opt[html_use][$opt[html_use]]<br>") ;

		$cmt_token = wb_token($comment) ;
		if($_debug) echo("[".count($cmt_token["cont"])."]<br>") ;
		$tmp_comment = "" ;
		for($i = 0; $i < count($cmt_token["cont"]); $i++ )
		{
			if($_debug) echo("B] $i:: {$cmt_token["cont"][$i]}<br>") ;
			// NORMAL인 경우에만 comment 내용 처리 
			if( $cmt_token["attr"][$i] == "NORMAL" )
			{
				switch($opt['html_use'])
				{
					case HTML_NOTUSE:
						if($_debug) echo("not use html<br>") ;
						$cmt_token["cont"][$i] = block_tags($cmt_token["cont"][$i], "ALL") ;
						break ;

					case HTML_FILTER:
						$cmt_token["cont"][$i] = block_tags($cmt_token["cont"][$i], $conf[block_tag]) ;
						break ;

					case HTML_USE:
					default:
						break ;
				}
			}
			if($_debug) echo("A] $i:: {$cmt_token["cont"][$i]}<br>") ;
			$tmp_comment .= $cmt_token["cont"][$i] ;
		}
		$comment = $tmp_comment ;

		if( $opt[is_notice] == "on" )
		{
			$save_filename = "${file}_notice" ;
		}
		else
		{
			$save_filename = "$file" ;
		}

		$head[1] = base64_encode($head[1]) ; // 2002/03/25 이곳에서 encoding.
		$cont_head = implode("|", $head) ; 

		$tmp_file = "$C_base[dir]/board/data/$_data/".md5(uniqid("")); 
		
		if($_debug) echo("tmp_file[$tmp_file]<br>") ;

		$fp = @fopen($tmp_file, "w") ;
		if( !$fp )
		{
			err_abort("[$C_base[dir]/board/data/$_data/$tmp_file]%s", _L_NOWRITE_PERM) ;
		}
		fwrite($fp, "$cont_head\n$comment") ;
		fclose($fp) ;

		if( @file_exists("$C_base[dir]/board/data/$_data/$save_filename") )
		{
			unlink("$C_base[dir]/board/data/$_data/$save_filename") ;
		}
        rename("$tmp_file", "$C_base[dir]/board/data/$_data/$save_filename") ;
	}
	
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
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

	///////////////////////////
	umask(0000) ;//웹서버의 기본 umask를 지워준다.
	///////////////////////////
	//unset() x-y.net php에서 이상한 오류로 변수 초기화로 변경 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	//스팸방지기능
	//2.6이전버젼에는 기능에 필요한 uniq_num이 없으므로 이곳에서 생성후 시스템설정을 자동으로 업그레이드 하도록 한다.
	if(empty($C_base["uniq_num"]))
	{
		//값이 없는 경우 생성 해줌
		$uniq_num_lists = get_uniq_num_list() ;
		$uniq_num = implode("", $uniq_num_lists) ;
		//system.ini갱신
		$system_conf = "{$C_base[dir]}/system.ini.php" ;		
		$C_base[uniq_num] = $uniq_num ;
		save_system_ini($system_conf, $C_base) ;
		
		if($_debug) echo("generate system uniq_num and update system.ini.php uniq_num[{$C_base[uniq_num]}]<br>") ;
	}


	// 시스템 변수등의 호환성을 위해. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	// write mode define
	$write_mode = 0 ;
	define("__ANONYMOUS_WRITE", "1") ;
	define("__MEMBER_WRITE", "2") ;
	define("__ADMIN_WRITE", "3") ;

	$_debug = 0 ;
	if($_debug) 
		$redirect_delay = 5 ;
	else 
		$redirect_delay = 1 ;

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	if( empty($data) )
	{
		err_abort("data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	///////////////////////////
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/board/skin/$skin/write.html") )
	{
		$conf[skin] = $skin ;
	}
	//C_변수 이전버젼 호환성 유지
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;	
	$_plugindir = "$C_base[dir]/board/plugin" ;

	// default conf변수 처리
	if( empty($conf[attach1_ext]))
	{
		$conf[attach1_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}
	if( empty($conf[attach2_ext]))
	{
		$conf[attach2_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}

	//관리자 도구의 HTML사용 설정변수 처리 2002/01/24
	$conf[html_use] = empty($conf[html_use])?0:$conf[html_use] ;
	if($_debug) echo("conf::html_use [$conf[html_use]]<br>") ;
	if( empty($conf[html_use]) )
	{
		$html_use = HTML_NOTUSE ;
	}
	else
	{
		// 2002/05/08 special_html_use:새로운 스킨에서 html사용 유무를 확인하기 위해서 
		// checkbox에서 유무의 set을 확인할 수 없다.
		if( empty($html_use) )
		{
			$html_use = HTML_NOTUSE ;
		}
		else if ($conf[block_tag])
		{
			$html_use = HTML_FILTER ;
		}
		else
		{
			$html_use = HTML_USE ; 
		}
		$html_use = isset($special_html_use)?$html_use:HTML_USE ;
	}

	//2002/03/18 기본 권한값지정
	if( !isset($conf[auth_perm]))
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

	$license  = license2() ;
	$license2 = license2() ;
	$new_license = license($conf[skin], $conf) ;
	$release = get_release($C_base) ;

	$URL = make_url($_data, $Row) ;
	$Row[board_title] = "$conf[board_title]" ;	
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
	// if mode is reply_form then, write_form is reply.html
	$write_form = "write" ; 

	if($_debug) echo("[$mode]<br>") ;
	//////////////////////////////
	// 저장: 새로추가
	/////////////////////////////
	if( $mode == "insert" )
	{
		if($_debug) echo("write mode is [$mode]<br>") ;

		///////// [스팸처리] 2004.9.15	
		 //$spam_check 는 $conf[spam_check_use]  가 1 일때 만들어지고 write form 에서 hidden 으로 들어온다.
		 // spam_check 가 1인데 name 으로 값이 들어온다면 스팸이다.
		 // name 이 비어 있다면 wb_[unicq_num] 으로 들어온 값을 이름으로 치환해 주면된다.
		if ($spam_check_use =="1")
		{
			if(!($name=="")) 
				{
					// 이곳에서 스팸인 경우 처리를 한다.	
					//$subject ="[spam]".$subject;									
					$subject ="[From ".$_data." ]".$subject;
					$_data="_spam";
				}
				else 
				{	
					$wb_name_tmp ="wb_".$C_base[uniq_num];
					$name =$$wb_name_tmp ;								
				}
		}
		else  // 다른 고려가 필요한 경우가 있을까?
		{
			
		}
		//////////////////////////////////

		$auth->run_mode(WRITE_MODE) ;
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

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		if(empty($status) && $notice_check == "on" )
		{
			//err_abort("공지글은 해당 관리자만 쓸 수 있습니다") ;	
		}

		if($conf[cookie_use] == "1" && $auth->is_anonymous())
		{
			setcookie("cw_name",  $name,     time()+604800, "/") ;
			setcookie("cw_email", $email,    time()+604800, "/") ;
			setcookie("cw_home",  $homepage, time()+604800, "/") ;
		}	

		// 쿠키에서 header sent문제가 발생하여 순서를 바꿈
		$release = get_release($C_base) ;
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		// data 무결성 검사 필요..
		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //다양한 시간형식을 지원하기 위해서

		//$total = get_total_cnt($_data, "data") ;
		//이미지박스 지원을 위해서 이부분을 수정필요.
		//이미 존재한다면 받은 ID로 넣어준다.
		if(empty($board_group))
		{
			$board_group = uniqid("D") ;	
			$board_id = uniqid(".") ;
		}
		else
		{
			$board_id = $board_group ;
			$board_group .= "D" ;
		}

		//파일을 받았는지 check
		//파일을 원하는 디렉토리에 복사한 후 삭제한다.
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $conf[attach1_ext], $InputFile_name ) )
			{
				err_abort("[$conf[attach1_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($InputFile, "$C_base[dir]/board/data/$_data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		if( $InputFile2 != "none" && !empty($InputFile2) )
		{
			if( !check_string_pattern( $conf[attach2_ext], $InputFile2_name ) )
			{
				err_abort("[$conf[attach2_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			$attach2_file = "${board_group}.${InputFile2_name}_attach2" ;
			// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($InputFile2, "$C_base[dir]/board/data/$_data/$attach2_file") ;
		}
		if( $InputFile2_size == 0 )
		{
			$InputFile2_name = "" ;
		}

		$remote_ip = $REMOTE_ADDR ;
		$password = wb_encrypt($password, $name) ;	
		//$name = base64_encode($name) ;
		if( $conf[subject_html_use] != "1" )
		{
			//$subject = strip_tags($subject) ;
			//$subject = htmlspecialchars($subject) ;
			$subject = str_replace("<", "&lt;", $subject) ;
			$subject = str_replace(">", "&gt;", $subject) ;
		}
		//$subject = base64_encode($subject) ;
		$encode_type = "1" ; // 1.4.5이후 자료들에 대해서 적용
		$timestamp = time() ;
		$uid = $W_SES[uid] ;
		$is_reply = "0" ;
		
		if( empty($conf[name_html_use]) )
		{
			//$name = htmlspecialchars($name) ; 
			$name = str_replace("<", "&lt;", $name) ;
			$name = str_replace(">", "&gt;", $name) ;
		}
		//2002/04/21
		//2002/06/29
		if(!$auth->is_anonymous())
		{
			if(empty($name))     $name     = $auth->alias() ;
			if(empty($homepage)) $homepage = $auth->homepage() ;
			if(empty($email))    $email    = $auth->email() ;
		}
		//이전에 스킨에는 없는 기능 이니까..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		$secret = ($secret == "on")? "1" : "0" ; 

		//저장하기 전에 플러그인 넣기
		$plug[insert] = include_plugin("insert", $_plugindir, $conf) ;
		//글내용과 정보 저장
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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ;
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;		//비밀글 여부 2005/08/04(목) 아폴로 
		$head[21] = $secret_passwd ;

		$opt['is_notice'] = $notice_check ;
		$opt['html_use'] = $html_use ;
		$opt['subject'] = $subject ;

		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt, $conf, $auth->is_anonymous()) ;

			// 인덱스 갱신
		$cnt = 0 ; $cnt2 = "0" ; $cnt3 = "0" ; $cnt4 = "0" ; 
		$nReply = "0" ; $nWriting = 1 ;

		$update_timestamp = $timestamp ;
		$idx_data = array( 
			"board_group" => $board_group, 
			"board_id" => $board_id, 
			"name" => $name, 
			"cnt" => $cnt, 
			"cnt2" => $cnt2, 
			"subject" => $subject, 
			"type" => $type, 
			"cnt3" => $cnt3, 
			"save_dir" => $save_dir, 
			"encode_type" => $encode_type, 
			"update_timestamp" => $update_timestamp, 
			"cnt4" => $cnt4, 
			"nReply" => $nReply,
			"nWriting" => $nWriting, 
			"subject_color" => $subject_color,
			"mail_reply"    => $mail_reply,
			"extra" => $extra,
			"" => "", 
		) ;

		if($_debug) echo("mail_reply[$mail_reply]") ;
		if( $notice_check == "on") //공지 글인경우
		{
			$index_name = "notice" ;
		}
		else
		{
			$index_name = "data" ;
			$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "insert") ;	
			update_total_cnt($_data, $index_name, +1) ; // total.cnt

			@make_news($_data, $Row) ;
		}
		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;

		err_msg(_L_SAVE_COMPLETE) ;
		$url="$C_base[url]/board/$conf[list_php]?data=$_data&cur_page=$cur_page&tot_page=$tot_page" ;
		redirect( $url, $redirect_delay ) ;
		exit ;
	} 
	///////////////////////////////
	// 저장 : 갱신 
	///////////////////////////////
	else if( $mode == "update" )
	{
		$auth->run_mode(WRITE_MODE) ;
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

		//////// [스팸 처리]를 위한 변수 삽입  2004.09.25 by 체리토마토		
		
		$wb_name = "name";

		//////////////////////////////////////////////////	

		//이전에 스킨에는 없는 기능 이니까..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		if( !$auth->is_admin() && empty($password) && !$passwd_exist)
		{
			err_abort(_L_PASSWORD_REQUIRED) ;
		}
		/*
		//갱신의 경우는 다른 사람것도 고칠 수 있기때문에 쿠키 설정을 하지 않는다.
		*/
		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$org = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		// 성능 향상을 위한 꽁수
		// file_fetch_array에서는 본문에서 내용을 가지고 오는데 
		// 본문에 name, subject, type이 저장되어 있지 않으므로 
		// 인덱스에 있는 내용이 수정되었는지의 여부를 확인하려면 
		// 스킨의 write.html에 넣으두는 것이 제일 간단한 방법이다.
		// @todo if reply do not check index update... 
		if( $name != $org['name'] || 
			$subject != $subject_org || 
			$type != $type_org || 
			$subject_color != $org['subject_color'] )
		{
			$index_name = "data" ;
			$idx_data = array("board_group" => $board_group, "name" => $name, "subject" => $subject, "type" => $type ) ; 
			$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "update") ;	
		}
			// 파일이 첨부되었을 경우...
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $conf[attach1_ext], $InputFile_name ) )
			{
				err_abort("[$conf[attach1_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			@unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach") ;

			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir 제한때문에 수정.
			move_uploaded_file($InputFile, "data/$_data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

	
		if( $InputFile2 != "none" && !empty($InputFile2) )
		{
			if( !check_string_pattern( $conf[attach2_ext], $InputFile2_name ) )
			{
				err_abort("[$conf[attach2_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			@unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2" ) ;
			$attach2_file = "${board_group}.${InputFile2_name}_attach2" ;
				//2002/03/26 open_basedir 제한 때문에 수정
			move_uploaded_file($InputFile2, "data/$_data/$attach2_file") ;
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

		if( empty($InputFile2_name) )
		{
			$InputFile2_name = $org[InputFile2_name] ;
			$InputFile2_size = $org[InputFile2_size] ;
			$InputFile2_type = $org[InputFile2_type] ;
		}

			//첨부파일 제거 추가 2003/12/26 
		if($remove_attach1 == "on")
		{
			if(file_exists("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach"))
			{
				unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach") ;
			}
			$InputFile_name = "" ;
		}
		if($remove_attach2 == "on")
		{
			if(file_exists("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2"))
			{
				unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2") ;
			}
			$InputFile2_name = "" ;
		}


		$secret = ($secret == "on")? "1" : "0" ; 

		//수정해도 변하지 않는 것들 원래대로 복원
		$w_date = $org[w_date] ;
		$remote_ip = $org[remote_ip] ;
		$timestamp = $org[timestamp] ;

		if( ($auth->is_admin() && empty($password)) || 
			$passwd_exist && empty($password) )
		{
			$password = $org[password] ;
		}
		else
		{
			$password = wb_encrypt($password, $name) ;	
		}

		$encode_type = "1" ;
		$uid = $org[uid] ; //원글의 소유자 uid를 넣어준다.
		$is_reply = $org[is_reply] ;

		//저장하기 전에 플러그인 넣기
		$plug[update] = include_plugin("update", $_plugindir, $conf) ;

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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ; 
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;		//비밀글 여부 2005/08/04(목) 아폴로 
		$head[21] = $secret_passwd ;

		$opt['html_use'] = $html_use ;
		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt,$conf, $auth->is_anonymous()) ;	   
	
		@make_news($_data, $Row) ;
		err_msg(_L_EDIT_COMPLETE) ;
		if ($to)
		{
			$url="$C_base[url]/board/$to?data=$_data" ;
			if($to=="cat.php") $url .= "&board_group=$board_group" ;
		}
		else if( @file_exists("$_skindir/cat.html") )
		{
			$url = "$C_base[url]/board/$conf[cat_php]?data=$_data&board_group=$board_group" ;
		}
		else
		{
			$url = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		}
		$url .= !empty($cur_page)?"&cur_page=$cur_page":"" ;
		$url .= !empty($filter_type)?"&filter_type=$filter_type":"" ;

		redirect( $url, $redirect_delay ) ;
		exit ;
	}
	///////////////////////////////
	// 저장 : 답글 
	///////////////////////////////
	else if( $mode == "reply" ) 
	{
		if($_debug) echo ("reply:conf[auth_reply_perm]:$conf[auth_reply_perm]<br>") ;
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_reply_perm], $check_data) ;
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

		///////// [스팸처리] 2004.9.15		
		if ($spam_check_use  =="1") 
		{
			if(!($name==""))
				{
					// 이곳에서 스팸인 경우 처리를 한다.	
				echo "잘못된 접근";
				exit;
				}
				else 
				{	
					$wb_name_tmp ="wb_".$C_base[uniq_num];
					$name =$$wb_name_tmp ;								
				}
		}
		else  // 다른 고려가 필요한 경우가 있을까?
		{
			
		}
		//////////////////////////////////

		if( $board_group == "" )
		{
			err_abort(_L_RETRY) ;
		}
		if( $name == "" || $comment == "" )
		{
			err_abort(_L_NAME_CONTENT_REQUIRED) ;
		}

		if($conf[cookie_use] == "1" && $auth->is_anonymous())
		{
			setcookie("cw_name",  $name,     time()+604800, "/") ;
			setcookie("cw_email", $email,    time()+604800, "/") ;
			setcookie("cw_home",  $homepage, time()+604800, "/") ;
		}

		
		if(strstr($__SERVER["HTTP_REFERER"], "cat.php"))
		{
			$_write_html = (file_exists("$_skindir/$sess_name/cat.html"))?"$_skindir/$sess_name/cat.html":"$_skindir/cat.html" ;
		}
		else if(strstr($__SERVER["HTTP_REFERER"], "list.php") )
		{
			$_write_html = (file_exists("$_skindir/$sess_name/list.html"))?"$_skindir/$sess_name/list.html":"$_skindir/list.html" ;
		}
		else
		{
			$write_form = "reply" ;
			if( ! @file_exists( "$_skindir/${write_form}.html") ) 
			{
				$write_form = "write" ;
			}
			$_write_html = (file_exists("$_skindir/$sess_name/{$write_form}.html"))?"$_skindir/$sess_name/{$write_form}.html":"$_skindir/{$write_form}.html" ;
		}

		$write_skin_contents = join ('', @file("$_write_html")) ;

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		//쿠키설정에서 header sent의 문제때문에.. 이곳에..
		$release = get_release($C_base) ;
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		//폼에서 넘어온 board_id를 저장시켜 메일답변할때 사용한다.
		$main_board_id = !isset($main_board_id)?$board_id:$main_board_id ;
		$board_id = uniqid(".") ;

		// reply cookie setting required. 
		$password = wb_encrypt($password, $name) ;	

		$remote_ip = $REMOTE_ADDR ;
		$encode_type = "1" ;

		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //다양한 시간 형식을 지원하기 위해서 1.4.5

		$uid = $W_SES[uid] ;
		$is_reply = 1 ;
		if (empty($conf[name_html_use]))
		{
			//$name = htmlspecialchars($name) ; 
			$name = str_replace("<", "&lt;", $name) ;
			$name = str_replace(">", "&gt;", $name) ;
		}
		//2002/04/21
		//2002/06/29
		if(!$auth->is_anonymous())
		{
			if(empty($name))     $name     = $auth->alias() ;
			if(empty($homepage)) $homepage = $auth->homepage() ;
			if(empty($email))    $email    = $auth->email() ;
		}
		//이전에 스킨에는 없는 기능 이니까..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		//저장하기 전에 플러그인 넣기
		$plug[reply] = include_plugin("reply", $_plugindir, $conf) ;

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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ; 
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;

		$opt['html_use'] = $html_use ;
		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt,$conf, $auth->is_anonymous()) ;		

		////////////////////////////////////////////////////
		//같은 보드그룹을 찾아서 날짜, 답글 개수 인덱스에서 갱신 
		////////////////////////////////////////////////////
		$idx_data = array( "board_group" => $board_group, "update_timestamp" => time(), "nReply" => "1", "nWriting" => "1" ) ; 
		$index_name = "data" ;
		$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "update") ;	
		$subject = empty($subject)?base64_decode($idx_data[subject]):$subject ;

		@make_news($_data, $Row) ;

		//다시 index_select해서 메일을 보낸다.
		//맨처음글의 정보와 인덱스 받아오기
		$Row = $dbi->row_fetch_array(0, $board_group, $main_board_id ) ;
		if($_debug) echo("mail_reply:[$Row[mail_reply]]<br>") ;
		if( $Row['mail_reply'] == "on" && !empty($Row[email]))
		{
			if($_debug) echo("reply mail ...") ;
			/* message */
			$style = "<style>
				.wBody 
				{
					background-color: #FFFFFF;
					scrollbar-face-color:#F7F7F7; 
					scrollbar-shadow-color:#cccccc ;
					scrollbar-highlight-color: #FFFFFF;
					scrollbar-3dlight-color: #FFFFFF;
					scrollbar-darkshadow-color: #FFFFFF;
					scrollbar-track-color: #FFFFFF;
					scrollbar-arrow-color: #cccccc
					font-family: Verdana, 굴림체;
					font-size: 12px;
					line-height: 20px;
				};
				A:hover {text-decoration:none; color:#CCCCCC}
				A:link {text-decoration:none; color:#666666}
				A:visited {text-decoration:none; color:#666666}
				A:active {text-decoration:none; color:#666666}
				.copyright {font-family:Verdana; font-size:8pt; color:#c0c0c0}
				</style>
				<body class='wBody'>" ;

			$copyright = "<p><table width='100%' border=0 cellpadding=0  cellspacing=0 class='wAdmin'>
				<tr><td align='right' class='copyright'>
				CopyRight(C) 2001-2002, <a href='http://whitebbs.net'>WhiteBBS.net</a> All Right Reserved.&nbsp;
				</td></tr></table>" ;

			/* recipients */
			$mailto  = "$Row[name] <$Row[email]>" ;
			$subject = "RE: $Row[subject]" ; 
			$message  = $style ;	
			//$message .= $copyright."<br>" ; 
			$message .= "■ $name 님의 답변 ■<br>" ;
			$message .= nl2br($comment)."<p>" ;
			$message .= "■ $Row[name] 님의 질문 ■<br>";
			$message .= nl2br($Row[comment]);
			$message .= $copyright ; 

			/* To send HTML mail, you can set the Content-type header. */
			$headers  = "Return-Path: <$email>\r\n";
			$headers .= "From: $name <$email>\r\n";
			//$headers .= "X-Sender: <$email>\r\n";
			$headers .= "X-Mailer: WhiteBBS Mailer\r\n"; //mailer

			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=euc-kr\r\n";

			/* and now mail it */
			$success = mail($mailto, $subject, $message, $headers);
			if($_debug) echo("mail_reply:board_id[$main_board_id]<br>") ;
			if($_debug) echo("mail_reply:from email[$email]<br>") ;
			if($_debug) echo("mail_reply:from name[$name]<br>") ;
			if($_debug) echo("mail_reply:to Row[email][$Row[email]]<br>") ;
			if($_debug) echo("mail_reply:to Row[name][$Row[name]]<br>") ;
			if($_debug) echo("mail_reply:Row[comment]$Row[comment]<br>") ;
			if($_debug) echo("mail_reply:REPLY[$comment]<br>") ;

			if(!$success)
			{
				//echo("메일을 제대로 전송하지 못했습니다.<br>") ;
				echo(_L_MAILESEND) ;
			}
		}

		err_msg(_L_SAVE_COMPLETE) ;
		if ($to)
		{
			$url="$C_base[url]/board/$to?data=$_data" ;
		}
		//cat.html이 있다면 게시판 형태의 스킨이므로...
		else if( @file_exists("$_skindir/cat.html") )
		{
			$url = "$C_base[url]/board/$conf[cat_php]?data=$_data&board_group=$board_group" ;
		}
		else
		{
			$url = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		}
		$url .= !empty($cur_page)?"&cur_page=$cur_page":"" ;
		$url .= !empty($filter_type)?"&filter_type=$filter_type":"" ;
		redirect( $url, $redirect_delay ) ;
		exit ;
	}
	///////////////////////////////
	///////////////////////////////
	// 수정 폼
	///////////////////////////////
	///////////////////////////////
	else if( $mode == "edit" || $mode == "edit_form" ) 
	{
		$mode = "update" ;
		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		if( $Row[uid] == __ANONYMOUS ) //anonymous가 쓴글이면...
		{
			if($_debug) echo("anonymous writing<br>") ;
			// 2.1.2 이하 버젼은 모두 anonymous글이므로
			//암호화 되어 있으면 
			if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
			{
				$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
			}
			$check_data[passwd] = $Row[password]  ;
			$Row[passwd_exist] = !empty($Row[password])?"1":"" ;	
		}
		else // member가 쓴글이면
		{
			// 2002/10/22 스킨에서 비밀번호 2.5용으로 수정을 안했을 경우 비밀번호 입력 여부 안묻도록
			$Row[passwd_exist] = 1 ;
			// member관리와 연동준비 2002/02/17
		}

		//check_data 때문에 이 위치가 되어야 한다. 
		if($_debug) print_r($check_data) ;
		$auth->run_mode(EXEC_MODE) ;
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

		//1.
		//$Row[subject] = stripslashes($subject) ;  
		//$Row[subject] = str_replace('"', "&quot;", $Row[subject]) ;
		//$Row[type] = $type ;
		$Row['html_use_checked'] = ($Row['html_use']==HTML_NOTUSE)?"":"checked" ;
		$Row['br_use_checked'] = ($Row['br_use']=="yes")?"checked":"" ;
		$Row[is_main_writing] = $main_writing ;
		$Row[category_select] = category_select($_data,$Row[type]) ;
		$Row[to] = $to ;
		$Row['mail_reply_checked'] = ($Row['mail_reply']=="on")?"checked":"" ;
		if($_debug) echo("Row[mail_reply][$Row[mail_reply]]<br>") ;

		//수정이며 관리자의 경우에는 alias를 사용하므로. 2002/04/28
		//원래글의 아이디를 넣어주어야 한다.	
		if(!$auth->is_anonymous())
		{
			$Row['alias'] = $Row['name'] ;
		}
		//2.
		$hide = make_comment( $_data, $Row ) ;

		// write_mode set
		if($_debug) echo("Row[uid]::[$Row[uid]]<br>") ;
	
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
		if( $conf[edit_outer_header_use] == "1" || 
			!isset($conf[edit_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// 외부 머리말 삽입
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}


		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];
		///////////////////////////////////
		// 폼 삽입
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER 대소문자 구분없이 읽기 v 1.3.0 
		// 각모드에 맞는 header가 있으면 그 헤더를 이용
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin 삽입
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin 삽입
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		//수정을 누른 위치로 돌아가기 위해서 추가
		//2002/09/15 가급적이면 인터페이스로 빼자 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin 삽입
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin 삽입
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//각모드에 맞는 footer가 있으면 그 헤더를 이용
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
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//라이센스 무조건 출력 2002/09/23
		echo $new_license ;
		if( $outer_header_use == "1" ) 
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
		exit ;


	}
	///////////////////////////////
	///////////////////////////////
	// 답글 폼
	///////////////////////////////
	///////////////////////////////
	else if( $mode == "reply_form" ) 
	{
		$mode = "reply" ;

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_reply_perm], $check_data) ;
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
		
		$write_form = "reply" ;
		if( ! @file_exists( "$_skindir/${write_form}.html") ) 
		{
			// not exit but load write.html
			$write_form = "write" ;
		}
		//게시판 형태일 경우에만 $board_id가 넘어온다. 2001.08.31
		// $board_id가 넘어오는 경우에만 내용을 읽어서 보여준다.
		//1.		
		
		if(!empty($board_id))
		{
			$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
			$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;			
			if($conf[url2link_use] == "1") 
			{
				//자동으로 링크 만들기 옵션선택시 1.2.2
				$Row[comment] = url2link( $Row[comment] ) ;
			}
			//처음쓴글 보게하려고... 
			// 2002/04/06 $Row[main_writing]은 오래됐음.
			$Row[main_writing] = nl2br($Row[comment]) ;
			$Row[main_comment] = $Row[main_writing] ; 
			$Row[comment] = "" ;			
		}		
		$Row['br_use_checked'] = "checked" ;
		// 답글이고 reply폼이 따로 있으면 그것을 띄워준다.
		$Row[is_main_writing] = 0 ;

		/////// [스팸 처리]를 위한 변수 삽입  2004.09.25 by 체리토마토		
		if (($conf[spam_check_use] =="1")&&($sess_name == ""))		
		{
			$wb_name = "wb_".$C_base["uniq_num"];	
			$spam_check_form = "[spam_check_use]<input type=hidden name='spam_check_use' value='1'><br><input type=text name=name class=wForm>[-->spam check]";
			//$spam_check_form ="<input type=hidden name='spam_check_use' value='1'><span style=display:none;><input type=text name=name class=wForm ></span>";	
		}
		else $wb_name = "name";
		//////////////////////////////////////////////////

		if(!$auth->is_anonymous())
		{
			$Row['alias']    = $auth->alias() ;
			$Row['email']    = $auth->email() ;
			$Row['homepage'] = $auth->homepage() ;
		}

		//2.
		$hide = make_comment( $_data, $Row ) ;

		if( $conf[reply_outer_header_use] == "1" || 
			!isset($conf[reply_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// 외부 머리말 삽입
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}


		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];
		///////////////////////////////////
		// 폼 삽입
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER 대소문자 구분없이 읽기 v 1.3.0 
		// 각모드에 맞는 header가 있으면 그 헤더를 이용
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin 삽입
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin 삽입
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		//처음글의 내용을 읽어오기 위해file_fetch_array를 하면 main_writing의 데이터가 $Row에 들어가니까 write_header 후에 쿠기 변수 처리 
		if($conf[cookie_use] == "1")
		{
			//한번 사용한 변수 이기 때문에 clear시킴.
			$Row[name] = "" ;
			$Row[email] = "" ;
			$Row[homepage] = "" ;

			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;
			$Row[name]  = $cw_name ;
			$Row[email] = $cw_email ;
			$Row[homepage]  = $cw_home ;
			/*
			$Row[name] = empty($cw_name)?$Row[name]:stripslashes($cw_name) ;
			$Row[email] = empty($cw_email)?$Row[email]:stripslashes($cw_email) ;
			$Row[homepage] = empty($cw_home)?$Row[homepage]:stripslashes($cw_home) ;
			*/
		}

		//수정을 누른 위치로 돌아가기 위해서 추가
		//2002/09/15 가급적이면 인터페이스로 빼자 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin 삽입
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin 삽입
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//각모드에 맞는 footer가 있으면 그 헤더를 이용
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
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//라이센스 무조건 출력 2002/09/23
		echo $new_license ;

		if( $outer_header_use == "1" ) 
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
		exit ;

	}
	///////////////////////////////
	// 글쓰기 폼 WRITE
	///////////////////////////////
	else 
	{
		//모드가 지정이 안되어 있는 경우 : 처음 글쓰기로 간주...
		$mode = "insert" ;

		$auth->run_mode(WRITE_MODE) ;
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
		
		/////// [스팸 처리]를 위한 변수 삽입  2004.09.25 by 체리토마토	
		if($_debug) echo "spam check : [$conf[spam_check_use]]";
		if (($conf[spam_check_use] =="1")&&($sess_name == ""))		
		{
			$wb_name = "wb_".$C_base["uniq_num"];	
			$spam_check_form = "[spam_check_use]<input type=hidden name='spam_check_use' value='1'><br><input type=text name=name class=wForm>[-->spam check]";
			//$spam_check_form ="<input type=hidden name='spam_check_use' value='1'><span style=display:none;><input type=text name=name class=wForm ></span>";						
		}
		else $wb_name = "name";
		//////////////////////////////////////////////////	
		
		$Row[notice_check] = notice_check($_data,$Row[status]) ;
		$Row[category_select] = category_select($_data,$Row[type]) ;
		$Row[is_main_writing] = "1" ;
		if( ! $auth->is_anonymous() )
		{
			$Row['alias']    = $auth->alias() ;
			$Row['email']    = $auth->email() ;
			$Row['homepage'] = $auth->homepage() ;
		}
	
		$Row[homepage] = "" ;
		$Row[link] = "" ;
		$Row[is_main_writing] = 1 ;
		$Row['br_use_checked'] = "checked" ;

		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row[name] = empty($cw_name)?$Row[name]:stripslashes($cw_name) ;
			$Row[email] = empty($cw_email)?$Row[email]:stripslashes($cw_email) ;
			$Row[homepage] = empty($cw_home)?$Row[homepage]:stripslashes($cw_home) ;
		}

		if( !$auth->is_anonymous() )
		{
			$Row[email] = $auth->email() ;
			$Row[member_info] = $auth->member_info() ;
			if($_debug) echo("write:".print_r($W_SES) ) ;
		}
			//2.
		$hide = make_comment($_data, $Row) ;		

		if( $conf[write_outer_header_use] == "1" || 
			!isset($conf[write_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// 외부 머리말 삽입
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];

		//edit, write_form이 들어가는 곳에 img_box를 넣어보자.
		include("../lib/imagebox.php") ;
		
		// imgbox변수복구 위해 추가 2004/10/06 by apollo
		// make_form_recover() ;
		/* 이부분에서의 변수 처리로 write 에서 쿠키 적용이 안됨
		$Row[comment] = $comment ;
		$Row[name] = $name ;
		$Row[subject] = $subject ;
		$Row[homepage] = $homepage ;
		$Row[to] = $to ;
		//카테고리 부분 복구 없음.
		*/



		///////////////////////////////////
		// 폼 삽입
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER 대소문자 구분없이 읽기 v 1.3.0 
		// 각모드에 맞는 header가 있으면 그 헤더를 이용
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin 삽입
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin 삽입
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		// imgbox변수복구 위해 추가 2004/10/06 by apollo
		$Row[html_use_checked] = ($html_use == "on")?"checked":"" ;
		$Row[mail_reply_checked] = ($mail_reply == "on")?"checked":"";
		$Row[br_use_checked] = "checked";  // br_use 기본 옵션을 위해 수정
		$Row[link] = $link ;


		//수정을 누른 위치로 돌아가기 위해서 추가
		//2002/09/15 가급적이면 인터페이스로 빼자 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin 삽입
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin 삽입
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//각모드에 맞는 footer가 있으면 그 헤더를 이용
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
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//라이센스 무조건 출력 2002/09/23
		echo $new_license ;

		if( $outer_header_use == "1" ) 
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
		exit ;
	}
	// 글쓰기 폼 WRITE 끝
	///////////////////////////////
?>
