<?php
	$C_base[dir] = $C_base_dir ;
	$conf[auth_perm] = "7555" ;
	$_data = "member" ;
	//�����ڵ������� ���� �⺻ ���丮 ����
	require_once("$C_base[dir]/lib/wb.inc.php") ;
	//���� ���� �ʿ�...

	// get_base�� ���� �ʿ�
	$C_base = get_base(-1) ;

	//$C_base[member_db_type] = $C_member_db_type ;
	require_once("$C_base[dir]/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����

	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	if( $log == "on" ) $auth->login() ; 
	else if( $log == "off" ) $auth->logout() ; 

	$sess = $auth->member_info() ;
	$hide = make_comment($_data, $sess, NOT_USE, "member") ;
?>
