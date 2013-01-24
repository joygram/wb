<?php



class Writer
{
	var $_db ; 

	var $_globals ; 

	var $_skindir ;

	var $_basedir ;

	var $_write_form ;

	var $_member ;

	var $_auth ;

	var $_conf ;




	function Writer( $db, $auth )
	{

		$this->_globals = prepare_server_vars() ;

		$this->_auth = $auth ;

		$this->_db = $db ; 


		$this->_basedir = $this->_auth->auth_data[base_dir] ;

		$this->_conf = read_member_config( $this->_db, $this->_basedir ) ;

		$this->_skindir = "{$this->_basedir}/member/skin/{$this->_conf[skin]}" ;	

		$this->_write_form = "write" ;

	}


	/// GET이나 POST 둘중의 하나만 사용함. 혼용사용은 무시함. 
	function get_server_var()
	{
    	eval( $this->_globals ) ;  //전역 서버 변수 사용 설정.

		if( empty( $__POST["uid"] ) )
		{
			return $__GET ;
		}

		return $_POST ;
	}


	function set_data()
	{
    	eval( $this->_globals ) ;  //전역 서버 변수 사용 설정.

		$VAR = get_server_var() ;

		$this->_member->set( "uid",					$VAR[uid] ) ;
		$this->_member->set( "gid",					$VAR[gid] ) ;

		$this->_member->set( "uname",				$VAR[uname] ) ;
		$this->_member->set( "alias",				$VAR[alias] ) ;

		$this->_member->set( "password",			$VAR[password] ) ;

		$this->_member->set( "access_count",		$VAR[access_count] ) ;
		$this->_member->set( "point",				$VAR[point] ) ;
		$this->_member->set( "auth_level",			$VAR[auth_level] ) ;

		$this->_member->set( "name",				$VAR[name] ) ;
		$this->_member->set( "lastname",			$VAR[lastname] ) ;
		$this->_member->set( "firstname",			$VAR[firstname] ) ;

		$this->_member->set( "sex",					$VAR[sex] ) ;
		$this->_member->set( "idnum",				$VAR[idnum] ) ;

		$this->_member->set( "birthday",			$VAR[birthday] ) ;
		$this->_member->set( "lunar_birth",			$VAR[lunar_birth] ) ;

		$this->_member->set( "email",				$VAR[email] ) ;
		$this->_member->set( "homepage",			$VAR[homepage] ) ;
		$this->_member->set( "mobilephone",			$VAR[mobilephone] ) ;

		$this->_member->set( "note",				$VAR[note] ) ;

		$this->_member->set( "final_scholarship",	$VAR[final_scholarship] ) ;
		$this->_member->set( "job_kind",			$VAR[job_kind] ) ;
		$this->_member->set( "foreigner",			$VAR[foreigner] ) ;

		$this->_member->set( "home_country",		$VAR[home_country] ) ;
		$this->_member->set( "home_city",			$VAR[home_city] ) ;
		$this->_member->set( "home_district",		$VAR[home_district] ) ;
		$this->_member->set( "home_address",		$VAR[home_address] ) ;
		$this->_member->set( "home_zipcode",		$VAR[home_zipcode] ) ;
		$this->_member->set( "home_phone",			$VAR[home_phone] ) ;
		$this->_member->set( "home_fax",			$VAR[home_fax] ) ;

		$this->_member->set( "company_country",		$VAR[company_country] ) ;
		$this->_member->set( "company_city",		$VAR[company_city] ) ;
		$this->_member->set( "company_district",	$VAR[company_district] ) ;
		$this->_member->set( "company_address",		$VAR[company_address] ) ;
		$this->_member->set( "company_zipcode",		$VAR[company_zipcode] ) ;
		$this->_member->set( "company_name",		$VAR[company_name] ) ;
		$this->_member->set( "company_department",	$VAR[company_department] ) ;
		$this->_member->set( "company_title",		$VAR[company_title] ) ;
		$this->_member->set( "company_phone",		$VAR[company_phone] ) ;
		$this->_member->set( "company_fax",			$VAR[company_fax] ) ;
		$this->_member->set( "company_homepage",	$VAR[company_homepage] ) ;

		$timestamp = time() ; //다양한 시간형식을 지원하기 위해서

		$this->_member->set( "create_time",			$timestamp ) ;
		$this->_member->set( "modify_time",			$timestamp ) ;
		//$this->_member->set( "login_time", $login_time ) ;
		$this->_member->set( "save_dir",			$save_dir ) ;

		$this->_member->set( "password_clue",		$VAR[password_clue] ) ;
		$this->_member->set( "password_answer",		$VAR[password_answer] ) ;
		$this->_member->set( "email_receive",		$VAR[email_receive] ) ;

	}


