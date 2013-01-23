<?php
$my_version = "WhiteBoard 2.4.1 2002/05/06" ;
/*
WhiteBoard 2.1.0 2002/1/2
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

 
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

	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $board_group) ;

	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	include("${C_base[dir]}/lib/wb.inc.php") ;
	include_once("${C_base[dir]}/lib/database.php") ;

	$C_data = $data  ;
	$index_name = "data" ;

	err_msg("${url}�� �̵���...") ;
	if(!empty($count_pos))
	{ 
		$dbi = new db_interface($C_data, "index", $mode, $filter_type, $key, $field, "file", "2", $C_base[dir] ) ;

		$idx_data = array("board_group" => $board_group, "count_pos" => $count_pos ) ; 
		$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "count") ;	
		$dbi->destroy() ;
	}

	if(function_exists("fsockopen"))
	{
		sock_redirect( $url ) ;
	}
	else
	{
		redirect( $url ) ;
	}
?>
