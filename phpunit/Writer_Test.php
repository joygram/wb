<?php 
require_once("../member/Writer.php") ;
require_once("PHPUnit.php") ;


class Writer_Test extends PHPUnit_TestCase
{
	var $_writer ;

	function Writer_Test( $name )
	{
		$this->PHPUnit_TestCase( $name ) ;
	}


	function setUp()
	{
		require_once("../lib/system_ini.php") ;
		require_once("../lib/get_base.php") ;
		$C_base = get_base(1) ; 

		$wb_charset = wb_charset($C_base[language]) ;

		require_once("$C_base[dir]/auth/auth.php") ;

		require_once("$C_base[dir]/lib/wb.inc.php") ;

		$this->_writer = new Writer( "member", $auth ) ;
	}

	function tearDown() 
	{
	}


	function test_write_form()
	{
		echo("write_form") ;


		$this->_writer->write_form() ;

	}

}


?>