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
	require_once("$C_base[dir]/board/conf/config.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //권한,인증모듈 선언및 초기화 실행
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////

		// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		err_abort("[$data] %s", _L_ILLEGAL_BOARDNAME ) ;
	}	
	else
	{
		$C_data = $data ;
	}
	include("./html/header.html") ;
	//중복 검사
	$flist = new file_list("$C_base[dir]/board/conf", 1) ;
	$flist->read("conf.php", 0) ;
	while( ($file_name = $flist->next()) )
	{
		if( "$data.conf.php" == $file_name ) 
		{
			err_msg($data._L_BOARD_EXIST) ;
			echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='board.php'\">") ;
			exit ;
		}
	}

	//에러처리 필요
	clearstatcache() ;
	umask(0000) ;
	
		// data 디렉토리가 없다면 이곳에서 자동생성을 시도
	if(!file_exists("$C_base[dir]/board/data"))
	{
		if(!@mkdir("$C_base[dir]/board/data", 0777))
		{
			err_msg(sprintf(_L_ERROR_MAKE_BOARD_DATA, "{$C_base[dir]}/board/data")) ;
			exit ; 
		}
	}

	if(!@mkdir("$C_base[dir]/board/data/{$data}", 0777))
	{
		err_msg(sprintf(_L_ERROR_MAKE_BOARD_DIR, "{$C_base[dir]}/board/data")) ;
		exit ;
	}

	if (!copy("$C_base[dir]/board/conf/config.php", "$C_base[dir]/board/conf/${data}.conf.php")) 
	{
		rmdir("$C_base[dir]/data/$data") ;
		err_msg(" $data.conf.php "._L_COPY_FAILURE) ;
		echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='board.php'\">") ;
		exit ;
	}
      
	touch("$C_base[dir]/board/data/$data/data.idx.php") ;
	touch("$C_base[dir]/board/data/$data/total.cnt") ;
	
	chmod("$C_base[dir]/board/data/$data/data.idx.php", 0666) ;
	chmod("$C_base[dir]/board/data/$data/total.cnt", 0666) ;
    
	err_msg("[$data]"._L_CREATEBOARD_COMPLETE."<br>[$data]"._L_TRY_FUNCTION_SETUP) ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='board.php'\">") ;
	include("./html/board_footer.html") ;
	exit ;
?>
