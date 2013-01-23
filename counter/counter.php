<?php
	// 2002/11/02
	//기본 URL 오픈을 기준으로 생각한다.
	if(empty($C_base_dir))
	{
		$C_base_dir = ".." ;
		$depth = 1 ;
		$_output = true ;
	}
	else
	{
		$depth = -1 ;
		$_output = false ;
	}
	require_once("$C_base_dir/lib/system_ini.php") ;
	require_once("$C_base_dir/lib/get_base.php") ;
	$C_base = get_base($depth) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base_dir/lib/wb.inc.php") ;

	prepare_server_vars() ;

	if(empty($data))
		$data = $counter_data ;

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	if( empty($data) )
	{
		err_abort("counter: [data]를 넣어주세요.") ;
	}	
	else
	{
		$_data = $data ;
	}

	$conf = read_counter_config($_data) ;
	if(!empty($skin))
	{
		$conf[skin] = $skin ;
	}
	$conf[cookie_time] = empty($conf[cookie_time])?1:$conf[cookie_time] ;

	$_skindir = "$C_base[dir]/counter/skin/$conf[skin]" ;
	$_datadir = "$C_base[dir]/counter/data" ;

	$cookie_time=3600*$conf[cookie_time] ;	 //한시간이 3600

	//interval이 지나거나 쿠키가 없으면 다시 세팅 해준다.
	//timestamp interval 초단위
	$_timestamp = time() ; //다양한 시간형식을 지원하기 위해서
	$_interval = $_timestamp - $__COOKIE[cw_counter] ;
	if($_interval > $conf[cookie_time]*60 || !isset($__COOKIE[cw_counter]))
	{
		setcookie("cw_counter", $_timestamp, time()+604800, "/") ;
		$update = true ;
	}
	if($update) update_counter($_data);

	$Row_c = array("") ;
	$Row_c["counter"] = show_counter($_data);

	if($_output)
	{
		echo $Row_c["counter"] ;
	}


