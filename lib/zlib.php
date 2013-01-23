<?php
if(!defined("__wb_zlib__")) define("__wb_zlib__","1") ;
else return ;
//업로드한 zip파일을 zip파일의 이름으로 올리고 압축을 풀어 디렉토리에 저장한다.
//
/*
usage example:

//require_once("zlib.php") ;
//print_r($HTTP_POST_FILES) ;

//prepare_server_vars() ;

echo("[".$__FILES['InputFile']['name']."][".$__FILES['InputFile']['tmp_name']."]<br>") ;
wb_upload_uncompress() ;
*/


function wb_upload_uncompress($target_dir)
{
	$_debug = 0 ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

	umask(0000) ;
		//check zlib ability 
	$extname = 'zlib';
	if (!extension_loaded($extname)) 
	{
		$dlext = (substr(PHP_OS, 0, 3) == 'WIN') ? '.dll' : '.so';
		@dl($extname . $dlext);
	}

	if ($_debug) print_r($__FILES) ;
	$is_zip = eregi("\.zip", $__FILES[InputFile][name]) ;
	$is_tar = eregi("\.tar", $__FILES[InputFile][name]) ;

	if($is_zip)
	{
		if (!extension_loaded($extname)) 
		{
			echo("사용하고 있는 웹서버에서 zlib지원을 하지 않습니다. tar파일을 사용하시기 바랍니다.") ; 
			exit ;
		}
		//$file_ext = ".zip" ;
	}
	else if($is_tar)
	{
		//
		//$file_ext = ".tar" ;
	}
	else
	{
		echo("you can only upload zip,tar:InputFile_name[".$__FILES[InputFile][name]."]<br>") ;
		exit ;
	}

	//2002/10/09 확장자 뽑아내기 
	$tmp = explode(".", $__FILES[InputFile][name]) ;
	$file_ext = ".".$tmp[sizeof($tmp)-1] ;

	if ($_debug) echo("[".$__FILES[InputFile].",[".$__FILES[InputFile][name]."]<br>") ;
	if( $__FILES[InputFile][size] != 0 && !empty($__FILES[InputFile][tmp_name]))
	{
		$base_dir_name = basename($__FILES[InputFile][name], $file_ext) ;
		$base_dir_name = "$target_dir/$base_dir_name" ;

		if (file_exists($base_dir_name))
		{
			//스크립트에서 동일한 이름을 확인하기.
		}
		else
		{
			mkdir($base_dir_name, 0777) ;
		}

		if(ini_get("open_basedir"))
		{
			if(!function_exists("move_uploaded_file"))
			{
				echo("웹에서 업로드 기능을 사용할 수 없습니다. 수동으로 업로드 해주세요") ;
				exit ;		
			}
			$unlink_archive = 1 ;
			$archive_file = "$base_dir_name/{$__FILES[InputFile][name]}" ;
			if(!move_uploaded_file($__FILES[InputFile][tmp_name], $archive_file))
			{
				echo("웹에서 업로드 기능을 사용할 수 없습니다. 수동으로 업로드 해주세요") ;
				exit ;		
			}
		}
		else
		{
			$archive_file = $__FILES[InputFile][tmp_name] ;
		}

		if($is_zip)
		{
			wb_unzip($archive_file, $base_dir_name) ;
		}
		else if($is_tar)
		{
			wb_untar($archive_file, $base_dir_name) ;
		}

		if($unlink_archive)
		{
			unlink($archive_file) ;
		}
	}
	else
	{
		echo("파일이 존재하지 않습니다. 현재 웹서버의 업로드 기능을 확인해주시기바랍니다.<br>") ;
		exit ;
	}
}


/**
*/
function wb_untar( $_package, $_dest_dir = ".", $_compress = false )
{
	global $C_base ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;

	require_once("$C_base[dir]/lib/contrib/PEAR.php") ;
	require_once("$C_base[dir]/lib/contrib/Tar.php") ;

	umask(0000) ;
	$pear = new PEAR() ;

	if ($_debug) echo ("wb_untar:start<br>") ;
	//$file_list = "extension.php os.php pear.php" ;  

	//$dest_dir = "t" ;
	//$dest_package = "t/test.tar" ;

	// 2002/10/26
	clearstatcache() ;
	$tar = new Archive_Tar($_package, $_compress) ;
	if(!$tar)
	{
		return false ;
	}

	//2002/11/04 IIS에서 오류가 나서 잘 안된다. 
	if (!$tar->extract($_dest_dir)) 
	{
		//echo('an error ocurred during package extract');
		echo('패키지파일을 푸는동안 오류가 발생했습니다. 수동으로 설치 해주세요.');
		exit ;
	}

	/*
	if (!$tar->create($file_list)) 
	{
		echo('an error ocurred during package creation');
		exit ;
	}
	else
	{
		echo("FILE_CREATION OK!") ;
	}
	*/
}


/**
*/
function wb_unzip($_zipfile, $_dest_dir = ".")
{
	$_debug = 0 ;
	global $C_base ;
	require_once("$C_base[dir]/lib/contrib/ziplib.php") ;

	umask(0000) ;

	//$zipfile = $InputFile ;
	//$_zipfile = $InputFile ;
	//2002/10/26
	clearstatcache() ;
	$zip = new ZipReader($_zipfile) ;

	if($zip->zlib_enable())
	{
		while(list($fullname, $data, $attrib) = $zip->readFile())
		{
			if($_debug) echo("fullname:[$fullname]<br>") ;

			$arr = "" ;
			$arr = explode("/", $fullname) ;

			//full path directory creation
			//$dir_name = "." ;
			//$dir_name = $base_dir_name ; //default position
			$dir_name = $_dest_dir ; //default position
			for($i=0; $i < sizeof($arr)-1; $i++)
			{
				if ($_debug) echo("->".$arr[$i]."<br>\n") ;
				$prev_dir_name = $dir_name ;
				$dir_name .= "/$arr[$i]" ;
				if ($_debug) echo("--> $dir_name <br>") ;
				if(!file_exists($dir_name) )
				{
					if(is_writeable($prev_dir_name))
					{
						mkdir($dir_name, 0777) ;
					}
					else
					{
						echo("$dir_name 디렉토리를 만들수 없습니다. <br>") ;
						exit ;
					}
				}
			}

			$npos = sizeof($arr)-1 ;
			$filename = $arr[$npos] ;

			$fp = fopen("$dir_name/$filename", "wb") ;
			fwrite($fp, $data) ;
			fclose($fp) ;
		} 
		@unlink($zipfile) ; //마무리
	}
	else
	{
		echo("사용하고 있는 웹서버에서 zlib지원을 하지 않습니다.") ; 
	}
}
?>
