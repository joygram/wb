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
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$skin = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $skin) ;

	require_once("../lib/system_ini.php") ;
	require_once("../lib/get_base.php") ;

	$C_base = get_base(1) ; 

	$wb_charset = wb_charset($C_base[language]) ;

	require_once("$C_base[dir]/auth/auth.php") ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	require_once("$C_base[dir]/lib/wb.inc.php") ;



	// �ý��� �������� ȣȯ���� ����. 2003/11/05
	global $__SERVER, $__GET, $__POST, $__COOKIE, $__FILES, $__ENV, $__SESSION ;
	prepare_server_vars() ;

	umask(0000) ;//�������� �⺻ umask�� �����ش�.

	$license  = license2() ;	
	$license2 = license2() ;	
	
	
	
	require_once("$C_base[dir]/member/Lister.php") ;	

	
//	$URL = make_url($C_data, $Row, "member") ;
//	$URL['list'] = "$C_base[url]/member/list.php?data=$C_data" ;


	//�˻����� �ƹ��͵� �Է����� ������ ��ü����� ������ �ؾ� �ϹǷ�
//	$mode = (empty($key))?"":$mode ;
//	$mode = ($filter_type > "0")?"find":$mode ;
//	if($C_debug) echo("MODE[$mode] $filter_type<br>") ;
		//�⺻ �˻� �ʵ� name
//	$field = empty($field)?"name":$field ;

//	if($C_debug) echo("LIST:filter_type[$filter_type]<br>") ;
	
	
	///
	$lister = new Lister( $data, $auth ) ;
	
	$lister->list() ;


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


	$lister->list() ;

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
