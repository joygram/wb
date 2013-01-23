<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	//include("$C_base[dir]/board/conf/config.php") ;
	$_debug = 0 ;  

	$conf[auth_perm] = "7000" ; //$C_auth_perm이름은 이곳 환경 설정에 영향을 준다. 변경 2002/03/24
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////

	if ($_debug) echo("conf_name[$conf_name]<br>") ;
	// 1ndr4<1ndr4@hanmail.net> & mAze<ninanuo@naver.com>보안권고로 필터링  	
	// 2001.11.17, 2002/03/15
	$conf_name = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|admin\.php|[[:space:]])", "", $conf_name) ;
	if (empty($conf_name))
	{
		echo("<script>window.alert('"._L_BOARDCONF_NEED."'); history.go(-1);</script>") ;
		exit ;
	}
	if( !@file_exists("$C_base[dir]/board/conf/$conf_name") )
	{
		if ($conf_name == "__global.conf.php")
		{
			touch("$C_base[dir]/board/conf/__global.conf.php", 0757) ;
		}
		else
		{
			echo("<script>window.alert('"._L_CONFIGFILE." [$conf_name]"._L_NOTEXIST."'); history.go(-1);</script>") ;
			exit ;
		}
	}

	include("$C_base[dir]/board/conf/$conf_name") ; //환경파일 읽어오기
	$conf_array = explode(".", $conf_name) ;
	if( $conf_name == "__global.conf.php" )
	{
		$Row[conf] = _L_BOARD._L_GLOBAL_FUNCTION_SETUP ;
	}
	else
	{
		$Row[conf] = "<font class='wTitle'>$conf_array[0] "._L_BOARD." "._L_FUNCTION_SETUP."</font>" ;
	}


	//////////////////////////////////////////
	$Row[title] = _L_BOARD_TOTAL_FUNCTION ;
	echo("<script>
		function toggle_func(form)
		{
			if( form.C_global_use.checked )
			{
				//disable all
				for( var i = 0; i < form.elements.length; i++)
				{
					//if( form.elements[i].type != 'hidden' )
						form.elements[i].disabled = true ;
				}
				form.C_global_use.disabled = false ;
			}
			else
			{
				//enable all
				for( var i = 0; i < form.elements.length; i++)
				{
					form.elements[i].disabled = false ;
				}
				//installed plugin select all 
				for( i = 0; i < form.elements['C_plugin_install[]'].length; i++)
				{
				}
			}
		}
		//저장할때 disable된것은 넘어가지 않으므로 사용
		//config_menu.php에서 호출함.
		function enable_func(form)
		{
			//enable all
			for( var i = 0; i < form.elements.length; i++)
			{
				form.elements[i].disabled = false ;
			}
			//
			for(i =0; i < form.elements['C_plugin_install[]'].options.length; i++)
			{
				form.elements['C_plugin_install[]'].options[i].selected = true ;
			}
		}
	</script>") ;


	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_use == "1" )?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_use $checked onClick='toggle_func(this.form)'>\n" ;
	}

	//////////////////////////////////////////
		// 주의: 위에 전역설정부분에 헤더에 들어감 유의 2002/07/14 
		// 초기값 지정 : 게시판 별로 바꾸려면 config/게시판명.php를 수정 
	include("./html/config_header.html") ;
	//////////////////////////////////////////
	$Row[title] = _L_BASIC_SETUP ;
	echo("<script>
		function toggle_general(form)
		{
			if( form.C_global_general_use.checked )
			{
				//disable all
				form.C_board_title.disabled = true ;
				form.C_skin.disabled        = true ;
				form.C_table_size.disabled  = true ;
				form.C_table_align.disabled = true ;
			}
			else
			{
				//enable all
				form.C_board_title.disabled = false ;
				form.C_skin.disabled        = false ;
				form.C_table_size.disabled  = false ;
				form.C_table_align.disabled = false ;
			}
		}
	</script>") ;


	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_general_use == "1" )?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_general_use $checked onClick='toggle_general(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$Row[title] = _L_BOARD_TITLE ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 maxlength=1000 name='C_board_title' value='$C_board_title' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_BOARD_SKIN ;
	$Row[func] = "" ;
	$Row[func] .= "<select name=C_skin class='wForm'>\n" ;

		// 스킨 디렉토리 이름을 읽어서 출력해주기
	$flist = new file_list("$C_base[dir]/board/skin", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;
		if ($_debug) echo("file_name[$file_name]<br>") ;
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name))
		{
			if ($_debug) echo("SKIP file_name[$file_name]<br>") ;
			continue ;
		}

		if($C_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row[func] .= "</select>\n" ;
	include("./html/config_list.html") ;


	if(!isset($C_table_size))
	{
		$C_table_size = 550 ;
	}
	$Row[title] = _L_BOARD_TABLE_SIZE ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=4 maxlength=5 name='C_table_size' value='$C_table_size' class='wForm'>("._L_PIXEL_OR_PERCENT.")\n" ;
	include("./html/config_list.html") ;
	
	if(!isset($C_table_align))
	{
		$C_table_align = "center" ;
	}
	if($_debug) echo("C_table_align[$C_table_align]<br>") ;
	$table_align_selected[$C_table_align] = "selected" ;

	$Row[title] = _L_BOARD_TABLE_ALIGN ;
	$Row[func] = "" ;
	$Row[func] .= "<select name='C_table_align' class='wForm'>\n" ;
	$Row[func] .= "<option value='center' {$table_align_selected[center]}>"._L_CENTER."</option>\n" ;
	$Row[func] .= "<option value='left'     {$table_align_selected[left]}>"._L_LEFT."</option>\n" ;
	$Row[func] .= "<option value='right'   {$table_align_selected[right]}>"._L_RIGHT."</option>\n" ;
	$Row[func] .= "</select>\n" ;
	$Row[func] .= _L_BOARD_TABLE_ALIGN_NOTICE ;
	include("./html/config_list.html") ;

	if(!isset($C_license_align))
	{
		$C_license_align = "center" ;
	}
	if($_debug) echo("C_table_align[$C_table_align]<br>") ;
	$license_align_selected[$C_license_align] = "selected" ;

	$Row[title] = _L_BOARD_LICENSE_ALIGN ;
	$Row[func] = "" ;
	$Row[func] .= "<select name='C_license_align' class='wForm'>\n" ;
	$Row[func] .= "<option value='center' {$license_align_selected[center]}>"._L_CENTER."</option>\n" ;
	$Row[func] .= "<option value='left'     {$license_align_selected[left]}>"._L_LEFT."</option>\n" ;
	$Row[func] .= "<option value='right'   {$license_align_selected[right]}>"._L_RIGHT."</option>\n" ;
	$Row[func] .= "</select>\n" ;
	include("./html/config_list.html") ;

	//설정에서 스팸체크 여부 추가
	$Row[title] = _L_SPAM_CHECK ;
	$Row[func] = "" ;
	$checked = "" ;
	if( $C_spam_check_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] .= _L_USE." <input type=checkbox name=C_spam_check_use $checked>\n" ;
	include("./html/config_list.html") ;

	/*
	$Row[title] = _L_THEME ;
	$Row[func] = "" ;
	$checked = "" ;
	if( $C_plugin_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] .= "적용 <input type=checkbox name=C_plugin_use $checked>\n" ;
	include("./html/config_list.html") ;
	*/


	//////////////////////////////////////////
	$Row[title] = _L_PERMISSION_SETUP ;
	echo("<script>
		function toggle_perm(form)
		{
			if( form.C_global_perm_use.checked )
			{
				//disable perm 
				form.elements['C_perm[admin_read]'].disabled  = true ;
				form.elements['C_perm[admin_cat]'].disabled   = true ;
				form.elements['C_perm[admin_write]'].disabled = true ;
				form.elements['C_perm[admin_reply]'].disabled = true ;
				form.elements['C_perm[admin_exec]'].disabled  = true ;
				/*
				form.elements['C_perm[group_read]'].disabled  = true ;
				form.elements['C_perm[group_cat]'].disabled   = true ;
				form.elements['C_perm[group_write]'].disabled = true ;
				form.elements['C_perm[group_reply]'].disabled = true ;
				form.elements['C_perm[group_exec]'].disabled  = true ;
				*/
				form.elements['C_perm[member_read]'].disabled  = true ;
				form.elements['C_perm[member_cat]'].disabled   = true ;
				form.elements['C_perm[member_write]'].disabled = true ;
				form.elements['C_perm[member_reply]'].disabled = true ;
				form.elements['C_perm[member_exec]'].disabled  = true ;

				form.elements['C_perm[anonymous_read]'].disabled  = true ;
				form.elements['C_perm[anonymous_cat]'].disabled   = true ;
				form.elements['C_perm[anonymous_write]'].disabled = true ;
				form.elements['C_perm[anonymous_reply]'].disabled = true ;
				form.elements['C_perm[anonymous_exec]'].disabled  = true ;
			}
			else
			{
				//enable perm 
				form.elements['C_perm[admin_read]'].disabled  = false ;
				form.elements['C_perm[admin_cat]'].disabled   = false ;
				form.elements['C_perm[admin_write]'].disabled = false ;
				form.elements['C_perm[admin_reply]'].disabled = false ;
				form.elements['C_perm[admin_exec]'].disabled  = false ;
				/*
				form.elements['C_perm[group_read]'].disabled  = false ;
				form.elements['C_perm[group_cat]'].disabled   = false ;
				form.elements['C_perm[group_write]'].disabled = false ;
				form.elements['C_perm[group_reply]'].disabled = false ;
				form.elements['C_perm[group_exec]'].disabled  = false ;
				*/
				form.elements['C_perm[member_read]'].disabled  = false ;
				form.elements['C_perm[member_cat]'].disabled   = false ;
				form.elements['C_perm[member_write]'].disabled = false ;
				form.elements['C_perm[member_reply]'].disabled = false ;
				form.elements['C_perm[member_exec]'].disabled  = false ;

				form.elements['C_perm[anonymous_read]'].disabled  = false ;
				form.elements['C_perm[anonymous_cat]'].disabled   = false ;
				form.elements['C_perm[anonymous_write]'].disabled = false ;
				form.elements['C_perm[anonymous_reply]'].disabled = false ;
				form.elements['C_perm[anonymous_exec]'].disabled  = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_perm_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_perm_use $checked onClick='toggle_perm(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$Row[title] = _L_ADMIN_PERM ;
	$Row[func] = "" ;

		//2002/03/23 2.1.x 이전 버젼인 경우 기본 권한 컨버젼.
	if( !isset($C_auth_cat_perm) )
	{
		if( $C_write_admin_only == 1 )
		{
			$C_admin_perm 		= "7" ;
			$C_admin_reply_perm	= "7" ;
			$C_admin_cat_perm	= "7" ;

			$C_group_perm 		= "5" ;
			$C_group_reply_perm	= "5" ;
			$C_group_cat_perm	= "5" ;

			$C_member_perm 		 = "5" ;
			$C_member_reply_perm = "5" ;
			$C_member_cat_perm	 = "5" ;

			$C_anonymous_perm 		= "5" ;
			$C_anonymous_reply_perm	= "5" ;
			$C_anonymous_cat_perm	= "5" ;
		}
		else
		{
			$C_admin_perm 		= "7" ;
			$C_admin_reply_perm	= "7" ;
			$C_admin_cat_perm	= "7" ;

			$C_group_perm 		= "6" ;
			$C_group_reply_perm	= "6" ;
			$C_group_cat_perm	= "6" ;

			$C_member_perm 		 = "6" ;
			$C_member_reply_perm = "6" ;
			$C_member_cat_perm	 = "6" ;

			$C_anonymous_perm 		= "7" ;
			$C_anonymous_reply_perm	= "7" ;
			$C_anonymous_cat_perm	= "7" ;
		}
	}

	$perm_check[admin_read]  = ($C_admin_perm & 4)?"checked":"" ;	
	$perm_check[admin_write] = ($C_admin_perm & 2)?"checked":"" ;	
	$perm_check[admin_exec]  = ($C_admin_perm & 1)?"checked":"" ;	
	$perm_check[admin_reply] = ($C_admin_reply_perm & 2)?"checked":"" ;	
	$perm_check[admin_cat]   = ($C_admin_cat_perm & 4)?"checked":"" ;	


	$Row[func] .= _L_LIST."<input type=checkbox name=C_perm[admin_read] $perm_check[admin_read]>\n" ;
	$Row[func] .= _L_CAT."<input type=checkbox name=C_perm[admin_cat] $perm_check[admin_cat]>\n" ;
	$Row[func] .= _L_WRITE."<input type=checkbox name=C_perm[admin_write] $perm_check[admin_write]>\n" ;
	$Row[func] .= _L_REPLY."<input type=checkbox name=C_perm[admin_reply] $perm_check[admin_reply]>\n" ;
	$Row[func] .= _L_EXEC."<input type=checkbox name=C_perm[admin_exec] $perm_check[admin_exec]>\n" ;
	include("./html/config_list.html") ;

/**
	$Row[title] = _L_GROUP_PERM ;
	$Row[func] = "" ;

	$perm_check[group_read]  = ($C_group_perm & 4)?"checked":"" ;	
	$perm_check[group_write] = ($C_group_perm & 2)?"checked":"" ;	
	$perm_check[group_exec]  = ($C_group_perm & 1)?"checked":"" ;	
	$perm_check[group_reply] = ($C_group_reply_perm & 2)?"checked":"" ;	
	$perm_check[group_cat]   = ($C_group_cat_perm & 4)?"checked":"" ;	


	$Row[func] .= _L_LIST."<input type=checkbox name=C_perm[group_read]  $perm_check[group_read] >\n" ;
	$Row[func] .= _L_CAT."<input type=checkbox name=C_perm[group_cat] $perm_check[group_cat]>\n" ;
	$Row[func] .= _L_WRITE."<input type=checkbox name=C_perm[group_write] $perm_check[group_write]>\n" ;
	$Row[func] .= _L_REPLY."<input type=checkbox name=C_perm[group_reply] $perm_check[group_reply]>\n" ;
	$Row[func] .= _L_EXEC."<input type=checkbox name=C_perm[group_exec]  $perm_check[group_exec]>\n" ;
	include("./html/config_list.html") ;
*/

	$Row[title] = _L_MEMBER_PERM ;
	$Row[func] = "" ;

	$perm_check[member_read]  = ($C_member_perm & 4)?"checked":"" ;	
	$perm_check[member_write] = ($C_member_perm & 2)?"checked":"" ;	
	$perm_check[member_exec]  = ($C_member_perm & 1)?"checked":"" ;	
	$perm_check[member_reply] = ($C_member_reply_perm & 2)?"checked":"" ;	
	$perm_check[member_cat]   = ($C_member_cat_perm & 4)?"checked":"" ;	


	$Row[func] .= _L_LIST."<input type=checkbox name=C_perm[member_read]  $perm_check[member_read]>\n" ;
	$Row[func] .= _L_CAT."<input type=checkbox name=C_perm[member_cat] $perm_check[member_cat]>\n" ;
	$Row[func] .= _L_WRITE."<input type=checkbox name=C_perm[member_write] $perm_check[member_write]>\n" ;
	$Row[func] .= _L_REPLY."<input type=checkbox name=C_perm[member_reply] $perm_check[member_reply]>\n" ;
	$Row[func] .= _L_EXEC."<input type=checkbox name=C_perm[member_exec]  $perm_check[member_exec]>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_ANONYMOUS_PERM ;
	$Row[func] = "" ;

	$perm_check[anonymous_read]  = ($C_anonymous_perm & 4)?"checked":"" ;	
	$perm_check[anonymous_write] = ($C_anonymous_perm & 2)?"checked":"" ;	
	$perm_check[anonymous_exec]  = ($C_anonymous_perm & 1)?"checked":"" ;	
	$perm_check[anonymous_reply] = ($C_anonymous_reply_perm & 2)?"checked":"" ;	
	$perm_check[anonymous_cat]   = ($C_anonymous_cat_perm & 4)?"checked":"" ;	

	$Row[func] .= _L_LIST."<input type=checkbox name=C_perm[anonymous_read]  $perm_check[anonymous_read]>\n" ;
	$Row[func] .= _L_CAT."<input type=checkbox name=C_perm[anonymous_cat] $perm_check[anonymous_cat]>\n" ;
	$Row[func] .= _L_WRITE."<input type=checkbox name=C_perm[anonymous_write] $perm_check[anonymous_write]>\n" ;
	$Row[func] .= _L_REPLY."<input type=checkbox name=C_perm[anonymous_reply] $perm_check[anonymous_reply]>\n" ;
	$Row[func] .= _L_EXEC."<input type=checkbox name=C_perm[anonymous_exec]  $perm_check[anonymous_exec]>\n" ;
	include("./html/config_list.html") ;


	//////////////////////////////////////////
	$Row[title] = _L_WRITE_SETUP ;
	echo("<script>
		function toggle_write(form)
		{
			if( form.C_global_write_use.checked )
			{
				//disable perm 
				form.C_small_reply_use.disabled   = true ;
				form.C_cookie_use.disabled        = true ;
				form.C_subject_html_use.disabled  = true ;
				form.C_name_html_use.disabled     = true ;
				form.C_html_use.disabled          = true ;
			}
			else
			{
				//enable perm 
				form.C_small_reply_use.disabled   = false ;
				form.C_cookie_use.disabled        = false ;
				form.C_subject_html_use.disabled  = false ;
				form.C_name_html_use.disabled     = false ;
				form.C_html_use.disabled          = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_write_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_write_use $checked onClick='toggle_write(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	
	$Row[title] = _L_REPLY ;
	$Row[func] = "" ;
	$checked = "" ;
	if( $C_small_reply_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] .= _L_USE." <input type=checkbox name=C_small_reply_use $checked>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_COOKIE ;
	$Row[func] = "" ;
	$checked = "" ;
	if( $C_cookie_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] .= _L_USE." <input type=checkbox name=C_cookie_use $checked>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_HTML_USE ;
	unset($checked) ; 
	if( $C_subject_html_use == 1 ) 
	{
		$checked[subject] = "checked" ;
	}
	if( $C_html_use == 1 ) 
	{
		$checked[content] = "checked" ;
	}
	if( $C_name_html_use == 1 )
	{
		$checked[name] = "checked" ;
	}
	$Row[func] = "" ;
	$Row[func] .= _L_TITLE."<input type=checkbox name=C_subject_html_use $checked[subject] >\n" ;
	$Row[func] .= _L_NAME."<input type=checkbox name=C_name_html_use $checked[name] >\n" ;
	$Row[func] .= _L_CONTENT."<input type=checkbox name=C_html_use $checked[content] >\n" ;
	include("./html/config_list.html") ;

	//////////////////////////////////////////
	$Row[title] = _L_LIST_SETUP ;
	echo("<script>
		function toggle_list(form)
		{
			if( form.C_global_list_use.checked )
			{
				//disable perm 
				form.C_nCol.disabled           = true ;
				form.C_nRow.disabled           = true ;
				form.C_subject_max.disabled    = true ;
				form.C_comment_max.disabled    = true ;
				form.C_img_size_limit.disabled = true ;
				form.C_url2link_use.disabled   = true ;
				form.C_sort_index.disabled     = true ;
				form.C_sort_order.disabled     = true ;
			}
			else
			{
				//enable perm 
				form.C_nCol.disabled           = false ;
				form.C_nRow.disabled           = false ;
				form.C_subject_max.disabled    = false ;
				form.C_comment_max.disabled    = false ;
				form.C_img_size_limit.disabled = false ;
				form.C_url2link_use.disabled   = false ;
				form.C_sort_index.disabled     = false ;
				form.C_sort_order.disabled     = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_list_use == "1" )?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_list_use $checked onClick='toggle_list(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$C_nCol = empty($C_nCol)?1:$C_nCol ;
	$C_nRow = empty($C_nRow)?5:$C_nRow ;
	$Row[title] = _L_ARTICLE_NUM ;
	$Row[func] = "" ;
	$Row[func] .= _L_LIST_WIDTH."<input type=text size=2 name='C_nCol' value='$C_nCol' class='wForm'>\n" ; 
	$Row[func] .= "* "._L_LIST_HEIGHT."<input type=text size=2 name='C_nRow' value='$C_nRow' class='wForm'>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_TITLE_LENGTH_LIMIT ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_subject_max size=2 value='$C_subject_max' class='wForm'> "._L_UPTO ;
	include("./html/config_list.html") ;

	$Row[title] = _L_CONTENT_LENGTH_LIMIT ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_comment_max size=2 value='$C_comment_max' class='wForm'> "._L_UPTO ;
	include("./html/config_list.html") ;

	$Row[title] = _L_IMAGE_WIDTH_LIMIT ; 
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=3 name='C_img_size_limit' value='$C_img_size_limit' class='wForm'> "._L_PIXEL."\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_URL_AUTOLINK ;
	$Row[func] = "" ;
	$checked = "" ;
	if( $C_url2link_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] .= _L_USE." <input type=checkbox name=C_url2link_use $checked>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_SORTTYPE ;
	$Row[func] = "" ;
	$selected[$C_sort_index] = "selected" ;
	$Row[func] .= "<select name='C_sort_index' class='wForm'>\n"; 
	$Row[func] .= "<option value='0' $selected[0] class='wMat'>"._L_DEFAULT."</option>\n" ;
	$Row[func] .= "<option value='10' $selected[10] class='wMat'>"._L_UPDATETIME."</option>\n" ;
	$Row[func] .= "<option value='3' $selected[3] class='wMat'>"._L_VISIT."</option>\n" ; 
	$Row[func] .= "<option value='4' $selected[4] class='wMat'>"._L_DOWNLOAD_CNT1."</option>\n" ;
	$Row[func] .= "<option value='7' $selected[7] class='wMat'>"._L_DOWNLOAD_CNT2."</option>\n" ;
//	$Row[func] .= "<option value='11' $selected[11] class='wMat'>"._L_READ_CNT."</option>\n" ; 
	$Row[func] .= "</select>\n" ;

	$selected[$C_sort_order] = "selected" ;
	$Row[func] .= "<select name='C_sort_order' class='wForm'>\n"; 
	$Row[func] .= "<option value='desc' $selected[desc] class='wMat'>"._L_DESCENDING."</option>\n" ;
	$Row[func] .= "<option value='asc' $selected[asc] class='wMat'>"._L_ASCENDING."</option>\n" ;
	$Row[func] .= "</select>\n" ;
	
	include("./html/config_list.html") ;

	//////////////////////////////////////////
	$Row[title] = _L_NEWS_SETUP ;
	echo("<script>
		function toggle_news(form)
		{
			if( form.C_global_news_use.checked )
			{
				//disable perm 
				form.C_news_nCol.disabled           = true ;
				form.C_news_nRow.disabled           = true ;
				form.C_news_subject_max.disabled    = true ;
				form.C_news_char_max.disabled       = true ;
				form.C_news_skin.disabled           = true ;
				form.C_news_use.disabled            = true ;
			}
			else
			{
				//enable perm 
				form.C_news_nCol.disabled           = false ;
				form.C_news_nRow.disabled           = false ;
				form.C_news_subject_max.disabled    = false ;
				form.C_news_char_max.disabled       = false ;
				form.C_news_skin.disabled           = false ;
				form.C_news_use.disabled            = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_news_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_news_use $checked onClick='toggle_news(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	if(!empty($C_nNews))
	{
		if($_debug) echo("not empty C_nNews[$C_nNews]<br>") ;
		$C_news_nCol = 1 ;
		$C_news_nRow = $C_nNews ;
	}
	if($_debug) echo("C_news_nCol[$C_news_nCol] C_news_nRow[$C_news_nRow]<br>") ;

	$checked = "" ;
	//스킨에 news.html파일이 있는 경우에만 사용하도록 한것을 250에서는 관리자 설정으로 돌려서 사용할 수 있도록 유도
	if (isset($C_news_use))
	{
		if ($C_news_use) 
			$checked = "checked" ;
	}
	else
	{
		//기존대로 사용여부 검사
		if (file_exists("$C_base[dir]/board/skin/$C_skin/news.html"))
		{
			$checked = "checked" ;
		}
	}
	$Row[title] = _L_USE ;
	$Row[func] = "<input type=checkbox name=C_news_use $checked>" ;
	include("./html/config_list.html") ;

	$C_news_nCol = empty($C_news_nCol)?1:$C_news_nCol ;
	$C_news_nRow = empty($C_news_nRow)?3:$C_news_nRow ;
	$Row[title] = _L_NEWS_NUM ;
	$Row[func] = "" ;
	$Row[func] .= _L_NEWS_WIDTH."<input type=text size=2 name='C_news_nCol' value='$C_news_nCol' class='wForm'>\n" ; 
	$Row[func] .= "* "._L_NEWS_HEIGHT."<input type=text size=2 name='C_news_nRow' value='$C_news_nRow' class='wForm'>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_TITLE_LENGTH_LIMIT ; 
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_news_subject_max size=2 value='$C_news_subject_max' class='wForm'> "._L_UPTO ;
	include("./html/config_list.html") ;

	$Row[title] = _L_CONTENT_LENGTH_LIMIT ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_news_char_max size=2 value='$C_news_char_max' class='wForm'> "._L_UPTO ; 
	include("./html/config_list.html") ;

		// 스킨 디렉토리 이름을 읽어서 출력해주기
	$Row[title] = _L_PARTSKIN_SETUP ;
	$Row[func] = "" ;
	$Row[func] .= "<select name=C_news_skin class='wForm'>" ;
	$Row[func] .= "<option value=''>"._L_CURRENT_SKINS ;
	$flist = new file_list("$C_base[dir]/board/skin/__global/news", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;

		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name))
		{
			continue ;
		}

		if($C_news_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row[func] .= "</select>"._L_USE."\n" ;

	include("./html/config_list.html") ;

	//////////////////////////////////////////
	$Row[title] = _L_PAGEBAR_SETUP ;
	echo("<script>
		function toggle_pagebar(form)
		{
			if( form.C_global_pagebar_use.checked )
			{
				//disable perm 
				form.MAX_PAGE_SHOW.disabled          = true ;
				//form.C_page_bar_align.disabled       = true ;
				form.C_pagebar_skin.disabled         = true ;
			}
			else
			{
				//enable perm 
				form.MAX_PAGE_SHOW.disabled          = false ;
				//form.C_page_bar_align.disabled       = false ;
				form.C_pagebar_skin.disabled         = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_pagebar_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_pagebar_use $checked onClick='toggle_pagebar(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$Row[title] = _L_PAGEBAR_SHOWNUM ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=MAX_PAGE_SHOW value=$MAX_PAGE_SHOW size=2 class='wForm'>" ;
	include("./html/config_list.html") ;


	/*
	$Row[title] = _L_PAGEBAR_ALIGN ;
	$Row[func] = "" ;
	$selected[$C_page_bar_align] = "selected" ;

	$Row[func] .= "<select name='C_page_bar_align' class='wForm'>\n"; 
	$Row[func] .= "<option value='center' ".$selected['center']." class='wMat'>가운데</option>\n" ; 
	$Row[func] .= "<option value='left'   ".$selected['left']." class='wMat'>왼쪽</option>\n" ; 
	$Row[func] .= "<option value='right'  ".$selected['right']." class='wMat'>오른쪽</option>\n" ;
	$Row[func] .= "</select>\n" ;
	include("./html/config_list.html") ;
	*/

	$Row[title] = _L_PARTSKIN_SETUP ;
	$Row[func] = "" ;
	$Row[func] .= "<select name=C_pagebar_skin class='wForm'>" ;
	$Row[func] .= "<option value=''>"._L_CURRENT_SKINS ;

	if(@!file_exists("$C_base[dir]/board/skin/__global/pagebar") )
	{
		if(!@mkdir("$C_base[dir]/board/skin/__global/pagebar", 0777))
		{
			//error message and abort needed.
		}
	}
	$flist = new file_list("$C_base[dir]/board/skin/__global/pagebar", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;

		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name))
		{
			continue ;
		}

		if($C_pagebar_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row[func] .= "</select>"._L_USE."\n" ;
	include("./html/config_list.html") ;

	//////////////////////////////////////////
	$Row[title] = _L_FILTER ;
	echo("<script>
		function toggle_filter(form)
		{
			if( form.C_global_filter_use.checked )
			{
				//disable form 
				form.C_block_tag.disabled      = true ;
				form.C_filter_name.disabled    = true ;
				form.C_filter_subject.disabled    = true ;
				form.C_filter_ip_use.disabled  = true ;
				form.C_filter_ip.disabled      = true ;
				form.C_filter_txt_use.disabled = true ;
				form.C_filter_txt.disabled     = true ;
			}
			else
			{
				//enable form 
				form.C_block_tag.disabled      = false ;
				form.C_filter_name.disabled    = false ;
				form.C_filter_subject.disabled    = false ;
				form.C_filter_ip_use.disabled  = false ;
				form.C_filter_ip.disabled      = false ;
				form.C_filter_txt_use.disabled = false ;
				form.C_filter_txt.disabled     = false ;
			}
		}
	</script>") ;


	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_filter_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_filter_use $checked onClick='toggle_filter(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$Row[title] = _L_BLOCK_TAGS ; 
	$Row[func] = "" ;
	if( empty($C_block_tag) )
	{
		$C_block_tag = "meta,xmp," ;
	}
	$Row[func] .= "<input type=text size=38 name='C_block_tag' value='$C_block_tag' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_BLOCK_NAMES ;
	if( !isset($C_filter_name) )
	{
		$C_filter_name = _L_BOARD_DEFAULT_RESTRICTED_NAME ;
	}
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_filter_name' value='$C_filter_name' class='wForm'>\n" ;
	include("./html/config_list.html") ;
	

	$Row[title] = _L_BLOCK_SUBJECT ;
	if( !isset($C_filter_subject) )
	{
		$C_filter_subject = _L_BOARD_DEFAULT_RESTRICTED_SUBJECT ; 
	}
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_filter_subject' value='$C_filter_subject' class='wForm'>\n" ;
	include("./html/config_list.html") ;


		//2002/06/26
	$Row[title] = _L_BLOCK_IP ;
	$Row[func] = "" ;

	if( !isset($C_filter_ip_use) )
	{
		$C_filter_ip_use = 1 ;
	}

	$checked = "" ;
	if( $C_filter_ip_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] = "" ;
	$Row[func] .= _L_USE." <input type=checkbox name=C_filter_ip_use $checked>\n" ;
	
	$Row[func] .= "<textarea cols=38 rows=3 name='C_filter_ip' class='wForm'>$C_filter_ip</textarea>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_BLOCK_CONTENTS ; 
	if( empty($C_filter_txt) )
	{
		$C_filter_txt = _L_BOARD_DEFAULT_RESTRICTED_TEXT ;  
	}

	if( !isset($C_filter_txt_use) )
	{
		$C_filter_txt_use = 1 ;
	}

	$checked = "" ;
	if( $C_filter_txt_use == 1 ) 
	{
		$checked = "checked" ;
	}
	$Row[func] = "" ;
	$Row[func] .= _L_USE." <input type=checkbox name=C_filter_txt_use $checked>\n" ;
	$Row[func] .= "<textarea cols=38 rows=4 name='C_filter_txt' class='wForm'>$C_filter_txt</textarea>\n" ;
	include("./html/config_list.html") ;


	//////////////////////////////////////////
	$Row[title] = _L_GRADATION_COLOR_SETUP ;
	echo("<script>
		function toggle_grad(form)
		{
			if( form.C_global_grad_use.checked )
			{
				//disable perm 
				form.C_grad_start_color.disabled      = true ;
				form.C_grad_end_color.disabled        = true ;
			}
			else
			{
				//enable perm 
				form.C_grad_start_color.disabled      = false ;
				form.C_grad_end_color.disabled        = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_grad_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_grad_use $checked onClick='toggle_grad(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$Row[title] = _L_GRADATION_START_COLOR ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_grad_start_color size=12 value='$C_grad_start_color' class='wForm'>" ;
	$Row[func] .= "[<font color='#$C_grad_start_color'>▒▒▒</font>]" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_GRADATION_END_COLOR ; 
	$Row[func] = "" ;
	$Row[func] .= "<input type=text name=C_grad_end_color size=12 value='$C_grad_end_color' class='wForm'>" ;
	$Row[func] .= "[<font color='#$C_grad_end_color'>▒▒▒</font>]" ;
	include("./html/config_list.html") ;


	//////////////////////////////////////////
	$Row[title] = _L_UPLOAD_SETUP ;
	echo("<script>
		function toggle_upload(form)
		{
			if( form.C_global_upload_use.checked )
			{
				//disable perm 
				form.C_attach1_ext.disabled      = true ;
				form.C_attach2_ext.disabled      = true ;
			}
			else
			{
				//enable perm 
				form.C_attach1_ext.disabled      = false ;
				form.C_attach2_ext.disabled      = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_upload_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_upload_use $checked onClick='toggle_upload(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$Row[title] = _L_UPLOAD1_EXT ;
	$Row[func] = "" ;
	$checked = "" ;

	if( empty($C_attach1_ext) )
	{
		$C_attach1_ext = "gif,jpg,png,bmp,psd" ;
	}
	$Row[func] .= "<input type=text size=38 name=C_attach1_ext value='$C_attach1_ext' class='wForm' >\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_UPLOAD2_EXT ;
	$Row[func] = "" ;
	$checked = "" ;
	if( empty($C_attach2_ext) )
  	{
		$C_attach2_ext = "zip,rar,lzh,gz,tgz" ;
	}
	$Row[func] .= "<input type=text size=38 name=C_attach2_ext value='$C_attach2_ext' class='wForm'>\n" ;
	include("./html/config_list.html") ;

	
	//////////////////////////////////////////
	$Row[title] = _L_LISTFRAME_SETUP ;
	echo("<script>
		function toggle_frame(form)
		{
			if( form.C_global_frame_use.checked )
			{
				//disable perm 
				form.C_box.disabled            = true ;
				form.C_BOX_START.disabled      = true ;
				form.C_BOX_END.disabled        = true ;
				form.C_BOX_BR.disabled         = true ;
				form.C_BOX_DATA_START.disabled = true ;
				form.C_BOX_DATA_END.disabled   = true ;
			}
			else
			{
				//enable perm 
				form.C_box.disabled            = false ;
				form.C_BOX_START.disabled      = false ;
				form.C_BOX_END.disabled        = false ;
				form.C_BOX_BR.disabled         = false ;
				form.C_BOX_DATA_START.disabled = false ;
				form.C_BOX_DATA_END.disabled   = false ;
			}
		}
	</script>") ;

	$checked = "" ;
	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_frame_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_frame_use $checked onClick='toggle_frame(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$Row[title] = _L_LISTFRAME_SELECT ;
	$Row[func] = "" ;

	//스크립트 전에 들어갈 SELECT태그 문장을 만든다.
	//간단한 편의가 이렇게 복잡할 줄이야~~
	$select_list = "" ;
	$select_list .= "<select name='C_box' class='wForm' onChange='change_box_text(this.form.C_box.selectedIndex);'>\n"; 

	$cnt = 0 ;
	$frame_list = new file_list("$C_base[dir]/admin/frame", 1) ;
	$frame_list->read("frame", 0) ;
	while( ($file_name = $frame_list->next()) )
	{
		$selected = "" ;
		$frame_name = explode(".", $file_name) ;
		if($C_box == $frame_name[0])
		{
			if($_debug) echo("C_box[$C_box]<br>") ;
			$selected = "selected" ;
		}
		$select_list .= "<option value='$frame_name[0]' $selected class='wMat'>$frame_name[0]</option>\n" ;

		include("$C_base[dir]/admin/frame/$file_name") ;
		//if($_debug) echo("<xmp>F_BOX_START[$F_BOX_START] F_BOX_END[$F_BOX_END] F_BOX_BR[$F_BOX_BR] F_BOX_DATA_START[$F_BOX_DATA_START] F_BOX_DATA_END[$F_BOX_DATA_END]</xmp><br>\n") ;
		$set_array .= "	arr[$cnt][0] = \"$F_BOX_START\" ;\n" ;
		$set_array .= "	arr[$cnt][1] = \"$F_BOX_END\" ;\n" ;
		$set_array .= "	arr[$cnt][2] = \"$F_BOX_BR\" ;\n" ;
		$set_array .= "	arr[$cnt][3] = \"$F_BOX_DATA_START\" ;\n" ;
		$set_array .= "	arr[$cnt][4] = \"$F_BOX_DATA_END\" ;\n" ;
		$cnt++ ;
	}
	$selected = "" ;
	//이전 버전은 C_box값이 없으므로 사용자 정의로 구분(바뀌었을 수도 있으므로)
	//사용자 정의는 conf파일에 있는 내용이 들어가도록 한다.
	if( $C_box == "user" || empty($C_box))
	{
		$selected ="selected" ;
	}
	$select_list .= "<option value='user' $selected class='wMat'>"._L_USER_DEFINE."</option>\n" ;
	$select_list .= "</select>\n" ;

	$set_array .= "	arr[$cnt][0] = \"$C_BOX_START\" ;\n" ;
	$set_array .= "	arr[$cnt][1] = \"$C_BOX_END\" ;\n" ;
	$set_array .= "	arr[$cnt][2] = \"$C_BOX_BR\" ;\n" ;
	$set_array .= "	arr[$cnt][3] = \"$C_BOX_DATA_START\" ;\n" ;
	$set_array .= "	arr[$cnt][4] = \"$C_BOX_DATA_END\" ;\n" ;
	$cnt++ ;
	if($_debug) echo("array cnt[$cnt]<br>\n") ;

	$Row[func] .= "<script>
		var arr = new Array( $cnt ) ;
		for(i = 0 ; i < arr.length ; i++)
		{
			arr[i] = new Array(5) ;
		}

		$set_array

		function change_box_text( index )
		{
			document.save_form.C_BOX_START.value      = arr[index][0] ;
			document.save_form.C_BOX_END.value        = arr[index][1] ;
			document.save_form.C_BOX_BR.value         = arr[index][2] ;
			document.save_form.C_BOX_DATA_START.value = arr[index][3] ;
			document.save_form.C_BOX_DATA_END.value   = arr[index][4] ;
		}

		function set_box_option_last()
		{
			save_form.C_box.selectedIndex = arr.length - 1 ;
		}
		</script>\n" ;

	$Row[func] .= "$select_list" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_LISTFRAME_START ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_BOX_START' value='$C_BOX_START' class='wForm' onChange='set_box_option_last();'>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_LISTFRAME_END ; 
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_BOX_END'   value='$C_BOX_END' class='wForm' onChange='set_box_option_last();'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_LISTFRAME_BR ; 
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_BOX_BR'    value='$C_BOX_BR' class='wForm' onChange='set_box_option_last();'>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_LISTFRAME_DATA_START ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_BOX_DATA_START'  value='$C_BOX_DATA_START' class='wForm' onChange='set_box_option_last();'>\n" ;
	include("./html/config_list.html") ;

	$Row[title] = _L_LISTFRAME_DATA_END ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name='C_BOX_DATA_END'    value='$C_BOX_DATA_END' class='wForm' onChange='set_box_option_last();'>\n" ;
	include("./html/config_list.html") ;


	//////////////////////////////////////////
	$Row[title] = _L_CATEGORY_SETUP ; 
	echo("<script>
		function toggle_category(form)
		{
			if( form.C_global_category_use.checked )
			{
				//disable perm 
				form.C_category_all_use.disabled             = true ;
				form.elements['C_category_use[1]'].disabled = true ;
				form.elements['C_category_use[2]'].disabled = true ;
				form.elements['C_category_use[3]'].disabled = true ;
				form.elements['C_category_use[4]'].disabled = true ;
				form.elements['C_category_use[5]'].disabled = true ;
				form.elements['C_category_use[6]'].disabled = true ;
				form.elements['C_category_use[7]'].disabled = true ;
				form.elements['C_category_use[8]'].disabled = true ;
				form.elements['C_category_use[9]'].disabled = true ;
				form.elements['C_category_use[10]'].disabled = true ;

				form.elements['C_category_name[0]'].disabled = true ;
				form.elements['C_category_name[1]'].disabled = true ;
				form.elements['C_category_name[2]'].disabled = true ;
				form.elements['C_category_name[3]'].disabled = true ;
				form.elements['C_category_name[4]'].disabled = true ;
				form.elements['C_category_name[5]'].disabled = true ;
				form.elements['C_category_name[6]'].disabled = true ;
				form.elements['C_category_name[7]'].disabled = true ;
				form.elements['C_category_name[8]'].disabled = true ;
				form.elements['C_category_name[9]'].disabled = true ;
				form.elements['C_category_name[10]'].disabled = true ;
			}
			else
			{
				form.C_category_all_use.disabled             = false ;
				form.elements['C_category_use[1]'].disabled = false ;
				form.elements['C_category_use[2]'].disabled = false ;
				form.elements['C_category_use[3]'].disabled = false ;
				form.elements['C_category_use[4]'].disabled = false ;
				form.elements['C_category_use[5]'].disabled = false ;
				form.elements['C_category_use[6]'].disabled = false ;
				form.elements['C_category_use[7]'].disabled = false ;
				form.elements['C_category_use[8]'].disabled = false ;
				form.elements['C_category_use[9]'].disabled = false ;
				form.elements['C_category_use[10]'].disabled = false ;

				form.elements['C_category_name[0]'].disabled = false ;
				form.elements['C_category_name[1]'].disabled = false ;
				form.elements['C_category_name[2]'].disabled = false ;
				form.elements['C_category_name[3]'].disabled = false ;
				form.elements['C_category_name[4]'].disabled = false ;
				form.elements['C_category_name[5]'].disabled = false ;
				form.elements['C_category_name[6]'].disabled = false ;
				form.elements['C_category_name[7]'].disabled = false ;
				form.elements['C_category_name[8]'].disabled = false ;
				form.elements['C_category_name[9]'].disabled = false ;
				form.elements['C_category_name[10]'].disabled = false ;
			}
		}
	</script>") ;


	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_category_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_category_use $checked onClick='toggle_category(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$MAX_CATEGORY = 10 ;

	$Row[title] = "" ;
	$Row[func] = "" ;
	$Row[func] = _L_CATEGORY_NAME ;
	include("./html/config_list.html") ;

	$Row[title] = _L_CATEGORY_ALL ;
	$Row[func] = "" ;
	if(!isset($C_category_all_use)||$C_category_all_use == "1") 
	{
		$checked = "checked" ;
	}
	$Row[func] .= "<input type=checkbox name='C_category_all_use' $checked>\n" ;

	if(empty($C_category_name[0]))
	{
		$C_category_name[0] = "ALL" ;
	}
	$Row[func] .= "<input type=text length=32 maxlength=32 name='C_category_name[0]' value='$C_category_name[0]' class='wForm'> \n" ;
	include("./html/config_list.html") ;


	for($i = 1 ; $i <= $MAX_CATEGORY ; $i++)
	{
		$cate_name = "C_catetory_$i_name" ;
		if( $C_category_use[$i] == "1" )
		{
			$checked = "checked" ;
		}
		else
		{
			$checked = "" ;
		}
		$Row[title] = _L_CATEGORY." $i" ;
		$Row[func] = "" ;
		$Row[func] .= "<input type=checkbox name='C_category_use[$i]' $checked>\n" ;
		$Row[func] .= "<input type=text length=32 maxlength=32 name='C_category_name[$i]' value='$C_category_name[$i]' class='wForm'> \n" ;
		include("./html/config_list.html") ;
	}

	$Row[title] = _L_PARTSKIN_SETUP ;
	$Row[func] = "" ;
	$Row[func] .= "<select name=C_category_skin class='wForm'>" ;
	$Row[func] .= "<option value=''>"._L_CURRENT_SKINS ;
	$flist = new file_list("$C_base[dir]/board/skin/__global/category", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name))
		{
			continue ;
		}

		if($C_category_skin == $file_name)
		{
			$selected = "selected" ;
		}
		$Row[func] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row[func] .= "</select>"._L_USE ;
	include("./html/config_list.html") ;



	//////////////////////////////////////////
	$Row[title] = _L_OUTER_HEADER_FOOTER_SETUP ;
	echo("<script>
		function toggle_outer(form)
		{
			if( form.C_global_outer_use.checked )
			{
				//disable perm 
				form.C_cat_outer_header_use.disabled        = true ;
				form.C_list_outer_header_use.disabled       = true ;
				form.C_write_outer_header_use.disabled      = true ;
				form.C_reply_outer_header_use.disabled      = true ;
				form.C_edit_outer_header_use.disabled       = true ;
				form.elements['C_OUTER_HEADER[0]'].disabled  = true ;
				form.elements['C_OUTER_HEADER[1]'].disabled  = true ;
				form.elements['C_OUTER_FOOTER[0]'].disabled  = true ;
				form.elements['C_OUTER_FOOTER[1]'].disabled  = true ;
			}
			else
			{
				//enable perm 
				form.C_cat_outer_header_use.disabled        = false ;
				form.C_list_outer_header_use.disabled       = false ;
				form.C_write_outer_header_use.disabled      = false ;
				form.C_reply_outer_header_use.disabled      = false ;
				form.C_edit_outer_header_use.disabled       = false ;
				form.elements['C_OUTER_HEADER[0]'].disabled  = false ;
				form.elements['C_OUTER_HEADER[1]'].disabled  = false ;
				form.elements['C_OUTER_FOOTER[0]'].disabled  = false ;
				form.elements['C_OUTER_FOOTER[1]'].disabled  = false ;
			}
		}
	</script>") ;


	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_outer_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_outer_use $checked onClick='toggle_outer(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	$Row[title] = _L_IS_USE ;
	$Row[func] = "" ;
	$checked = "" ;
		//이전 버젼에서 사용하는 경우를 검사하기 위해서
	if( !isset($C_cat_outer_header_use) )
	{
		$C_cat_outer_header_use = 1 ;
	}
	if( !isset($C_list_outer_header_use) )
	{
		$C_list_outer_header_use = 1 ;
	}
	if( !isset($C_write_outer_header_use) )
	{
		$C_write_outer_header_use = 1 ;
	}
	if( !isset($C_reply_outer_header_use) )
	{
		$C_reply_outer_header_use = 1 ;
	}
	if( !isset($C_edit_outer_header_use) )
	{
		$C_edit_outer_header_use = 1 ;
	}


	if( $C_cat_outer_header_use == 1 ) 
	{
		$checked["cat_outer"] = "checked" ;
	}
	if( $C_list_outer_header_use == 1 ) 
	{
		$checked["list_outer"] = "checked" ;
	}
	if( $C_write_outer_header_use == 1 ) 
	{
		$checked["write_outer"] = "checked" ;
	}
	if( $C_edit_outer_header_use == 1 )
	{
		$checked["edit_outer"] = "checked" ;
	}
	if( $C_reply_outer_header_use == 1 ) 
	{
		$checked["reply_outer"] = "checked" ;
	}

	$Row[func] .= _L_LIST."<input type=checkbox name=C_list_outer_header_use $checked[list_outer]>\n" ;
	$Row[func] .= _L_CAT."<input type=checkbox name=C_cat_outer_header_use $checked[cat_outer]>\n" ;
	$Row[func] .= _L_WRITE."<input type=checkbox name=C_write_outer_header_use $checked[write_outer]>\n" ;
	$Row[func] .= _L_EDIT."<input type=checkbox name=C_edit_outer_header_use $checked[edit_outer]>\n" ;
	$Row[func] .= _L_REPLY."<input type=checkbox name=C_reply_outer_header_use $checked[reply_outer]>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_OUTER_HEADER1_PATH ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name=C_OUTER_HEADER[0]  value='$C_OUTER_HEADER[0]' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_OUTER_HEADER2_PATH ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name=C_OUTER_HEADER[1]  value='$C_OUTER_HEADER[1]' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_OUTER_FOOTER1_PATH ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name=C_OUTER_FOOTER[0]  value='$C_OUTER_FOOTER[0]' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	$Row[title] = _L_OUTER_FOOTER2_PATH ;
	$Row[func] = "" ;
	$Row[func] .= "<input type=text size=38 name=C_OUTER_FOOTER[1]  value='$C_OUTER_FOOTER[1]' class='wForm'>\n" ;
	include("./html/config_list.html") ;


	//////////////////////////////////////////
	$Row[title] = _L_PLUGIN_SETUP ;
	echo("<script>
		function toggle_plugin(form)
		{
			if( form.C_global_plugin_use.checked )
			{
				//disable perm 
				form.C_plugin_list.disabled       = true ;
				form.elements['C_plugin_install[]'].disabled    = true ;
				form.C_plugin_add.disabled        = true ;
				form.C_plugin_del.disabled        = true ;
				form.C_plugin_moveup.disabled     = true ;
				form.C_plugin_movedown.disabled   = true ;
			}
			else
			{
				//enable perm 
				form.C_plugin_list.disabled       = false ;
				form.elements['C_plugin_install[]'].disabled    = false ;
				form.C_plugin_add.disabled        = false ;
				form.C_plugin_del.disabled        = false ;
				form.C_plugin_moveup.disabled     = false ;
				form.C_plugin_movedown.disabled   = false ;
			}
		}
	</script>") ;

	$Row[func] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_outer_use == "1")?"checked":"" ; 
		$Row[func] .= _L_GLOBAL_SETUP_APPLY." <input type=checkbox name=C_global_plugin_use $checked onClick='toggle_plugin(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////

	echo("
	<script>
	sortitems = 0;  // Automatically sort items within lists? (1 or 0)
	function move(fbox,tbox) 
	{
		for(var i=0; i<fbox.options.length; i++) 
		{
			if(fbox.options[i].selected && fbox.options[i].value != \"\") 
			{
				var no = new Option();
				no.value = fbox.options[i].value;
				no.text = fbox.options[i].text;
				//alert(\"tbox:\"+tbox.options.length) ;
				tbox.options[tbox.options.length] = no;
				fbox.options[i].value = \"\";
				fbox.options[i].text = \"\";
			}
		}
		BumpUp(fbox);
	//	if (sortitems) SortD(tbox);
	}

	function BumpUp(box)  
	{
		for(var i=0; i<box.options.length; i++) 
		{
			if(box.options[i].value == \"\")  
			{
				for(var j=i; j<box.options.length-1; j++)  
				{
					box.options[j].value = box.options[j+1].value;
					box.options[j].text = box.options[j+1].text;
				}
				var ln = i;
				break;
		   }
		}
		
		if(ln < box.options.length)  
		{
			box.options.length -= 1;
			BumpUp(box);
		}
	}

	function MoveUP(box)
	{
		for(var i=0; i< box.options.length; i++) 
		{
			if(box.options[i].selected && i == 0)
			{
				break ;
			}
			if(box.options[i].selected && i != 0 ) 
			{
				var no = new Option();
				no.value = box.options[i].value;
				no.text  = box.options[i].text;
				
				box.options[i].value = box.options[i-1].value;
				box.options[i].text = box.options[i-1].text;
				box.options[i].selected = false ;
				
				box.options[i-1].value = no.value ;
				box.options[i-1].text  = no.text ;
				box.options[i-1].selected = true ;
			}
		}
	}

	function MoveDown(box)
	{
		for(var i = (box.options.length -1); i >= 0; i--) 
		{
			if(box.options[i].selected && (i+1) == box.options.length)
			{
				break ;
			}
			if(box.options[i].selected && (i+1) != box.options.length ) 
			{
				var no = new Option();
				no.value = box.options[i].value;
				no.text  = box.options[i].text;
				
				box.options[i].value = box.options[i+1].value;
				box.options[i].text = box.options[i+1].text;
				box.options[i].selected = false ;
				
				box.options[i+1].value = no.value ;
				box.options[i+1].text  = no.text ;
				box.options[i+1].selected = true ;
			}
		}
	}
	</script> ") ;

	//make total plugin_list
	$flist = new file_list("$C_base[dir]/board/plugin", 1) ;
	$flist->read("*", 0) ;
	while( ($file_name = $flist->next()) )
	{
		$selected = "" ;
		if ($_debug) echo("file_name[$file_name]<br>") ;
		if( $file_name == "." || $file_name == ".." || $file_name == "CVS" || 
			$file_name == "__global" || eregi("deleted", $file_name))
		{
			if ($_debug) echo("SKIP file_name[$file_name]<br>") ;
			continue ;
		}
		$_plugin_total[] = $file_name ;
	}

	$Row[title] = _L_PLUGIN_LIST ;
	$Row[func]  = "" ;
	$Row[func] .= "		<select multiple size='5'  name='C_plugin_list' class='wForm'>" ;
	//make not selected list
	for($i = 0 ; $i < sizeof($_plugin_total) ; $i++)
	{
		if(!ereg("$_plugin_total[$i],", $C_plugin_install))
		{
			if($_debug) echo("not [$_plugin_total[$i]] in the list [$C_plugin_install]<br>") ;
			//$_plugin_list[] = $_plugin_total[$i] ; 
			$Row[func] .= "<option value=\"$_plugin_total[$i]\">$_plugin_total[$i]</option>\n" ;
		}
	}
	$Row[func] .= "		</select>" ;
	$Row[func] .= "<br>" ;
	$Row[func] .= "	<input type='button' value='"._L_PLUGIN_PLUG."' onclick=\"move(this.form.C_plugin_list, this.form.elements['C_plugin_install[]'])\" name='C_plugin_add' class='wButton'>" ;
	$Row[func] .= "	";
	$Row[func] .= "	<input type='button' value='"._L_PLUGIN_DEPLUG."' onclick=\"move(this.form.elements['C_plugin_install[]'], this.form.C_plugin_list)\" name='C_plugin_del' class='wButton'>";
	include("./html/config_list.html") ;

	$Row[title] = _L_INSTALLED_PLUGIN ;
	$Row[func] = "" ;
	$Row[func] .= "		<select multiple size='5' name='C_plugin_install[]' class='wForm'>";

	$_plugin_install = explode(",", $C_plugin_install) ;
	for($i = 0 ; $i < sizeof($_plugin_install) ; $i++)
	{
		if(empty($_plugin_install[$i])) 
			continue ;
		$Row[func] .= "<option value='$_plugin_install[$i]'>$_plugin_install[$i]</option>\n" ;
	}
	$Row[func] .= "		</select>";
	include("./html/config_list.html") ;

	$Row[title] = _L_PLUGIN_ORDER_CHANGE ;
	$Row[func] = "" ;
	$Row[func] .= "<input type='button' value='"._L_PLUGIN_UP."' onclick=\"MoveUP(this.form.elements['C_plugin_install[]'])\" name='C_plugin_moveup' class='wButton'>";
	$Row[func] .= " <input type='button' value='"._L_PLUGIN_DOWN."' onclick=\"MoveDown(this.form.elements['C_plugin_install[]'])\" name='C_plugin_movedown' class='wButton'>";
	include("./html/config_list.html") ;

	echo("<input type=hidden name=conf_name   value=$conf_name>\n") ;
	echo("<input type=hidden name=LIST_PHP    value=$LIST_PHP>\n") ;
	echo("<input type=hidden name=WRITE_PHP   value=$WRITE_PHP>\n") ;
	echo("<input type=hidden name=DELETE_PHP  value=$DELETE_PHP>\n") ;
	echo("<input type=hidden name=CONFIRM_PHP value=$CONFIRM_PHP>\n") ;
	echo("<input type=hidden name=C_bg_img_use value=\"on\" \n") ;

	$C_BOX_START = chop($C_BOX_START) ;
	$C_BOX_END   = chop($C_BOX_END) ;
	$C_BOX_BR    = chop($C_BOX_BR) ;
	$C_BOX_DATA_START = chop($C_BOX_DATA_START) ;
	$C_BOX_DATA_END   = chop($C_BOX_DATA_END) ;

	echo("</form>") ;
	if($C_global_use) 
	{
		echo("<script> toggle_func(document.save_form) ; </script>") ;
	}
	else
	{
		if($C_global_general_use) 
			echo("<script> toggle_general(document.save_form) ; </script>") ;

		if($C_global_perm_use) 
			echo("<script> toggle_perm(document.save_form) ; </script>") ;

		if($C_global_write_use) 
			echo("<script> toggle_write(document.save_form) ; </script>") ;

		if($C_global_list_use) 
			echo("<script> toggle_list(document.save_form) ; </script>") ;

		if($C_global_news_use) 
			echo("<script> toggle_news(document.save_form) ; </script>") ;

		if($C_global_pagebar_use) 
			echo("<script> toggle_pagebar(document.save_form) ; </script>") ;

		if($C_global_grad_use) 
			echo("<script> toggle_grad(document.save_form) ; </script>") ;

		if($C_global_filter_use) 
			echo("<script> toggle_filter(document.save_form) ; </script>") ;

		if($C_global_upload_use) 
			echo("<script> toggle_upload(document.save_form) ; </script>") ;

		if($C_global_frame_use) 
			echo("<script> toggle_frame(document.save_form) ; </script>") ;

		if($C_global_category_use) 
			echo("<script> toggle_category(document.save_form) ; </script>") ;

		if($C_global_outer_use) 
			echo("<script> toggle_outer(document.save_form) ; </script>") ;
	}

	include("./html/config_footer.html") ;
?>
