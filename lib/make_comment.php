<?php
if(!defined("__wb_make_comment__")) define("__wb_make_comment__","1") ;
else return ;
	/**
	// 내용이 없는 경우 주석처리 해주는 기능을 수행.
	*/
	function make_comment($_data, $Row, $row_no = "NOT_USE", $wb_prg = "board")
	{
		$_debug = 0 ;

		global $W_SES ; //세션 변수
		global $C_base ;

		global $auth ;
		global $auth_param ;
		/*
		global $C_auth_perm ;
		global $C_auth_cat_perm ;
		global $C_auth_reply_perm ;
		*/
		global $URL ;
		//호환성 유지를 위해.
		global $bg_exist_start,      $bg_exist_end ;
		global $home_exist_start,    $home_exist_end ;
		global $email_exist_start,   $email_exist_end ;
		global $attach_exist_start,  $attach_exist_end ; 
		global $attach2_exist_start, $attach2_exist_end ;
		global $link_exist_start,    $link_exist_end ;
		global $reply_hide_start,    $reply_hide_end ;

		require_once("$C_base[dir]/lib/config.php") ;
		$read_config = "read_{$wb_prg}_config" ;
		$conf = $read_config($_data) ;
		$C_skin = $conf[skin] ;

		if(empty($Row[bgimg]))
		{
			$bg_exist_start = "<!--\n" ;
			$bg_exist_end = "\n-->" ;
		}
		else
		{
			$bg_exist_start = "" ;
			$bg_exist_end = "" ;
		}

		if(empty($Row[homepage]) || $Row[homepage] == "http://") 
		{
			$home_exist_start = "<!--\n" ;
			$home_exist_end   = "-->\n" ;
		}
		else
		{
			$home_exist_start = "" ;
			$home_exist_end   = "" ;
		}

		if(empty($Row[email]))
		{
			$email_exist_start = "<!--\n" ;
			$email_exist_end   = "-->\n" ;
		}
		else
		{
			$email_exist_start = "" ;
			$email_exist_end   = "" ;
		}

		if(empty($Row[InputFile_name]))
		{
			$attach_exist_start = "<!--\n" ;
			$attach_exist_end   = "-->\n" ;
		}
		else
		{
			$attach_exist_start = "" ;
			$attach_exist_end   = "" ;
		}

		if(empty($Row[InputFile2_name]))
		{
			$attach2_exist_start = "<!--\n" ;
			$attach2_exist_end   = "-->\n" ;
		}
		else
		{
			$attach2_exist_start = "" ;
			$attach2_exist_end   = "" ;
		}

		if(empty($Row[link]) || $Row[link] == "http://") 
		{
			$link_exist_start = "<!--\n" ;
			$link_exist_end = "-->\n" ;
		}		
		else
		{
			$link_exist_start = "" ;
			$link_exist_end = "" ;
		}

		if( $conf[small_reply_use] == "0" )
		{
			$reply_hide_start = "<!--\n" ;
			$reply_hide_end   = "-->\n" ;
		}
		else
		{
			$reply_hide_start = "" ;
			$reply_hide_end   = "" ;
		}

		if( $Row[nReply] > 0 )
		{
			$nReply_start = "" ;
			$nReply_end = "" ;
		}
		else
		{
			$nReply_start = "<!--\n" ;
			$nReply_end = "-->\n" ;
		}


		if( empty($conf[board_title]) )
		{
			$board_title_start = "<!--\n" ;
			$board_title_end = "-->\n" ;
		}
		else
		{
			$board_title_start = "" ;
			$board_title_end = "" ;
		}

		$type = $Row[type] ;
		if( empty($conf[category_name][$type]) )
		{
			$category_name_start = "<!--\n" ;
			$category_name_end = "-->\n" ;
		}
		else
		{
			$category_name_start = "" ;
			$category_name_end = "" ;
		}

		$hide = array(
				"bg_start"		=> $bg_exist_start,
				"bg_end"		=> $bg_exist_end,
				"bg"			=> $bg_exist_start,
				"/bg"			=> $bg_exist_end,

				"home_start"    => $home_exist_start,
				"home_end"      => $home_exist_end,
				"home"			=> $home_exist_start,
				"/home"			=> $home_exist_end,

				"email_start"   => $email_exist_start,
				"email_end"     => $email_exist_end,
				"email"			=> $email_exist_start,
				"/email"		=> $email_exist_end,

				"attach_start"  => $attach_exist_start,
				"attach_end"    => $attach_exist_end,
				"attach"		=> $attach_exist_start,
				"/attach"		=> $attach_exist_end,

				"attach2_start" => $attach2_exist_start,
				"attach2_end"   => $attach2_exist_end,
				"attach2"		=> $attach2_exist_start,
				"/attach2"		=> $attach2_exist_end,

				"link_start"    => $link_exist_start,
				"link_end"      => $link_exist_end,
				"link"			=> $link_exist_start,
				"/link"			=> $link_exist_end,

				"reply_start"   => $reply_hide_start,
				"reply_end"     => $reply_hide_end,
				"reply"			=> $reply_hide_start,
				"/reply"		=> $reply_hide_end,

				"board_title_start" => $board_title_start,
				"board_title_end"   => $board_title_end,
				"board_title"		=> $board_title_start,
				"/board_title"		=> $board_title_end,

				"nReply_start"  => $nReply_start,
				"nReply_end"    => $nReply_end,
				"nReply"		=> $nReply_start,
				"/nReply"		=> $nReply_end,

				"category_name_start"	=> $category_name_start,
				"category_name_end"		=> $category_name_end,
				"category_name"			=> $category_name_start,
				"/category_name"		=> $category_name_end
			) ;

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
		$hide['board_title'] = $hide[board_title_start]  ;
		$hide['/board_title'] = $hide[board_title_end] ; 

		/////////////////////////////
		//html_use
		/////////////////////////////
		if( empty($conf[html_use]) )
		{
			$hide['html_use'] = "<!--\n" ;
			$hide['/html_use'] = "-->\n" ;
		}
		else
		{
			$hide['html_use'] = "" ;
			$hide['/html_use'] = "" ;
		}
		
		

		/////////////////////////////
		//첫글과 답글 구분
		/////////////////////////////
		$hide['main_writing'] = "<style>/*\n" ;
		$hide['/main_writing'] = "*/</style>\n" ;
		$hide['reply_writing'] = "<style>/*\n" ;
		$hide['/reply_writing'] = "*/</style>\n" ;

		$hide['/*main_writing'] = "/*\n" ;
		$hide['main_writing*/'] = "*/\n" ;
		$hide['/*reply_writing'] = "/*\n" ;
		$hide['reply_writing*/'] = "*/\n" ;

		if( $Row[is_main_writing] == "1" )
		{
			if($_debug) echo("main comment <br>") ;
			//오래됐음.
			$hide[script_start] = "" ;
			$hide[script_start] = "" ;
			$hide['script'] = "" ;
			$hide['/script'] = "" ;

			//오래됐음.
			$hide[write_data_start] = "" ;
			$hide[write_data_end]   = "" ;
			$hide['write_data'] = "" ;
			$hide['/write_data']   = "" ;

			//2002/04/06 오래됐음.
			//글쓸경우 처음글의 내용과 제목을 감추기 위해서 사용
			//답글에서는 처음글의 제목과 내용이 나와야 하므로
			$hide[main_subject_start] = "<!--\n" ;
			$hide[main_subject_end] = "-->\n" ;

			//2002/04/06 오래됐음.
			//답글에서 제목 입력을 안하도록 하기 위해서 사용.
			$hide[subject_start] = "" ;
			$hide[subject_end] = "" ;

			//2002/04/06 write_data script_ subject_ main_subject_ 대치,글쓰기나 보기에서  처음글과 답글의 구분사용
			$hide['main_writing'] = "" ;
			$hide['/main_writing'] = "" ;

			$hide['/*main_writing'] = "" ;
			$hide['main_writing*/'] = "" ;

			$hide[category_select_start] = "" ;
			$hide[category_select_end] = "" ;
		}
		else
		{
			//if($_debug) echo("non main comment <br>") ;

			$hide[script_start] = "/*\n" ;
			$hide[script_end] = "*/\n" ;

			//답글의 경우, 오래됐음. 
			$hide[write_data_start] = "<!--\n" ;
			$hide[write_data_end]   = "-->\n" ;

			//2002/04/06 오래됐음.
			//글쓸경우 처음글의 내용과 제목을 감추기 위해서 사용
			//답글에서는 처음글의 제목과 내용이 나와야 하므로
			$hide[main_subject_start] = "" ;
			$hide[main_subject_end] = "" ;

			//2002/04/06 오래됐음.
			//답글에서 제목 입력을 안하도록 하기 위해서 사용.
			$hide[subject_start] = "<!--\n" ;
			$hide[subject_end] = "-->\n" ;

			//2002/04/06 script_ subject_ main_subject_ 대치,글쓰기나 보기에서  처음글과 답글의 구분사용
			$hide['reply_writing'] = "" ;
			$hide['/reply_writing'] = "" ;
			$hide['/*reply_writing'] = "" ;
			$hide['reply_writing*/'] = "" ;
		
			//2002/03/16 답글은 같은 카테고리에 무조건 속하므로 카테고리 선택을 안한다.
			$hide[category_select_start] = "<!--\n" ;
			$hide[category_select_end] = "-->\n" ;
		}

		//////////////////////////////////////
		// subject_color 
		// admin 에서 interface필요 2002/07/03
		//////////////////////////////////////
		$hide['subject_color'] = "<!--\n" ;
		$hide['/subject_color'] = "-->\n" ;
		if(!empty($Row[subject_color]))
		{
			$hide['subject_color'] = "" ;
			$hide['/subject_color'] = "" ;
		}	
		//////////////////////////////////////
		// category_select
		//////////////////////////////////////
		if( empty($Row[category_select]) )
        {
            $hide["category_select_start"] = "<!--" ;
            $hide["category_select_end"] = "-->" ;
        }
		$hide['category_select'] = $hide['category_select_start'] ;
		$hide['/category_select'] = $hide['category_select_end'] ;


		//////////////////////////////////////
		//글의 소유자를 판단
		//////////////////////////////////////
		$hide['admin_writing'] = "<noframes>\n" ;
		$hide['/admin_writing'] = "</noframes>\n" ;

		$hide['member_writing'] = "<noframes>\n" ;
		$hide['/member_writing'] = "</noframes>\n" ;

		$hide['anonymous_writing'] = "<noframes>\n" ;
		$hide['/anonymous_writing'] = "</noframes>\n" ;

		if($Row[uid] == __ANONYMOUS )
		{
			if($_debug) echo("anonymous writing.<br>") ;
			$hide['anonymous_writing'] = "" ;
			$hide['/anonymous_writing'] = "" ;
		}
		else if($Row[uid] == __ROOT || ($Row[user] == $conf[auth_user]) && !empty($Row[user]) ) 
		{
			if($_debug) echo("root writing<br>") ;
			$hide['admin_writing'] = "" ;
			$hide['/admin_writing'] = "" ;
		}
		else // is member writing?
		{
			if($_debug) echo("member writing<br>") ;
			$hide['member_writing'] = "" ;
			$hide['/member_writing'] = "" ;
		}


		//////////////////////////////////////
		// LOG-IN/OUT COMMENT
		//////////////////////////////////////
		$hide['login'] = "<!--\n" ;
		$hide['/login'] = "-->\n" ;
		$hide['logout'] = "<!--\n" ;
		$hide['/logout'] = "-->\n" ;
		if($auth->is_anonymous()) // not login
		{
			$hide['login'] = "" ;
			$hide['/login'] = "" ;
		}
		else
		{
			$Row[alias] = $auth->alias() ;
			$Row[user]  = $auth->user() ;
			$hide['logout'] = "" ;
			$hide['/logout'] = "" ;
		}


		//////////////////////////////////////
		// 기능설정에서 권한 판단 
		//////////////////////////////////////
		//권한은 rwx로 처리한다.
		//list는   R이고 conf[auth_perm]을 사용
		//cat은    R이고 conf[auth_cat_perm]을 사용
		//reply는  W이고 conf[auth_reply_perm]을 사용
		//delete는 X이고 conf[auth_perm]을 사용 
		//edit는   X이고 conf[auth_perm]을 사용 
		//write는 page에 적용되고 나머지는 하나의 글에 적용되므로 이곳에 위치

		$hide['cat_perm'] = "<script>/*\n" ;
		$hide['/cat_perm'] = "*/</script>\n" ;
		
		$hide['reply_perm'] = "<script>/*\n" ;
		$hide['/reply_perm'] = "*/</script>\n" ;

		$hide['exec_perm'] = "<script>/*\n" ;
		$hide['/exec_perm'] = "*/</script>\n" ;

		$hide['write_perm'] = "<script>/*\n" ;
		$hide['/write_perm'] = "*/</script>\n" ;


		//스킨에 맴버쉽에 따른 조건부 출력하기 위해서 2002.03.28, 2002/04/06
		// auth.php에서 만든 세션데이터를 이용한다.
		$hide['admin'] = "<noscript>\n" ;
		$hide['/admin'] = "</noscript>\n" ;

		$hide['member'] = "<noscript>\n" ;
		$hide['/member'] = "</noscript>\n" ;

		$hide['anonymous'] = "<noscript>\n" ;
		$hide['/anonymous'] = "</noscript>\n" ;

		//암호 입력폼만 가리기 위해서... 권한, 현재세션과 관계가 없이 작동해야 하므로
		$hide['password'] = "<!--\n" ;
		$hide['/password'] = "-->\n" ;
		$hide['/*password'] = "/*\n" ;
		$hide['password*/'] = "*/\n" ;

		if($auth->is_superuser())
		{
			$hide['admin'] = "" ;
			$hide['/admin'] = "" ;
			$Row[alias] = $auth->alias() ;

			$hide['cat_perm'] = "" ;
			$hide['/cat_perm'] = "" ;

			$hide['reply_perm'] = "" ;
			$hide['/reply_perm'] = "" ;

			$hide['write_perm'] = "" ;
			$hide['/write_perm'] = "" ;

			$hide['exec_perm'] = "" ;
			$hide['/exec_perm'] = "" ;
		}
		else if($auth->is_admin())
		{
			$hide['admin'] = "" ;
			$hide['/admin'] = "" ;
			$Row[alias] = $auth->alias() ;

			if(($conf[auth_cat_perm] & "4000")=="4000")
			{
				$hide['cat_perm'] = "" ;
				$hide['/cat_perm'] = "" ;
			}

			if(($conf[auth_reply_perm] & "2000")=="2000")
			{
				$hide['reply_perm'] = "" ;
				$hide['/reply_perm'] = "" ;
			}

			if(($conf[auth_perm] & "2000")=="2000")
			{
				$hide['write_perm'] = "" ;
				$hide['/write_perm'] = "" ;
			}
			//설정에 없으면 무조건 clear
			if(($conf[auth_perm] & "1000")=="1000")
			{
				$hide['exec_perm'] = "" ;
				$hide['/exec_perm'] = "" ;
			}
		}
		else if($auth->is_member())
		{
			$hide['member'] = "" ;
			$hide['/member'] = "" ;
			$Row[alias] = $auth->alias() ;

			if(($conf[auth_cat_perm] & "0040")=="0040")
			{
				$hide['cat_perm'] = "" ;
				$hide['/cat_perm'] = "" ;
			}

			if(($conf[auth_reply_perm] & "0020")=="0020")
			{
				$hide['reply_perm'] = "" ;
				$hide['/reply_perm'] = "" ;
			}

			if(($conf[auth_perm] & "0020")=="0020")
			{
				$hide['write_perm'] = "" ;
				$hide['/write_perm'] = "" ;
			}
	
			///////////////////////////////////	
			// EXEC의 경우 관리자가 아닌경우 
			// 본인의 글이 아닌경우 가려야 하므로
			///////////////////////////////////	
			if(($conf[auth_perm] & "0010")=="0010")
			{
				if($Row[uid] == __ANONYMOUS||$Row[uid] == $auth->uid())
				{
					$hide['exec_perm'] = "" ;
					$hide['/exec_perm'] = "" ;
				}
			}
		}
		else // is_anonymous()
		{
			$hide['anonymous'] = "" ;
			$hide['/anonymous'] = "" ;
			//암호를 입력해야 하므로
			$hide['password'] = "" ;
			$hide['/password'] = "" ;
			$hide['/*password'] = "" ;
			$hide['password*/'] = "" ;

			if(($conf[auth_cat_perm] & "0004")=="0004")
			{
				$hide['cat_perm'] = "" ;
				$hide['/cat_perm'] = "" ;
			}

			if( ($conf[auth_reply_perm] & "0002")=="0002" )
			{
				$hide['reply_perm'] = "" ;
				$hide['/reply_perm'] = "" ;
			}

			if(($conf[auth_perm] & "0002")=="0002")
			{
				$hide['write_perm'] = "" ;
				$hide['/write_perm'] = "" ;
			}

			///////////////////////////////////	
			// EXEC의 경우 관리자가 아닌경우 
			// 본인의 글이 아닌경우 가려야 하므로
			///////////////////////////////////	
			if(($conf[auth_perm] & "0001")=="0001")
			{
				if($Row[uid] == __ANONYMOUS)
				{
					$hide['exec_perm'] = "" ;
					$hide['/exec_perm'] = "" ;
				}
			}
		}


		//////////////////////////////////////
		if( $row_no != "NOT_USE" || $row_no == 0) 
		{
			$hide['even'] = "<style>/*\n" ;
			$hide['/even'] = "/*</style>\n" ;
			$hide['odd'] = "<style>/*\n" ;
			$hide['/odd'] = "*/</style>\n" ;
			if($row_no%2 == 0)
			{
				$hide['even'] = "" ;
				$hide['/even'] = "" ;
			}
			else
			{
				$hide['odd'] = "" ;
				$hide['/odd'] = "" ;
			}
		}

		$hide['avatar'] = "<!--\n";
		$hide['/avatar']  = "-->\n";
		if(!empty($Row[avatar]) && file_exists("$C_base[dir]/member/$Row[avatar]")) 
		{
			$hide['avatar'] = "";
			$hide['/avatar']  = "";
		}

		return $hide ;
	}
?>
