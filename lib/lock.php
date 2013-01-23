<?php
if(!defined("__wb_lock__")) define("__wb_lock__","1") ;
else return ;
	///////////////////////////////////
	// 파일 lock관련한 함수 
	///////////////////////////////////
/**
	락걸기
	@todo try락 기능은 없을까?
*/
	function wb_lock($data_path_name)
	{
		global $C_base ;
		require_once("$C_base[dir]/lib/io.php") ;

		//register_shutdown_function("wb_unlock", $data_path_name) ; // php func
		clearstatcache();
		while( @file_exists( $data_path_name."_lock" ) )
		{
			if( filemtime( $data_path_name."_lock" ) + 300 < time() )
			{
				unlink( $data_path_name."_lock") ;
			}
			usleep(500000) ;
			$i++ ;
			if( $i > 9 ) 
			{
				if($_debug) echo("data is locked ! try again later") ;
				return -1 ;
			}
		}
			//락 얻기
		$lock = wb_fopen( $data_path_name."_lock", "w" ) ;
		if(!$lock)
		{
			if($_debug) echo("try lock failed.") ;
			return -2 ;
		}
		fputs($lock,"lock file");
		fclose($lock) ;

		return 1 ;
	}


/**
	락풀기
*/
	function wb_unlock($data_path_name) 
	{
		if( @file_exists($data_path_name."_lock") )
		{
			unlink($data_path_name."_lock")  ;
		}
	}
?>
