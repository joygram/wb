<?php
/*
WhiteBoard 2.1.0 2002/1/2
WhiteBoard 2.0.1 pre 2001/10
WhiteBoard 2.0.0 pre 2001/10
Copyright (c) 2001-2002, WhiteBBs.net, All rights reserved.

�ҽ��� ���̳ʸ� ������ ������� ����� ������ ���ϰ� �׷��� �ʰ� �Ʒ��� ������ ������ ��쿡 ���ؼ��� ����մϴ�:

1. �ҽ��ڵ带 ����� �Ҷ��� ���� ��õ� ���۱� ǥ�ÿ� �� ��Ͽ� �ִ� ���ǿ� �Ʒ��� ������ �ݵ�� ǥ�� �ؾ߸� �մϴ�.

2. ������ �� �ִ� ������ ����������� ���� ���۱� ǥ��( ȭ��Ʈ�������� �� �������� �̸�)�� �� ����� ���ǰ� �Ʒ��� ������ ������ �� ������ ���� �����Ǵ� �ٸ� �͵鿡�� ���� �Ǿ�� �մϴ�.

3. �� ����Ʈ����κ��� �Ļ��Ǿ� ���� ��ǰ�� ��ü���� ���� ���� ���� ���� ȭ��Ʈ�������� �̸��̳� �� �������� �̸����� �����̳� ��ǰ�ǸŸ� ������ �� �����ϴ�.  


���۱��� ��������� �� �����ڴ� �� ����Ʈ��� ���� �״�� ������ �� � ǥ���̳� ���� ���� ���� Ư���� ������ ���� �Ÿź����̳� �ո������� �����մϴ�. ���۱��� ���� ����̳� �����ڴ� ��� ������, ������, �μ���, Ư����, ������, �ʿ����� �ջ�, �� ��ǰ�̳� ������ ��ü, ������ �ս�, �������� �ս�, �̵�, ������ �ߴ� � ���� �װ��� ��� ���ο� ���� ���̳�, ��� å�ӷп� ���ϰų�, ��࿡ ���ϰų� �������� å��, �λ�� �ҹ������� ���ǿ� ���ϰų� �׷��� �ƴ��ϰų� �� ����Ʈ������ ����߿� �߻��� �ջ� ���ؼ��� ��� �װ��� �̹� ����� ���̶� ������ å���� �����ϴ�.  

WhiteBoard 1.4.2 : 2001/08/15
WhiteBoard 1.4.0 pre : 2001/08/11
WhiteBoard 1.3.0 : 2001/06/17
WhiteBoard 1.2.3 : 2001/05/10
WhiteBoard 1.1.1 2001/4/11
*/
	//2002/03/18 ���Ѱ˻� �ʿ�.
	ob_start() ;
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!)", "", $data) ;
	$board_group = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|conf)", "", $board_group) ;
	if( substr_count($file, "/") > 3 ) 
	{
		$file = "" ;
	}
	$file = eregi_replace("(^\/|\.\.|`|'|;|#|~|@|\?|=|&|!|php)", "", $file) ;

	include_once("../lib/system_ini.php") ;
	include_once("../lib/get_base.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.

	include("../lib/wb.inc.php") ;
	include_once("${C_base[dir]}/lib/database.php") ;

	$C_data = $data ;
	if($no_image == 1)
	{
		$attach_file = "${file}" ;	
	}
	else
	{
		$attach_file = "${C_base[dir]}/board/data/$C_data/${file}" ;
	}


	if( !is_dir($attach_file) ) 
	{
		$file_size = filesize($attach_file) ;
		$fp_data = wb_fopen($attach_file, "r") ; 
		$buffer = fread($fp_data, $file_size);
		fclose($fp_data);
	}
	else
	{
		echo("�߸��� �����Դϴ�.") ;
		exit ;
	}

		
		//2002/03/21 ����(v 1.x��) download count �����͸� �״�� ����� �� �ְ� �ϱ� ����
	if($count_pos == 3)
	{
		$count_pos = 2 ;
	}
	else if($count_pos == 2)
	{
		$count_pos = 3 ;
	}

	$dbi = new db_interface($C_data, "member", $mode, $filter_type, $key, $field, $C_base[member_db_type], "2", $C_base[dir] ) ;

	$idx_data = array("board_group" => $board_group, "count_pos" => $count_pos ) ; 
	$idx_data = $dbi->update_index($C_data, $index_name, $idx_data, "count") ;	
	$dbi->destroy() ;

	//Header("Content-type: application/x-something"); 
	//Header("Content-type: application/octet-stream"); 
	//Header("Content-type: ${file_type}"); 
	if( eregi("image", $file_type ) )
	{
		Header("Content-type: ${file_type}"); 
	}
	else
	{
		Header("Content-type: Unknown"); 
	}
	//Header("Content-Transfer-Encoding: binary");
	//Header("Content-disposition: attachment; filename=${file_name}"); 
	//Header("Content-disposition: filename=${file_name}"); 
	Header("Content-length: $file_size") ;
	Header("Content-disposition: inline; filename=${file_name}"); 
	Header("Pragma: no-cache"); 
	Header("Expires: 0"); 
	print $buffer ;
?>
