<?php
$my_version = "WhiteBoard 2.4.1 2002/05/06" ;
$my_version = "WhiteBoard 2.0.6 2001/11/20" ;
$my_version = "WhiteBoard 2.1.0 2001/1/2" ;
$my_version = "WhiteBoard 2.1.2 2001/1/12" ;
/*
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

WhiteBoard 1.4.2 :     2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 :     2001/06/17
WhiteBoard 1.2.3 :     2001/05/10
WhiteBoard 1.1.1       2001/4/11
*/


	$C_debug = 0 ;
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	include_once("${C_base[dir]}/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	include("../lib/wb.inc.php") ;
	///////////////////////////
	//include_once("${C_base[dir]}/lib/database.php") ;
	umask(0000) ;//�������� �⺻ umask�� �����ش�.
	///////////////////////////
	unset($C_auth_perm) ;
	unset($C_auth_cat_perm) ;
	unset($C_auth_reply_perm) ;
	unset($C_auth_user) ;
	unset($C_auth_group) ;

		// CGI variable filtering
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;

	if( empty($data) )
	{
		// ���� ��ġ�� �ʰ� ���� �־����� data������ ó������� �ָŸ�ȣ��.
		//�ӽ� ó�� �������� data�� member�� preset��.
		// 2002/0618
		//err_abort("��ũ�� [data]�� �־��ּ���.") ;
		$C_data = "member" ;
	}	
	else
	{
		$C_data = $data ;
	}

	if( empty($list) ) $list = "list" ; 

	$conf_file = "${C_base[dir]}/member/conf/${C_data}.conf.php" ;
	if( @file_exists($conf_file) )
	{
		include($conf_file) ;
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
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	$sess = $auth->member_info() ;

		// for support multi skin 2002.01.24
	if( !empty($skin) && @file_exists("$C_base[dir]/member/skin/$skin/write.html") )
	{
		$C_skin = $skin ;
	}

		// skin dir pre setting 2002/04/09
	$C_skindir = "$C_base[dir]/member/skin/$C_skin" ;

	echo("<!--$my_version-->\n") ;

	//bsd_license($lang) ;
	$lang = "kr" ;
	include("$C_base[dir]/admin/bsd_license.$lang") ;
	$license  = license2() ;	
	$license2 = license2() ;	

	
	$URL = make_url($C_data, $Row, "member") ;
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//////////////////////////
	// START MAIN
	//////////////////////////

		//�⺻���� asc�� �Ǿ� �ִٸ� ���(�˻����ϵ���...)
		//�⺻���� ���������� �� ���� �߽����� �����ϵ��� �Ǿ� �ִ�.
	$C_sort_index = (!isset($C_sort_index))?"0":$C_sort_index ;

		//�˻����� �ƹ��͵� �Է����� ������ ��ü����� ������ �ؾ� �ϹǷ�
	$mode = (empty($key))?"":$mode ;
	$mode = ($filter_type > "0")?"find":$mode ;
	if($C_debug) echo("MODE[$mode] $filter_type<br>") ;
		//�⺻ �˻� �ʵ� name
	$field = empty($field)?"name":$field ;

	if($C_debug) echo("LIST:filter_type[$filter_type]<br>") ;

	/////////////////////////////////////////////////
	// ��ü ������ �� ��� : ������ �ٸ� ���ؼ�.. 
	// DB�� ������� �ʱ� ������ �̺κп� �ð��� ���� ����.
	/////////////////////////////////////////////////
	$dbi = new db_member($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

		// select�ϱ������� total���� ���� �� ���� ������...
		// dbi class���� limit���� ���� �� �ִ� ����� ����.
	$dbi->count_data() ;

	$tot_page = get_total_page( $dbi->total, $C_nCol*$C_nRow ) ;
	if($C_debug) echo("total[$dbi->total], TOT_PAGE:[$tot_page]<br>") ;

		// ������� ��ü �������� ������ ���� �������� ��ġ���� ������ ���� �������� ���ʷ� reset��Ų��.	
		// page control variable set
	$cur_page = ($cur_page < 0 )?0:$cur_page ;
	$cur_page = ($cur_page >= $tot_page )?0:$cur_page ;

		// offset calc
	$line_begin = $cur_page * ($C_nCol * $C_nRow) ;
	if($C_debug) echo("line_begin[$line_begin] cur_page[$cur_page]<br>") ;

	$dbi->select_data($line_begin, $C_nCol * $C_nRow) ;

		// category list 2001/12/09
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
	//$Row[category_list] = category_list($C_data, $URL['list']) ;
		//�Ӹ����� �� ������
	$Row[nTotal]   = $dbi->total ; 
	$Row[cur_page] = empty($cur_page)?1:$cur_page ;
	$Row[tot_page] = $tot_page ;
	$Row[play_list] = $play_list ; //���� ���ð� ���
	

	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;


	///////////////////////////////////
	// �Ӹ��� ó��
	///////////////////////////////////
		// �ܺ� �Ӹ��� ����
	if( $C_list_outer_header_use == "1" || !isset($C_list_outer_header_use) )
	{
		for($i = 0 ; $i < sizeof($C_OUTER_HEADER) ; $i++ )
		{
			if( !empty($C_OUTER_HEADER[$i]) )
			{
				@include($C_OUTER_HEADER[$i]) ;
			}
		}
	}

	///////////////////////////////////
		// header ����
	///////////////////////////////////
		//�۾���� ����������ü�� ������ �ǹǷ� 
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;
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
		err_abort("list: $C_skindir/header ������ �����ϴ�.") ;
	}
		// list header
	if( @file_exists("$C_skindir/{$list}_header") )
	{
		include("$C_skindir/{$list}_header") ;
	}
	///////////////////////////////////


	$nPos = $start ; //�˻��� ���  ���� br�� ���ؼ� ���� 
	$nCnt = $line_begin ; // �ѹ����� ���� ����
	if($C_debug) echo("[$dbi->row_begin][$dbi->row_end]<br>") ;
	echo("$C_BOX_START") ;
	for($i = $dbi->row_begin ; $i < $dbi->row_end ; $i ++)
	{
		///////////////////////////////////////
			//1.
		$Row = $dbi->row_fetch_array($i) ;
		if( $Row == -1)
		{
			echo("Row [$i]th is -1<br>") ;
			break ;
		}

		$Row[no] = $dbi->total - $nCnt ;

		$Row[cur_page] = $cur_page ;
		$Row[tot_page] = $tot_page ;
		$Row[filter_type] = $filter_type ;


			//plug_in ó�� �ʿ� 2003/06/13
		$Row[name] = $Row[firstname].$Row[lastname] ;
		$Row[mobilephone] = mobile_phone($Row[mobilephone]) ;
		$Row[sex] = $Row[sex]?"��":"��" ;
		$result = get_department($Row[interest_department]) ;
		$Row[interest_department] = $result["name"] ;
		$Row[job] = get_job($Row[job_kind]) ;

		if(!$auth->is_anonymous())
		{
			//$Row[alias] = $auth->alias() ;
		}
			//2.
		$URL = make_url($C_data, $Row, "member") ;
		if( $URL[no_img] == "1" )
		{
			if(@file_exists($URL[attach_filename]))
				$size = GetImageSize($URL[attach_filename]) ;
			$Row[img_width] = $size[0] ;
			$Row[img_height] = $size[1] ;
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

		echo("$C_BOX_DATA_START") ;
		include "$C_skindir/{$list}.html" ;
		echo("$C_BOX_DATA_END") ;
		if( ($nPos % $C_nCol) == ($C_nCol-1) )
		{
			echo("$C_BOX_BR") ;
		}
		$nPos++ ;
		$nCnt++ ;
	}
	echo("$C_BOX_END") ;

	$dbi->destroy() ;

	///////////////////////////////////
	// �˻�â�� �� ���� �غ�
	///////////////////////////////////
	$checked[$field] = "checked" ;
	$selected[$field] = "selected" ;
	$Row[field] = $field ;

	$page_bar = wb_page_bar( $C_data, $cur_page, $tot_page, $key, $field, $mode, "member" ) ;

		//�۾���� ����������ü�� ������ �ǰ� 
		//�������� �� �ϳ��� ����ǹǷ� �̰����� ����
	$hide = make_comment($C_data, $Row, NOT_USE, "member") ;
	$Row[board_title] = "$C_board_title" ;	
	$URL = make_url($C_data, $Row, "member") ;
	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;
		// category list 2001/12/09
	//$Row[category_list] = category_list($C_data, $URL['list']) ;

	/////////////////////////////////////
	// ������ ó�� 
	/////////////////////////////////////
		// list footer
	if( @file_exists("$C_skindir/{$list}_footer") )
	{
		include("$C_skindir/{$list}_footer") ;
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

	if( $C_list_outer_header_use == "1" || !isset($C_list_outer_header_use) )
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
?>
