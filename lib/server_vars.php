<?php
if(!defined("__wb_server_vars__")) define("__wb_server_vars__","1") ;
else return ;

/// coding style 
/// member debug_
/// class Char_Name 
/// function test_Name 
/// var test_name 
class Server_Vars
{
	var $debug ;
	var $globals_pre ;	/// 4.1이하 버젼 전역 변수 
	var $globals_new ; 
	var $globals ;
	
		
	/** */
	function Server_Vars( $global_param = "" )
	{
		//전역설정 지정
		$this->globals_pre = '$HTTP_SERVER_VARS, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_POST_FILES, $HTTP_ENV_VARS, $HTTP_SESSION_VARS' ;
		$this->globals_new = '$_SERVER, $_GET, $_POST, $_COOKIE, $_FILES, $_ENV, $_SESSION ' ;

		//사용할 변수의 전역설정.
		if( $this->is_New() )	// 4.1 미만
		{
			$this->globals = "$global_param {$this->_globals_pre}"  ;
		}
		else  // 4.1 이상 
		{
			$this->globals = "$global_param {$this->_globals_new}"  ;
		}
		
		$this->_globals = "global {$this->_globals}  ; " ;
	}
	
	
	/** */
	function is_New() 
	{
		global $_SERVER ; //서버변수가 존재하는지 확인하기 위해서 global = off인경우...				
		return isset($_SERVER) ;
	}
	
	
	/** */
	function get_Vars_Pre() 
	{
		eval( $this->globals ) ;		
		
		$__SERVER  = $HTTP_SERVER_VARS ;
		$__GET     = $HTTP_GET_VARS ;
		$__POST    = $HTTP_POST_VARS ;
		$__COOKIE  = $HTTP_COOKIE_VARS ;
		$__FILES   = $HTTP_POST_FILES ;
		$__ENV     = $HTTP_ENV_VARS ;
		$__SESSION = $HTTP_SESSION_VARS ;
		
		return '$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION' ;		
	}
	
	/** */
	function get_Vars_New()
	{
		eval( $this->globals ) ;				
		$__SERVER	= $_SERVER ;
		$__GET		= $_GET ;
		$__POST		= $_POST ;
		$__COOKIE	= $_COOKIE ;
		$__FILES		= $_FILES ;
		$__ENV		= $_ENV ;
		$__SESSION	= $_SESSION ;
		
		return '$__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION' ;				
	}
	
	/** */
	function update_Vars_Pre() 
	{
		eval( $this->globals ) ;				
				
		$HTTP_SERVER_VARS  = $__SERVER ;
		$HTTP_GET_VARS = $__GET ;
		$HTTP_POST_VARS = $__POST ;
		$HTTP_COOKIE_VARS = $__COOKIE ;
		$HTTP_POST_FILES = $__FILES ;
		$HTTP_ENV_VARS = $__ENV ;
		$HTTP_SESSION_VARS = $__SESSION ;
		
	}
	
	/** */
	function update_Vars_New()
	{
		eval( $this->globals ) ;				
				
		$_SERVER	= $__SERVER ;
		$_GET		= $__GET ;
		$_POST 		= $__POST ;
		$_COOKIE	= $__COOKIE ;
		$_FILES		= $__FILES ;
		$_ENV		= $__ENV ;
		$_SESSION	= $__SESSION ;
	}
	
	/** */
	function get_Vars() 
	{
		return ( $this->is_New()? $this->get_Vars_New() : $this->get_Vars_Pre() ) ;
	}
	
	/** */
	function update_Vars() 
	{
		$this->is_New()? $this->update_Vars_New() : $this->update_Vars_Pre() ; 
	}
		
} 
?>