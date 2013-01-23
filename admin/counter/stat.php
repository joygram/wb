<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	$_debug = 0 ;
	if($_debug) ob_start() ;

	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행

	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	if($dock == "yes" )
	{
		$hide['close']  = "<!--\n" ;
		$hide['/close'] = "-->\n" ;
	}


	$_data = empty($data)?$data_counter:$data ;
	if(empty($_data))
	{
		err_abort("링크에 카운터명을 넣어주세요.") ;
	}
	$mode = empty($mode)?"day":$mode ;
	$_date = empty($date)?date("Y/m/d/w"):$date ;
	
	$_datadir = "$C_base[dir]/counter/data/$_data" ;
	$_staturl = "$C_base[url]/admin/counter/stat.php" ;
	$URL = array("") ;
	$URL['total']   = "$_staturl?data=$_data&mode=total&dock=$dock" ;
	$URL['day']     = "$_staturl?data=$_data&mode=day&dock=$dock" ; 
	$URL['month']   = "$_staturl?data=$_data&mode=month&dock=$dock" ;
	$URL['year']    = "$_staturl?data=$_data&mode=year&dock=$dock" ;
	$URL['referer'] = "$_staturl?data=$_data&mode=referer&dock=$dock" ;
	$URL['os']      = "$_staturl?data=$_data&mode=os&dock=$dock" ;
	$URL['browser'] = "$_staturl?data=$_data&mode=browser&dock=$dock" ;
	$URL['lang']    = "$_staturl?data=$_data&mode=lang&dock=$dock" ;
	$URL['ip']      = "$_staturl?data=$_data&mode=ip&dock=$dock" ;

	switch($mode)
	{
		case "total" :
			$Row['header'] = "TOTAL" ;
			break ;
		case "day" :
			$Row['header'] = "DAY" ;
			break ;
		case "month" :
			$Row['header'] = "MONTH" ;
			break ;
		case "year" :
			$Row['header'] = "YEAR" ;
			break ;
		case "referer" :
			$Row['header'] = "REFERER" ;
			break ;
		case "os" :
			$Row['header'] = "OS" ;
			break ;
		case "browser" :
			$Row['header'] = "BROWSER" ;
			break ;
		case "lang" :
			$Row['header'] = "LANGUAGE" ;
			break ;
		case "ip" :
			$Row['header'] = "IP" ;
			break ;
	}

	include("./html/stat_header.html") ;
	$stat_func = "stat_$mode" ;
	$stat_func($_data, $_date) ;
	include("./html/stat_footer.html") ;

