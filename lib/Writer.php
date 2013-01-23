<?php

require_once("../lib/WhiteBBS_Suite.php") ;

class Writer extends WhiteBBS_Suite
{
	var $_write_form ;

	var $_mode ;


	function Writer( $table, $auth )
	{
		$this->_globals = "global ".prepare_server_vars()." ;" ;

		$this->_auth = $auth ;

		$this->_table = $table ; 


		$this->_basedir = $this->_auth->auth_data[base_dir] ;

		$this->_debug = 0 ;

	}

	/// 지정한 데이터 구조에 WEB으로부터 받은 변수를 세팅 
	function set_data()
	{
	}


	/// 데이터 보정 
	function correct_data()
	{
	}

	
	/// 업로드 파일 처리 
	function move_upload_file() 
	{
	}


	/// DB에 저장 
	function insert_to_db() 
	{
	}

	///
	function include_write_form() 
	{
	}




	///글쓰기 폼 
	function write_form()
	{
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


	/// 라이센스 출력
	function print_licence()
	{
		// 쿠키에서 header sent문제가 발생하여 순서를 바꿈
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("{$this->_basedir}/admin/bsd_license.$lang") ;
	}






}


?>