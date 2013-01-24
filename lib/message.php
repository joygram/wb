<?php
if(!defined("__wb_message__")) define("__wb_message__","1") ;
else return ;
	/**
	// 메시지 보여주기
	*/
	function err_msg($message, $code="")
	{
		global $C_base ;

		$system_conf = "$C_base[dir]/system.ini.php" ;
		@include($system_conf) ;

		if(!empty($code))
		{
			$message = sprintf($message, $code) ;
		}

		$Row['theme_url'] = "$C_base[url]/theme/$C_theme" ;
		if( @file_exists("$C_base[dir]/theme/$C_theme/message.html") )
		{
			include("$C_base[dir]/theme/$C_theme/message.html") ;
		}
		else
		{
			echo $message ;
		}
	}


	/**
	// 메시지를 출력하고 프로그램 종료
	*/	
	function err_abort($message, $code ="")
	{
		global $C_base ;

		$system_conf = "$C_base[dir]/system.ini.php" ;
		@include($system_conf) ;

		if(!empty($code))
		{
			$message = sprintf($message, $code) ;
		}

		$mesg = urlencode($message) ;
		$script = urlencode($GLOBALS[PHP_SELF]) ;
		$hostname = urlencode($GLOBALS[SERVER_NAME]) ;

		$Row['theme_url'] = "$C_base[url]/theme/$C_theme" ;
		
		$msg_file = "$C_base[dir]/theme/$C_theme/message_abort.html" ;
		if( @file_exists($msg_file) )
		{
			include("$msg_file") ;
		}
		else
		{
			echo $message ;
		}
		exit ;
	}
?>