///////////////////////////
function stat_day($_data, $_date) 
{
	global $C_base ;
	$_today = date("Y/m/d/w") ;
	$_staturl = "$C_base[url]/admin/counter/stat.php" ;
	$_datadir = "$C_base[dir]/counter/data/$_data" ;
	$_date_array = explode("/", $_date) ;

	$found = false ;	
	$total = 0 ;

	if(file_exists("$_datadir/data.idx.php"))
	{
		$fd = wb_fopen("$_datadir/data.idx.php", "r") ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$row = explode("|", $line) ;
			if(strstr($row[0],$_date))
			{
				$total = $row[2] ;
				$hours = explode(",", $row[1]) ;
				for($j = 0; $j < sizeof($hours); $j++)
				{
					$Row['title'] = "$j 시" ;
					$Row['func'] = "$hours[$j]" ;
					ob_start() ;
					include("./html/stat_list.html") ;
					$stat .= ob_get_contents() ;
					ob_end_clean() ;
				}
				$found = true ;
				break ;
			}
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					document.cal.month.focus() ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					document.cal.day.focus() ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;

	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='day'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;
	$Row['func'] = "Total: $total" ;
		
	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	include("./html/stat_list.html") ;
	echo("$stat") ;
}

function stat_total($_data, $_date) 
{
}
function stat_month($_data, $_date) 
{
	global $C_base ;
	$week = array("Sun","Mon","Tue","Wed","Thur","Fri","Sat") ; 
	$_datadir = "$C_base[dir]/counter/data/$_data" ;
	$_date_array = explode("/", $_date) ;


	if(file_exists("$_datadir/data.idx.php"))
	{
		$fd = wb_fopen("$_datadir/data.idx.php", "r") ;
		$total = 0 ;
		$stat = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$row = explode("|", $line) ;
			$prev_day = 0 ;

			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}

			if(eregi("$_date_array[0]/$_date_array[1]", $row[0]))
			{
				$tmp_date = explode("/", $row[0]) ;
				$Row['title'] = "$tmp_date[1]/$tmp_date[2] {$week[$tmp_date[3]]}" ;
				$Row['func']  = "$row[2]" ;
				$total += $row[2] ; 
				$day_total += $row[2] ;
				if($prev_day != $tmp_date[2])
				{
					ob_start() ;
					include("./html/stat_list.html") ;
					$stat .= ob_get_contents() ;
					ob_end_clean() ;
					$prev_day = $tmp_date[2] ;
					$day_total = 0 ;
				}
			}
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					document.cal.month.focus() ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/' ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;

	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='month'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	$Row['func'] = "Total: $total" ;
	include("./html/stat_list.html") ;

	echo("$stat") ;


}
function stat_year($_data, $_date) 
{
}

function stat_referer($_data, $_date) 
{
	global $C_base ;
	$_datadir = "$C_base[dir]/counter/data/$_data/referer" ;
	$_date_array = explode("/", $_date) ;
	$_date_str = "$_date_array[0]$_date_array[1]$_date_array[2]" ;

	if(file_exists("$_datadir/referer$_date_str.php"))
	{
		$fd = wb_fopen("$_datadir/referer$_date_str.php", "r") ;
		$total = 0 ;
		$bookmark = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$line = chop($line) ;
			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}
			$row = explode("|", $line) ;
			$Row['title'] = "" ;
			ob_start() ;
			if(empty($row[0]))
			{
				$Row['title'] = "Bookmark or Direct Connect" ;
				$Row['func'] = $row[2] ;
				include("./html/stat_list.html") ;
				$bookmark = ob_get_contents() ;
			}
			else
			{
				$Row['title'] = cutting("$row[0]/$row[1]",35) ;
				$Row['title'] .= (strlen("$row[0]/$row[1]")>35)?"...":"" ;
				$Row['title'] = "<a href='http://$row[0]/$row[1]' target='_blank'>$Row[title]</a>" ;
				$Row['func'] = $row[2] ;
				include("./html/stat_list.html") ;
				$stat .= ob_get_contents() ;
			}
			ob_end_clean() ;
			$total += $row[2] ;
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					document.cal.month.focus() ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					document.cal.day.focus() ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;

	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='referer'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	$Row['func'] = "Total: $total" ;
	include("./html/stat_list.html") ;

	echo("$bookmark") ;
	echo("$stat") ;

}
function stat_os($_data, $_date) 
{
	global $C_base ;
	$_datadir = "$C_base[dir]/counter/data/$_data/os" ;
	$_date_array = explode("/", $_date) ;
	$_date_str = "$_date_array[0]$_date_array[1]$_date_array[2]" ;

	if(file_exists("$_datadir/os$_date_str.php"))
	{
		$fd = wb_fopen("$_datadir/os$_date_str.php", "r") ;
		$total = 0 ;
		$bookmark = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$line = chop($line) ;
			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}
			$row = explode("|", $line) ;
			$Row['title'] = "" ;
			ob_start() ;
			if(empty($row[0]))
			{
				$Row['title'] = "Unknown" ;
				$Row['func'] = $row[2] ;
				include("./html/stat_list.html") ;
				$bookmark = ob_get_contents() ;
			}
			else
			{
				$Row['title'] = "$row[0] $row[1]" ;
				$Row['func'] = $row[2] ;
				include("./html/stat_list.html") ;
				$stat .= ob_get_contents() ;
			}
			ob_end_clean() ;
			$total += $row[2] ;
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					document.cal.month.focus() ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					document.cal.day.focus() ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;

	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='os'>" ;

	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	$Row['func'] = "Total: $total" ;
	include("./html/stat_list.html") ;

	echo("$bookmark") ;
	echo("$stat") ;


}
function stat_browser($_data, $_date) 
{
	global $C_base ;
	$_datadir = "$C_base[dir]/counter/data/$_data/browser" ;
	$_date_array = explode("/", $_date) ;
	$_date_str = "$_date_array[0]$_date_array[1]$_date_array[2]" ;

	if(file_exists("$_datadir/browser$_date_str.php"))
	{
		$fd = wb_fopen("$_datadir/browser$_date_str.php", "r") ;
		$total = 0 ;
		$bookmark = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$line = chop($line) ;
			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}
			$row = explode("|", $line) ;
			$Row['title'] = "" ;
			ob_start() ;
			if(empty($row[1]))
			{
				$Row['title'] = "Unknown" ;
				$Row['func'] = $row[3] ;
				include("./html/stat_list.html") ;
				$bookmark = ob_get_contents() ;
			}
			else
			{
				$Row['title'] = "$row[1] $row[2]" ;
				$Row['func'] = $row[3] ;
				include("./html/stat_list.html") ;
				$stat .= ob_get_contents() ;
			}
			ob_end_clean() ;
			$total += $row[3] ;
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;
	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='browser'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;

	$Row['func'] = "Total: $total" ;
	include("./html/stat_list.html") ;

	echo("$bookmark") ;
	echo("$stat") ;
}

function stat_lang($_data, $_date) 
{
	global $C_base ;
	$_datadir = "$C_base[dir]/counter/data/$_data/lang" ;
	$_date_array = explode("/", $_date) ;
	$_date_str = "$_date_array[0]$_date_array[1]$_date_array[2]" ;

	$total = 0 ;
	if(file_exists("$_datadir/lang$_date_str.php"))
	{
		$fd = wb_fopen("$_datadir/lang$_date_str.php", "r") ;
		$total = 0 ;
		$bookmark = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$line = chop($line) ;
			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}
			$row = explode("|", $line) ;
			$Row['title'] = "" ;
			ob_start() ;
			if(empty($row[0]))
			{
				$Row['title'] = "Unknown" ;
				$Row['func'] = $row[1] ;
				include("./html/stat_list.html") ;
				$bookmark = ob_get_contents() ;
			}
			else
			{
				$Row['title'] = "$row[0]" ;
				$Row['func'] = $row[1] ;
				include("./html/stat_list.html") ;
				$stat .= ob_get_contents() ;
			}
			ob_end_clean() ;
			$total += $row[1] ;
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;
	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='lang'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	$Row['func'] = $total ;
	include("./html/stat_list.html") ;

	echo("$bookmark") ;
	echo("$stat") ;


}

function stat_ip($_data, $_date) 
{
	global $C_base ;
	$_datadir = "$C_base[dir]/counter/data/$_data/ip" ;
	$_date_array = explode("/", $_date) ;
	$_date_str = "$_date_array[0]$_date_array[1]$_date_array[2]" ;

	if(file_exists("$_datadir/ip$_date_str.php")) 
	{
		$fd = wb_fopen("$_datadir/ip$_date_str.php", "r") ;
		$total = 0 ;
		$bookmark = "" ;
		while(!feof($fd))
		{
			$line = fgets($fd, 4096) ;
			$line = chop($line) ;
			if(empty($line)) continue ;
			if(eregi("<\?php", $line)||eregi("\?>", $line))
			{
				continue ;
			}
			$row = explode("|", $line) ;
			$Row['title'] = "" ;
			ob_start() ;
			if(empty($row[0]))
			{
				$Row['title'] = "Unknown" ;
				$Row['func'] = $row[1] ;
				include("./html/stat_list.html") ;
				$bookmark = ob_get_contents() ;
			}
			else
			{
				$Row['title'] = "<a href=\"javascript:void(0);\" onClick=\"window.open('./whois.php?ip=$row[0]','new','width=460,height=480,scrollbars=yes,resizable=0,status=yes,menubar=0');\">$row[0]</a>" ;
				$Row['func'] = $row[1] ;
				include("./html/stat_list.html") ;
				$stat .= ob_get_contents() ;
			}
			ob_end_clean() ;
			$total += $row[1] ;
		}
		fclose($fd) ;
	}

	$Row['title'] = "<script>
			function goStat() {
				if(document.cal.month.value < 1 || document.cal.month.value > 12) 
				{
					alert('1-12월 사이의 값을 넣어주세요') ;
					return false ; 
				}
				if(document.cal.month.value.length == 1)
				{
					document.cal.month.value = '0'+document.cal.month.value ;
				}

				if(document.cal.day.value < 1 || document.cal.day.value > 31) 
				{
					alert('1-31일 사이의 값을 넣어주세요') ;
					return false ;
				}
				if(document.cal.day.value.length == 1)
				{
					document.cal.day.value = '0'+document.cal.day.value ;
				}

				document.cal.date.value = document.cal.year.value+'/'+document.cal.month.value+'/'+document.cal.day.value ;
				document.cal.submit() ;
			}
			function goToday() {
				document.cal.date.value = '$_today' ;
				document.cal.submit() ;
			}
			</script>" ;

	$Row['title'] .= "<form name='cal' action='$_staturl' method='get'>" ;
	$Row['title'] .= "<input type=hidden name='data' value='$_data'>" ;
	$Row['title'] .= "<input type=hidden name='date' value=''>" ;
	$Row['title'] .= "<input type=hidden name='mode' value='ip'>" ;
	$Row['title'] .= "<input type=text size=4 name='year' value='$_date_array[0]' class='cForm'>년 " ;
	$Row['title'] .= "<input type=text size=2 name='month' value='$_date_array[1]' class='cForm'>월 " ;
	$Row['title'] .= "<input type=text size=2 name='day' value='$_date_array[2]' class='cForm'>일 " ;

	$Row['title'] .= "<input type=button name='go' value='Go!' class='cButton' onClick='goStat();'> " ;
	$Row['title'] .= "&nbsp;<input type=button name='today' value='Today' class='cButton' onClick='goToday();'>" ;
	$Row['title'] .= "</form>" ;
	$Row['func'] = "Total: $total" ;
	include("./html/stat_list.html") ;

	echo("$bookmark") ;
	echo("$stat") ;
}


?>



