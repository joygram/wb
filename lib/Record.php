<?php

if(!defined("__wb_record__")) define("__wb_record__","1") ;
else return ;

global $C_base ;
global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

require_once("$C_base[dir]/lib/Xplode.php") ;
	
//require_once("./xplode.php") ;

class Record 
{
	var $_fields ;

	var $_data_a ; 


	function Record()
	{
		$this->_fields = array("") ;
		$this->_data_a = array("") ;
	}


	function dump()
	{	
		echo("FIELDS<br>") ;
		print_r( $this->_fields ) ;
		echo("<br>") ;

		echo("DATA<br>") ;
		print_r( $this->_data_a ) ;
		echo("<p>") ;
	}

	/// �����ڷ� ������ �����͸� �迭�� �и�
	function explode( $delim, $line ) 
	{
		return Xplode::ordered_explode( $delim, $this->_fields, $line ) ;

	}

	/// �迭�� �ʵ� ������ �� ���ڿ��� ����
	//function implode( $delim, $src_array )
	function implode( $delim )
	{
		return Xplode::ordered_implode( $delim, $this->_fields, $this->_data_a ) ;
	}

	/// �ʵ� ������ ���� 
	function set( $field, $data ) 
	{

		if( ! in_array( $field, $this->_fields ) ) 
		{
			echo( __FUNCTION__.":E> [$field]�� �������� �ʽ��ϴ�.<br>" ) ;
			return false ;
		}


		$this->_data_a[$field] = $data ;

		//$this->dump() ;

		return true ;
	}


	/// �ʵ� ������ �ޱ� 
	function get( $field ) 
	{


		if( ! in_array( $field, $this->_fields ) ) 
		{
			echo( __FUNCTION__.":E> [$field]�� �������� �ʽ��ϴ�.<br>" ) ;
			return false ;
		}


		//$this->dump() ;

		return $this->_data_a[$field] ; 
	}

} ;


?>
