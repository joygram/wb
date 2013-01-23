<?php
/*
Whitebbs 2.8.0 2003/12/27 
see also HISTORY.TXT 

Copyright (c) 2001-2004, WhiteBBs.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ�ÿ� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  
*/ 

//write.php�籸�� �ʿ�.
	/**
		���� ����κ��� �����ϴ� ������
		@todo : ���߿� database������ �Űܾ� ���� ������?
	*/
	function save_content($_data, $file, $head, $comment, $opt, $conf, $is_anonymous = 1)
		//$conf[block_tag] �� �Ѿ���� �ʾ� $conf �߰���  2004.01.13 by ü���丶��
	{
		global $C_base ;
		$_debug = 0 ;	
		require_once("$C_base[dir]/lib/wb.inc.php") ;

		if($is_anonymous)
		{
			if(filter_name($_data, $head[1]))
			{
				err_abort(_L_FOBIDDEN_NAME) ;
			}
		}

		$conf_file = "$C_base[dir]/board/conf/${_data}.conf.php" ;
		if( @file_exists($conf_file) )
		{
			include($conf_file) ;
		} 
		else
		{
			err_abort("save_content: $conf_file %s", _L_NOFILE) ;
		}

		if(filter_subject($_data,$opt['subject']))
		{
			err_abort(_L_FORBIDDEN_SUBJECT) ;
		}

		if(filter_ip($_data, $head[13]))
		{
			err_abort(_L_FORBIDDEN_IP) ;
		}
		
		$filtered_word = filter_txt($_data, $comment);		
		if (!empty($filtered_word))
		{			
			err_abort("${filtered_word}%s",_L_FORBIDDEN_CONTENT) ;
		}

		if($_debug) echo("conf[html_use][$conf[html_use]] opt[html_use][$opt[html_use]]<br>") ;

		$cmt_token = wb_token($comment) ;
		if($_debug) echo("[".count($cmt_token["cont"])."]<br>") ;
		$tmp_comment = "" ;
		for($i = 0; $i < count($cmt_token["cont"]); $i++ )
		{
			if($_debug) echo("B] $i:: {$cmt_token["cont"][$i]}<br>") ;
			// NORMAL�� ��쿡�� comment ���� ó�� 
			if( $cmt_token["attr"][$i] == "NORMAL" )
			{
				switch($opt['html_use'])
				{
					case HTML_NOTUSE:
						if($_debug) echo("not use html<br>") ;
						$cmt_token["cont"][$i] = block_tags($cmt_token["cont"][$i], "ALL") ;
						break ;

					case HTML_FILTER:
						$cmt_token["cont"][$i] = block_tags($cmt_token["cont"][$i], $conf[block_tag]) ;
						break ;

					case HTML_USE:
					default:
						break ;
				}
			}
			if($_debug) echo("A] $i:: {$cmt_token["cont"][$i]}<br>") ;
			$tmp_comment .= $cmt_token["cont"][$i] ;
		}
		$comment = $tmp_comment ;

		if( $opt[is_notice] == "on" )
		{
			$save_filename = "${file}_notice" ;
		}
		else
		{
			$save_filename = "$file" ;
		}

		$head[1] = base64_encode($head[1]) ; // 2002/03/25 �̰����� encoding.
		$cont_head = implode("|", $head) ; 

		$tmp_file = "$C_base[dir]/board/data/$_data/".md5(uniqid("")); 
		
		if($_debug) echo("tmp_file[$tmp_file]<br>") ;

		$fp = @fopen($tmp_file, "w") ;
		if( !$fp )
		{
			err_abort("[$C_base[dir]/board/data/$_data/$tmp_file]%s", _L_NOWRITE_PERM) ;
		}
		fwrite($fp, "$cont_head\n$comment") ;
		fclose($fp) ;

		if( @file_exists("$C_base[dir]/board/data/$_data/$save_filename") )
		{
			unlink("$C_base[dir]/board/data/$_data/$save_filename") ;
		}
        rename("$tmp_file", "$C_base[dir]/board/data/$_data/$save_filename") ;
	}
	
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
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

	///////////////////////////
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////
	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	//���Թ������
	//2.6������������ ��ɿ� �ʿ��� uniq_num�� �����Ƿ� �̰����� ������ �ý��ۼ����� �ڵ����� ���׷��̵� �ϵ��� �Ѵ�.
	if(empty($C_base["uniq_num"]))
	{
		//���� ���� ��� ���� ����
		$uniq_num_lists = get_uniq_num_list() ;
		$uniq_num = implode("", $uniq_num_lists) ;
		//system.ini����
		$system_conf = "{$C_base[dir]}/system.ini.php" ;		
		$C_base[uniq_num] = $uniq_num ;
		save_system_ini($system_conf, $C_base) ;
		
		if($_debug) echo("generate system uniq_num and update system.ini.php uniq_num[{$C_base[uniq_num]}]<br>") ;
	}


	// �ý��� �������� ȣȯ���� ����. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	// write mode define
	$write_mode = 0 ;
	define("__ANONYMOUS_WRITE", "1") ;
	define("__MEMBER_WRITE", "2") ;
	define("__ADMIN_WRITE", "3") ;

	$_debug = 0 ;
	if($_debug) 
		$redirect_delay = 5 ;
	else 
		$redirect_delay = 1 ;

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	if( empty($data) )
	{
		err_abort("data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	///////////////////////////
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/board/skin/$skin/write.html") )
	{
		$conf[skin] = $skin ;
	}
	//C_���� �������� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;	
	$_plugindir = "$C_base[dir]/board/plugin" ;

	// default conf���� ó��
	if( empty($conf[attach1_ext]))
	{
		$conf[attach1_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}
	if( empty($conf[attach2_ext]))
	{
		$conf[attach2_ext] = "gif,jpg,png,bmp,psd,jpeg,doc,hwp,gul,zip,rar,lha,gz,tgz,txt" ;
	}

	//������ ������ HTML��� �������� ó�� 2002/01/24
	$conf[html_use] = empty($conf[html_use])?0:$conf[html_use] ;
	if($_debug) echo("conf::html_use [$conf[html_use]]<br>") ;
	if( empty($conf[html_use]) )
	{
		$html_use = HTML_NOTUSE ;
	}
	else
	{
		// 2002/05/08 special_html_use:���ο� ��Ų���� html��� ������ Ȯ���ϱ� ���ؼ� 
		// checkbox���� ������ set�� Ȯ���� �� ����.
		if( empty($html_use) )
		{
			$html_use = HTML_NOTUSE ;
		}
		else if ($conf[block_tag])
		{
			$html_use = HTML_FILTER ;
		}
		else
		{
			$html_use = HTML_USE ; 
		}
		$html_use = isset($special_html_use)?$html_use:HTML_USE ;
	}

	//2002/03/18 �⺻ ���Ѱ�����
	if( !isset($conf[auth_perm]))
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

	$license  = license2() ;
	$license2 = license2() ;
	$new_license = license($conf[skin], $conf) ;
	$release = get_release($C_base) ;

	$URL = make_url($_data, $Row) ;
	$Row[board_title] = "$conf[board_title]" ;	
	if( empty($conf[board_title]) )
	{
		$hide[board_title_start] = "<!--\n" ;
		$hide[board_title_end] = "-->\n" ;
	}
	else
	{
		$hide[board_title_start] = "" ;
		$hide[board_title_end] = "" ;
	}
	// if mode is reply_form then, write_form is reply.html
	$write_form = "write" ; 

	if($_debug) echo("[$mode]<br>") ;
	//////////////////////////////
	// ����: �����߰�
	/////////////////////////////
	if( $mode == "insert" )
	{
		if($_debug) echo("write mode is [$mode]<br>") ;

		///////// [����ó��] 2004.9.15	
		 //$spam_check �� $conf[spam_check_use]  �� 1 �϶� ��������� write form ���� hidden ���� ���´�.
		 // spam_check �� 1�ε� name ���� ���� ���´ٸ� �����̴�.
		 // name �� ��� �ִٸ� wb_[unicq_num] ���� ���� ���� �̸����� ġȯ�� �ָ�ȴ�.
		if ($spam_check_use =="1")
		{
			if(!($name=="")) 
				{
					// �̰����� ������ ��� ó���� �Ѵ�.	
					//$subject ="[spam]".$subject;									
					$subject ="[From ".$_data." ]".$subject;
					$_data="_spam";
				}
				else 
				{	
					$wb_name_tmp ="wb_".$C_base[uniq_num];
					$name =$$wb_name_tmp ;								
				}
		}
		else  // �ٸ� ����� �ʿ��� ��찡 ������?
		{
			
		}
		//////////////////////////////////

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		if(empty($status) && $notice_check == "on" )
		{
			//err_abort("�������� �ش� �����ڸ� �� �� �ֽ��ϴ�") ;	
		}

		if($conf[cookie_use] == "1" && $auth->is_anonymous())
		{
			setcookie("cw_name",  $name,     time()+604800, "/") ;
			setcookie("cw_email", $email,    time()+604800, "/") ;
			setcookie("cw_home",  $homepage, time()+604800, "/") ;
		}	

		// ��Ű���� header sent������ �߻��Ͽ� ������ �ٲ�
		$release = get_release($C_base) ;
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		// data ���Ἲ �˻� �ʿ�..
		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //�پ��� �ð������� �����ϱ� ���ؼ�

		//$total = get_total_cnt($_data, "data") ;
		//�̹����ڽ� ������ ���ؼ� �̺κ��� �����ʿ�.
		//�̹� �����Ѵٸ� ���� ID�� �־��ش�.
		if(empty($board_group))
		{
			$board_group = uniqid("D") ;	
			$board_id = uniqid(".") ;
		}
		else
		{
			$board_id = $board_group ;
			$board_group .= "D" ;
		}

		//������ �޾Ҵ��� check
		//������ ���ϴ� ���丮�� ������ �� �����Ѵ�.
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $conf[attach1_ext], $InputFile_name ) )
			{
				err_abort("[$conf[attach1_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile, "$C_base[dir]/board/data/$_data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

		if( $InputFile2 != "none" && !empty($InputFile2) )
		{
			if( !check_string_pattern( $conf[attach2_ext], $InputFile2_name ) )
			{
				err_abort("[$conf[attach2_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			$attach2_file = "${board_group}.${InputFile2_name}_attach2" ;
			// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile2, "$C_base[dir]/board/data/$_data/$attach2_file") ;
		}
		if( $InputFile2_size == 0 )
		{
			$InputFile2_name = "" ;
		}

		$remote_ip = $REMOTE_ADDR ;
		$password = wb_encrypt($password, $name) ;	
		//$name = base64_encode($name) ;
		if( $conf[subject_html_use] != "1" )
		{
			//$subject = strip_tags($subject) ;
			//$subject = htmlspecialchars($subject) ;
			$subject = str_replace("<", "&lt;", $subject) ;
			$subject = str_replace(">", "&gt;", $subject) ;
		}
		//$subject = base64_encode($subject) ;
		$encode_type = "1" ; // 1.4.5���� �ڷ�鿡 ���ؼ� ����
		$timestamp = time() ;
		$uid = $W_SES[uid] ;
		$is_reply = "0" ;
		
		if( empty($conf[name_html_use]) )
		{
			//$name = htmlspecialchars($name) ; 
			$name = str_replace("<", "&lt;", $name) ;
			$name = str_replace(">", "&gt;", $name) ;
		}
		//2002/04/21
		//2002/06/29
		if(!$auth->is_anonymous())
		{
			if(empty($name))     $name     = $auth->alias() ;
			if(empty($homepage)) $homepage = $auth->homepage() ;
			if(empty($email))    $email    = $auth->email() ;
		}
		//������ ��Ų���� ���� ��� �̴ϱ�..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		$secret = ($secret == "on")? "1" : "0" ; 

		//�����ϱ� ���� �÷����� �ֱ�
		$plug[insert] = include_plugin("insert", $_plugindir, $conf) ;
		//�۳���� ���� ����
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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ;
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;		//��б� ���� 2005/08/04(��) ������ 
		$head[21] = $secret_passwd ;

		$opt['is_notice'] = $notice_check ;
		$opt['html_use'] = $html_use ;
		$opt['subject'] = $subject ;

		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt, $conf, $auth->is_anonymous()) ;

			// �ε��� ����
		$cnt = 0 ; $cnt2 = "0" ; $cnt3 = "0" ; $cnt4 = "0" ; 
		$nReply = "0" ; $nWriting = 1 ;

		$update_timestamp = $timestamp ;
		$idx_data = array( 
			"board_group" => $board_group, 
			"board_id" => $board_id, 
			"name" => $name, 
			"cnt" => $cnt, 
			"cnt2" => $cnt2, 
			"subject" => $subject, 
			"type" => $type, 
			"cnt3" => $cnt3, 
			"save_dir" => $save_dir, 
			"encode_type" => $encode_type, 
			"update_timestamp" => $update_timestamp, 
			"cnt4" => $cnt4, 
			"nReply" => $nReply,
			"nWriting" => $nWriting, 
			"subject_color" => $subject_color,
			"mail_reply"    => $mail_reply,
			"extra" => $extra,
			"" => "", 
		) ;

		if($_debug) echo("mail_reply[$mail_reply]") ;
		if( $notice_check == "on") //���� ���ΰ��
		{
			$index_name = "notice" ;
		}
		else
		{
			$index_name = "data" ;
			$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "insert") ;	
			update_total_cnt($_data, $index_name, +1) ; // total.cnt

			@make_news($_data, $Row) ;
		}
		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;

		err_msg(_L_SAVE_COMPLETE) ;
		$url="$C_base[url]/board/$conf[list_php]?data=$_data&cur_page=$cur_page&tot_page=$tot_page" ;
		redirect( $url, $redirect_delay ) ;
		exit ;
	} 
	///////////////////////////////
	// ���� : ���� 
	///////////////////////////////
	else if( $mode == "update" )
	{
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;

		//////// [���� ó��]�� ���� ���� ����  2004.09.25 by ü���丶��		
		
		$wb_name = "name";

		//////////////////////////////////////////////////	

		//������ ��Ų���� ���� ��� �̴ϱ�..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		if( !$auth->is_admin() && empty($password) && !$passwd_exist)
		{
			err_abort(_L_PASSWORD_REQUIRED) ;
		}
		/*
		//������ ���� �ٸ� ����͵� ��ĥ �� �ֱ⶧���� ��Ű ������ ���� �ʴ´�.
		*/
		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$org = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		// ���� ����� ���� �Ǽ�
		// file_fetch_array������ �������� ������ ������ ���µ� 
		// ������ name, subject, type�� ����Ǿ� ���� �����Ƿ� 
		// �ε����� �ִ� ������ �����Ǿ������� ���θ� Ȯ���Ϸ��� 
		// ��Ų�� write.html�� �����δ� ���� ���� ������ ����̴�.
		// @todo if reply do not check index update... 
		if( $name != $org['name'] || 
			$subject != $subject_org || 
			$type != $type_org || 
			$subject_color != $org['subject_color'] )
		{
			$index_name = "data" ;
			$idx_data = array("board_group" => $board_group, "name" => $name, "subject" => $subject, "type" => $type ) ; 
			$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "update") ;	
		}
			// ������ ÷�εǾ��� ���...
		if( $InputFile != "none" && !empty($InputFile) )
		{
			if( !check_string_pattern( $conf[attach1_ext], $InputFile_name ) )
			{
				err_abort("[$conf[attach1_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			@unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach") ;

			$attach_file = "${board_group}.${InputFile_name}_attach" ;
				// 2002/03/26 open_basedir ���Ѷ����� ����.
			move_uploaded_file($InputFile, "data/$_data/$attach_file") ;
		}
		if( $InputFile_size == 0 )
		{
			$InputFile_name = "" ;
		}

	
		if( $InputFile2 != "none" && !empty($InputFile2) )
		{
			if( !check_string_pattern( $conf[attach2_ext], $InputFile2_name ) )
			{
				err_abort("[$conf[attach2_ext]] %s", _L_UPLOAD_LIMIT); 
			}
			@unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2" ) ;
			$attach2_file = "${board_group}.${InputFile2_name}_attach2" ;
				//2002/03/26 open_basedir ���� ������ ����
			move_uploaded_file($InputFile2, "data/$_data/$attach2_file") ;
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

		if( empty($InputFile2_name) )
		{
			$InputFile2_name = $org[InputFile2_name] ;
			$InputFile2_size = $org[InputFile2_size] ;
			$InputFile2_type = $org[InputFile2_type] ;
		}

			//÷������ ���� �߰� 2003/12/26 
		if($remove_attach1 == "on")
		{
			if(file_exists("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach"))
			{
				unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile_name]."_attach") ;
			}
			$InputFile_name = "" ;
		}
		if($remove_attach2 == "on")
		{
			if(file_exists("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2"))
			{
				unlink("$C_base[dir]/board/data/$_data/${board_group}.".$org[InputFile2_name]."_attach2") ;
			}
			$InputFile2_name = "" ;
		}


		$secret = ($secret == "on")? "1" : "0" ; 

		//�����ص� ������ �ʴ� �͵� ������� ����
		$w_date = $org[w_date] ;
		$remote_ip = $org[remote_ip] ;
		$timestamp = $org[timestamp] ;

		if( ($auth->is_admin() && empty($password)) || 
			$passwd_exist && empty($password) )
		{
			$password = $org[password] ;
		}
		else
		{
			$password = wb_encrypt($password, $name) ;	
		}

		$encode_type = "1" ;
		$uid = $org[uid] ; //������ ������ uid�� �־��ش�.
		$is_reply = $org[is_reply] ;

		//�����ϱ� ���� �÷����� �ֱ�
		$plug[update] = include_plugin("update", $_plugindir, $conf) ;

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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ; 
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;		//��б� ���� 2005/08/04(��) ������ 
		$head[21] = $secret_passwd ;

		$opt['html_use'] = $html_use ;
		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt,$conf, $auth->is_anonymous()) ;	   
	
		@make_news($_data, $Row) ;
		err_msg(_L_EDIT_COMPLETE) ;
		if ($to)
		{
			$url="$C_base[url]/board/$to?data=$_data" ;
			if($to=="cat.php") $url .= "&board_group=$board_group" ;
		}
		else if( @file_exists("$_skindir/cat.html") )
		{
			$url = "$C_base[url]/board/$conf[cat_php]?data=$_data&board_group=$board_group" ;
		}
		else
		{
			$url = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		}
		$url .= !empty($cur_page)?"&cur_page=$cur_page":"" ;
		$url .= !empty($filter_type)?"&filter_type=$filter_type":"" ;

		redirect( $url, $redirect_delay ) ;
		exit ;
	}
	///////////////////////////////
	// ���� : ��� 
	///////////////////////////////
	else if( $mode == "reply" ) 
	{
		if($_debug) echo ("reply:conf[auth_reply_perm]:$conf[auth_reply_perm]<br>") ;
		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_reply_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;

		///////// [����ó��] 2004.9.15		
		if ($spam_check_use  =="1") 
		{
			if(!($name==""))
				{
					// �̰����� ������ ��� ó���� �Ѵ�.	
				echo "�߸��� ����";
				exit;
				}
				else 
				{	
					$wb_name_tmp ="wb_".$C_base[uniq_num];
					$name =$$wb_name_tmp ;								
				}
		}
		else  // �ٸ� ����� �ʿ��� ��찡 ������?
		{
			
		}
		//////////////////////////////////

		if( $board_group == "" )
		{
			err_abort(_L_RETRY) ;
		}
		if( $name == "" || $comment == "" )
		{
			err_abort(_L_NAME_CONTENT_REQUIRED) ;
		}

		if($conf[cookie_use] == "1" && $auth->is_anonymous())
		{
			setcookie("cw_name",  $name,     time()+604800, "/") ;
			setcookie("cw_email", $email,    time()+604800, "/") ;
			setcookie("cw_home",  $homepage, time()+604800, "/") ;
		}

		
		if(strstr($__SERVER["HTTP_REFERER"], "cat.php"))
		{
			$_write_html = (file_exists("$_skindir/$sess_name/cat.html"))?"$_skindir/$sess_name/cat.html":"$_skindir/cat.html" ;
		}
		else if(strstr($__SERVER["HTTP_REFERER"], "list.php") )
		{
			$_write_html = (file_exists("$_skindir/$sess_name/list.html"))?"$_skindir/$sess_name/list.html":"$_skindir/list.html" ;
		}
		else
		{
			$write_form = "reply" ;
			if( ! @file_exists( "$_skindir/${write_form}.html") ) 
			{
				$write_form = "write" ;
			}
			$_write_html = (file_exists("$_skindir/$sess_name/{$write_form}.html"))?"$_skindir/$sess_name/{$write_form}.html":"$_skindir/{$write_form}.html" ;
		}

		$write_skin_contents = join ('', @file("$_write_html")) ;

		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		//��Ű�������� header sent�� ����������.. �̰���..
		$release = get_release($C_base) ;
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$lang = "kr" ;
		include("$C_base[dir]/admin/bsd_license.$lang") ;

		//������ �Ѿ�� board_id�� ������� ���ϴ亯�Ҷ� ����Ѵ�.
		$main_board_id = !isset($main_board_id)?$board_id:$main_board_id ;
		$board_id = uniqid(".") ;

		// reply cookie setting required. 
		$password = wb_encrypt($password, $name) ;	

		$remote_ip = $REMOTE_ADDR ;
		$encode_type = "1" ;

		$w_date = date("m/d H:i") ;
		$timestamp = time() ; //�پ��� �ð� ������ �����ϱ� ���ؼ� 1.4.5

		$uid = $W_SES[uid] ;
		$is_reply = 1 ;
		if (empty($conf[name_html_use]))
		{
			//$name = htmlspecialchars($name) ; 
			$name = str_replace("<", "&lt;", $name) ;
			$name = str_replace(">", "&gt;", $name) ;
		}
		//2002/04/21
		//2002/06/29
		if(!$auth->is_anonymous())
		{
			if(empty($name))     $name     = $auth->alias() ;
			if(empty($homepage)) $homepage = $auth->homepage() ;
			if(empty($email))    $email    = $auth->email() ;
		}
		//������ ��Ų���� ���� ��� �̴ϱ�..
		if($_debug) echo("write:special_br_use[$special_br_use]<br>") ;
		$br_use = !isset($special_br_use)?"yes":$br_use ; 
		$br_use = ($br_use == "on"||$br_use == "yes")?"yes":"no" ;
		if($_debug) echo("write: br_use[$br_use]<br>") ;

		//�����ϱ� ���� �÷����� �ֱ�
		$plug[reply] = include_plugin("reply", $_plugindir, $conf) ;

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
		$head[9] = $InputFile2_name ;
		$head[10] = $InputFile2_size ;
		$head[11] = $InputFile2_type ; 
		$head[12] = $link ;
		$head[13] = $remote_ip ;
		$head[14] = $encode_type ;
		$head[15] = $timestamp ;
		$head[16] = $uid ;
		$head[17] = $is_reply ;
		$head[18] = $html_use ;
		$head[19] = $br_use ;
		$head[20] = $secret ;

		$opt['html_use'] = $html_use ;
		$save_filename = "${board_group}${board_id}" ;
		save_content($_data, $save_filename, $head, $comment, $opt,$conf, $auth->is_anonymous()) ;		

		////////////////////////////////////////////////////
		//���� ����׷��� ã�Ƽ� ��¥, ��� ���� �ε������� ���� 
		////////////////////////////////////////////////////
		$idx_data = array( "board_group" => $board_group, "update_timestamp" => time(), "nReply" => "1", "nWriting" => "1" ) ; 
		$index_name = "data" ;
		$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "update") ;	
		$subject = empty($subject)?base64_decode($idx_data[subject]):$subject ;

		@make_news($_data, $Row) ;

		//�ٽ� index_select�ؼ� ������ ������.
		//��ó������ ������ �ε��� �޾ƿ���
		$Row = $dbi->row_fetch_array(0, $board_group, $main_board_id ) ;
		if($_debug) echo("mail_reply:[$Row[mail_reply]]<br>") ;
		if( $Row['mail_reply'] == "on" && !empty($Row[email]))
		{
			if($_debug) echo("reply mail ...") ;
			/* message */
			$style = "<style>
				.wBody 
				{
					background-color: #FFFFFF;
					scrollbar-face-color:#F7F7F7; 
					scrollbar-shadow-color:#cccccc ;
					scrollbar-highlight-color: #FFFFFF;
					scrollbar-3dlight-color: #FFFFFF;
					scrollbar-darkshadow-color: #FFFFFF;
					scrollbar-track-color: #FFFFFF;
					scrollbar-arrow-color: #cccccc
					font-family: Verdana, ����ü;
					font-size: 12px;
					line-height: 20px;
				};
				A:hover {text-decoration:none; color:#CCCCCC}
				A:link {text-decoration:none; color:#666666}
				A:visited {text-decoration:none; color:#666666}
				A:active {text-decoration:none; color:#666666}
				.copyright {font-family:Verdana; font-size:8pt; color:#c0c0c0}
				</style>
				<body class='wBody'>" ;

			$copyright = "<p><table width='100%' border=0 cellpadding=0  cellspacing=0 class='wAdmin'>
				<tr><td align='right' class='copyright'>
				CopyRight(C) 2001-2002, <a href='http://whitebbs.net'>WhiteBBS.net</a> All Right Reserved.&nbsp;
				</td></tr></table>" ;

			/* recipients */
			$mailto  = "$Row[name] <$Row[email]>" ;
			$subject = "RE: $Row[subject]" ; 
			$message  = $style ;	
			//$message .= $copyright."<br>" ; 
			$message .= "�� $name ���� �亯 ��<br>" ;
			$message .= nl2br($comment)."<p>" ;
			$message .= "�� $Row[name] ���� ���� ��<br>";
			$message .= nl2br($Row[comment]);
			$message .= $copyright ; 

			/* To send HTML mail, you can set the Content-type header. */
			$headers  = "Return-Path: <$email>\r\n";
			$headers .= "From: $name <$email>\r\n";
			//$headers .= "X-Sender: <$email>\r\n";
			$headers .= "X-Mailer: WhiteBBS Mailer\r\n"; //mailer

			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=euc-kr\r\n";

			/* and now mail it */
			$success = mail($mailto, $subject, $message, $headers);
			if($_debug) echo("mail_reply:board_id[$main_board_id]<br>") ;
			if($_debug) echo("mail_reply:from email[$email]<br>") ;
			if($_debug) echo("mail_reply:from name[$name]<br>") ;
			if($_debug) echo("mail_reply:to Row[email][$Row[email]]<br>") ;
			if($_debug) echo("mail_reply:to Row[name][$Row[name]]<br>") ;
			if($_debug) echo("mail_reply:Row[comment]$Row[comment]<br>") ;
			if($_debug) echo("mail_reply:REPLY[$comment]<br>") ;

			if(!$success)
			{
				//echo("������ ����� �������� ���߽��ϴ�.<br>") ;
				echo(_L_MAILESEND) ;
			}
		}

		err_msg(_L_SAVE_COMPLETE) ;
		if ($to)
		{
			$url="$C_base[url]/board/$to?data=$_data" ;
		}
		//cat.html�� �ִٸ� �Խ��� ������ ��Ų�̹Ƿ�...
		else if( @file_exists("$_skindir/cat.html") )
		{
			$url = "$C_base[url]/board/$conf[cat_php]?data=$_data&board_group=$board_group" ;
		}
		else
		{
			$url = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
		}
		$url .= !empty($cur_page)?"&cur_page=$cur_page":"" ;
		$url .= !empty($filter_type)?"&filter_type=$filter_type":"" ;
		redirect( $url, $redirect_delay ) ;
		exit ;
	}
	///////////////////////////////
	///////////////////////////////
	// ���� ��
	///////////////////////////////
	///////////////////////////////
	else if( $mode == "edit" || $mode == "edit_form" ) 
	{
		$mode = "update" ;
		$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;
		if( $Row[uid] == __ANONYMOUS ) //anonymous�� �����̸�...
		{
			if($_debug) echo("anonymous writing<br>") ;
			// 2.1.2 ���� ������ ��� anonymous���̹Ƿ�
			//��ȣȭ �Ǿ� ������ 
			if( strlen($Row[password]) > 15 || $Row[encode_type] == "1" )
			{
				$Row[password] = wb_decrypt($Row[password], $Row[name]) ;
			}
			$check_data[passwd] = $Row[password]  ;
			$Row[passwd_exist] = !empty($Row[password])?"1":"" ;	
		}
		else // member�� �����̸�
		{
			// 2002/10/22 ��Ų���� ��й�ȣ 2.5������ ������ ������ ��� ��й�ȣ �Է� ���� �ȹ�����
			$Row[passwd_exist] = 1 ;
			// member������ �����غ� 2002/02/17
		}

		//check_data ������ �� ��ġ�� �Ǿ�� �Ѵ�. 
		if($_debug) print_r($check_data) ;
		$auth->run_mode(EXEC_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;

		//1.
		//$Row[subject] = stripslashes($subject) ;  
		//$Row[subject] = str_replace('"', "&quot;", $Row[subject]) ;
		//$Row[type] = $type ;
		$Row['html_use_checked'] = ($Row['html_use']==HTML_NOTUSE)?"":"checked" ;
		$Row['br_use_checked'] = ($Row['br_use']=="yes")?"checked":"" ;
		$Row[is_main_writing] = $main_writing ;
		$Row[category_select] = category_select($_data,$Row[type]) ;
		$Row[to] = $to ;
		$Row['mail_reply_checked'] = ($Row['mail_reply']=="on")?"checked":"" ;
		if($_debug) echo("Row[mail_reply][$Row[mail_reply]]<br>") ;

		//�����̸� �������� ��쿡�� alias�� ����ϹǷ�. 2002/04/28
		//�������� ���̵� �־��־�� �Ѵ�.	
		if(!$auth->is_anonymous())
		{
			$Row['alias'] = $Row['name'] ;
		}
		//2.
		$hide = make_comment( $_data, $Row ) ;

		// write_mode set
		if($_debug) echo("Row[uid]::[$Row[uid]]<br>") ;
	
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
		if( $conf[edit_outer_header_use] == "1" || 
			!isset($conf[edit_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// �ܺ� �Ӹ��� ����
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}


		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];
		///////////////////////////////////
		// �� ����
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER ��ҹ��� ���о��� �б� v 1.3.0 
		// ����忡 �´� header�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin ����
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin ����
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		//������ ���� ��ġ�� ���ư��� ���ؼ� �߰�
		//2002/09/15 �������̸� �������̽��� ���� 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin ����
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin ����
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//����忡 �´� footer�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/FOOTER") )
			include("$_skindir/$sess_name/FOOTER") ;
		else if( @file_exists("$_skindir/$sess_name/footer") )
			include("$_skindir/$sess_name/footer") ;
		else if( @file_exists("$_skindir/FOOTER") )
			include("$_skindir/FOOTER") ;
		else if( @file_exists("$_skindir/footer") )
			include("$_skindir/footer") ;
		else
		{
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//���̼��� ������ ��� 2002/09/23
		echo $new_license ;
		if( $outer_header_use == "1" ) 
		{
			//�ܺ� ������ ����
			for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
			{
				if( !empty($conf[OUTER_FOOTER][$i]) )
				{
					@include($conf[OUTER_FOOTER][$i]) ;
				}
			}
		}
		exit ;


	}
	///////////////////////////////
	///////////////////////////////
	// ��� ��
	///////////////////////////////
	///////////////////////////////
	else if( $mode == "reply_form" ) 
	{
		$mode = "reply" ;

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_reply_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;		
		
		$write_form = "reply" ;
		if( ! @file_exists( "$_skindir/${write_form}.html") ) 
		{
			// not exit but load write.html
			$write_form = "write" ;
		}
		//�Խ��� ������ ��쿡�� $board_id�� �Ѿ�´�. 2001.08.31
		// $board_id�� �Ѿ���� ��쿡�� ������ �о �����ش�.
		//1.		
		
		if(!empty($board_id))
		{
			$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
			$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;			
			if($conf[url2link_use] == "1") 
			{
				//�ڵ����� ��ũ ����� �ɼǼ��ý� 1.2.2
				$Row[comment] = url2link( $Row[comment] ) ;
			}
			//ó������ �����Ϸ���... 
			// 2002/04/06 $Row[main_writing]�� ��������.
			$Row[main_writing] = nl2br($Row[comment]) ;
			$Row[main_comment] = $Row[main_writing] ; 
			$Row[comment] = "" ;			
		}		
		$Row['br_use_checked'] = "checked" ;
		// ����̰� reply���� ���� ������ �װ��� ����ش�.
		$Row[is_main_writing] = 0 ;

		/////// [���� ó��]�� ���� ���� ����  2004.09.25 by ü���丶��		
		if (($conf[spam_check_use] =="1")&&($sess_name == ""))		
		{
			$wb_name = "wb_".$C_base["uniq_num"];	
			$spam_check_form = "[spam_check_use]<input type=hidden name='spam_check_use' value='1'><br><input type=text name=name class=wForm>[-->spam check]";
			//$spam_check_form ="<input type=hidden name='spam_check_use' value='1'><span style=display:none;><input type=text name=name class=wForm ></span>";	
		}
		else $wb_name = "name";
		//////////////////////////////////////////////////

		if(!$auth->is_anonymous())
		{
			$Row['alias']    = $auth->alias() ;
			$Row['email']    = $auth->email() ;
			$Row['homepage'] = $auth->homepage() ;
		}

		//2.
		$hide = make_comment( $_data, $Row ) ;

		if( $conf[reply_outer_header_use] == "1" || 
			!isset($conf[reply_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// �ܺ� �Ӹ��� ����
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}


		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];
		///////////////////////////////////
		// �� ����
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER ��ҹ��� ���о��� �б� v 1.3.0 
		// ����忡 �´� header�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin ����
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin ����
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		//ó������ ������ �о���� ����file_fetch_array�� �ϸ� main_writing�� �����Ͱ� $Row�� ���ϱ� write_header �Ŀ� ��� ���� ó�� 
		if($conf[cookie_use] == "1")
		{
			//�ѹ� ����� ���� �̱� ������ clear��Ŵ.
			$Row[name] = "" ;
			$Row[email] = "" ;
			$Row[homepage] = "" ;

			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;
			$Row[name]  = $cw_name ;
			$Row[email] = $cw_email ;
			$Row[homepage]  = $cw_home ;
			/*
			$Row[name] = empty($cw_name)?$Row[name]:stripslashes($cw_name) ;
			$Row[email] = empty($cw_email)?$Row[email]:stripslashes($cw_email) ;
			$Row[homepage] = empty($cw_home)?$Row[homepage]:stripslashes($cw_home) ;
			*/
		}

		//������ ���� ��ġ�� ���ư��� ���ؼ� �߰�
		//2002/09/15 �������̸� �������̽��� ���� 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin ����
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin ����
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//����忡 �´� footer�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/FOOTER") )
			include("$_skindir/$sess_name/FOOTER") ;
		else if( @file_exists("$_skindir/$sess_name/footer") )
			include("$_skindir/$sess_name/footer") ;
		else if( @file_exists("$_skindir/FOOTER") )
			include("$_skindir/FOOTER") ;
		else if( @file_exists("$_skindir/footer") )
			include("$_skindir/footer") ;
		else
		{
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//���̼��� ������ ��� 2002/09/23
		echo $new_license ;

		if( $outer_header_use == "1" ) 
		{
			//�ܺ� ������ ����
			for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
			{
				if( !empty($conf[OUTER_FOOTER][$i]) )
				{
					@include($conf[OUTER_FOOTER][$i]) ;
				}
			}
		}
		exit ;

	}
	///////////////////////////////
	// �۾��� �� WRITE
	///////////////////////////////
	else 
	{
		//��尡 ������ �ȵǾ� �ִ� ��� : ó�� �۾���� ����...
		$mode = "insert" ;

		$auth->run_mode(WRITE_MODE) ;
		$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		$sess = $auth->member_info() ;
		//���Ǻ� ��Ų ����� �����߰� 2002/09/21
		if($auth->is_admin())
			$sess_name = "admin" ;
		else if($auth->is_group())
			$sess_name = "group" ;
		else if($auth->is_member())
			$sess_name = "member" ;
		else
			$sess_name = "" ;
		if($_debug) echo("sess_name[$sess_name]<br>") ;
		
		/////// [���� ó��]�� ���� ���� ����  2004.09.25 by ü���丶��	
		if($_debug) echo "spam check : [$conf[spam_check_use]]";
		if (($conf[spam_check_use] =="1")&&($sess_name == ""))		
		{
			$wb_name = "wb_".$C_base["uniq_num"];	
			$spam_check_form = "[spam_check_use]<input type=hidden name='spam_check_use' value='1'><br><input type=text name=name class=wForm>[-->spam check]";
			//$spam_check_form ="<input type=hidden name='spam_check_use' value='1'><span style=display:none;><input type=text name=name class=wForm ></span>";						
		}
		else $wb_name = "name";
		//////////////////////////////////////////////////	
		
		$Row[notice_check] = notice_check($_data,$Row[status]) ;
		$Row[category_select] = category_select($_data,$Row[type]) ;
		$Row[is_main_writing] = "1" ;
		if( ! $auth->is_anonymous() )
		{
			$Row['alias']    = $auth->alias() ;
			$Row['email']    = $auth->email() ;
			$Row['homepage'] = $auth->homepage() ;
		}
	
		$Row[homepage] = "" ;
		$Row[link] = "" ;
		$Row[is_main_writing] = 1 ;
		$Row['br_use_checked'] = "checked" ;

		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row[name] = empty($cw_name)?$Row[name]:stripslashes($cw_name) ;
			$Row[email] = empty($cw_email)?$Row[email]:stripslashes($cw_email) ;
			$Row[homepage] = empty($cw_home)?$Row[homepage]:stripslashes($cw_home) ;
		}

		if( !$auth->is_anonymous() )
		{
			$Row[email] = $auth->email() ;
			$Row[member_info] = $auth->member_info() ;
			if($_debug) echo("write:".print_r($W_SES) ) ;
		}
			//2.
		$hide = make_comment($_data, $Row) ;		

		if( $conf[write_outer_header_use] == "1" || 
			!isset($conf[write_outer_header_use]))
		{
			$outer_header_use = 1 ;
		}

		if( $outer_header_use == "1" ) 
		{
			// �ܺ� �Ӹ��� ����
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}
		echo("<!--VER: $release[1] $release[0]-->\n") ;
		$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
		$Row[table_size] = $conf[table_size] ;

		$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
		$Row[table_align] = $conf[table_align];

		//edit, write_form�� ���� ���� img_box�� �־��.
		include("../lib/imagebox.php") ;
		
		// imgbox�������� ���� �߰� 2004/10/06 by apollo
		// make_form_recover() ;
		/* �̺κп����� ���� ó���� write ���� ��Ű ������ �ȵ�
		$Row[comment] = $comment ;
		$Row[name] = $name ;
		$Row[subject] = $subject ;
		$Row[homepage] = $homepage ;
		$Row[to] = $to ;
		//ī�װ� �κ� ���� ����.
		*/



		///////////////////////////////////
		// �� ����
		///////////////////////////////////
		$plug = array("") ;
		//2002/10/31
		$plug[header] = include_plugin("header", $_plugindir, $conf) ;
		//HEADER ��ҹ��� ���о��� �б� v 1.3.0 
		// ����忡 �´� header�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/HEADER") )
			include("$_skindir/$sess_name/HEADER") ;
		else if( @file_exists("$_skindir/$sess_name/header") )
			include("$_skindir/$sess_name/header") ;
		else if( @file_exists("$_skindir/HEADER") )
			include("$_skindir/HEADER") ;
		else if( @file_exists("$_skindir/header") )
			include("$_skindir/header") ;
		else
			err_abort("$_skindir/header %s", _L_NOFILE); 

		//2002/10/31 plugin ����
		$write_header = "{$write_form}_header" ;
		$plug[$write_header] = include_plugin("$write_header", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_header"))
			include("$_skindir/$sess_name/{$write_form}_header") ;
		else if(file_exists("$_skindir/{$write_form}_header"))
			include("$_skindir/{$write_form}_header") ;

		//2002/10/31 plugin ����
		$plug[$write_form] = include_plugin("$write_form", $_plugindir, $conf) ;

		// imgbox�������� ���� �߰� 2004/10/06 by apollo
		$Row[html_use_checked] = ($html_use == "on")?"checked":"" ;
		$Row[mail_reply_checked] = ($mail_reply == "on")?"checked":"";
		$Row[br_use_checked] = "checked";  // br_use �⺻ �ɼ��� ���� ����
		$Row[link] = $link ;


		//������ ���� ��ġ�� ���ư��� ���ؼ� �߰�
		//2002/09/15 �������̸� �������̽��� ���� 
		if(file_exists("$_skindir/$sess_name/{$write_form}.html"))
			include "$_skindir/$sess_name/{$write_form}.html" ;
		else
			include "$_skindir/{$write_form}.html" ;

		//2002/10/31 plugin ����
		$write_footer = "{$write_form}_footer" ;
		$plugin[$write_footer]  = include_plugin("$write_footer", $_plugindir, $conf) ;
		if(file_exists("$_skindir/$sess_name/{$write_form}_footer"))
			include("$_skindir/$sess_name/{$write_form}_footer") ;
		else if(file_exists("$_skindir/{$write_form}_footer"))
			include("$_skindir/{$write_form}_footer") ;

		//2002/10/31 plugin ����
		$plugin[footer] = include_plugin("footer", $_plugindir, $conf) ;
		//����忡 �´� footer�� ������ �� ����� �̿�
		if( @file_exists("$_skindir/$sess_name/FOOTER") )
			include("$_skindir/$sess_name/FOOTER") ;
		else if( @file_exists("$_skindir/$sess_name/footer") )
			include("$_skindir/$sess_name/footer") ;
		else if( @file_exists("$_skindir/FOOTER") )
			include("$_skindir/FOOTER") ;
		else if( @file_exists("$_skindir/footer") )
			include("$_skindir/footer") ;
		else
		{
			err_abort("$_skindir/footer %s", _L_NOFILE); 
		}
		//���̼��� ������ ��� 2002/09/23
		echo $new_license ;

		if( $outer_header_use == "1" ) 
		{
			//�ܺ� ������ ����
			for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
			{
				if( !empty($conf[OUTER_FOOTER][$i]) )
				{
					@include($conf[OUTER_FOOTER][$i]) ;
				}
			}
		}
		exit ;
	}
	// �۾��� �� WRITE ��
	///////////////////////////////
?>
