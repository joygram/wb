<?
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	require_once("../../lib/system_ini.php") ;
	require_once("../../lib/get_base.php") ;
	$C_base = get_base(2) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	require_once("../../lib/wb.inc.php") ;
	require_once($C_base[dir]."/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����
	$conf[auth_perm] = "7000" ;
	$auth->perm($conf[auth_user], $conf[auth_group], $conf[auth_perm], $check_data) ;
	///////////////////////////
		// filtering, 2002/03/15 
	$data = eregi_replace("(\.\.|\/|`|'|;|#|~|@|\?|=|&|!|php|conf|[[:space:]])", "", $data) ;
	if( empty($data) )
	{
		echo("<script>window.alert('"._L_BOARDNAME_NEED."'); history.go(-1);</script>") ;
		exit ;
	}	
	else
	{
		$C_data = $data ;
	}

	include("./html/header.html") ;

	$uniq_id = uniqid("deleted.") ;

	$data_dir = "$C_base[dir]/board/data/$C_data" ; 
	$conf_file = "$C_base[dir]/board/conf/$C_data.conf.php" ;

	wb_rename($conf_file, "$conf_file.$uniq_id",1,1) ;
	wb_rename($data_dir, "$data_dir.$uniq_id",1,1) ;
     
	err_msg("[${data}]"._L_DELETEBOARD_COMPLETE) ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"1; URL='board.php'\">") ;
	exit ;
?>