///////////////////////////////////////////////////////////////////////////
function show_counter($_data)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;

	$Row_c = array("") ;
	$URL_c = array("") ;
	$hide_c = array("") ;

	$_skindir = "$C_base[dir]/counter/skin/$conf[skin]" ;
	if($_debug) echo("[$_skindir]<br>") ;
	
	// make URL_c
	$URL_c["skin"] = "$C_base[url]/counter/skin/$conf[skin]" ;
	//$URL_c["stat"] = "$C_base[url]/admin/counter/

	$tot_array = read_total($_data) ;
	// make Row_c
	$Row_c['yesterday'] 	= $tot_array[0] ;
	$Row_c['today'] 		= $tot_array[1] ;
	$Row_c['week'] 		    = $tot_array[2] ;
	$Row_c['month'] 		= $tot_array[3] ;
	$Row_c['year'] 			= $tot_array[4] ;
	$Row_c['total'] 		= $tot_array[5] ; 
	$Row_c['total'] += ($conf['total_base'])?$conf['total_base']:"" ;
	$Row_c['max'] 			= $tot_array[6] ;

	if($_debug) echo("total [$Row_c[total]][$Row_c[total_base]]<br>") ;

	//make comment
	$hide_c['view_yesterday'] = "<!--\n" ;
	$hide_c['/view_yesterday'] = "-->\n" ;
	$hide_c['view_today'] = "<!--\n" ;
	$hide_c['/view_today'] = "-->\n" ;
	$hide_c['view_month'] = "<!--\n" ;
	$hide_c['/view_month'] = "-->\n" ;
	$hide_c['view_year'] = "<!--\n" ;
	$hide_c['/view_year'] = "-->\n" ;
	$hide_c['view_total'] = "<!--\n" ;
	$hide_c['/view_total'] = "-->\n" ;
	$hide_c['view_max'] = "<!--\n" ;
	$hide_c['/view_max'] = "-->\n" ;
	if($conf[view_yesterday]=="on")
	{
		$hide_c['view_yesterday'] = "" ;
		$hide_c['/view_yesterday'] = "" ;
	}
	if($conf[view_today]=="on")
	{
		$hide_c['view_today'] = "" ;
		$hide_c['/view_today'] = "" ;
	}
	if($conf[view_month]=="on")
	{
		$hide_c['view_month'] = "" ;
		$hide_c['/view_month'] = "" ;
	}
	if($conf[view_year]=="on")
	{
		$hide_c['view_year'] = "" ;
		$hide_c['/view_year'] = "" ;
	}
	if($conf[view_total]=="on")
	{
		$hide_c['view_total'] = "" ;
		$hide_c['/view_total'] = "" ;
	}
	if($conf[view_max]=="on")
	{
		$hide_c['view_max'] = "" ;
		$hide_c['/view_max'] = "" ;
	}


	if(file_exists("$_skindir/images/0.gif"))	//이미지 카운터 스킨인 경우
	{
		$str_total=(string) $data[0];
		$str_today=(string) $data[1];
		$str_yesterday= (string) $data[2];
		$str_max = (string) $data[3];

		$Row_c['yesterday'] = makeimage($Row_c['yesterday'], $conf[skin]);
		$Row_c['today']     = makeimage($Row_c['today'],     $conf[skin]);
		$Row_c['month']     = makeimage($Row_c['month'],     $conf[skin]);
		$Row_c['total']     = makeimage($Row_c['total'],     $conf[skin]);
		$Row_c['max']       = makeimage($Row_c['max'],       $conf[skin]);
	}

	ob_start() ;
	if (!($popup=="none"))
	{	
		if ($popup=="1")
		{
			$title="로그인";
		}
		else
		{
			$title="통계보기";
			$popup_url="$C_base[url]/admin/counter/stat.php?data=$_data" ;	
		}
		
		echo("<script>\n function popup()\n {window.open('$popup_url', 'statview', 'width=520, height=480, status=yes, scrollbars=yes');}\n	</script>\n") ;

		echo("<table border='0' cellspacing='0' cellpadding='0' style='cursor:hand' title=$title onclick='popup()'><tr><td>");
	}
	
	if($_debug) echo("skindir:$_skindir<br>") ;
	include("$_skindir/counter.html");

	if (!($popup=="none"))
		echo "</td></tr></table>";

	$count_str = ob_get_contents() ;
	ob_end_clean() ;


	if ($conf[event_point]==$Row_c[total])	//이벤트 페이지로 넘어가기, 카운터가 증가하는 경우에만 
	{
		echo ("<script>window.open('$conf[event_url]','new','width=350,height=400,scrollbars=yes,resizable=0,status=yes,menubar=0'); popup.focus();</script>") ;
	}
	return $count_str ;
}

function makeimage($str, $skin)
{
	$_debug = 0 ;
	global $C_base ;
	$count_str="";

	for($i=0; $i<strlen($str); $i++)
	{
		$count = "" ;
		$sub_str=substr($str, $i, 1);
		switch ($sub_str) 
		{
			case 0:
			$count= "<img src='$C_base[url]/counter/skin/$skin/images/0.gif'>";
			break;
			case 1:
			$count= "<img src='$C_base[url]/counter/skin/$skin/images/1.gif'>";
			break;
			case 2:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/2.gif'>";
	        break;
			case 3:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/3.gif'>";
			break;     
			case 4:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/4.gif'>";
			break;
			case 5:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/5.gif'>";
			break;
			case 6:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/6.gif'>";
			break;
			case 7:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/7.gif'>";
			break;
			case 8:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/8.gif'>";
			break;
			case 9:
		    $count= "<img src='$C_base[url]/counter/skin/$skin/images/9.gif'>";
			break;
		}

		$count_str .= $count;
	}

	return ($count_str);
}

