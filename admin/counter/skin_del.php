<?
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
	///////////////////////////

		// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		echo("<script>window.alert('��Ų�� �������� �ʾҽ��ϴ�. data=��Ų'); history.go(-1);</script>") ;
		exit ;
	}	
	else
	{
		$C_data = $data ;
	}
	include("./html/header.html") ;
	$uniq_id = uniqid("deleted.") ;
	$_part = $part ;
	if (empty($_part))
	{
		$_skin_dir = "$C_base[dir]/counter/skin/$C_data" ; 
	}
	else
	{
		$_skin_dir = "$C_base[dir]/counter/skin/__global/$_part/$C_data" ;
	}
	wb_rename($_skin_dir, "$_skin_dir.$uniq_id",1,1) ;
	err_msg("��Ų [$data]�� ���� �Ͽ����ϴ�.") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='skin.php?part=$_part'\">") ;
	exit ;
?>
