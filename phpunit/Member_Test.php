<?php

require_once("../member/Member.php") ;
require_once("PHPUnit.php") ;

class Member_Test extends PHPUnit_TestCase
{
	var $_member ;

	function Member_Test( $name )
	{
		$this->PHPUnit_TestCase( $name ) ;
	}


	function setUp()
	{
		$this->_member = new Member() ;
	}

	function tearDown() 
	{
	}


	function test_Set()
	{

		$this->assertEquals( $this->_member->set( "uid", "002" ), true ) ;

		$this->assertEquals( $this->_member->set( "gid", "001" ), true ) ;

		$this->assertEquals( $this->_member->set( "uname", "apollo" ), true ) ;

		$this->assertEquals( $this->_member->set( "alias", "타조" ), true ) ;

		$this->assertEquals( $this->_member->set( "password", "1111" ), true ) ;

	}

	
	function test_Get()
	{
		$this->assertEquals( $this->_member->set( "uid", "002" ), true ) ;

		$this->assertEquals( $this->_member->set( "gid", "001" ), true ) ;

		$this->assertEquals( $this->_member->set( "uname", "apollo" ), true ) ;

		$this->assertEquals( $this->_member->set( "alias", "타조" ), true ) ;

		$this->assertEquals( $this->_member->set( "password", "1111" ), true ) ;



		$this->assertEquals( $this->_member->get( "uid" ), "002" ) ;

		$this->assertEquals( $this->_member->get( "gid" ), "001" ) ;

		$this->assertEquals( $this->_member->get( "uname" ), "apollo" ) ;

		$this->assertEquals( $this->_member->get( "alias" ), "타조" ) ;

		$this->assertEquals( $this->_member->get( "password" ), "1111" ) ;

	}

}

?>