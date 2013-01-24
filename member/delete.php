<?php
$my_version = "WhiteBoard 2.4.1 2002/05/06" ;
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2002/1/2" ;
$my_version = "WhiteBoard 2.1.2 2002/1/12" ;
$my_version = "WhiteBoard 2.1.3 2002/1/12" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001, WhiteBBs.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  

���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

WhiteBoard 1.4.2: 2001/08/15
WhiteBoard 1.4.0 pre: 2001/08/11
WhiteBoard 1.3.0: 2001/06/17
WhiteBoard 1.2.3: 2001/05/10
WhiteBoard 1.1.1: 2001/4/11
*/
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	include("../lib/wb.inc.php") ;
	include_once($C_base[dir]."/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	include_once("$C_base[dir]/lib/database.php") ;
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;

	$C_debug = 0 ;	

	//���� ���� ����� ��쿡�� ��۵� ��� �����Ѵ�.

		//���͸�
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	$board_id = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_id) ;
	if( empty($data) )
	{
		err_abort("��ũ�� �ùٸ��� �ʽ��ϴ�.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	$conf_file = "$C_base[dir]/board/conf/{$C_data}.conf.php" ;
	if(@file_exists($conf_file))
	{
		include("$conf_file") ;
	} 
	else
	{
		err_abort("$conf_file ������ �������� �ʽ��ϴ�.") ;
	}
		//2002/03/18 �⺻ ���Ѱ�����
	if( !isset($C_auth_perm) )
	{
		if($C_write_admin_only == 1)
		{
			$C_auth_perm = "7555" ; //�⺻ ���� ����
			$C_auth_cat_perm = "7555" ;
			$C_auth_reply_perm = "7555" ;
		}
		else
		{
			$C_auth_perm = "7667" ; //�⺻ ���� ����
			$C_auth_cat_perm = "7667" ;
			$C_auth_reply_perm = "7667" ;
		}

		$C_auth_user = "root" ; //�⺻ ������ ���̵� 
		$C_auth_group = "wheel" ; //�⺻ ������ �׷�
	}

	$dbi = new db_interface($C_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

	$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
	if( empty($Row[user]) ) //���� anonymous�ΰ� �ƴѰ� �˻�
	{
		// 2.1.2 ���� ������ ��� anonymous���̹Ƿ�
			//��ȣȭ �Ǿ� ������ 
		if( strlen($Row[password]) > 15 || $Row['encode_type'] == "1" )
		{
			$Row['password'] = wb_decrypt($Row[password], $Row[name]) ;
		}
		$check_data[passwd] = $Row['password']  ;
		if($C_debug) echo("$PHP_SELF:check_data[passwd][$check_data[passwd]]<br>") ;
	}
	else 
	{
		// member������ �����غ� 2002/02/17
	}

	$auth->run_mode(EXEC_MODE) ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	$sess = $auth->member_info() ;

	if( $debug == "1" )
	{
		echo("$PHP_SELF $my_version") ;
		exit ;	
	}
	
	$index_name = "data" ;
	$idx_data = array("board_group" => $board_group, "board_id" => $board_id, "nWriting" => "-1"  ) ; 
	$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "delete") ;	

	if( $idx_data[main_writing_delete] == "1" )
	{
			// ��ü �۰��� ����
		$fp = wb_fopen("$C_base[dir]/board/data/$C_data/total.cnt", "w") ;
			//
		fwrite($fp, $dbi->total ) ;
		fclose($fp) ;

		$flist = new file_list("$C_base[dir]/board/data/$C_data/", 1) ;
		$flist->read("$board_group") ;
		$nCnt = 0 ;
		while( ($file_name = $flist->next()) )
		{
			unlink("data/$C_data/$file_name") ;
		} // end of while 
	}
	else
	{
			//��۸� �����ϵ��� ó��
		unlink("data/$C_data/$board_group$board_id") ;
	}

	$dbi->destroy() ;

	make_news($C_data, $Row)  ;

	err_msg("���� �Ͽ����ϴ�.") ;
	if(@file_exists("skin/$C_skin/cat.html") && 
		$idx_data[main_writing_delete] != "1" )
	{
		$url = "$C_base[url]/board/cat.php?data=$C_data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
	}
	else
	{
		$url = "$C_base[url]/board/$LIST_PHP?data=$data&tot_page=$tot_page&cur_page=$cur_page" ;
	}
	redirect( $url, 1 ) ;
?>