function update_counter($_data)
{
	$_debug = 0 ;
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	global $C_base ;
	global $conf ;

	//require_once("$C_base[dir]/lib/wb.inc.php") ;
	$_datadir = "$C_base[dir]/counter/data/$_data" ;

	//phpSniff그대로 끌어다 씀
	require_once("$C_base[dir]/lib/contrib/phpsniff/phpSniff.core.php");
	require_once("$C_base[dir]/lib/contrib/phpsniff/phpSniff.class.php");
	require_once("$C_base[dir]/lib/contrib/phpsniff/phpTimer.class.php");

	$sniffer_settings = array('check_cookies'=>$cc,
							  'default_language'=>$dl,
							  'allow_masquerading'=>$am);
	$client = new phpSniff($UA,$sniffer_settings);
	if($_debug) echo $client->property("platform") ;
	if($_debug) echo $client->property("os") ;
	if($_debug) echo $client->property("language") ;
	if($_debug) echo $client->property("ua") ;
	if($_debug) echo $client->property("browser") ;
	if($_debug) echo $client->property("long_name") ;
	if($_debug) echo $client->property("version") ;


	$dbi = new db_counter($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

	//데이터 갱신
	$tmp_date = date("Y,m,d,w,H,i") ;
	if($_debug) echo("[$tmp_date]<br>") ;
	$c_date = explode(",", $tmp_date) ;
	//$c_date[2] += 2 ; //  for test 
	$cont_array = array(
		"date"			=> "$c_date[0]/$c_date[1]/$c_date[2]/$c_date[3]",
		"date2"         => "$c_date[0]$c_date[1]$c_date[2]",
		"hour"			=> $c_date[4],
		"ip"			=> $__SERVER["REMOTE_ADDR"],
		"referer"		=> $__SERVER['HTTP_REFERER'],
		"ua"			=> $client->property("ua"), 
		"browser"		=> $client->property("browser"),
		"long_name"		=> $client->property("long_name"),
		"version"		=> $client->property("version"),
		"os"			=> $client->property("os"), 
		"platform"		=> $client->property("platform"),
		"language"		=> $client->property("language"),
		) ;
	$return_data = $dbi->update_index($_data, "data", $cont_array) ;	

	// SAVE total.dat.php 
	// return idx로 저장 
	if($_debug) print_r($return_data) ;
	update_total($_data, $return_data) ;

	save_contents($cont_array) ;

	return $return_data ;
}


function save_contents($cont_array)
{
	save_ip($cont_array) ;
	save_referer($cont_array) ;
	save_browser($cont_array) ;
	save_os($cont_array) ;
	save_lang($cont_array) ;
}

//레퍼러 정보 저장
function save_referer($cont_array)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;
	global $_data ;
	$_datadir = "$C_base[dir]/counter/data/$_data/referer" ;

	$data_file = "$_datadir/referer{$cont_array[date2]}.php" ;
	// update_ip
	if(!file_exists($data_file))
	{
		touch($data_file) ;
		chmod($data_file, 0666) ;
	}
	$domain = "" ;
	$uri = "" ;
	$tmp_ref = explode("/",$cont_array[referer]) ;
	$cont_domain = $tmp_ref[2] ;
	$cont_uri = implode("/", array_slice($tmp_ref,3)) ;
	if($_debug) echo("[$cont_domain][$cont_uri]<br>") ;

	$contents = file($data_file) ;
	$write_cnt = 0 ;
	$found = false ;
	$tmp_file = "$_datadir/".md5(uniqid("")) ;
	$fd = fopen($tmp_file, "w") ;
	fwrite($fd, "<?php /*$idx_info\n") ;
	for($i = 0; $i < sizeof($contents); $i++)
	{
		$contents[$i] = chop($contents[$i]) ;
		if(empty($contents[$i])) 
			continue ;
		if(eregi("<\?php", $contents[$i])||eregi("\?>", $contents[$i]))
		{
			continue ;
		}
		$tmp = explode("|", $contents[$i]) ;
		if($tmp[0] == $cont_domain && $tmp[1] == $cont_uri)
		{
			//REFERERymd::DOMAIN|URI|total
			$tmp[2] += 1 ;
			$contents[$i] = implode("|", $tmp) ;
			$found = true ;
		}
		fwrite($fd, $contents[$i]."\n") ;
		$write_cnt++ ;
	}
	//못찾은 경우는 새로운 것이므로 추가한다.
	if(!$found)
	{
		$content_str = "$cont_domain|$cont_uri|1" ;
		fwrite($fd, $content_str."\n") ;
		$write_cnt++ ;
	}
	fwrite($fd, "*/ ?>\n") ;
	fclose($fd) ;
	//2002/11/02 추후 내용가져온 후 sorting할 필요가 있음.

	//성공적으로 저장했을경우에만 
	$backup_file = "$_datadir/referer{$cont_array[date2]}.backup.php" ;
	if(filesize($tmp_file) > 0)
	{
		wb_lock($data_file) ;
		if($write_cnt > 0 )
		{
			if($_debug) echo("BACKUP INDEX<br>") ;
			if(filesize($data_file) > 0)
			{
				@unlink($backup_file) ;
				rename($data_file, $backup_file) ;
				@chmod($backup_file, 0666) ;
			}
		}
		if($_debug) echo("rename data_file<br>") ;
		if(@file_exists($data_file))
		{
			//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
			if($_debug) echo("unlink $data_file<br>") ;
			@unlink($data_file) ;
		}
		rename("$tmp_file", $data_file) ;
		chmod($data_file, 0666) ;
		wb_unlock($data_file) ;
	}
}


