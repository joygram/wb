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


*/

	//2002/11/10 cat���� �÷����� ���� ����Ʈ ������ �ϱ� ���ؼ� ���� ���� 
	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	$wb_charset = wb_charset($C_base[language]) ;
	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 
	require_once("$C_base[dir]/lib/wb.inc.php") ;

	$_debug = 0 ;
	// �ý��� �������� ȣȯ���� ����. 2003/12/28
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	//2002/11/10 plugin���� ȣ���ϴ� ��� auth�� �̹� ȣ���ϱ� ������ ��ü�� �����ȵǹǷ� ���������� ��Ƴ��´�.
	global $auth ;
	global $C_base ;

	global $WRITE_URL, $EDIT_URL, $REPLY_URL ;
	global $LIST_URL, $DELETE_URL, $CAT_URL ;
	global $ATTACH_URL, $ATTACH2_URL, $ATTACH_FILE, $ATTACH2_FILE ;
	global $DOWNLOAD_URL, $DOWNLOAD2_URL, $HOMEPAGE_URL ;


	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////
	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 
	$conf[auth_perm] = "" ;
	$conf[auth_cat_perm] = "" ;
	$conf[auth_reply_perm] = "" ;
	$conf[auth_user] = "" ;
	$conf[auth_group] = "" ;

	$Row = array("") ;

	//���Թ������
	//2.6������������ ��ɿ� �ʿ��� uniq_num�� �����Ƿ� �̰����� ������ �ý��ۼ����� �ڵ����� ���׷��̵� �ϵ��� �Ѵ�.
	if(empty($C_base["uniq_num"]))
	{
		//���� ���� ��� ���� ����
		$uniq_num_lists = get_uniq_num_list() ;
		$uniq_num = implode("", $uniq_num_lists) ;
		//system.ini����
		$system_conf = "{$C_base[dir]}/system.ini.php" ;
		
		//$ini[uniq_num] = $uniq_num ;
		//save_system_ini($system_conf, $ini) ;  $ini ��� $C_base �� �Ѱ��ش�.
		$C_base[uniq_num] = $uniq_num ;
		save_system_ini($system_conf, $C_base) ;
		
		if($_debug) echo("generate system uniq_num and update system.ini.php uniq_num[{$C_base[uniq_num]}]<br>") ;
	}

	// CGI variable filtering
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	if( empty($data) )
	{
		err_abort("list: data %s", _L_INVALID_LINK) ;
	}	
	else
	{
		$_data = $data ;
	}

	if( empty($list) ) $list = "list" ; 
	$conf = read_board_config($_data) ;
	// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/board/skin/{$skin}/write.html") )
	{
		$conf[skin] = $skin ;
	}
	//C_���� �������� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	$C_data = $_data ;

	$C_data = $_data ;
	$LIST_PHP = $conf[list_php] ;
	$WRITE_PHP = $conf[write_php] ; 
	$DELETE_PHP = $conf[delete_php] ;

	//2002/03/18 �⺻ ���Ѱ�����
	if (!isset($conf[auth_perm]))
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
	//C_���� ���� ���� ȣȯ�� ����
	$C_skin = $conf[skin] ;
	// skin dir pre setting 2002/04/09
	$_skindir = "$C_base[dir]/board/skin/$conf[skin]" ;
	$_plugindir = "$C_base[dir]/board/plugin" ;

	$release = get_release($C_base) ;
	echo("<!--VER: $release[1] $release[0]-->\n") ;

	//bsd_license($lang) ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;
	$license  = license2() ; 
	$license2 = license2() ;	
	$new_license = license($C_skin,$conf) ;	

	$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	//////////////////////////
	// START MAIN
	//////////////////////////
	//�⺻���� asc�� �Ǿ� �ִٸ� ���(�˻����ϵ���...)
	//�⺻���� ���������� �� ���� �߽����� �����ϵ��� �Ǿ� �ִ�.
	$conf[sort_index] = (!isset($conf[sort_index]))?"0":$conf[sort_index] ;
	//�˻���� ���� db_board������ ���� ���� ������?
	//�˻����� �ƹ��͵� �Է����� ������ ��ü����� ������ �ؾ� �ϹǷ�
	$mode = (empty($key))?"":$mode ;
	$mode = ($filter_type > "0")?"find":$mode ;
	$mode = ($conf[sort_order]=="asc" && $conf[sort_order] == 0)?"find":$mode ;
	$mode = ($conf[sort_index] != 0)?"find":$mode ;
	if($_debug) echo("LIST:mode[{$mode}] filter_type:$filter_type<br>") ;
	//�⺻ �˻� �ʵ� name
	$field = empty($field)?"name":$field ;

	if($_debug) echo("LIST:conf[sort_order][$conf[sort_order]]conf[sort_index][$conf[sort_index]]filter_type[$filter_type]<br>") ;
	/////////////////////////////////////////////////
	// ��ü ������ �� ��� : ������ �ٸ� ���ؼ�.. 
	// DB�� ������� �ʱ� ������ �̺κп� �ð��� ���� ����.
	/////////////////////////////////////////////////
	$dbi = new db_board($_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

	// select�ϱ������� total���� ���� �� ���� ������...
	// dbi class���� limit���� ���� �� �ִ� ����� ����.

	$_time_start = getmicrotime() ;
	$dbi->count_data() ;
	$_time_spend = number_format(getmicrotime() - $_time_start, 3) ;	
	if($_debug) echo("count_data exec time[$_time_spend]<br>") ;

	$tot_page = get_total_page( $dbi->total, $conf[nCol]*$conf[nRow] ) ;
	if($_debug) echo("total[$dbi->total], TOT_PAGE:[$tot_page]<br>") ;
	// ������� ��ü �������� ������ ���� �������� ��ġ���� ������ ���� �������� ���ʷ� reset��Ų��.	
	// page control variable set
	$cur_page = ($cur_page < 0 )?0:$cur_page ;
	$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;
	// offset calc
	$line_begin = $cur_page * ($conf[nCol] * $conf[nRow]) ;
	if($_debug) echo("line_begin[$line_begin] cur_page[$cur_page]<br>") ;

	$_time_start = getmicrotime() ;
	$dbi->select_data($line_begin, $conf[nCol] * $conf[nRow]) ;
	$_time_spend = number_format(getmicrotime() - $_time_start, 3) ;	
	if($_debug) echo("select_data exec time[$_time_spend]<br>") ;

	// category list 2001/12/09
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	$Row[category_list] = category_list($_data, $URL['list']) ;
	//�Ӹ����� �� ������
	$Row[nTotal]   = $dbi->total ; 
	$Row[cur_page] = empty($cur_page)?1:$cur_page+1 ;
	$Row[tot_page] = $tot_page ;
	$Row[play_list] = $play_list ; //���� ���ð� ���


	//����üũ�� ���� ������ : �۾��� �ð� ���Ѱ� ����üũ���� ������ �Ѱ� �޵��� �Ѵ�.
	$timestamp = time() ;
	$write_num = "$timestamp" ;
	$spam_check = base64_encode(encrypt($write_num, $C_base["uniq_num"])) ;
	SetCookie('wb_spam_check', $spam_check, time()+604800, '/') ;
	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;



	$hide = make_comment($_data, $Row) ;
	///////////////////////////////////
	// �Ӹ� ó��
	///////////////////////////////////
	if(empty($_plugin_use))
	{
		// �ܺ� �Ӹ� ����
		if( $conf[list_outer_header_use] == "1" || !isset($conf[list_outer_header_use]) )
		{
			for($i = 0 ; $i < sizeof($conf[OUTER_HEADER]) ; $i++ )
			{
				if( !empty($conf[OUTER_HEADER][$i]) )
				{
					@include($conf[OUTER_HEADER][$i]) ;
				}
			}
		}
	}

	//2002/10/26 plugin header ����
	$plug[header] = include_plugin("header", $_plugindir, $conf) ;

	///////////////////////////////////
	// header ����
	///////////////////////////////////
	//������� ��Űó��
	if($conf[cookie_use] == "1")
	{
		$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
		$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
		$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

		$Row[cookie_name]       = stripslashes($cw_name) ;
		$Row[cookie_email]      = stripslashes($cw_email) ;
		$Row[cookie_homepage]   = stripslashes($cw_home) ;

	}
	$Row[name]     = $Row[cookie_name] ;
	$Row[email]    = $Row[cookie_email] ;
	$Row[homepage] = $Row[cookie_homepage] ;

	if(empty($_plugin_use))
	{
		$Row[board_title] = "$conf[board_title]" ;	
	}

	$conf[table_size] = !isset($conf[table_size])?500:$conf[table_size] ;
	$Row[table_size]  = $conf[table_size] ;

	$conf[table_align] = !isset($conf[table_align])?"center":$conf[table_align] ;
	$Row[table_align] = $conf[table_align];

	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;

	//�۾���� ����������ü�� ������ �ǹǷ� 
	$hide = make_comment($_data, $Row) ;
	if(file_exists("$_skindir/$sess_name/HEADER"))
		include("$_skindir/$sess_name/HEADER") ;
	else if(file_exists("$_skindir/$sess_name/header"))
		include("$_skindir/$sess_name/header") ; 
	else if(file_exists("$_skindir/HEADER"))
		include("$_skindir/HEADER") ;
	else if(file_exists("$_skindir/header"))
		include("$_skindir/header") ;
	else
	{
		err_abort("list: {$_skindir}/header %s", _L_NOFILE) ;
	}

	//2002/10/26 list_header plugin ����
	$plug[list_header] = include_plugin("list_header", $_plugindir, $conf) ;

	// list header
	if( @file_exists("$_skindir/$sess_name/{$list}_header") )
	{
		include("$_skindir/$sess_name/{$list}_header") ;
	}
	else if( @file_exists("$_skindir/{$list}_header") )
	{
		include("$_skindir/{$list}_header") ;
	}
	///////////////////////////////////
	$nPos = $start ; //�˻��� ���  ���� br�� ���ؼ� ���� 
	$nCnt = $line_begin ; // �ѹ����� ���� ����
	if($_debug) echo("[$dbi->row_begin][$dbi->row_end]<br>") ;
	echo("$conf[BOX_START]") ;
	for($i = $dbi->row_begin ; $i < $dbi->row_end ; $i ++)
	{
		///////////////////////////////////////
		//1.
		$Row = $dbi->row_fetch_array($i) ;
		if( $Row == -1)
		{
			if($_debug) echo("Row [$i]th is -1<br>") ;
			err_abort(_L_INDEX_BROKEN) ;
		}

		//���� ���� ���� ���� �̵�( row_fetch_array  �� ��� �ִ� ���� cat ������ ����Ǵ� ������ list.php �� �̵���
		if(!empty($conf[subject_max]))
		{
			$Row[subject] = cutting($Row[subject], $conf[subject_max]) ;
		}
			//������� �������� 2002/01/24
			// list������ ������.
			// ����Ʈ���� ������� ������ �ִ� ��쿡�� �±׸� ����.
		if( !empty($conf[comment_max])) 
		{
			$Row[comment] = cutting($Row[comment], $conf[comment_max]) ;
			$Row[comment] = block_tags($Row[comment],"ALL") ;
		}

		$Row[comment_raw] = $Row[comment];
			// use br�� ���� 2002/05/15
		if($br_use == "no")
		{
		}
		else
		{
			if( $Row[html_use] == HTML_NOTUSE || $Row[br_use] != "no" ) 
			{
				$Row[comment] = nl2br($Row[comment]) ;
				$Row[comment] = str_replace("  ", "&nbsp;&nbsp;", $Row[comment]) ;
				$Row[comment] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $Row[comment]) ;
			}
		}
		//�ڵ����� ��ũ ����� �ɼǼ��ý� 
		//html����� �ϸ� �ּ� �ڵ���ũ�� ����� ���ϵ��� ��.
		if($conf[url2link_use] == "1" && $Row[html_use] == HTML_NOTUSE ) 
		{
			$Row[comment] = url2link( $Row[comment] ) ;
		}

		$Row[no] = $dbi->total - $nCnt ;
		if(!empty($_plugin_use))
		{
			if($board_group == $Row[board_group])  
			{
				if(empty($_plugin_list_no))
				{
					$Row[no] = ">>" ;
				}
				else
				{
					$Row[no] = $_plugin_list_no ;
				}
			}
		}
		$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;
		$Row[grad_color] = make_gradation_color($_data, $dbi->total, $conf[nCol]*$conf[nRow], "board") ;

		$Row[cnt_download]  = $Row[cnt3] ;
		$Row[cnt_download2] = $Row[cnt2] ;

		$Row[is_main_writing] = 1 ;
		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;
		$Row[filter_type] = $filter_type ;
		$Row[to] = $conf[list_php] ;


		if(!$auth->is_anonymous())
		{
			$Row[alias] = $auth->alias() ;
		}
			//2002/04/21 ����Ʈ �ȿ� ���� �� ��� ��Ű ó���� ���ؼ�.
		if($conf[cookie_use] == "1")
		{
			$cw_name  = $HTTP_COOKIE_VARS[cw_name] ;
			$cw_email = $HTTP_COOKIE_VARS[cw_email] ;
			$cw_home  = $HTTP_COOKIE_VARS[cw_home] ;

			$Row[cookie_name]     = stripslashes($cw_name) ;
			$Row[cookie_email]    = stripslashes($cw_email) ;
			$Row[cookie_homepage] = stripslashes($cw_home) ;
		}

		//2.
		$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
		/*
		if( $URL[no_img] == "1" )
		{
			$size = GetImageSize($URL[attach_filename]) ;
			$Row[img_width] = $size[0] ;
			$Row[img_height] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}
		*/

		//3.
		$hide = make_comment($_data, $Row, $i) ;

		// 2002/10/26 plugin list.php ����
		$plug['list'] = include_plugin("list", $_plugindir, $conf) ;
		if($_plugin_list_control == "skip") 
		{
			continue ;
		}

		echo("$conf[BOX_DATA_START]") ;
		if(file_exists("$_skindir/$sess_name/{$list}.html"))
			include "$_skindir/$sess_name/{$list}.html" ;
		else 
			include "$_skindir/{$list}.html" ;
		echo("$conf[BOX_DATA_END]") ;
		if( ($nPos % $conf[nCol]) == ($conf[nCol]-1) )
		{
			echo("$conf[BOX_BR]") ;
		}
		$nPos++ ;
		$nCnt++ ;
	}
	echo("$conf[BOX_END]") ;

	///////////////////////////////////
	// �˻�â�� �� ���� �غ�
	///////////////////////////////////
	$checked[$field] = "checked" ;
	$selected[$field] = "selected" ;
	$Row[field] = $field ;
	$Row[spam_check] = base64_encode("$spam_check|$timestamp") ;

	$page_bar = wb_page_bar( $_data, $cur_page, $tot_page, $key, $field, $mode ) ;

	//�۾���� ����������ü�� ������ �ǰ� 
	//�������� �� �ϳ��� ����ǹǷ� �̰����� ����
	$hide = make_comment($_data, $Row) ;
	$Row[board_title] = "$conf[board_title]" ;	
	$URL = make_url($_data, $Row, "board", $conf[list_php]) ;
	$URL['list'] = "$C_base[url]/board/$conf[list_php]?data=$_data" ;
	// category list 2001/12/09
	$Row[category_list] = category_list($_data, $URL['list']) ;

	/////////////////////////////////////
	// ������ ó�� 
	/////////////////////////////////////
	//2002/10/26 plugin list_footer ����
	$plug[list_footer] = include_plugin("list_footer", $_plugindir, $conf) ;
	
	// list footer
	if( @file_exists("$_skindir/$sess_name/{$list}_footer") )
		include("$_skindir/$sess_name/{$list}_footer") ;
	else if( @file_exists("$_skindir/{$list}_footer") )
		include("$_skindir/{$list}_footer") ;

	//2002/10/26 plugin footer ����
	$plug[footer] = include_plugin("footer", $_plugindir, $conf) ;

	if(file_exists("$_skindir/$sess_name/FOOTER") )
		include("$_skindir/$sess_name/FOOTER") ;
	else if(file_exists("$_skindir/$sess_name/footer"))
		include("$_skindir/$sess_name/footer") ;
	else if(file_exists("$_skindir/FOOTER"))
		include("$_skindir/FOOTER") ;
	else if(file_exists("$_skindir/footer"))
		include("$_skindir/footer") ;
	else
	{
		err_abort("list: $_skindir/footer %s", _L_NOFILE) ;
	}

	if(empty($_plugin_use))
	{
		//���̼��� ������ ��� 2002/09/23
		echo $new_license ;

		if ($conf[list_outer_header_use] == "1" || !isset($conf[list_outer_header_use]))
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
	}
?>
