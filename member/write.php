<?php
/*
Copyright (c) 2001-2005, WhiteBBS.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ�ÿ� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ������ �״�Ρ� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  
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

	//unset() x-y.net php���� �̻��� ������ ���� �ʱ�ȭ�� ���� 2004/08/24 

	// write mode define
	$write_mode = 0 ;
	define("__ANONYMOUS_WRITE", "1") ;
	define("__MEMBER_WRITE", "2") ;
	define("__ADMIN_WRITE", "3") ;

	$license  = license2() ;
	$license2 = license2() ;


	require_once("$C_base[dir]/member/Member_Writer.php") ;

	$data = "member" ;

	$writer = new Member_Writer( $data, $auth ) ;


	if( $mode == "insert" )
	{
		$writer->insert() ;
	} 
	else 
	{
		$writer->write_form() ;
	}
	
?>