//아이피 정보저장
function save_ip($cont_array)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;
	global $_data ;
	$_datadir = "$C_base[dir]/counter/data/$_data/ip" ;

	$data_file = "$_datadir/ip{$cont_array[date2]}.php" ;
	// update_ip
	if(!file_exists($data_file))
	{
		touch($data_file) ;
		chmod($data_file, 0666) ;
	}
	$contents = file($data_file) ;
	$write_cnt = 0 ;
	$found = false ;
	$tmp_file = "$_datadir/".md5(uniqid("")) ;
	$fd = fopen($tmp_file, "w") ;
	fwrite($fd, "<?php /*$idx_info\n") ;
	for($i = 0; $i < sizeof($contents); $i++)
	{
		$contents[$i] = chop($contents[$i]) ;
		if(empty($contents[$i])) 
			continue ;
		if(eregi("<\?php", $contents[$i])||eregi("\?>", $contents[$i]))
		{
			continue ;
		}
		$tmp = explode("|", $contents[$i]) ;
		if($tmp[0] == $cont_array['ip'])
		{ 
			//IPymd::IP,|total
			$tmp[1] += 1 ;
			$contents[$i] = implode("|", $tmp) ;
			$found = true ;
		}
		fwrite($fd, $contents[$i]."\n") ;
		$write_cnt++ ;
	}
	//못찾은 경우는 새로운 것이므로 추가한다.
	if(!$found)
	{
		$content_str = "$cont_array[ip]|1" ;
		fwrite($fd, $content_str."\n") ;
		$write_cnt++ ;
	}
	fwrite($fd, "*/ ?>\n") ;
	fclose($fd) ;

	//성공적으로 저장했을경우에만 
	$backup_file = "$_datadir/ip{$cont_array[date2]}.backup.php" ;
	if(filesize($tmp_file) > 0)
	{
		wb_lock($data_file) ;
		if($write_cnt > 0 )
		{
			if($_debug) echo("BACKUP INDEX<br>") ;
			if(filesize($data_file) > 0)
			{
				@unlink($backup_file) ;
				rename($data_file, $backup_file) ;
				@chmod($backup_file, 0666) ;
			}
		}
		if($_debug) echo("rename data_file<br>") ;
		if(@file_exists($data_file))
		{
			//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
			if($_debug) echo("unlink $data_file<br>") ;
			@unlink($data_file) ;
		}
		rename("$tmp_file", $data_file) ;
		chmod($data_file, 0666) ;
		wb_unlock($data_file) ;
	}
}

