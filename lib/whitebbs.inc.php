<?php
if(!defined("__wbbs_inc__")) define("__wbbs_inc__","1") ;
else return ;

$C_base["dir"] = $C_base_dir ; //$C_basedir�� �ܺο��� ����
include("{$C_base["dir"]}/system.ini.php") ;

if($C_use_member == "on")
{
	//$conf[auth_perm] = "7555" ; // $C_auth_perm�� �ܺο��� ���� ;
	$conf[auth_perm] = $C_auth_perm ; // $C_auth_perm�� �ܺο��� ���� ;
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
}

if($C_use_counter == "on")
{
	if(!empty($counter_data))
	{
		include("{$C_base["dir"]}/counter/counter.php") ;
	}
}
?>
