<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;

	$_debug = 0 ;
	///////////////////////////
	if ($_debug) print_r($HTTP_POST_FILES) ;

	prepare_server_vars() ;
	if ($_debug) echo("[".$__FILES['InputFile']['name']."][".$__FILES['InputFile']['tmp_name']."]<br>") ;

	switch($part)
	{
		case "news":
			$target_dir = "$C_base[dir]/counter/skin/__global/news" ;
			$Row['title'] = "�ֱٰԽù�" ;
			break ;
		case "category":
			$target_dir = "$C_base[dir]/counter/skin/__global/category" ;
			$title = "ī�װ�" ;
			break ;
		case "pagebar":
			$target_dir = "$C_base[dir]/counter/skin/__global/pagebar" ;
			$title = "��������" ;
			break ;
		default:
			$target_dir = "$C_base[dir]/counter/skin" ;
			$title = "����" ;
			break ;
	}
	wb_upload_uncompress($target_dir) ;

	//2002/09/10 ��ġ ��Ų �̸� ���ϱ�
	$tmp = explode(".", $__FILES['InputFile']['name']) ;
	$file_ext = ".".$tmp[sizeof($tmp)-1] ;
	$base_name = basename($__FILES['InputFile']['name'], $file_ext) ;

	err_msg("[${base_name}]��Ų�� ��ġ �߽��ϴ�.") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='skin.php?part=$part'\">") ;
	exit ;
?>
