<?php

require_once("PHPUnit.php") ;

require_once("../lib/xplode.php") ;

class Xplode_Test extends PHPUnit_TestCase
{
	function Xplode_Test( $name )
	{
		$this->PHPUnit_TestCase( $name ) ;
	}


	function setUp()
	{
	}

	function tearDown() 
	{
	}

	
	function test_Ordered_implode()
	{
		$_fields = array( "board_group","board_id","uid","uname" ) ;

		///무순서 입력 
		$row_a = array() ;

		$row_a["board_id"] = "i_001" ;
		$row_a["uid"] = "001" ;
		$row_a["board_group"] = "g_002" ;
		$row_a["uname"] = "apollo" ;


		$row_str = Xplode::ordered_implode("|", $_fields, $row_a) ;

		$this->assertEquals( $row_str, "g_002|i_001|001|apollo" ) ;

	}


	function test_Ordered_explode()
	{
		$_fields = array( "board_group","board_id","uid","uname" ) ;

		$line = "g_001|i_001|001|apollo|" ;


		$row_a = Xplode::ordered_explode( "|", $_fields, $line ) ;

		$this->assertEquals( $row_a["board_group"], "g_001" ) ;

		$this->assertEquals( $row_a["board_id"], "i_001" ) ;

		$this->assertEquals( $row_a["uid"], "001" ) ;

		$this->assertEquals( $row_a["uname"], "apollo" ) ;

	}


}

?>