//브라우져정보 저장
function save_browser($cont_array)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;
	global $_data ;
	$_datadir = "$C_base[dir]/counter/data/$_data/browser" ;
 
	$data_file = "$_datadir/browser{$cont_array[date2]}.php" ;
	// update_ip
	if(!file_exists($data_file))
	{
		touch($data_file) ;
		chmod($data_file, 0666) ;
	}
	$contents = file($data_file) ;
	$write_cnt = 0 ;
	$found = false ;
	$tmp_file = "$_datadir/".md5(uniqid("")) ;
	$fd = fopen($tmp_file, "w") ;
	fwrite($fd, "<?php /*$idx_info\n") ;
	for($i = 0; $i < sizeof($contents); $i++)
	{
		$contents[$i] = chop($contents[$i]) ;
		if(empty($contents[$i])) 
			continue ;
		if(eregi("<\?php", $contents[$i])||eregi("\?>", $contents[$i]))
		{
			continue ;
		}
		$tmp = explode("|", $contents[$i]) ;
		if($tmp[1] == $cont_array['long_name'] && $tmp[2] == $cont_array['version'])
		{ 
			$tmp[3] += 1 ;
			$contents[$i] = implode("|", $tmp) ;
			$found = true ;
		}
		fwrite($fd, $contents[$i]."\n") ;
		$write_cnt++ ;
	}
	//못찾은 경우는 새로운 것이므로 추가한다.
	if(!$found)
	{
		$content_str = "$cont_array[browser]|$cont_array[long_name]|$cont_array[version]|1" ;
		fwrite($fd, $content_str."\n") ;
		$write_cnt++ ;
	}
	fwrite($fd, "*/ ?>\n") ;
	fclose($fd) ;

	//성공적으로 저장했을경우에만 
	$backup_file = "$_datadir/browser{$cont_array[date2]}.backup.php" ;
	if(filesize($tmp_file) > 0)
	{
		wb_lock($data_file) ;
		if($write_cnt > 0 )
		{
			if($_debug) echo("BACKUP INDEX<br>") ;
			if(filesize($data_file) > 0)
			{
				@unlink($backup_file) ;
				rename($data_file, $backup_file) ;
				@chmod($backup_file, 0666) ;
			}
		}
		if($_debug) echo("rename data_file<br>") ;
		if(@file_exists($data_file))
		{
			//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
			if($_debug) echo("unlink $data_file<br>") ;
			@unlink($data_file) ;
		}
		rename("$tmp_file", $data_file) ;
		chmod($data_file, 0666) ;
		wb_unlock($data_file) ;
	}
}


function save_os($cont_array)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;
	global $_data ;
	$_datadir = "$C_base[dir]/counter/data/$_data/os" ;
 
	$data_file = "$_datadir/os{$cont_array[date2]}.php" ;
	// update_ip
	if(!file_exists($data_file))
	{
		touch($data_file) ;
		chmod($data_file, 0666) ;
	}
	$contents = file($data_file) ;
	$write_cnt = 0 ;
	$found = false ;
	$tmp_file = "$_datadir/".md5(uniqid("")) ;
	$fd = fopen($tmp_file, "w") ;
	fwrite($fd, "<?php /*$idx_info\n") ;
	for($i = 0; $i < sizeof($contents); $i++)
	{
		$contents[$i] = chop($contents[$i]) ;
		if(empty($contents[$i])) 
			continue ;
		if(eregi("<\?php", $contents[$i])||eregi("\?>", $contents[$i]))
		{
			continue ;
		}
		$tmp = explode("|", $contents[$i]) ;
		if($tmp[0] == $cont_array['platform'] && $tmp[1] == $cont_array['os'])
		{ 
			$tmp[2] += 1 ;
			$contents[$i] = implode("|", $tmp) ;
			$found = true ;
		}
		fwrite($fd, $contents[$i]."\n") ;
		$write_cnt++ ;
	}
	//못찾은 경우는 새로운 것이므로 추가한다.
	if(!$found)
	{
		$content_str = "$cont_array[platform]|$cont_array[os]|1" ;
		fwrite($fd, $content_str."\n") ;
		$write_cnt++ ;
	}
	fwrite($fd, "*/ ?>\n") ;
	fclose($fd) ;

	//성공적으로 저장했을경우에만 
	$backup_file = "$_datadir/os{$cont_array[date2]}.backup.php" ;
	if(filesize($tmp_file) > 0)
	{
		wb_lock($data_file) ;
		if($write_cnt > 0 )
		{
			if($_debug) echo("BACKUP INDEX<br>") ;
			if(filesize($data_file) > 0)
			{
				@unlink($backup_file) ;
				rename($data_file, $backup_file) ;
				@chmod($backup_file, 0666) ;
			}
		}
		if($_debug) echo("rename data_file<br>") ;
		if(@file_exists($data_file))
		{
			//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
			if($_debug) echo("unlink $data_file<br>") ;
			@unlink($data_file) ;
		}
		rename("$tmp_file", $data_file) ;
		chmod($data_file, 0666) ;
		wb_unlock($data_file) ;
	}
}


