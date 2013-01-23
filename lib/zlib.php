<?php
if(!defined("__wb_zlib__")) define("__wb_zlib__","1") ;
else return ;
//���ε��� zip������ zip������ �̸����� �ø��� ������ Ǯ�� ���丮�� �����Ѵ�.
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
			echo("����ϰ� �ִ� ���������� zlib������ ���� �ʽ��ϴ�. tar������ ����Ͻñ� �ٶ��ϴ�.") ; 
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

	//2002/10/09 Ȯ���� �̾Ƴ��� 
	$tmp = explode(".", $__FILES[InputFile][name]) ;
	$file_ext = ".".$tmp[sizeof($tmp)-1] ;

	if ($_debug) echo("[".$__FILES[InputFile].",[".$__FILES[InputFile][name]."]<br>") ;
	if( $__FILES[InputFile][size] != 0 && !empty($__FILES[InputFile][tmp_name]))
	{
		$base_dir_name = basename($__FILES[InputFile][name], $file_ext) ;
		$base_dir_name = "$target_dir/$base_dir_name" ;

		if (file_exists($base_dir_name))
		{
			//��ũ��Ʈ���� ������ �̸��� Ȯ���ϱ�.
		}
		else
		{
			mkdir($base_dir_name, 0777) ;
		}

		if(ini_get("open_basedir"))
		{
			if(!function_exists("move_uploaded_file"))
			{
				echo("������ ���ε� ����� ����� �� �����ϴ�. �������� ���ε� ���ּ���") ;
				exit ;		
			}
			$unlink_archive = 1 ;
			$archive_file = "$base_dir_name/{$__FILES[InputFile][name]}" ;
			if(!move_uploaded_file($__FILES[InputFile][tmp_name], $archive_file))
			{
				echo("������ ���ε� ����� ����� �� �����ϴ�. �������� ���ε� ���ּ���") ;
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
		echo("������ �������� �ʽ��ϴ�. ���� �������� ���ε� ����� Ȯ�����ֽñ�ٶ��ϴ�.<br>") ;
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

	//2002/11/04 IIS���� ������ ���� �� �ȵȴ�. 
	if (!$tar->extract($_dest_dir)) 
	{
		//echo('an error ocurred during package extract');
		echo('��Ű�������� Ǫ�µ��� ������ �߻��߽��ϴ�. �������� ��ġ ���ּ���.');
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
						echo("$dir_name ���丮�� ����� �����ϴ�. <br>") ;
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
		@unlink($zipfile) ; //������
	}
	else
	{
		echo("����ϰ� �ִ� ���������� zlib������ ���� �ʽ��ϴ�.") ; 
	}
}
?>
