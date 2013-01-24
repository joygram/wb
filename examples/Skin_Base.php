<?php

class Skin_Base 
{
	var $debug ;

	var $globals ;
	var $default_globals ;

	var $sess_name ;
	var $skin ;
	var $skindir ;
	var $type ;

	var $old_var ;

	//var $conf ;
	//var $C_base ;

	function Skin_Base( $skin="", $type="", $globals="" )
	{
		$this->debug = 1 ;
		$this->type = $type ;
		$this->set_globals($globals) ;
		$this->skin = $skin ;
		
		$this->skindir = "./skin/$skin" ;

		///기본 전역 변수는 안에서 세팅 해놓자.
		$this->default_globals = '$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ' ;
		$this->globals = "global ".$this->default_globals.", ".$globals." ;" ; 

		if($this->debug) echo("Skin_Base <".time()."><br>") ;

	}

	function set_globals($globals) 
	{
		$this->globals = "global ".$this->default_globals." ,".$globals." ;" ;
		//if($this->debug) echo("set_globals $this->globals<br>") ;
	}

	function outer_header()
	{
		eval($this->globals) ;
	}

	/**
	@brief 각 기능에 해당하는 헤더를 출력해줌.
	@author : apollo@whitebbs.net
	@date : 2005/01/16(일)

	@todo : 2005/01/16 $hide를 받아처리하는 부분 추가 필요.
	*/
	function header($row = "")
	{
		eval($this->globals) ; // eval을 쓴이유는 코드의 유연성을 증가시켜주기 때문. 바깥에서 임의로 전역변수 등록을 조정할 수 있게 할 수 있다.

		$this->show($row, "", "header", FALSE) ;	//윗부분의 헤더를 보여줌.

		$this->show($row, "", "_header") ;	//type별 헤더를 보여줌. (list, cat, write)

		return ;
	}

	/**
	@brief 스킨 가져오기.
	@author apollo@whitebbs.net
	@date 2005/01/16(일)
	찾는 순서: 최근 스킨파일(현재 세션, 기본 스킨), 과거 스킨파일(현재 세션, 기본스킨) @n
	3.0 부터는 header, footer에 확장자 html붙여 사용하도록 한다. @n

	@section name_rule 스킨파일 이름 규칙
	- "_"문자로 연결하여 기능별로 이름짓는다.
	- attr은 header, footer의 경우에만 적용한다. 
	session_type_attr.html @n
	- session 	
		현재 자신의 권한 적용 그룹( admin, group, member, anonymoue) @n
		admin : admin
		group : group
		member : member
		anonymous : 없음.
	- type
		list : list
		cat : cat
		write : write

	- attr
		header : header
		footer : footer

	@param $Row : DB데이터의 한줄
	@param $hide : 스킨변수에서 코멘트처리
	@param $attr : header, footer 속성, 원래는 header이지만 코드사용상 편의를 위해 실제 사용은 "_header"로 사용하도록한다.

	@todo sess_name(auth와 연동)
	@todo board class에서 conf처리 필요.
	*/
	function show($Row, $hide, $attr="", $type_enable=TRUE)
	{
		eval($this->globals) ;

		//타입을 사용하지 않는 경우는 header, footer의 경우이다.
		if($type_enable)
			$type = $this->type ;
		else
			$type = "" ;

		// 찾는 순서: 최근 스킨파일(현재 세션, 기본 스킨), 과거 스킨파일(현재 세션, 기본스킨)
		$i = 1 ;
		$skin_file = "$this->skindir/$sess_name_$type$attr.html" ;
		while($i < 4)
		{
			if(!@file_exists($skin_file))
			{
				++$i ;
				switch($i)
				{
				case 1:
					// 3.0 부터는 header,footer에 확장자 html을 붙인다.
					$skin_file = "$this->skindir/$type$attr.html" ;
					if($this->debug) echo("$type$attr: skin_file[$skin_file] $i<br>") ;
					continue ;

				case 2:
					// 2.x 세션 스킨시도
					// $sess_name 처리가 안되어서 여기서 기본스킨을 찾음.
					$skin_file = "$this->skindir/$sess_name/$type$attr" ;
					if($this->debug) echo("$type$attr: skin_file[$skin_file] $i<br>") ;
					continue ;

				case 3:
					// 2.x 기본 스킨 시도
					$skin_file = "$this->skindir/$type$attr" ;
					if($this->debug) echo("$type$attr: skin_file[$skin_file] $i<br>") ;
					continue ;

				default:
					if($this->debug) echo("$type$attr can't find") ;
					$skin_file = "" ;
					break ;
				}
			}
			else
			{
				break ;
			}
		}

		if(@file_exists($skin_file))
		{
			include($skin_file) ;
		}

		return ;
	}

	/**
	*/
	function footer()
	{
		eval($this->globals) ;
	}

	/**
	외부 꼬리말 삽입.
	*/
	function outer_footer() 
	{
		eval($this->globals) ;
	}
}


/*
//list skin TEST
//set Row
$Row['name'] = "아폴로 나이스" ;
$Row['uid'] = 100 ;

$skin = new Skin_Base("list") ;
$skin->set_globals('$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION, $conf') ; //이 함수는 반드시 '(홑따옴표)를 써주도록 한다.
$skin->lists($Row) ;

//write skin

//edit skin
//

*/
?>