	/// 데이터 보정 
	function correct_data()
	{
		// boolean값 보정 mysql에서 boolean지원 안함.

		//$update_timestamp = $timestamp ;

		//$birthday_select = ($birthday_select == "on")? 't':'f' ;

		$this->_member->set( "lunar_birth", (($this->_member->get("lunar_birth") =='t')? 1:0) )  ;

		$this->_member->set( "email_receive", (( $this->_member->get("email_receive") =='t')? 1:0) ) ;

		$this->_member->set( "foreigner", (($this->_member->get("foreigner") =='t')? 1:0) ) ;

		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $this->_member->get("idnum") ) ;
		$this->_member->set( "idnum", $idnum ) ;

		$this->_member->set( "sex", (($this->_member->get("sex") =='t')? 1:0) ) ;
		

		//if( ! 
		$this->_member->get("idnum") ;

			
		//&& 
		//	! $this->_member->get("foreigner") )
		//{
		if(substr($idnum, 6, 1) == "1") $sex = '1' ;
		if(substr($idnum, 6, 1) == "2") $sex = '0' ;
		//}
	}

	
	/// 업로드 파일 처리 
	function move_upload_file() 
	{
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

	}


	/// DB에 저장 
	function insert_to_db() 
	{

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		
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
		
	}


	/// 라이센스 출력
	function print_licence()
	{
		// 쿠키에서 header sent문제가 발생하여 순서를 바꿈
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;
	}


	function insert()
	{
		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		//$sess = $this->_auth->member_info() ;

		$this->print_licence() ;
	
		$this->set_data() ;

		$this->correct_data() ;

		$this->move_upload_file() ;

		$this->insert_to_db() ;

	
		err_msg("가입신청을 하였습니다.") ;
		
		redirect( $C_base[url], 1 ) ;
	} 


	function outer_header()
	{
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

	}
	

	function header() 
	{
		$M_conf = $this->_conf ; 
	
		//print_r( $M_conf ) ;


		//$M_conf[table_size] = empty($C_table_size)?500:$C_table_size ;  
		//$C_table_size = empty($C_table_size)?500:$C_table_size ; 
		//$M_$Row['table_size'] = $C_table_size ;

		
		//$M_row[]
		//$M_hide[]
		//$B_row[]

			//HEADER 대소문자 구분없이 읽기 v 1.3.0 
			// 각모드에 맞는 header가 있으면 그 헤더를 이용
		if( @file_exists("{$this->_skindir}/header.html") )
		{
			include("{$this->_skindir}/header.html") ;
		}
		else
		{
			err_abort( "$this->_skindir/header.html 파일이 존재하지 않습니다." ); 
		}

	}


	function outer_footer()
	{
		if( $C_write_outer_header_use == "1" || 
			!isset($C_write_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}


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
	}



	function footer()
	{
		//$hide = make_comment($this->_db, $Row, NOT_USE, "member") ;
	
		$this->outer_footer() ;
	}


	function write_header() 
	{

		if( @file_exists("{$this->_skindir}/{$this->_write_form}_header.html") )
		{
			include("{$this->_skindir}/{$this->_write_form}_header.html") ;
		}
	}



	function write_form()
	{

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


		//모드가 지정이 안되어 있는 경우 : 처음 글쓰기로 간주...
		$mode = "insert" ;

		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		//$sess = $auth->member_info() ;

		if( ! $this->_auth->is_anonymous() )
		{
			err_abort("로그인 중에는 가입하실 수 없습니다.") ;
		}

		$this->outer_header() ;

		$this->header() ;

		$this->write_header() ;

		include "{$this->_skindir}/{$this->_write_form}.html" ;

		$this->footer() ;

		$this->outer_footer() ;

	}

}


?>