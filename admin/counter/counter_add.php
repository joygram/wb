<?php
	///////////////////////////
	// 권한검사와 기본환경 읽기 
	// 순서를 지켜주어야 함. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //기준이 되는 주소 받아오기, lib.php에 있음.

	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/counter/conf/config.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////

	// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		err_abort("[$data]는 올바른 카운터 명이 아닙니다.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	$_datadir = "$C_base[dir]/counter/data/$data" ;

	include("./html/header.html") ;
	//중복 검사
	$flist = new file_list("$C_base[dir]/counter/conf", 1) ;
	$flist->read("conf.php", 0) ;
	while( ($file_name = $flist->next()) )
	{
		if( "$data.conf.php" == $file_name ) 
		{
			err_msg("카운터 $data 는 이미 존재합니다.") ;
			echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
			exit ;
		}
	}
	//에러처리 필요
	umask(0000) ;

	
	if(!file_exists("$C_base[dir]/counter/data"))
	{
		mkdir ("$C_base[dir]/counter/data", 0777);
	}

	mkdir ("$C_base[dir]/counter/data/$data", 0777);

	if (!copy("$C_base[dir]/counter/conf/config.php", "$C_base[dir]/counter/conf/${data}.conf.php")) 
	{
		rmdir("$C_base[dir]/data/$data") ;
		err_msg(" $data.conf.php 파일을 복사하는 데 실패했습니다.") ;
		echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
		exit ;
	}
      
	mkdir("$_datadir/ip",      0777) ;
	mkdir("$_datadir/browser", 0777) ;
	mkdir("$_datadir/lang",    0777) ;
	mkdir("$_datadir/referer", 0777) ;
	mkdir("$_datadir/os",      0777) ;

	touch("$_datadir/data.idx.php") ;
	chmod("$_datadir/data.idx.php", 0666) ;

	touch("$_datadir/total.dat.php") ;
	chmod("$_datadir/total.dat.php", 0666) ;
    
	err_msg("카운터 [${data}] 를 만들었습니다.<br>[${data}] 카운터의 기능설정을 해주세요.") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='counter.php'\">") ;
	include("./html/counter_footer.html") ;
	exit ;
?>
