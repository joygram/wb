<?php
	///////////////////////////
	// ���Ѱ˻�� �⺻ȯ�� �б� 
	// ������ �����־�� ��. 
	// 2002/03/15
	///////////////////////////
	include("../lib.php") ;
	$C_base = get_base(1) ; //������ �Ǵ� �ּ� �޾ƿ���, lib.php�� ����.
	include($C_base[dir]."/auth/auth.php") ; //����,������� ����� �ʱ�ȭ ����

	include("../file_list.php") ;
	include("../conf/config.php") ;

	$C_auth_perm = "7000" ;
	$auth->perm($C_auth_user, $C_auth_group, $C_auth_perm, $check_data) ;
	///////////////////////////


	if( empty($data) )
	{
		echo("<script>window.alert('�Խ����� �������� �ʾҽ��ϴ�. data=�Խ���'); history.go(-1);</script>") ;
		exit ;
	}	
	else
	{
		$C_data = $data ;
	}
?>
<link rel=StyleSheet href=../skin/default/style.css type=text/css >
<body topmargin='0'  leftmargin='0' marginwidth='0' marginheight='0' bgcolor=#336699>

<?
	//$uniq_id = uniqid("deleted.") ;

	//rename("../data/$data",      "../data/${data}.${uniq_id}") ;
	//rename("../conf/$data.conf", "../conf/${data}.conf.${uniq_id}") ;

	$code="../data/".$data;
	$dir=opendir($code);
	while ($file = readdir($dir)) 
	{
		if(($file != ".") && ($file != ".."))
		{
			unlink ($code."/".$file);
			//$list[]=$file;
		}//if
	}//while
	closedir($dir);
	rmdir ($code);
	unlink ("../conf/".$data.".conf");
     
	echo("<center>�Խ��� ${data}�� �����Ͽ����ϴ�.<br>  </center>") ;
	echo("<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL='index.php'\">") ;
	exit ;

?>
