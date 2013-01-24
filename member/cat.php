<?php
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2002/1/2" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001, WhiteBBs.net, All rights reserved.

 
�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
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
	//include_once("$C_base[dir]/lib/database.php") ;
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;
	unset($C_debug) ;

	$C_debug = 0 ;	
	
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;
	
	if( empty($data) )
	{
		err_abort("cat: data ��ũ�� �ùٸ��� �ʽ��ϴ�.") ;
	}	
	else
	{
		$C_data = $data ;
	}

	if( empty($board_group) )
	{
		err_abort("cat: board_group ��ũ�� �ùٸ��� �ʽ��ϴ�.") ;
	}	

	if($C_debug) echo("cat:board_group[$board_group]<br>") ;

		// conf ���� �ִ� �� �˻� v 1.3.0
	$conf_file = "$C_base[dir]/member/conf/${C_data}.conf.php" ;
	if( @file_exists($conf_file) )
	{
		include($conf_file) ;
	} 
	else
	{
		err_abort("cat: $conf_file ������ �������� �ʽ��ϴ�.") ;
	}

		// support outer_header use set with CGI var 2002/05/11
	if($outer_header=="0") 
	{
		$C_cat_outer_header_use = "0" ;
	}	
		// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}
	$C_skindir = "$C_base[dir]/member/skin/$C_skin" ;

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
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_cat_perm, $check_data) ;
	$sess = $auth->member_info() ;


		// cat�� �ش��ϴ� ��Ų������ �ݵ�� �����Ͽ��� �Ѵ�.
		// ��Ų���� cat�� ��ũ�Ͽ����Ƿ�...
	if( !@file_exists("$C_skindir/cat.html") )
	{
		err_abort("��Ų���� $C_skindir/cat.html�� �������� �ʽ��ϴ�.") ;
	}

	echo("<!--$my_version-->\n") ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;

		//�ε������� �����ϴ� �ڷ�� URL�� ���� �޴´�.
	$Row['subject'] = stripslashes($subject) ;  
	$Row['type'] = $type ; 
	$Row['board_title'] = "$C_board_title" ;	
	if( empty($C_board_title) )
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
	
	if(empty($DOWNLOAD_PHP))
	{
		$DOWNLOAD_PHP = "download.php" ;
	}

		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row['category_list'] = category_list($C_data, $URL['list']) ;

	$URL = make_url($C_data, $Row, "member") ;
	///////////////////////////////////
	// header ó��
	///////////////////////////////////
	if($C_cookie_use == "1")
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

	$C_table_size = empty($C_table_size)?500:$C_table_size ; 
	$Row['table_size'] = $C_table_size ;
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;


	///////////////////////////////////
	// �ܺ� �Ӹ��� ó��
	///////////////////////////////////
	if( $C_cat_outer_header_use == "1" || !isset($C_cat_outer_header_use) )
	{
    	for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
    	{
			if( !empty($C_OUTER_HEADER[$i]) )
        	{
				@include($C_OUTER_HEADER[$i]) ;
        	}
    	}
	}


	if( @file_exists("$C_skindir/HEADER") )
	{
		include("$C_skindir/HEADER") ;
	}
	else if( @file_exists("$C_skindir/header") )
	{
		include("$C_skindir/header") ;
	}
	else
	{
		err_abort("$C_skindir/header ������ �����ϴ�.") ;
	}
		// cat header
	if( @file_exists("$C_skindir/cat_header") )
	{
		include("$C_skindir/cat_header") ;
	}
	///////////////////////////////////
	
	$flist = new file_list("$C_base[dir]/member/data/$C_data/", 1) ;

		//DBMS�� �̿��Ұ�� �⺻������ �������ϴ� ���̴�.
		//DB������ �ű��� ���.
		//2002/06/20
	if(empty($field))
	{
		$field = "uid" ;
		$key = $board_group ;
	}

	$dbi = new db_member($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;
	$dbi->select_data() ;
	
	$flist->read("$board_group") ;
	echo("<table border=0 width=100%>") ;
	$main_writing = 1 ;
	$i = 0 ;
	while( ($file_name = $flist->next()) )
	{
		if( strstr($file_name, "attach") ) { continue ; }
			//1.
		$tmp = explode(".", $file_name) ;
		$board_id = ".".$tmp[1] ;
		$Row = $dbi->row_fetch_array(0, $board_group, $board_id, "member") ;

		$Row['type'] = $type ;
		$Row['tot_page'] = $tot_page ;
		$Row['cur_page'] = $cur_page ;
		$Row['filter_type'] = $filter_type ;
		$Row['alias'] = $auth->alias() ;
			//2.
		$URL = make_url($C_data, $Row, "member") ;
		if( $URL[no_img] == "1" )
		{
			if(@file_exists($URL[attach_filename])) 
				$size = GetImageSize($URL[attach_filename]) ;
			$Row['img_width'] = $size[0] ;
			$Row['img_height'] = $size[1] ;
		}
		if( $URL[no_img2] == "1" )
		{
			if(@file_exists($URL[attach2_filename])) 
				$size = GetImageSize($URL[attach2_filename]) ;
			$Row[img2_width] = $size[0] ;
			$Row[img2_height] = $size[1] ;
		}
			//3.
		$hide = make_comment($C_data, $Row, $i, "member") ;
		
		echo("<tr><td>") ;
		if( $main_writing == 1 )
		{
			$first_reply_url = $URL[reply] ;

			include "$C_skindir/cat.html" ;
			$main_writing = 0 ;
				//cat���� ��۸���� ���� ���� �ʴٸ� 2002/05/11 
			if($no_reply_list) break ;
		}
		else
		{
			if( @file_exists("$C_skindir/reply_list.html") )
			{
				include "$C_skindir/reply_list.html" ;
			}
			else 
			{
				include "$C_skindir/cat.html" ;
			}
		}
		echo("</td></tr>") ;
		$i++ ;

	} // end of while 

	echo("</table>") ;


		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row['category_list'] = category_list($C_data, $URL['list']) ;

	$URL = make_url($C_data, $Row, "member") ;
		// use first reply_url 
	$URL[reply] = $first_reply_url ;
	/////////////////////////////////////
	// footer ó�� �κ� 
	/////////////////////////////////////
		//��Űó��
	if($C_cookie_use == "1")
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

		// cat footer
	if( @file_exists("$C_skindir/cat_footer") )
	{
		include("$C_skindir/cat_footer") ;
	}

	if( @file_exists("$C_skindir/FOOTER") )
	{
		include("$C_skindir/FOOTER") ;
	}
	else if( @file_exists("$C_skindir/footer") )
	{
		include("$C_skindir/footer") ;
	}
	else
	{
		err_abort("$C_skindir/footer ������ �����ϴ�.") ;
	}

	///////////////////////////
	//�ܺ� ������ ó��
	///////////////////////////
	if( $C_cat_outer_header_use == "1" || !isset($C_cat_outer_header_use) )
	{
    	for($i = 0 ; $i < sizeof($C_OUTER_FOOTER) ; $i++ )
    	{
			if( !empty($C_OUTER_FOOTER[$i]) )
        	{
				@include($C_OUTER_FOOTER[$i]) ;
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
		//$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "count") ;	
	}

	exit ;
?>
