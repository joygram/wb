<?php

require_once("../lib/Writer.php") ;
require_once("Member.php") ;

class Member_Writer extends Writer
{
	var $_member ;
	
	function Member_Writer( $table, $auth )
	{
		$this->_debug = 0 ;		
		
		$this->_globals = "global ".prepare_server_vars()." ;" ;

		$this->_auth = $auth ;

		$this->_table = $table ; 


		$this->_basedir = $this->_auth->auth_data[base_dir] ;

		$this->_conf = read_member_config( $this->_table, $this->_basedir ) ;

		$this->_skindir = "{$this->_basedir}/member/skin/{$this->_conf[skin]}" ;	

		$this->_write_form = "write" ;
		
	}



	/// 스킨 변수 Row_m 생성 
	function skin_var_row()
	{
		$this->_row["mode"] = $this->_mode ; 
		
		return $this->_row ;
	}


/*
	///글쓰기 폼 
	function write_form()
	{
		$this->skin_var_row() ;
		
		$this->_mode = "insert" ;

		$this->check_perm() ;

		$this->outer_header() ;

		$this->header() ;

		$this->write_header() ;

		$this->include_write_form() ;
		
		
		$this->footer() ;


		$this->outer_footer() ;
	}


	///
	function insert()
	{
		
		$this->check_perm() ;

		$this->print_licence() ;
	
		$this->set_data() ;

		$this->correct_data() ;

		$this->move_upload_file() ;

		$this->insert_to_db() ;

	
		err_msg("가입신청을 하였습니다.") ;
		
//		redirect( $C_base[url], 1 ) ;
	} 
*/

	function set_data()
	{
		if($this->_debug) echo(__FUNCTION__." BEGIN<br>") ;

    	eval( $this->_globals ) ;  //전역 서버 변수 사용 설정.

		// 다른 방식으로 개선 필요. 변수로 받는게 아니라 객체에서 검색하도록...
		$VAR = $this->get_server_var( "mode" ) ;

		$this->_member = new Member() ;

		$uid = uniqid("") ;
		$gid = -1 ;
		
		$this->_member->set( "uid",				$uid ) ;
		$this->_member->set( "gid",				$gid ) ;

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

		$this->_dbi = new db_member($this->_table, $this->_table, $this->_mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $this->_basedir ) ;
		
		$ret_data = $this->_dbi->update_index($this->_table, "data", $this->_member, "insert") ;	

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
		include("{$this->_basedir}/admin/bsd_license.$lang") ;
	}



	///
	function include_write_form() 
	{
		$Row_m = $this->skin_var_row() ;

		$Conf_m = $this->_conf ; 


		include "{$this->_skindir}/{$this->_write_form}.html" ;
	}


}


?>