function save_lang($cont_array)
{
	$_debug = 0 ;
	global $C_base ;
	global $conf ;
	global $_data ;
	$_datadir = "$C_base[dir]/counter/data/$_data/lang" ;
 
	$data_file = "$_datadir/lang{$cont_array[date2]}.php" ;
	// update_ip
	if(!file_exists($data_file))
	{
		touch($data_file) ;
		chmod($data_file, 0666) ;
	}
	$contents = file($data_file) ;
	$write_cnt = 0 ;
	$found = false ;
	$tmp_file = "$_datadir/".md5(uniqid("")) ;
	$fd = fopen($tmp_file, "w") ;
	fwrite($fd, "<?php /*$idx_info\n") ;
	for($i = 0; $i < sizeof($contents); $i++)
	{
		$contents[$i] = chop($contents[$i]) ;
		if(empty($contents[$i])) 
			continue ;
		if(eregi("<\?php", $contents[$i])||eregi("\?>", $contents[$i]))
		{
			continue ;
		}
		$tmp = explode("|", $contents[$i]) ;
		if($tmp[0] == $cont_array['language']) 
		{ 
			$tmp[1] += 1 ;
			$contents[$i] = implode("|", $tmp) ;
			$found = true ;
		}
		fwrite($fd, $contents[$i]."\n") ;
		$write_cnt++ ;
	}
	//못찾은 경우는 새로운 것이므로 추가한다.
	if(!$found)
	{
		$content_str = "$cont_array[language]|1" ;
		fwrite($fd, $content_str."\n") ;
		$write_cnt++ ;
	}
	fwrite($fd, "*/ ?>\n") ;
	fclose($fd) ;

	//성공적으로 저장했을경우에만 
	$backup_file = "$_datadir/lang{$cont_array[date2]}.backup.php" ;
	if(filesize($tmp_file) > 0)
	{
		wb_lock($data_file) ;
		if($write_cnt > 0 )
		{
			if($_debug) echo("BACKUP INDEX<br>") ;
			if(filesize($data_file) > 0)
			{
				@unlink($backup_file) ;
				rename($data_file, $backup_file) ;
				@chmod($backup_file, 0666) ;
			}
		}
		if($_debug) echo("rename data_file<br>") ;
		if(@file_exists($data_file))
		{
			//2002/03/26 이부분에서 파일이 최초없을 경우 오류가 난다.
			if($_debug) echo("unlink $data_file<br>") ;
			@unlink($data_file) ;
		}
		rename("$tmp_file", $data_file) ;
		chmod($data_file, 0666) ;
		wb_unlock($data_file) ;
	}
}


function read_total($_data)
{
	global $C_base ;
	global $conf ;
	$_datadir = "$C_base[dir]/counter/data/$_data" ;
	$cont = file("$_datadir/total.dat.php") ;
	$total_array = explode("|", chop($cont[0])) ;

	return $total_array ;
}

function update_total($_data, $_summ) 
{
	global $C_base ;
	global $conf ;

	$summary_str = "$_summ[yesterday]|$_summ[today]|$_summ[week]|$_summ[month]|$_summ[year]|$_summ[total]|$_summ[max]|" ;

	$_datadir = "$C_base[dir]/counter/data/$_data" ;
	$fd = fopen("$_datadir/total.dat.php", "w+") ;
	fwrite($fd, "$summary_str\n") ;
	fclose($fd) ;
}

?>
