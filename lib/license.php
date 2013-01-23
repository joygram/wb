<?php
if(!defined("__wb_license__")) define("__wb_license__","1") ;
else return ;
	///////////////////////////
	// 라이센스 보여주기
	///////////////////////////
	function license($_skin="", $conf, $wb_prg="board")
	{
		$_debug = 0 ;
		global $C_base ;

		require_once("$C_base[dir]/lib/io.php") ;
		if(!file_exists("$C_base[dir]/admin/license"))
		{
			err_abort("$C_base[dir]/admin/license %s", _L_NOFILE) ;
		}

		$_skindir = "$C_base[dir]/$wb_prg/skin/$_skin" ;
		if(!file_exists("$_skindir/author.txt"))
		{
			if($_debug) echo("author.txt file not exist.<br>") ;
			$fp = wb_fopen("$C_base[dir]/admin/license", "r") ;
			while(!feof($fp))
			{
				$license .= fgets($fp, 2048) ;
			}
			fclose($fp) ;
		}
		else
		{
			//author|author_email|author_url|type|version|auth_range
			$cont = @file("$_skindir/author.txt") ;
			$nCnt = 1 ;
			for($i = 0; $i < sizeof($cont); $i++)
			{
				$cont[$i] = chop($cont[$i]) ;
				if(empty($cont[$i])) continue ;
				//2002/11/10 첫줄의 레지나는 출력안함.
				if(@eregi($cont[$i],"rezina") && $i==0) 
				{
					continue ;
				}

				$cont[$i] = str_replace("<", "&lt;", $cont[$i]) ;
				$cont[$i] = str_replace(">", "&gt;", $cont[$i]) ;
				$tmp_arr = explode("|", $cont[$i]) ;

				$_author[$nCnt]  = $tmp_arr ; 
				$nCnt++ ;
			}
		}

		if(@file_exists("$C_base[dir]/release_no"))
		{
			//버젼 검사
			$cont = file("$C_base[dir]/release_no") ;
			$installed_release_no = chop($cont[0]) ;
			$installed_ver_str = chop($cont[1]) ;
			$installed_ver = chop($cont[2]) ;
			$_version_info = "ver $installed_ver_str, release $installed_release_no" ;
		}

		$i = 0 ;
		$_author[$i][0] = "Copyright(c)  WhiteBBS . net" ;  	
		$_author[$i][1] = "white@whitebbs.net" ; 	
		$_author[$i][2] = "http://whitebbs.net" ;
		$_author[$i][5] = "Copyright(c) 2001-2004,&#10;&#13;WhiteBBS.net $_version_info, &#10;&#13;All rights reserved" ; 	

		//conf[license_align 이 없을때의 처리(2004.12.14)	 by 체리토마토
		$conf[license_align] = !isset($conf[license_align])?"center":$conf[license_align] ;	

		$license = "" ;
		if($_debug) print_r($_author) ;
		$license .= "<style type='text/css'>.wCopy {font-family:tahoma; font-size:7pt; };</style>" ;
		$license .= "<table width='$conf[table_size]' align='".$conf[table_align]."' border=0><tr><td>" ;
		$license .= "<div align='".$conf[license_align]."' class='wCopy'>" ;
		for($i = 0 ; $i < sizeof($_author) ; $i++)
		{
			if(empty($_author[$i][0])) continue ;
			$license .= "<a href='{$_author[$i][2]}' target='_blank' title='{$_author[$i][5]}' >{$_author[$i][0]}" ;	
			if( $i+1 < sizeof($_author))
			{
				$license .= " / </a>" ;
			}
			else
			{
				$license .= "</a>" ;
			}
		}
		$license .= ("</div></td></tr></table>") ;
		
		return $license ;
	}

	function license2()
	{
		global $C_base ;
		require_once("$C_base[dir]/lib/io.php") ;

		$fp = wb_fopen("$C_base[dir]/admin/license2", "r") ;
		while(!feof($fp))
		{
			$license .= fgets($fp, 2048) ;
		}
		fclose($fp) ;
	
		return $license ;
	}

	function bsd_license( $lang )
	{
		switch(lang)
		{
			case "kr":
				require_once("admin/bsd_license.kr") ;
				break ;
			case "jp":
				break ;

			case "en":
				break ;
		}
	}
?>
