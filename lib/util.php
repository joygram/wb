<?php
if(!defined("__wb_util__")) define("__wb_util__","1") ;
else return ;

	function getmicrotime()
	{ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
	} 

	function get_release($_base)
	{
		//버젼 검사
		$cont = file("$_base[dir]/release_no") ;
		$cont[0] = chop($cont[0]) ; //$installed_release_no
		$cont[1] = chop($cont[1]) ; //$installed_ver_str
		$cont[2] = chop($cont[2]) ; //$installed_ver

		return $cont ;
	}


	/*
		referer 오류 극복 함수 
		2002/05/16
	*/
	function sock_redirect( $url, $time=0 )
	{
		$tmp = str_replace("://", ":/", $url) ;
		$host = explode("/", $tmp) ;
		$uri = explode(":", $host[1]) ; 
		$uri[1] = empty($uri[1])?80:$uri[1] ;

		$fp = fsockopen ($uri[0], $uri[1], $errno, $errstr, 30);
		if (!$fp) 
		{
			// message 처리필요 2002/12/15
			echo "$errstr ($errno)<br>\n";
			exit ;
		} 
		else 
		{
			if($uri[1] == 80)
			{
				$location = $uri[0] ;
			}
			else
			{
				$location = "$uri[0]:$uri[1]" ;
			}

			fputs ($fp, "Location: http://$location\r\n") ;
			fputs ($fp, "Connection: Close\r\n\r\n");

			while (!feof($fp)) 
			{
				fgets ($fp,128);
			}
			fclose ($fp);
		}
		echo("<meta http-equiv='Refresh' content='$time; URL=$url'>") ;
	}

/**
		화면이동하는 스크립트 출력
*/
	function redirect($url, $time=0)
	{

		echo("<meta http-equiv='Refresh' content='$time; URL=$url'>") ;
		/*
		echo("<script>\n") ;
		echo("document.location='$url';\n") ;
		echo("</script>\n") ;
		*/
	}

/**
	이름변경::메시지 추가
*/
	function wb_rename($src, $dest, $msg = 0, $fail_exit = 0)
	{

		$src_array = explode("/", $src) ;
		unset($src_array[sizeof($src_array)-1]) ;
		$src_upper = implode("/", $src_array) ;

		$dest_array = explode("/", $dest) ;
		unset($dest_array[sizeof($dest_array)-1]) ;
		$dest_upper = implode("/", $dest_array) ;

		$no_perm = 0 ;
		$dir_list = "" ;

		//존재여부 검사
		if(!file_exists($src))
		{
		}

		//권한을 먼저 검사
		if(!is_writeable($src_upper))
		{
			$no_perm = 1 ;
			$dir_list .= $src_upper ;
		}
		if(!is_writeable($dest_upper))
		{
			$no_perm = 1 ;
			$dir_list .= ", $dest_upper" ;
		}

		if($no_perm)
		{
			if($msg) echo("[$dir_list]에 쓰기 권한이 없습니다.") ;
			return -1 ;
		}

		if( @rename($src,$dest) == true )
		{
			if($msg) echo ("<div class='wDefault'><b>$src</b>에서<br><b>$dest</b>로 이동하였습니다.<p>") ;
			$ret_val = 0 ;
		}
		else
		{
			if($src_upper == $dest_upper)
			{
				unset($dest_upper) ; 
			}

			if($msg) echo ("wb_rename:<b>$src</b>를 옮기는데 실패했습니다.") ;
			$ret_val = -1 ;

			if($fail_exit)
			{
				exit ;
			}
		}
		return $ret_val ;
	}
?>
