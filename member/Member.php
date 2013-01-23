<?php

global $C_base ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

require_once("$C_base[dir]/lib/Record.php") ;


/// 코딩스타일 가이드
/// member변수는 $_로 시작함. 
/// 배열변수명 끝에 _a또는 s로 끝나게...
class Member extends Record 
{
	var $_debug ;

	function Member()
	{
		$this->_debug = 1 ;

		$this->init() ;
	}

	function init()
	{
		if($this->_debug) echo(__FUNCTION__." BEGIN<br>") ;
		
		/// @warn 새로운 필드가 추가될 때는 반드시 맨 뒤쪽에 추가 해주어야 함.
		/// @warn 파일시스템의 경우 중간에 끼워넣으면 데이터가 꼬임.
		$this->_fields = array(
			"uid",			 
			"gid",

			"uname",
			"alias",
			"password",
			"crypt_type", 

			"access_count",
			"point",
			"auth_level",

			"name",
			"lastname",
			"firstname",

			"sex",
			"idnum",
			"birthday",
			"lunar_birth",

			"email",
			"mobilephone",
			"homepage",

			"password_clue",
			"password_answer",
			"email_receive", 

			"create_time",
			"modify_time",
			"login_time",
			"save_dir",

			"note",

			"final_scholarship",
			"job_kind",
			"foreigner",

			"home_country",
			"home_city",
			"home_district",
			"home_address",
			"home_zipcode",
			"home_phone",
			"home_fax",

			"company_country",
			"company_city",
			"company_district",
			"company_address",
			"company_zipcode",
			"company_name",
			"company_department",
			"company_title",
			"company_phone",
			"company_fax",
			"company_homepage"
			) ;

	} // init 
} ;






?>
