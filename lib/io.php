<?php
if(!defined("__wb_io__")) define("__wb_io__","1") ;
else return ;

	if (!function_exists("file_get_contents")) 
	{
		function file_get_contents($filename, $use_include_path = 0) 
		{
			$file = @fopen($filename, "rb", $use_include_path);
			if ($file) 
			{
				if ($fsize = @filesize($filename)) 
				{
					$data = fread($file, $fsize);
				} 
				else 
				{
					$data = "";  // just to be safe.
					while (!feof($file)) $data .= fread($file, 1024);
				}
				fclose($file);
			}
			return $data;
		}
	}


	function wb_fopen($file, $mode, $msg=1, $exit=1)
	{
		$file_array = explode("/", $file) ;
		unset($file_array[sizeof($file_array)-1]) ;
		$file_upper = implode("/", $file_array) ;

		if(!file_exists($file) && ($mode =="r" || $mode == "r+"))
		{
			if($msg) echo("wb_fopen:[$file]"._L_NOFILE) ;
			if($exit) exit ;
			return false ;
		}

		$fd = @fopen($file, $mode) ;
		if(!$fd)
		{
			$message = "$file 파일을 [$mode]모드로 여는데 실패했습니다.<br><b><ol>" ;
			switch($mode)
			{
				case "r" :
				case "r+" :
					if(!is_readable($file))
					{
						$message = "$file "._L_NOREAD_PERM ; 
					}
					else
					{
						$message = "$file "._L_READOPEN_FAIL ; 
					}
					break ;

				case "a" :
				case "a+" :
				case "w" :
				case "w+" :
					if(!is_writeable($file))
					{
						$message = "$file "._L_NOWRITE_PERM ;
					}
					else if( diskfreespace("./") < 1024*1024 )
					{
						$message = _L_DISK_FULL ;
					}
					else
					{
						$message = "$file "._L_WRITEOPEN_FAIL ;
					}
					break ;
			}

			if($exit)
			{
				echo($message) ;
				exit ;
			}
			else if($msg)
			{
				echo($message) ;
			}
		}
		return $fd ;
	}
?>
