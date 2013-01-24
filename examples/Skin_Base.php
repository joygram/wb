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

		///�⺻ ���� ������ �ȿ��� ���� �س���.
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
	@brief �� ��ɿ� �ش��ϴ� ����� �������.
	@author : apollo@whitebbs.net
	@date : 2005/01/16(��)

	@todo : 2005/01/16 $hide�� �޾�ó���ϴ� �κ� �߰� �ʿ�.
	*/
	function header($row = "")
	{
		eval($this->globals) ; // eval�� �������� �ڵ��� �������� ���������ֱ� ����. �ٱ����� ���Ƿ� �������� ����� ������ �� �ְ� �� �� �ִ�.

		$this->show($row, "", "header", FALSE) ;	//���κ��� ����� ������.

		$this->show($row, "", "_header") ;	//type�� ����� ������. (list, cat, write)

		return ;
	}

	/**
	@brief ��Ų ��������.
	@author apollo@whitebbs.net
	@date 2005/01/16(��)
	ã�� ����: �ֱ� ��Ų����(���� ����, �⺻ ��Ų), ���� ��Ų����(���� ����, �⺻��Ų) @n
	3.0 ���ʹ� header, footer�� Ȯ���� html�ٿ� ����ϵ��� �Ѵ�. @n

	@section name_rule ��Ų���� �̸� ��Ģ
	- "_"���ڷ� �����Ͽ� ��ɺ��� �̸����´�.
	- attr�� header, footer�� ��쿡�� �����Ѵ�. 
	session_type_attr.html @n
	- session 	
		���� �ڽ��� ���� ���� �׷�( admin, group, member, anonymoue) @n
		admin : admin
		group : group
		member : member
		anonymous : ����.
	- type
		list : list
		cat : cat
		write : write

	- attr
		header : header
		footer : footer

	@param $Row : DB�������� ����
	@param $hide : ��Ų�������� �ڸ�Ʈó��
	@param $attr : header, footer �Ӽ�, ������ header������ �ڵ���� ���Ǹ� ���� ���� ����� "_header"�� ����ϵ����Ѵ�.

	@todo sess_name(auth�� ����)
	@todo board class���� confó�� �ʿ�.
	*/
	function show($Row, $hide, $attr="", $type_enable=TRUE)
	{
		eval($this->globals) ;

		//Ÿ���� ������� �ʴ� ���� header, footer�� ����̴�.
		if($type_enable)
			$type = $this->type ;
		else
			$type = "" ;

		// ã�� ����: �ֱ� ��Ų����(���� ����, �⺻ ��Ų), ���� ��Ų����(���� ����, �⺻��Ų)
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
					// 3.0 ���ʹ� header,footer�� Ȯ���� html�� ���δ�.
					$skin_file = "$this->skindir/$type$attr.html" ;
					if($this->debug) echo("$type$attr: skin_file[$skin_file] $i<br>") ;
					continue ;

				case 2:
					// 2.x ���� ��Ų�õ�
					// $sess_name ó���� �ȵǾ ���⼭ �⺻��Ų�� ã��.
					$skin_file = "$this->skindir/$sess_name/$type$attr" ;
					if($this->debug) echo("$type$attr: skin_file[$skin_file] $i<br>") ;
					continue ;

				case 3:
					// 2.x �⺻ ��Ų �õ�
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
	�ܺ� ������ ����.
	*/
	function outer_footer() 
	{
		eval($this->globals) ;
	}
}


/*
//list skin TEST
//set Row
$Row['name'] = "������ ���̽�" ;
$Row['uid'] = 100 ;

$skin = new Skin_Base("list") ;
$skin->set_globals('$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION, $conf') ; //�� �Լ��� �ݵ�� '(Ȭ����ǥ)�� ���ֵ��� �Ѵ�.
$skin->lists($Row) ;

//write skin

//edit skin
//

*/
?>
