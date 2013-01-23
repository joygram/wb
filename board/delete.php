<?php
/*
Whitebbs 2.8.0 2003/12/27 
see also HISTORY.TXT 

Copyright (c) 2001-2004, WhiteBBs.net, All rights reserved.

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
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	///////////////////////////
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////

	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	$_debug = 0 ;	

	//���� ���� ����� ��쿡�� ��۵� ��� �����Ѵ�.

		//���͸�
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	$board_id = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_id) ;
	if( empty($data) )
	{
		err_abort("data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	$conf = read_board_config($_data) ;
	//C_���� ���� ���� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	//2002/03/18 �⺻ ���Ѱ�����
	if( !isset($conf[auth_perm]) )
	{
		if($conf[write_admin_only] == 1)
		{
			$conf[auth_perm] = "7555" ; //�⺻ ���� ����
			$conf[auth_cat_perm] = "7555" ;
			$conf[auth_reply_perm] = "7555" ;
		}
		else
		{
			$conf[auth_perm] = "7667" ; //�⺻ ���� ����
			$conf[auth_cat_perm] = "7667" ;
			$conf[auth_reply_perm] = "7667" ;
		}

		$conf[auth_user] = "root" ; //�⺻ ������ ���̵� 
		$conf[auth_group] = "wheel" ; //�⺻ ������ �׷�
	}

	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
	$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
	if( empty($Row[user]) ) //���� anonymous�ΰ� �ƴѰ� �˻�
	{
		// 2.1.2 ���� ������ ��� anonymous���̹Ƿ�
			//��ȣȭ �Ǿ� ������ 
		if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
		{
			$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
		}
		$check_data[passwd] = $Row[password]  ;
		if($_debug) echo("$PHP_SELF:check_data[passwd][$check_data[passwd]]<br>") ;
	}
	else 
	{
		// member������ �����غ� 2002/02/17
	}

	$auth->run_mode( EXEC_MODE ) ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	$sess = $auth->member_info() ;

	
	$index_name = "data" ;
	$idx_data = array("board_group" => $board_group, "board_id" => $board_id, "nWriting" => "-1"  ) ; 
	$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "delete") ;	

	if( $idx_data[main_writing_delete] == "1" )
	{
			// ��ü �۰��� ����
		$fp = wb_fopen("$C_base[dir]/board/data/$_data/total.cnt", "w") ;
			//
		fwrite($fp, $dbi->total ) ;
		fclose($fp) ;

		$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
		$flist->read("$board_group") ;
		$nCnt = 0 ;
		while( ($file_name = $flist->next()) )
		{
			unlink("data/$_data/$file_name") ;
		} // end of while 
	}
	else
	{
			//��۸� �����ϵ��� ó��
		unlink("data/$_data/$board_group$board_id") ;
	}
	make_news($_data, $Row)  ;

	err_msg(_L_DELETE_COMPLETE) ;
	if(@file_exists("skin/$conf[skin]/cat.html") && 
		$idx_data[main_writing_delete] != "1" )
	{
		$url = "$C_base[url]/board/cat.php?data=$_data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
	}
	else
	{
		$url = "$C_base[url]/board/$conf[list_php]?data=$data&tot_page=$tot_page&cur_page=$cur_page" ;
	}
	redirect( $url, 1 ) ;
?>
