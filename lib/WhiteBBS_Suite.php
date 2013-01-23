<?php

class WhiteBBS_Suite
{
	
	var $_table ; 

	var $_globals ; 

	var $_skindir ;

	var $_basedir ;

	var $_auth ;

	var $_dbi ;

	/// 스킨 변수 
	var $_conf ;
	
	var $_row ;
	
	var $_url ;
	
	var $_hide ;
	
	/// 처리 변수 
	var $_chk_vars ;	

	var $_debug ;


	function WhiteBBS_Suite()
	{
		$this->_debug = 1 ;		
	}
	
	/// GET이나 POST 둘중의 하나만 사용함. whiteBBS용 데이터를 설정
	function get_server_var( $chk_vars )
	{
		/// 아니면 해당하는 변수를 찾아서 넘겨주는 멤버를 만들던가... 
		
    	eval( $this->_globals ) ;  //전역 서버 변수 사용 설정.

		// @todo $chk_vars array로 체크 배열 루프돌면서 체크 

		if( array_key_exists( $chk_vars, $__POST ) )
		{
			return $__POST ;
		}

		return $_GET ;
	}

	
	///
	function check_perm()
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


		$this->_auth->run_mode(WRITE_MODE) ;

		$this->_auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;

		if( ! $this->_auth->is_anonymous() )
		{
			err_abort("로그인 중에는 가입하실 수 없습니다.") ;
		}
	}
	
	/// 외부머리말 
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
		$Row_m = $this->_row ;

		$Conf_m = $this->_conf ; 

		if( $this->_debug) print_r( $Conf_m ) ;
		//$URL_m = make_url($data, $Row, "member") ;

	
		//print_r( $M_conf ) ;


		//$M_conf[table_size] = empty($C_table_size)?500:$C_table_size ;  
		//$C_table_size = empty($C_table_size)?500:$C_table_size ; 
		//$Row_m[table_size] = $C_table_size ;

		
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


	///외부 꼬리말
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


	/// 꼬리말
	function footer()
	{
		$Row_m = $this->skin_var_row() ;

		$Conf_m = $this->_conf ; 


		//$hide = make_comment($this->_table, $Row, NOT_USE, "member") ;


		$this->outer_footer() ;
	}


	function write_header() 
	{

		if( @file_exists("{$this->_skindir}/{$this->_write_form}_header.html") )
		{
			include("{$this->_skindir}/{$this->_write_form}_header.html") ;
		}
	}


	
	
} ;

?>