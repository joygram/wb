<?php
/*
Copyright (c) 2001-2005, WhiteBBS.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ�ÿ� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  
*/ 

	///////////////////////////
	// ������ �����־�� ��. 
	// 2002/03/15
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	//������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	$C_base = get_base(1) ; 

	$wb_charset = wb_charset($C_base[language]) ;
 	//����,������� ����� �ʱ�ȭ ����
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	require_once("$C_base[dir]/lib/wb.inc.php") ;

	require_once("$C_base[dir]/member/Member.php") ;


	///////////////////////////
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	// write mode define
	$write_mode = 0 ;
	define("__ANONYMOUS_WRITE", "1") ;
	define("__MEMBER_WRITE", "2") ;
	define("__ADMIN_WRITE", "3") ;

	// �ý��� �������� ȣȯ���� ����. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;


	$C_debug = 1 ;

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	
	$C_data = empty( $data ) ? "member": $data ;
	

	$conf = read_member_config( $data ) ;


	// conf ���� �ִ� �� �˻� v 1.3.0
	$conf_file = "$C_base[dir]/member/conf/$data.conf.php" ;
	if( ! @file_exists( $conf_file ) ) err_abort( "write: $conf_file ������ �������� �ʽ��ϴ�.") ;
	
	include($conf_file) ;

	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}

	//C_���� �������� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	$_skindir = "$C_base[dir]/member/skin/$conf[skin]" ;	
	$_plugindir = "$C_base[dir]/member/plugin" ;


	// default conf���� ó��
	if( empty($conf[attach1_ext]))
	{
		$conf[attach1_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}
	if( empty($conf[attach2_ext]))
	{
		$conf[attach2_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
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


	$license  = license2() ;
	$license2 = license2() ;

	$URL = make_url($data, $Row, "member") ;

	$write_form = "write" ; 




	$_member = new Member() ;


	//////////////////////////////
	// ����: �����߰�
	if( $mode == "insert" )
	{
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

		// ��Ű���� header sent������ �߻��Ͽ� ������ �ٲ�
		echo("<!--$my_version-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		// data ���Ἲ �˻� �ʿ�..
		//$password = wb_encrypt($password, $uname) ;	
		//$name = base64_encode($name) ;

		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //�پ��� �ð������� �����ϱ� ���ؼ�

		if( ! $auth->is_anonymous() )
		{
			if(empty($name)) $name = $auth->alias() ;

			$homepage = $auth->homepage() ;
			$email    = $auth->email() ;
		}
		
		//�ڷ� ����
		// boolean�� ���� mysql���� boolean������ ���ϱ⿡... ��_��
		$update_timestamp = $timestamp ;
		$birthday_select = ($birthday_select == "on")?'t':'f' ;
		$lunar_birth 	 = ($lunar_birth=='t')?1:0 ;
		$email_receive   = ($email_receive=='t')?1:0 ;
		$foreigner       = ($foreigner=='t')?1:0 ;
		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $idnum) ;
		
		//���� �������� ��� ��������
		if(empty($sex)) $sex='t' ;
		if(!empty($idnum) && !$foreigner)
		{
			if(substr($idnum, 6, 1) == "2") $sex = '0' ;
			$sex = ($sex=='t')?1:0 ;
		}

		
		$_member->set( "uid",  $uid ) ;
		$_member->set( "uname", $uname ) ;
		$_member->set( "gid", $gid ) ;
		$_member->set( "password", $password ) ;
		$_member->set( "alias", $alias ) ;
		$_member->set( "access_count", $access_count ) ;
		$_member->set( "point", $point ) ;
		$_member->set( "auth_level", $auth_level ) ;

		$_member->set( "name", $name ) ;
		$_member->set( "firstname", $firstname ) ;
		$_member->set( "sex", $sex ) ;
		$_member->set( "idnum", $idnum ) ;
		$_member->set( "birthday", $birthday ) ;
		$_member->set( "lunar_birth", $lunar_birth ) ;
		$_member->set( "email", $email ) ;
		$_member->set( "homepage", $homepage ) ;
		$_member->set( "mobilephone", $mobilephone ) ;

		$_member->set( "note", $note ) ;

		$_member->set( "final_scholarship", $final_scholarship ) ;
		$_member->set( "job_kind", $job_kind ) ;
		$_member->set( "foreigner", $foreigner ) ;

		$_member->set( "home_country", $home_country ) ;
		$_member->set( "home_city", $home_city ) ;
		$_member->set( "home_district", $home_district ) ;
		$_member->set( "home_address", $home_address ) ;
		$_member->set( "home_zipcode", $home_zipcode ) ;
		$_member->set( "home_phone", $home_phone ) ;
		$_member->set( "home_fax", $home_fax ) ;

		$_member->set( "company_country", $company_country ) ;
		$_member->set( "company_city", $company_city ) ;
		$_member->set( "company_district", $company_district ) ;
		$_member->set( "company_address", $company_address ) ;
		$_member->set( "company_zipcode", $company_zipcode ) ;
		$_member->set( "company_name", $company_name ) ;
		$_member->set( "company_department", $company_department ) ;
		$_member->set( "company_title", $company_title ) ;
		$_member->set( "company_phone", $company_phone ) ;
		$_member->set( "company_fax", $company_fax ) ;
		$_member->set( "company_homepage", $company_homepage ) ;

		$_member->set( "create_time", $timestamp ) ;
		$_member->set( "modify_time", $timestamp ) ;
		$_member->set( "login_time", $login_time ) ;
		$_member->set( "save_dir", $save_dir ) ;

		$_member->set( "password_clue", $password_clue ) ;
		$_member->set( "password_answer", $password_answer ) ;
		$_member->set( "email_receive", $email_receive ) ;


		$index_name = "data" ;
		$ret_data = $dbi->update_index($data, $index_name, $idx_data, "insert") ;	
		switch($ret_data)
		{
			case E_QUERY :
				err_abort("SQL��û �����Դϴ�.<br> �����ڿ��� �������ּ���.") ; 
				break ;

			case E_USER_EXIST :
				err_abort("[$idx_data[uname]]����� �̸��� ������Դϴ�. �ٸ� ���̵� �������ּ���.") ; 
				break ;
		}
		
		//@make_news($data, $Row) ;
		$idx_data = $ret_data ;

		//uid select
		$dbi->init($data, "member", "", "", $uname, "uname", $C_base[member_db_type], "", $C_base[dir]) ;
		$dbi->select_data() ;
		$one_row = $dbi->row_fetch_array(0,"","","member") ;

		$board_group = $one_row[0] ;
		$board_id    = $one_row[0] ;

		$dbi->destroy() ;

		//������ �޾Ҵ��� check
		//������ ���ϴ� ���丮�� ������ �� �����Ѵ�.
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $C_attach1_ext, $InputFile_name ) )
			{
				err_abort("Ȯ���ڰ�[$C_attach1_ext]�� ���ϸ� �ø��� �� �ֽ��ϴ�."); 
			}
			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile, "$C_base[dir]/member/data/$data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		$remote_ip = $REMOTE_ADDR ;

		if( $C_subject_html_use != "1" )
		{
			//$subject = strip_tags($subject) ;
			$subject = htmlspecialchars($subject) ;
		}
		//$subject = base64_encode($subject) ;
		$encode_type = "1" ; // 1.4.5���� �ڷ�鿡 ���ؼ� ����
		$uid = $W_SES[uid] ;
		$is_reply = "0" ;
		
		if( empty($C_name_html_use) )
		{
			$name = htmlspecialchars($name) ; 
		}

		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;

		//�۳���� ���� ����
		$head = array("") ;
		$head[0] = $password ; //������.
		$head[1] = $name ; 
		$head[2] = $w_date ;
		$head[3] = $email ;
		$head[4] = $homepage ;
		$head[5] = $bgimg ;
		$head[6] = $InputFile_name ;
		$head[7] = $InputFile_size ;
		$head[8] = $InputFile_type ;

		$opt['is_notice'] = $notice_check ;
		$opt['html_use'] = $html_use ;

		$save_filename = "$board_group.$board_id" ;
		save_content($data, $save_filename, $head, $comment, $opt, $auth->is_anonymous()) ;

		err_msg("���Խ�û�� �Ͽ����ϴ�.") ;
		
		$url="$C_base[url]" ;
		redirect( $url, 1 ) ;
		exit ;
	} 
	///////////////////////////////
	// ���� 
	///////////////////////////////
	else if( $mode == "update" )
	{
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		if( !$auth->is_admin() && empty($password) )
		{
			err_abort("��й�ȣ�� �־��ּ���.") ;
		}

		$idnum = eregi_replace("(\.\.|\/|`|'|;|#|~|-|@|\?|=|&|!)", "", $idnum) ;
		/*
		//������ ���� �ٸ� ����͵� ��ĥ �� �ֱ⶧���� ��Ű ������ ���� �ʴ´�.
		*/
		$field = 'uid' ;
		$key = $auth->uid() ;
		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		//register_shutdown_function($dbi->destroy()) ;

		$org = $dbi->row_fetch_array(0, $board_group, $board_id) ;
			// ���� ����� ���� �Ǽ�
			// file_fetch_array������ �������� ������ ������ ���µ� 
			// ������ name, subject, type�� ����Ǿ� ���� �����Ƿ� 
			// �ε����� �ִ� ������ �����Ǿ������� ���θ� Ȯ���Ϸ��� 
			// ��Ų�� write.html�� �����δ� ���� ���� ������ ����̴�.
			// @todo if reply do not check index update... 
		$index_name = "data" ;
		$idx_data = $org ;
		$idx_data["password"] = $password ;
	  	$idx_data["alias"] = $alias ;
		$idx_data["lastname"] = $lastname ;
		$idx_data["firstname"]	= $firstname ;
		$idx_data["idnum"] = $idnum ;
		$idx_data["birthday"] = $birthday ;
		$idx_data["birthday_select"] = !empty($birthday_select)?'t':'f' ;
		$idx_data["email"]	= $email ;
		$idx_data["mobilephone"] = $mobilephone ;
		$idx_data["board_group"] = $board_group ;
		$idx_data["sex"] = 't' ;
		//$idx_data = array("board_group" => $board_group, "name" => $name, "subject" => $subject, "type" => $type ) ; 
		$idx_data = $dbi->update_index($data, $index_name, $idx_data, "update") ;	
			// ������ ÷�εǾ��� ���...
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $C_attach1_ext, $InputFile_name ) )
			{
				err_abort("Ȯ���ڰ�[$C_attach1_ext]�� ���ϸ� �ø��� �� �ֽ��ϴ�."); 
			}
			unlink("$C_base[dir]/member/data/$data/${board_group}.".$org[InputFile_name]."_attach") ;

			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile, "data/$data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}
			//�߰� ó�� 
		if( empty($InputFile_name) )
		{
			$InputFile_name = $org[InputFile_name] ;
			$InputFile_size = $org[InputFile_size] ;
			$InputFile_type = $org[InputFile_type] ;
		}

		//�����ص� ������ �ʴ� �͵� ������� ����
		$w_date = $org[w_date] ;
		$remote_ip = $org[remote_ip] ;
		$timestamp = $org[timestamp] ;

		//$password = wb_encrypt($password, $name) ;	
		$encode_type = "1" ;
		$uid = $org[uid] ; //������ ������ uid�� �־��ش�.
		$is_reply = $org[is_reply] ;

		$head = array("") ;
		$head[0] = $password ;
		$head[1] = $name ; 
		$head[2] = $w_date ;
		$head[3] = $email ;
		$head[4] = $homepage ;
		$head[5] = $bgimg ;
		$head[6] = $InputFile_name ;
		$head[7] = $InputFile_size ;
		$head[8] = $InputFile_type ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;

		$save_filename = "${board_group}${board_id}" ;
		save_content($data, $save_filename, $head, $comment, $opt, $auth->is_anonymous()) ;
	
		err_msg("���� �Ͽ����ϴ�.") ;

		//������ ���ԿϷ� ȭ�� 
		
		if( @file_exists("$C_base[dir]/member/skin/$C_skin/cat.html") )
		{
			$url = "$C_base[url]/member/cat.php?data=$data&board_group=$board_group&cur_page=$cur_page&tot_page=$tot_page&subject=".urlencode($subject)."&filter_type=$filter_type" ;
		}
		else
		{
			$url="$C_base[url]/member/$LIST_PHP?data=$data&cur_page=$cur_page&tot_page=$tot_page&filter_type=$filter_type" ;
		}

		redirect( $url, 1 ) ;
		exit ;
	}
	///////////////////////////////
	// ���� ��
	///////////////////////////////
	else if( $mode == "edit" || $mode == "edit_form" ) 
	{
		$mode = "update" ;

			//DBMS�� ��� SELECT���� ������..
			//2002/06/20
		if(empty($field))
		{
			$field = "uid" ;
			$key = $board_group ;
		}

		$dbi = new db_member($data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		
		if( $Row[uid] == __ANONYMOUS ) //anonymous�� �����̸�...
		{
			// 2.1.2 ���� ������ ��� anonymous���̹Ƿ�
				//��ȣȭ �Ǿ� ������ 
			if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
			{
				$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
			}
			$check_data[passwd] = $Row[password]  ;
		}
		else // member�� �����̸�
		{
			// member������ �����غ� 2002/02/17
		}

			//check_data ������ �� ��ġ�� �Ǿ�� �Ѵ�. 
		$auth->run_mode(EXEC_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

			//1.
		$Row[subject] = stripslashes($subject) ;  
		$Row[subject] = str_replace('"', "&quot;", $Row[subject]) ;
		$Row[type] = $type ;
		$Row['html_use_checked'] = ($Row['html_use']==HTML_NOTUSE)?"":"checked" ;
		$Row['br_use_checked'] = $Row['br_use']?"checked":"" ;
		$Row[is_main_writing] = $main_writing ;
		//$Row[category_select] = category_select($data,$Row[type]) ;

			//�����̸� �������� ��쿡�� alias�� ����ϹǷ�. 2002/04/28
			//�������� ���̵� �־��־�� �Ѵ�.	
		if( ! $auth->is_anonymous() )
		{
			$Row['alias'] = $Row['name'] ;
		}
			//2.
		$hide = make_comment( $data, $Row, NOT_USE, "member" ) ;
			// write_mode set
		if($C_debug) echo("Row[uid]::[$Row[uid]]<br>") ;
	
		if($auth->is_admin())
		{
			//$hide['password'] = "<!--\n" ;
			//$hide['/password'] = "-->\n" ;

				//���� ���� ��� ��ȣ�� ������ ��� ������ ������ �� �ִ�.
				//��ȣ�� reset��ų ��쵵 �����ϱ�.. ����ÿ��� skip�� �� �ֵ���..
			if($Row[uid] == __ANONYMOUS) 
			{
				$hide['password'] = "<!--\n" ;
				$hide['/password'] = "-->\n" ;

				$hide['/*password'] = "/*\n" ;
				$hide['password*/'] = "*/\n" ;

					//������ ��尡 �ƴ� ������ ó�� �Ǿ�� ��.
				$hide['admin'] = "<noframes>\n" ;
				$hide['/admin'] = "</noframes>\n" ;
				$hide['anonymous'] ="" ;
				$hide['/anonymous'] ="" ;
			}
		}

		if( $C_edit_outer_header_use == "1" || 
			!isset($C_edit_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}
	}
	///////////////////////////////
	// �۾��� �� WRITE
	///////////////////////////////
	else 
	{
			//��尡 ������ �ȵǾ� �ִ� ��� : ó�� �۾���� ����...
		$mode = "insert" ;

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
		$sess = $auth->member_info() ;

		if( ! $auth->is_anonymous() )
		{
			err_abort("�α��� �߿��� �����Ͻ� �� �����ϴ�.") ;
		}

		$hide = make_comment($data, $Row, NOT_USE, "member") ;

		if( $C_write_outer_header_use == "1" || 
			!isset($C_write_outer_header_use) )
		{
			$outer_header_use = 1 ;
		}

	}

	
	
	if( $outer_header_use == "1" ) 
	{
		// �ܺ� �Ӹ��� ����
		for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
		{
			if( !empty($C_OUTER_HEADER[$i]) )
			{
				@include($C_OUTER_HEADER[$i]) ;
			}
		}
	}
	echo("<!--$my_version-->\n") ;

	$C_table_size = empty($C_table_size)?500:$C_table_size ; 
	$Row[table_size] = $C_table_size ;

		//HEADER ��ҹ��� ���о��� �б� v 1.3.0 
		// ����忡 �´� header�� ������ �� ����� �̿�
	if( @file_exists("$_skindir/${write_form}_header.html") )
    {
        include("$_skindir/${write_form}_header.html") ;
    }
	else if( @file_exists("$_skindir/HEADER") )
	{
		include("$_skindir/HEADER") ;
	}
	else if( @file_exists("$_skindir/header") )
	{
		include("$_skindir/header") ;
	}
	else
	{
		err_abort( "$_skindir/header ������ �������� �ʽ��ϴ�." ); 
	}


	if( @file_exists("$_skindir/${write_form}_header") )
	{
		include("$_skindir/${write_form}_header") ;
	}	

	include "$_skindir/${write_form}.html" ;

	if( $outer_header_use == "1" ) 
	{
		//�ܺ� ������ ����
		for($i = 0 ; $i < sizeof($C_OUTER_FOOTER) ; $i++ )
		{
			if( !empty($C_OUTER_FOOTER[$i]) )
			{
				@include($C_OUTER_FOOTER[$i]) ;
			}
		}
	}
	exit ;


	/**
		���� ����κ��� �����ϴ� ������
		@todo : ���߿� database������ �Űܾ� ���� ������?
	*/
	function save_content($data, $file, $head, $comment, $opt, $is_anonymous = 1)
	{
		global $C_base ;
		$C_debug = 0 ;	
		include("$C_base[dir]/lib/wb.inc.php") ;

		if($is_anonymous)
		{
			if(filter_name($data, $head[1], "member"))
			{
				err_abort("�˼��մϴ�! [������� �ʴ� �̸�]�Դϴ�.") ;
			}
		}

		$head[1] = base64_encode($head[1]) ; // 2002/03/25 �̰����� encoding.
		$cont_head = implode("|", $head) ; 

		$conf_file = "$C_base[dir]/member/conf/$data.conf.php" ;
		if( @file_exists($conf_file) )
		{
			include($conf_file) ;
		} 
		else
		{
			err_abort("save_content: $conf_file ������ �������� �ʽ��ϴ�.") ;
		}

		if(filter_txt($data, $comment, "member"))
		{
			err_abort("�˼��մϴ�! [������� �ʴ� ����, ����, �弳]�� Ÿ�ο��� ���ذ� �� �� �����Ƿ� �ø��� �� �����ϴ�.") ;
		}

		if($C_debug) echo("C_html_use[$C_html_use] opt[html_use][$opt[html_use]]<br>") ;

		switch($opt['html_use'])
		{
			case HTML_NOTUSE:
				if($C_debug) echo("not use html<br>") ;
				$comment = htmlspecialchars($comment) ;
				break ;

			case HTML_FILTER:
				$comment = block_tags($comment, $C_block_tag) ;
				break ;

			case HTML_USE:
			default:
				break ;
		}

		if( $opt[is_notice] == "on" )
		{
			$save_filename = "${file}_notice" ;
		}
		else
		{
			$save_filename = "$file" ;
		}

		$tmp_file = "$C_base[dir]/member/data/$data/".md5(uniqid("")); 
		
		if($C_debug) echo("tmp_file[$tmp_file]<br>") ;

		$fp = @fopen($tmp_file, "w") ;
		if( !$fp )
		{
			err_abort("[$C_base[dir]/member/data/$data/$tmp_file] ������ �������� ���µ� �����߽��ϴ�.") ;
		}
		fwrite($fp, "$cont_head\n$comment") ;
		fclose($fp) ;

		if( @file_exists("$C_base[dir]/member/data/$data/$save_filename") )
		{
			unlink("$C_base[dir]/member/data/$data/$save_filename") ;
		}
        rename("$tmp_file", "$C_base[dir]/member/data/$data/$save_filename") ;

	}
	
?>
