<?php
/*
WhiteBBS 2.5.0_B 2002/10/11
WhiteBoard 2.0.6 2001/11/20
WhiteBoard 2.1.0 2002/1/2
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.
 
�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  

���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

*/



class cat 
{
}







///////////////////////////////////////////////////////////////////////////////
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	$wb_charset = wb_charset($C_base[language]) ;
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	//����,������� ����� �ʱ�ȭ ����
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	umask(0000) ;//�������� �⺻ umask�� �����ش�.

	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;
	///////////////////////////

	$_debug = 0 ;	

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	if( empty($data) )
	{
		err_abort("cat: data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}
	if( empty($board_group) )
	{
		err_abort("cat: board_group %s", _L_INVALID_LINK) ;
	}	
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if (!empty($skin) && @file_exists("$C_base[dir]/board/skin/$skin/write.html") )
	{
		$conf[skin] = $skin ;
	}

	$plug = array("") ;

	$timestamp = time() ;
	$write_num = "$timestamp" ;
	$spam_check = base64_encode(encrypt($write_num, $C_base["uniq_num"])) ;
	SetCookie('wb_spam_check', $spam_check, time()+604800, '/') ;

	//C_���� �������� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	// support outer_header use set with CGI var 2002/05/11
	if($outer_header=="0") 
	{
		$conf[cat_outer_header_use] = "0" ;
	}	
	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;
	$_plugindir = "$C_base[dir]/board/plugin" ;

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
	
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_cat_perm], $check_data) ;
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

	// cat�� �ش��ϴ� ��Ų������ �ݵ�� �����Ͽ��� �Ѵ�.
	// ��Ų���� cat�� ��ũ�Ͽ����Ƿ�...
	if( !file_exists("$_skindir/$sess_name/cat.html") && 
		!file_exists("$_skindir/cat.html") )
	{
		err_abort("{$_skindir}/cat.html %s", _L_NOSKINFILE) ;
	}

	$release = get_release($C_base) ;
	echo("<!--VER: $release[1] $release[0]-->\n") ;

	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;

	$Row['board_title'] = "$conf[board_title]" ;	
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

	$license  = license2() ;	
	$license2 = license2() ;	
	$new_license = license($C_skin,$conf) ;
	
	if(empty($DOWNLOAD_PHP))
	{
		$DOWNLOAD_PHP = "download.php" ;
	}
		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row['category_list'] = category_list($_data, $URL['list']) ;

	$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;

	if($conf[cookie_use] == "1")
	{
		$Row['name'] = "" ;
		$Row['email'] = "" ;
		$Row['homepage'] = "" ;

		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row['cookie_name']     = stripslashes($cw_name) ;
		$Row['cookie_email']    = stripslashes($cw_email) ;
		$Row['cookie_homepage'] = stripslashes($cw_home) ;
	}
	//�������� ȣȯ�� ���ؼ�.
	$Row['name'] = $Row['cookie_name'] ;
	$Row['email'] = $Row['cookie_email'] ;
	$Row['homepage'] = $Row['cookie_homepage'] ;

	$conf[table_size] = empty($conf[table_size])?500:$conf[table_size] ; 
	$Row['table_size'] = $conf[table_size] ;

	$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
	$Row['table_align'] = $conf[table_align];

	$hide = make_comment($_data, $Row) ;


	///////////////////////////////////
	// �ܺ� �Ӹ��� ó��
	///////////////////////////////////
	if( $conf[cat_outer_header_use] == "1" || !isset($conf[cat_outer_header_use]))
	{
    	for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
    	{
			if( !empty($conf[OUTER_HEADER][$i]) )
        	{
				@include($conf[OUTER_HEADER][$i]) ;
        	}
    	}
	}

	$Row['spam_check'] = base64_encode("$spam_check|$timestamp") ;
	///////////////////////////////////
	// header ó��
	///////////////////////////////////
	//2002/10/31
	$plug[header] = include_plugin("header", $_plugindir, $conf) ;
	if( @file_exists("$_skindir/$sess_name/HEADER") )
		include("$_skindir/$sess_name/HEADER") ;
	else if( @file_exists("$_skindir/$sess_name/header") )
		include("$_skindir/$sess_name/header") ;
	else if( @file_exists("$_skindir/HEADER") )
		include("$_skindir/HEADER") ;
	else if( @file_exists("$_skindir/header") )
		include("$_skindir/header") ;
	else
	{
		err_abort("$_skindir/header %s", _L_NOFILE) ;
	}

	// cat header 
	if( @file_exists("$_skindir/$sess_name/cat_header") )
		include("$_skindir/cat_header") ;
	else if( @file_exists("$_skindir/cat_header") )
		include("$_skindir/cat_header") ;

	///////////////////////////////////
	$flist = new file_list("$C_base[dir]/board/data/$_data/", 1) ;
	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;
	$flist->read("$board_group") ;
	echo("<table border=0 width=100%>") ;
	$main_writing = 1 ;
	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if($_debug) echo("file_name[$file_name]<br>") ;
		if( strstr($file_name, "attach") ) { continue ; }
		//1.
		$tmp = explode(".", $file_name) ;
		$board_id = ".".$tmp[1] ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id) ;

		if( $Row['secret'] )
		{
			//�۹̼ǰ˻��ϰ�..
			//redirect�� �Ǵ°�?
			$auth->run_mode( EXEC_MODE ) ;
			
			if($_debug ) echo("��[{$Row[secret_passwd]}::{$auth->auth_data[passwd]}] <br>") ;
			//���⼭ �ɸ��� ���̾ƴϰ� ���⼭�� ���� ���´�. 
			//���� ù��° ���� �˻翡�� �ɸ� 
			//���� ���� �˻縦 ��� �� �ִ� ����� �־�� ��. 
			
			if( $auth->mode == "on_check_secret" ) 	
				$auth->auth_mode( "auth_anonymous" ) ;			
			else 
				$auth->auth_mode( "check_secret" );			

			$check_data[passwd] = $Row['secret_passwd']  ;
			$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
		}

		//2002/10/20 ���� ó�� ����
		$cmt_token = wb_token($Row[comment]) ;
		$tmp_comment = "" ;
		for($i = 0; $i < count($cmt_token["cont"]); $i++ )
		{
			if( $cmt_token["attr"][$i] == "NORMAL" )
			{
				// use br�� ���� 2002/05/15
				if($_debug) echo("cat:Row[br_use][$Row[br_use]]<br>") ;
				if($Row['br_use'] == "no")
				{
				}
				else
				{
					if( $Row['html_use'] == HTML_NOTUSE || $Row['br_use'] != "no" ) 
					{
						$cmt_token["cont"][$i] = nl2br($cmt_token["cont"][$i]) ;
						$cmt_token["cont"][$i] = clear_br($cmt_token["cont"][$i]) ;  //table ���� <br />�� ���� ���� ����
						$cmt_token["cont"][$i] = str_replace("  ", "&nbsp;&nbsp;", $cmt_token["cont"][$i]) ;
						$cmt_token["cont"][$i] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $cmt_token["cont"][$i]) ;
					}
				}
				//�ڵ����� ��ũ ����� �ɼǼ��ý� 
				if($conf[url2link_use] == "1" && $Row['html_use'] == HTML_NOTUSE ) 
				{
					$cmt_token["cont"][$i] = url2link( $cmt_token["cont"][$i] ) ;
				}

			}
			else if($cmt_token["attr"][$i] == "W_CODE") 
			{
				$cmt_token["cont"][$i] = wb_highlight($cmt_token["cont"][$i]) ;
			}
			$tmp_comment .= $cmt_token["cont"][$i] ;
		}
		$Row['comment'] = $tmp_comment ;
		
		//���� ���� ��Ų���� ī��Ʈ�� ��ġ�� �ٲ㼭 ����� ���� �־ ȣȯ�� ������ ����...
		$Row['is_main_writing'] = $main_writing ;
		$Row['tot_page'] = $tot_page ;
		$Row['cur_page'] = $cur_page ;
		$Row['filter_type'] = $filter_type ;
		$Row[cnt3] = $Row['cnt_download'] ;
		$Row[cnt2] = $Row[cnt_download2] ;
		$Row['alias'] = $auth->alias() ;
		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row['cookie_name']     = stripslashes($cw_name) ;
			$Row['cookie_email']    = stripslashes($cw_email) ;
			$Row['cookie_homepage'] = stripslashes($cw_home) ;
		}

		$hide = make_comment($_data, $Row, $i) ;

		$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;
		if( $URL[no_img] == "1" )
		{
			$size = GetImageSize($URL[attach_filename]) ;
			$Row['img_width'] = $size[0] ;
			$Row['img_height'] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}

		echo("<tr><td>") ;
		if( $main_writing == 1 )
		{
			$first_reply_url = $URL[reply] ;

			$main_board_id = $board_id ; 
			$Row['main_board_id'] = $main_board_id ;

			$plug[cat] = include_plugin("cat", $_plugindir, $conf) ;
			if(file_exists("$_skindir/$sess_name/cat.html"))
				include "$_skindir/$sess_name/cat.html" ;
			else
				include "$_skindir/cat.html" ;
			$main_writing = 0 ;
				//cat���� ��۸���� ���� ���� �ʴٸ� 2002/05/11 
			if($no_reply_list) break ;
		}
		else
		{
			$Row['main_board_id'] = $main_board_id ;
			$plug[reply_list] = include_plugin("reply_list", $_plugindir, $conf) ;
			if($_debug) echo("include REPLY_LIST.html<br>") ;
			if( @file_exists("$_skindir/$sess_name/reply_list.html") )
				include "$_skindir/$sess_name/reply_list.html" ;
			else if( @file_exists("$_skindir/reply_list.html") )
				include "$_skindir/reply_list.html" ;
			else if( @file_exists("$_skindir/$sess_name/cat.html") )
				include "$_skindir/$sess_name/cat.html" ;
			else
				include "$_skindir/cat.html" ;
		}
		//���뿡�� xmp�±׿� ���� ��� �߰� �ʿ�.
		echo("</td></tr>") ;
		$i++ ;
	} // end of while 
	echo("</table>") ;

	// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row['category_list'] = category_list($_data, $URL['list']) ;

	$URL = make_url($_data, $Row, "board", $conf[cat_php]) ;
	// use first reply_url 
	$URL[reply] = $first_reply_url ;

	$Row['spam_check'] = base64_encode("$spam_check|$timestamp") ;
	/////////////////////////////////////
	// footer ó�� �κ� 
	/////////////////////////////////////
	//��Űó��
	if($conf[cookie_use] == "1")
	{
		$Row['name'] = "" ;
		$Row['email'] = "" ;
		$Row['homepage'] = "" ;

		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row['cookie_name']     = stripslashes($cw_name) ;
		$Row['cookie_email']    = stripslashes($cw_email) ;
		$Row['cookie_homepage'] = stripslashes($cw_home) ;
	}
	//�������� ȣȯ�� ���ؼ�.
	$Row['name']     = $Row['cookie_name'] ;
	$Row['email']    = $Row['cookie_email'] ;
	$Row['homepage'] = $Row['cookie_homepage'] ;

	//2002/06/17
	if( ! $auth->is_anonymous() )
	{
		$Row['email'] = $auth->email() ;
		$Row['member_info'] = $auth->member_info() ;
		if($_debug) echo("write:".print_r($W_SES) ) ;
	}

	//plugin�� ����Ҷ� ���� ������ ������ ���� �ؾ� �ϴ°�?
	//����ġȯ�� �ϴ� ��쿡�� �����ϸ� �ȵǰ� ���� ġȯ�� �ؼ��� �ȵǴ� ��� ���� �� �ݵ�� �ؾ��Ѵ�.
	$plug[cat_footer] = include_plugin("cat_footer", $_plugindir, $conf) ;

	// cat footer
	if( @file_exists("$_skindir/$sess_name/cat_footer") )
		include("$_skindir/cat_footer") ;
	else if( @file_exists("$_skindir/cat_footer") )
		include("$_skindir/cat_footer") ;

	$plug[footer] = include_plugin("footer", $_plugindir, $conf) ;
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
		err_abort("$_skindir/footer %s", _L_NOFILE) ;
	}

	//���̼��� ������ ��� 2002/09/23
	echo $new_license ;
	///////////////////////////
	//�ܺ� ������ ó��
	///////////////////////////
	if ($conf[cat_outer_header_use] == "1" || !isset($conf[cat_outer_header_use]))
	{
    	for($i = 0 ; $i < sizeof($conf[OUTER_FOOTER]) ; $i++ )
    	{
			if( !empty($conf[OUTER_FOOTER][$i]) )
        	{
				@include($conf[OUTER_FOOTER][$i]) ;
        	}
    	}
	}
	//2002/04/21 ������ �ΰ�� count���� �ö��� �ʵ��� ����.
	if(! $auth->is_admin() )
	{
		// count_pos is empty when reply data saved. please this correct.
		// ��ȸ�� ���� ��Ű��
		if(empty($count_pos))
		{
			$count_pos = "4" ;
		}
		$idx_data = array("board_group" => $board_group, "count_pos" => $count_pos ) ;
		$index_name = "data" ;
		$idx_data = $dbi->update_index($_data, $index_name, $idx_data, "count") ;	
	}
	exit ;
?>
