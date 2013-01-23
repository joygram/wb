<?php

require_once("PHPUnit.php") ;

require_once("xplode_Test.php") ;
require_once("Member_Test.php") ;
require_once("Writer_Test.php") ;

ob_start() ;
echo( "TEST BEGIN :".time()." <br>") ;


$suite = new PHPUnit_TestSuite( "Xplode_Test" ) ;
$result = PHPUnit::run( $suite ) ;
echo $result->toHtml() ;


$suite = new PHPUnit_TestSuite( "Member_Test" ) ;
$result = PHPUnit::run( $suite ) ;
echo $result->toHtml() ;


$suite = new PHPUnit_TestSuite( "Writer_Test" ) ;
$result = PHPUnit::run( $suite ) ;
echo $result->toHtml() ;





$output = ob_get_contents() ;
ob_end_clean() ;
echo $output ; 


?>