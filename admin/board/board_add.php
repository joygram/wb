<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/board/conf/config.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
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
	//�ߺ� �˻�
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

	//����ó�� �ʿ�
	clearstatcache() ;
	umask(0000) ;
	
		// data ���丮�� ���ٸ� �̰����� �ڵ������� �õ�
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
