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
	//include("$C_base[dir]/counter/conf/config.php") ;
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
		echo("<script>window.alert('카운터가 선택 되지 않았습니다.'); history.go(-1);</script>") ;
		exit ;
	}
	if( !@file_exists("$C_base[dir]/counter/conf/$conf_name") )
	{
		if ($conf_name == "__global.conf.php")
		{
			touch("$C_base[dir]/counter/conf/__global.conf.php", 0757) ;
		}
		else
		{
			echo("<script>window.alert('설정파일 [$conf_name]가 존재하지 않습니다.'); history.go(-1);</script>") ;
			exit ;
		}
	}

	include("$C_base[dir]/counter/conf/$conf_name") ; //환경파일 읽어오기
	$conf_array = explode(".", $conf_name) ;
	if( $conf_name == "__global.conf.php" )
	{
		$Row['conf'] = "카운터 전역 기능 설정(Global Function Setting)" ;	
	}
	else
	{
		$Row['conf'] = "<font class='wTitle'>$conf_array[0] 카운터 기능설정</font>" ;
	}


	//////////////////////////////////////////
	$Row['title'] = "카운터 전체 기능" ;
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
		}
	</script>") ;


	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_use == "1" )?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_use $checked onClick='toggle_func(this.form)'>\n" ;
	}

	//////////////////////////////////////////
		// 주의: 위에 전역설정부분에 헤더에 들어감 유의 2002/07/14 
		// 초기값 지정 : 게시판 별로 바꾸려면 config/게시판명.php를 수정 
	include("./html/config_header.html") ;
	//////////////////////////////////////////
	$Row['title'] = "기본 설정" ;
	echo("<script>
		function toggle_general(form)
		{
			if( form.C_global_general_use.checked )
			{
				//disable all
				form.C_skin.disabled        	= true ;
				form.C_cookie_time.disabled     = true ;
				form.C_popup_func.disabled      = true ;
			}
			else
			{
				//enable all
				form.C_skin.disabled            = false ;
				form.C_cookie_time.disabled     = false ;
				form.C_popup_func.disabled      = false ;
			}
		}
	</script>") ;


	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_general_use == "1" )?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_general_use $checked onClick='toggle_general(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$Row['title'] = "사용할 스킨" ;
	$Row['func'] = "" ;
	$Row['func'] .= "<select name=C_skin class='wForm'>\n" ;

		// 스킨 디렉토리 이름을 읽어서 출력해주기
	$flist = new file_list("$C_base[dir]/counter/skin", 1) ;
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
		$Row['func'] .= "<option value='$file_name' $selected>$file_name</option>\n" ;
	}
	$Row['func'] .= "</select>\n" ;
	include("./html/config_list.html") ;


	if(!isset($C_cookie_time))
	{
		$C_cookie_time = 1 ;
	}
	$Row['title'] = "쿠키 적용 시간" ;
	$Row['func'] = "" ;
	$Row['func'] .= "<input type=text size=4 maxlength=5 name='C_cookie_time' value='$C_cookie_time' class='wForm'>분 단위\n" ;
	include("./html/config_list.html") ;


	$Row['title'] = "카운터 클릭시" ;
	$Row['func'] = "" ;
	$selected[$C_popup_func] = "selected" ;
	$Row['func'] .= "<select name='C_popup_func' class='wForm'>\n"; 
	$Row['func'] .= "<option value='0' $selected[0] class='wMat'>없음</option>\n" ; 
	$Row['func'] .= "<option value='1' $selected[1] class='wMat'>통계보기</option>\n" ;
	$Row['func'] .= "<option value='2' $selected[2] class='wMat'>기능설정</option>\n" ;
	$Row['func'] .= "</select>\n" ;
	include("./html/config_list.html") ;

	/*
	//////////////////////////////////////////
	$Row['title'] = "권한(Permission) 설정" ;
	echo("<script>
		function toggle_perm(form)
		{
			if( form.C_global_perm_use.checked )
			{
				//disable perm 
				form.elements['C_perm[admin_read]'].disabled  = true ;
				form.elements['C_perm[group_read]'].disabled  = true ;
				form.elements['C_perm[member_read]'].disabled  = true ;
				form.elements['C_perm[anonymous_read]'].disabled  = true ;
			}
			else
			{
				//enable perm 
				form.elements['C_perm[admin_read]'].disabled  = false ;
				form.elements['C_perm[group_read]'].disabled  = false ;
				form.elements['C_perm[member_read]'].disabled  = false ;
				form.elements['C_perm[anonymous_read]'].disabled  = false ;
			}
		}
	</script>") ;

	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ($C_global_perm_use == "1")?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_perm_use $checked onClick='toggle_perm(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$Row['title'] = "관리자(admin) 권한" ;
	$Row['func'] = "" ;

		//2002/03/23 2.1.x 이전 버젼인 경우 기본 권한 컨버젼.
	if( !isset($C_auth_cat_perm) )
	{
		$C_admin_perm 		= "7" ;
		$C_group_perm 		= "6" ;
		$C_member_perm 		 = "6" ;
		$C_anonymous_perm 		= "7" ;
	}

	$perm_check[admin_stat]  = ($C_admin_perm & 4)?"checked":"" ;	
	$Row['func'] .= "통계<input type=checkbox name=C_perm[admin_stat] $perm_check[admin_stat]>\n" ;
	include("./html/config_list.html") ;

	$Row['title'] = "그룹(group) 권한" ;
	$Row['func'] = "" ;

	$perm_check[group_stat]  = ($C_group_perm & 4)?"checked":"" ;	
	$Row['func'] .= "통계<input type=checkbox name=C_perm[group_stat] $perm_check[group_stat]>\n" ;
	include("./html/config_list.html") ;

	$Row['title'] = "회원(member) 권한" ;
	$Row['func'] = "" ;

	$perm_check[member_stat]  = ($C_member_perm & 4)?"checked":"" ;	
	$Row['func'] .= "통계<input type=checkbox name=C_perm[member_stat] $perm_check[member_stat]>\n" ;
	include("./html/config_list.html") ;

	$Row['title'] = "무명씨(anonymous) 권한" ;
	$Row['func'] = "" ;

	$perm_check[anonymous_stat]  = ($C_anonymous_perm & 4)?"checked":"" ;	
	$Row['func'] .= "통계<input type=checkbox name=C_perm[anonymous_stat] $perm_check[anonymous_stat]>\n" ;
	include("./html/config_list.html") ;
	*/

	//////////////////////////////////////////
	$Row['title'] = "보기 설정" ;
	echo("<script>
		function toggle_view(form)
		{
			if( form.C_global_view_use.checked )
			{
				//disable perm 
				form.C_view_yesterday.disabled  = true ;
				form.C_view_today.disabled      = true ;
				form.C_view_month.disabled      = true ;
				form.C_view_year.disabled       = true ;
				form.C_view_total.disabled      = true ;
				form.C_view_max.disabled        = true ;
			}
			else
			{
				//enable perm 
				form.C_view_yesterday.disabled  = false ;
				form.C_view_today.disabled      = false ;
				form.C_view_month.disabled      = false ;
				form.C_view_year.disabled       = false ;
				form.C_view_total.disabled      = false ;
				form.C_view_max.disabled        = false ;
			}
		}
	</script>") ;

	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_view_use == "1" )?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_view_use $checked onClick='toggle_view(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	$view_check[yesterday] =  ($C_view_yesterday=="on")?"checked":"" ;
	$view_check[today] =  ($C_view_today =="on")?"checked":"" ;
	$view_check[month] =  ($C_view_month =="on")?"checked":"" ;
	$view_check[year]  =  ($C_view_year =="on")?"checked":"" ;
	$view_check[total] =  ($C_view_total =="on")?"checked":"" ;
	$view_check[max]   =  ($C_view_max =="on")?"checked":"" ;
	
	$Row['title'] = "보기 설정" ;
	$Row['func'] = "" ;
	$Row['func'] .= "어제<input type=checkbox name=C_view_yesterday  $view_check[yesterday]>\n" ;
	$Row['func'] .= "오늘<input type=checkbox name=C_view_today      $view_check[today]>\n" ;
	$Row['func'] .= "한달<input type=checkbox name=C_view_month      $view_check[month]>\n" ;
	$Row['func'] .= "일년<input type=checkbox name=C_view_year       $view_check[year]>\n" ;
	$Row['func'] .= "전체<input type=checkbox name=C_view_total      $view_check[total]>\n" ;
	$Row['func'] .= "하루최고<input type=checkbox name=C_view_max    $view_check[max]>\n" ;
	include("./html/config_list.html") ;

	//////////////////////////////////////////

	$Row['title'] = "데이터 설정" ;
	echo("<script>
		function toggle_data(form)
		{
			if( form.C_global_data_use.checked )
			{
				//disable perm 
				form.C_total_base.disabled    = true ;
			}
			else
			{
				//enable perm 
				form.C_total_base.disabled    = false ;
			}
		}
	</script>") ;

	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_data_use == "1" )?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_data_use $checked onClick='toggle_data(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;

	$Row['title'] = "Total 보정" ;
	$Row['func'] = "" ;
	$Row['func'] .= "<input type=text name=C_total_base size=4 maxlength=10 value='$C_total_base' class='wForm'> 점 가산" ;
	include("./html/config_list.html") ;
	//////////////////////////////////////////
	$Row['title'] = "이벤트(Event) 설정" ;
	echo("<script>
		function toggle_event(form)
		{
			if( form.C_global_event_use.checked )
			{
				//disable perm 
				form.C_event_point.disabled    = true ;
				form.C_event_url.disabled      = true ;
			}
			else
			{
				//enable perm 
				form.C_event_point.disabled    = false;
				form.C_event_url.disabled    = false;
			}
		}
	</script>") ;

	$Row['func'] = "" ;
	if( $conf_name != "__global.conf.php" )
	{
		$checked = ( $C_global_list_use == "1" )?"checked":"" ; 
		$Row['func'] .= "전역설정 적용 <input type=checkbox name=C_global_event_use $checked onClick='toggle_event(this.form)'>\n" ;
	}
	include("./html/config_title.html") ;
	//////////////////////////////////////////
	if(!isset($C_event_point))
	{
		$C_event_point = 1000 ;
	}
	$Row['title'] = "이벤트 설정" ;
	$Row['func'] = "" ;
	$Row['func'] .= "<input type=text name=C_event_point size=4 maxlength=10 value='$C_event_point' class='wForm'>점일 때 " ;
	include("./html/config_list.html") ;

	$Row['title'] = "팝업 URL" ;
	$Row['func'] = "" ;
	$Row['func'] .= "<input type=text name=C_event_url size=32 maxlength=100 value='$C_event_url' class='wForm'> 열기" ;
	include("./html/config_list.html") ;
	//////////////////////////////////////////
	echo("<input type=hidden name=conf_name   value=$conf_name>\n") ;
	echo("</form>") ;
	if($C_global_use) 
	{
		echo("<script> toggle_func(document.save_form) ; </script>") ;
	}
	else
	{
		if($C_global_general_use) 
			echo("<script> toggle_general(document.save_form) ; </script>") ;

		if($C_global_event_use) 
			echo("<script> toggle_event(document.save_form) ; </script>") ;
	}

	include("./html/config_footer.html") ;
?>
